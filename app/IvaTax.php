<?php

namespace ElectronicInvoicing;

use Illuminate\Database\Eloquent\Model;

class IvaTax extends Model
{
    protected $fillable = [
        'code',
        'auxiliary_code',
        'description',
        'rate'
    ];

    public function productTaxes()
    {
        return $this->hasMany('ElectronicInvoicing\ProductTax');
    }
}
