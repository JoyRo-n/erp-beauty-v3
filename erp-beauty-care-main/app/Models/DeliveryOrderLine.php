<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class DeliveryOrderLine extends Model
{
    protected $table = 'delivery_order_lines';
    protected $guarded = [];

    public function deliveryOrder()
    {
        return $this->belongsTo(DeliveryOrder::class, 'do_number', 'do_number');
    }

    public function product()
    {
        return $this->belongsTo(Product::class, 'variant_sku', 'variant_sku');
    }
}
