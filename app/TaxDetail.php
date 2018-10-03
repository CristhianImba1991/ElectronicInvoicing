<?php

namespace ElectronicInvoicing;

use Illuminate\Database\Eloquent\Model;

class TaxDetail extends Model
{
    protected $fillable = [
        'detail_id',
        'code',
        'percentage_code',
        'rate',
        'tax_base',
        'value'
    ];

    public function detail()
    {
        return $this->belongsTo('ElectronicInvoicing\Detail');
    }
}
