<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('issued_documents', function (Blueprint $table) {
            $table->id();
            $table->foreignId('document_request_id')->unique()->constrained()->cascadeOnDelete();
            $table->foreignId('document_type_id')->constrained();
            $table->foreignId('resident_id')->constrained()->cascadeOnDelete();
            $table->string('control_number')->unique();
            $table->json('document_data');
            $table->string('svg_path')->nullable();
            $table->string('pdf_path')->nullable();
            $table->boolean('pdf_generated')->default(false);
            $table->timestamp('generated_at')->nullable();
            $table->timestamp('printed_at')->nullable();
            $table->integer('print_count')->default(0);
            $table->foreignId('generated_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('issued_documents');
    }
};