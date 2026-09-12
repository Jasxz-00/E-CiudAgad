<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class DocumentTypePurposeSeeder extends Seeder
{
    public function run(): void
    {
        $mapping = [
            'BRGY_CLEARANCE' => [
                'EDUCATION_SCHOOL_REQUIREMENT',
                'SCHOLARSHIP',
                'FINANCIAL_LOAN_TRANSACTION',
                'MEDICAL_ASSISTANCE',
                'FINANCIAL_ASSISTANCE',
                'LEGAL_REQUIREMENT',
            ],
            'CERT_RESIDENCY' => [
                'EDUCATION_SCHOOL_REQUIREMENT',
                'SCHOLARSHIP',
                'FINANCIAL_LOAN_TRANSACTION',
                'MEDICAL_ASSISTANCE',
                'FINANCIAL_ASSISTANCE',
                'LEGAL_REQUIREMENT',
                'PROOF_OF_ADDRESS',
            ],
            'CERT_INDIGENCY' => [
                'EDUCATION_SCHOOL_REQUIREMENT',
                'SCHOLARSHIP',
                'FINANCIAL_LOAN_TRANSACTION',
                'MEDICAL_ASSISTANCE',
                'FINANCIAL_ASSISTANCE',
                'LEGAL_REQUIREMENT',
            ],
            'CERT_GOOD_MORAL' => [
                'EMPLOYMENT',
                'EDUCATION',
            ],
        ];

        foreach ($mapping as $documentCode => $purposeCodes) {
            $documentTypeId = DB::table('document_types')->where('code', $documentCode)->value('id');

            if (! $documentTypeId) {
                continue;
            }

            foreach ($purposeCodes as $purposeCode) {
                $purposeId = DB::table('request_purposes')->where('code', $purposeCode)->value('id');

                if (! $purposeId) {
                    continue;
                }

                DB::table('document_type_purposes')->updateOrInsert(
                    [
                        'document_type_id' => $documentTypeId,
                        'request_purpose_id' => $purposeId,
                    ],
                    [
                        'created_at' => now(),
                        'updated_at' => now(),
                    ]
                );
            }
        }

        // Remove stale mappings for deactivated document types or purposes.
        $validDocumentIds = DB::table('document_types')
            ->whereIn('code', array_keys($mapping))
            ->pluck('id');
        $validPurposeCodes = array_values(array_unique(array_merge(...array_values($mapping))));
        $validPurposeIds = DB::table('request_purposes')
            ->whereIn('code', $validPurposeCodes)
            ->pluck('id');
        DB::table('document_type_purposes')
            ->whereNotIn('document_type_id', $validDocumentIds)
            ->orWhereNotIn('request_purpose_id', $validPurposeIds)
            ->delete();
    }
}