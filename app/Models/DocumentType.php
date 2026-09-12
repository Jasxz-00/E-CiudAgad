<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class DocumentType extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'code',
        'description',
        'complexity',
        'complexity_weight',
        'requires_attachments',
        'is_active',
    ];

    protected function casts(): array
    {
        return [
            'is_active' => 'boolean',
            'requires_attachments' => 'boolean',
            'complexity_weight' => 'decimal:2',
        ];
    }

    public function documentRequests()
    {
        return $this->hasMany(DocumentRequest::class);
    }

    public function purposes()
    {
        return $this->belongsToMany(RequestPurpose::class, 'document_type_purposes')->withTimestamps();
    }
}
