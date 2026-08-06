<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class DuplicateClaim extends Model
{
    protected $fillable = [
        'registration_data',
        'matched_resident_id',
        'status',
        'staff_notes',
        'reviewed_by',
        'reviewed_at',
    ];

    protected function casts(): array
    {
        return [
            'registration_data' => 'array',
            'reviewed_at' => 'datetime',
        ];
    }

    public function matchedResident()
    {
        return $this->belongsTo(Resident::class, 'matched_resident_id');
    }

    public function reviewer()
    {
        return $this->belongsTo(User::class, 'reviewed_by');
    }
}
