<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('id_verifications', function (Blueprint $table) {
            $table->text('id_number')->nullable()->change();
            $table->string('back_file_path')->nullable()->after('file_size');
            $table->string('back_file_type')->nullable()->after('back_file_path');
            $table->integer('back_file_size')->nullable()->after('back_file_type');
        });
    }

    public function down(): void
    {
        Schema::table('id_verifications', function (Blueprint $table) {
            $table->dropColumn(['back_file_path', 'back_file_type', 'back_file_size']);
            $table->text('id_number')->nullable(false)->change();
        });
    }
};
