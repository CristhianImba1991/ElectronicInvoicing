<?php

namespace ElectronicInvoicing\Http\Controllers;

use ElectronicInvoicing\{Branch, Company, EmissionPoint};
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
     * Validate the resource.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \ElectronicInvoicing\EmissionPoint  $emissionPoint
     * @return \Illuminate\Http\Response
     */
    public function validateRequest(Request $request, EmissionPoint $emissionPoint = NULL)
    {
        if ($request->method() === 'PUT') {
            $validator = Validator::make($request->all(), [
                'company' => 'required|exists:companies,id',
                'branch' => 'required|exists:branches,id',
                'code' => 'required|min:1|max:999|integer',
            ]);
        } else {
            $validator = Validator::make($request->all(), [
                'company' => 'required|exists:companies,id',
                'branch' => 'required|exists:branches,id',
                'code' => 'required|min:1|max:999|integer|uniquemultiple:emission_points,branch_id,' . $request->branch . ',code,' . $request->code,
            ], array(
                'uniquemultiple' => 'The :attribute has already been taken.'
            ));
        }
        $isValid = !$validator->fails();
        if ($isValid) {
            if ($request->method() === 'PUT') {
                $this->update($request, $emissionPoint);
                $request->session()->flash('status', 'Emission point updated successfully.');
            } else {
                $this->store($request);
                $request->session()->flash('status', 'Emission point added successfully.');
            }
        }
        return json_encode(array("status" => $isValid, "messages" => $validator->messages()->messages()));
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
            $allBranches = Branch::all();
            $branches = collect();
            foreach ($allBranches as $branch) {
                if ($branch->company !== null) {
                    $branches->push($branch);
                }
            }
        } else {
            $branches = CompanyUser::getBranchesAllowedToUser($user);
        }
        if ($user->hasPermissionTo('delete_hard_emission_points')) {
            $allEmissionPoints = EmissionPoint::withTrashed()->whereIn('branch_id', $branches->pluck('id'))->get()->sortBy(['branch_id', 'code']);
        } else {
            $allEmissionPoints = EmissionPoint::all()->whereIn('branch_id', $branches->pluck('id'))->sortBy(['branch_id', 'code']);
        }
        $emissionPoints = collect();
        foreach ($allEmissionPoints as $emissionPoint) {
            if ($emissionPoint->branch !== null) {
                $emissionPoints->push($emissionPoint);
            }
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
    private function store(Request $request)
    {
        $user = Auth::user();
        $input = $request->except(['company', 'branch']);
        $input['branch_id'] = $request->branch;
        $emissionPoint = EmissionPoint::create($input);
        if ($user->hasRole('owner')) {
            $user->emissionPoints()->save($emissionPoint);
        }
        return true;
    }

    /**
     * Display the specified resource.
     *
     * @param  \ElectronicInvoicing\EmissionPoint  $emissionPoint
     * @return \Illuminate\Http\Response
     */
    public function show(EmissionPoint $emissionPoint)
    {
        $user = Auth::user();
        if ($user->hasRole('admin')) {
            $allBranches = Branch::all();
            $branches = collect();
            foreach ($allBranches as $branch) {
                if ($branch->company !== null) {
                    $branches->push($branch);
                }
            }
        } else {
            $branches = CompanyUser::getBranchesAllowedToUser($user);
        }
        if ($user->hasPermissionTo('delete_hard_emission_points')) {
            $allEmissionPoints = EmissionPoint::withTrashed()->whereIn('branch_id', $branches->pluck('id'))->get()->sortBy(['branch_id', 'code']);
        } else {
            $allEmissionPoints = EmissionPoint::all()->whereIn('branch_id', $branches->pluck('id'))->sortBy(['branch_id', 'code']);
        }
        $emissionPoints = collect();
        foreach ($allEmissionPoints as $amissionPointAux) {
            if ($amissionPointAux->branch !== null) {
                $emissionPoints->push($amissionPointAux);
            }
        }
        if (in_array($emissionPoint->id, $emissionPoints->pluck('id')->toArray())) {
            return view('emission_points.show', compact('emissionPoint'));
        }
        return abort('404');
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
    private function update(Request $request, EmissionPoint $emissionPoint)
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
        $user = Auth::user();
        if ($user->hasRole('admin')) {
            $allBranches = Branch::all();
            $branches = collect();
            foreach ($allBranches as $branch) {
                if ($branch->company !== null) {
                    $branches->push($branch);
                }
            }
        } else {
            $branches = CompanyUser::getBranchesAllowedToUser($user);
        }
        if ($user->hasPermissionTo('delete_hard_emission_points')) {
            $allEmissionPoints = EmissionPoint::withTrashed()->whereIn('branch_id', $branches->pluck('id'))->get()->sortBy(['branch_id', 'code']);
        } else {
            $allEmissionPoints = EmissionPoint::all()->whereIn('branch_id', $branches->pluck('id'))->sortBy(['branch_id', 'code']);
        }
        $emissionPoints = collect();
        foreach ($allEmissionPoints as $amissionPointAux) {
            if ($amissionPointAux->branch !== null) {
                $emissionPoints->push($amissionPointAux);
            }
        }
        if (!in_array($emissionPoint->id, $emissionPoints->pluck('id')->toArray())) {
            return abort('404');
        }
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
