<?php

namespace App\Services;

use App\Models\DocumentRequest;
use App\Models\DocumentType;
use App\Models\RequestPurpose;
use App\Models\Resident;
use App\Models\WFQConfiguration;
use Illuminate\Database\UniqueConstraintViolationException;
use Illuminate\Support\Facades\DB;

class WFQService
{
    public function calculateWeight(Resident $resident, DocumentType $documentType, ?RequestPurpose $purpose): float
    {
        $categoryWeight = $this->getActiveWeight('category_weight', $resident->category);

        $complexityWeight = $documentType->complexity_weight > 0
            ? (float) $documentType->complexity_weight
            : $this->getActiveWeight('complexity_weight', $documentType->complexity);

        $purposeWeight = $purpose && $purpose->priority_weight > 0
            ? (float) $purpose->priority_weight
            : $this->getActiveWeight('purpose_weight', $purpose?->code ?? '');

        return (float) $categoryWeight * $complexityWeight * $purposeWeight;
    }

    public function calculateVirtualFinishTime(float $totalWeight): float
    {
        $lastVft = DocumentRequest::whereIn('status', ['pending', 'reviewing'])
            ->max('virtual_finish_time') ?? 0;

        return (float) $lastVft + (1 / max($totalWeight, 0.0001));
    }

    public function enqueue(DocumentRequest $request): void
    {
        $resident = $request->resident;
        $documentType = $request->documentType;
        $purpose = $request->purpose;

        $totalWeight = $this->calculateWeight($resident, $documentType, $purpose);
        $vft = $this->calculateVirtualFinishTime($totalWeight);

        $request->update([
            'total_weight' => $totalWeight,
            'virtual_finish_time' => $vft,
        ]);

        $this->recalculateQueue();
    }

    public function recalculateQueue(): void
    {
        $requests = DocumentRequest::whereIn('status', ['pending', 'reviewing'])
            ->orderBy('virtual_finish_time', 'asc')
            ->orderBy('created_at', 'asc')
            ->get();

        DB::transaction(function () use ($requests) {
            $position = 1;
            foreach ($requests as $req) {
                $req->update(['queue_position' => $position++]);
            }
        });
    }

    private function getActiveWeight(string $type, string $key): float
    {
        $config = WFQConfiguration::where('config_type', $type)
            ->where('config_key', $key)
            ->where('is_active', true)
            ->first();

        return $config ? (float) $config->weight : 1.0;
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

    public function isQueueNumberCollision(\Throwable $e): bool
    {
        return str_contains($e->getMessage(), 'document_requests_queue_number_unique')
            || (str_contains($e->getMessage(), 'queue_number') && $e instanceof UniqueConstraintViolationException);
    }
}
