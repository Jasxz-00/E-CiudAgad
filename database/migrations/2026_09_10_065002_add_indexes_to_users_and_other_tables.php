<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            if (!Schema::hasIndex('users', 'users_tracking_number_index')) {
                $table->index('tracking_number');
            }
            if (!Schema::hasIndex('users', 'users_role_index')) {
                $table->index('role');
            }
            if (!Schema::hasIndex('users', 'users_is_active_index')) {
                $table->index('is_active');
            }
        });

        Schema::table('id_verifications', function (Blueprint $table) {
            if (!Schema::hasIndex('id_verifications', 'id_verifications_resident_id_index')) {
                $table->index('resident_id');
            }
            if (!Schema::hasIndex('id_verifications', 'id_verifications_is_verified_index')) {
                $table->index('is_verified');
            }
        });

        Schema::table('concerns', function (Blueprint $table) {
            if (!Schema::hasIndex('concerns', 'concerns_resident_id_index')) {
                $table->index('resident_id');
            }
            if (!Schema::hasIndex('concerns', 'concerns_status_index')) {
                $table->index('status');
            }
        });

        Schema::table('duplicate_claims', function (Blueprint $table) {
            if (!Schema::hasIndex('duplicate_claims', 'duplicate_claims_status_index')) {
                $table->index('status');
            }
            if (!Schema::hasIndex('duplicate_claims', 'duplicate_claims_matched_resident_id_index')) {
                $table->index('matched_resident_id');
            }
        });

        Schema::table('personnel_registrations', function (Blueprint $table) {
            if (!Schema::hasIndex('personnel_registrations', 'personnel_registrations_personnel_id_index')) {
                $table->index('personnel_id');
            }
            if (!Schema::hasIndex('personnel_registrations', 'personnel_registrations_resident_id_index')) {
                $table->index('resident_id');
            }
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            if (Schema::hasIndex('users', 'users_tracking_number_index')) {
                $table->dropIndex(['tracking_number']);
            }
            if (Schema::hasIndex('users', 'users_role_index')) {
                $table->dropIndex(['role']);
            }
            if (Schema::hasIndex('users', 'users_is_active_index')) {
                $table->dropIndex(['is_active']);
            }
        });

        Schema::table('id_verifications', function (Blueprint $table) {
            if (Schema::hasIndex('id_verifications', 'id_verifications_resident_id_index')) {
                $table->dropIndex(['resident_id']);
            }
            if (Schema::hasIndex('id_verifications', 'id_verifications_is_verified_index')) {
                $table->dropIndex(['is_verified']);
            }
        });

        Schema::table('concerns', function (Blueprint $table) {
            if (Schema::hasIndex('concerns', 'concerns_resident_id_index')) {
                $table->dropIndex(['resident_id']);
            }
            if (Schema::hasIndex('concerns', 'concerns_status_index')) {
                $table->dropIndex(['status']);
            }
        });

        Schema::table('duplicate_claims', function (Blueprint $table) {
            if (Schema::hasIndex('duplicate_claims', 'duplicate_claims_status_index')) {
                $table->dropIndex(['status']);
            }
            if (Schema::hasIndex('duplicate_claims', 'duplicate_claims_matched_resident_id_index')) {
                $table->dropIndex(['matched_resident_id']);
            }
        });

        Schema::table('personnel_registrations', function (Blueprint $table) {
            if (Schema::hasIndex('personnel_registrations', 'personnel_registrations_personnel_id_index')) {
                $table->dropIndex(['personnel_id']);
            }
            if (Schema::hasIndex('personnel_registrations', 'personnel_registrations_resident_id_index')) {
                $table->dropIndex(['resident_id']);
            }
        });
    }
};
