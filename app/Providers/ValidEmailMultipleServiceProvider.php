<?php

namespace ElectronicInvoicing\Providers;

use Illuminate\Support\ServiceProvider;
use Validator;

class ValidEmailMultipleServiceProvider extends ServiceProvider
{
    /**
     * Bootstrap services.
     *
     * @return void
     */
    public function boot()
    {
        Validator::extend('validemailmultiple', function ($attribute, $value, $parameters, $validator) {
            foreach (explode(',', $value) as $email) {
                $validator = Validator::make(['email' => $email], ['email' => 'email']);
                if ($validator->fails()) {
                    return false;
                }
            }
            return true;
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
