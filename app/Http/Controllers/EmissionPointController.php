<?php

namespace ElectronicInvoicing\Http\Controllers;

use ElectronicInvoicing\{Branch, Company, EmissionPoint};
use ElectronicInvoicing\StaticClasses\ValidationRule;
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
        $validator = Validator::make($request->all(), ValidationRule::makeRule('emission_point', $request));
        $isValid = !$validator->fails();
        if ($isValid) {
            if ($request->method() === 'PUT') {
                $this->update($request, $emissionPoint);
                $request->session()->flash('status', trans_choice(__('message.model_updated_successfully', ['model' => trans_choice(__('view.emission_point'), 0)]), 0));
            } else {
                $this->store($request);
                $request->session()->flash('status', trans_choice(__('message.model_added_successfully', ['model' => trans_choice(__('view.emission_point'), 0)]), 0));
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
        $emissionPoints = $emissionPoints->sortBy(['branch_id', 'code']);
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
            $companies = Company::all()->sortBy('social_reason');
        } else {
            $companies = CompanyUser::getCompaniesAllowedToUser($user)->sortBy('social_reason');
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
        return redirect()->route('emission_points.index')->with(['status' => trans_choice(__('message.model_deactivated_successfully', ['model' => trans_choice(__('view.emission_point'), 0)]), 0)]);
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
        return redirect()->route('emission_points.index')->with(['status' => trans_choice(__('message.model_activated_successfully', ['model' => trans_choice(__('view.emission_point'), 0)]), 0)]);
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
        return redirect()->route('emission_points.index')->with(['status' => trans_choice(__('message.model_deleted_successfully', ['model' => trans_choice(__('view.emission_point'), 0)]), 0)]);
    }
}
