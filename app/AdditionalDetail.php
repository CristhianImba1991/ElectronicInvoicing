<?php

namespace ElectronicInvoicing;

use Illuminate\Database\Eloquent\Model;

class AdditionalDetail extends Model
{
    protected $fillable = [
        'detail_id',
        'name',
        'value'
    ];

    public function detail()
    {
        return $this->belongsTo('ElectronicInvoicing\Detail');
    }
}
