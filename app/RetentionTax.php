<?php

namespace ElectronicInvoicing;

use Illuminate\Database\Eloquent\Model;

class RetentionTax extends Model
{
    protected $fillable = [
        'code',
        'tax'
    ];

    public function descriptions()
    {
        return $this->hasMany('ElectronicInvoicing\RetentionTaxDescription');
    }
}
