<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AccessGrant extends Model
{
    protected $table = 'access_grants';
    public $timestamps = false;
    protected $guarded = [];

    protected $casts = [
        'granted_by' => \App\Casts\EncryptedString::class,
        'note' => \App\Casts\EncryptedString::class,
        'created_at' => 'datetime',
        'expires_at' => 'datetime',
    ];
}

