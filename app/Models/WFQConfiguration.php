<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class WFQConfiguration extends Model
{
    use HasFactory;

    protected $table = 'wfq_configurations';

    protected $fillable = [
        'config_type',
        'config_key',
        'config_value',
        'weight',
        'is_active',
    ];

    protected function casts(): array
    {
        return [
            'is_active' => 'boolean',
            'weight' => 'decimal:4',
        ];
    }
}
