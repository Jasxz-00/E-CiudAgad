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
        $extension = $file->guessExtension() ?: 'bin';

        return $file->storeAs('id_verifications', $prefix.time().'_'.uniqid().'.'.$extension, 'private');
    }

    public function uploadRequestDocument(UploadedFile $file): string
    {
        $extension = $file->guessExtension() ?: 'bin';

        return $file->storeAs('request_documents', 'doc_'.time().'_'.uniqid().'.'.$extension, 'private');
    }

    public function uploadProofOfDisability(UploadedFile $file): string
    {
        $extension = $file->guessExtension() ?: 'bin';

        return $file->storeAs('disability_proofs', 'disability_'.time().'_'.uniqid().'.'.$extension, 'private');
    }

    public function uploadStatusVerification(UploadedFile $file): string
    {
        $extension = $file->guessExtension() ?: 'bin';

        return $file->storeAs('status_verifications', 'status_verif_'.time().'_'.uniqid().'.'.$extension, 'private');
    }

    public function deleteFile(?string $path): void
    {
        if ($path && Storage::disk('private')->exists($path)) {
            Storage::disk('private')->delete($path);
        }
    }
}
