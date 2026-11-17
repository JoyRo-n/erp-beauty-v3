<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AppNotification extends Model
{
    protected $table = 'app_notifications';
    public $timestamps = false;
    protected $guarded = [];

    protected $casts = [
        'message' => \App\Casts\EncryptedString::class,
        'created_at' => 'datetime',
        'read_at' => 'datetime',
    ];
}

