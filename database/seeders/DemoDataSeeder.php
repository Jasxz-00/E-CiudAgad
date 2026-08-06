<?php

namespace Database\Seeders;

use App\Models\Announcement;
use App\Models\AuditLog;
use App\Models\Concern;
use App\Models\DocumentRequest;
use App\Models\DuplicateClaim;
use App\Models\IdVerification;
use App\Models\PersonnelRegistration;
use App\Models\Resident;
use App\Models\RequestDocument;
use App\Models\User;
use App\Services\CredentialService;
use App\Services\WFQService;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DemoDataSeeder extends Seeder
{
    public function run(): void
    {
        $credentials = new CredentialService;
        $wfq = new WFQService;

        $admin = User::where('email', 'admin@eciudadagad.gov.ph')->first();
        $personnel = User::where('email', 'personnel@eciudadagad.gov.ph')->first();

        if (! $admin || ! $personnel) {
            $this->command->error('Run RolePermissionSeeder and AdminUserSeeder first.');

            return;
        }

        $residents = [
            [
                'first_name' => 'Juan', 'middle_name' => 'Dela', 'last_name' => 'Cruz', 'suffix' => null,
                'birthdate' => '1985-03-12', 'gender' => 'Male', 'civil_status' => 'Married',
                'occupation' => 'Tricycle Driver', 'religion' => 'Roman Catholic',
                'place_of_birth' => 'Bacoor City, Cavite', 'category' => 'regular', 'person_status' => 'regular',
                'purok' => 'Purok 1', 'verified' => true,
            ],
            [
                'first_name' => 'Maria', 'middle_name' => 'Santos', 'last_name' => 'Lopez', 'suffix' => null,
                'birthdate' => '1990-07-25', 'gender' => 'Female', 'civil_status' => 'Single',
                'occupation' => 'Sari-sari Store Owner', 'religion' => 'Iglesia ni Cristo',
                'place_of_birth' => 'Las Piñas City', 'category' => 'regular', 'person_status' => 'regular',
                'purok' => 'Purok 2', 'verified' => true,
            ],
            [
                'first_name' => 'Pedro', 'middle_name' => 'Reyes', 'last_name' => 'Gonzales', 'suffix' => 'Sr.',
                'birthdate' => '1956-01-08', 'gender' => 'Male', 'civil_status' => 'Widowed',
                'occupation' => 'Retired', 'religion' => 'Roman Catholic',
                'place_of_birth' => 'Bacoor City, Cavite', 'category' => 'senior', 'person_status' => 'senior',
                'purok' => 'Purok 3', 'verified' => true,
            ],
            [
                'first_name' => 'Ana', 'middle_name' => null, 'last_name' => 'Reyes', 'suffix' => null,
                'birthdate' => '1992-11-19', 'gender' => 'Female', 'civil_status' => 'Married',
                'occupation' => 'Public School Teacher', 'religion' => 'Roman Catholic',
                'place_of_birth' => 'Imus City, Cavite', 'category' => 'pregnant', 'person_status' => 'pregnant',
                'purok' => 'Purok 1', 'verified' => true,
            ],
            [
                'first_name' => 'Jose', 'middle_name' => 'M.', 'last_name' => 'Garcia', 'suffix' => null,
                'birthdate' => '1978-05-30', 'gender' => 'Male', 'civil_status' => 'Single',
                'occupation' => 'Self-employed', 'religion' => 'Roman Catholic',
                'place_of_birth' => 'Bacoor City, Cavite', 'category' => 'pwd', 'person_status' => 'pwd',
                'purok' => 'Purok 4', 'verified' => true,
            ],
            [
                'first_name' => 'Liza', 'middle_name' => 'Mercado', 'last_name' => 'Dizon', 'suffix' => null,
                'birthdate' => '2000-02-14', 'gender' => 'Female', 'civil_status' => 'Single',
                'occupation' => 'Call Center Agent', 'religion' => 'Born Again Christian',
                'place_of_birth' => 'Manila City', 'category' => 'regular', 'person_status' => 'regular',
                'purok' => 'Purok 2', 'verified' => true,
            ],
            [
                'first_name' => 'Ramon', 'middle_name' => 'C.', 'last_name' => 'Castillo', 'suffix' => 'Jr.',
                'birthdate' => '1982-09-04', 'gender' => 'Male', 'civil_status' => 'Married',
                'occupation' => 'Construction Foreman', 'religion' => 'Roman Catholic',
                'place_of_birth' => 'Bacoor City, Cavite', 'category' => 'regular', 'person_status' => 'regular',
                'purok' => 'Purok 5', 'verified' => true,
            ],
            [
                'first_name' => 'Elena', 'middle_name' => 'Villanueva', 'last_name' => 'Aquino', 'suffix' => null,
                'birthdate' => '1951-12-01', 'gender' => 'Female', 'civil_status' => 'Widowed',
                'occupation' => 'Retired', 'religion' => 'Roman Catholic',
                'place_of_birth' => 'Bacoor City, Cavite', 'category' => 'senior', 'person_status' => 'senior',
                'purok' => 'Purok 3', 'verified' => true,
            ],
            [
                'first_name' => 'Marco', 'middle_name' => 'B.', 'last_name' => 'Bautista', 'suffix' => null,
                'birthdate' => '1995-06-22', 'gender' => 'Male', 'civil_status' => 'Single',
                'occupation' => 'Graphic Designer', 'religion' => 'Aglipayan',
                'place_of_birth' => 'General Trias, Cavite', 'category' => 'regular', 'person_status' => 'regular',
                'purok' => 'Purok 4', 'verified' => true,
            ],
            [
                'first_name' => 'Sofia', 'middle_name' => 'F.', 'last_name' => 'Fernandez', 'suffix' => null,
                'birthdate' => '1988-08-16', 'gender' => 'Female', 'civil_status' => 'Married',
                'occupation' => 'OFW - Caregiver', 'religion' => 'Roman Catholic',
                'place_of_birth' => 'Bacoor City, Cavite', 'category' => 'regular', 'person_status' => 'regular',
                'purok' => 'Purok 1', 'verified' => true,
            ],
            [
                'first_name' => 'Carlo', 'middle_name' => null, 'last_name' => 'Mendoza', 'suffix' => null,
                'birthdate' => '1998-04-10', 'gender' => 'Male', 'civil_status' => 'Single',
                'occupation' => 'Biker / Courier', 'religion' => 'Roman Catholic',
                'place_of_birth' => 'Bacoor City, Cavite', 'category' => 'regular', 'person_status' => 'regular',
                'purok' => 'Purok 2', 'verified' => true,
            ],
            [
                'first_name' => 'Andrea', 'middle_name' => 'N.', 'last_name' => 'Navarro', 'suffix' => null,
                'birthdate' => '1993-10-05', 'gender' => 'Female', 'civil_status' => 'Single',
                'occupation' => 'Online Seller', 'religion' => 'Roman Catholic',
                'place_of_birth' => 'Bacoor City, Cavite', 'category' => 'regular', 'person_status' => 'regular',
                'purok' => 'Purok 5', 'verified' => false,
            ],
        ];

        $residentModels = [];
        foreach ($residents as $i => $data) {
            $birthdate = \Carbon\Carbon::parse($data['birthdate']);
            $email = 'resident'.($i + 1).'@example.com';
            $username = 'resident'.($i + 1);

            $user = User::updateOrCreate(
                ['email' => $email],
                [
                    'username' => $username,
                    'password' => bcrypt('resident123'),
                    'role' => 'resident',
                    'is_active' => true,
                    'email_verified_at' => now()->subDays(30 - $i),
                    'tracking_number' => $credentials->generateTrackingNumber(),
                    'pin' => Hash::make('123456'),
                ]
            );
            $user->assignRole('resident');

            $resident = Resident::updateOrCreate(
                ['user_id' => $user->id],
                [
                    'first_name' => $data['first_name'],
                    'middle_name' => $data['middle_name'],
                    'middle_name_none' => $data['middle_name'] === null,
                    'last_name' => $data['last_name'],
                    'suffix' => $data['suffix'],
                    'birthdate' => $data['birthdate'],
                    'age' => $birthdate->age,
                    'gender' => $data['gender'],
                    'civil_status' => $data['civil_status'],
                    'nationality' => 'FILIPINO',
                    'occupation' => $data['occupation'],
                    'religion' => $data['religion'],
                    'place_of_birth' => $data['place_of_birth'],
                    'building_no' => (string) (100 + $i * 7),
                    'unit_no' => null,
                    'street' => 'Molino Road',
                    'road' => 'Molino Road',
                    'subdivision' => 'Phase '.($i % 3 + 1),
                    'barangay' => 'MOLINO I',
                    'purok' => $data['purok'],
                    'city' => 'BACOOR CITY',
                    'province' => 'CAVITE',
                    'zip_code' => '4102',
                    'contact_number' => '0917'.str_pad((string) (1000000 + $i * 111111), 7, '0', STR_PAD_LEFT),
                    'emergency_contact' => 'Emergency: 0928'.str_pad((string) (2000000 + $i * 222222), 7, '0', STR_PAD_LEFT),
                    'category' => $data['category'],
                    'person_status' => $data['person_status'],
                    'category_remarks' => in_array($data['category'], ['pwd', 'senior', 'pregnant']) ? 'Verified supporting document on file.' : null,
                    'status_verification_photo' => in_array($data['category'], ['pwd', 'senior', 'pregnant']) ? 'uploads/status-verifications/demo-'.($i + 1).'.jpg' : null,
                    'verified_at' => $data['verified'] ? now()->subDays(25 - $i) : null,
                ]
            );
            $residentModels[] = $resident;

            if ($data['verified']) {
                IdVerification::updateOrCreate(
                    ['resident_id' => $resident->id, 'id_type' => 'phil_id'],
                    [
                        'id_number' => $this->generateIdNumber($i),
                        'file_path' => 'uploads/id-verifications/demo-'.($i + 1).'.jpg',
                        'file_type' => 'jpg',
                        'file_size' => 250000 + $i * 13771,
                        'is_verified' => true,
                        'verified_at' => now()->subDays(24 - $i),
                        'verified_by' => $personnel->id,
                    ]
                );
            }
        }

        $requestSpecs = [
            // [resident index, document type index, purpose index, status, days ago, remarks]
            [0, 0, 2, 'pending', 0, null],
            [3, 5, 0, 'pending', 0, null],
            [6, 2, 0, 'pending', 0, null],
            [1, 1, 4, 'reviewing', 1, 'Under initial verification'],
            [4, 0, 0, 'reviewing', 1, 'Awaiting ID verification'],
            [8, 7, 3, 'pending', 0, null],
            [5, 4, 6, 'approved', 2, 'Approved, awaiting printing'],
            [9, 0, 5, 'approved', 3, 'Payment received'],
            [2, 2, 0, 'completed', 5, 'Released to representative'],
            [7, 5, 2, 'completed', 6, null],
            [10, 0, 3, 'completed', 4, null],
            [0, 6, 7, 'rejected', 8, 'Incomplete requirements'],
            [1, 3, 6, 'rejected', 7, 'Business address outside jurisdiction'],
            [11, 0, 1, 'pending', 0, null],
            [2, 0, 0, 'released', 3, null],
            [3, 7, 3, 'completed', 9, null],
            [4, 0, 4, 'released', 5, null],
            [6, 3, 6, 'rejected', 10, 'No barangay business clearance from previous year'],
            [8, 1, 7, 'completed', 11, null],
            [9, 2, 4, 'released', 2, null],
            [5, 0, 1, 'completed', 12, null],
        ];

        foreach ($requestSpecs as $i => $spec) {
            [$residentIndex, $docIndex, $purposeIndex, $status, $daysAgo, $remarks] = $spec;

            $resident = $residentModels[$residentIndex];
            $documentType = \App\Models\DocumentType::orderBy('id')->get()[$docIndex] ?? \App\Models\DocumentType::first();
            $purpose = \App\Models\RequestPurpose::orderBy('id')->get()[$purposeIndex] ?? \App\Models\RequestPurpose::first();

            $createdAt = now()->subDays($daysAgo)->subHours($i % 8);

            $request = DocumentRequest::create([
                'queue_number' => 'Q-'.str_pad((string) ($i + 1), 4, '0', STR_PAD_LEFT),
                'resident_id' => $resident->id,
                'document_type_id' => $documentType->id,
                'purpose_id' => $purpose->id,
                'purpose_other' => null,
                'status' => $status,
                'remarks' => $remarks,
                'rejection_reason' => $status === 'rejected' ? ($remarks ?? 'Missing supporting documents.') : null,
                'created_at' => $createdAt,
                'updated_at' => $createdAt,
            ]);

            if (in_array($status, ['pending', 'reviewing'])) {
                $wfq->enqueue($request);
            } else {
                $request->update(['total_weight' => $wfq->calculateWeight($resident, $documentType, $purpose)]);
            }

            if (in_array($status, ['approved', 'completed', 'released'])) {
                $request->update([
                    'processed_by' => $personnel->id,
                    'processing_started_at' => $createdAt->addHour(),
                ]);
            }

            if (in_array($status, ['completed', 'released'])) {
                $request->update(['completed_at' => $createdAt->addDay()]);
            }

            if ($i % 5 === 0) {
                RequestDocument::create([
                    'document_request_id' => $request->id,
                    'file_path' => 'uploads/requests/demo-'.($i + 1).'.pdf',
                    'file_type' => 'pdf',
                    'file_size' => 120000 + $i * 9341,
                    'original_name' => 'supporting-document-'.($i + 1).'.pdf',
                    'created_at' => $createdAt,
                    'updated_at' => $createdAt,
                ]);
            }
        }

        $concernSpecs = [
            [0, 'Uncollected Garbage', 'Garbage has not been collected in Purok 1 for over a week. The trash is piling up near the canal.', 'pending', null, 1],
            [4, 'Damaged Street Light', 'The street light in front of my house has been broken for a month. It is very dark at night.', 'reviewing', 'We have reported this to the barangay electrician. Estimated repair within the week.', 2],
            [7, 'Flooding During Rain', 'Water does not drain properly along Molino Road after heavy rain.', 'resolved', 'Canal clearing was completed last week. Drainage is now working properly.', 5],
            [3, 'Request for Senior Citizen ID Assistance', 'Need assistance in processing my father\'s senior citizen benefits.', 'pending', null, 0],
            [6, 'Noise Complaint', 'Loud karaoke every night from a neighbor until 2 AM.', 'reviewing', 'Barangay tanod has been advised to visit the household.', 3],
            [9, 'Illegal Parking', 'Vehicles are parked on the sidewalk blocking pedestrians.', 'resolved', 'Warning signs were placed and erring drivers were informed.', 6],
            [10, 'Medical Assistance Inquiry', 'How do I apply for medical assistance from the barangay?', 'pending', null, 1],
        ];

        foreach ($concernSpecs as $i => $spec) {
            [$residentIndex, $subject, $message, $status, $adminNotes, $daysAgo] = $spec;
            Concern::create([
                'resident_id' => $residentModels[$residentIndex]->id,
                'subject' => $subject,
                'message' => $message,
                'status' => $status,
                'admin_notes' => $adminNotes,
                'created_at' => now()->subDays($daysAgo)->subHours($i),
                'updated_at' => now()->subDays(max($daysAgo - 1, 0)),
            ]);
        }

        $announcements = [
            ['Bacoor City Free Medical Mission', 'A free medical and dental mission will be held at the barangay covered court on the first Saturday of next month. Bring your PhilHealth ID and barangay ID.', true, 3],
            ['Purok 2 Cleanup Drive', 'Community cleanup drive this weekend. Volunteers are encouraged to join at 7 AM in front of the chapel.', true, 5],
            ['Barangay Fiesta Celebration', 'Annual barangay fiesta on August 15. There will be games, a bingo night, and a community dinner.', true, 8],
            ['Distribution of Senior Citizen Allowance', 'Senior citizens may claim their monthly allowance at the barangay hall every 15th of the month.', true, 2],
            ['Water Service Interruption Advisory', 'Water service will be interrupted on Thursday from 8 AM to 5 PM due to pipeline repair along Molino Road.', true, 1],
            ['Barangay Basketball League 2026', 'Registration is now open for the upcoming inter-purok basketball league. Deadline is on August 30.', false, 0],
        ];

        foreach ($announcements as $i => $spec) {
            [$title, $content, $isPublished, $daysAgo] = $spec;
            Announcement::create([
                'title' => $title,
                'content' => $content,
                'is_published' => $isPublished,
                'published_at' => $isPublished ? now()->subDays($daysAgo)->subHours($i) : null,
                'created_by' => $admin->id,
                'created_at' => now()->subDays($daysAgo + 1)->subHours($i),
                'updated_at' => now()->subDays($daysAgo),
            ]);
        }

        $auditActions = [
            ['created', DocumentRequest::class, 'Document request Q-0001 was submitted by resident Juan Dela Cruz.'],
            ['approved', DocumentRequest::class, 'Approved document request Q-0007.'],
            ['rejected', DocumentRequest::class, 'Rejected document request Q-0012: Incomplete requirements.'],
            ['completed', DocumentRequest::class, 'Completed document request Q-0010.'],
            ['verified', User::class, 'Verified ID of resident Maria Lopez.'],
            ['updated', Concern::class, 'Updated concern status to resolved.'],
        ];

        foreach ($auditActions as $i => $spec) {
            [$action, $type, $description] = $spec;
            AuditLog::create([
                'user_id' => $i % 2 === 0 ? $admin->id : $personnel->id,
                'action' => $action,
                'auditable_type' => $type,
                'auditable_id' => $i + 1,
                'description' => $description,
                'ip_address' => '127.0.0.1',
                'user_agent' => 'Seeder',
                'created_at' => now()->subDays(7 - $i)->subHours($i * 2),
                'updated_at' => now()->subDays(7 - $i),
            ]);
        }

        DuplicateClaim::create([
            'registration_data' => [
                'first_name' => 'Juan',
                'middle_name' => 'Dela',
                'last_name' => 'Cruz',
                'birthdate' => '1985-03-12',
            ],
            'matched_resident_id' => $residentModels[0]->id,
            'status' => 'pending',
            'staff_notes' => null,
            'reviewed_by' => null,
            'reviewed_at' => null,
            'created_at' => now()->subDays(2),
            'updated_at' => now()->subDays(2),
        ]);

        DuplicateClaim::create([
            'registration_data' => [
                'first_name' => 'Elena',
                'middle_name' => 'Villanueva',
                'last_name' => 'Aquino',
                'birthdate' => '1951-12-01',
            ],
            'matched_resident_id' => $residentModels[7]->id,
            'status' => 'resolved',
            'staff_notes' => 'Confirmed as the same resident. Registration was voided.',
            'reviewed_by' => $admin->id,
            'reviewed_at' => now()->subDay(),
            'created_at' => now()->subDays(3),
            'updated_at' => now()->subDay(),
        ]);

        $registrations = [
            ['registered', 0, ['channel' => 'walk-in', 'remarks' => 'Assisted with online registration']],
            ['filed_request', 2, ['document' => 'Certificate of Indigency', 'channel' => 'walk-in']],
            ['filed_request', 7, ['document' => 'Barangay ID', 'channel' => 'online']],
        ];

        foreach ($registrations as $i => $spec) {
            [$action, $residentIndex, $metadata] = $spec;
            PersonnelRegistration::create([
                'personnel_id' => $personnel->id,
                'resident_id' => $residentModels[$residentIndex]->id,
                'action' => $action,
                'metadata' => $metadata,
                'created_at' => now()->subDays($i + 1),
            ]);
        }

        $this->command->info('Demo data seeded: '.count($residentModels).' residents, '.count($requestSpecs).' document requests, '.count($concernSpecs).' concerns, '.count($announcements).' announcements.');
    }

    private function generateIdNumber(int $index): string
    {
        $types = ['0001-'.$index, 'P'.($index + 1000).'0', 'UMID-'.$index.'-'.($index * 7), 'D'.str_pad((string) ($index * 12345), 6, '0', STR_PAD_LEFT), 'TIN-'.$index.'-'.($index * 111)];

        return $types[$index % count($types)];
    }
}
