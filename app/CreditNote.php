<?php

namespace ElectronicInvoicing;

use Illuminate\Database\Eloquent\Model;

class CreditNote extends Model
{
    protected $fillable = [
        'voucher_id',
        'rise',
        'value',
        'reason'
    ];

    public function voucher()
    {
        return $this->belongsTo('ElectronicInvoicing\Voucher');
    }

    public function taxes()
    {
        return $this->hasMany('ElectronicInvoicing\CreditNoteTax');
    }
}
