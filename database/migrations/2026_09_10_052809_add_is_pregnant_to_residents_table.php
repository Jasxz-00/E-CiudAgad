<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasColumn('residents', 'is_pregnant')) {
            Schema::table('residents', function (Blueprint $table) {
                $table->boolean('is_pregnant')->default(false)->after('person_status');
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasColumn('residents', 'is_pregnant')) {
            Schema::table('residents', function (Blueprint $table) {
                $table->dropColumn('is_pregnant');
            });
        }
    }
};
