<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class DocumentTypeSeeder extends Seeder
{
    public function run(): void
    {
        $documents = [
            [
                'name' => 'Barangay Clearance',
                'code' => 'BRGY_CLEARANCE',
                'description' => 'Official document certifying good moral character and residency.',
                'complexity' => 'simple',
                'complexity_weight' => 1.50,
                'processing_fee' => 50.00,
            ],
            [
                'name' => 'Certificate of Residency',
                'code' => 'CERT_RESIDENCY',
                'description' => 'Proof of residency within the barangay.',
                'complexity' => 'simple',
                'complexity_weight' => 1.50,
                'processing_fee' => 30.00,
            ],
            [
                'name' => 'Certificate of Indigency',
                'code' => 'CERT_INDIGENCY',
                'description' => 'Certificate proving financial incapacity for government assistance.',
                'complexity' => 'moderate',
                'complexity_weight' => 1.00,
                'processing_fee' => 0.00,
            ],
            [
                'name' => 'Business Permit Endorsement',
                'code' => 'BIZ_PERMIT',
                'description' => 'Endorsement for business permit application.',
                'complexity' => 'complex',
                'complexity_weight' => 0.50,
                'processing_fee' => 200.00,
            ],
            [
                'name' => 'Cedula (Community Tax Certificate)',
                'code' => 'CEDULA',
                'description' => 'Community tax certificate for residents.',
                'complexity' => 'simple',
                'complexity_weight' => 2.00,
                'processing_fee' => 10.00,
            ],
            [
                'name' => 'Barangay ID',
                'code' => 'BRGY_ID',
                'description' => 'Barangay identification card.',
                'complexity' => 'moderate',
                'complexity_weight' => 1.00,
                'processing_fee' => 100.00,
            ],
            [
                'name' => 'Certificate of Good Moral Character',
                'code' => 'GOOD_MORAL',
                'description' => 'Character reference certificate.',
                'complexity' => 'simple',
                'complexity_weight' => 1.50,
                'processing_fee' => 30.00,
            ],
            [
                'name' => 'Clearance for Employment',
                'code' => 'EMPLOYMENT_CLEARANCE',
                'description' => 'Clearance required for local employment.',
                'complexity' => 'simple',
                'complexity_weight' => 1.50,
                'processing_fee' => 50.00,
            ],
        ];

        foreach ($documents as $doc) {
            DB::table('document_types')->updateOrInsert(
                ['code' => $doc['code']],
                array_merge($doc, [
                    'requires_attachments' => false,
                    'is_active' => true,
                    'created_at' => now(),
                    'updated_at' => now(),
                ])
            );
        }
    }
}
