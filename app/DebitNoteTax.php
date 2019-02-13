<?php

namespace ElectronicInvoicing;

use Illuminate\Database\Eloquent\Model;

class DebitNoteTax extends Model
{
    protected $fillable = [
        'voucher_id',
        'code',
        'percentage_code',
        'tax_base',
        'value'
    ];

    public function voucher()
    {
        return $this->belongsTo('ElectronicInvoicing\Voucher');
    }

    public function debitNotes()
    {
        return $this->hasMany('ElectronicInvoicing\DebitNote');
    }
}
