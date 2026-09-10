<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Resident extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'first_name',
        'last_name',
        'middle_name',
        'middle_name_none',
        'suffix',
        'birthdate',
        'age',
        'gender',
        'civil_status',
        'nationality',
        'occupation',
        'religion',
        'place_of_birth',
        'building_no',
        'unit_no',
        'street',
        'road',
        'subdivision',
        'barangay',
        'purok',
        'city',
        'province',
        'zip_code',
        'contact_number',
        'emergency_contact',
        'category',
        'category_remarks',
        'person_status',
        'status_verification_photo',
        'verified_at',
        'is_pregnant',
    ];

    protected function casts(): array
    {
        return [
            'birthdate' => 'date',
            'verified_at' => 'datetime',
            'first_name' => 'string',
            'last_name' => 'string',
            'middle_name' => 'string',
            'emergency_contact' => 'encrypted',
            'place_of_birth' => 'encrypted',
        ];
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function idVerifications()
    {
        return $this->hasMany(IdVerification::class);
    }

    public function documentRequests()
    {
        return $this->hasMany(DocumentRequest::class);
    }

    public function concerns()
    {
        return $this->hasMany(Concern::class);
    }

    public function profileChangeRequests()
    {
        return $this->hasMany(ProfileChangeRequest::class);
    }

    public function auditLogs()
    {
        return $this->morphMany(AuditLog::class, 'subject');
    }

    public function getFullNameAttribute(): string
    {
        $name = $this->last_name.', '.$this->first_name;
        if ($this->middle_name) {
            $name .= ' '.$this->middle_name[0].'.';
        }
        if ($this->suffix) {
            $name .= ' '.$this->suffix;
        }

        return $name;
    }

    public function getFullAddressAttribute(): string
    {
        $parts = [];
        if ($this->building_no) {
            $parts[] = 'BLDG '.$this->building_no;
        }
        if ($this->unit_no) {
            $parts[] = 'UNIT '.$this->unit_no;
        }
        if ($this->street) {
            $parts[] = $this->street;
        }
        if ($this->road) {
            $parts[] = $this->road;
        }
        if ($this->subdivision) {
            $parts[] = $this->subdivision;
        }
        if ($this->barangay) {
            $parts[] = $this->barangay;
        }
        $parts[] = $this->city.', '.$this->province.' '.$this->zip_code;

        return implode(', ', $parts);
    }
}
