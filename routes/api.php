<?php

use Illuminate\Http\Request;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/

/*Route::middleware('auth:api')->get('/user', function (Request $request) {
    return $request->user();
});*/

Route::group(['prefix' => 'auth'], function () {
    Route::group(['middleware' => 'apithrottle:5,1'], function() {
        Route::post('login', 'ApiController@login');
    });
    Route::group(['middleware' => 'auth:api'], function() {
        Route::get('logout', 'ApiController@logout');
        Route::post('send_voucher', 'ApiController@sendVoucher');
        Route::post('create_product', 'ApiController@createProduct');
        Route::post('create_customer', 'ApiController@createCustomer');
    });
});
