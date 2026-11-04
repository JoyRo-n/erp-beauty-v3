<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ProductReturn extends Model
{
    protected $table = 'returns';
    protected $primaryKey = 'return_number';
    public $incrementing = false;
    protected $keyType = 'string';

    protected $guarded = [];

    protected $casts = [
        'credit_note_total' => 'decimal:2',
        'completed_at' => 'datetime',
    ];

    public function customer()
    {
        return $this->belongsTo(Contact::class, 'customer_id', 'id');
    }

    public function originInvoice()
    {
        return $this->belongsTo(CustomerInvoice::class, 'origin_invoice_number', 'invoice_number');
    }

    public function lines()
    {
        return $this->hasMany(ReturnLine::class, 'return_number', 'return_number');
    }
}
