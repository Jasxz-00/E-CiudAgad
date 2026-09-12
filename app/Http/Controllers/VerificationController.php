<?php

namespace App\Http\Controllers;

use App\Models\DocumentRequest;
use Illuminate\Http\Request;

class VerificationController extends Controller
{
    public function show(Request $request, string $token)
    {
        $documentRequest = DocumentRequest::where('verification_token', $token)
            ->with(['documentType', 'purpose'])
            ->firstOrFail();

        $user = $request->user();

        if ($user && ! in_array($user->role, ['admin', 'personnel']) && (int) $user?->resident?->id !== (int) $documentRequest->resident_id) {
            abort(403);
        }

        return view('resident.verify', compact('documentRequest'));
    }
}