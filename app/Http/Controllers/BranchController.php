<?php

namespace ElectronicInvoicing\Http\Controllers;

use ElectronicInvoicing\{Branch, Company, EmissionPoint, Product, ProductTax};
use ElectronicInvoicing\Http\Requests\StoreBranchRequest;
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
            $companies = Company::all();
        } else {
            $companies = CompanyUser::getCompaniesAllowedToUser($user);
        }
        return view('branches.create', compact('companies'));
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(StoreBranchRequest $request)
    {
        Validator::make($request->all(), [
            'establishment' => 'uniquemultiple:branches,company_id,' . $request->company . ',establishment,' . $request->establishment
        ], array('uniquemultiple' => 'The :attribute has already been taken.'))->validate();
        $input = $request->except(['company']);
        $input['company_id'] = $request['company'];
        Branch::create($input);
        return redirect()->route('branches.index')->with(['status' => 'Branch added successfully.']);
    }

    /**
     * Display the specified resource.
     *
     * @param  \ElectronicInvoicing\Branch  $branch
     * @return \Illuminate\Http\Response
     */
    public function show(Branch $branch)
    {
        return view('branches.show', compact('branch'));
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \ElectronicInvoicing\Branch  $branch
     * @return \Illuminate\Http\Response
     */
    public function edit(Branch $branch)
    {
        return view('branches.edit', compact('branch'));
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \ElectronicInvoicing\Branch  $branch
     * @return \Illuminate\Http\Response
     */
    public function update(StoreBranchRequest $request, Branch $branch)
    {
        $branch->fill($request->except('company_branch'))->save();
        return redirect()->route('branches.index')->with(['status' => 'Branch updated successfully.']);
    }

    /**
     * Deactivate the specified resource.
     *
     * @param  \ElectronicInvoicing\Branch  $branch
     * @return \Illuminate\Http\Response
     */
    public function delete(Branch $branch)
    {
        $branch->delete();
        return redirect()->route('branches.index')->with(['status' => 'Branch deactivated successfully.']);
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
        return redirect()->route('branches.index')->with(['status' => 'Branch activated successfully.']);
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
        return redirect()->route('branches.index')->with(['status' => 'Branch deleted successfully.']);
    }

    /**
     * @param  \Illuminate\Http\Request  $request
     */
    public function emissionPoints(Request $request) {
        if (is_array($request->id)) {
            $emissionPoints = EmissionPoint::whereIn('branch_id', $request->id)->with(['branch', 'branch.company'])->get();
            return $emissionPoints->toJson();
        } else if (is_string($request->id)) {
            $emissionPoints = EmissionPoint::where('branch_id', $request->id)->with(['branch', 'branch.company'])->get();
            return $emissionPoints->toJson();
        }
    }

    /**
     * @param  \Illuminate\Http\Request  $request
     */
    public function products(Request $request){
        if (is_array($request->id)) {
            $products = Product::whereIn('branch_id', $request->id)->get();
            return $products->toJson();
        } else if (is_string($request->id)) {
            $products = Product::where('branch_id', $request->id)->get();
            return $products->toJson();
        }
    }
}
