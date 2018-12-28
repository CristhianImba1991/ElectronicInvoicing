<?php

namespace ElectronicInvoicing\Http\Controllers;

use ElectronicInvoicing\Http\Controllers\VoucherController;
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
        $vouchers = VoucherController::getVouchers($user);
        if (!$user->hasRole('customer')) {
            $draftVouchers = VoucherController::getDraftVouchers($user);
            return view('home', compact(['vouchers', 'draftVouchers']));
        }
        return view('home', compact('vouchers'));
    }
}
