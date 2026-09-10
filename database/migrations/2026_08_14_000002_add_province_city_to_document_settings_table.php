<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('document_settings', function (Blueprint $table) {
            $table->string('province_name')->nullable()->after('barangay_name');
            $table->string('city_name')->nullable()->after('province_name');
        });
    }

    public function down(): void
    {
        Schema::table('document_settings', function (Blueprint $table) {
            $table->dropColumn(['province_name', 'city_name']);
        });
    }
};
