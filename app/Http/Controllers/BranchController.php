<?php

namespace ElectronicInvoicing\Http\Controllers;

use ElectronicInvoicing\{Branch, Company, EmissionPoint, Product, ProductTax};
use ElectronicInvoicing\StaticClasses\ValidationRule;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Validator;

class BranchController extends Controller
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
     * @param  \ElectronicInvoicing\Branch  $branch
     * @return \Illuminate\Http\Response
     */
    public function validateRequest(Request $request, Branch $branch = NULL)
    {
        $validator = Validator::make($request->all(), ValidationRule::makeRule('branch', $request));
        $isValid = !$validator->fails();
        if ($isValid) {
            if ($request->method() === 'PUT') {
                $this->update($request, $branch);
                $request->session()->flash('status', trans_choice(__('message.model_updated_successfully', ['model' => trans_choice(__('view.branch'), 0)]), 1));
            } else {
                $this->store($request);
                $request->session()->flash('status', trans_choice(__('message.model_added_successfully', ['model' => trans_choice(__('view.branch'), 0)]), 1));
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
            $companies = Company::all();
        } else {
            $companies = CompanyUser::getCompaniesAllowedToUser($user);
        }
        if ($user->hasPermissionTo('delete_hard_branches')) {
            $branches = Branch::withTrashed()->whereIn('company_id', $companies->pluck('id'))->get()->sortBy(['company_id', 'establishment']);
        } else {
            $branches = Branch::all()->whereIn('company_id', $companies->pluck('id'))->sortBy(['company_id', 'establishment']);
        }
        return view('branches.index', compact('branches'));
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
        return view('branches.create', compact('companies'));
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    private function store(Request $request)
    {
        $input = $request->except(['company']);
        $input['company_id'] = $request['company'];
        Branch::create($input);
        return true;
    }

    /**
     * Display the specified resource.
     *
     * @param  \ElectronicInvoicing\Branch  $branch
     * @return \Illuminate\Http\Response
     */
    public function show(Branch $branch)
    {
        $user = Auth::user();
        if ($user->hasRole('admin')) {
            $companies = Company::all();
        } else {
            $companies = CompanyUser::getCompaniesAllowedToUser($user);
        }
        if ($branch->company !== null) {
            if (in_array($branch->company->id, $companies->pluck('id')->toArray())) {
                return view('branches.show', compact('branch'));
            }
        }
        return abort('404');
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \ElectronicInvoicing\Branch  $branch
     * @return \Illuminate\Http\Response
     */
    public function edit(Branch $branch)
    {
        $user = Auth::user();
        if ($user->hasRole('admin')) {
            $companies = Company::all();
        } else {
            $companies = CompanyUser::getCompaniesAllowedToUser($user);
        }
        if ($branch->company !== null) {
            if (in_array($branch->company->id, $companies->pluck('id')->toArray())) {
                return view('branches.edit', compact('branch'));
            }
        }
        return abort('404');
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \ElectronicInvoicing\Branch  $branch
     * @return \Illuminate\Http\Response
     */
    private function update(Request $request, Branch $branch)
    {
        $user = Auth::user();
        if ($user->hasRole('admin')) {
            $companies = Company::all();
        } else {
            $companies = CompanyUser::getCompaniesAllowedToUser($user);
        }
        if ($branch->company !== null) {
            if (!in_array($branch->company->id, $companies->pluck('id')->toArray())) {
                return false;
            }
        } else {
            return false;
        }
        $branch->fill($request->except('company_branch'))->save();
        return true;
    }

    /**
     * Deactivate the specified resource.
     *
     * @param  \ElectronicInvoicing\Branch  $branch
     * @return \Illuminate\Http\Response
     */
    public function delete(Branch $branch)
    {
        $user = Auth::user();
        if ($user->hasRole('admin')) {
            $companies = Company::all();
        } else {
            $companies = CompanyUser::getCompaniesAllowedToUser($user);
        }
        if ($branch->company !== null) {
            if (!in_array($branch->company->id, $companies->pluck('id')->toArray())) {
                return abort('404');
            }
        } else {
            return abort('404');
        }
        $branch->delete();
        return redirect()->route('branches.index')->with(['status' => trans_choice(__('message.model_deactivated_successfully', ['model' => trans_choice(__('view.branch'), 0)]), 1)]);
    }

    /**
     * Restore the specified resource.
     *
     * @param  $branch
     * @return \Illuminate\Http\Response
     */
    public function restore($branch)
    {
        Branch::withTrashed()->where('id', $branch)->restore();
        return redirect()->route('branches.index')->with(['status' => trans_choice(__('message.model_activated_successfully', ['model' => trans_choice(__('view.branch'), 0)]), 1)]);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \ElectronicInvoicing\Branch  $branch
     * @return \Illuminate\Http\Response
     */
    public function destroy($branch)
    {
        $branchOld = Branch::withTrashed()->where('id', $branch)->first();
        $branchOld->forceDelete();
        return redirect()->route('branches.index')->with(['status' => trans_choice(__('message.model_deleted_successfully', ['model' => trans_choice(__('view.branch'), 0)]), 1)]);
    }

    /**
     * @param  \Illuminate\Http\Request  $request
     */
    public function emissionPoints(Request $request) {
        $user = Auth::user();
        if (is_array($request->id)) {
            if ($user->hasAnyRole(['admin', 'owner'])) {
                $emissionPoints = EmissionPoint::whereIn('branch_id', $request->id)->with(['branch', 'branch.company'])->get();
            } else {
                $emissionPoints = EmissionPoint::whereIn('branch_id', $request->id)->whereIn('id', $user->emissionPoints()->pluck('id')->toArray())->with(['branch', 'branch.company'])->get();
            }
            return $emissionPoints->toJson();
        } else if (is_string($request->id)) {
            if ($user->hasAnyRole(['admin', 'owner'])) {
                $emissionPoints = EmissionPoint::where('branch_id', $request->id)->with(['branch', 'branch.company'])->get();
            } else {
                $emissionPoints = EmissionPoint::where('branch_id', $request->id)->whereIn('id', $user->emissionPoints()->pluck('id')->toArray())->with(['branch', 'branch.company'])->get();
            }
            return $emissionPoints->toJson();
        }
    }

    /**
     * @param  \Illuminate\Http\Request  $request
     */
    public function products(Request $request){
        if (is_array($request->id)) {
            $products = Product::whereIn('branch_id', $request->id)->orderBy('main_code')->get();
            return $products->toJson();
        } else if (is_string($request->id)) {
            $products = Product::where('branch_id', $request->id)->orderBy('main_code')->get();
            return $products->toJson();
        }
    }
}
