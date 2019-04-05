<?php

namespace ElectronicInvoicing\Providers;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\ServiceProvider;
use Validator;

class UniqueMultipleServiceProvider extends ServiceProvider
{
    /**
     * Bootstrap services.
     *
     * @return void
     */
    public function boot()
    {
        Validator::extend('uniquemultiple', function ($attribute, $value, $parameters, $validator) {
            $table = array_shift($parameters);
            $conditions = array();
            while($field = array_shift($parameters)) {
                $column = $field;
                $field = array_shift($parameters);
                $value = $field;
                array_push($conditions, array($column, '=', html_entity_decode($value)));
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
