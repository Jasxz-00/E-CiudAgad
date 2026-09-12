<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class WFQConfigurationSeeder extends Seeder
{
    public function run(): void
    {
        $configs = [
            // Resident category weights (Wr) — single source: config/queue.php
            ['config_type' => 'category_weight', 'config_key' => 'regular', 'config_value' => 'Regular', 'weight' => 1.0000],
            ['config_type' => 'category_weight', 'config_key' => 'pwd', 'config_value' => 'Person with Disability', 'weight' => 4.0000],
            ['config_type' => 'category_weight', 'config_key' => 'pregnant', 'config_value' => 'Pregnant', 'weight' => 3.0000],
            ['config_type' => 'category_weight', 'config_key' => 'senior', 'config_value' => 'Senior Citizen', 'weight' => 2.0000],
            // Complexity weights (unused by the current formula; kept for the admin matrix)
            ['config_type' => 'complexity_weight', 'config_key' => 'simple', 'config_value' => 'Simple', 'weight' => 1.0000],
            ['config_type' => 'complexity_weight', 'config_key' => 'moderate', 'config_value' => 'Moderate', 'weight' => 1.0000],
            ['config_type' => 'complexity_weight', 'config_key' => 'complex', 'config_value' => 'Complex', 'weight' => 1.0000],
            // Purpose weights (Wp) keyed by request_purposes.code
            ['config_type' => 'purpose_weight', 'config_key' => 'MEDICAL_ASSISTANCE', 'config_value' => 'Medical Assistance', 'weight' => 4.0000],
            ['config_type' => 'purpose_weight', 'config_key' => 'FINANCIAL_ASSISTANCE', 'config_value' => 'Financial Assistance', 'weight' => 3.0000],
            ['config_type' => 'purpose_weight', 'config_key' => 'EDUCATION_SCHOOL_REQUIREMENT', 'config_value' => 'Education/School Requirement', 'weight' => 2.0000],
            ['config_type' => 'purpose_weight', 'config_key' => 'SCHOLARSHIP', 'config_value' => 'Scholarship', 'weight' => 2.0000],
            ['config_type' => 'purpose_weight', 'config_key' => 'LEGAL_REQUIREMENT', 'config_value' => 'Legal Requirement', 'weight' => 2.0000],
            ['config_type' => 'purpose_weight', 'config_key' => 'EMPLOYMENT', 'config_value' => 'Employment', 'weight' => 2.0000],
            ['config_type' => 'purpose_weight', 'config_key' => 'EDUCATION', 'config_value' => 'Education', 'weight' => 2.0000],
            ['config_type' => 'purpose_weight', 'config_key' => 'FINANCIAL_LOAN_TRANSACTION', 'config_value' => 'Financial/Loan Transaction', 'weight' => 1.0000],
            ['config_type' => 'purpose_weight', 'config_key' => 'PROOF_OF_ADDRESS', 'config_value' => 'Proof of Address', 'weight' => 1.0000],
        ];

        $obsoleteKeys = [
            'purpose_weight' => ['MEDICAL', 'EMERGENCY', 'GOVT_REQ', 'LOAN', 'BUSINESS', 'PERSONAL', 'OTHERS'],
        ];

        foreach ($configs as $config) {
            DB::table('wfq_configurations')->updateOrInsert(
                ['config_type' => $config['config_type'], 'config_key' => $config['config_key']],
                array_merge($config, ['is_active' => true, 'created_at' => now(), 'updated_at' => now()])
            );
        }

        foreach ($obsoleteKeys as $type => $keys) {
            DB::table('wfq_configurations')
                ->where('config_type', $type)
                ->whereIn('config_key', $keys)
                ->update(['is_active' => false, 'updated_at' => now()]);
        }
    }
}