<?php

namespace ElectronicInvoicing\Http\Controllers;

use ElectronicInvoicing\{Branch, Company, EmissionPoint, User};
use Illuminate\Support\Facades\Auth;
use Spatie\Permission\Models\Role;

class CompanyUser extends Controller
{
    public static function getCompaniesAllowedToUser(User $user, $withDeactivatedCompanies = true)
    {
        $branches = self::getBranchesAllowedToUser($user);
        $companies = array();
        foreach ($branches as $branch) {
            if(!self::inArray($branch->company, $companies)) {
                if ($branch->company === null && $user->hasPermissionTo('delete_hard_companies')) {
                    if ($withDeactivatedCompanies) {
                        array_push($companies, Company::withTrashed()->where('id', '=', $branch->company_id)->first());
                    } else {
                        array_push($companies, Company::where('id', '=', $branch->company_id)->first());
                    }
                } else {
                    array_push($companies, $branch->company);
                }
            }
        }
        return collect($companies);
    }

    public static function getBranchesAllowedToUser(User $user, $withDeactivatedBranches = true)
    {
        $emissionPoints = $user->emissionPoints()->withTrashed()->get();
        $branches = array();
        foreach ($emissionPoints as $emissionPoint) {
            if(!self::inArray($emissionPoint->branch, $branches)){
                if ($emissionPoint->branch === null && $user->hasPermissionTo('delete_hard_branches')) {
                    if ($withDeactivatedBranches) {
                        $branch = Branch::withTrashed()->where('id', '=', $emissionPoint->branch_id)->first();
                    } else {
                        $branch = Branch::where('id', '=', $emissionPoint->branch_id)->first();
                    }
                    if ($branch !== null) {
                        if ($branch->company !== null) {
                            array_push($branches, $branch);
                        }
                    }
                } else {
                    if ($emissionPoint->branch->company !== null) {
                        array_push($branches, $emissionPoint->branch);
                    }
                }
            }
        }
        return collect($branches);
    }

    public static function getUsersBelongingToCompany(Company $company, Role $role = null)
    {
        $branches = Branch::withTrashed()->where('company_id', '=', $company->id)->get();
        $emissionPoints = array();
        foreach ($branches as $branch) {
            foreach (EmissionPoint::withTrashed()->where('branch_id', '=', $branch->id)->get() as $emissionPoint) {
                array_push($emissionPoints, $emissionPoint);
            }
        }
        if (Auth::user()->hasPermissionTo('delete_hard_users')) {
            $allUsers = User::withTrashed()->get();
        } else {
            $allUsers = User::all();
        }
        $users = array();
        foreach ($allUsers as $user) {
            if (!$user->hasRole('admin')) {
                foreach ($user->emissionPoints()->withTrashed()->get() as $emissionPoint) {
                    if (in_array($emissionPoint->id, collect($emissionPoints)->pluck('id')->toArray(), true) && !self::inArray($user, $users)) {
                        if ($role === null) {
                            array_push($users, $user);
                            break;
                        } elseif ($user->hasRole($role)) {
                            array_push($users, $user);
                            break;
                        }
                    }
                }
            }
        }
        return collect($users);
    }

    private static function inArray($model, $array)
    {
        foreach ($array as $value) {
            if ($value->id === $model->id) {
                return true;
            }
        }
        return false;
    }
}
