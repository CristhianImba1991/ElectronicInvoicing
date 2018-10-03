<?php

namespace ElectronicInvoicing;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Customer extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'identification_type_id',
        'identification',
        'social_reason',
        'address',
        'phone',
        'email'
    ];

    protected $dates = ['deleted_at'];

    public function identificationType()
    {
        return $this->belongsTo('ElectronicInvoicing\IdentificationType');
    }

    public function companies()
    {
        return $this->belongsToMany(
            'ElectronicInvoicing\Company',
            'company_customers')->withTimestamps();
    }

    public function users()
    {
        return $this->belongsToMany(
            'ElectronicInvoicing\User',
            'customer_users')->withTimestamps();
    }

    public function vouchers()
    {
        return $this->hasMany('ElectronicInvoicing\Voucher');
    }
}
