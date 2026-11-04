<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class BeautyTreatment extends Model
{
    protected $table = 'beauty_treatments';
    protected $primaryKey = 'treatment_number';
    public $incrementing = false;
    protected $keyType = 'string';

    protected $guarded = [];

    protected $casts = [
        'billed_total' => 'decimal:2',
        'completed_at' => 'datetime',
    ];

    public function service()
    {
        return $this->belongsTo(BeautyService::class, 'service_id', 'service_id');
    }

    public function customer()
    {
        return $this->belongsTo(Contact::class, 'customer_id', 'id');
    }

    public function employee()
    {
        return $this->belongsTo(Employee::class, 'employee_id', 'id');
    }

    public function invoice()
    {
        return $this->belongsTo(CustomerInvoice::class, 'invoice_id', 'invoice_number');
    }
}
