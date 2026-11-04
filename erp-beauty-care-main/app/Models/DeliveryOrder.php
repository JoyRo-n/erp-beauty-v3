<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class DeliveryOrder extends Model
{
    protected $table = 'delivery_orders';
    protected $primaryKey = 'do_number';
    public $incrementing = false;
    protected $keyType = 'string';

    protected $guarded = [];

    protected $casts = [
        'is_backorder' => 'boolean',
        'validated_at' => 'datetime',
    ];

    public function salesOrder()
    {
        return $this->belongsTo(SalesOrder::class, 'sales_order_number', 'order_number');
    }

    public function customer()
    {
        return $this->belongsTo(Contact::class, 'customer_id', 'id');
    }

    public function lines()
    {
        return $this->hasMany(DeliveryOrderLine::class, 'do_number', 'do_number');
    }
}
