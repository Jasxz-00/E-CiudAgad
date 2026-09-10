<?php

namespace App\Http\Controllers\Personnel;

use App\Http\Controllers\Controller;
use App\Models\AuditLog;
use App\Models\DocumentRequest;
use App\Models\IssuedDocument;
use App\Services\DocumentGenerationService;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;

class DocumentController extends Controller
{
    public function preview($requestId)
    {
        $documentRequest = DocumentRequest::with(['documentType', 'purpose', 'resident', 'issuedDocument'])
            ->findOrFail($requestId);

        if (! in_array($documentRequest->status, ['approved', 'completed', 'released'])) {
            return back()->with('error', 'Document preview is only available for approved or completed requests.');
        }

        if ($documentRequest->issuedDocument) {
            $svg = Storage::disk('private')->get($documentRequest->issuedDocument->svg_path);
        } else {
            $svg = (new DocumentGenerationService)->preview($documentRequest);
        }

        $issued = $documentRequest->issuedDocument;

        return view('personnel.documents.preview', compact('documentRequest', 'svg', 'issued'));
    }

    public function print($issuedId)
    {
        $issued = IssuedDocument::findOrFail($issuedId);

        (new DocumentGenerationService)->markPrinted($issued, Auth::id());

        AuditLog::create([
            'user_id' => Auth::id(),
            'action' => 'printed_document',
            'subject_type' => IssuedDocument::class,
            'subject_id' => $issued->id,
            'description' => 'Printed document '.$issued->control_number.' (print count: '.$issued->print_count.')',
        ]);

        return response()->json(['ok' => true]);
    }
}