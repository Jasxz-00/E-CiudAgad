<?php

namespace App\Http\Controllers\Queue;

use App\Http\Controllers\Controller;
use App\Models\DocumentRequest;
use App\Services\QueueScheduleService;
use Illuminate\Http\Request;

class QueueMonitorController extends Controller
{
    public function __construct(protected QueueScheduleService $queueScheduleService)
    {
    }

    public function index()
    {
        $today = now()->toDateString();

        $nowServing = DocumentRequest::where('status', 'pending')
            ->whereDate('service_date', $today)
            ->whereNotNull('virtual_finish_time')
            ->orderBy('virtual_finish_time', 'asc')
            ->orderBy('created_at', 'asc')
            ->orderBy('id', 'asc')
            ->first();

        $nextUp = DocumentRequest::where('status', 'pending')
            ->whereDate('service_date', $today)
            ->whereNotNull('virtual_finish_time')
            ->when($nowServing, fn ($q) => $q->where('id', '!=', $nowServing->id))
            ->orderBy('virtual_finish_time', 'asc')
            ->orderBy('created_at', 'asc')
            ->orderBy('id', 'asc')
            ->limit(3)
            ->get();

        $remainingCount = DocumentRequest::where('status', 'pending')
            ->whereDate('service_date', $today)
            ->count();

        $queueStatus = $this->queueScheduleService->queueStatus();

        return view('queue.monitor', compact('nowServing', 'nextUp', 'remainingCount', 'queueStatus'));
    }

    public function personnel()
    {
        $today = now()->toDateString();

        $nowServing = DocumentRequest::where('status', 'pending')
            ->whereDate('service_date', $today)
            ->whereNotNull('virtual_finish_time')
            ->orderBy('virtual_finish_time', 'asc')
            ->orderBy('created_at', 'asc')
            ->orderBy('id', 'asc')
            ->first();

        $requests = DocumentRequest::whereIn('status', ['pending', 'reviewing', 'on_hold'])
            ->whereDate('service_date', $today)
            ->orderByRaw('CASE WHEN virtual_finish_time IS NULL THEN 1 ELSE 0 END')
            ->orderBy('virtual_finish_time', 'asc')
            ->orderBy('created_at', 'asc')
            ->orderBy('id', 'asc')
            ->get();

        $futureScheduled = DocumentRequest::whereIn('status', ['pending'])
            ->whereDate('service_date', '>', $today)
            ->orderBy('service_date', 'asc')
            ->orderBy('virtual_finish_time', 'asc')
            ->get();

        $queueStatus = $this->queueScheduleService->queueStatus();

        return view('queue.personnel', compact('requests', 'nowServing', 'futureScheduled', 'queueStatus'));
    }

    public function publicStatus()
    {
        $today = now('Asia/Manila')->toDateString();

        $nowServing = DocumentRequest::where('status', 'pending')
            ->whereDate('service_date', $today)
            ->whereNotNull('virtual_finish_time')
            ->orderBy('virtual_finish_time', 'asc')
            ->orderBy('created_at', 'asc')
            ->orderBy('id', 'asc')
            ->first();

        // Privacy-safe: queue number, document type, and position only.
        // Never expose resident names, weights, purposes, or personal details.
        $nextUp = DocumentRequest::where('status', 'pending')
            ->whereDate('service_date', $today)
            ->whereNotNull('virtual_finish_time')
            ->when($nowServing, fn ($q) => $q->where('id', '!=', $nowServing->id))
            ->orderBy('virtual_finish_time', 'asc')
            ->orderBy('created_at', 'asc')
            ->orderBy('id', 'asc')
            ->limit(5)
            ->get(['queue_number', 'document_type_id', 'queue_position']);

        return response()->json([
            'ok' => true,
            'service_date' => $today,
            'now_serving' => $nowServing ? [
                'queue_number' => $nowServing->queue_number,
                'document' => $nowServing->documentType?->name,
                'window' => $nowServing->queue_position ?? 1,
            ] : null,
            'next_up' => $nextUp->map(fn ($req) => [
                'queue_number' => $req->queue_number,
                'document' => $req->documentType?->name,
            ])->all(),
            'remaining_count' => DocumentRequest::where('status', 'pending')
                ->whereDate('service_date', $today)
                ->count(),
            'last_updated' => now('Asia/Manila')->toDateTimeString(),
            'timezone' => 'Asia/Manila',
        ]);
    }

    public function statusJson(Request $request)
    {
        $today = now()->toDateString();

        $nowServing = DocumentRequest::where('status', 'pending')
            ->whereDate('service_date', $today)
            ->whereNotNull('virtual_finish_time')
            ->orderBy('virtual_finish_time', 'asc')
            ->orderBy('created_at', 'asc')
            ->orderBy('id', 'asc')
            ->first();

        $nextUp = DocumentRequest::where('status', 'pending')
            ->whereDate('service_date', $today)
            ->whereNotNull('virtual_finish_time')
            ->when($nowServing, fn ($q) => $q->where('id', '!=', $nowServing->id))
            ->orderBy('virtual_finish_time', 'asc')
            ->orderBy('created_at', 'asc')
            ->orderBy('id', 'asc')
            ->limit(3)
            ->get(['queue_number', 'document_type_id']);

        $queueStatus = $this->queueScheduleService->queueStatus();

        return response()->json([
            'ok' => true,
            'service_date' => $today,
            'now_serving' => $nowServing ? [
                'queue_number' => $nowServing->queue_number,
                'document' => $nowServing->documentType?->name,
                'window' => $nowServing->queue_position ?? 1,
            ] : null,
            'next_up' => $nextUp->map(fn ($req) => [
                'queue_number' => $req->queue_number,
                'document' => $req->documentType?->name,
            ])->all(),
            'remaining_count' => DocumentRequest::where('status', 'pending')
                ->whereDate('service_date', $today)
                ->count(),
            'queue' => $queueStatus,
            'last_updated' => now('Asia/Manila')->toDateTimeString(),
        ]);
    }

    public function show($controlNumber)
    {
        $request = DocumentRequest::where('control_number', $controlNumber)->firstOrFail();

        $user = auth()->user();
        if (! in_array($user->role, ['admin', 'personnel']) && (int) $user->resident?->id !== (int) $request->resident_id) {
            abort(403);
        }

        return view('resident.confirmation', ['documentRequest' => $request]);
    }
    public function getOrderedActiveQueue()
    {
        return DocumentRequest::query()
            ->whereIn('status', [
                'approved',
                'queued',
            ])
            ->whereNotNull('virtual_finish_time')
            ->orderBy('virtual_finish_time', 'asc')
            ->orderBy('eligible_at', 'asc')
            ->orderBy('id', 'asc')
            ->get();
    }
}