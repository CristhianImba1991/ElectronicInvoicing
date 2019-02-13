<?php

namespace ElectronicInvoicing;

use Illuminate\Database\Eloquent\Model;

class DebitNote extends Model
{
    protected $fillable = [
        'debit_note_tax_id',
        'reason',
        'value'
    ];

    public function debitNoteTax()
    {
        return $this->belongsTo('ElectronicInvoicing\DebitNoteTax');
    }
}
