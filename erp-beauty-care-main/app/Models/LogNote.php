<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class LogNote extends Model
{
    protected $table = 'log_notes';
    public $timestamps = false;
    protected $guarded = [];

    protected $casts = [
        'message' => \App\Casts\EncryptedString::class,
        'user_name' => \App\Casts\EncryptedString::class,
        'created_at' => 'datetime',
    ];
}
