<?php

namespace App\Http\Controllers\Personnel;

use App\Http\Controllers\Controller;
use App\Http\Requests\RegisterResidentRequest;
use App\Models\DocumentType;
use App\Models\PersonnelRegistration;
use App\Models\RequestPurpose;
use App\Models\Resident;
use App\Models\User;
use App\Services\AgeService;
use App\Services\CredentialService;
use App\Services\FileService;
use App\Services\WFQService;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;

class RegistrationController extends Controller
{
    protected AgeService $ageService;

    protected CredentialService $credentialService;

    protected FileService $fileService;

    protected WFQService $wFQService;

    public function __construct()
    {
        $this->ageService = new AgeService;
        $this->credentialService = new CredentialService;
        $this->fileService = new FileService;
        $this->wFQService = new WFQService;
    }

    public function create()
    {
        $documentTypes = DocumentType::where('is_active', true)->get();
        $purposes = RequestPurpose::where('is_active', true)->where('code', '!=', 'OTHERS')->get();
        $othersPurpose = RequestPurpose::where('code', 'OTHERS')->first();

        return view('personnel.register-resident', compact('documentTypes', 'purposes', 'othersPurpose'));
    }

    public function store(RegisterResidentRequest $request)
    {
        $validated = $request->validated();

        $age = $this->ageService->computeAge(
            $validated['birthdate_year'],
            $validated['birthdate_month'],
            $validated['birthdate_day']
        );

        $personStatus = $validated['person_status'] ?? null;

        if ($personStatus === 'pregnant' && $validated['gender'] !== 'female') {
            throw ValidationException::withMessages(['person_status' => 'Pregnant status is only applicable for female residents.']);
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

        $idFilePath = null;
        $idBackFilePath = null;
        if ($request->hasFile('id_scan_front')) {
            $idFilePath = $this->fileService->uploadIdFile($request->file('id_scan_front'));
        }
        if ($request->hasFile('id_scan_back')) {
            $idBackFilePath = $this->fileService->uploadIdBack($request->file('id_scan_back'));
        }

        DB::beginTransaction();
        try {
            $email = $validated['email'] ?? ('resident_'.$this->credentialService->generateTrackingNumber().'@example.com');

            $trackingNumber = $this->credentialService->generateTrackingNumber();
            $pin = $this->credentialService->generatePin();

            $user = User::create([
                'email' => $email,
                'password' => Hash::make($pin),
                'pin' => Hash::make($pin),
                'tracking_number' => $trackingNumber,
                'role' => 'resident',
                'is_active' => true,
            ]);

            $user->assignRole('resident');

            $residentData = [
                'user_id' => $user->id,
                'first_name' => strtoupper($validated['first_name']),
                'last_name' => strtoupper($validated['last_name']),
                'middle_name' => ! empty($validated['middle_name']) ? strtoupper($validated['middle_name']) : null,
                'middle_name_none' => $validated['middle_name_none'] ?? false,
                'suffix' => ! empty($validated['suffix']) ? strtoupper($validated['suffix']) : null,
                'birthdate' => $birthdate,
                'age' => $age,
                'gender' => $validated['gender'],
                'civil_status' => $validated['civil_status'] ?? null,
                'nationality' => $validated['nationality'] ?? 'FILIPINO',
                'occupation' => $validated['occupation'] ?? null,
                'religion' => $validated['religion'] ?? null,
                'place_of_birth' => $validated['place_of_birth'] ?? null,
                'person_status' => $personStatus,
                'status_verification_photo' => $statusVerificationPath,
                'contact_number' => $validated['contact_number'],
                'emergency_contact' => $validated['emergency_contact'],
                'category' => $category,
                'category_remarks' => $category === 'pwd' ? 'Pending disability verification' : null,
            ];

            $optionalFields = ['building_no', 'unit_no', 'street', 'road', 'barangay', 'subdivision', 'purok'];
            foreach ($optionalFields as $field) {
                if (array_key_exists($field, $validated)) {
                    $residentData[$field] = ! empty($validated[$field]) ? strtoupper($validated[$field]) : null;
                }
            }
            if (! array_key_exists('barangay', $residentData)) {
                $residentData['barangay'] = 'MOLINO I';
            }
            $residentData['city'] = 'BACOOR CITY';
            $residentData['province'] = 'CAVITE';
            $residentData['zip_code'] = '4102';

            $resident = Resident::create($residentData);

            $resident->idVerifications()->create([
                'id_type' => $validated['id_type'],
                'file_path' => $idFilePath,
                'file_type' => $request->hasFile('id_scan_front') ? $request->file('id_scan_front')->getClientOriginalExtension() : null,
                'file_size' => $request->hasFile('id_scan_front') ? $request->file('id_scan_front')->getSize() : null,
                'back_file_path' => $idBackFilePath,
                'back_file_type' => $request->hasFile('id_scan_back') ? $request->file('id_scan_back')->getClientOriginalExtension() : null,
                'back_file_size' => $request->hasFile('id_scan_back') ? $request->file('id_scan_back')->getSize() : null,
            ]);

            $documentRequest = $resident->documentRequests()->create([
                'queue_number' => $this->wFQService->generateQueueNumber(),
                'document_type_id' => $validated['document_type_id'],
                'purpose_id' => $validated['purpose_id'],
                'purpose_other' => $validated['purpose_other'] ?? null,
                'status' => 'pending',
            ]);

            $this->wFQService->enqueue($documentRequest);

            PersonnelRegistration::create([
                'personnel_id' => Auth::id(),
                'resident_id' => $resident->id,
                'action' => 'registered',
                'metadata' => [
                    'tracking_number' => $trackingNumber,
                    'queue_number' => $documentRequest->queue_number,
                    'staff_badge' => $validated['staff_badge'] ?? null,
                ],
            ]);

            DB::commit();

            return redirect()->route('personnel.registrations.show', $resident->id)
                ->with('success', "Resident registered successfully. Tracking #: {$trackingNumber}, PIN: {$pin}");

        } catch (\Exception $e) {
            DB::rollBack();
            if ($idFilePath) {
                $this->fileService->deleteFile($idFilePath);
            }
            if ($idBackFilePath) {
                $this->fileService->deleteFile($idBackFilePath);
            }
            if ($statusVerificationPath) {
                $this->fileService->deleteFile($statusVerificationPath);
            }
            throw $e;
        }
    }

    public function show($id)
    {
        $resident = Resident::with(['user', 'documentRequests'])->findOrFail($id);

        return view('personnel.registration-show', compact('resident'));
    }
}
