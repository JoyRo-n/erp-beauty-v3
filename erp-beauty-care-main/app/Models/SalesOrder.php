<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SalesOrder extends Model
{
    protected $table = 'sales_orders';
    protected $primaryKey = 'order_number';
    public $incrementing = false;
    protected $keyType = 'string';

    protected $guarded = [];

    protected $casts = [
        'untaxed_amount' => 'decimal:2',
        'tax_amount' => 'decimal:2',
        'total_amount' => 'decimal:2',
        'confirmed_at' => 'datetime',
    ];

    public function customer()
    {
        return $this->belongsTo(Contact::class, 'customer_id', 'id');
    }

    public function lines()
    {
        return $this->hasMany(SalesOrderLine::class, 'order_number', 'order_number');
    }

    public function deliveryOrders()
    {
        return $this->hasMany(DeliveryOrder::class, 'sales_order_number', 'order_number');
    }

    public function invoices()
    {
        return $this->hasMany(CustomerInvoice::class, 'sales_order_number', 'order_number');
    }
}
