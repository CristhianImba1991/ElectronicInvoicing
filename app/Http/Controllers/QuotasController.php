<?php

namespace ElectronicInvoicing\Http\Controllers;

use Illuminate\Http\Request;


use ElectronicInvoicing\{Company, Quotas};
use ElectronicInvoicing\StaticClasses\ValidationRule;
use Spatie\Permission\Models\{Role};
use Validator;

class QuotasController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function __construct()
    {
        $this->middleware('auth');
    }

    /**
     * Validate the resource.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \ElectronicInvoicing\Quotas $quota
     * @return \Illuminate\Http\Response
     */
    public function validateRequest(Request $request, Quotas $quotas = NULL)
    {
        $validator = Validator::make($request->all(), ValidationRule::makeRule('quotas', $request));
        $isValid = !$validator->fails();
        if ($isValid) {
            if ($request->method() === 'PUT') {
                $this->update($request, $quotas);
                $request->session()->flash('status', trans_choice(__('message.model_updated_successfully', ['model' => trans_choice(__('view.quota'), 0)]), 1));
            } else {
                $this->store($request);
                $request->session()->flash('status', trans_choice(__('message.model_added_successfully', ['model' => trans_choice(__('view.quota'), 0)]), 1));
            }
        }
        return json_encode(array("status" => $isValid, "messages" => $validator->messages()->messages()));
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */

    public function index()
    {
        $quotas = Quotas::all();
        return view('quotas.index', compact('quotas'));
    }

    public function create()
    {

        return view('quotas.create');
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
      info($request);
        $quotas = Quotas::create($request->only(['description','max_users_owner', 'max_users_supervisor', 'max_users_employee', 'max_branches', 'max_emission_points']));

        return true;
    }

    /**
     * Display the specified resource.
     *
     * @param  \ElectronicInvoicing\Product  $product
     * @return \Illuminate\Http\Response
     */
    public function show(Quotas $quotas)



    {
        return view('quotas.show', compact('quotas'));
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \ElectronicInvoicing\Product  $product
     * @return \Illuminate\Http\Response
     */
    public function edit(Quotas $quota)
    {
      //$quota = Quotas::all();
        return view('quotas.edit', compact('quota'));

    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \ElectronicInvoicing\Product  $product
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, Quotas $quotas)
    {
        $quotas->fill($request->only(['description','max_users_owner', 'max_users_supervisor', 'max_users_employee', 'max_branches', 'max_emission_points']))->save();

        return true;
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \ElectronicInvoicing\Product  $product
     * @return \Illuminate\Http\Response
     */

    public function delete(Quotas $quota)
    {
      info($quota);
        $quota->delete();
        return redirect()->route('quotas.index')->with(['status' => trans_choice(__('message.model_deleted_successfully', ['model' => trans_choice(__('view.quota'), 0)]), 1)]);
    }


    public static function queryBranches(Company $company){
      return Company::join('branches', 'companies.id','=', 'branches.company_id')
      ->select ('branches.*')
      -> where ('companies.id', $company->id)
      ->distinct()-> count();

    }

    public static function queryEmissionPoints(Company $company){
      return Company::join('branches', 'companies.id','=', 'branches.company_id')
      -> join ('emission_points', 'branches.id', '=', 'emission_points.branch_id')
      ->select ('emission_points.*')
      -> where ('companies.id', $company->id)
      ->distinct()-> count();

    }

    public static function queryUsers(Company $company){

      return Company::join('branches', 'companies.id','=', 'branches.company_id')
      -> join ('emission_points', 'branches.id', '=', 'emission_points.branch_id')
      -> join ('is_alloweds','emission_points.id','=','is_alloweds.emission_point_id')
      -> join('users','is_alloweds.user_id','=','users.id')
      -> select ('users.*')
      -> where ('companies.id', $company->id)
      ->distinct()-> count();
    }

    public static function queryRoles(Company $company, Role $roles){

      return Company::join('branches', 'companies.id','=', 'branches.company_id')
      -> join ('emission_points', 'branches.id', '=', 'emission_points.branch_id')
      -> join ('is_alloweds','emission_points.id','=','is_alloweds.emission_point_id')
      -> join('users','is_alloweds.user_id','=','users.id')
      -> join('model_has_roles','users.id','=','model_has_roles.model_id')
      -> join('roles','model_has_roles.role_id','=','roles.id')
      -> select ('users.*')
      -> where ('companies.id', $company->id )
      -> where ('roles.id', $roles->id )
      -> distinct()-> count();
    }
    /**
     * @param  \Illuminate\Http\Request  $request
     */

}
