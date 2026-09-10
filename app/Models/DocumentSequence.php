<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class DocumentSequence extends Model
{
    use HasFactory;

    protected $fillable = [
        'document_type_code',
        'year',
        'last_seq',
    ];

    protected function casts(): array
    {
        return [
            'last_seq' => 'integer',
            'year' => 'string',
        ];
    }
}
