<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ReturnLine extends Model
{
    protected $table = 'return_lines';
    protected $guarded = [];

    protected $casts = [
        'unit_price' => 'decimal:2',
        'unit_cost' => 'decimal:2',
        'tax_amount' => 'decimal:2',
        'subtotal' => 'decimal:2',
    ];

    public function productReturn()
    {
        return $this->belongsTo(ProductReturn::class, 'return_number', 'return_number');
    }

    public function product()
    {
        return $this->belongsTo(Product::class, 'variant_sku', 'variant_sku');
    }
}
