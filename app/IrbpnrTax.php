<?php

namespace ElectronicInvoicing;

use Illuminate\Database\Eloquent\Model;

class IrbpnrTax extends Model
{
    protected $fillable = [
        'code',
        'auxiliary_code',
        'description',
        'specific_rate'
    ];

    public function productTaxes()
    {
        return $this->hasMany('ElectronicInvoicing\ProductTax');
    }
}
