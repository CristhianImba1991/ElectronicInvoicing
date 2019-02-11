<?php

namespace ElectronicInvoicing\Http\Controllers;

use ElectronicInvoicing\{RetentionTax, RetentionTaxDescription};
use Illuminate\Http\Request;

class RetentionTaxController extends Controller
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
     * @param  \ElectronicInvoicing\RetentionTax  $retentionTax
     * @return \Illuminate\Http\Response
     */
    public function show(RetentionTax $retentionTax)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \ElectronicInvoicing\RetentionTax  $retentionTax
     * @return \Illuminate\Http\Response
     */
    public function edit(RetentionTax $retentionTax)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \ElectronicInvoicing\RetentionTax  $retentionTax
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, RetentionTax $retentionTax)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \ElectronicInvoicing\RetentionTax  $retentionTax
     * @return \Illuminate\Http\Response
     */
    public function destroy(RetentionTax $retentionTax)
    {
        //
    }

    /**
     * @param  \Illuminate\Http\Request  $request
     */
    public function retentionTaxes(Request $request){
        return RetentionTax::all()->toJson();
    }

    /**
     * @param  \Illuminate\Http\Request  $request
     */
    public function retentionTaxDescriptions(Request $request){
        if (is_array($request->id)) {
            $retentionTaxDescriptions = RetentionTaxDescription::whereIn('retention_tax_id', $request->id)->get();
            return $retentionTaxDescriptions->toJson();
        } else if (is_string($request->id)) {
            $retentionTaxDescriptions = RetentionTaxDescription::where('retention_tax_id', $request->id)->get();
            return $retentionTaxDescriptions->toJson();
        }
    }
}
