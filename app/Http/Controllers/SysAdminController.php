<?php

namespace ElectronicInvoicing\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;

class SysAdminController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        Role::create(['name' => 'admin']);
        Role::create(['name' => 'owner']);
        Role::create(['name' => 'supervisor']);
        Role::create(['name' => 'employee']);
        Role::create(['name' => 'customer']);
        Permission::create(['name' => 'create_companies']);
        Permission::create(['name' => 'read_companies']);
        Permission::create(['name' => 'update_companies']);
        Permission::create(['name' => 'delete_soft_companies']);
        Permission::create(['name' => 'delete_hard_companies']);
        Permission::create(['name' => 'create_branches']);
        Permission::create(['name' => 'read_branches']);
        Permission::create(['name' => 'update_branches']);
        Permission::create(['name' => 'delete_soft_branches']);
        Permission::create(['name' => 'delete_hard_branches']);
        Permission::create(['name' => 'create_emission_points']);
        Permission::create(['name' => 'read_emission_points']);
        Permission::create(['name' => 'update_emission_points']);
        Permission::create(['name' => 'delete_soft_emission_points']);
        Permission::create(['name' => 'delete_hard_emission_points']);
        Permission::create(['name' => 'create_customers']);
        Permission::create(['name' => 'read_customers']);
        Permission::create(['name' => 'update_customers']);
        Permission::create(['name' => 'delete_soft_customers']);
        Permission::create(['name' => 'delete_hard_customers']);
        Permission::create(['name' => 'create_users']);
        Permission::create(['name' => 'read_users']);
        Permission::create(['name' => 'update_users']);
        Permission::create(['name' => 'delete_soft_users']);
        Permission::create(['name' => 'delete_hard_users']);
        Permission::create(['name' => 'create_products']);
        Permission::create(['name' => 'read_products']);
        Permission::create(['name' => 'update_products']);
        Permission::create(['name' => 'delete_soft_products']);
        Permission::create(['name' => 'delete_hard_products']);
        Permission::create(['name' => 'create_vouchers']);
        Permission::create(['name' => 'read_vouchers']);
        Permission::create(['name' => 'update_vouchers']);
        Permission::create(['name' => 'delete_vouchers']);
        Permission::create(['name' => 'send_vouchers']);
        Permission::create(['name' => 'report_vouchers']);





        $role_admin = Role::findByName('admin');
        $role_owner = Role::findByName('owner');
        $role_supervisor = Role::findByName('supervisor');
        $role_employee = Role::findByName('employee');
        $role_customer = Role::findByName('customer');
        $permission_create_companies = Permission::findByName('create_companies');
        $permission_read_companies = Permission::findByName('read_companies');
        $permission_update_companies = Permission::findByName('update_companies');
        $permission_delete_soft_companies = Permission::findByName('delete_soft_companies');
        $permission_delete_hard_companies = Permission::findByName('delete_hard_companies');
        $permission_create_branches = Permission::findByName('create_branches');
        $permission_read_branches = Permission::findByName('read_branches');
        $permission_update_branches = Permission::findByName('update_branches');
        $permission_delete_soft_branches = Permission::findByName('delete_soft_branches');
        $permission_delete_hard_branches = Permission::findByName('delete_hard_branches');
        $permission_create_emission_points = Permission::findByName('create_emission_points');
        $permission_read_emission_points = Permission::findByName('read_emission_points');
        $permission_update_emission_points = Permission::findByName('update_emission_points');
        $permission_delete_soft_emission_points = Permission::findByName('delete_soft_emission_points');
        $permission_delete_hard_emission_points = Permission::findByName('delete_hard_emission_points');
        $permission_create_customers = Permission::findByName('create_customers');
        $permission_read_customers = Permission::findByName('read_customers');
        $permission_update_customers = Permission::findByName('update_customers');
        $permission_delete_soft_customers = Permission::findByName('delete_soft_customers');
        $permission_delete_hard_customers = Permission::findByName('delete_hard_customers');
        $permission_create_users = Permission::findByName('create_users');
        $permission_read_users = Permission::findByName('read_users');
        $permission_update_users = Permission::findByName('update_users');
        $permission_delete_soft_users = Permission::findByName('delete_soft_users');
        $permission_delete_hard_users = Permission::findByName('delete_hard_users');
        $permission_create_products = Permission::findByName('create_products');
        $permission_read_products = Permission::findByName('read_products');
        $permission_update_products = Permission::findByName('update_products');
        $permission_delete_soft_products = Permission::findByName('delete_soft_products');
        $permission_delete_hard_products = Permission::findByName('delete_hard_products');
        $permission_create_vouchers = Permission::findByName('create_vouchers');
        $permission_read_vouchers = Permission::findByName('read_vouchers');
        $permission_update_vouchers = Permission::findByName('update_vouchers');
        $permission_delete_vouchers = Permission::findByName('delete_vouchers');
        $permission_send_vouchers = Permission::findByName('send_vouchers');
        $permission_report_vouchers = Permission::findByName('report_vouchers');


        $role_admin->givePermissionTo($permission_create_companies);
        $role_admin->givePermissionTo($permission_read_companies);
        $role_admin->givePermissionTo($permission_update_companies);
        $role_admin->givePermissionTo($permission_delete_soft_companies);
        $role_admin->givePermissionTo($permission_delete_hard_companies);
        $role_owner->givePermissionTo($permission_read_companies);
        $role_owner->givePermissionTo($permission_update_companies);

        $role_admin->givePermissionTo($permission_create_branches);
        $role_admin->givePermissionTo($permission_read_branches);
        $role_admin->givePermissionTo($permission_update_branches);
        $role_admin->givePermissionTo($permission_delete_soft_branches);
        $role_admin->givePermissionTo($permission_delete_hard_branches);
        $role_owner->givePermissionTo($permission_create_branches);
        $role_owner->givePermissionTo($permission_read_branches);
        $role_owner->givePermissionTo($permission_update_branches);
        $role_owner->givePermissionTo($permission_delete_soft_branches);
        $role_owner->givePermissionTo($permission_delete_hard_branches);

        $role_admin->givePermissionTo($permission_create_emission_points);
        $role_admin->givePermissionTo($permission_read_emission_points);
        $role_admin->givePermissionTo($permission_update_emission_points);
        $role_admin->givePermissionTo($permission_delete_soft_emission_points);
        $role_admin->givePermissionTo($permission_delete_hard_emission_points);
        $role_owner->givePermissionTo($permission_create_emission_points);
        $role_owner->givePermissionTo($permission_read_emission_points);
        $role_owner->givePermissionTo($permission_update_emission_points);
        $role_owner->givePermissionTo($permission_delete_soft_emission_points);
        $role_owner->givePermissionTo($permission_delete_hard_emission_points);

        $role_admin->givePermissionTo($permission_create_customers);
        $role_admin->givePermissionTo($permission_read_customers);
        $role_admin->givePermissionTo($permission_update_customers);
        $role_admin->givePermissionTo($permission_delete_soft_customers);
        $role_admin->givePermissionTo($permission_delete_hard_customers);
        $role_owner->givePermissionTo($permission_create_customers);
        $role_owner->givePermissionTo($permission_read_customers);
        $role_owner->givePermissionTo($permission_update_customers);
        $role_owner->givePermissionTo($permission_delete_soft_customers);
        $role_owner->givePermissionTo($permission_delete_hard_customers);
        $role_supervisor->givePermissionTo($permission_create_customers);
        $role_supervisor->givePermissionTo($permission_read_customers);
        $role_supervisor->givePermissionTo($permission_update_customers);
        $role_supervisor->givePermissionTo($permission_delete_soft_customers);
        $role_employee->givePermissionTo($permission_create_customers);
        $role_employee->givePermissionTo($permission_read_customers);
        $role_employee->givePermissionTo($permission_update_customers);

        $role_admin->givePermissionTo($permission_create_users);
        $role_admin->givePermissionTo($permission_read_users);
        $role_admin->givePermissionTo($permission_update_users);
        $role_admin->givePermissionTo($permission_delete_soft_users);
        $role_admin->givePermissionTo($permission_delete_hard_users);
        $role_owner->givePermissionTo($permission_create_users);
        $role_owner->givePermissionTo($permission_read_users);
        $role_owner->givePermissionTo($permission_update_users);
        $role_owner->givePermissionTo($permission_delete_soft_users);
        $role_owner->givePermissionTo($permission_delete_hard_users);
        $role_supervisor->givePermissionTo($permission_create_users);
        $role_supervisor->givePermissionTo($permission_read_users);
        $role_supervisor->givePermissionTo($permission_update_users);
        $role_supervisor->givePermissionTo($permission_delete_soft_users);
        $role_employee->givePermissionTo($permission_create_users);
        $role_employee->givePermissionTo($permission_read_users);
        $role_employee->givePermissionTo($permission_update_users);

        $role_admin->givePermissionTo($permission_create_products);
        $role_admin->givePermissionTo($permission_read_products);
        $role_admin->givePermissionTo($permission_update_products);
        $role_admin->givePermissionTo($permission_delete_soft_products);
        $role_admin->givePermissionTo($permission_delete_hard_products);
        $role_owner->givePermissionTo($permission_create_products);
        $role_owner->givePermissionTo($permission_read_products);
        $role_owner->givePermissionTo($permission_update_products);
        $role_owner->givePermissionTo($permission_delete_soft_products);
        $role_owner->givePermissionTo($permission_delete_hard_products);
        $role_supervisor->givePermissionTo($permission_create_products);
        $role_supervisor->givePermissionTo($permission_read_products);
        $role_supervisor->givePermissionTo($permission_update_products);
        $role_supervisor->givePermissionTo($permission_delete_soft_products);
        $role_employee->givePermissionTo($permission_create_products);
        $role_employee->givePermissionTo($permission_read_products);
        $role_employee->givePermissionTo($permission_update_products);

        $role_admin->givePermissionTo($permission_create_vouchers);
        $role_admin->givePermissionTo($permission_read_vouchers);
        $role_admin->givePermissionTo($permission_update_vouchers);
        $role_admin->givePermissionTo($permission_delete_vouchers);
        $role_owner->givePermissionTo($permission_create_vouchers);
        $role_owner->givePermissionTo($permission_read_vouchers);
        $role_owner->givePermissionTo($permission_update_vouchers);
        $role_owner->givePermissionTo($permission_delete_vouchers);
        $role_supervisor->givePermissionTo($permission_create_vouchers);
        $role_supervisor->givePermissionTo($permission_read_vouchers);
        $role_supervisor->givePermissionTo($permission_update_vouchers);
        $role_supervisor->givePermissionTo($permission_delete_vouchers);
        $role_employee->givePermissionTo($permission_create_vouchers);
        $role_employee->givePermissionTo($permission_read_vouchers);
        $role_employee->givePermissionTo($permission_update_vouchers);
        $role_employee->givePermissionTo($permission_delete_vouchers);
        $role_customer->givePermissionTo($permission_read_vouchers);

        $role_admin->givePermissionTo($permission_send_vouchers);
        $role_owner->givePermissionTo($permission_send_vouchers);
        $role_supervisor->givePermissionTo($permission_send_vouchers);

        $role_admin->givePermissionTo($permission_report_vouchers);
        $role_owner->givePermissionTo($permission_report_vouchers);
        $role_supervisor->givePermissionTo($permission_report_vouchers);
        $role_employee->givePermissionTo($permission_report_vouchers);
        $role_customer->givePermissionTo($permission_report_vouchers);

        $user = Auth::user();
        $user->assignRole('admin');

        return 'PERMISOS ASIGNADOS CON EXITO';
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        //
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        //
    }
}
