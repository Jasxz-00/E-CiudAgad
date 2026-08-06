<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class RequestDocument extends Model
{
    use HasFactory;

    protected $fillable = [
        'document_request_id',
        'file_path',
        'file_type',
        'file_size',
        'original_name',
    ];

    protected function casts(): array
    {
        return [
            'file_size' => 'integer',
        ];
    }

    public function documentRequest()
    {
        return $this->belongsTo(DocumentRequest::class);
    }
}
