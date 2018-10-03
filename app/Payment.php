<?php

namespace ElectronicInvoicing;

use Illuminate\Database\Eloquent\Model;

class Payment extends Model
{
    protected $fillable = [
        'voucher_id',
        'payment_method_id',
        'total',
        'term',
        'time_unit_id'
    ];

    public function voucher()
    {
        return $this->belongsTo('ElectronicInvoicing\Voucher');
    }

    public function method()
    {
        return $this->belongsTo('ElectronicInvoicing\PaymentMethod');
    }

    public function timeUnit()
    {
        return $this->belongsTo('ElectronicInvoicing\TimeUnit');
    }
}
