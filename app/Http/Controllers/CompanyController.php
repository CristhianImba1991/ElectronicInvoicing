<?php
namespace ElectronicInvoicing\Http\Controllers;

use ElectronicInvoicing\{Company, Customer, Branch, Quotas};
use ElectronicInvoicing\Rules\{ValidRUC, ValidSign};
use ElectronicInvoicing\StaticClasses\ValidationRule;
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
     * Validate the resource.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \ElectronicInvoicing\Company  $company
     * @return \Illuminate\Http\Response
     */
    public function validateRequest(Request $request, Company $company = NULL)
    {
        $validator = Validator::make($request->all(), ValidationRule::makeRule('company', $request));
        $isValid = !$validator->fails();
        if ($isValid) {
            if ($request->method() === 'PUT') {
                $this->update($request, $company);
                $request->session()->flash('status', trans_choice(__('message.model_updated_successfully', ['model' => trans_choice(__('view.company'), 0)]), 1));
            } else {
                $this->store($request);
                $request->session()->flash('status', trans_choice(__('message.model_added_successfully', ['model' => trans_choice(__('view.company'), 0)]), 1));
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
        if ($user->hasPermissionTo('delete_hard_companies')) {
            $companies = Company::withTrashed()->get()->sortBy('social_reason');
        } else {
            $companies = CompanyUser::getCompaniesAllowedToUser($user)->sortBy('social_reason');
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
        $quota=Quotas::all();
        return view('companies.create', compact ('quota'));

    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    private function store(Request $request)
    {
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
        $data = openssl_x509_parse($certout);
        $validFrom = \DateTime::createFromFormat('U', strval($data['validFrom_time_t']));
        $validFrom->setTimeZone(new \DateTimeZone('America/Guayaquil'));
        $input['sign_valid_from'] = $validFrom->format('Y/m/d H:i:s');
        $validTo = \DateTime::createFromFormat('U', strval($data['validTo_time_t']));
        $validTo->setTimeZone(new \DateTimeZone('America/Guayaquil'));
        $input['sign_valid_to'] = $validTo->format('Y/m/d H:i:s');
        $company = Company::create($input);
        $company->quotas()->save(Quotas::find($request->quota));
        return true;
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
        $quota=Quotas::all();

        if ($user->hasRole('admin')) {
            $companies = Company::all();
        } else {
            $companies = CompanyUser::getCompaniesAllowedToUser($user);
        }
        if (in_array($company->id, $companies->pluck('id')->toArray())) {
            return view('companies.show', compact('company','quota'));
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
        $quota= Quotas::all();

        $user = Auth::user();

        if ($user->hasRole('admin')) {
            $companies = Company::all();
        } else {
            $companies = CompanyUser::getCompaniesAllowedToUser($user);
        }
        if (in_array($company->id, $companies->pluck('id')->toArray())) {
            return view('companies.edit', compact('company','quota'));

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
    private function update(Request $request, Company $company)
    {
        $user = Auth::user();
        if ($user->hasRole('admin')) {
            $companies = Company::all();
        } else {
            $companies = CompanyUser::getCompaniesAllowedToUser($user);
        }
        if (!in_array($company->id, $companies->pluck('id')->toArray())) {
            return false;
        }
        $request->merge(['keep_accounting' => $request->has('keep_accounting')]);
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

            $data = openssl_x509_parse($certout);
            $validFrom = \DateTime::createFromFormat('U', strval($data['validFrom_time_t']));
            $validFrom->setTimeZone(new \DateTimeZone('America/Guayaquil'));
            $input['sign_valid_from'] = $validFrom->format('Y/m/d H:i:s');
            $validTo = \DateTime::createFromFormat('U', strval($data['validTo_time_t']));
            $validTo->setTimeZone(new \DateTimeZone('America/Guayaquil'));
            $input['sign_valid_to'] = $validTo->format('Y/m/d H:i:s');

        }
        $company->fill($input)->save();
        $company->quotas()->detach();
        $company->quotas()->save(Quotas::find($request->quota));
        return true;
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
        return redirect()->route('companies.index')->with(['status' => trans_choice(__('message.model_deactivated_successfully', ['model' => trans_choice(__('view.company'), 0)]), 1)]);
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
        return redirect()->route('companies.index')->with(['status' => trans_choice(__('message.model_activated_successfully', ['model' => trans_choice(__('view.company'), 0)]), 1)]);
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
        return redirect()->route('companies.index')->with(['status' => trans_choice(__('message.model_deleted_successfully', ['model' => trans_choice(__('view.company'), 0)]), 1)]);
    }

    /**
     * @param  \Illuminate\Http\Request  $request
     */

     public function quotas (Request $request){
       if (is_array($request->id)) {
           $quotas = collect();
           $companies = Company::whereIn('id', $request->id)->orderBy('social_reason')->get();
           foreach ($companies as $company) {
               foreach ($company->quotas()->get() as $quota) {
                   $quotas->push($quota);
               }
           }
           return $quotas->toJson();
       } else if (is_string($request->id)) {
           $quotas = Company::where('id', $request->id)->orderBy('social_reason')->first()->quotas()->get();
           return $quotas->toJson();
       }
  }

    public function branches(Request $request) {
        $user = Auth::user();
        if (is_array($request->id)) {
            if ($user->hasAnyRole(['admin', 'owner'])) {
                $branches = Branch::whereIn('company_id', $request->id)->with('company')->get();
            } else {
                $allowedBranches = CompanyUser::getBranchesAllowedToUser($user);
                $branches = Branch::whereIn('company_id', $request->id)->whereIn('id', $allowedBranches->pluck('id')->toArray())->with('company')->get();
            }
            return $branches->toJson();
        } else if (is_string($request->id)) {
            if ($user->hasAnyRole(['admin', 'owner'])) {
                $branches = Branch::where('company_id', $request->id)->with('company')->get();
            } else {
                $allowedBranches = CompanyUser::getBranchesAllowedToUser($user);
                $branches = Branch::where('company_id', $request->id)->whereIn('id', $allowedBranches->pluck('id')->toArray())->with('company')->get();
            }
            return $branches->toJson();
        }
    }

    /**
     * @param  \Illuminate\Http\Request  $request
     */
    public function customers(Request $request) {
        if (is_array($request->id)) {
            $customers = collect();
            $companies = Company::whereIn('id', $request->id)->orderBy('social_reason')->get();
            foreach ($companies as $company) {
                foreach ($company->customers()->get() as $customer) {
                    $customers->push($customer);
                }
            }
            $customers->push(Customer::where('identification', '=', '9999999999999')->first());
            return $customers->toJson();
        } else if (is_string($request->id)) {
            $customers = Company::where('id', $request->id)->first()->customers()->orderBy('social_reason')->get();
            $customers->push(Customer::where('identification', '=', '9999999999999')->first());
            return $customers->toJson();
        }
    }
}
