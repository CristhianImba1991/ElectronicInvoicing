<?php

namespace ElectronicInvoicing;

use Illuminate\Database\Eloquent\Model;

class RetentionDetail extends Model
{
    protected $fillable = [
        'retention_id',
        'retention_tax_description_id',
        'tax_base',
        'rate',
        'support_doc_code'
    ];

    public function retention()
    {
        return $this->belongsTo('ElectronicInvoicing\Retention');
    }

    public function description()
    {
        return $this->belongsTo('ElectronicInvoicing\RetentionTaxDescription');
    }
}
