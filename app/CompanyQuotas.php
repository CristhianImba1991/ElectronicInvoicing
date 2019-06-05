<?php
namespace ElectronicInvoicing;

use Illuminate\Database\Eloquent\Model;

class CompanyQuotas extends Model
{
  protected $fillable[

    'company_id',
    'quotas_id',
  ];


  public function quotas()
  {
      return $this->belongsToMany(
          'ElectronicInvoicing\Quotas',
          'company_quotas')->withTimestamps();
  }

  public function companies()
  {
      return $this->belongsToMany(
          'ElectronicInvoicing\Company',
          'company_quotas')->withTimestamps();
  }
}
