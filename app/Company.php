<?php

namespace ElectronicInvoicing;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Company extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'ruc',
        'social_reason',
        'tradename',
        'address',
        'special_contributor',
        'keep_accounting',
        'phone',
        'logo',
        'sign_valid_from',
        'sign_valid_to'
    ];

    protected $dates = ['deleted_at'];

    public function branches()
    {
        return $this->hasMany('ElectronicInvoicing\Branch');
    }

    public function customers()
    {
        return $this->belongsToMany(
            'ElectronicInvoicing\Customer',
            'company_customers')->withTimestamps();
    }

    public function quotas()
    {
        return $this->belongsToMany(
            'ElectronicInvoicing\Quotas',
            'company_quotas')->withTimestamps();
    }
}
