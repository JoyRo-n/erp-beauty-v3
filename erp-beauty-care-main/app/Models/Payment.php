<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Payment extends Model
{
    protected $table = 'payments';
    protected $primaryKey = 'payment_number';
    public $incrementing = false;
    protected $keyType = 'string';

    protected $guarded = [];

    protected $casts = [
        'amount' => 'decimal:2',
        'payment_date' => 'date',
    ];

    public function customerInvoice()
    {
        return $this->belongsTo(CustomerInvoice::class, 'invoice_id', 'invoice_number');
    }

    public function vendorBill()
    {
        return $this->belongsTo(VendorBill::class, 'bill_id', 'bill_number');
    }

    public function journalEntry()
    {
        return $this->belongsTo(JournalEntry::class, 'journal_entry_number', 'entry_number');
    }
}
