<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class StockQuant extends Model
{
    protected $table = 'stock_quants';
    protected $guarded = [];

    protected $appends = ['available'];

    public function getAvailableAttribute()
    {
        return $this->on_hand - $this->reserved;
    }

    public function location()
    {
        return $this->belongsTo(WarehouseLocation::class, 'location_code', 'location_code');
    }

    public function product()
    {
        return $this->belongsTo(Product::class, 'variant_sku', 'variant_sku');
    }
}
