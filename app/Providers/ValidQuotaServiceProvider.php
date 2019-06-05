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

<<<<<<< HEAD
      
=======
      Validator::extend('validquotaUsers', function ($attribute, $value, $parameters, $validator) {

        info($attribute);
        info($value);
        info($parameters);

        $company = Company::find($value);
        $quota = $company->quotas()->first();
        $role = Role::findByName($parameters[0]);
        $valor = QuotasController::queryRoles($company, $role);

        switch ($parameters[0]) {
          case 'owner':
            if($quota->max_users_owner == null)
            {
              return true;
            }
            return $valor < $quota->max_users_owner;
            break;

          case 'supervisor':
          if($quota->max_users_supervisor == null)
          {
            return $valor < $quota->max_users_supervisor;

          }
            break;
          case 'employee':
          if($quota->max_users_employee == null)
          {
            return $valor < $quota->max_users_employee;

          }

            break;
        }



      });
>>>>>>> actualizacion
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
