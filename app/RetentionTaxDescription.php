<?php

namespace ElectronicInvoicing;

use Illuminate\Database\Eloquent\Model;

class RetentionTaxDescription extends Model
{
    protected $fillable = [
        'retention_tax_id',
        'code',
        'description'
    ];

    public function tax()
    {
        return $this->belongsTo('ElectronicInvoicing\RetentionTax');
    }

    public function details()
    {
        return $this->hasMany('ElectronicInvoicing\RetentionTaxDetail');
    }
}
