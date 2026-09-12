<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasColumn('audit_logs', 'subject_type') || Schema::hasColumn('audit_logs', 'subject_id')) {
            return;
        }

        Schema::table('audit_logs', function (Blueprint $table) {
            $table->string('subject_type')->nullable()->after('action');
            $table->unsignedBigInteger('subject_id')->nullable()->after('subject_type');

            $table->index(['subject_type', 'subject_id'], 'idx_audit_subject');
        });
    }

    public function down(): void
    {
        if (! Schema::hasColumn('audit_logs', 'subject_type')) {
            return;
        }

        Schema::table('audit_logs', function (Blueprint $table) {
            $table->dropIndex('idx_audit_subject');
            $table->dropColumn(['subject_type', 'subject_id']);
        });
    }
};