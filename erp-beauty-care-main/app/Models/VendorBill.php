<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class VendorBill extends Model
{
    protected $table = 'vendor_bills';
    protected $primaryKey = 'bill_number';
    public $incrementing = false;
    protected $keyType = 'string';

    protected $guarded = [];

    protected $casts = [
        'untaxed_amount' => 'decimal:2',
        'tax_amount' => 'decimal:2',
        'total_amount' => 'decimal:2',
        'paid_amount' => 'decimal:2',
        'open_amount' => 'decimal:2',
        'posted_at' => 'datetime',
    ];

    public function vendor()
    {
        return $this->belongsTo(Contact::class, 'vendor_id', 'id');
    }

    public function purchaseOrder()
    {
        return $this->belongsTo(PurchaseOrder::class, 'po_number', 'po_number');
    }

    public function receipt()
    {
        return $this->belongsTo(PurchaseReceipt::class, 'receipt_number', 'receipt_number');
    }

    public function lines()
    {
        return $this->hasMany(VendorBillLine::class, 'bill_number', 'bill_number');
    }

    public function payments()
    {
        return $this->hasMany(Payment::class, 'bill_id', 'bill_number');
    }
}
