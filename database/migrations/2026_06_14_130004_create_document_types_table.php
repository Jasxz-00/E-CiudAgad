<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('document_types', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('code')->unique(); // e.g., BRGY_CLEARANCE, CERT_RESIDENCY
            $table->text('description')->nullable();
            $table->string('complexity')->default('simple'); // simple, moderate, complex
            $table->decimal('complexity_weight', 5, 2)->default(1.00);

            $table->boolean('requires_attachments')->default(false);
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('document_types');
    }
};
