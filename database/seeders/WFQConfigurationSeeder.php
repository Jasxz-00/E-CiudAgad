<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class WFQConfigurationSeeder extends Seeder
{
    public function run(): void
    {
        $configs = [
            // Category weights
            ['config_type' => 'category_weight', 'config_key' => 'regular', 'config_value' => 'Regular', 'weight' => 1.0000],
            ['config_type' => 'category_weight', 'config_key' => 'pwd', 'config_value' => 'Person with Disability', 'weight' => 3.0000],
            ['config_type' => 'category_weight', 'config_key' => 'pregnant', 'config_value' => 'Pregnant', 'weight' => 2.5000],
            ['config_type' => 'category_weight', 'config_key' => 'senior', 'config_value' => 'Senior Citizen', 'weight' => 2.5000],
            // Complexity weights
            ['config_type' => 'complexity_weight', 'config_key' => 'simple', 'config_value' => 'Simple', 'weight' => 2.0000],
            ['config_type' => 'complexity_weight', 'config_key' => 'moderate', 'config_value' => 'Moderate', 'weight' => 1.0000],
            ['config_type' => 'complexity_weight', 'config_key' => 'complex', 'config_value' => 'Complex', 'weight' => 0.5000],
            // Purpose priority weights
            ['config_type' => 'purpose_weight', 'config_key' => 'MEDICAL', 'config_value' => 'Medical Purposes', 'weight' => 3.0000],
            ['config_type' => 'purpose_weight', 'config_key' => 'EMERGENCY', 'config_value' => 'Emergency Purposes', 'weight' => 3.0000],
            ['config_type' => 'purpose_weight', 'config_key' => 'EMPLOYMENT', 'config_value' => 'Employment', 'weight' => 2.0000],
            ['config_type' => 'purpose_weight', 'config_key' => 'SCHOLARSHIP', 'config_value' => 'Scholarship', 'weight' => 2.0000],
            ['config_type' => 'purpose_weight', 'config_key' => 'GOVT_REQ', 'config_value' => 'Government Requirement', 'weight' => 1.5000],
            ['config_type' => 'purpose_weight', 'config_key' => 'LOAN', 'config_value' => 'Loan Application', 'weight' => 1.5000],
            ['config_type' => 'purpose_weight', 'config_key' => 'BUSINESS', 'config_value' => 'Business Requirement', 'weight' => 1.0000],
            ['config_type' => 'purpose_weight', 'config_key' => 'PERSONAL', 'config_value' => 'Personal Use', 'weight' => 1.0000],
            ['config_type' => 'purpose_weight', 'config_key' => 'OTHERS', 'config_value' => 'Others', 'weight' => 0.5000],
        ];

        foreach ($configs as $config) {
            DB::table('wfq_configurations')->updateOrInsert(
                ['config_type' => $config['config_type'], 'config_key' => $config['config_key']],
                array_merge($config, ['is_active' => true, 'created_at' => now(), 'updated_at' => now()])
            );
        }
    }
}
