<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class StockMove extends Model
{
    protected $table = 'stock_moves';
    protected $guarded = [];

    protected $casts = [
        'unit_cost' => 'decimal:2',
        'total_cost' => 'decimal:2',
        'is_quarantine' => 'boolean',
    ];

    public function product()
    {
        return $this->belongsTo(Product::class, 'variant_sku', 'variant_sku');
    }
}
