<?php

namespace App\Models;

use App\Enums\AuditActorType;
use Illuminate\Database\Eloquent\Model;

class AuditLog extends Model
{
    public $timestamps = false;

    protected $fillable = [
        'actor_type',
        'actor_id',
        'action',
        'target_type',
        'target_id',
        'ip_address',
        'user_agent',
        'metadata',
        'created_at',
    ];

    protected function casts(): array
    {
        return [
            'actor_type' => AuditActorType::class,
            'metadata' => 'array',
            'created_at' => 'datetime',
        ];
    }
}
