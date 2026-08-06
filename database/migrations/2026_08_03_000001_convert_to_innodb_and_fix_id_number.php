<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    protected array $tables = [
        'announcements',
        'audit_logs',
        'cache',
        'cache_locks',
        'concerns',
        'document_requests',
        'document_types',
        'duplicate_claims',
        'failed_jobs',
        'id_verifications',
        'jobs',
        'job_batches',
        'migrations',
        'model_has_permissions',
        'model_has_roles',
        'password_reset_tokens',
        'permissions',
        'personnel_registrations',
        'request_documents',
        'request_purposes',
        'residents',
        'resident_categories',
        'roles',
        'role_has_permissions',
        'sessions',
        'users',
        'wfq_configurations',
    ];

    public function up(): void
    {
        $driver = DB::getDriverName();

        if ($driver !== 'mysql') {
            return;
        }

        // 1. Convert every table to InnoDB so transactions, rollbacks, and foreign keys actually work.
        foreach ($this->tables as $table) {
            if (Schema::hasTable($table)) {
                DB::statement("ALTER TABLE `{$table}` ENGINE = InnoDB");
            }
        }

        // 2. id_number is stored encrypted (Laravel 'encrypted' cast); the ciphertext
        //    overflows VARCHAR(125). Widen to TEXT like other encrypted columns.
        if (Schema::hasTable('id_verifications') && Schema::hasColumn('id_verifications', 'id_number')) {
            Schema::table('id_verifications', function (Blueprint $table) {
                $table->text('id_number')->nullable()->change();
            });
        }

        // 3. Re-add the foreign key constraints that MyISAM silently dropped on table creation.
        $this->ensureForeignKey('residents', 'residents_user_id_foreign', ['user_id'], 'users', ['id'], 'CASCADE');
        $this->ensureForeignKey('id_verifications', 'id_verifications_resident_id_foreign', ['resident_id'], 'residents', ['id'], 'CASCADE');
        $this->ensureForeignKey('id_verifications', 'id_verifications_verified_by_foreign', ['verified_by'], 'users', ['id'], 'SET NULL');
        $this->ensureForeignKey('document_requests', 'document_requests_resident_id_foreign', ['resident_id'], 'residents', ['id'], 'CASCADE');
        $this->ensureForeignKey('document_requests', 'document_requests_document_type_id_foreign', ['document_type_id'], 'document_types', ['id'], 'CASCADE');
        $this->ensureForeignKey('document_requests', 'document_requests_purpose_id_foreign', ['purpose_id'], 'request_purposes', ['id'], 'CASCADE');
        $this->ensureForeignKey('document_requests', 'document_requests_processed_by_foreign', ['processed_by'], 'users', ['id'], 'SET NULL');
        $this->ensureForeignKey('request_documents', 'request_documents_document_request_id_foreign', ['document_request_id'], 'document_requests', ['id'], 'CASCADE');
        $this->ensureForeignKey('concerns', 'concerns_resident_id_foreign', ['resident_id'], 'residents', ['id'], 'CASCADE');
        $this->ensureForeignKey('personnel_registrations', 'personnel_registrations_personnel_id_foreign', ['personnel_id'], 'users', ['id'], 'CASCADE');
        $this->ensureForeignKey('personnel_registrations', 'personnel_registrations_resident_id_foreign', ['resident_id'], 'residents', ['id'], 'SET NULL');
        $this->ensureForeignKey('duplicate_claims', 'duplicate_claims_matched_resident_id_foreign', ['matched_resident_id'], 'residents', ['id'], 'SET NULL');
        $this->ensureForeignKey('duplicate_claims', 'duplicate_claims_reviewed_by_foreign', ['reviewed_by'], 'users', ['id'], 'SET NULL');
        $this->ensureForeignKey('audit_logs', 'audit_logs_user_id_foreign', ['user_id'], 'users', ['id'], 'SET NULL');
        $this->ensureForeignKey('model_has_roles', 'model_has_roles_role_id_foreign', ['role_id'], 'roles', ['id'], 'CASCADE');
        $this->ensureForeignKey('model_has_permissions', 'model_has_permissions_permission_id_foreign', ['permission_id'], 'permissions', ['id'], 'CASCADE');
        $this->ensureForeignKey('role_has_permissions', 'role_has_permissions_permission_id_foreign', ['permission_id'], 'permissions', ['id'], 'CASCADE');
        $this->ensureForeignKey('role_has_permissions', 'role_has_permissions_role_id_foreign', ['role_id'], 'roles', ['id'], 'CASCADE');
    }

    protected function ensureForeignKey(
        string $table,
        string $constraintName,
        array $columns,
        string $referencesTable,
        array $references,
        string $onDelete
    ): void {
        if (! Schema::hasTable($table) || ! Schema::hasTable($referencesTable)) {
            return;
        }

        $exists = DB::table('information_schema.TABLE_CONSTRAINTS')
            ->where('TABLE_SCHEMA', DB::getDatabaseName())
            ->where('TABLE_NAME', $table)
            ->where('CONSTRAINT_NAME', $constraintName)
            ->where('CONSTRAINT_TYPE', 'FOREIGN KEY')
            ->exists();

        if ($exists) {
            return;
        }

        Schema::table($table, function (Blueprint $blueprint) use ($columns, $referencesTable, $references, $onDelete) {
            $foreign = $blueprint->foreign($columns)
                ->references($references)
                ->on($referencesTable);

            if ($onDelete === 'CASCADE') {
                $foreign->cascadeOnDelete();
            } else {
                $foreign->nullOnDelete();
            }
        });
    }

    public function down(): void
    {
        // Intentionally no-op: converting back to MyISAM would silently break
        // transactions and foreign keys again. Requires a manual decision.
    }
};
