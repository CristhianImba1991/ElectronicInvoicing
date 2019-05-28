<?php

namespace ElectronicInvoicing;

use Illuminate\Database\Eloquent\Model;

class Quotas extends Model
{
    protected $fillable =[

      'description',
      'max_users_owner',
      'max_users_supervisor',
      'max_users_employee',
      'max_branches',
      'max_emission_points'

    ];

    protected $dates = ['deleted_at'];

    public function quota()
    {
        return $this->belongsToMany(
            'ElectronicInvoicing\Quotas',
            'company_quotas')->withTimestamps();
    }



}
