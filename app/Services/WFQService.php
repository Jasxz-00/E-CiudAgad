<?php

namespace App\Services;

use App\Models\DocumentRequest;
use App\Models\DocumentType;
use App\Models\RequestPurpose;
use App\Models\Resident;
use App\Models\WFQConfiguration;
use Illuminate\Support\Facades\DB;

class WFQService
{
    public function calculateWeight(Resident $resident, DocumentType $documentType, RequestPurpose $purpose): float
    {
        $categoryWeight = $this->getActiveWeight('category_weight', $resident->category);
        $complexityWeight = $this->getActiveWeight('complexity_weight', $documentType->complexity);
        $purposeWeight = $this->getActiveWeight('purpose_weight', $purpose->code);

        return (float) $categoryWeight * (float) $complexityWeight * (float) $purposeWeight;
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
        $lastRequest = DocumentRequest::whereDate('created_at', today())
            ->orderBy('id', 'desc')
            ->first();

        if ($lastRequest && preg_match('/-(\d+)$/', $lastRequest->queue_number, $matches)) {
            $nextNumber = (int) $matches[1] + 1;
        } else {
            $nextNumber = 1;
        }

        return $prefix.str_pad($nextNumber, 4, '0', STR_PAD_LEFT);
    }
}
