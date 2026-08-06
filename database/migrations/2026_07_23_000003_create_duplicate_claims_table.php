<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('duplicate_claims', function (Blueprint $table) {
            $table->id();
            $table->json('registration_data');
            $table->foreignId('matched_resident_id')->nullable()->constrained('residents')->onDelete('set null');
            $table->string('status')->default('pending');
            $table->text('staff_notes')->nullable();
            $table->foreignId('reviewed_by')->nullable()->constrained('users')->onDelete('set null');
            $table->timestamp('reviewed_at')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('duplicate_claims');
    }
};
