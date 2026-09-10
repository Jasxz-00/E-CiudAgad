<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class DocumentSetting extends Model
{
    use HasFactory;

    protected $fillable = [
        'chairman_name',
        'barangay_name',
        'province_name',
        'city_name',
        'barangay_address',
        'left_logo_path',
        'right_logo_path',
        'signature_path',
        'updated_by',
    ];

    public static function bootstrap(): static
    {
        return static::query()->firstOrCreate(['id' => 1]);
    }

    public function updatedBy()
    {
        return $this->belongsTo(User::class, 'updated_by');
    }
}
