<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Product extends Model
{
    protected $table = 'products';
    protected $primaryKey = 'variant_sku';
    public $incrementing = false;
    protected $keyType = 'string';

    protected $guarded = [];

    protected $casts = [
        'sales_price' => 'decimal:2',
        'cost' => 'decimal:2',
    ];

    public function defaultVendor()
    {
        return $this->belongsTo(Contact::class, 'default_vendor_id', 'id');
    }

    public function stockQuants()
    {
        return $this->hasMany(StockQuant::class, 'variant_sku', 'variant_sku');
    }

    public function stockMoves()
    {
        return $this->hasMany(StockMove::class, 'variant_sku', 'variant_sku');
    }
}
