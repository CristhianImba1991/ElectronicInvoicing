<?php

namespace ElectronicInvoicing\Providers;
use ElectronicInvoicing\{Quotas, Company};
use Illuminate\Support\ServiceProvider;
use ElectronicInvoicing\Http\Controllers\QuotasController;
use ElectronicInvoicing\StaticClasses\ValidationRule;
use Spatie\Permission\Models\{Role};
use Validator;

class ValidQuotaServiceProvider extends ServiceProvider
{
    /**
     * Bootstrap services.
     *
     * @return void
     */
    public function boot()
    {

      Validator::extend('validquotaBranches', function ($attribute, $value, $parameters, $validator) {


        $company = Company::find($value);
        $quota = $company->quotas()->first();

      $valor = QuotasController::queryBranches($company);


        if($quota->max_branches == null)
        {
          return true;

        }
        if ($valor < $quota->max_branches){
          return true;

        }

      });
      Validator::extend('validquotaEmissionPoints', function ($attribute, $value, $parameters, $validator) {

        $company = Company::find($value);
        $quota = $company->quotas()->first();

      $valor = QuotasController::queryEmissionPoints($company);


        if($quota->max_emission_points == null)
        {
          return true;

        }
        if ($valor < $quota->max_emission_points){
          return true;

        }

      });

      
    }

    /**
     * Register services.
     *
     * @return void
     */
    public function register()
    {
        //
    }
}
