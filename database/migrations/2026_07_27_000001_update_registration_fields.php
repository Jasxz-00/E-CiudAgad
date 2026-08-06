<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        $driver = DB::getDriverName();

        // Update civil_status - remove enum restriction; use string column
        // ASSUMPTION: This replaces 'separated' with 'divorced' in the UI.
        // The DB stores any string value so no destructive ALTER is needed.

        // Add person_status column (single-select: pwd, senior, pregnant, regular)
        // ASSUMPTION: Single-select. If multi-select is needed (e.g., senior who is also PWD),
        // change to a JSON array or pivot table.
        if (! Schema::hasColumn('residents', 'person_status')) {
            Schema::table('residents', function (Blueprint $table) {
                $table->string('person_status', 50)->nullable()->after('civil_status');
            });
        }

        // Add status_verification_photo for PWD/Senior/Pregnant supporting documents
        if (! Schema::hasColumn('residents', 'status_verification_photo')) {
            Schema::table('residents', function (Blueprint $table) {
                $table->string('status_verification_photo', 255)->nullable()->after('proof_of_disability');
            });
        }

        // Add religion field
        if (! Schema::hasColumn('residents', 'religion')) {
            Schema::table('residents', function (Blueprint $table) {
                $table->string('religion', 100)->nullable()->after('occupation');
            });
        }

        // Add place_of_birth field
        if (! Schema::hasColumn('residents', 'place_of_birth')) {
            Schema::table('residents', function (Blueprint $table) {
                $table->string('place_of_birth', 255)->nullable()->after('birthdate');
            });
        }

        // Only run MySQL-specific ALTERs on MySQL
        if ($driver === 'mysql') {
            // Make subdivision nullable and remove default
            if (Schema::hasColumn('residents', 'subdivision')) {
                DB::statement('ALTER TABLE residents MODIFY COLUMN subdivision VARCHAR(255) NULL DEFAULT NULL');
            }

            // Update default for barangay to 'MOLINO I'
            if (Schema::hasColumn('residents', 'barangay')) {
                DB::statement("ALTER TABLE residents MODIFY COLUMN barangay VARCHAR(255) NULL DEFAULT 'MOLINO I'");
            }

            // Update default for street to 'MOLINO I' - ASSUMPTION: defaulting street to "Molino I" also
            if (Schema::hasColumn('residents', 'street')) {
                DB::statement("ALTER TABLE residents MODIFY COLUMN street VARCHAR(255) NULL DEFAULT 'MOLINO I'");
            }
        } else {
            // Non-MySQL drivers (e.g. the SQLite test database): keep schema consistent with MySQL
            if (Schema::hasColumn('residents', 'subdivision')) {
                Schema::table('residents', function (Blueprint $table) {
                    $table->string('subdivision', 255)->nullable()->default(null)->change();
                });
            }
        }
    }

    public function down(): void
    {
        if (Schema::hasColumn('residents', 'person_status')) {
            Schema::table('residents', function (Blueprint $table) {
                $table->dropColumn('person_status');
            });
        }
        if (Schema::hasColumn('residents', 'status_verification_photo')) {
            Schema::table('residents', function (Blueprint $table) {
                $table->dropColumn('status_verification_photo');
            });
        }
        if (Schema::hasColumn('residents', 'religion')) {
            Schema::table('residents', function (Blueprint $table) {
                $table->dropColumn('religion');
            });
        }
        if (Schema::hasColumn('residents', 'place_of_birth')) {
            Schema::table('residents', function (Blueprint $table) {
                $table->dropColumn('place_of_birth');
            });
        }
    }
};
