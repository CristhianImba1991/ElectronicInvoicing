<?php

namespace ElectronicInvoicing\Http\Controllers;

use ElectronicInvoicing\{Branch, Company, EmissionPoint, User};
use ElectronicInvoicing\Http\Logic\DraftJson;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Spatie\Permission\Models\Role;
use Validator;

class UserController extends Controller
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
     * @param  \ElectronicInvoicing\User  $user
     * @return \Illuminate\Http\Response
     */
    public function validateRequest(Request $request, User $user = NULL)
    {
        if ($request->method() === 'PUT') {
            $validator = Validator::make($request->all(), [
                'role' => 'nullable|exists:roles,name|string',
                'name' => 'required|max:255',
                'email' => 'required|email|max:255',
                //'password' => 'required|min:6|confirmed',
                'company' => 'nullable|min:1',
                'company.*' => 'exists:companies,id',
                'branch' => 'nullable|min:1',
                'branch.*' => 'exists:branches,id',
                'emission_point' => 'nullable|min:1',
                'emission_point.*' => 'exists:emission_points,id',
            ], [
                'company.required_unless' => 'The :attribute field is required.',
                'branch.required_unless' => 'The :attribute field is required.',
                'emission_point.required_unless' => 'The :attribute field is required.',
            ]);
        } else {
            $validator = Validator::make($request->all(), [
                'role' => 'required|exists:roles,name|string',
                'name' => 'required|max:255',
                'email' => 'required|email|max:255|unique:users',
                'password' => 'required|min:6|confirmed',
                'company' => 'required_unless:role,admin|min:1',
                'company.*' => 'exists:companies,id',
                'branch' => 'required_unless:role,admin,owner|min:1',
                'branch.*' => 'exists:branches,id',
                'emission_point' => 'required_unless:role,admin,owner|min:1',
                'emission_point.*' => 'exists:emission_points,id',
            ], [
                'company.required_unless' => 'The :attribute field is required.',
                'branch.required_unless' => 'The :attribute field is required.',
                'emission_point.required_unless' => 'The :attribute field is required.',
            ]);
        }
        $isValid = !$validator->fails();
        if ($isValid) {
            if ($request->method() === 'PUT') {
                $this->update($request, $user);
                $request->session()->flash('status', 'User updated successfully.');
            } else {
                $this->store($request);
                $request->session()->flash('status', 'User added successfully.');
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
            $users = User::withTrashed()->where('id', '<>', $user->id)->get();
        } else {
            $users = array();
            $companies = CompanyUser::getCompaniesAllowedToUser($user);
            foreach ($companies as $company) {
                $allUsers = CompanyUser::getUsersBelongingToCompany($company);
                foreach ($allUsers as $userAux) {
                    if (!in_array($userAux->id, collect($users)->pluck('id')->toArray(), true) && $userAux->hasAnyRole(Role::where('id', '>=', Role::findByName($user->getRoleNames()->where('id', '<>', Role::findByName('customer')->id)->first())->id)->get()) && $user->id !== $userAux->id) {
                        array_push($users, $userAux);
                    }
                }
            }
        }
        return view('users.index', compact('users'));
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
        $roles = Role::all()->where('id', '>=', Role::findByName($user->getRoleNames()->first())->id)->where('id', '<>', Role::findByName('customer')->id);
        return view('users.create', compact(['companies', 'roles']));
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    private function store(Request $request)
    {
        $input = $request->only(['name', 'email', 'password']);
        $input['password'] = Hash::make($input['password']);
        $user = User::create($input);
        $user->assignRole($request->role);
        if (!$user->hasRole('admin')) {
            if ($user->hasRole('owner')) {
                $branches = Branch::withTrashed()->whereIn('company_id', $request->company)->get();
                $emissionPointsGroup = collect();
                foreach ($branches as $branch) {
                    $emissionPointsGroup->push(EmissionPoint::withTrashed()->where('branch_id', $branch->id)->get());
                }
                foreach ($emissionPointsGroup as $emissionPoints) {
                    foreach ($emissionPoints as $emissionPoint) {
                        $user->emissionPoints()->save(EmissionPoint::where('id', $emissionPoint->id)->first());
                    }
                }
            } else {
                foreach ($request->emission_point as $emissionPoint) {
                    $user->emissionPoints()->save(EmissionPoint::where('id', $emissionPoint)->first());
                }
            }
        }
        DraftJson::getInstance()->appendUser($user);
        return true;
    }

    /**
     * Display the specified resource.
     *
     * @param  User  $user
     * @return \Illuminate\Http\Response
     */
    public function show(User $user)
    {
        if (Auth::user()->id === $user->id) {
            return abort('404');
        }
        if (!Auth::user()->hasRole('admin')) {
            $users = array();
            $companies = CompanyUser::getCompaniesAllowedToUser(Auth::user());
            foreach ($companies as $company) {
                $allUsers = CompanyUser::getUsersBelongingToCompany($company);
                foreach ($allUsers as $userAux) {
                    if (!in_array($userAux->id, collect($users)->pluck('id')->toArray(), true) && $userAux->hasAnyRole(Role::where('id', '>=', Role::findByName(Auth::user()->getRoleNames()->where('id', '<>', Role::findByName('customer')->id)->first())->id)->get())) {
                        array_push($users, $userAux);
                    }
                }
            }
            if (!in_array($user->id, collect($users)->pluck('id')->toArray())) {
                return abort('404');
            }
        }
        return view('users.show', compact('user'));
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  User  $user
     * @return \Illuminate\Http\Response
     */
    public function edit(User $user)
    {
        if (Auth::user()->id === $user->id) {
            return abort('404');
        }
        if (!Auth::user()->hasRole('admin')) {
            $users = array();
            $companies = CompanyUser::getCompaniesAllowedToUser(Auth::user());
            foreach ($companies as $company) {
                $allUsers = CompanyUser::getUsersBelongingToCompany($company);
                foreach ($allUsers as $userAux) {
                    if (!in_array($userAux->id, collect($users)->pluck('id')->toArray(), true) && $userAux->hasAnyRole(Role::where('id', '>=', Role::findByName(Auth::user()->getRoleNames()->where('id', '<>', Role::findByName('customer')->id)->first())->id)->get())) {
                        array_push($users, $userAux);
                    }
                }
            }
            if (!in_array($user->id, collect($users)->pluck('id')->toArray())) {
                return abort('404');
            }
        }
        if (Auth::user()->hasRole('admin')) {
            $companies = Company::all();
        } else {
            $companies = CompanyUser::getCompaniesAllowedToUser(Auth::user());
        }
        $roles = Role::all()->where('id', '>=', Role::findByName(Auth::user()->getRoleNames()->first())->id)->where('id', '<>', Role::findByName('customer')->id);
        return view('users.edit', compact(['companies', 'roles', 'user']));
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    private function update(Request $request, User $user)
    {
        if ($request->role !== null) {
            $user->syncRoles($request->role);
        }
        $user->fill($request->only(['name', 'email']))->save();
        if (!$user->hasRole('admin')) {
            if ($request->company !== null) {
                if ($user->hasRole('owner')) {
                    $branches = Branch::withTrashed()->whereIn('company_id', $request->company)->get();
                    $emissionPoints = collect();
                    foreach ($branches as $branch) {
                        $emissionPoints->push(EmissionPoint::withTrashed()->whereIn('branch_id', $branch->id)->get());
                    }
                    $user->emissionPoints()->detach();
                    foreach ($emissionPoints as $emissionPoint) {
                        $user->emissionPoints()->save(EmissionPoint::where('id', $emissionPoint->id)->first());
                    }
                } else {
                    if ($request->emission_point !== null) {
                        $user->emissionPoints()->detach();
                        foreach ($request->emission_point as $emissionPoint) {
                            $user->emissionPoints()->save(EmissionPoint::where('id', $emissionPoint)->first());
                        }
                    }
                }
            }
        }
        return true;
    }

    /**
     * Deactivate the specified resource in storage.
     *
     * @param  User  $user
     * @return \Illuminate\Http\Response
     */
    public function delete(User $user)
    {
        if (Auth::user()->id === $user->id) {
            return abort('404');
        }
        if (!Auth::user()->hasRole('admin')) {
            $users = array();
            $companies = CompanyUser::getCompaniesAllowedToUser(Auth::user());
            foreach ($companies as $company) {
                $allUsers = CompanyUser::getUsersBelongingToCompany($company);
                foreach ($allUsers as $userAux) {
                    if (!in_array($userAux->id, collect($users)->pluck('id')->toArray(), true) && $userAux->hasAnyRole(Role::where('id', '>=', Role::findByName(Auth::user()->getRoleNames()->where('id', '<>', Role::findByName('customer')->id)->first())->id)->get())) {
                        array_push($users, $userAux);
                    }
                }
            }
            if (!in_array($user->id, collect($users)->pluck('id')->toArray())) {
                return abort('404');
            }
        }
        $user->delete();
        return redirect()->route('users.index')->with(['status' => 'User deactivated successfully.']);
    }

    /**
     * Restore the specified resource in storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function restore($user)
    {
        User::withTrashed()->where('id', $user)->restore();
        return redirect()->route('users.index')->with(['status' => 'User activated successfully.']);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($user)
    {
        $userOld = User::withTrashed()->where('id', $user)->first();
        DraftJson::getInstance()->removeUser($userOld);
        $userOld->forceDelete();
        return redirect()->route('users.index')->with(['status' => 'User deleted successfully.']);
    }
}
