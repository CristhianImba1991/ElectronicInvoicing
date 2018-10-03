<?php

namespace ElectronicInvoicing;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Product extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'branch_id',
        'main_code',
        'auxiliary_code',
        'unit_price',
        'description',
        'stock'
    ];

    protected $dates = ['deleted_at'];

    public function branch()
    {
        return $this->belongsTo('ElectronicInvoicing\Branch');
    }

    public function taxes()
    {
        return $this->hasMany('ElectronicInvoicing\ProductTax');
    }

    public function details()
    {
        return $this->hasMany('ElectronicInvoicing\Detail');
    }
}
