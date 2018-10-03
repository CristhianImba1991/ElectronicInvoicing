<?php

namespace ElectronicInvoicing;

use Illuminate\Database\Eloquent\Model;

class Voucher extends Model
{
    protected $fillable = [
        'emission_point_id',
        'voucher_type_id',
        'environment_id',
        'voucher_state_id',
        'sequential',
        'numeric_code',
        'customer_id',
        'issue_date',
        'authorization_date',
        'currecy_id',
        'tip',
        'xml',
        'extra_detail',
        'user_id',
        'support_document'
    ];

    public function emissionPoint()
    {
        return $this->belongsTo('ElectronicInvoicing\EmissionPoint');
    }

    public function user()
    {
        return $this->belongsTo('ElectronicInvoicing\User');
    }

    public function customer()
    {
        return $this->belongsTo('ElectronicInvoicing\Customer');
    }

    public function currency()
    {
        return $this->belongsTo('ElectronicInvoicing\Currency');
    }

    public function type()
    {
        return $this->belongsTo('ElectronicInvoicing\VoucherType');
    }

    public function state()
    {
        return $this->belongsTo('ElectronicInvoicing\VoucherState');
    }

    public function environment()
    {
        return $this->belongsTo('ElectronicInvoicing\Environment');
    }

    public function details()
    {
        return $this->hasMany('ElectronicInvoicing\Detail');
    }

    public function aditionalFields()
    {
        return $this->hasMany('ElectronicInvoicing\AditionalField');
    }

    public function payments()
    {
        return $this->hasMany('ElectronicInvoicing\Payment');
    }

    public function creditNotes()
    {
        return $this->hasMany('ElectronicInvoicing\CreditNote');
    }

    public function debitNotes()
    {
        return $this->hasMany('ElectronicInvoicing\DebitNote');
    }

    public function retentions()
    {
        return $this->hasMany('ElectronicInvoicing\Retention');
    }

    public function waybills()
    {
        return $this->hasMany('ElectronicInvoicing\Waybill');
    }
}
