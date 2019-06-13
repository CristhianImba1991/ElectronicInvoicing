<?php

namespace ElectronicInvoicing\Http\Controllers;

use ElectronicInvoicing\{Country, ForeignFiscalRegimeType};
use ElectronicInvoicing\StaticClasses\Tax;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ForeignFiscalRegimeTypeController extends Controller
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
     * @param  \ElectronicInvoicing\ForeignFiscalRegimeType  $foreignFiscalRegimeType
     * @return \Illuminate\Http\Response
     */
    public function show(ForeignFiscalRegimeType $foreignFiscalRegimeType)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \ElectronicInvoicing\ForeignFiscalRegimeType  $foreignFiscalRegimeType
     * @return \Illuminate\Http\Response
     */
    public function edit(ForeignFiscalRegimeType $foreignFiscalRegimeType)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \ElectronicInvoicing\ForeignFiscalRegimeType  $foreignFiscalRegimeType
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, ForeignFiscalRegimeType $foreignFiscalRegimeType)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \ElectronicInvoicing\ForeignFiscalRegimeType  $foreignFiscalRegimeType
     * @return \Illuminate\Http\Response
     */
    public function destroy(ForeignFiscalRegimeType $foreignFiscalRegimeType)
    {
        //
    }

    public function countries(Request $request)
    {
        if (is_string($request->id)) {
            switch ($request->id) {
                case ForeignFiscalRegimeType::where('code', 1)->first()->id:
                    $countries = Country::select('code', DB::raw('min(id)'), 'name')
                        ->distinct('code')
                        ->groupBy('code', 'name')
                        ->orderBy('name')
                        ->get();
                    break;
                case ForeignFiscalRegimeType::where('code', 2)->first()->id:
                    $countries = Country::whereNotNull('tax_haven_code')
                        ->orderBy('name')
                        ->get();
                    break;
                case ForeignFiscalRegimeType::where('code', 3)->first()->id:
                    $countries = Country::select('code', DB::raw('min(id)'), 'name')
                        ->distinct('code')
                        ->where('code', '<>', 593)
                        ->groupBy('code', 'name')
                        ->orderBy('name')
                        ->get();
                    break;
            }
            return $countries->toJson();
        }
    }
}
