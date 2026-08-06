<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('id_verifications', function (Blueprint $table) {
            $table->id();
            $table->foreignId('resident_id')->constrained()->onDelete('cascade');
            $table->string('id_type'); // phil_id, passport, umid, drivers_license, prc_id, postal_id, sss_id, tin_id, ibp_id, owowa_ofw_id, barangay_id, school_id
            $table->string('id_number');
            $table->string('file_path');
            $table->string('file_type'); // jpg, jpeg, png, pdf
            $table->integer('file_size');
            $table->boolean('is_verified')->default(false);
            $table->timestamp('verified_at')->nullable();
            $table->foreignId('verified_by')->nullable()->constrained('users')->onDelete('set null');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('id_verifications');
    }
};
