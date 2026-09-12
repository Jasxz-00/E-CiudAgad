<?php

namespace App\Services;

use App\Models\AuditLog;
use App\Models\DocumentRequest;
use App\Models\DocumentType;
use App\Models\RequestPurpose;
use App\Models\Resident;
use Illuminate\Database\UniqueConstraintViolationException;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class WFQService
{
    const SERVICE_LENGTH = 1.0;

    public function calculateWeight(Resident $resident, ?RequestPurpose $purpose): float
    {
        $residentWeight = (float) config(
            "queue.weights.resident.{$resident->category}",
            config('queue.weights.default_resident_weight', 1)
        );

        $purposeWeight = (float) config('queue.weights.default_purpose_weight', 1);
        if ($purpose && $purpose->code) {
            $purposeWeight = (float) config("queue.weights.purpose.{$purpose->code}", $purposeWeight);
        }

        return $residentWeight + $purposeWeight;
    }

    public function residentWeight(Resident $resident): float
    {
        return (float) config(
            "queue.weights.resident.{$resident->category}",
            config('queue.weights.default_resident_weight', 1)
        );
    }

    public function purposeWeight(?RequestPurpose $purpose): float
    {
        $purposeWeight = (float) config('queue.weights.default_purpose_weight', 1);

        if ($purpose && $purpose->code) {
            $purposeWeight = (float) config("queue.weights.purpose.{$purpose->code}", $purposeWeight);
        }

        return $purposeWeight;
    }

    public function calculateVirtualFinishTime(float $totalWeight, ?string $serviceDate = null): float
    {
        $query = DocumentRequest::whereIn('status', ['pending', 'reviewing']);

        if ($serviceDate) {
            $query->whereDate('service_date', $serviceDate);
        }

        $lastVft = $query->max('virtual_finish_time') ?? 0;

        return (float) $lastVft + (self::SERVICE_LENGTH / max($totalWeight, 0.0001));
    }

    public function enqueue(DocumentRequest $request): void
    {
        // Concurrency safety: wrap in transaction with row-level locks to prevent race conditions
        // when multiple residents submit requests for the same service_date simultaneously.
        DB::transaction(function () use ($request) {
            $serviceDate = $request->service_date ?? now()->toDateString();

            // Lock all existing pending/reviewing requests for the same service_date
            // to prevent concurrent enqueue operations from reading stale VFT values.
            DocumentRequest::where('service_date', $serviceDate)
                ->whereIn('status', ['pending', 'reviewing'])
                ->lockForUpdate()
                ->get();

            $resident = $request->resident;
            $purpose = $request->purpose;

            $residentWeight = $this->residentWeight($resident);
            $purposeWeight = $this->purposeWeight($purpose);
            $totalWeight = $residentWeight + $purposeWeight;

            $vft = $this->calculateVirtualFinishTime($totalWeight, $serviceDate);

            $request->update([
                'resident_weight' => $residentWeight,
                'purpose_weight' => $purposeWeight,
                'total_weight' => $totalWeight,
                'virtual_finish_time' => $vft,
            ]);

            AuditLog::create([
                'user_id' => Auth::id() ?? null,
                'action' => 'queue_enqueue',
                'description' => "Request #{$request->id} enqueued with virtual_finish_time={$vft} for service_date={$serviceDate}",
                'model_type' => DocumentRequest::class,
                'model_id' => $request->id,
                'auditable_type' => DocumentRequest::class,
                'auditable_id' => $request->id,
                'old_values' => null,
                'new_values' => ['virtual_finish_time' => $vft, 'resident_weight' => $residentWeight, 'purpose_weight' => $purposeWeight, 'total_weight' => $totalWeight],
            ]);

            $this->recalculateQueue();
        });
    }

    public function recalculateQueue(): void
    {
        DB::transaction(function () {
            $dates = DocumentRequest::whereIn('status', ['pending', 'reviewing'])
                ->distinct()
                ->orderBy('service_date', 'asc')
                ->pluck('service_date');

            foreach ($dates as $date) {
                $requests = DocumentRequest::whereIn('status', ['pending', 'reviewing'])
                    ->whereDate('service_date', $date)
                    ->lockForUpdate()
                    ->orderBy('virtual_finish_time', 'asc')
                    ->orderBy('created_at', 'asc')
                    ->orderBy('id', 'asc')
                    ->get();

                $position = 1;
                foreach ($requests as $req) {
                    if ((int) $req->queue_position !== $position) {
                        $req->update(['queue_position' => $position]);
                    }
                    $position++;
                }
            }

            AuditLog::create([
                'user_id' => Auth::id() ?? null,
                'action' => 'queue_recalculate',
                'description' => 'Queue positions recalculated across all active service dates',
                'model_type' => DocumentRequest::class,
                'model_id' => null,
                'auditable_type' => DocumentRequest::class,
                'auditable_id' => 0,
                'old_values' => null,
                'new_values' => null,
            ]);
        });
    }

    public function lock(DocumentRequest $request): ?object
    {
        return DB::table('document_requests')
            ->where('id', $request->id)
            ->lockForUpdate()
            ->first();
    }

    public function removeFromActiveQueue(DocumentRequest $request): void
    {
        $oldVft = $request->virtual_finish_time;
        $request->update(['virtual_finish_time' => 0.000000]);
        $this->recalculateQueue();

        AuditLog::create([
            'user_id' => Auth::id() ?? null,
            'action' => 'queue_remove',
            'description' => "Request #{$request->id} removed from active queue (virtual_finish_time was {$oldVft})",
            'model_type' => DocumentRequest::class,
            'model_id' => $request->id,
            'auditable_type' => DocumentRequest::class,
            'auditable_id' => $request->id,
            'old_values' => ['virtual_finish_time' => $oldVft],
            'new_values' => ['virtual_finish_time' => 0.000000],
        ]);
    }

    public function generateQueueNumber(): string
    {
        $prefix = 'Q-';
        $maxSuffix = 0;

        foreach (DocumentRequest::pluck('queue_number') as $queueNumber) {
            if (preg_match('/-(\d+)$/', $queueNumber, $matches)) {
                $maxSuffix = max($maxSuffix, (int) $matches[1]);
            }
        }

        return $prefix.str_pad($maxSuffix + 1, 4, '0', STR_PAD_LEFT);
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

    public function isQueueNumberCollision(\Throwable $e): bool
    {
        return str_contains($e->getMessage(), 'document_requests_queue_number_unique')
            || (str_contains($e->getMessage(), 'queue_number') && $e instanceof UniqueConstraintViolationException);
    }
}
