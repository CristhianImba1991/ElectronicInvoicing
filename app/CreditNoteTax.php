<?php

namespace ElectronicInvoicing;

use Illuminate\Database\Eloquent\Model;

class CreditNoteTax extends Model
{
    protected $fillable = [
        'credit_note_id',
        'code',
        'percentage_code',
        'rate',
        'tax_base',
        'value'
    ];

    public function creditNote()
    {
        return $this->belongsTo('ElectronicInvoicing\CreditNote');
    }
}
