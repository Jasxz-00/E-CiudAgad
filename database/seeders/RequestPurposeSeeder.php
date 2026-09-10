<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class RequestPurposeSeeder extends Seeder
{
    public function run(): void
    {
        $purposes = [
            ['name' => 'Medical Assistance', 'code' => 'MEDICAL_ASSISTANCE', 'priority_weight' => 3.00],
            ['name' => 'Financial Assistance', 'code' => 'FINANCIAL_ASSISTANCE', 'priority_weight' => 3.00],
            ['name' => 'Enrollment', 'code' => 'ENROLLMENT', 'priority_weight' => 2.00],
            ['name' => 'Scholarship', 'code' => 'SCHOLARSHIP', 'priority_weight' => 2.00],
            ['name' => 'Voucher', 'code' => 'VOUCHER', 'priority_weight' => 2.00],
            ['name' => 'Local Employment', 'code' => 'LOCAL_EMPLOYMENT', 'priority_weight' => 2.00],
            ['name' => 'NBI Requirement', 'code' => 'NBI_REQUIREMENT', 'priority_weight' => 1.50],
            ['name' => 'Police Clearance Requirement', 'code' => 'POLICE_CLEARANCE', 'priority_weight' => 1.50],
            ['name' => 'Postal ID Requirement', 'code' => 'POSTAL_ID', 'priority_weight' => 1.50],
            ['name' => 'Bank/Loan Requirement', 'code' => 'BANK_LOAN', 'priority_weight' => 1.50],
            ['name' => 'Marriage Requirement', 'code' => 'MARRIAGE', 'priority_weight' => 2.00],
        ];

        foreach ($purposes as $purpose) {
            DB::table('request_purposes')->updateOrInsert(
                ['code' => $purpose['code']],
                array_merge($purpose, ['is_active' => true, 'created_at' => now(), 'updated_at' => now()])
            );
        }
    }
}