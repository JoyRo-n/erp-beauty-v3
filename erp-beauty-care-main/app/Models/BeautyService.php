<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class BeautyService extends Model
{
    protected $table = 'beauty_services';
    protected $primaryKey = 'service_id';
    public $incrementing = false;
    protected $keyType = 'string';

    protected $guarded = [];

    protected $casts = [
        'sales_price' => 'decimal:2',
    ];

    public function consumedProduct()
    {
        return $this->belongsTo(Product::class, 'consumed_variant_sku', 'variant_sku');
    }
}
