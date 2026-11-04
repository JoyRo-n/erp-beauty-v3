<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class VendorPricelist extends Model
{
    protected $table = 'vendor_pricelists';
    protected $guarded = [];

    protected $casts = [
        'purchase_price' => 'decimal:2',
    ];

    public function vendor()
    {
        return $this->belongsTo(Contact::class, 'vendor_id', 'id');
    }

    public function product()
    {
        return $this->belongsTo(Product::class, 'variant_sku', 'variant_sku');
    }
}
