<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class IdVerification extends Model
{
    use HasFactory;

    protected $fillable = [
        'resident_id',
        'id_type',
        'id_number',
        'file_path',
        'file_type',
        'file_size',
        'is_verified',
        'verified_at',
        'verified_by',
    ];

    protected function casts(): array
    {
        return [
            'is_verified' => 'boolean',
            'verified_at' => 'datetime',
            'file_size' => 'integer',
            'id_number' => 'encrypted',
        ];
    }

    public function resident()
    {
        return $this->belongsTo(Resident::class);
    }

    public function verifiedBy()
    {
        return $this->belongsTo(User::class, 'verified_by');
    }
}
