<?php

namespace ElectronicInvoicing;

use Illuminate\Database\Eloquent\Model;

class DetailAddressee extends Model
{
    protected $fillable = [
        'addressee_id',
        'product_id',
        'quantity'
    ];

    public function addressee()
    {
        return $this->belongsTo('ElectronicInvoicing\Addressee');
    }

    public function additionalDetails()
    {
        return $this->hasMany('ElectronicInvoicing\AdditionalDetailAddressee');
    }
}
