<?php

namespace ElectronicInvoicing\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ApiController extends Controller
{
    public function login(Request $request)
    {
        return response()->json(['message' => 'HELLO WORLD!!']);
    }
}
