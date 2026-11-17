<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AuditLog extends Model
{
    protected $table = 'audit_logs';
    public $timestamps = false;
    protected $guarded = [];

    protected $casts = [
        'description' => \App\Casts\EncryptedString::class,
        'user_name' => \App\Casts\EncryptedString::class,
        'created_at' => 'datetime',
    ];
}
