<?php

namespace ElectronicInvoicing\Http\Controllers;

use ElectronicInvoicing\{Branch, Company, EmissionPoint};
use ElectronicInvoicing\Http\Requests\StoreEmissionPointRequest;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Validator;

class EmissionPointController extends Controller
{
    /**
     * @return void
     */
    public function __construct()
    {
        $this->middleware('auth');
    }

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $user = Auth::user();
        if ($user->hasRole('admin')) {
            $branches = Branch::all();
        } else {
            $branches = CompanyUser::getBranchesAllowedToUser($user);
        }
        if ($user->hasPermissionTo('delete_hard_emission_points')) {
            $emissionPoints = EmissionPoint::withTrashed()->whereIn('branch_id', $branches->pluck('id'))->get()->sortBy(['branch_id', 'code']);
        } else {
            $emissionPoints = EmissionPoint::all()->whereIn('branch_id', $branches->pluck('id'))->sortBy(['branch_id', 'code']);
        }
        return view('emission_points.index', compact('emissionPoints'));
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        $user = Auth::user();
        if ($user->hasRole('admin')) {
            $companies = Company::all();
        } else {
            $companies = CompanyUser::getCompaniesAllowedToUser($user);
        }
        return view('emission_points.create', compact('companies'));
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(StoreEmissionPointRequest $request)
    {
        Validator::make($request->all(), [
            'code' => 'uniquemultiple:emission_points,branch_id,' . $request->branch . ',code,' . $request->code
        ], array('uniquemultiple' => 'The :attribute has already been taken.'))->validate();
        $input = $request->except(['company', 'branch']);
        $input['branch_id'] = $request->branch;
        EmissionPoint::create($input);
        return redirect()->route('emission_points.index')->with(['status' => 'Emission point added successfully.']);
    }

    /**
     * Display the specified resource.
     *
     * @param  \ElectronicInvoicing\EmissionPoint  $emissionPoint
     * @return \Illuminate\Http\Response
     */
    public function show(EmissionPoint $emissionPoint)
    {
        return view('emission_points.show', compact('emissionPoint'));
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \ElectronicInvoicing\EmissionPoint  $emissionPoint
     * @return \Illuminate\Http\Response
     */
    public function edit(EmissionPoint $emissionPoint)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \ElectronicInvoicing\EmissionPoint  $emissionPoint
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, EmissionPoint $emissionPoint)
    {
        //
    }

    /**
     * Deactivate the specified resource from storage.
     *
     * @param  \ElectronicInvoicing\EmissionPoint  $emissionPoint
     * @return \Illuminate\Http\Response
     */
    public function delete(EmissionPoint $emissionPoint)
    {
        $emissionPoint->delete();
        return redirect()->route('emission_points.index')->with(['status' => 'Emission point deactivated successfully.']);
    }

    /**
     * Restore the specified resource.
     *
     * @param  $branch
     * @return \Illuminate\Http\Response
     */
    public function restore($emissionPoint)
    {
        EmissionPoint::withTrashed()->where('id', $emissionPoint)->restore();
        return redirect()->route('emission_points.index')->with(['status' => 'Emission point activated successfully.']);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  $branch
     * @return \Illuminate\Http\Response
     */
    public function destroy($emissionPoint)
    {
        $emissionPointOld = EmissionPoint::withTrashed()->where('id', $emissionPoint)->first();
        $emissionPointOld->forceDelete();
        return redirect()->route('emission_points.index')->with(['status' => 'Emission point deleted successfully.']);
    }
}
