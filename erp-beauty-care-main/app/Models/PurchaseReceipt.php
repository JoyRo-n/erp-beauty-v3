<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PurchaseReceipt extends Model
{
    protected $table = 'purchase_receipts';
    protected $primaryKey = 'receipt_number';
    public $incrementing = false;
    protected $keyType = 'string';

    protected $guarded = [];

    protected $casts = [
        'validated_at' => 'datetime',
    ];

    public function purchaseOrder()
    {
        return $this->belongsTo(PurchaseOrder::class, 'po_number', 'po_number');
    }

    public function vendor()
    {
        return $this->belongsTo(Contact::class, 'vendor_id', 'id');
    }

    public function lines()
    {
        return $this->hasMany(PurchaseReceiptLine::class, 'receipt_number', 'receipt_number');
    }
}
