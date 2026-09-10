<?php

namespace App\Http\Controllers\Resident;

use App\Http\Controllers\Controller;
use App\Http\Requests\SubmitDocumentRequest;
use App\Http\Requests\UpdateProfileRequest;
use App\Mail\PinResetConfirmation;
use App\Mail\ProfileUpdateConfirmation;
use App\Models\Announcement;
use App\Models\DocumentRequest;
use App\Models\DocumentType;
use App\Models\RequestPurpose;
use App\Services\ControlNumberService;
use App\Services\NotificationService;
use App\Services\WFQService;
use Illuminate\Database\UniqueConstraintViolationException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Illuminate\Validation\ValidationException;

class DashboardController extends Controller
{
    protected WFQService $wFQService;

    public function __construct()
    {
        $this->wFQService = new WFQService;
    }

    public function index()
    {
        $resident = Auth::user()->resident;
        $recentRequests = DocumentRequest::where('resident_id', $resident->id)
            ->with(['documentType', 'purpose'])
            ->orderByRaw("CASE status WHEN 'pending' THEN 0 WHEN 'reviewing' THEN 1 WHEN 'approved' THEN 2 WHEN 'completed' THEN 3 WHEN 'released' THEN 4 ELSE 5 END")
            ->orderBy('created_at', 'desc')
            ->take(5)
            ->get();

        $pendingCount = DocumentRequest::where('resident_id', $resident->id)
            ->whereIn('status', ['pending', 'reviewing'])
            ->count();

        $completedCount = DocumentRequest::where('resident_id', $resident->id)
            ->whereIn('status', ['completed', 'released'])
            ->count();

        $announcements = Announcement::where('is_published', true)
            ->orderBy('published_at', 'desc')
            ->take(5)
            ->get();

        $notifications = Auth::user()
            ->notifications()
            ->take(10)
            ->get();

        return view('resident.dashboard', compact(
            'recentRequests',
            'pendingCount',
            'completedCount',
            'resident',
            'announcements',
            'notifications'
        ));
    }

    public function requests()
    {
        $resident = Auth::user()->resident;
        $requests = DocumentRequest::where('resident_id', $resident->id)
            ->with(['documentType', 'purpose'])
            ->orderByRaw("CASE status WHEN 'pending' THEN 0 WHEN 'reviewing' THEN 1 WHEN 'approved' THEN 2 WHEN 'completed' THEN 3 WHEN 'released' THEN 4 ELSE 5 END")
            ->orderBy('created_at', 'desc')
            ->paginate(10);

        return view('resident.requests', compact('requests'));
    }

    public function showRequest($id)
    {
        $request = DocumentRequest::with(['documentType', 'purpose', 'resident', 'documents'])
            ->where('resident_id', Auth::user()->resident->id)
            ->findOrFail($id);

        return view('resident.request-show', compact('request'));
    }

    public function profile()
    {
        $resident = Auth::user()->resident;

        return view('resident.profile', compact('resident'));
    }

    public function updateProfile(UpdateProfileRequest $request)
    {
        $resident = Auth::user()->resident;
        $user = Auth::user();

        $validated = $request->validated();

        $changedFields = [];

        if ($resident->contact_number !== $validated['contact_number']) {
            $changedFields[] = 'Contact Number';
        }
        if ($resident->emergency_contact !== $validated['emergency_contact']) {
            $changedFields[] = 'Emergency Contact';
        }

        $updateData = [
            'contact_number' => $validated['contact_number'],
            'emergency_contact' => $validated['emergency_contact'],
        ];

        if (isset($validated['civil_status']) && $resident->civil_status !== $validated['civil_status']) {
            $updateData['civil_status'] = $validated['civil_status'];
            $changedFields[] = 'Civil Status';
        }
        if (isset($validated['religion']) && $resident->religion !== $validated['religion']) {
            $updateData['religion'] = $validated['religion'];
            $changedFields[] = 'Religion';
        }
        if (isset($validated['place_of_birth']) && $resident->place_of_birth !== $validated['place_of_birth']) {
            $updateData['place_of_birth'] = $validated['place_of_birth'];
            $changedFields[] = 'Place of Birth';
        }
        if (isset($validated['occupation']) && $resident->occupation !== $validated['occupation']) {
            $updateData['occupation'] = $validated['occupation'];
            $changedFields[] = 'Occupation';
        }
        if (isset($validated['street']) && $resident->street !== $validated['street']) {
            $updateData['street'] = $validated['street'];
            $changedFields[] = 'Street Address';
        }
        if (isset($validated['barangay']) && $resident->barangay !== $validated['barangay']) {
            $updateData['barangay'] = $validated['barangay'];
            $changedFields[] = 'Barangay';
        }
        if (isset($validated['subdivision']) && $resident->subdivision !== $validated['subdivision']) {
            $updateData['subdivision'] = $validated['subdivision'];
            $changedFields[] = 'Subdivision';
        }
        if (isset($validated['purok']) && $resident->purok !== $validated['purok']) {
            $updateData['purok'] = $validated['purok'];
            $changedFields[] = 'Purok/Zone';
        }
        if (isset($validated['building_no']) && $resident->building_no !== $validated['building_no']) {
            $updateData['building_no'] = $validated['building_no'];
            $changedFields[] = 'Building No.';
        }
        if (isset($validated['unit_no']) && $resident->unit_no !== $validated['unit_no']) {
            $updateData['unit_no'] = $validated['unit_no'];
            $changedFields[] = 'Unit No.';
        }
        if (isset($validated['city']) && $resident->city !== $validated['city']) {
            $updateData['city'] = $validated['city'];
            $changedFields[] = 'City';
        }
        if (isset($validated['province']) && $resident->province !== $validated['province']) {
            $updateData['province'] = $validated['province'];
            $changedFields[] = 'Province';
        }
        if (isset($validated['zip_code']) && $resident->zip_code !== $validated['zip_code']) {
            $updateData['zip_code'] = $validated['zip_code'];
            $changedFields[] = 'Zip Code';
        }

        $resident->update($updateData);

        if (isset($validated['email']) && $validated['email'] !== $user->email) {
            $user->update(['email' => $validated['email']]);
            $changedFields[] = 'Email Address';
        }

        if (! empty($changedFields) && $user->email) {
            try {
                Mail::to($user->email)->send(new ProfileUpdateConfirmation($user, $changedFields));
            } catch (\Throwable $e) {
                \Illuminate\Support\Facades\Log::warning('Failed to send profile update confirmation', ['message' => $e->getMessage()]);
            }
        }

        return back()->with('success', __('profile.updated_success'));
    }

    public function resetPin(Request $request)
    {
        $request->validate([
            'current_pin' => ['required', 'string'],
            'new_pin' => ['required', 'string', 'digits:6', 'confirmed'],
        ]);

        $user = Auth::user();

        if (! $user->pin || ! Hash::check($request->current_pin, $user->pin)) {
            throw ValidationException::withMessages([
                'current_pin' => 'The current PIN you entered is incorrect.',
            ]);
        }

        $user->update([
            'pin' => Hash::make($request->new_pin),
        ]);

        if ($user->email) {
            try {
                Mail::to($user->email)->send(new PinResetConfirmation($user));
            } catch (\Throwable $e) {
                \Illuminate\Support\Facades\Log::warning('Failed to send PIN reset confirmation', ['message' => $e->getMessage()]);
            }
        }

        return back()->with('success', __('profile.pin_reset_success'));
    }

    public function newRequest()
    {
        $documentTypes = DocumentType::where('is_active', true)->get();
        $purposes = RequestPurpose::where('is_active', true)->get();
        $othersPurpose = RequestPurpose::where('code', 'OTHERS')->first();
        $resident = Auth::user()->resident;

        $activeRequests = DocumentRequest::where('resident_id', $resident->id)
            ->whereIn('status', ['pending', 'reviewing'])
            ->get();

        $activeCount = $activeRequests->count();
        $activeDocumentTypeIds = $activeRequests->pluck('document_type_id')->all();
        $maxActiveRequests = 2;
        $limitReached = $activeCount >= $maxActiveRequests;

        return view('resident.new-request', compact(
            'documentTypes',
            'purposes',
            'othersPurpose',
            'resident',
            'activeCount',
            'activeDocumentTypeIds',
            'maxActiveRequests',
            'limitReached'
        ));
    }

    public function submitRequest(SubmitDocumentRequest $request)
    {
        $validated = $request->validated();

        $resident = Auth::user()->resident;

        $activeRequests = DocumentRequest::where('resident_id', $resident->id)
            ->whereIn('status', ['pending', 'reviewing'])
            ->get();

        if ($activeRequests->count() >= 2) {
            return back()
                ->withErrors(['document_type_id' => 'You already have 2 active document requests. Please wait for your current requests to be completed or released before requesting again.'])
                ->withInput();
        }

        if ($activeRequests->where('document_type_id', $validated['document_type_id'])->isNotEmpty()) {
            return back()
                ->withErrors(['document_type_id' => 'You already have an active request for this document. Please choose a different document type or wait for the current request to be completed.'])
                ->withInput();
        }

        $documentRequest = $this->createDocumentRequest($resident, $validated);

        $this->wFQService->enqueue($documentRequest);

        NotificationService::notifyPersonnelOfNewRequest($documentRequest);

        return redirect()->route('resident.requests')
            ->with('success', 'Document request submitted successfully. Your queue number is '.$documentRequest->queue_number);
    }

    protected function createDocumentRequest($resident, array $validated)
    {
        $controlNumberService = app(ControlNumberService::class);
        $queueNumber = '';

        for ($attempt = 1; $attempt <= 4; $attempt++) {
            $controlNumber = $controlNumberService->generateControlNumber();

            try {
                if (! $queueNumber) {
                    $queueNumber = $this->wFQService->generateQueueNumber();
                }
                $qrCode = $controlNumberService->generateQrCodePath($controlNumber);

                return $resident->documentRequests()->create([
                    'control_number' => $controlNumber,
                    'queue_number' => $queueNumber,
                    'qr_code' => $qrCode,
                    'document_type_id' => $validated['document_type_id'],
                    'purpose_id' => $validated['purpose_id'],
                    'purpose_other' => $validated['purpose_other'] ?? null,
                    'status' => 'pending',
                    'processing_fee' => 0.00,
                    'expires_at' => now()->addDays(30),
                ]);
            } catch (UniqueConstraintViolationException $e) {
                $queueCollision = $this->wFQService->isQueueNumberCollision($e);
                $controlCollision = str_contains($e->getMessage(), 'document_requests_control_number_unique');

                if ($attempt === 4 || (! $queueCollision && ! $controlCollision)) {
                    throw $e;
                }
            }
        }
    }
}
