<?php

namespace ElectronicInvoicing\Providers;

use ElectronicInvoicing\Voucher;
use Illuminate\Support\ServiceProvider;
use Validator;

class ValidAccessKeyServiceProvider extends ServiceProvider
{
    /**
     * Bootstrap services.
     *
     * @return void
     */
    public function boot()
    {
        Validator::extend('validaccesskey', function ($attribute, $value, $parameters, $validator) {
            if (strlen($value) !== 49) {
                return false;
            }
            $accessKey = substr($value, 0, -1);
            $checkDigit = substr($value, -1, 1);
            return Voucher::getCheckDigit($accessKey) === (integer) $checkDigit;
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
