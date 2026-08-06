<?php

namespace App\Services;

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

class FileService
{
    public function uploadIdFile(UploadedFile $file): string
    {
        return $this->storeIdFile($file, 'id_');
    }

    public function uploadIdBack(UploadedFile $file): string
    {
        return $this->storeIdFile($file, 'id_back_');
    }

    protected function storeIdFile(UploadedFile $file, string $prefix): string
    {
        $filename = $prefix.time().'_'.uniqid().'.'.$file->getClientOriginalExtension();

        return $file->storeAs('id_verifications', $filename, 'private');
    }

    public function uploadRequestDocument(UploadedFile $file): string
    {
        $filename = 'doc_'.time().'_'.uniqid().'.'.$file->getClientOriginalExtension();

        return $file->storeAs('request_documents', $filename, 'private');
    }

    public function uploadProofOfDisability(UploadedFile $file): string
    {
        $filename = 'disability_'.time().'_'.uniqid().'.'.$file->getClientOriginalExtension();

        return $file->storeAs('disability_proofs', $filename, 'private');
    }

    public function uploadStatusVerification(UploadedFile $file): string
    {
        $filename = 'status_verif_'.time().'_'.uniqid().'.'.$file->getClientOriginalExtension();

        return $file->storeAs('status_verifications', $filename, 'private');
    }

    public function deleteFile(?string $path): void
    {
        if ($path && Storage::disk('private')->exists($path)) {
            Storage::disk('private')->delete($path);
        }
    }

    public function getFileUrl(string $path): ?string
    {
        if (Storage::disk('private')->exists($path)) {
            return Storage::disk('private')->url($path);
        }

        return null;
    }
}
