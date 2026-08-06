<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class RequestPurposeSeeder extends Seeder
{
    public function run(): void
    {
        $purposes = [
            ['name' => 'Medical Purposes', 'code' => 'MEDICAL', 'description' => 'For medical consultations, hospital requirements, or health-related needs.', 'priority_weight' => 3.00],
            ['name' => 'Emergency Purposes', 'code' => 'EMERGENCY', 'description' => 'For urgent and emergency situations requiring immediate documentation.', 'priority_weight' => 3.00],
            ['name' => 'Employment', 'code' => 'EMPLOYMENT', 'description' => 'For local or overseas employment requirements.', 'priority_weight' => 2.00],
            ['name' => 'Scholarship', 'code' => 'SCHOLARSHIP', 'description' => 'For educational scholarship applications.', 'priority_weight' => 2.00],
            ['name' => 'Government Requirement', 'code' => 'GOVT_REQ', 'description' => 'For government agency requirements and transactions.', 'priority_weight' => 1.50],
            ['name' => 'Loan Application', 'code' => 'LOAN', 'description' => 'For financial loan applications from banks or institutions.', 'priority_weight' => 1.50],
            ['name' => 'Business Requirement', 'code' => 'BUSINESS', 'description' => 'For business registration, permits, or related needs.', 'priority_weight' => 1.00],
            ['name' => 'Personal Use', 'code' => 'PERSONAL', 'description' => 'For personal reasons not covered by other categories.', 'priority_weight' => 1.00],
            ['name' => 'Others', 'code' => 'OTHERS', 'description' => 'Other purposes not listed above.', 'priority_weight' => 0.50],
        ];

        foreach ($purposes as $purpose) {
            DB::table('request_purposes')->updateOrInsert(
                ['code' => $purpose['code']],
                array_merge($purpose, ['is_active' => true, 'created_at' => now(), 'updated_at' => now()])
            );
        }
    }
}
