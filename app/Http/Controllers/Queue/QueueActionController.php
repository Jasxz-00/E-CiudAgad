<?php

namespace App\Http\Controllers\Queue;

use App\Http\Controllers\Controller;
use App\Models\AuditLog;
use App\Models\DocumentRequest;
use App\Services\QueueScheduleService;
use App\Services\WFQService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class QueueActionController extends Controller
{
    public function __construct(
        protected WFQService $wFQService,
        protected QueueScheduleService $queueScheduleService
    ) {
    }

    public function callNext(Request $request, string $controlNumber)
    {
        return $this->transition($request, $controlNumber, 'pending', 'reviewing', 'call_next');
    }

    public function hold(Request $request, string $controlNumber)
    {
        return $this->transition($request, $controlNumber, ['pending', 'reviewing'], 'on_hold', 'hold');
    }

    public function resume(Request $request, string $controlNumber)
    {
        $docRequest = DB::transaction(function () use ($request, $controlNumber) {
            $docRequest = DocumentRequest::where('control_number', $controlNumber)
                ->lockForUpdate()
                ->firstOrFail();

            if ($docRequest->status !== 'on_hold') {
                return null;
            }

            $this->applyTransition($request, $docRequest, 'pending', 'resume');

            return $docRequest;
        });

        if ($docRequest === null) {
            return back()->withErrors(['queue' => __('registration.request_not_available')]);
        }

        $this->wFQService->enqueue($docRequest);

        return back()->with('success', __('registration.request_resumed'));
    }

    public function skip(Request $request, string $controlNumber)
    {
        DB::transaction(function () use ($request, $controlNumber) {
            $docRequest = DocumentRequest::where('control_number', $controlNumber)
                ->lockForUpdate()
                ->firstOrFail();

            if (! in_array($docRequest->status, ['pending', 'reviewing', 'on_hold'])) {
                return;
            }

            $assignment = $this->queueScheduleService->assignServiceDate();

            $docRequest->update([
                'status' => 'pending',
                'service_date' => $assignment['service_date'],
                'scheduled_after_cutoff' => $assignment['scheduled_after_cutoff'],
                'virtual_finish_time' => 0.000000,
            ]);

            $this->logAction($request, $docRequest, 'skip', [
                'to_service_date' => $assignment['service_date'],
            ]);
        });

        $this->wFQService->recalculateQueue();

        return back()->with('success', __('registration.request_skipped'));
    }

    public function markReady(Request $request, string $controlNumber)
    {
        return $this->transition($request, $controlNumber, 'reviewing', 'ready_for_release', 'mark_ready');
    }

    public function release(Request $request, string $controlNumber)
    {
        return $this->transition($request, $controlNumber, 'ready_for_release', 'released', 'release');
    }

    protected function transition(Request $request, string $controlNumber, $from, string $to, string $action)
    {
        $docRequest = DB::transaction(function () use ($request, $controlNumber, $from, $to, $action) {
            $docRequest = DocumentRequest::where('control_number', $controlNumber)
                ->lockForUpdate()
                ->firstOrFail();

            $allowed = is_array($from) ? $from : [$from];
            if (! in_array($docRequest->status, $allowed)) {
                return null;
            }

            $this->applyTransition($request, $docRequest, $to, $action);

            return $docRequest;
        });

        if ($docRequest === null) {
            return back()->withErrors(['queue' => __('registration.request_not_available')]);
        }

        return back()->with('success', __('registration.action_applied'));
    }

    protected function applyTransition(Request $request, DocumentRequest $docRequest, string $to, string $action): void
    {
        $oldValues = ['status' => $docRequest->status];

        $data = ['status' => $to];

        if ($to === 'reviewing') {
            $data['processing_started_at'] = now();
            $data['processed_by'] = $request->user()->id;
        } elseif ($to === 'released') {
            $data['completed_at'] = now();
        } elseif ($to === 'pending') {
            $data['processed_by'] = null;
            $data['processing_started_at'] = null;
        }

        $docRequest->update($data);

        $this->wFQService->recalculateQueue();

        $this->logAction($request, $docRequest, $action, [
            'new_values' => ['status' => $to],
            'old_values' => $oldValues,
        ]);
    }

    protected function logAction(Request $request, DocumentRequest $docRequest, string $action, array $data = []): void
    {
        AuditLog::create(array_merge([
            'user_id' => $request->user()?->id,
            'action' => $action,
            'subject_type' => DocumentRequest::class,
            'subject_id' => $docRequest->id,
            'description' => $action.' on request '.$docRequest->queue_number,
        ], $data));
    }
}