<?php

namespace App\Http\Controllers\Personnel;

use App\Http\Controllers\Controller;
use App\Models\DocumentType;
use App\Models\PersonnelRegistration;
use App\Models\RequestPurpose;
use App\Models\Resident;
use App\Services\WFQService;
use Illuminate\Database\UniqueConstraintViolationException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class ResidentRequestController extends Controller
{
    protected WFQService $wFQService;

    public function __construct()
    {
        $this->wFQService = new WFQService;
    }

    public function create()
    {
        $residents = Resident::with('user')->orderBy('last_name')->get();
        $documentTypes = DocumentType::where('is_active', true)->get();
        $purposes = RequestPurpose::where('is_active', true)->get();

        return view('personnel.file-request', compact('residents', 'documentTypes', 'purposes'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'resident_id' => ['required', 'exists:residents,id'],
            'document_type_id' => ['required', 'exists:document_types,id'],
            'purpose_id' => ['required', 'exists:request_purposes,id'],
            'purpose_other' => ['nullable', 'string', 'max:255'],
        ]);

        $resident = Resident::findOrFail($validated['resident_id']);

        $activeRequests = $resident->documentRequests()
            ->whereIn('status', ['pending', 'reviewing'])
            ->get();

        if ($activeRequests->count() >= 2) {
            return back()->withErrors(['resident_id' => 'This resident already has 2 active document requests.'])->withInput();
        }

        if ($activeRequests->where('document_type_id', $validated['document_type_id'])->isNotEmpty()) {
            return back()->withErrors(['document_type_id' => 'This resident already has an active request for this document type.'])->withInput();
        }

        DB::beginTransaction();
        try {
            $documentRequest = $this->createDocumentRequest($resident, $validated);
            $this->wFQService->enqueue($documentRequest);

            \App\Services\NotificationService::notifyPersonnelOfNewRequest($documentRequest);

            PersonnelRegistration::create([
                'personnel_id' => Auth::id(),
                'resident_id' => $resident->id,
                'action' => 'filed_request',
                'metadata' => [
                    'queue_number' => $documentRequest->queue_number,
                    'document_type_id' => $validated['document_type_id'],
                ],
            ]);

            DB::commit();
        } catch (\Throwable $e) {
            DB::rollBack();
            throw $e;
        }

        return redirect()->route('personnel.requests')
            ->with('success', "Document request filed for {$resident->full_name}. Queue #: {$documentRequest->queue_number}");
    }

    protected function createDocumentRequest($resident, array $validated)
    {
        for ($attempt = 1; $attempt <= 3; $attempt++) {
            try {
                return $resident->documentRequests()->create([
                    'queue_number' => $this->wFQService->generateQueueNumber(),
                    'document_type_id' => $validated['document_type_id'],
                    'purpose_id' => $validated['purpose_id'],
                    'purpose_other' => $validated['purpose_other'] ?? null,
                    'status' => 'pending',
                ]);
            } catch (UniqueConstraintViolationException $e) {
                if ($attempt === 3 || ! $this->wFQService->isQueueNumberCollision($e)) {
                    throw $e;
                }
            }
        }
    }
}
