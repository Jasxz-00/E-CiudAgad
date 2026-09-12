<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AuditLog;
use App\Models\ProfileChangeRequest;
use App\Models\Resident;
use App\Notifications\ProfileChangeNotification;
use App\Services\WFQService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class ProfileChangeController extends Controller
{
    public function index(Request $request)
    {
        $status = $request->input('status', 'pending');

        $changes = ProfileChangeRequest::with(['resident.user', 'reviewedBy'])
            ->when($status !== 'all', fn ($q) => $q->where('status', $status))
            ->orderByRaw("CASE status WHEN 'pending' THEN 0 WHEN 'approved' THEN 1 ELSE 2 END")
            ->orderByDesc('created_at')
            ->paginate(15)
            ->withQueryString();

        $counts = [
            'pending' => ProfileChangeRequest::where('status', 'pending')->count(),
            'approved' => ProfileChangeRequest::where('status', 'approved')->count(),
            'rejected' => ProfileChangeRequest::where('status', 'rejected')->count(),
        ];

        return view('admin.profile-changes.index', compact('changes', 'counts', 'status'));
    }

    public function approve($id)
    {
        $change = ProfileChangeRequest::with('resident', 'user')->findOrFail($id);

        if ($change->status !== ProfileChangeRequest::STATUS_PENDING) {
            return back()->with('error', 'Only pending profile changes can be approved.');
        }

        DB::transaction(function () use ($change) {
            $oldCategory = $change->resident->category ?? null;
            $residentData = $change->new_data['resident'] ?? [];

            $change->resident->update($residentData);
            if (! empty($change->new_data['email'])) {
                $change->user->update(['email' => $change->new_data['email']]);
            }

            $newCategory = $residentData['category'] ?? $oldCategory;
            $oldValues = ['category' => $oldCategory];

            if ($newCategory !== $oldCategory) {
                $change->resident->update(['category' => $newCategory]);

                AuditLog::create([
                    'user_id' => Auth::id(),
                    'action' => 'resident_category_updated',
                    'subject_type' => Resident::class,
                    'subject_id' => $change->resident_id,
                    'description' => 'Resident category changed from '.($oldCategory ?: 'none').' to '.$newCategory.' via profile change approval.',
                    'old_values' => $oldValues,
                    'new_values' => ['category' => $newCategory],
                ]);

                $wfq = new WFQService;
                $change->resident->documentRequests()
                    ->whereIn('status', ['pending', 'reviewing'])
                    ->get()
                    ->each(fn ($request) => $wfq->enqueue($request));
            }

            $change->update([
                'status' => ProfileChangeRequest::STATUS_APPROVED,
                'reviewed_by' => Auth::id(),
                'reviewed_at' => now(),
            ]);

            AuditLog::create([
                'user_id' => Auth::id(),
                'action' => 'profile_change_approved',
                'subject_type' => ProfileChangeRequest::class,
                'subject_id' => $change->id,
                'description' => 'Approved profile change for resident #'.$change->resident_id.': '.implode(', ', $change->changed_fields),
            ]);
        });

        $change->user->notify(new ProfileChangeNotification(
            $change,
            'Profile Change Approved',
            'Your profile change ('.implode(', ', $change->changed_fields).') has been approved by the admin.'
        ));

        return back()->with('success', 'Profile change approved and applied.');
    }

    public function reject(Request $request, $id)
    {
        $validated = $request->validate([
            'rejection_reason' => 'required|string|max:500',
        ]);

        $change = ProfileChangeRequest::with('user')->findOrFail($id);

        if ($change->status !== ProfileChangeRequest::STATUS_PENDING) {
            return back()->with('error', 'Only pending profile changes can be rejected.');
        }

        $change->update([
            'status' => ProfileChangeRequest::STATUS_REJECTED,
            'rejection_reason' => $validated['rejection_reason'],
            'reviewed_by' => Auth::id(),
            'reviewed_at' => now(),
        ]);

        AuditLog::create([
            'user_id' => Auth::id(),
            'action' => 'profile_change_rejected',
            'subject_type' => ProfileChangeRequest::class,
            'subject_id' => $change->id,
            'description' => 'Rejected profile change for resident #'.$change->resident_id.': '.$validated['rejection_reason'],
        ]);

        $change->user->notify(new ProfileChangeNotification(
            $change,
            'Profile Change Rejected',
            'Your profile change ('.implode(', ', $change->changed_fields).') was rejected. Reason: '.$validated['rejection_reason']
        ));

        return back()->with('success', 'Profile change rejected.');
    }
}