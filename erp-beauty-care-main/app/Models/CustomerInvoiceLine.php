<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CustomerInvoiceLine extends Model
{
    protected $table = 'customer_invoice_lines';
    protected $guarded = [];

    protected $casts = [
        'unit_price' => 'decimal:2',
        'discount_percent' => 'decimal:2',
        'tax_amount' => 'decimal:2',
        'subtotal' => 'decimal:2',
    ];

    public function invoice()
    {
        return $this->belongsTo(CustomerInvoice::class, 'invoice_number', 'invoice_number');
    }
}
