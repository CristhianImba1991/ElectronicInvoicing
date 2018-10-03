<?php

namespace ElectronicInvoicing;

use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Spatie\Permission\Traits\HasRoles;

class User extends Authenticatable
{
    use Notifiable;
    use HasRoles;
    use SoftDeletes;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'name', 'email', 'password',
    ];

    /**
     * The attributes that should be hidden for arrays.
     *
     * @var array
     */
    protected $hidden = [
        'password', 'remember_token',
    ];

    protected $dates = ['deleted_at'];

    public function customers()
    {
        return $this->belongsToMany(
            'ElectronicInvoicing\Customer',
            'customer_users')->withTimestamps();
    }

    public function emissionPoints()
    {
        return $this->belongsToMany(
            'ElectronicInvoicing\EmissionPoint',
            'is_alloweds')->withTimestamps();
    }

    public function vouchers()
    {
        return $this->hasMany('ElectronicInvoicing\Voucher');
    }
}
