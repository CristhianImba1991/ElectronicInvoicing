<?php

namespace ElectronicInvoicing\Providers;

use DateTime;
use ElectronicInvoicing\Company;
use Illuminate\Support\ServiceProvider;
use Validator;

class SignatureNotExpiredServiceProvider extends ServiceProvider
{
    /**
     * Bootstrap services.
     *
     * @return void
     */
    public function boot()
    {
        Validator::extend('sign_not_expired', function ($attribute, $value, $parameters, $validator) {
            if ($parameters[0] === 'id') {
                $company = Company::find($value);
            } elseif ($parameters[0] === 'ruc') {
                $company = Company::where('ruc', '=', $value)->first();
            } else {
                return false;
            }
            return DateTime::createFromFormat('Y-m-d H:i:s', $company->sign_valid_from) <= new DateTime() && DateTime::createFromFormat('Y-m-d H:i:s', $company->sign_valid_to) >= new DateTime();
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
