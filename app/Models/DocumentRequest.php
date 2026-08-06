<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class DocumentRequest extends Model
{
    use HasFactory;

    protected $fillable = [
        'queue_number',
        'resident_id',
        'document_type_id',
        'purpose_id',
        'purpose_other',
        'status',
        'total_weight',
        'virtual_finish_time',
        'queue_position',
        'remarks',
        'rejection_reason',
        'processed_by',
        'processing_started_at',
        'completed_at',
    ];

    protected function casts(): array
    {
        return [
            'total_weight' => 'decimal:2',
            'virtual_finish_time' => 'decimal:6',
            'queue_position' => 'integer',
            'processing_started_at' => 'datetime',
            'completed_at' => 'datetime',
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

    public function documents()
    {
        return $this->hasMany(RequestDocument::class);
    }
}
