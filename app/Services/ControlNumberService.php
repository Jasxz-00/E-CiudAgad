<?php

namespace App\Services;

use App\Models\DocumentRequest;
use SimpleSoftwareIO\QrCode\Facades\QrCode;

class ControlNumberService
{
    public function generateControlNumber(): string
    {
        $date = now()->format('Ymd');

        return 'REQ-'.$date.'-'.str_pad($this->todayCount() + 1, 4, '0', STR_PAD_LEFT);
    }

    public function generateQrCodePath(string $controlNumber): string
    {
        $path = 'qrcodes/'.$controlNumber.'.png';

        try {
            $fullPath = storage_path('app/private/'.$path);
            @mkdir(dirname($fullPath), 0755, true);
            QrCode::format('png')->size(300)->generate($controlNumber, $fullPath);

            return $path;
        } catch (\Throwable) {
            // QR library not available; path is still recorded so the
            // confirmation page can render the code inline.
            return $path;
        }
    }

    protected function todayCount(): int
    {
        $count = DocumentRequest::whereDate('created_at', today())->count();

        do {
            $candidate = 'REQ-'.now()->format('Ymd').'-'.str_pad($count + 1, 4, '0', STR_PAD_LEFT);
            $exists = DocumentRequest::where('control_number', $candidate)->exists();
            if (! $exists) {
                return $count;
            }
            $count++;
        } while (true);
    }
}