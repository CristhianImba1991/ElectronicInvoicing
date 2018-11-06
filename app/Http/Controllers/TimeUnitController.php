<?php

namespace ElectronicInvoicing\Http\Controllers;

use ElectronicInvoicing\TimeUnit;
use Illuminate\Http\Request;

class TimeUnitController extends Controller
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
     * @param  \ElectronicInvoicing\TimeUnit  $timeUnit
     * @return \Illuminate\Http\Response
     */
    public function show(TimeUnit $timeUnit)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \ElectronicInvoicing\TimeUnit  $timeUnit
     * @return \Illuminate\Http\Response
     */
    public function edit(TimeUnit $timeUnit)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \ElectronicInvoicing\TimeUnit  $timeUnit
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, TimeUnit $timeUnit)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \ElectronicInvoicing\TimeUnit  $timeUnit
     * @return \Illuminate\Http\Response
     */
    public function destroy(TimeUnit $timeUnit)
    {
        //
    }

    public function timeUnits(Request $request) {
        return TimeUnit::all()->toJson();
    }
}
