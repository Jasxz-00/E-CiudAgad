<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('wfq_configurations', function (Blueprint $table) {
            $table->id();
            $table->string('config_type'); // category_weight, complexity_weight, purpose_weight
            $table->string('config_key'); // e.g., 'regular', 'senior', 'simple', 'complex', 'employment'
            $table->string('config_value');
            $table->decimal('weight', 8, 4)->default(1.0000);
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('wfq_configurations');
    }
};
