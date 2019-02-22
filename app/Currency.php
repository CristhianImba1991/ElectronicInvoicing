<?php

namespace ElectronicInvoicing;

use Illuminate\Database\Eloquent\Model;

class Currency extends Model
{
    protected $fillable = [
        'symbol',
        'iso',
        'name'
    ];

    public function vouchers()
    {
        return $this->hasMany('ElectronicInvoicing\Voucher');
    }
}
