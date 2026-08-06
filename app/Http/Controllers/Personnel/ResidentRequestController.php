<?php

namespace App\Http\Controllers\Personnel;

use App\Http\Controllers\Controller;
use App\Models\DocumentType;
use App\Models\PersonnelRegistration;
use App\Models\RequestPurpose;
use App\Models\Resident;
use App\Services\WFQService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

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

        $documentRequest = $resident->documentRequests()->create([
            'queue_number' => $this->wFQService->generateQueueNumber(),
            'document_type_id' => $validated['document_type_id'],
            'purpose_id' => $validated['purpose_id'],
            'purpose_other' => $validated['purpose_other'] ?? null,
            'status' => 'pending',
        ]);

        $this->wFQService->enqueue($documentRequest);

        PersonnelRegistration::create([
            'personnel_id' => Auth::id(),
            'resident_id' => $resident->id,
            'action' => 'filed_request',
            'metadata' => [
                'queue_number' => $documentRequest->queue_number,
                'document_type_id' => $validated['document_type_id'],
            ],
        ]);

        return redirect()->route('personnel.requests')
            ->with('success', "Document request filed for {$resident->full_name}. Queue #: {$documentRequest->queue_number}");
    }
}
