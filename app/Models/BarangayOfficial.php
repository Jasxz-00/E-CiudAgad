<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class BarangayOfficial extends Model
{
    use HasFactory;

    const POSITION_PUNONG_BARANGAY = 'punong_barangay';
    const POSITION_KAGAWAD = 'kagawad';
    const POSITION_SECRETARY = 'secretary';
    const POSITION_TREASURER = 'treasurer';

    protected $fillable = [
        'position',
        'name',
        'sort_order',
    ];

    public function scopeOrdered($query)
    {
        return $query->orderBy('sort_order')->orderBy('id');
    }

    public function getPositionLabelAttribute(): string
    {
        return match ($this->position) {
            self::POSITION_PUNONG_BARANGAY => 'Punong Barangay',
            self::POSITION_KAGAWAD => 'Kagawad',
            self::POSITION_SECRETARY => 'Barangay Secretary',
            self::POSITION_TREASURER => 'Barangay Treasurer',
            default => str_replace('_', ' ', ucwords($this->position, '_')),
        };
    }
}