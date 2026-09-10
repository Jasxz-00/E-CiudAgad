<?php

namespace App\Http\Controllers\Queue;

use App\Http\Controllers\Controller;
use App\Models\DocumentRequest;
use Illuminate\Http\Request;

class QueueMonitorController extends Controller
{
    public function index()
    {
        $nowServing = DocumentRequest::where('status', 'pending')
            ->whereNotNull('virtual_finish_time')
            ->orderBy('virtual_finish_time', 'asc')
            ->orderBy('created_at', 'asc')
            ->first();

        $nextUp = DocumentRequest::where('status', 'pending')
            ->whereNotNull('virtual_finish_time')
            ->when($nowServing, fn ($q) => $q->where('id', '!=', $nowServing->id))
            ->orderBy('virtual_finish_time', 'asc')
            ->orderBy('created_at', 'asc')
            ->limit(3)
            ->get();

        $remainingCount = DocumentRequest::where('status', 'pending')->count();

        return view('queue.monitor', compact('nowServing', 'nextUp', 'remainingCount'));
    }

    public function personnel()
    {
        $nowServing = DocumentRequest::where('status', 'pending')
            ->whereNotNull('virtual_finish_time')
            ->orderBy('virtual_finish_time', 'asc')
            ->orderBy('created_at', 'asc')
            ->first();

        $requests = DocumentRequest::whereIn('status', ['pending', 'reviewing'])
            ->whereNotNull('virtual_finish_time')
            ->orderBy('virtual_finish_time', 'asc')
            ->orderBy('created_at', 'asc')
            ->get();

        return view('queue.personnel', compact('requests', 'nowServing'));
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

    public function process(Request $request, $controlNumber)
    {
        $docRequest = DocumentRequest::where('control_number', $controlNumber)->firstOrFail();

        if (! in_array($docRequest->status, ['pending', 'reviewing'])) {
            return back()->withErrors(['queue' => __('registration.request_not_available')]);
        }

        $docRequest->update([
            'status' => 'reviewing',
            'processing_started_at' => now(),
            'processed_by' => $request->user()->id,
        ]);

        return back()->with('success', __('registration.request_started_review'));
    }
}
