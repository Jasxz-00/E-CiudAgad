<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class IssuedDocument extends Model
{
    use HasFactory;

    protected $fillable = [
        'document_request_id',
        'document_type_id',
        'resident_id',
        'control_number',
        'document_data',
        'svg_path',
        'pdf_path',
        'pdf_generated',
        'generated_at',
        'printed_at',
        'print_count',
        'generated_by',
    ];

    protected function casts(): array
    {
        return [
            'document_data' => 'array',
            'pdf_generated' => 'boolean',
            'generated_at' => 'datetime',
            'printed_at' => 'datetime',
            'print_count' => 'integer',
        ];
    }

    public function documentRequest()
    {
        return $this->belongsTo(DocumentRequest::class);
    }

    public function documentType()
    {
        return $this->belongsTo(DocumentType::class);
    }

    public function resident()
    {
        return $this->belongsTo(Resident::class);
    }

    public function generatedBy()
    {
        return $this->belongsTo(User::class, 'generated_by');
    }
}