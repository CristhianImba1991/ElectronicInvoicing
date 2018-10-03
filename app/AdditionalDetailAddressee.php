<?php

namespace ElectronicInvoicing;

use Illuminate\Database\Eloquent\Model;

class AdditionalDetailAddressee extends Model
{
    protected $fillable = [
        'detail_addressee_id',
        'name',
        'value'
    ];

    public function detail()
    {
        return $this->belongsTo('ElectronicInvoicing\DetailAddressee');
    }
}
