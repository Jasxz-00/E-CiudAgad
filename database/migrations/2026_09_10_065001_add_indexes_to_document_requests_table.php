<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('document_requests', function (Blueprint $table) {
            if (!Schema::hasIndex('document_requests', 'document_requests_status_index')) {
                $table->index('status');
            }
            if (!Schema::hasIndex('document_requests', 'document_requests_created_at_index')) {
                $table->index('created_at');
            }
            if (!Schema::hasIndex('document_requests', 'document_requests_resident_id_index')) {
                $table->index('resident_id');
            }
            if (!Schema::hasIndex('document_requests', 'document_requests_document_type_id_index')) {
                $table->index('document_type_id');
            }
            if (!Schema::hasIndex('document_requests', 'document_requests_purpose_id_index')) {
                $table->index('purpose_id');
            }
            if (!Schema::hasIndex('document_requests', 'document_requests_queue_number_index')) {
                $table->index('queue_number');
            }
            if (!Schema::hasIndex('document_requests', 'document_requests_control_number_index')) {
                $table->index('control_number');
            }
        });
    }

    public function down(): void
    {
        Schema::table('document_requests', function (Blueprint $table) {
            if (Schema::hasIndex('document_requests', 'document_requests_status_index')) {
                $table->dropIndex(['status']);
            }
            if (Schema::hasIndex('document_requests', 'document_requests_created_at_index')) {
                $table->dropIndex(['created_at']);
            }
            if (Schema::hasIndex('document_requests', 'document_requests_resident_id_index')) {
                $table->dropIndex(['resident_id']);
            }
            if (Schema::hasIndex('document_requests', 'document_requests_document_type_id_index')) {
                $table->dropIndex(['document_type_id']);
            }
            if (Schema::hasIndex('document_requests', 'document_requests_purpose_id_index')) {
                $table->dropIndex(['purpose_id']);
            }
            if (Schema::hasIndex('document_requests', 'document_requests_queue_number_index')) {
                $table->dropIndex(['queue_number']);
            }
            if (Schema::hasIndex('document_requests', 'document_requests_control_number_index')) {
                $table->dropIndex(['control_number']);
            }
        });
    }
};
