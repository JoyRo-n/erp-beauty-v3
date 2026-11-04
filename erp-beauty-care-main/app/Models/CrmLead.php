<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CrmLead extends Model
{
    protected $table = 'crm_leads';
    protected $primaryKey = 'id';
    public $incrementing = false;
    protected $keyType = 'string';

    protected $guarded = [];

    public function customer()
    {
        return $this->belongsTo(Contact::class, 'customer_id', 'id');
    }
}
