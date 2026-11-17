<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class JournalEntry extends Model
{
    protected $table = 'journal_entries';
    protected $primaryKey = 'entry_number';
    public $incrementing = false;
    protected $keyType = 'string';

    protected $guarded = [];

    protected $casts = [
        'date' => 'date',
        'reference' => \App\Casts\EncryptedString::class,
        'posted_by' => \App\Casts\EncryptedString::class,
        'total_debit' => 'decimal:2',
        'total_credit' => 'decimal:2',
        'posted_at' => 'datetime',
    ];

    public function items()
    {
        return $this->hasMany(JournalItem::class, 'entry_number', 'entry_number');
    }
}
