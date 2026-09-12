<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('queue_schedule_holidays', function (Blueprint $table) {
            $table->id();
            $table->foreignId('queue_schedule_id')->constrained()->cascadeOnDelete();
            $table->date('holiday_date');
            $table->string('name');
            $table->boolean('is_active')->default(true);
            $table->timestamps();

            $table->unique(['queue_schedule_id', 'holiday_date']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('queue_schedule_holidays');
    }
};