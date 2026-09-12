<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('document_requests', function (Blueprint $table) {
            $table->date('service_date')->nullable()->after('queue_position');
            $table->boolean('scheduled_after_cutoff')->default(false)->after('service_date');
            $table->string('verification_token', 64)->nullable()->unique()->after('qr_code');
            $table->decimal('resident_weight', 8, 4)->nullable()->after('total_weight');
            $table->decimal('purpose_weight', 8, 4)->nullable()->after('resident_weight');
            $table->index(['service_date', 'status', 'virtual_finish_time'], 'idx_docreq_svcdate_status_vft');
        });

        DB::statement("UPDATE document_requests SET service_date = DATE(created_at) WHERE service_date IS NULL");

        DB::table('document_requests')
            ->whereNull('verification_token')
            ->select('id')
            ->orderBy('id')
            ->chunkById(500, function ($rows) {
                foreach ($rows as $row) {
                    DB::table('document_requests')
                        ->where('id', $row->id)
                        ->update(['verification_token' => Str::random(32)]);
                }
            });
    }

    public function down(): void
    {
        Schema::table('document_requests', function (Blueprint $table) {
            $table->dropIndex('idx_docreq_svcdate_status_vft');
            $table->dropColumn([
                'service_date',
                'scheduled_after_cutoff',
                'verification_token',
                'resident_weight',
                'purpose_weight',
            ]);
        });
    }
};