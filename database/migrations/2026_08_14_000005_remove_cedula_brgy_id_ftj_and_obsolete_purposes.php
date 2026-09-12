<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        DB::table('document_types')
            ->whereIn('code', ['CEDULA', 'BRGY_ID', 'FTJ_CERT'])
            ->delete();

        DB::table('request_purposes')
            ->whereIn('code', ['PERSONAL', 'EMERGENCY', 'SCHOOL'])
            ->delete();

        DB::table('request_purposes')
            ->where('code', 'SCHOLARSHIP')
            ->update(['name' => 'Scholarship / School Requirements']);

        DB::table('wfq_configurations')
            ->where('config_type', 'purpose_weight')
            ->whereIn('config_key', ['PERSONAL', 'EMERGENCY'])
            ->delete();
    }

    public function down(): void
    {
        $documents = [
            ['name' => 'Cedula (Community Tax Certificate)', 'code' => 'CEDULA', 'description' => 'Community tax certificate for residents.', 'complexity' => 'simple', 'complexity_weight' => 2.00],
            ['name' => 'Barangay ID', 'code' => 'BRGY_ID', 'description' => 'Barangay identification card.', 'complexity' => 'moderate', 'complexity_weight' => 1.00],
            ['name' => 'First Time Job Seeker Certificate', 'code' => 'FTJ_CERT', 'description' => 'Certificate under RA 11261 exempting first-time job seekers from documentary fees.', 'complexity' => 'simple', 'complexity_weight' => 1.50],
        ];

        foreach ($documents as $doc) {
            DB::table('document_types')->updateOrInsert(
                ['code' => $doc['code']],
                array_merge($doc, ['requires_attachments' => false, 'is_active' => true, 'created_at' => now(), 'updated_at' => now()])
            );
        }

        $purposes = [
            ['name' => 'Personal Use', 'code' => 'PERSONAL', 'description' => 'For personal reasons not covered by other categories.', 'priority_weight' => 1.00],
            ['name' => 'Emergency Purposes', 'code' => 'EMERGENCY', 'description' => 'For urgent and emergency situations requiring immediate documentation.', 'priority_weight' => 3.00],
            ['name' => 'School Requirements', 'code' => 'SCHOOL', 'description' => 'For school enrollment, transfer, or other school requirements.', 'priority_weight' => 2.00],
        ];

        foreach ($purposes as $purpose) {
            DB::table('request_purposes')->updateOrInsert(
                ['code' => $purpose['code']],
                array_merge($purpose, ['is_active' => true, 'created_at' => now(), 'updated_at' => now()])
            );
        }

        DB::table('request_purposes')
            ->where('code', 'SCHOLARSHIP')
            ->update(['name' => 'Scholarship']);

        $wfq = [
            ['config_type' => 'purpose_weight', 'config_key' => 'PERSONAL', 'config_value' => 'Personal Use', 'weight' => 1.0000],
            ['config_type' => 'purpose_weight', 'config_key' => 'EMERGENCY', 'config_value' => 'Emergency Purposes', 'weight' => 3.0000],
        ];

        foreach ($wfq as $config) {
            DB::table('wfq_configurations')->updateOrInsert(
                ['config_type' => $config['config_type'], 'config_key' => $config['config_key']],
                array_merge($config, ['is_active' => true, 'created_at' => now(), 'updated_at' => now()])
            );
        }
    }
};
