<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class InternalTransfer extends Model
{
    protected $table = 'internal_transfers';
    protected $primaryKey = 'transfer_number';
    public $incrementing = false;
    protected $keyType = 'string';

    protected $guarded = [];

    protected $casts = [
        'transferred_at' => 'datetime',
    ];

    public function product()
    {
        return $this->belongsTo(Product::class, 'variant_sku', 'variant_sku');
    }
}
