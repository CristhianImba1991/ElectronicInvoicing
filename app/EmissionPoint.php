<?php

namespace ElectronicInvoicing;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class EmissionPoint extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'branch_id',
        'code'
    ];

    protected $dates = ['deleted_at'];

    public function branch()
    {
        return $this->belongsTo('ElectronicInvoicing\Branch');
    }

    public function vouchers()
    {
        return $this->hasMany('ElectronicInvoicing\Voucher');
    }

    public function users()
    {
        return $this->belongsToMany(
            'ElectronicInvoicing\User',
            'is_allowed')->withTimestamps();
    }
}
