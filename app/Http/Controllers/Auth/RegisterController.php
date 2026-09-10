<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\RegisterResidentRequest;
use App\Models\DocumentType;
use App\Models\DocumentRequest;
use App\Models\DuplicateClaim;
use App\Models\PersonnelRegistration;
use App\Models\RequestPurpose;
use App\Models\Resident;
use App\Models\User;
use App\Services\AgeService;
use App\Services\ControlNumberService;
use App\Services\CredentialService;
use App\Services\FileService;
use App\Services\WFQService;
use Illuminate\Support\Str;
use Illuminate\Database\UniqueConstraintViolationException;
use Illuminate\Validation\ValidationException;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;

class RegisterController extends Controller
{
    protected AgeService $ageService;

    protected CredentialService $credentialService;

    protected FileService $fileService;

    protected WFQService $wFQService;

    protected ControlNumberService $controlNumberService;

    public function __construct()
    {
        $this->ageService = new AgeService;
        $this->credentialService = new CredentialService;
        $this->fileService = new FileService;
        $this->wFQService = new WFQService;
        $this->controlNumberService = new ControlNumberService;
    }

    public function showRegistrationForm()
    {
        return view('auth.register', [
            'documentTypes' => DocumentType::where('is_active', true)->get(),
            'purposes' => RequestPurpose::where('is_active', true)->get(),
            'certResidencyId' => DocumentType::where('code', 'CERT_RESIDENCY')->value('id'),
            'documentTypeCodeMap' => json_encode(DocumentType::pluck('id', 'code')),
            'translations' => [
                'en' => __('registration', [], 'en'),
                'fil' => __('registration', [], 'fil'),
            ],
        ]);
    }

    public function register(RegisterResidentRequest $request): JsonResponse|RedirectResponse
    {
        try {
            $validated = $request->validated();
        } catch (ValidationException $e) {
            if ($request->expectsJson()) {
                return response()->json(['errors' => $e->errors()], 422);
            }
            throw $e;
        }

        $submissionId = $validated['submission_id'] ?? null;

        if ($submissionId && session('last_submission_id') === $submissionId) {
            $cached = session('last_submission_response');
            if ($request->expectsJson() && is_array($cached)) {
                return response()->json($cached, 200);
            }

            return redirect()->route('login')
                ->with('success', __('Your registration has already been submitted. Please login using your Tracking Number and PIN.'));
        }

        $assistedMode = ($validated['assisted_mode'] ?? '0') === '1';

        $age = $this->ageService->computeAge(
            $validated['birthdate_year'],
            $validated['birthdate_month'],
            $validated['birthdate_day']
        );

        $personStatus = $validated['person_status'] ?? null;

        if ($personStatus === 'pregnant' && $validated['gender'] !== 'female') {
            if ($request->expectsJson()) {
                return response()->json(['errors' => ['person_status' => ['Pregnant status is only applicable for female residents.']]], 422);
            }
            throw ValidationException::withMessages(['person_status' => 'Pregnant status is only applicable for female residents.']);
        }

        if (! empty($validated['is_pregnant']) && $validated['gender'] !== 'female') {
            if ($request->expectsJson()) {
                return response()->json(['errors' => ['is_pregnant' => ['Pregnant status is only applicable for female residents.']]], 422);
            }
            throw ValidationException::withMessages(['is_pregnant' => 'Pregnant status is only applicable for female residents.']);
        }

        $statusVerificationPath = null;
        if ($request->hasFile('status_verification_photo')) {
            $statusVerificationPath = $this->fileService->uploadStatusVerification($request->file('status_verification_photo'));
        }

        if ($personStatus === 'senior') {
            $category = 'senior';
        } elseif ($personStatus === 'pregnant') {
            $category = 'pregnant';
        } elseif ($personStatus === 'pwd') {
            $category = 'pwd';
        } else {
            if ($this->ageService->isSeniorCitizen($age)) {
                $category = 'senior';
            } elseif (! empty($validated['is_pregnant']) && $validated['gender'] === 'female') {
                $category = 'pregnant';
            } else {
                $category = 'regular';
            }
        }

        $birthdate = sprintf('%04d-%02d-%02d', $validated['birthdate_year'], $validated['birthdate_month'], $validated['birthdate_day']);

        if (! $assistedMode) {
            $duplicateQuery = Resident::where('first_name', strtoupper($validated['first_name']))
                ->where('last_name', strtoupper($validated['last_name']))
                ->where('birthdate', $birthdate);

            $duplicateResident = $duplicateQuery->first();

            if ($duplicateResident) {
                if ($statusVerificationPath) {
                    $this->fileService->deleteFile($statusVerificationPath);
                }

                $storableData = collect($validated)
                    ->except(['id_scan_front', 'id_scan_back', 'status_verification_photo', 'submission_id'])
                    ->all();

                session()->put('duplicate_found', true);
                session()->put('duplicate_resident_name', $duplicateResident->full_name);
                session()->put('duplicate_registration_data', $storableData);

                if ($request->expectsJson()) {
                    return response()->json([
                        'duplicate' => true,
                        'message' => 'An account associated with this information already exists. Please login using your Tracking Number and PIN, or contact Barangay Staff for assistance.',
                        'resident_name' => $duplicateResident->full_name,
                    ], 409);
                }

                return redirect()->route('register')
                    ->withErrors(['duplicate' => 'An account associated with this information already exists. Please login using your Tracking Number and PIN, or contact Barangay Staff for assistance.'])
                    ->withInput();
            }
        }

        $idFilePath = null;
        $idBackFilePath = null;
        $id1x1Path = null;
        $idFilePath = $this->fileService->uploadIdFile($request->file('id_scan_front'));
        $idBackFilePath = $this->fileService->uploadIdBack($request->file('id_scan_back'));
        $id1x1Path = $this->fileService->uploadIdFile($request->file('id_1x1'));

        DB::beginTransaction();
        try {
            $trackingNumber = $this->credentialService->generateTrackingNumber();
            $pin = $this->credentialService->generatePin();

            $email = $validated['email'] ?? ('resident_'.$trackingNumber.'@example.com');

            $user = User::create([
                'email' => $email,
                'password' => Hash::make($pin),
                'pin' => Hash::make($pin),
                'tracking_number' => $trackingNumber,
                'role' => 'resident',
                'is_active' => true,
            ]);

            $user->assignRole('resident');

            $resident = Resident::create([
                'user_id' => $user->id,
                'first_name' => strtoupper($validated['first_name']),
                'last_name' => strtoupper($validated['last_name']),
                'middle_name' => $validated['middle_name'] ?? null ? strtoupper($validated['middle_name']) : null,
                'middle_name_none' => $validated['middle_name_none'] ?? false,
                'suffix' => $validated['suffix'] ?? null ? strtoupper($validated['suffix']) : null,
                'birthdate' => $birthdate,
                'age' => $age,
                'gender' => $validated['gender'],
                'civil_status' => $validated['civil_status'] ?? null,
                'nationality' => $validated['nationality'] ?? 'FILIPINO',
                'occupation' => $validated['occupation'] ?? null,
                'religion' => $validated['religion'] ?? null,
                'place_of_birth' => $validated['place_of_birth'] ?? null,
                'person_status' => $personStatus ?: null,
                'status_verification_photo' => $statusVerificationPath,
                'building_no' => $validated['building_no'] ?? null ? strtoupper($validated['building_no']) : null,
                'unit_no' => $validated['unit_no'] ?? null ? strtoupper($validated['unit_no']) : null,
                'street' => strtoupper($validated['street'] ?? 'MOLINO I'),
                'road' => $validated['road'] ?? null ? strtoupper($validated['road']) : 'MOLINO ROAD',
                'subdivision' => $validated['subdivision'] ?? null ? strtoupper($validated['subdivision']) : null,
                'barangay' => strtoupper($validated['barangay'] ?? 'MOLINO I'),
                'purok' => $validated['purok'] ?? null ? strtoupper($validated['purok']) : null,
                'contact_number' => $validated['contact_number'],
                'emergency_contact' => $validated['emergency_contact'],
                'city' => 'BACOOR CITY',
                'province' => 'CAVITE',
                'zip_code' => '4102',
                'category' => $category,
                'category_remarks' => $category === 'pwd' ? 'Pending disability verification' : null,
                'is_pregnant' => ! empty($validated['is_pregnant']) ? 1 : 0,
            ]);

            $resident->idVerifications()->create([
                'id_type' => $validated['id_type'],
                'file_path' => $idFilePath,
                'file_type' => $request->file('id_scan_front')->getClientOriginalExtension(),
                'file_size' => $request->file('id_scan_front')->getSize(),
                'back_file_path' => $idBackFilePath,
                'back_file_type' => $request->file('id_scan_back')->getClientOriginalExtension(),
                'back_file_size' => $request->file('id_scan_back')->getSize(),
                'id_1x1_path' => $id1x1Path,
                'id_1x1_type' => $request->file('id_1x1')->getClientOriginalExtension(),
                'id_1x1_size' => $request->file('id_1x1')->getSize(),
            ]);

            $documentRequest = $this->createDocumentRequest($resident, $validated);

            $this->wFQService->enqueue($documentRequest);

            \App\Services\NotificationService::notifyPersonnelOfNewRequest($documentRequest);

            if ($assistedMode && ! empty($validated['staff_badge'])) {
                PersonnelRegistration::create([
                    'personnel_id' => Auth::id() ?? 0,
                    'resident_id' => $resident->id,
                    'action' => 'registered',
                    'metadata' => [
                        'tracking_number' => $trackingNumber,
                        'queue_number' => $documentRequest->queue_number,
                        'staff_badge' => $validated['staff_badge'],
                        'source' => 'self-service-assisted',
                    ],
                ]);
            }

            DB::commit();

            if ($submissionId) {
                session(['last_submission_id' => $submissionId]);
            }

if ($request->expectsJson()) {
                    if (! $assistedMode) {
                        Auth::login($user);

                        session()->flash('credentials', [
                            'tracking_number' => $trackingNumber,
                            'pin' => $pin,
                            'email' => $email,
                            'queue_number' => $documentRequest->queue_number,
                            'control_number' => $documentRequest->control_number,
                            'qr_code' => $documentRequest->qr_code,
                        ]);

                        $payload = [
                            'success' => true,
                            'redirect' => route('request.show', ['control_number' => $documentRequest->control_number]),
                            'credentials' => [
                                'tracking_number' => $trackingNumber,
                                'pin' => $pin,
                                'email' => $email,
                                'queue_number' => $documentRequest->queue_number,
                                'control_number' => $documentRequest->control_number,
                                'qr_code' => $documentRequest->qr_code,
                            ],
                        ];
                    } else {
                        $payload = [
                            'success' => true,
                            'message' => "Resident registered successfully via assisted mode. Tracking #: {$trackingNumber}",
                            'redirect' => route('login'),
                        ];
                    }

                    if ($submissionId) {
                        session(['last_submission_response' => $payload]);
                    }

                    return response()->json($payload);
                }

                if (! $assistedMode) {
                    Auth::login($user);

                    session()->flash('credentials', [
                        'tracking_number' => $trackingNumber,
                        'pin' => $pin,
                        'email' => $email,
                        'queue_number' => $documentRequest->queue_number,
                        'control_number' => $documentRequest->control_number,
                        'qr_code' => $documentRequest->qr_code,
                    ]);

                    return redirect()->route('request.show', ['control_number' => $documentRequest->control_number]);
                }

            return redirect()->route('login')
                ->with('success', "Resident registered successfully via assisted mode. Tracking #: {$trackingNumber}");

        } catch (\Throwable $e) {
            DB::rollBack();
            if ($idFilePath) {
                $this->fileService->deleteFile($idFilePath);
            }
            if ($idBackFilePath) {
                $this->fileService->deleteFile($idBackFilePath);
            }
            if ($id1x1Path) {
                $this->fileService->deleteFile($id1x1Path);
            }
            if ($statusVerificationPath) {
                $this->fileService->deleteFile($statusVerificationPath);
            }
            Log::error('Registration failed', [
                'exception' => get_class($e),
                'message' => $e->getMessage(),
                'file' => $e->getFile(),
                'line' => $e->getLine(),
                'trace' => $e->getTraceAsString(),
            ]);
            if ($request->expectsJson()) {
                return response()->json(['error' => 'Registration failed. Please try again.'], 500);
            }
            throw $e;
        }
    }

    protected function createDocumentRequest(Resident $resident, array $validated)
    {
        $queueNumber = '';

        for ($attempt = 1; $attempt <= 4; $attempt++) {
            $controlNumber = $this->controlNumberService->generateControlNumber();

            try {
                if (! $queueNumber) {
                    $queueNumber = $this->wFQService->generateQueueNumber();
                }
                $qrCode = $this->controlNumberService->generateQrCodePath($controlNumber);
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

    public function insistDuplicate(Request $request)
    {
        $data = session('duplicate_registration_data');

        if (! $data) {
            return redirect()->route('register')->withErrors(['duplicate' => 'Session expired. Please fill out the registration form again.']);
        }

        $data = collect($data)
            ->except(['id_scan_front', 'id_scan_back', 'status_verification_photo', 'submission_id'])
            ->all();

        $duplicateResident = Resident::where('first_name', strtoupper($data['first_name']))
            ->where('last_name', strtoupper($data['last_name']))
            ->where('birthdate', sprintf('%04d-%02d-%02d', $data['birthdate_year'], $data['birthdate_month'], $data['birthdate_day']))
            ->first();

        DuplicateClaim::create([
            'registration_data' => $data,
            'matched_resident_id' => $duplicateResident?->id,
            'status' => 'pending',
        ]);

        session()->forget(['duplicate_found', 'duplicate_resident_name', 'duplicate_registration_data']);

        return redirect()->route('login')
            ->with('insistence_submitted', true);
    }
}
