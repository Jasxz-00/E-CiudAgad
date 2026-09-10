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
            if (!in_array('subject_type', $columns)) {
                $table->string('subject_type')->after('action')->default('');
            }
            if (!in_array('subject_id', $columns)) {
                $table->unsignedBigInteger('subject_id')->after('subject_type')->default(0);
            }
            if (!Schema::hasIndex('audit_logs', 'audit_logs_subject_type_subject_id_index')) {
                $table->index(['subject_type', 'subject_id']);
            }
        });
    }

    public function down(): void
    {
        Schema::table('audit_logs', function (Blueprint $table) {
            if (Schema::hasIndex('audit_logs', 'audit_logs_subject_type_subject_id_index')) {
                $table->dropIndex(['subject_type', 'subject_id']);
            }
            $columns = Schema::getColumnListing('audit_logs');
            if (in_array('subject_type', $columns)) {
                $table->dropColumn('subject_type');
            }
            if (in_array('subject_id', $columns)) {
                $table->dropColumn('subject_id');
            }
        });
    }
};
