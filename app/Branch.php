<?php

namespace ElectronicInvoicing;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Branch extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'company_id',
        'establishment',
        'name',
        'address',
        'phone'
    ];

    protected $dates = ['deleted_at'];

    public function company()
    {
        return $this->belongsTo('ElectronicInvoicing\Company');
    }

    public function emissionPoints()
    {
        return $this->hasMany('ElectronicInvoicing\EmissionPoint');
    }

    public function products()
    {
        return $this->hasMany('ElectronicInvoicing\Product');
    }
}
