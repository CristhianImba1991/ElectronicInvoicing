<?php

namespace ElectronicInvoicing;

use Illuminate\Database\Eloquent\Model;

class Waybill extends Model
{
    protected $fillable = [
        'voucher_id',
        'starting_address',
        'rise',
        'start_date_transport',
        'end_date_transport',
        'licence_plate'
    ];

    public function voucher()
    {
        return $this->belongsTo('ElectronicInvoicing\Voucher');
    }

    public function addressees()
    {
        return $this->hasMany('ElectronicInvoicing\Addressee');
    }
}
