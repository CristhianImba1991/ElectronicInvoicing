<?php

namespace ElectronicInvoicing\Http\Controllers;

use DateTime;
use ElectronicInvoicing\Company;
use ElectronicInvoicing\Http\Controllers\{CompanyUser, VoucherController};
use Illuminate\Support\Facades\Auth;

class HomeController extends Controller
{
    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->middleware('auth');
    }

    /**
     * Show the application dashboard.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $user = Auth::user();
        $vouchers = VoucherController::getVouchersAllowedToUserQueryBuilder($user, 5)->get();
        if (!$user->hasRole('customer')) {
            $draftVouchers = VoucherController::getDraftVouchers($user);
            $notification = [];
            $companies = $user->hasRole('admin') ? Company::all() : CompanyUser::getCompaniesAllowedToUser($user);
            foreach ($companies as $company) {
                if (DateTime::createFromFormat('Y-m-d H:i:s', $company->sign_valid_from) > new DateTime() || DateTime::createFromFormat('Y-m-d H:i:s', $company->sign_valid_to) < new DateTime()) {
                    array_push($notification, [
                        'status' => 'danger',
                        'message' => __('view.the_sign_of_tradename_has_expired_or_is_not_within_the_validity_period_signvalidfrom_signvalidto', ['tradename' => $company->tradename, 'sign_valid_from' => $company->sign_valid_from, 'sign_valid_to' => $company->sign_valid_to])
                    ]);
                } elseif (DateTime::createFromFormat('Y-m-d H:i:s', $company->sign_valid_to) < (new DateTime())->modify('+3 week')) {
                    array_push($notification, [
                        'status' => 'warning',
                        'message' => __('view.the_sign_of_tradename_has_less_than_three_weeks_validity_it_will_expire_on_signvalidto', ['tradename' => $company->tradename, 'sign_valid_to' => $company->sign_valid_to])
                    ]);
                }
            }
            return view('home', compact(['vouchers', 'draftVouchers', 'notification']));
        }
        return view('home', compact('vouchers'));
    }
}
