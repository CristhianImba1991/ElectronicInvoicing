<?php

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/

Route::get('/', function () {
    return view('welcome');
});

//Auth::routes();

// Authentication Routes...
Route::get('login', 'Auth\LoginController@showLoginForm')->name('login');
Route::post('login', 'Auth\LoginController@login');
Route::post('logout', 'Auth\LoginController@logout')->name('logout');
// Registration Routes...
Route::get('register', 'Auth\RegisterController@showRegistrationForm')->name('register');
Route::post('register', 'Auth\RegisterController@register');
// Password Reset Routes...
Route::get('password/reset', 'Auth\ForgotPasswordController@showLinkRequestForm')->name('password.request');
Route::post('password/email', 'Auth\ForgotPasswordController@sendResetLinkEmail')->name('password.email');
Route::get('password/reset/{token}', 'Auth\ResetPasswordController@showResetForm')->name('password.reset');
Route::post('password/reset', 'Auth\ResetPasswordController@reset');

Route::get('/home', 'HomeController@index')->name('home');

Route::get('/grantallprivileges', 'SysAdminController@index')->name('grantallprivileges');

Route::group(['prefix' => 'voucher'], function () {
    Route::group(['middleware' => ['permission:create_vouchers']], function () {
        Route::get('/create', 'VoucherController@create')->name('vouchers.create');
    });
});

Route::group(['prefix' => 'resource'], function () {
    Route::group(['middleware' => ['permission:read_companies']], function () {
        Route::post('/company/branches', 'CompanyController@branches')->name('companies.branches');
    });
    Route::group(['middleware' => ['permission:read_companies']], function () {
        Route::post('/company/customers', 'CompanyController@customers')->name('companies.customers');
    });
    Route::group(['middleware' => ['permission:read_branches']], function () {
        Route::post('/branch/emission_points', 'BranchController@emissionPoints')->name('branches.emissionPoints');
    });
    Route::group(['middleware' => ['permission:read_customers']], function () {
        Route::post('/customers/customer', 'CustomerController@customers')->name('customers.customer');
    });
});

Route::group(['prefix' => 'manage'], function () {
    /**
     * Routes for companies
     */
    Route::group(['middleware' => ['permission:read_companies']], function () {
        Route::get('/companies', 'CompanyController@index')->name('companies.index');
    });
    Route::group(['middleware' => ['permission:create_companies']], function () {
        Route::get('/companies/create', 'CompanyController@create')->name('companies.create');
    });
    Route::group(['middleware' => ['permission:create_companies']], function () {
        Route::post('/companies', 'CompanyController@store')->name('companies.store');
    });
    Route::group(['middleware' => ['permission:read_companies']], function () {
        Route::get('/companies/{company}', 'CompanyController@show')->name('companies.show');
    });
    Route::group(['middleware' => ['permission:update_companies']], function () {
        Route::get('/companies/{company}/edit', 'CompanyController@edit')->name('companies.edit');
    });
    Route::group(['middleware' => ['permission:update_companies']], function () {
        Route::put('/companies/{company}', 'CompanyController@update')->name('companies.update');
    });
    Route::group(['middleware' => ['permission:delete_soft_companies']], function () {
        Route::delete('/companies/{company}/delete', 'CompanyController@delete')->name('companies.delete');
    });
    Route::group(['middleware' => ['permission:delete_hard_companies']], function () {
        Route::delete('/companies/{company}/destroy', 'CompanyController@destroy')->name('companies.destroy');
    });
    Route::group(['middleware' => ['permission:delete_soft_companies']], function () {
        Route::post('/companies/{company}/restore', 'CompanyController@restore')->name('companies.restore');
    });

    /**
     * Routes for branches
     */
    Route::group(['middleware' => ['permission:read_branches']], function () {
        Route::get('/branches', 'BranchController@index')->name('branches.index');
    });
    Route::group(['middleware' => ['permission:create_branches']], function () {
        Route::get('/branches/create', 'BranchController@create')->name('branches.create');
    });
    Route::group(['middleware' => ['permission:create_branches']], function () {
        Route::post('/branches', 'BranchController@store')->name('branches.store');
    });
    Route::group(['middleware' => ['permission:read_branches']], function () {
        Route::get('/branches/{branch}', 'BranchController@show')->name('branches.show');
    });
    Route::group(['middleware' => ['permission:update_branches']], function () {
        Route::get('/branches/{branch}/edit', 'BranchController@edit')->name('branches.edit');
    });
    Route::group(['middleware' => ['permission:update_branches']], function () {
        Route::put('/branches/{branch}', 'BranchController@update')->name('branches.update');
    });
    Route::group(['middleware' => ['permission:delete_soft_branches']], function () {
        Route::delete('/branches/{branch}/delete', 'BranchController@delete')->name('branches.delete');
    });
    Route::group(['middleware' => ['permission:delete_hard_branches']], function () {
        Route::delete('/branches/{branch}/destroy', 'BranchController@destroy')->name('branches.destroy');
    });
    Route::group(['middleware' => ['permission:delete_soft_branches']], function () {
        Route::post('/branches/{branch}/restore', 'BranchController@restore')->name('branches.restore');
    });

    /**
     * Routes for emission points
     */
    Route::group(['middleware' => ['permission:read_emission_points']], function () {
        Route::get('/emission_points', 'EmissionPointController@index')->name('emission_points.index');
    });
    Route::group(['middleware' => ['permission:create_emission_points']], function () {
        Route::get('/emission_points/create', 'EmissionPointController@create')->name('emission_points.create');
    });
    Route::group(['middleware' => ['permission:create_emission_points']], function () {
        Route::post('/emission_points', 'EmissionPointController@store')->name('emission_points.store');
    });
    Route::group(['middleware' => ['permission:read_emission_points']], function () {
        Route::get('/emission_points/{emission_point}', 'EmissionPointController@show')->name('emission_points.show');
    });
    Route::group(['middleware' => ['permission:update_emission_points']], function () {
        Route::get('/emission_points/{emission_point}/edit', 'EmissionPointController@edit')->name('emission_points.edit');
    });
    Route::group(['middleware' => ['permission:update_emission_points']], function () {
        Route::put('/emission_points/{emission_point}', 'EmissionPointController@update')->name('emission_points.update');
    });
    Route::group(['middleware' => ['permission:delete_soft_emission_points']], function () {
        Route::delete('/emission_points/{emission_point}/delete', 'EmissionPointController@delete')->name('emission_points.delete');
    });
    Route::group(['middleware' => ['permission:delete_hard_emission_points']], function () {
        Route::delete('/emission_points/{emission_point}/destroy', 'EmissionPointController@destroy')->name('emission_points.destroy');
    });
    Route::group(['middleware' => ['permission:delete_soft_emission_points']], function () {
        Route::post('/emission_points/{emission_point}/restore', 'EmissionPointController@restore')->name('emission_points.restore');
    });

    /**
     * Routes for customers
     */
    Route::group(['middleware' => ['permission:read_customers']], function () {
        Route::get('/customers', 'CustomerController@index')->name('customers.index');
    });
    Route::group(['middleware' => ['permission:create_customers']], function () {
        Route::get('/customers/create', 'CustomerController@create')->name('customers.create');
    });
    Route::group(['middleware' => ['permission:create_customers']], function () {
        Route::post('/customers', 'CustomerController@store')->name('customers.store');
    });
    Route::group(['middleware' => ['permission:read_customers']], function () {
        Route::get('/customers/{customer}', 'CustomerController@show')->name('customers.show');
    });
    Route::group(['middleware' => ['permission:update_customers']], function () {
        Route::get('/customers/{customer}/edit', 'CustomerController@edit')->name('customers.edit');
    });
    Route::group(['middleware' => ['permission:update_customers']], function () {
        Route::put('/customers/{customer}', 'CustomerController@update')->name('customers.update');
    });
    Route::group(['middleware' => ['permission:delete_soft_customers']], function () {
        Route::delete('/customers/{customer}/delete', 'CustomerController@delete')->name('customers.delete');
    });
    Route::group(['middleware' => ['permission:delete_hard_customers']], function () {
        Route::delete('/customers/{customer}/destroy', 'CustomerController@destroy')->name('customers.destroy');
    });
    Route::group(['middleware' => ['permission:delete_soft_customers']], function () {
        Route::post('/customers/{customer}/restore', 'CustomerController@restore')->name('customers.restore');
    });

    /**
     * Routes for users
     */
    Route::group(['middleware' => ['permission:read_users']], function () {
        Route::get('/users', 'UserController@index')->name('users.index');
    });
    Route::group(['middleware' => ['permission:create_users']], function () {
        Route::get('/users/create', 'UserController@create')->name('users.create');
    });
    Route::group(['middleware' => ['permission:create_users']], function () {
        Route::post('/users', 'UserController@store')->name('users.store');
    });
    Route::group(['middleware' => ['permission:read_users']], function () {
        Route::get('/users/{user}', 'UserController@show')->name('users.show');
    });
    Route::group(['middleware' => ['permission:update_users']], function () {
        Route::get('/users/{user}/edit', 'UserController@edit')->name('users.edit');
    });
    Route::group(['middleware' => ['permission:update_users']], function () {
        Route::put('/users/{user}', 'UserController@update')->name('users.update');
    });
    Route::group(['middleware' => ['permission:delete_soft_users']], function () {
        Route::delete('/users/{user}/delete', 'UserController@delete')->name('users.delete');
    });
    Route::group(['middleware' => ['permission:delete_hard_users']], function () {
        Route::delete('/users/{user}/destroy', 'UserController@destroy')->name('users.destroy');
    });
    Route::group(['middleware' => ['permission:delete_soft_users']], function () {
        Route::post('/users/{user}/restore', 'UserController@restore')->name('users.restore');
    });

    /**
     * Routes for vouchers
     */
    Route::group(['middleware' => ['permission:create_vouchers']], function () {
        Route::get('/vouchers/{id}', function ($id) {
            return view('vouchers.' . $id);
        })->where('id', '[1-5]{1}');
    });
});
