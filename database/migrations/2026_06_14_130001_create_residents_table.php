<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('residents', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->string('first_name');
            $table->string('last_name');
            $table->string('middle_name')->nullable();
            $table->string('suffix')->nullable();
            $table->date('birthdate');
            $table->integer('age');
            $table->string('gender');
            $table->string('civil_status')->nullable();
            $table->string('nationality')->default('FILIPINO');
            $table->string('building_no')->nullable();
            $table->string('unit_no')->nullable();
            $table->string('street')->nullable();
            $table->string('road')->nullable();
            $table->string('subdivision')->default('PHASE 1');
            $table->string('barangay')->default('CIUDAD DE STRIKE');
            $table->string('city')->default('BACOOR CITY');
            $table->string('province')->default('CAVITE');
            $table->string('zip_code')->nullable();
            $table->string('contact_number');
            $table->string('emergency_contact');
            $table->string('category')->default('regular'); // regular, pwd, pregnant, senior
            $table->text('category_remarks')->nullable();
            $table->string('proof_of_disability')->nullable();
            $table->timestamp('verified_at')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('residents');
    }
};
