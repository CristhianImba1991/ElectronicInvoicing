<?php

namespace ElectronicInvoicing\Http\Controllers;

use ElectronicInvoicing\{Company, Customer, Branch};
use ElectronicInvoicing\Http\Requests\StoreCompanyRequest;
use ElectronicInvoicing\Rules\{ValidRUC, ValidSign};
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\File;
use Image;
use Storage;
use Validator;

class CompanyController extends Controller
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
        if ($user->hasPermissionTo('delete_hard_companies')) {
            $companies = Company::withTrashed()->get();
        } else {
            $companies = CompanyUser::getCompaniesAllowedToUser($user);
        }
        return view('companies.index', compact('companies'));
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        return view('companies.create');
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(StoreCompanyRequest $request)
    {
        Validator::make($request->all(), [
            'sign' => 'validsign:' . $request->password
        ], array('validsign' => 'Unable to read the cert store or the password is wrong.'))->validate();
        $request->merge(['keep_accounting' => $request->has('keep_accounting')]);
        $input = $request->except(['password', 'logo', 'sign']);
        if ($request->logo === NULL) {
            $input['logo'] = 'default.png';
        } else {
            $image = $request->file('logo');
            $imagename = $request->ruc . '.' . $image->getClientOriginalExtension();
            $destinationPath = public_path('/storage/logo/images');
            $img = Image::make($image->getRealPath());
            $img->resize(300, 300, function ($constraint) {
                $constraint->aspectRatio();
            });
            $img->resizeCanvas(300, 300, 'center', false, '#ffffff')->save($destinationPath . '/' . $imagename);
            $destinationPath = public_path('/storage/logo/thumbnail');
            $img = Image::make($image->getRealPath());
            $img->resize(50, 50, function ($constraint) {
                $constraint->aspectRatio();
            });
            $img->resizeCanvas(50, 50, 'center', false, '#ffffff')->save($destinationPath . '/' . $imagename);
            $input['logo'] = $imagename;
        }
        $results = array();
        openssl_pkcs12_read(file_get_contents($request->sign), $results, $request->password);
        $cert = $results['cert'];
		$pkey = $results['pkey'];
		openssl_x509_export($cert, $certout);
        Storage::put('signs/' . $request->ruc . '_cert.pem', $certout);
        Storage::put('signs/' . $request->ruc . '_pkey.pem', $pkey);
        Company::create($input);
        return redirect()->route('companies.index')->with(['status' => 'Company added successfully.']);
    }

    /**
     * Display the specified resource.
     *
     * @param  \ElectronicInvoicing\Company  $company
     * @return \Illuminate\Http\Response
     */
    public function show(Company $company)
    {
        $user = Auth::user();
        if ($user->hasRole('admin')) {
            $companies = Company::all();
        } else {
            $companies = CompanyUser::getCompaniesAllowedToUser($user);
        }
        if (in_array($company->id, $companies->pluck('id')->toArray())) {
            return view('companies.show', compact('company'));
        }
        return abort('404');
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \ElectronicInvoicing\Company  $company
     * @return \Illuminate\Http\Response
     */
    public function edit(Company $company)
    {
        $user = Auth::user();
        if ($user->hasRole('admin')) {
            $companies = Company::all();
        } else {
            $companies = CompanyUser::getCompaniesAllowedToUser($user);
        }
        if (in_array($company->id, $companies->pluck('id')->toArray())) {
            return view('companies.edit', compact('company'));
        }
        return abort('404');
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \ElectronicInvoicing\Company  $company
     * @return \Illuminate\Http\Response
     */
    public function update(StoreCompanyRequest $request, Company $company)
    {
        $user = Auth::user();
        if ($user->hasRole('admin')) {
            $companies = Company::all();
        } else {
            $companies = CompanyUser::getCompaniesAllowedToUser($user);
        }
        if (!in_array($company->id, $companies->pluck('id')->toArray())) {
            return abort('404');
        }
        $request->merge(['keep_accounting' => $request->has('keep_accounting')]);
        if ($request->has('sign')) {
            Validator::make($request->all(), [
                'sign' => 'validsign:' . $request->password
            ], array('validsign' => 'Unable to read the cert store or the password is wrong.'))->validate();
        }
        $input = $request->except(['password', 'logo', 'sign']);
        if ($request->logo !== NULL) {
            $companyOld = Company::where('ruc', $company->ruc)->first();
            if ($company->old !== 'default.png') {
                File::delete(public_path('/storage/logo/images') . '/' . $companyOld->logo);
                File::delete(public_path('/storage/logo/thumbnail') . '/' . $companyOld->logo);
            }
            $image = $request->file('logo');
            $imagename = $request->ruc . '.' . $image->getClientOriginalExtension();
            $destinationPath = public_path('/storage/logo/images');
            $img = Image::make($image->getRealPath());
            $img->resize(300, 300, function ($constraint) {
                $constraint->aspectRatio();
            });
            $img->resizeCanvas(300, 300, 'center', false, '#ffffff')->save($destinationPath . '/' . $imagename);
            $destinationPath = public_path('/storage/logo/thumbnail');
            $img = Image::make($image->getRealPath());
            $img->resize(50, 50, function ($constraint) {
                $constraint->aspectRatio();
            });
            $img->resizeCanvas(50, 50, 'center', false, '#ffffff')->save($destinationPath . '/' . $imagename);
            $input['logo'] = $imagename;
        }
        if ($request->has('sign')) {
            $results = array();
            openssl_pkcs12_read(file_get_contents($request->sign), $results, $request->password);
            $cert = $results['cert'];
    		$pkey = $results['pkey'];
    		openssl_x509_export($cert, $certout);
            Storage::put('signs/' . $request->ruc . '_cert.pem', $certout);
            Storage::put('signs/' . $request->ruc . '_pkey.pem', $pkey);
        }
        $company->fill($input)->save();
        return redirect()->route('companies.index')->with(['status' => 'Company updated successfully.']);
    }

    /**
     * Deactivate the specified resource.
     *
     * @param  \ElectronicInvoicing\Company  $company
     * @return \Illuminate\Http\Response
     */
    public function delete(Company $company)
    {
        $user = Auth::user();
        if ($user->hasRole('admin')) {
            $companies = Company::all();
        } else {
            $companies = CompanyUser::getCompaniesAllowedToUser($user);
        }
        if (!in_array($company->id, $companies->pluck('id')->toArray())) {
            return abort('404');
        }
        $company->delete();
        return redirect()->route('companies.index')->with(['status' => 'Company deactivated successfully.']);
    }

    /**
     * Restore the specified resource.
     *
     * @param  $company
     * @return \Illuminate\Http\Response
     */
    public function restore($company)
    {
        Company::withTrashed()->where('id', $company)->restore();
        return redirect()->route('companies.index')->with(['status' => 'Company activated successfully.']);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \ElectronicInvoicing\Company  $company
     * @return \Illuminate\Http\Response
     */
    public function destroy($company)
    {
        $companyOld = Company::withTrashed()->where('ruc', $company)->first();
        File::delete(public_path('/storage/logo/images') . '/' . $companyOld->logo);
        File::delete(public_path('/storage/logo/thumbnail') . '/' . $companyOld->logo);
        Storage::delete('signs/' . $companyOld->ruc . '_cert.pem');
        Storage::delete('signs/' . $companyOld->ruc . '_pkey.pem');
        $companyOld->forceDelete();
        return redirect()->route('companies.index')->with(['status' => 'Company deleted successfully.']);
    }

    /**
     * @param  \Illuminate\Http\Request  $request
     */
    public function branches(Request $request) {
        if (is_array($request->id)) {
            $branches = Branch::whereIn('company_id', $request->id)->with('company')->get();
            return $branches->toJson();
        } else if (is_string($request->id)) {
            $branches = Branch::where('company_id', $request->id)->with('company')->get();
            return $branches->toJson();
        }
    }

    /**
     * @param  \Illuminate\Http\Request  $request
     */
    public function customers(Request $request) {
        if (is_string($request->id)) {
            $customers = Company::where('id', $request->id)->first()->customers()->get();
            $customers->push(Customer::where('identification', '=', '9999999999999')->first());
            return $customers->toJson();
        }
    }
}
