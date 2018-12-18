<?php

namespace ElectronicInvoicing\Http\Controllers;

use ElectronicInvoicing\{Company, Customer, IdentificationType, User};
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Validator;

class CustomerController extends Controller
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
     * @param  \ElectronicInvoicing\Customer  $customer
     * @return \Illuminate\Http\Response
     */
    public function validateRequest(Request $request, Customer $customer = NULL)
    {
        if ($request->method() === 'PUT') {
            $validator = Validator::make($request->all(), [
                'company' => 'required|exists:companies,id',
                'identification_type' => 'required|exists:identification_types,id',
                'identification' => 'required|exists:customers,identification|max:20',
                'social_reason' => 'required|max:300',
                'address' => 'required|max:300',
                'phone' => 'max:30',
                'email' => 'required|max:300',
            ]);
        } else {
            $rules = [
                'company' => 'required|exists:companies,id',
                'identification_type' => 'required|exists:identification_types,id',
                'identification' => 'required|max:20',
                'social_reason' => 'required|max:300',
                'address' => 'required|max:300',
                'phone' => 'max:30',
                'email' => 'required|max:300',
            ];
            if (Customer::where('identification', '=', $request->identification)->exists()) {
                $customer = Customer::where('identification', '=', $request->identification)->first();
                $rules['identification'] .= '|uniquecustomer:company_customers,company_id,' . $request->company . ',customer_id,' . $customer->id;
            }
            $validator = Validator::make($request->all(), $rules, array(
                'uniquecustomer' => 'The :attribute has already been taken.'
            ));
        }
        $isValid = !$validator->fails();
        if ($isValid) {
            if ($request->method() === 'PUT') {
                $this->update($request, $customer);
                $request->session()->flash('status', 'Customer updated successfully.');
            } else {
                $this->store($request);
                $request->session()->flash('status', 'Customer added successfully. Remember that for the login, the customer must enter the first email provided and the identification as password.');
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
        $customers = array();
        foreach ($companies as $company) {
            foreach ($company->customers()->get() as $customer) {
                if (!in_array($customer->id, collect($customers)->pluck('id')->toArray(), true)) {
                    array_push($customers, $customer);
                }
            }
        }
        return view('customers.index', compact('customers'));
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
        $identificationTypes = IdentificationType::where('code', '!=', 7)->get();
        return view('customers.create', compact(['companies', 'identificationTypes']));
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    private function store(Request $request)
    {
        $input = $request->except(['company', 'identification_type']);
        $input['identification_type_id'] = $request->identification_type;
        $customer = Customer::create($input);
        $customer->companies()->save(Company::where('id', $request->company)->first());
        $userEmail = explode(',', $request->email)[0];
        if (!User::where('email', '=', $userEmail)->exists()) {
            $input['name'] = $request->social_reason;
            $input['email'] = $userEmail;
            $input['password'] = Hash::make($request->identification);
            $user = User::create($input);
            $user->assignRole('customer');
        } else {
            $user = User::where('email', '=', $userEmail)->first();
        }
        foreach (Customer::where('identification', '=', $request->identification)->get() as $customer) {
            if (!$user->customers()->where('id', $customer->id)->exists()) {
                $user->customers()->save($customer);
            }
        }
        return true;
    }

    /**
     * Display the specified resource.
     *
     * @param  \ElectronicInvoicing\Customer  $customer
     * @return \Illuminate\Http\Response
     */
    public function show(Customer $customer)
    {
        return view('customers.show', compact('customer'));
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \ElectronicInvoicing\Customer  $customer
     * @return \Illuminate\Http\Response
     */
    public function edit(Customer $customer)
    {
        return view('customers.edit', compact('customer'));
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \ElectronicInvoicing\Customer  $customer
     * @return \Illuminate\Http\Response
     */
    private function update(Request $request, Customer $customer)
    {
        $customer->fill($request->except(['ruc', 'company', 'identification_type_name', 'identification_type', 'identification']))->save();
        return redirect()->route('customers.index')->with(['status' => 'Customer updated successfully.']);
    }

    /**
     * Deactivate the specified resource.
     *
     * @param  \ElectronicInvoicing\Customer  $customer
     * @return \Illuminate\Http\Response
     */
    public function delete(Customer $customer)
    {
        $customer->delete();
        return redirect()->route('customers.index')->with(['status' => 'Customer deactivated successfully.']);
    }

    /**
     * Restore the specified resource.
     *
     * @param  $customer
     * @return \Illuminate\Http\Response
     */
    public function restore($customer)
    {
        Customer::withTrashed()->where('id', $customer)->restore();
        return redirect()->route('customers.index')->with(['status' => 'Customer activated successfully.']);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \ElectronicInvoicing\Customer  $customer
     * @return \Illuminate\Http\Response
     */
    public function destroy($customer)
    {
        $customerOld = Customer::withTrashed()->where('id', $customer)->first();
        $customerOld->forceDelete();
        return redirect()->route('customers.index')->with(['status' => 'Customer deleted successfully.']);
    }

    /**
     * @param  \Illuminate\Http\Request  $request
     */
    public function customers(Request $request) {
        if (is_string($request->id)) {
            $customer = Customer::where('id', $request->id)->with('identificationType')->get();
            return $customer->toJson();
        }
    }
}
