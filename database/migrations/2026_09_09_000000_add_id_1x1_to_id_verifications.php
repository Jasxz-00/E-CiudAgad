<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('id_verifications', function (Blueprint $table) {
            $table->string('id_1x1_path')->nullable()->after('back_file_size');
            $table->string('id_1x1_type')->nullable()->after('id_1x1_path');
            $table->integer('id_1x1_size')->nullable()->after('id_1x1_type');
        });
    }

    public function down(): void
    {
        Schema::table('id_verifications', function (Blueprint $table) {
            $table->dropColumn(['id_1x1_path', 'id_1x1_type', 'id_1x1_size']);
        });
    }
};
