<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CustomerInvoice extends Model
{
    protected $table = 'customer_invoices';
    protected $primaryKey = 'invoice_number';
    public $incrementing = false;
    protected $keyType = 'string';

    protected $guarded = [];

    protected $casts = [
        'untaxed_amount' => 'decimal:2',
        'tax_amount' => 'decimal:2',
        'total_amount' => 'decimal:2',
        'paid_amount' => 'decimal:2',
        'open_amount' => 'decimal:2',
        'posted_at' => 'datetime',
    ];

    public function customer()
    {
        return $this->belongsTo(Contact::class, 'customer_id', 'id');
    }

    public function salesOrder()
    {
        return $this->belongsTo(SalesOrder::class, 'sales_order_number', 'order_number');
    }

    public function deliveryOrder()
    {
        return $this->belongsTo(DeliveryOrder::class, 'delivery_order_number', 'do_number');
    }

    public function lines()
    {
        return $this->hasMany(CustomerInvoiceLine::class, 'invoice_number', 'invoice_number');
    }

    public function payments()
    {
        return $this->hasMany(Payment::class, 'invoice_id', 'invoice_number');
    }
}
