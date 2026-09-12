<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('document_type_purposes')) {
            Schema::create('document_type_purposes', function (Blueprint $table) {
                $table->id();
                $table->foreignId('document_type_id')->constrained('document_types')->onDelete('cascade');
                $table->foreignId('request_purpose_id')->constrained('request_purposes')->onDelete('cascade');
                $table->timestamps();

                $table->unique(['document_type_id', 'request_purpose_id'], 'document_type_purposes_unique');
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('document_type_purposes');
    }
};