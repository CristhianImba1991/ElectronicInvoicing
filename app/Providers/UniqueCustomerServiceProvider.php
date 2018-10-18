<?php

namespace ElectronicInvoicing\Providers;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\ServiceProvider;
use Validator;

class UniqueCustomerServiceProvider extends ServiceProvider
{
    /**
     * Bootstrap services.
     *
     * @return void
     */
    public function boot()
    {
        Validator::extend('uniquecustomer', function ($attribute, $value, $parameters, $validator) {
            $table = array_shift($parameters);
            $conditions = array();
            while($field = array_shift($parameters)) {
                $column = $field;
                $field = array_shift($parameters);
                $value = $field;
                array_push($conditions, array($column, '=', $value));
            }
            $result = DB::table($table)->where($conditions)->get();
            return $result->isEmpty();
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
