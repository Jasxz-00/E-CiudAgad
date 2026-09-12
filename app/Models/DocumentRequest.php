<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class DocumentRequest extends Model
{
    use HasFactory;

    protected $fillable = [
        'control_number',
        'queue_number',
        'qr_code',
        'verification_token',
        'resident_id',
        'document_type_id',
        'purpose_id',
        'purpose_other',
        'status',
        'total_weight',
        'resident_weight',
        'purpose_weight',
        'virtual_finish_time',
        'queue_position',
        'service_date',
        'scheduled_after_cutoff',
        'remarks',
        'rejection_reason',
        'processed_by',
        'processing_started_at',
        'completed_at',
        'expires_at',
    ];

    protected function casts(): array
    {
        return [
            'total_weight' => 'decimal:2',
            'resident_weight' => 'decimal:4',
            'purpose_weight' => 'decimal:4',
            'virtual_finish_time' => 'decimal:6',
            'queue_position' => 'integer',
            'service_date' => 'date',
            'scheduled_after_cutoff' => 'boolean',
            'processing_started_at' => 'datetime',
            'completed_at' => 'datetime',
            'expires_at' => 'datetime',
        ];
    }

    public function resident()
    {
        return $this->belongsTo(Resident::class);
    }

    public function documentType()
    {
        return $this->belongsTo(DocumentType::class);
    }

    public function purpose()
    {
        return $this->belongsTo(RequestPurpose::class, 'purpose_id');
    }

    public function processedBy()
    {
        return $this->belongsTo(User::class, 'processed_by');
    }

    public function issuedDocument()
    {
        return $this->hasOne(IssuedDocument::class);
    }

    public function idVerifications()
    {
        return $this->hasMany(IdVerification::class);
    }

    public function documents()
    {
        return $this->hasMany(RequestDocument::class);
    }

    public function auditLogs()
    {
        return $this->morphMany(AuditLog::class, 'subject');
    }
}
