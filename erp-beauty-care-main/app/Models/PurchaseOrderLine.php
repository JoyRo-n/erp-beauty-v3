<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PurchaseOrderLine extends Model
{
    protected $table = 'purchase_order_lines';
    protected $guarded = [];

    protected $casts = [
        'unit_cost' => 'decimal:2',
        'tax_amount' => 'decimal:2',
        'subtotal' => 'decimal:2',
    ];

    public function purchaseOrder()
    {
        return $this->belongsTo(PurchaseOrder::class, 'po_number', 'po_number');
    }

    public function product()
    {
        return $this->belongsTo(Product::class, 'variant_sku', 'variant_sku');
    }
}
