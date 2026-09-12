<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('audit_logs', function (Blueprint $table) {
            $columns = Schema::getColumnListing('audit_logs');
            if (!in_array('model_type', $columns)) {
                $table->string('model_type')->nullable()->after('subject_type');
            }
            if (!in_array('model_id', $columns)) {
                $table->unsignedBigInteger('model_id')->nullable()->after('model_type');
            }
        });
    }

    public function down(): void
    {
        Schema::table('audit_logs', function (Blueprint $table) {
            $columns = Schema::getColumnListing('audit_logs');
            if (in_array('model_type', $columns)) {
                $table->dropColumn('model_type');
            }
            if (in_array('model_id', $columns)) {
                $table->dropColumn('model_id');
            }
        });
    }
};
