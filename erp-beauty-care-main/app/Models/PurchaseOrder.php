<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PurchaseOrder extends Model
{
    protected $table = 'purchase_orders';
    protected $primaryKey = 'po_number';
    public $incrementing = false;
    protected $keyType = 'string';

    protected $guarded = [];

    protected $casts = [
        'untaxed_amount' => 'decimal:2',
        'tax_amount' => 'decimal:2',
        'total_amount' => 'decimal:2',
        'is_locked' => 'boolean',
        'confirmed_at' => 'datetime',
    ];

    public function vendor()
    {
        return $this->belongsTo(Contact::class, 'vendor_id', 'id');
    }

    public function lines()
    {
        return $this->hasMany(PurchaseOrderLine::class, 'po_number', 'po_number');
    }

    public function receipts()
    {
        return $this->hasMany(PurchaseReceipt::class, 'po_number', 'po_number');
    }

    public function bills()
    {
        return $this->hasMany(VendorBill::class, 'po_number', 'po_number');
    }
}
