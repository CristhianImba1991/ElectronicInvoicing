<?php

namespace ElectronicInvoicing\Providers;

use Illuminate\Support\ServiceProvider;
use Validator;

class ValidCedulaServiceProvider extends ServiceProvider
{
    /**
     * Bootstrap services.
     *
     * @return void
     */
    public function boot()
    {
        Validator::extend('validcedula', function ($attribute, $value, $parameters, $validator) {
            if (!((intval(substr($value, 0, 2)) >= 1 && intval(substr($value, 0, 2)) <= 24) || intval(substr($value, 0, 2)) == 30)) {
                return false;
            }
            if (!(intval(substr($value, 2, 1)) >= 0 && intval(substr($value, 2, 1)) <= 6)) {
                return false;
            }
            if (strlen($value) !== 10) {
                return false;
            }
            $cedula = array_map('intval', str_split($value));
            $coefficients = array(
                0 => 2,
                1 => 1,
                2 => 2,
                3 => 1,
                4 => 2,
                5 => 1,
                6 => 2,
                7 => 1,
                8 => 2,
            );
            $total = 0;
            for ($i = 0; $i < count($coefficients); $i++) {
                $total += $coefficients[$i] * $cedula[$i] + ($coefficients[$i] * $cedula[$i] > 9 ? -9 : 0);
            }
            $mod = $total % 10;
            $digit = $mod == 10 ? 0 : $mod;
            return $cedula[9] === $digit;
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
