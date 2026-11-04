<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Contact extends Model
{
    protected $table = 'contacts';
    protected $primaryKey = 'id';
    public $incrementing = false;
    protected $keyType = 'string';

    protected $guarded = [];

    protected $casts = [
        'credit_limit' => 'decimal:2',
        'opening_receivable' => 'decimal:2',
        'opening_payable' => 'decimal:2',
    ];

    public function salesOrders()
    {
        return $this->hasMany(SalesOrder::class, 'customer_id', 'id');
    }

    public function purchaseOrders()
    {
        return $this->hasMany(PurchaseOrder::class, 'vendor_id', 'id');
    }

    public function invoices()
    {
        return $this->hasMany(CustomerInvoice::class, 'customer_id', 'id');
    }

    public function bills()
    {
        return $this->hasMany(VendorBill::class, 'vendor_id', 'id');
    }
}
