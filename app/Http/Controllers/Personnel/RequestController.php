<?php

namespace App\Http\Controllers\Personnel;

use App\Http\Controllers\Controller;
use App\Models\AuditLog;
use App\Models\DocumentRequest;
use App\Services\FileService;
use App\Services\WFQService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class RequestController extends Controller
{
    protected WFQService $wFQService;

    protected FileService $fileService;

    public function __construct()
    {
        $this->wFQService = new WFQService;
        $this->fileService = new FileService;
    }

    public function index(Request $request)
    {
        $query = DocumentRequest::with(['resident.user', 'documentType', 'purpose']);

        if ($search = $request->input('search')) {
            $query->where(function ($q) use ($search) {
                $q->whereHas('resident', function ($r) use ($search) {
                    $r->where('first_name', 'like', "%{$search}%")
                        ->orWhere('last_name', 'like', "%{$search}%");
                })->orWhere('queue_number', 'like', "%{$search}%");
            });
        }

        $datePreset = $request->input('date_preset');
        $dateFrom = $request->input('date_from');
        $dateTo = $request->input('date_to');

        if ($datePreset && ! $dateFrom && ! $dateTo) {
            match ($datePreset) {
                'today' => $query->whereDate('created_at', today()),
                'this_week' => $query->whereBetween('created_at', [now()->startOfWeek(), now()->endOfWeek()]),
                'this_month' => $query->whereMonth('created_at', now()->month)->whereYear('created_at', now()->year),
                default => null,
            };
        }

        if ($dateFrom) {
            $query->whereDate('created_at', '>=', $dateFrom);
        }
        if ($dateTo) {
            $query->whereDate('created_at', '<=', $dateTo);
        }

        if ($status = $request->input('status')) {
            $query->where('status', $status);
        }

        if ($category = $request->input('category')) {
            $query->whereHas('resident', function ($r) use ($category) {
                $r->where('category', $category);
            });
        }

        $statsQuery = clone $query;

        $requests = $query
            ->orderByRaw("CASE WHEN status IN ('pending', 'reviewing') THEN 0 ELSE 1 END")
            ->orderByRaw("CASE WHEN status IN ('pending', 'reviewing') THEN COALESCE(queue_position, 999999999) ELSE 999999999 END")
            ->orderBy('created_at', 'desc')
            ->paginate(15)
            ->withQueryString();

        $stats = [
            'pending' => (clone $statsQuery)->where('status', 'pending')->count(),
            'reviewing' => (clone $statsQuery)->where('status', 'reviewing')->count(),
            'approved' => (clone $statsQuery)->where('status', 'approved')->count(),
            'completed' => (clone $statsQuery)->where('status', 'completed')->count(),
        ];

        return view('personnel.requests', compact('requests', 'stats'));
    }

    public function show($id)
    {
        $request = DocumentRequest::with(['resident.user', 'resident.idVerifications', 'documentType', 'purpose', 'documents', 'processedBy'])
            ->findOrFail($id);

        return view('personnel.request-show', compact('request'));
    }

    public function review($id)
    {
        $documentRequest = DocumentRequest::findOrFail($id);
        $documentRequest->update([
            'status' => 'reviewing',
            'processed_by' => Auth::id(),
            'processing_started_at' => now(),
        ]);

        AuditLog::create([
            'user_id' => Auth::id(),
            'action' => 'reviewed',
            'auditable_type' => DocumentRequest::class,
            'auditable_id' => $documentRequest->id,
            'description' => 'Started reviewing document request '.$documentRequest->queue_number,
        ]);

        return back()->with('success', 'Request is now under review.');
    }

    public function approve($id)
    {
        $documentRequest = DocumentRequest::findOrFail($id);

        DB::transaction(function () use ($documentRequest) {
            $documentRequest->update([
                'status' => 'approved',
                'processed_by' => Auth::id(),
            ]);

            $documentRequest->resident->idVerifications()
                ->where('is_verified', false)
                ->update([
                    'is_verified' => true,
                    'verified_at' => now(),
                    'verified_by' => Auth::id(),
                ]);

            AuditLog::create([
                'user_id' => Auth::id(),
                'action' => 'approved',
                'auditable_type' => DocumentRequest::class,
                'auditable_id' => $documentRequest->id,
                'description' => 'Approved document request '.$documentRequest->queue_number,
            ]);

            $this->wFQService->recalculateQueue();
        });

        return back()->with('success', 'Request approved successfully.');
    }

    public function reject(Request $request, $id)
    {
        $validated = $request->validate([
            'rejection_reason' => 'required|string|max:500',
        ]);

        $documentRequest = DocumentRequest::findOrFail($id);
        $documentRequest->update([
            'status' => 'rejected',
            'rejection_reason' => $validated['rejection_reason'],
            'processed_by' => Auth::id(),
        ]);

        AuditLog::create([
            'user_id' => Auth::id(),
            'action' => 'rejected',
            'auditable_type' => DocumentRequest::class,
            'auditable_id' => $documentRequest->id,
            'description' => 'Rejected document request '.$documentRequest->queue_number.': '.$validated['rejection_reason'],
        ]);

        $this->wFQService->recalculateQueue();

        return back()->with('success', 'Request rejected.');
    }

    public function complete($id)
    {
        $documentRequest = DocumentRequest::findOrFail($id);
        $documentRequest->update([
            'status' => 'completed',
            'completed_at' => now(),
        ]);

        AuditLog::create([
            'user_id' => Auth::id(),
            'action' => 'completed',
            'auditable_type' => DocumentRequest::class,
            'auditable_id' => $documentRequest->id,
            'description' => 'Completed document request '.$documentRequest->queue_number,
        ]);

        $this->wFQService->recalculateQueue();

        return back()->with('success', 'Request marked as completed.');
    }
}
