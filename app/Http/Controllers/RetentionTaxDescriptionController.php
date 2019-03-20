<?php

namespace ElectronicInvoicing\Http\Controllers;

use ElectronicInvoicing\RetentionTaxDescription;
use Illuminate\Http\Request;

class RetentionTaxDescriptionController extends Controller
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
     * @param  \ElectronicInvoicing\RetentionTaxDescription  $retentionTaxDescription
     * @return \Illuminate\Http\Response
     */
    public function show(RetentionTaxDescription $retentionTaxDescription)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \ElectronicInvoicing\RetentionTaxDescription  $retentionTaxDescription
     * @return \Illuminate\Http\Response
     */
    public function edit(RetentionTaxDescription $retentionTaxDescription)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \ElectronicInvoicing\RetentionTaxDescription  $retentionTaxDescription
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, RetentionTaxDescription $retentionTaxDescription)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \ElectronicInvoicing\RetentionTaxDescription  $retentionTaxDescription
     * @return \Illuminate\Http\Response
     */
    public function destroy(RetentionTaxDescription $retentionTaxDescription)
    {
        //
    }

    /**
     * @param  \Illuminate\Http\Request  $request
     */
    public function taxDescription(Request $request){
        if (is_array($request->id)) {
            $retentionTaxDescription = RetentionTaxDescription::whereIn('id', $request->id)->get();
            return $retentionTaxDescription->toJson();
        } else if (is_string($request->id)) {
            $retentionTaxDescription = RetentionTaxDescription::where('id', $request->id)->get();
            return $retentionTaxDescription->toJson();
        }
    }
}
