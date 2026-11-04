<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SalesOrderLine extends Model
{
    protected $table = 'sales_order_lines';
    protected $guarded = [];

    protected $casts = [
        'unit_price' => 'decimal:2',
        'discount_percent' => 'decimal:2',
        'tax_rate' => 'decimal:2',
        'tax_amount' => 'decimal:2',
        'subtotal' => 'decimal:2',
    ];

    public function order()
    {
        return $this->belongsTo(SalesOrder::class, 'order_number', 'order_number');
    }

    public function product()
    {
        return $this->belongsTo(Product::class, 'variant_sku', 'variant_sku');
    }
}
