<?php

namespace ElectronicInvoicing;

use Illuminate\Database\Eloquent\Model;

class Addressee extends Model
{
    protected $fillable = [
        'waybill_id',
        'customer_id',
        'address',
        'transfer_reason',
        'single_customs_doc',
        'destination_establishment_code',
        'route',
        'support_doc_code'
    ];

    public function waybill()
    {
        return $this->belongsTo('ElectronicInvoicing\Waybill');
    }

    public function details()
    {
        return $this->hasMany('ElectronicInvoicing\DetailAddressee');
    }
}
