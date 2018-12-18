<?php

namespace ElectronicInvoicing\Http\Controllers;

use ElectronicInvoicing\{Company, Environment, VoucherType};
use ElectronicInvoicing\Http\Controllers\VoucherController;
use ElectronicInvoicing\Http\Logic\DraftJson;
use Illuminate\Http\Request;
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
        $draftVouchers = VoucherController::getDraftVouchers($user);
        return view('home', compact('draftVouchers'));
    }
}
