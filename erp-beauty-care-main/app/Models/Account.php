<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Account extends Model
{
    protected $table = 'accounts';
    protected $primaryKey = 'code';
    public $incrementing = false;
    protected $keyType = 'string';

    protected $guarded = [];

    public function journalItems()
    {
        return $this->hasMany(JournalItem::class, 'account_code', 'code');
    }
}
