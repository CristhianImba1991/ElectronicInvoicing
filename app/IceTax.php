<?php

namespace ElectronicInvoicing;

use Illuminate\Database\Eloquent\Model;

class IceTax extends Model
{
    protected $fillable = [
        'code',
        'auxiliary_code',
        'description',
        'specific_rate',
        'ad_valorem_rate'
    ];

    public function productTaxes()
    {
        return $this->hasMany('ElectronicInvoicing\ProductTax');
    }
}
