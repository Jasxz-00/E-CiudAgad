<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\DuplicateClaim;
use App\Models\Resident;
use App\Models\User;
use App\Services\AgeService;
use App\Services\CredentialService;
use App\Services\WFQService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class DuplicateClaimController extends Controller
{
    protected CredentialService $credentialService;

    protected WFQService $wFQService;

    public function __construct()
    {
        $this->credentialService = new CredentialService;
        $this->wFQService = new WFQService;
    }

    public function index()
    {
        $claims = DuplicateClaim::with(['matchedResident', 'reviewer'])
            ->orderBy('created_at', 'desc')
            ->get();

        return view('admin.duplicate-claims.index', compact('claims'));
    }

    public function show($id)
    {
        $claim = DuplicateClaim::with(['matchedResident.user', 'reviewer'])->findOrFail($id);

        return view('admin.duplicate-claims.show', compact('claim'));
    }

    public function approve($id, Request $request)
    {
        $claim = DuplicateClaim::with('matchedResident')->findOrFail($id);

        if ($claim->status !== 'pending') {
            return back()->with('error', 'This claim has already been resolved.');
        }

        $data = $claim->registration_data;

        DB::beginTransaction();
        try {
            $trackingNumber = $this->credentialService->generateTrackingNumber();
            $pin = $this->credentialService->generatePin();

            $email = $data['email'] ?? ('resident_'.$trackingNumber.'@example.com');

            $user = User::create([
                'email' => $email,
                'password' => Hash::make($pin),
                'pin' => Hash::make($pin),
                'tracking_number' => $trackingNumber,
                'role' => 'resident',
                'is_active' => true,
            ]);

            $user->assignRole('resident');

            $birthdate = sprintf('%04d-%02d-%02d', $data['birthdate_year'], $data['birthdate_month'], $data['birthdate_day']);

            $ageService = new AgeService;
            $age = $ageService->computeAge(
                $data['birthdate_year'],
                $data['birthdate_month'],
                $data['birthdate_day']
            );

            $category = 'regular';
            if ($ageService->isSeniorCitizen($age)) {
                $category = 'senior';
            }

            Resident::create([
                'user_id' => $user->id,
                'first_name' => strtoupper($data['first_name']),
                'last_name' => strtoupper($data['last_name']),
                'middle_name' => isset($data['middle_name']) ? strtoupper($data['middle_name']) : null,
                'suffix' => isset($data['suffix']) ? strtoupper($data['suffix']) : null,
                'birthdate' => $birthdate,
                'age' => $age,
                'gender' => $data['gender'],
                'civil_status' => $data['civil_status'] ?? null,
                'nationality' => isset($data['nationality']) ? strtoupper($data['nationality']) : 'FILIPINO',
                'occupation' => isset($data['occupation']) ? strtoupper($data['occupation']) : null,
                'building_no' => isset($data['building_no']) ? strtoupper($data['building_no']) : null,
                'unit_no' => isset($data['unit_no']) ? strtoupper($data['unit_no']) : null,
                'street' => isset($data['street']) ? strtoupper($data['street']) : null,
                'purok' => isset($data['purok']) ? strtoupper($data['purok']) : null,
                'contact_number' => $data['contact_number'],
                'emergency_contact' => $data['emergency_contact'],
                'category' => $category,
            ]);

            $claim->update([
                'status' => 'approved',
                'reviewed_by' => Auth::id(),
                'reviewed_at' => now(),
                'staff_notes' => $request->input('staff_notes'),
            ]);

            DB::commit();

            return redirect()->route('admin.duplicate-claims.index')
                ->with('success', "Registration approved. Tracking #: {$trackingNumber}");

        } catch (\Exception $e) {
            DB::rollBack();

            return back()->with('error', 'Failed to approve claim: '.$e->getMessage());
        }
    }

    public function dismiss($id, Request $request)
    {
        $claim = DuplicateClaim::findOrFail($id);

        if ($claim->status !== 'pending') {
            return back()->with('error', 'This claim has already been resolved.');
        }

        $claim->update([
            'status' => 'dismissed',
            'reviewed_by' => Auth::id(),
            'reviewed_at' => now(),
            'staff_notes' => $request->input('staff_notes'),
        ]);

        return redirect()->route('admin.duplicate-claims.index')
            ->with('success', 'Duplicate claim dismissed. Resident advised to use existing account.');
    }
}
