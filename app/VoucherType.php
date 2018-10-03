<?php

namespace ElectronicInvoicing;

use Illuminate\Database\Eloquent\Model;

class VoucherType extends Model
{
    protected $fillable = [
        'code',
        'name'
    ];

    public function vouchers()
    {
        return $this->hasMany('ElectronicInvoicing\Voucher');
    }
}
