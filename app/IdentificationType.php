<?php

namespace ElectronicInvoicing;

use Illuminate\Database\Eloquent\Model;

class IdentificationType extends Model
{
    protected $fillable = [
        'code',
        'name'
    ];

    public function customers()
    {
        return $this->hasMany('ElectronicInvoicing\Customer');
    }
}
