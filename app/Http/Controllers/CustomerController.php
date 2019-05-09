<?php

namespace ElectronicInvoicing\Http\Controllers;

use ElectronicInvoicing\{Company, Customer, IdentificationType, User};
use ElectronicInvoicing\StaticClasses\ValidationRule;
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
        $validator = Validator::make($request->all(), ValidationRule::makeRule('customer', $request));
        $isValid = !$validator->fails();
        if ($isValid) {
            if ($request->method() === 'PUT') {
                $this->update($request, $customer);
                $request->session()->flash('status', trans_choice(__('message.model_updated_successfully', ['model' => trans_choice(__('view.customer'), 0)]), 0));
            } else {
                $this->store($request);
                $request->session()->flash('status', trans_choice(__('message.model_added_successfully', ['model' => trans_choice(__('view.customer'), 0)]), 0));
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
            $companies = Company::all()->sortBy('social_reason');
        } else {
            $companies = CompanyUser::getCompaniesAllowedToUser($user)->sortBy('social_reason');
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
    public function store(Request $request)
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
            //MailController::sendMailNewUser($user, $request->identification);
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
        return redirect()->route('customers.index')->with(['status' => trans_choice(__('message.model_updated_successfully', ['model' => trans_choice(__('view.customer'), 0)]), 0)]);
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
        return redirect()->route('customers.index')->with(['status' => trans_choice(__('message.model_deactivated_successfully', ['model' => trans_choice(__('view.customer'), 0)]), 0)]);
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
        return redirect()->route('customers.index')->with(['status' => trans_choice(__('message.model_activated_successfully', ['model' => trans_choice(__('view.customer'), 0)]), 0)]);
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
        return redirect()->route('customers.index')->with(['status' => trans_choice(__('message.model_deleted_successfully', ['model' => trans_choice(__('view.customer'), 0)]), 0)]);
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
