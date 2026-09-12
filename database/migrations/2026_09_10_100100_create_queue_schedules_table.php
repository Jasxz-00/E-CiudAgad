<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('queue_schedules', function (Blueprint $table) {
            $table->id();
            $table->time('cut_off_time')->nullable();
            $table->time('opening_time')->nullable();
            $table->unsignedInteger('max_requests_per_day')->nullable();
            $table->boolean('cut_off_enabled')->default(true);
            $table->boolean('is_active')->default(true);
            $table->string('timezone', 50)->default('Asia/Manila');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('queue_schedules');
    }
};