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
            ],
            [
                'name' => 'Certificate of Residency',
                'code' => 'CERT_RESIDENCY',
                'description' => 'Proof of residency within the barangay.',
                'complexity' => 'simple',
            ],
            [
                'name' => 'Certificate of Indigency',
                'code' => 'CERT_INDIGENCY',
                'description' => 'Certificate proving financial incapacity for government assistance.',
                'complexity' => 'moderate',
            ],
            [
                'name' => 'Certificate of Good Moral Character',
                'code' => 'CERT_GOOD_MORAL',
                'description' => 'Official document certifying good moral character.',
                'complexity' => 'simple',
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

        DB::table('document_types')->whereNotIn('code', ['BRGY_CLEARANCE', 'CERT_RESIDENCY', 'CERT_INDIGENCY', 'CERT_GOOD_MORAL'])->update(['is_active' => false]);
    }
}
