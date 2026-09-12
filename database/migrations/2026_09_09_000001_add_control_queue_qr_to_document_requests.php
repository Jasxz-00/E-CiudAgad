<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('document_requests', function (Blueprint $table) {
            $table->string('control_number')->unique()->nullable()->after('id');
            $table->string('qr_code')->nullable()->after('queue_number');
            $table->timestamp('expires_at')->nullable()->after('completed_at');
        });
    }

    public function down(): void
    {
        Schema::table('document_requests', function (Blueprint $table) {
            $table->dropColumn(['control_number', 'qr_code', 'expires_at']);
        });
    }
};
