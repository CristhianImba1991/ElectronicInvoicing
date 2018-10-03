<?php

namespace ElectronicInvoicing\Http\Controllers;

class CompanyUser extends Controller
{
    public static function getCompaniesAllowedToUser($user)
    {
        $emissionPoints = $user->emissionPoints()->get();
        $companies = array();
        foreach ($emissionPoints as $emissionPoint) {
            if(!in_array($emissionPoint->branch->company, $companies, true)) {
                array_push($companies, $emissionPoint);
            }
        }
        return collect($companies);
    }

    public static function getBranchesAllowedToUser($user)
    {
        $emissionPoints = $user->emissionPoints()->get();
        $branches = array();
        foreach ($emissionPoints as $emissionPoint) {
            if(!in_array($emissionPoint->branch, $branches, true)){
                array_push($branches, $emissionPoint->branch);
            }
        }
        return collect($branches);
    }
}
