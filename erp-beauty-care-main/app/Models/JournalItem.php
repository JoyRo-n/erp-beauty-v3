<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class JournalItem extends Model
{
    protected $table = 'journal_items';
    protected $guarded = [];

    protected $casts = [
        'debit' => 'decimal:2',
        'credit' => 'decimal:2',
    ];

    public function entry()
    {
        return $this->belongsTo(JournalEntry::class, 'entry_number', 'entry_number');
    }

    public function account()
    {
        return $this->belongsTo(Account::class, 'account_code', 'code');
    }
}
