<?php

namespace ElectronicInvoicing\Providers;

use Illuminate\Support\ServiceProvider;
use Validator;

class ValidRUCServiceProvider extends ServiceProvider
{
    /**
     * Bootstrap services.
     *
     * @return void
     */
    public function boot()
    {
        Validator::extend('validruc', function ($attribute, $value, $parameters, $validator) {
            if (!((intval(substr($value, 0, 2)) >= 1 && intval(substr($value, 0, 2)) <= 24) || intval(substr($value, 0, 2)) == 30)) {
                return false;
            }
            if (!((intval(substr($value, 2, 1)) >= 0 && intval(substr($value, 2, 1)) <= 6) || intval(substr($value, 2, 1)) == 9)) {
                return false;
            }
            $ruc = array_map('intval', str_split($value));
            if ($ruc[2] >= 0 && $ruc[2] <= 5) {
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
            } elseif ($ruc[2] == 9) {
                $coefficients = array(
                    0 => 4,
                    1 => 3,
                    2 => 2,
                    3 => 7,
                    4 => 6,
                    5 => 5,
                    6 => 4,
                    7 => 3,
                    8 => 2,
                );
            } elseif ($ruc[2] == 6) {
                $coefficients = array(
                    0 => 3,
                    1 => 2,
                    2 => 7,
                    3 => 6,
                    4 => 5,
                    5 => 4,
                    6 => 3,
                    7 => 2,
                );
            }
            $total = 0;
            for ($i = 0; $i < count($coefficients); $i++) {
                $total += $coefficients[$i] * $ruc[$i] + ($ruc[2] >= 0 && $ruc[2] <= 5 ? ($coefficients[$i] * $ruc[$i] > 9 ? -9 : 0) : 0);
            }
            $mod = $total % ($ruc[2] >= 0 && $ruc[2] <= 5 ? 10 : 11);
            $digit = $mod == 0 ? $mod : ($ruc[2] >= 0 && $ruc[2] <= 5 ? 10 : 11) - $mod;
            return $ruc[$ruc[2] == 6 ? 8 : 9] === $digit;
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
