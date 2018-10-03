<?php

namespace ElectronicInvoicing;

use Illuminate\Database\Eloquent\Model;

class ProductTax extends Model
{
    protected $fillable = [
        'product_id',
        'iva_tax_id',
        'ice_tax_id',
        'irbpnr_tax_id'
    ];

    public function product()
    {
        return $this->belongsTo('ElectronicInvoicing\Product');
    }

    public function iva()
    {
        return $this->belongsTo('ElectronicInvoicing\IvaTax');
    }

    public function ice()
    {
        return $this->belongsTo('ElectronicInvoicing\IceTax');
    }

    public function irbpnr()
    {
        return $this->belongsTo('ElectronicInvoicing\IrbpnrTax');
    }
}
