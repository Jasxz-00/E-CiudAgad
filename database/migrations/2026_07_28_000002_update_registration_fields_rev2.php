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

        // Add middle_name_none boolean (2.4 - None option for middle name)
        if (! Schema::hasColumn('residents', 'middle_name_none')) {
            Schema::table('residents', function (Blueprint $table) {
                $table->boolean('middle_name_none')->default(false)->after('middle_name');
            });
        }

        // Add road field (2.6 - "Molino Road" default)
        if (! Schema::hasColumn('residents', 'road')) {
            Schema::table('residents', function (Blueprint $table) {
                $table->string('road', 255)->nullable()->after('street');
            });
        }

        // Change encrypted columns to text to support Laravel's encrypted cast
        if (Schema::hasColumn('residents', 'emergency_contact')) {
            Schema::table('residents', function (Blueprint $table) {
                $table->text('emergency_contact')->nullable()->change();
            });
        }
        if (Schema::hasColumn('residents', 'place_of_birth')) {
            Schema::table('residents', function (Blueprint $table) {
                $table->text('place_of_birth')->nullable()->change();
            });
        }

        // Only run MySQL-specific ALTERs on MySQL
        if ($driver === 'mysql') {
            if (Schema::hasColumn('residents', 'road')) {
                DB::statement("ALTER TABLE residents MODIFY COLUMN road VARCHAR(255) NULL DEFAULT 'Molino Road'");
            }
        }
    }

    public function down(): void
    {
        if (Schema::hasColumn('residents', 'middle_name_none')) {
            Schema::table('residents', function (Blueprint $table) {
                $table->dropColumn('middle_name_none');
            });
        }
        if (Schema::hasColumn('residents', 'road')) {
            Schema::table('residents', function (Blueprint $table) {
                $table->dropColumn('road');
            });
        }
    }
};
