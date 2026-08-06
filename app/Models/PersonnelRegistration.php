<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PersonnelRegistration extends Model
{
    const UPDATED_AT = null;

    protected $fillable = [
        'personnel_id',
        'resident_id',
        'action',
        'metadata',
    ];

    protected function casts(): array
    {
        return [
            'metadata' => 'array',
        ];
    }

    public function personnel()
    {
        return $this->belongsTo(User::class, 'personnel_id');
    }

    public function resident()
    {
        return $this->belongsTo(Resident::class);
    }
}
