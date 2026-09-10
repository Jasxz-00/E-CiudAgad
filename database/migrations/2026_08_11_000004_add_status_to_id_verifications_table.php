<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('id_verifications', function (Blueprint $table) {
            $table->string('status')->default('pending')->after('id_type');
            $table->string('rejected_reason')->nullable()->after('verified_by');
            $table->timestamp('admin_reviewed_at')->nullable()->after('verified_by');
        });

        DB::table('id_verifications')->where('is_verified', true)->update(['status' => 'verified']);
    }

    public function down(): void
    {
        Schema::table('id_verifications', function (Blueprint $table) {
            $table->dropColumn(['status', 'rejected_reason', 'admin_reviewed_at']);
        });
    }
};