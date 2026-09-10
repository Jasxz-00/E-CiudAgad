<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AuditLog;
use App\Models\IdVerification;
use App\Notifications\IdVerificationStatusNotification;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class VerificationController extends Controller
{
    public function index(Request $request)
    {
        $status = $request->input('status', 'pending');

        $verifications = IdVerification::with(['resident.user'])
            ->when($status !== 'all', fn ($q) => $q->where('status', $status))
            ->orderByRaw("CASE status WHEN 'pending' THEN 0 WHEN 'rejected' THEN 1 ELSE 2 END")
            ->orderByDesc('created_at')
            ->paginate(15)
            ->withQueryString();

        $counts = [
            'pending' => IdVerification::where('status', 'pending')->count(),
            'verified' => IdVerification::where('status', 'verified')->count(),
            'rejected' => IdVerification::where('status', 'rejected')->count(),
        ];

        return view('admin.verifications.index', compact('verifications', 'counts', 'status'));
    }

    public function show($id)
    {
        $verification = IdVerification::with(['resident.user', 'verifiedBy'])->findOrFail($id);

        return view('admin.verifications.show', compact('verification'));
    }

    public function verify($id)
    {
        $verification = IdVerification::findOrFail($id);

        if ($verification->status === 'verified') {
            return back()->with('error', 'This ID is already verified.');
        }

        $verification->update([
            'status' => 'verified',
            'is_verified' => true,
            'verified_at' => now(),
            'admin_reviewed_at' => now(),
            'verified_by' => Auth::id(),
            'rejected_reason' => null,
        ]);

        AuditLog::create([
            'user_id' => Auth::id(),
            'action' => 'id_verified',
            'subject_type' => IdVerification::class,
            'subject_id' => $verification->id,
            'description' => 'Verified ID ('.$verification->id_type.') of resident #'.$verification->resident_id,
        ]);

        $verification->resident->user?->notify(new IdVerificationStatusNotification(
            $verification,
            'ID Verified',
            'Your submitted ID has been verified. You can now file document requests.'
        ));

        return back()->with('success', 'ID verification approved.');
    }

    public function reject(Request $request, $id)
    {
        $validated = $request->validate([
            'rejection_reason' => 'required|string|max:500',
        ]);

        $verification = IdVerification::findOrFail($id);

        if ($verification->status !== 'pending') {
            return back()->with('error', 'Only pending verifications can be rejected.');
        }

        $verification->update([
            'status' => 'rejected',
            'is_verified' => false,
            'admin_reviewed_at' => now(),
            'rejected_reason' => $validated['rejection_reason'],
        ]);

        AuditLog::create([
            'user_id' => Auth::id(),
            'action' => 'id_rejected',
            'subject_type' => IdVerification::class,
            'subject_id' => $verification->id,
            'description' => 'Rejected ID ('.$verification->id_type.') of resident #'.$verification->resident_id.': '.$validated['rejection_reason'],
        ]);

        $verification->resident->user?->notify(new IdVerificationStatusNotification(
            $verification,
            'ID Verification Rejected',
            'Your submitted ID was rejected. Reason: '.$validated['rejection_reason'].' Please resubmit a valid ID.'
        ));

        return back()->with('success', 'ID verification rejected.');
    }
}