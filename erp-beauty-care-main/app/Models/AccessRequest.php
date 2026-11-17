<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AccessRequest extends Model
{
    protected $table = 'access_requests';
    public $timestamps = false;
    protected $guarded = [];

    protected $casts = [
        'requester_name' => \App\Casts\EncryptedString::class,
        'reason' => \App\Casts\EncryptedString::class,
        'reviewed_by' => \App\Casts\EncryptedString::class,
        'review_note' => \App\Casts\EncryptedString::class,
        'created_at' => 'datetime',
        'reviewed_at' => 'datetime',
    ];
}

