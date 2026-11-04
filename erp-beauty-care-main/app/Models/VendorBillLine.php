<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class VendorBillLine extends Model
{
    protected $table = 'vendor_bill_lines';
    protected $guarded = [];

    protected $casts = [
        'unit_cost' => 'decimal:2',
        'tax_amount' => 'decimal:2',
        'subtotal' => 'decimal:2',
    ];

    public function bill()
    {
        return $this->belongsTo(VendorBill::class, 'bill_number', 'bill_number');
    }

    public function product()
    {
        return $this->belongsTo(Product::class, 'variant_sku', 'variant_sku');
    }
}
