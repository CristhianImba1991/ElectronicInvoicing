<?php

namespace ElectronicInvoicing\Http\Controllers;

use ElectronicInvoicing\VoucherType;
use Illuminate\Http\Request;

class VoucherTypeController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        //
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
     * @param  \ElectronicInvoicing\VoucherType  $voucherType
     * @return \Illuminate\Http\Response
     */
    public function show(VoucherType $voucherType)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \ElectronicInvoicing\VoucherType  $voucherType
     * @return \Illuminate\Http\Response
     */
    public function edit(VoucherType $voucherType)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \ElectronicInvoicing\VoucherType  $voucherType
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, VoucherType $voucherType)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \ElectronicInvoicing\VoucherType  $voucherType
     * @return \Illuminate\Http\Response
     */
    public function destroy(VoucherType $voucherType)
    {
        //
    }

    public static function updateNames()
    {
        $vt7 = VoucherType::find(7);
        $vt7->name = 'LIQUIDACIÓN DE COMPRA';
        $vt7->save();
        $vt8 = VoucherType::find(8);
        $vt8->name = 'BOLETOS ESPECTÁCULOS PÚBLICOS';
        $vt8->save();
        $vt9 = VoucherType::find(9);
        $vt9->name = 'TIQUETES DE MÁQ. REGISTRADORAS';
        $vt9->save();
    }
}
