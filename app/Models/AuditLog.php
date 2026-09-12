<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AuditLog extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'action',
        'description',
        'model_type',
        'model_id',
        'old_values',
        'new_values',
        'subject_type',
        'subject_id',
        'auditable_type',
        'auditable_id',
        'ip_address',
        'user_agent',
    ];

    protected function casts(): array
    {
        return [
            'old_values' => 'array',
            'new_values' => 'array',
            'subject_id' => 'integer',
            'model_id' => 'integer',
            'auditable_id' => 'integer',
        ];
    }

    protected static function booted(): void
    {
        static::creating(function (AuditLog $log) {
            if ($log->subject_type && $log->subject_id && ! $log->auditable_type) {
                $log->auditable_type = $log->subject_type;
                $log->auditable_id = $log->subject_id;
            } elseif ($log->auditable_type && $log->auditable_id && ! $log->subject_type) {
                $log->subject_type = $log->auditable_type;
                $log->subject_id = $log->auditable_id;
            }
        });
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function subject()
    {
        return $this->morphTo();
    }
}
