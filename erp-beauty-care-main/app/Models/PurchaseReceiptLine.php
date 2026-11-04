<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PurchaseReceiptLine extends Model
{
    protected $table = 'purchase_receipt_lines';
    protected $guarded = [];

    protected $casts = [
        'unit_cost' => 'decimal:2',
    ];

    public function receipt()
    {
        return $this->belongsTo(PurchaseReceipt::class, 'receipt_number', 'receipt_number');
    }

    public function product()
    {
        return $this->belongsTo(Product::class, 'variant_sku', 'variant_sku');
    }
}
