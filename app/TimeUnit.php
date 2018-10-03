<?php

namespace ElectronicInvoicing;

use Illuminate\Database\Eloquent\Model;

class TimeUnit extends Model
{
    protected $fillable = [
        'name'
    ];

    public function payments()
    {
        return $this->hasMany('ElectronicInvoicing\Payment');
    }
}
