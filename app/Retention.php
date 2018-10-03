<?php

namespace ElectronicInvoicing;

use Illuminate\Database\Eloquent\Model;

class Retention extends Model
{
    protected $fillable = [
        'voucher_id',
        'fiscal_period'
    ];

    public function voucher()
    {
        return $this->belongsTo('ElectronicInvoicing\Voucher');
    }

    public function details()
    {
        return $this->hasMany('ElectronicInvoicing\RetentionDetail');
    }
}
