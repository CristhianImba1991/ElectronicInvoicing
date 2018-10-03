<?php

namespace ElectronicInvoicing\Providers;

use Illuminate\Support\ServiceProvider;
use Validator;

class ValidSignServiceProvider extends ServiceProvider
{
    /**
     * Bootstrap services.
     *
     * @return void
     */
    public function boot()
    {
        Validator::extend('validsign', function ($attribute, $value, $parameters, $validator) {
            return openssl_pkcs12_read(file_get_contents($value), $results, $parameters[0]);
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
