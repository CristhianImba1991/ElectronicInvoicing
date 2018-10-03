<?php

namespace ElectronicInvoicing;

use Illuminate\Database\Eloquent\Model;

class AdditionalField extends Model
{
    protected $fillable = [
        'voucher_id',
        'name',
        'value'
    ];

    public function voucher()
    {
        return $this->belongsTo('ElectronicInvoicing\Voucher');
    }
}
