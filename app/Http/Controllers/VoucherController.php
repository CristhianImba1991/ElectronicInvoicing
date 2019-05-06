<?php

namespace ElectronicInvoicing\Http\Controllers;

use Chumper\Zipper\Zipper;
use DateTime;
use DateTimeZone;
use ElectronicInvoicing\{
    AdditionalDetail,
    AdditionalDetailAddressee,
    AdditionalField,
    Addressee,
    Branch,
    Company,
    CreditNote,
    Currency,
    Customer,
    DebitNote,
    DebitNoteTax,
    Detail,
    DetailAddressee,
    EmissionPoint,
    Environment,
    IceTax,
    IdentificationType,
    IrbpnrTax,
    IvaTax,
    Payment,
    PaymentMethod,
    Product,
    Retention,
    RetentionDetail,
    RetentionTax,
    RetentionTaxDescription,
    TaxDetail,
    TimeUnit,
    User,
    Voucher,
    VoucherState,
    VoucherType,
    Waybill
};
use ElectronicInvoicing\Http\Controllers\CompanyUser;
use ElectronicInvoicing\Http\Logic\DraftJson;
use ElectronicInvoicing\StaticClasses\{VoucherStates, ValidationRule};
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Str;
use PDF;
use SoapClient;
use Spatie\ArrayToXml\ArrayToXml;
use Storage;
use Validator;

class VoucherController extends Controller
{
    /**
     * @return void
     */
    public function __construct()
    {
        $this->middleware('auth');
    }

    public static function isValidRequest(Request $request, $state)
    {
        if ($request->voucher_type !== NULL) {
            $isApiRequest = Str::startsWith($request->decodedPath(), 'api/auth');
            if ($request->voucher_type === $isApiRequest ? "7" : 5) {
                if ($isApiRequest) {
                    if ($request->customer !== NULL && $request->issue_date_support_document !== NULL && $request->voucher_type_support_document !== NULL && $request->supportdocument_establishment !== NULL && $request->supportdocument_emissionpoint !== NULL && $request->supportdocument_sequential !== NULL) {
                        $issueDateSupportDocument = DateTime::createFromFormat('Y-m-d', $request->issue_date_support_document);
                        if ($issueDateSupportDocument) {
                            $request->merge([
                                'support_document' => $request->customer .
                                    $issueDateSupportDocument->format('dmY') .
                                    str_pad(strval($request->voucher_type_support_document), 2, '0', STR_PAD_LEFT) .
                                    str_pad(strval($request->supportdocument_establishment), 3, '0', STR_PAD_LEFT) .
                                    str_pad(strval($request->supportdocument_emissionpoint), 3, '0', STR_PAD_LEFT) .
                                    str_pad(strval($request->supportdocument_sequential), 9, '0', STR_PAD_LEFT)
                            ]);
                        }
                    }
                } else {
                    if ($request->customer !== NULL && $request->issue_date_support_document !== NULL && $request->voucher_type_support_document !== NULL && $request->supportdocument_establishment !== NULL && $request->supportdocument_emissionpoint !== NULL && $request->supportdocument_sequential !== NULL) {
                        $issueDateSupportDocument = DateTime::createFromFormat('Y-m-d', $request->issue_date_support_document);
                        if ($issueDateSupportDocument) {
                            $customer = Customer::find($request->customer);
                            $voucherTypeSupportDocument = VoucherType::find($request->voucher_type_support_document);
                            $request->merge([
                                'support_document' => ($customer === NULL ? '' : $customer->identification) .
                                    $issueDateSupportDocument->format('dmY') .
                                    str_pad(strval($voucherTypeSupportDocument === NULL ? '' : $voucherTypeSupportDocument->code), 2, '0', STR_PAD_LEFT) .
                                    str_pad(strval($request->supportdocument_establishment), 3, '0', STR_PAD_LEFT) .
                                    str_pad(strval($request->supportdocument_emissionpoint), 3, '0', STR_PAD_LEFT) .
                                    str_pad(strval($request->supportdocument_sequential), 9, '0', STR_PAD_LEFT)
                            ]);
                        }
                    }
                }
            }
        }
        return Validator::make($request->all(), ValidationRule::makeRule('voucher', $request, $state));
    }

    /**
     * Validate the resource.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \ElectronicInvoicing\Company  $company
     * @return \Illuminate\Http\Response
     */
    public function validateRequest(Request $request, $state, $voucher = NULL)
    {
        $validator = self::isValidRequest($request, $state);
        $isValid = !$validator->fails();
        if ($isValid) {
            if ($request->method() === 'PUT') {
                $this->update($request, $state, $voucher);
                $request->session()->flash('status', trans_choice(__('message.model_updated_successfully', ['model' => trans_choice(__('view.voucher'), 0)]), 0));
            } else {
                $this->store($request, $state);
                $request->session()->flash('status', trans_choice(__('message.model_added_successfully', ['model' => trans_choice(__('view.voucher'), 0)]), 0));
            }
        }
        return json_encode(array("status" => $isValid, "messages" => $validator->messages()->messages()));
    }

    private static function getVoucherQueryBuilder()
    {
        return Voucher::join('emission_points', 'emission_points.id', '=', 'vouchers.emission_point_id')
            ->join('branches', 'branches.id', '=', 'emission_points.branch_id')
            ->join('companies', 'companies.id', '=', 'branches.company_id')
            ->join('users', 'users.id', '=', 'vouchers.user_id')
            ->join('customers', 'customers.id', '=', 'vouchers.customer_id')
            ->join('environments', 'environments.id', '=', 'vouchers.environment_id')
            ->join('voucher_states', 'voucher_states.id', '=', 'vouchers.voucher_state_id')
            ->join('voucher_types', 'voucher_types.id', '=', 'vouchers.voucher_type_id')
            ->select('vouchers.*');
    }

    public static function getVouchersAllowedToUserQueryBuilder(User $user, $limit = NULL)
    {
        if ($user->hasRole('admin')) {
            $query = self::getVoucherQueryBuilder();
        } elseif ($user->hasRole('owner')) {
            $branches = CompanyUser::getBranchesAllowedToUser($user, false);
            $emissionPoints = collect();
            foreach ($branches as $branch) {
                foreach ($branch->emissionPoints()->get() as $emissionPoint) {
                    $emissionPoints->push($emissionPoint);
                }
            }
            $query = self::getVoucherQueryBuilder()
                ->whereIn('vouchers.emission_point_id', $emissionPoints->pluck('id'));
        } elseif ($user->hasRole('supervisor')) {
            $branches = CompanyUser::getBranchesAllowedToUser($user, false);
            $allEmissionPoints = collect();
            foreach ($branches as $branch) {
                foreach ($branch->emissionPoints()->get() as $emissionPoint) {
                    $allEmissionPoints->push($emissionPoint);
                }
            }
            $emissionPoints = collect();
            foreach ($allEmissionPoints as $emissionPoint) {
                if (in_array($emissionPoint->id, $user->emissionPoints()->pluck('id')->toArray(), true)) {
                    $emissionPoints->push($emissionPoint);
                }
            }
            $query = self::getVoucherQueryBuilder()
                ->whereIn('vouchers.emission_point_id', $emissionPoints->pluck('id'));
        } elseif ($user->hasRole('employee')) {
            $branches = CompanyUser::getBranchesAllowedToUser($user, false);
            $allEmissionPoints = collect();
            foreach ($branches as $branch) {
                foreach ($branch->emissionPoints()->get() as $emissionPoint) {
                    $allEmissionPoints->push($emissionPoint);
                }
            }
            $emissionPoints = collect();
            foreach ($allEmissionPoints as $emissionPoint) {
                if (in_array($emissionPoint->id, $user->emissionPoints()->pluck('id')->toArray(), true)) {
                    $emissionPoints->push($emissionPoint);
                }
            }
            $union = self::getVoucherQueryBuilder()
                ->where('vouchers.user_id', $user->id);
            $query = self::getVoucherQueryBuilder()
                ->whereIn('vouchers.emission_point_id', $emissionPoints->pluck('id'))
                ->where('voucher_state_id', VoucherStates::AUTHORIZED)
                ->where('vouchers.user_id', '<>', $user->id)
                ->latest('vouchers.created_at')
                ->union($union);
        } elseif ($user->hasRole('customer')) {
            $query = self::getVoucherQueryBuilder()
                ->whereIn('vouchers.customer_id', $user->customers->pluck('id'))
                ->where('voucher_state_id', VoucherStates::AUTHORIZED)
                ->where('environment_id', 2);
        }
        if (!$user->hasRole('employee')) {
            $query = $query->latest('vouchers.created_at');
        }
        return $limit === NULL ? $query : $query->limit($limit);
    }

    private static function getFilteredVouchersAllowedToUserQueryBuilder(User $user, Request $criteria)
    {
        $query = self::getVouchersAllowedToUserQueryBuilder($user);
        if ($criteria->has('company')) {
            $query = $query->whereIn('companies.id', $criteria->company);
        }
        if ($criteria->has('branch')) {
            $query = $query->whereIn('branches.id', $criteria->branch);
        }
        if ($criteria->has('emission_point')) {
            $query = $query->whereIn('emission_points.id', $criteria->emission_point);
        }
        if ($criteria->has('customer')) {
            $query = $query->whereIn('customers.id', $criteria->customer);
        }
        if ($criteria->has('environment')) {
            $query = $query->whereIn('environments.id', $criteria->environment);
        }
        if ($criteria->has('voucher_state')) {
            $query = $query->whereIn('voucher_states.id', $criteria->voucher_state);
        }
        if ($criteria->has('voucher_type')) {
            $query = $query->whereIn('voucher_types.id', $criteria->voucher_type);
        }
        if ($criteria->has('issue_date_from') && $criteria->has('issue_date_to')) {
            if ($criteria->issue_date_from !== NULL && $criteria->issue_date_to !== NULL) {
                $query = $query->whereBetween('vouchers.issue_date', [$criteria->issue_date_from, $criteria->issue_date_to]);
            } else if ($criteria->issue_date_from !== NULL && $criteria->issue_date_to === NULL) {
                $query = $query->where('vouchers.issue_date', '>=', $criteria->issue_date_from);
            } else if ($criteria->issue_date_from === NULL && $criteria->issue_date_to !== NULL) {
                $query = $query->where('vouchers.issue_date', '<=', $criteria->issue_date_to);
            }
        } else if ($criteria->has('issue_date_from') && !$criteria->has('issue_date_to')) {
            if ($criteria->issue_date_from !== NULL && $criteria->issue_date_to === NULL) {
                $query = $query->where('vouchers.issue_date', '>=', $criteria->issue_date_from);
            }
        } else if (!$criteria->has('issue_date_from') && $criteria->has('issue_date_to')) {
            if ($criteria->issue_date_from === NULL && $criteria->issue_date_to !== NULL) {
                $query = $query->where('vouchers.issue_date', '<=', $criteria->issue_date_to);
            }
        }
        if ($criteria->has('sequential_from') && $criteria->has('sequential_to')) {
            if ($criteria->sequential_from !== NULL && $criteria->sequential_to !== NULL) {
                $query = $query->whereBetween('vouchers.sequential', [$criteria->sequential_from, $criteria->sequential_to]);
            } else if ($criteria->sequential_from !== NULL && $criteria->sequential_to === NULL) {
                $query = $query->where('vouchers.sequential', '>=', $criteria->sequential_from);
            } else if ($criteria->sequential_from === NULL && $criteria->sequential_to !== NULL) {
                $query = $query->where('vouchers.sequential', '<=', $criteria->sequential_to);
            }
        } else if ($criteria->has('sequential_from') && !$criteria->has('sequential_to')) {
            if ($criteria->sequential_from !== NULL && $criteria->sequential_to === NULL) {
                $query = $query->where('vouchers.sequential', '>=', $criteria->sequential_from);
            }
        } else if (!$criteria->has('sequential_from') && $criteria->has('sequential_to')) {
            if ($criteria->sequential_from === NULL && $criteria->sequential_to !== NULL) {
                $query = $query->where('vouchers.sequential', '<=', $criteria->sequential_to);
            }
        }
        return $query;
    }

    private static function doesVoucherBelongToUser(Voucher $voucher, User $user)
    {
        return self::getVouchersAllowedToUserQueryBuilder($user)
            ->where('vouchers.id', '=', $voucher->id)
            ->exists();
    }

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $user = Auth::user();
        $vouchers = self::getVouchersAllowedToUserQueryBuilder($user)->get();
        if ($user->hasRole('admin')) {
            $companies = Company::all();
        } else {
            $companies = $user->hasRole('customer') ? collect() : CompanyUser::getCompaniesAllowedToUser($user);
        }
        $environments = Environment::all();
        $voucherStates = VoucherState::where('id', '>', 1)->get();
        $voucherTypes = VoucherType::whereIn('code', [1, 4, 5, 6, 7])->get();
        return view('vouchers.index', compact(['companies', 'environments', 'vouchers', 'voucherStates', 'voucherTypes']));
    }

    /**
     * Display a listing of the resource according to a filter criteria.
     *
     * @return \Illuminate\Http\Response
     */
    public function filter(Request $request)
    {
        $user = Auth::user();
        $vouchers = self::getFilteredVouchersAllowedToUserQueryBuilder($user, $request)->get();
        if ($user->hasRole('admin')) {
            $companies = Company::all();
        } else {
            $companies = $user->hasRole('customer') ? collect() : CompanyUser::getCompaniesAllowedToUser($user);
        }
        $environments = Environment::all();
        $voucherStates = VoucherState::where('id', '>', 1)->get();
        $voucherTypes = VoucherType::whereIn('code', [1, 4, 5, 6, 7])->get();
        $filter = $request->only(['company', 'branch', 'emission_point', 'customer', 'environment', 'voucher_state', 'voucher_type', 'issue_date_from', 'issue_date_to', 'sequential_from', 'sequential_to']);
        return view('vouchers.index', compact(['companies', 'environments', 'vouchers', 'voucherStates', 'voucherTypes', 'filter']));
    }

    public function download(Request $request)
    {
        $filter = array();
        foreach (explode('&', $request->filter) as $chunk) {
            $param = explode("=", $chunk);
            if ($param) {
                if (Str::endsWith(urldecode($param[0]), '[]')) {
                    if (!array_key_exists(Str::replaceLast('[]', '', urldecode($param[0])), $filter)) {
                        $filter[Str::replaceLast('[]', '', urldecode($param[0]))] = array();
                    }
                    array_push($filter[Str::replaceLast('[]', '', urldecode($param[0]))], urldecode($param[1]) === '' ? NULL : urldecode($param[1]));
                } else {
                    $filter[urldecode($param[0])] = urldecode($param[1]) === '' ? NULL : urldecode($param[1]);
                }
            }
        }
        return ReportController::download(Auth::user(), (new Request)->merge($filter), $request->type);
    }

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function indexDraft()
    {
        $user = Auth::user();
        if (!$user->hasRole('customer')) {
            $draftVouchers = VoucherController::getDraftVouchers($user);
            return view('vouchers.draft', compact('draftVouchers'));
        }
        return abort('404');
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        $user = Auth::user();
        $companies = $user->hasRole('admin') ? Company::all() : $companies = CompanyUser::getCompaniesAllowedToUser($user);
        $currencies = Currency::all();
        $environments = Environment::all();
        $identificationTypes = IdentificationType::all();
        $voucherTypes = VoucherType::whereIn('code', [1, 4, 5, 6, 7])->get();
        return view('vouchers.create', compact(['companies', 'currencies', 'environments', 'identificationTypes', 'voucherTypes']));
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request, $state)
    {
        $user = Auth::user();
        switch ($state) {
            case VoucherStates::DRAFT:
                DraftJson::getInstance()->storeDraftVoucher($user, $request);
                break;
            case VoucherStates::SAVED:
                self::saveVoucher($request, $state);
                break;
            case VoucherStates::ACCEPTED:
                $voucher = self::saveVoucher($request, $state);
                self::acceptVoucher($voucher);
                break;
            case VoucherStates::SENDED:
                $voucher = self::saveVoucher($request, $state);
                self::acceptVoucher($voucher);
                self::sendVoucher($voucher);
                break;
        }
        return true;
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function storeDraft(Request $request)
    {
        $user = Auth::user();
        DraftJson::getInstance()->storeDraftVoucher($user, $request);
        $request->session()->flash('status', 'Draft voucher added successfully.');
        return json_encode(array("status" => true, "messages" => array()));
    }

    /**
     * Display the specified resource.
     *
     * @param  \ElectronicInvoicing\Voucher  $voucher
     * @return \Illuminate\Http\Response
     */
    public function show(Voucher $voucher)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \ElectronicInvoicing\Voucher  $voucher
     * @return \Illuminate\Http\Response
     */
    public function edit(Voucher $voucher)
    {
        $user = Auth::user();
        if (self::doesVoucherBelongToUser($voucher, $user)) {
            $canEditVoucher = self::getVouchersAllowedToUserQueryBuilder($user)
                ->where('vouchers.id', '=', $voucher->id)
                ->whereNotIn('vouchers.voucher_state_id', [
                    VoucherStates::ACCEPTED,
                    VoucherStates::SENDED,
                    VoucherStates::RECEIVED,
                    VoucherStates::AUTHORIZED,
                    VoucherStates::IN_PROCESS,
                    VoucherStates::CANCELED
                ])->exists();
            if ($canEditVoucher) {
                $action = 'edit';
                $companies = $user->hasRole('admin') ? Company::all() : CompanyUser::getCompaniesAllowedToUser($user);
                $currencies = Currency::all();
                $environments = Environment::all();
                $identificationTypes = IdentificationType::all();
                $voucherTypes = VoucherType::where('id', '=', $voucher->voucher_type_id)->get();
                return view('vouchers.edit', compact(['action', 'companies', 'currencies', 'voucher', 'environments', 'identificationTypes', 'voucherTypes']));
            }
        }
        return abort('404');
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  $id
     * @return \Illuminate\Http\Response
     */
    public function editDraft($id)
    {
        $user = Auth::user();
        $action = 'draft';
        $companies = $user->hasRole('admin') ? Company::all() : CompanyUser::getCompaniesAllowedToUser($user);
        $currencies = Currency::all();
        $draftVoucher = DraftJson::getInstance()->getDraftVoucher($user, intval($id));
        $environments = Environment::all();
        $identificationTypes = IdentificationType::all();
        if ($draftVoucher['voucher_type'] === null) {
            $voucherTypes = VoucherType::whereIn('code', [1, 4, 5, 6, 7])->get();
        } else {
            $voucherTypes = VoucherType::where('id', '=', $draftVoucher['voucher_type'])->get();
        }
        return view('vouchers.edit', compact(['action', 'companies', 'currencies', 'draftVoucher', 'environments', 'identificationTypes', 'voucherTypes']));
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \ElectronicInvoicing\Voucher  $voucher
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $state, $id)
    {
        $user = Auth::user();
        switch ($state) {
            case VoucherStates::SAVED:
                self::saveVoucher($request, $state, true, $id);
                break;
            case VoucherStates::ACCEPTED:
                self::saveVoucher($request, $state, true, $id);
                self::acceptVoucher(Voucher::find($id));
                break;
            case VoucherStates::SENDED:
                self::saveVoucher($request, $state, true, $id);
                self::acceptVoucher(Voucher::find($id));
                self::sendVoucher(Voucher::find($id));
                break;
        }
        return true;//redirect()->route('home')->with(['status' => 'Voucher updated successfully.']);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \ElectronicInvoicing\Voucher  $voucher
     * @return \Illuminate\Http\Response
     */
    public function updateDraft(Request $request, $state, $voucherId)
    {
        $user = Auth::user();
        switch ($state) {
            case VoucherStates::DRAFT:
                DraftJson::getInstance()->updateDraftVoucher($user, $voucherId, $request);
                $isValid = true;
                $messages = array();
                $request->session()->flash('status', 'Draft voucher updated successfully.');
                break;
            case VoucherStates::SAVED:
                $validator = self::isValidRequest($request, $state);
                $isValid = !$validator->fails();
                if ($isValid) {
                    DraftJson::getInstance()->deleteDraftVoucher($user, $voucherId);
                    self::saveVoucher($request, VoucherStates::SAVED);
                    $request->session()->flash('status', 'Draft voucher saved successfully.');
                }
                $messages = $validator->messages()->messages();
                break;
            case VoucherStates::ACCEPTED:
                $validator = self::isValidRequest($request, $state);
                $isValid = !$validator->fails();
                if ($isValid) {
                    DraftJson::getInstance()->deleteDraftVoucher($user, $voucherId);
                    $voucher = self::saveVoucher($request, VoucherStates::ACCEPTED);
                    self::acceptVoucher($voucher);
                    $request->session()->flash('status', 'Draft voucher accepted successfully.');
                }
                $messages = $validator->messages()->messages();
                break;
            case VoucherStates::SENDED:
                $validator = self::isValidRequest($request, $state);
                $isValid = !$validator->fails();
                if ($isValid) {
                    DraftJson::getInstance()->deleteDraftVoucher($user, $voucherId);
                    $voucher = self::saveVoucher($request, VoucherStates::SENDED);
                    self::acceptVoucher($voucher);
                    self::sendVoucher($voucher);
                    $request->session()->flash('status', 'Draft voucher sended successfully.');
                }
                $messages = $validator->messages()->messages();
                break;
        }
        return json_encode(array("status" => $isValid, "messages" => $messages));
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \ElectronicInvoicing\Voucher  $voucher
     * @return \Illuminate\Http\Response
     */
    public function destroy(Voucher $voucher)
    {
        //
    }

    /**
     * Remove the specified resource from JSON file.
     *
     * @param  $id
     * @return \Illuminate\Http\Response
     */
    public function destroyDraft($id)
    {
        $user = Auth::user();
        DraftJson::getInstance()->deleteDraftVoucher($user, intval($id));
        $draftVouchers = self::getDraftVouchers($user);
        return redirect()->route('home')->with(['status' => 'Draft voucher deleted successfully.', 'draftVouchers' => $draftVouchers]);
    }

    public static function getDraftVouchers(User $user)
    {
        $draftVouchers = DraftJson::getInstance()->getDraftVouchers($user);
        $draftVouchers = array_splice($draftVouchers, -5);
        for ($i = 0; $i < count($draftVouchers); $i++) {
            if ($draftVouchers[$i]['company']) {
                $draftVouchers[$i]['company'] = Company::find($draftVouchers[$i]['company']);
            }
            if ($draftVouchers[$i]['environment']) {
                $draftVouchers[$i]['environment'] = Environment::find($draftVouchers[$i]['environment']);
            }
            if ($draftVouchers[$i]['voucher_type']) {
                $draftVouchers[$i]['voucher_type'] = VoucherType::find($draftVouchers[$i]['voucher_type']);
            }
        }
        return $draftVouchers;
    }

    /**
     * Return the requested voucher
     *
     * @return \Illuminate\Http\Response
     */
    public function getVoucherView($id, $voucherId = null)
    {
        $action = $voucherId == null ? 'create' : 'edit';
        $user = Auth::user();
        switch ($id) {
            case 1:
                $companiesproduct = $user->hasRole('admin') ? Company::all() : CompanyUser::getCompaniesAllowedToUser($user);
                $iva_taxes = IvaTax::all()->sortBy(['auxiliary_code']);
                $ice_taxes = IceTax::all()->sortBy(['auxiliary_code']);
                $irbpnr_taxes = IrbpnrTax::all()->sortBy(['auxiliary_code']);
                $data = ['action', 'companiesproduct', 'iva_taxes', 'ice_taxes', 'irbpnr_taxes'];
                break;
            case 2:
                $companiesproduct = $user->hasRole('admin') ? Company::all() : CompanyUser::getCompaniesAllowedToUser($user);
                $iva_taxes = IvaTax::all()->sortBy(['auxiliary_code']);
                $ice_taxes = IceTax::all()->sortBy(['auxiliary_code']);
                $irbpnr_taxes = IrbpnrTax::all()->sortBy(['auxiliary_code']);
                $data = ['action', 'companiesproduct', 'iva_taxes', 'ice_taxes', 'irbpnr_taxes'];
                break;
            case 3:
                $iva_taxes = IvaTax::all()->sortBy(['auxiliary_code']);
                $data = ['action', 'iva_taxes'];
                break;
            case 4:
                $identificationTypes = IdentificationType::where('code', '!=', 7)->get();
                $companiesproduct = $user->hasRole('admin') ? Company::all() : CompanyUser::getCompaniesAllowedToUser($user);
                $iva_taxes = IvaTax::all()->sortBy(['auxiliary_code']);
                $ice_taxes = IceTax::all()->sortBy(['auxiliary_code']);
                $irbpnr_taxes = IrbpnrTax::all()->sortBy(['auxiliary_code']);
                $data = ['action', 'companiesproduct', 'iva_taxes', 'ice_taxes', 'irbpnr_taxes', 'identificationTypes'];
                break;
            case 5:
                $voucherTypes = VoucherType::whereIn('code', [1, 2, 3, 5, 8, 9])->get();
                $data = ['action', 'voucherTypes'];
                break;
        }
        if ($action === 'edit') {
            $voucher = Voucher::find($voucherId);
            array_push($data, 'voucher');
        }
        return view('vouchers.' . $id, compact($data));
    }

    /**
     * Return the requested voucher
     *
     * @return \Illuminate\Http\Response
     */
    public function getDraftVoucherView($id, $voucherId)
    {
        $user = Auth::user();
        $action = 'draft';
        $draftVoucher = DraftJson::getInstance()->getDraftVoucher($user, intval($voucherId));
        switch ($id) {
            case 1:
                $companiesproduct = $user->hasRole('admin') ? Company::all() : CompanyUser::getCompaniesAllowedToUser($user);
                $iva_taxes = IvaTax::all()->sortBy(['auxiliary_code']);
                $ice_taxes = IceTax::all()->sortBy(['auxiliary_code']);
                $irbpnr_taxes = IrbpnrTax::all()->sortBy(['auxiliary_code']);
                $data = ['action', 'companiesproduct', 'draftVoucher', 'iva_taxes', 'ice_taxes', 'irbpnr_taxes'];
                break;
            case 2:
                $companiesproduct = $user->hasRole('admin') ? Company::all() : CompanyUser::getCompaniesAllowedToUser($user);
                $iva_taxes = IvaTax::all()->sortBy(['auxiliary_code']);
                $ice_taxes = IceTax::all()->sortBy(['auxiliary_code']);
                $irbpnr_taxes = IrbpnrTax::all()->sortBy(['auxiliary_code']);
                $data = ['action', 'companiesproduct', 'draftVoucher', 'iva_taxes', 'ice_taxes', 'irbpnr_taxes'];
                break;
            case 3:
                $iva_taxes = IvaTax::all()->sortBy(['auxiliary_code']);
                $data = ['action', 'draftVoucher', 'iva_taxes'];
                break;
            case 4:
                $identificationTypes = IdentificationType::where('code', '!=', 7)->get();
                $companiesproduct = $user->hasRole('admin') ? Company::all() : CompanyUser::getCompaniesAllowedToUser($user);
                $iva_taxes = IvaTax::all()->sortBy(['auxiliary_code']);
                $ice_taxes = IceTax::all()->sortBy(['auxiliary_code']);
                $irbpnr_taxes = IrbpnrTax::all()->sortBy(['auxiliary_code']);
                $data = ['action', 'draftVoucher', 'companiesproduct', 'iva_taxes', 'ice_taxes', 'irbpnr_taxes', 'identificationTypes'];
                break;
            case 5:
                $voucherTypes = VoucherType::whereIn('code', [1, 2, 3, 5, 8, 9])->get();
                $data = ['action', 'draftVoucher', 'voucherTypes'];
                break;
        }
        return view('vouchers.' . $id, compact($data));
    }

    public static function generateRandomNumericCode()
    {
        for ($i = 0; $i < 8; $i++) {
            $numericCode[$i] = rand(0, 9);
        }
    	return implode($numericCode);
    }

    private static function hex2Base64($hex)
    {
        $base64 = '';
        foreach (str_split($hex, 2) as $pair) {
            $base64 .= chr(hexdec($pair));
        }
        return base64_encode($base64);
    }

    private static function generateRandomNumber($digits)
    {
        if ($digits > 9) {
            return generateRandomNumber($digits - 9) . generateRandomNumber(9);
        }
        return "" . rand(pow(10, $digits - 1), pow(10, $digits) - 1);
    }

    private static function moveXmlFile($voucher, $to)
    {
        $fromFolder = substr($voucher->xml, 19, strpos(substr($voucher->xml, 19), '/'));
        $toFolder = VoucherState::find($to)->name;
        Storage::move($voucher->xml, str_replace('/' . $fromFolder . '/', '/' . $toFolder . '/', $voucher->xml));
        $voucher->xml = str_replace('/' . $fromFolder . '/', '/' . $toFolder . '/', $voucher->xml);
        $voucher->save();
    }

    public static function saveVoucher($request, $state, $isUpdate = false, $id = null)
    {
        $company = Company::find($request->company);
        $branch = Branch::find($request->branch);
        $emissionPoint = EmissionPoint::find($request->emission_point);
        $customer = Customer::find($request->customer);
        $currency = Currency::find($request->currency);
        $environment = Environment::find($request->environment);
        $voucherType = VoucherType::find($request->voucher_type);
        $issueDate = DateTime::createFromFormat('Y-m-d', $request->issue_date);
        if ($isUpdate) {
            $voucher = Voucher::find($id);
        } else {
            $voucherState = VoucherState::find(VoucherStates::SAVED);
            $sequential = Voucher::where([
                ['emission_point_id', '=', $emissionPoint->id],
                ['voucher_type_id', '=', $voucherType->id],
                ['environment_id', '=', $environment->id],
                ['voucher_state_id', '<', VoucherStates::SENDED],
            ])->max('sequential') + 1;
            $voucher = new Voucher;
            $voucher->voucher_state_id = $voucherState->id;
            $voucher->sequential = $sequential;
            $voucher->user_id = Auth::user()->id;
            $voucher->numeric_code = self::generateRandomNumericCode();
        }

        $voucher->emission_point_id = $emissionPoint->id;
        $voucher->voucher_type_id = $voucherType->id;
        $voucher->environment_id = $environment->id;
        $voucher->customer_id = $customer->id;
        $voucher->issue_date = $issueDate->format('Y-m-d');
        $voucher->currency_id = $currency->id;
        $voucher->tip = NULL;
        $voucher->extra_detail = $request->extra_detail;



        switch ($voucherType->id) {
            case 1:
                $voucher->tip = $request->tip;
                $voucher->iva_retention = $request->ivaRetentionValue;
                $voucher->rent_retention = $request->rentRetentionValue;
                if ($request->waybill_establishment !== NULL && $request->waybill_emissionpoint !== NULL && $request->waybill_sequential !== NULL) {
                    $voucher->support_document = str_pad($request->waybill_establishment, 3, '0', STR_PAD_LEFT) . str_pad($request->waybill_emissionpoint, 3, '0', STR_PAD_LEFT) . str_pad($request->waybill_sequential, 9, '0', STR_PAD_LEFT);
                }
                $voucher->support_document_date = NULL;
                $voucher->save();
                $products = $request->product;
                $additionalDetail1 = $request->product_detail1;
                $additionalDetail2 = $request->product_detail2;
                $additionalDetail3 = $request->product_detail3;
                $quantities = $request->product_quantity;
                $unitPrices = $request->product_unitprice;
                $discounts = $request->product_discount;
                foreach ($voucher->details()->get() as $detail) {
                    foreach ($detail->taxDetails as $taxDetail) {
                        $taxDetail->delete();
                    }
                    foreach ($detail->additionalDetails as $additionalDetail) {
                        $additionalDetail->delete();
                    }
                    $detail->delete();
                }
                for ($i = 0; $i < count($products); $i++) {
                    $product = Product::find($products[$i]);
                    $ivaTax = IvaTax::find($product->taxes()->first()->iva_tax_id);
                    //$iceTax = IceTax::find($product->taxes()->first()->ice_tax_id);
                    //$irbpnrTax = IrbpnrTax::find($product->taxes()->first()->irbpnr_tax_id);
                    $detail = new Detail;
                    $detail->voucher_id = $voucher->id;
                    $detail->product_id = $product->id;
                    $detail->quantity = $quantities[$i];
                    $detail->unit_price = $unitPrices[$i];
                    $detail->discount = $discounts[$i];
                    $detail->save();
                    $taxDetail = new TaxDetail;
                    $taxDetail->detail_id = $detail->id;
                    $taxDetail->code = $ivaTax->code;
                    $taxDetail->percentage_code = $ivaTax->auxiliary_code;
                    $taxDetail->rate = $ivaTax->rate;
                    $taxDetail->tax_base = $detail->quantity * $detail->unit_price - $detail->discount;
                    $taxDetail->value = ($detail->quantity * $detail->unit_price - $detail->discount) * $ivaTax->rate / 100.0;
                    $taxDetail->save();
                    if ($additionalDetail1[$i] !== NULL) {
                        $additionalDetail = new AdditionalDetail;
                        $additionalDetail->detail_id = $detail->id;
                        $additionalDetail->name = "Detalle adicional";
                        $additionalDetail->value = $additionalDetail1[$i];
                        $additionalDetail->save();
                    }
                    if ($additionalDetail2[$i] !== NULL) {
                        $additionalDetail = new AdditionalDetail;
                        $additionalDetail->detail_id = $detail->id;
                        $additionalDetail->name = "Detalle adicional";
                        $additionalDetail->value = $additionalDetail2[$i];
                        $additionalDetail->save();
                    }
                    if ($additionalDetail3[$i] !== NULL) {
                        $additionalDetail = new AdditionalDetail;
                        $additionalDetail->detail_id = $detail->id;
                        $additionalDetail->name = "Detalle adicional";
                        $additionalDetail->value = $additionalDetail3[$i];
                        $additionalDetail->save();
                    }
                }
                foreach (Payment::where('voucher_id', '=', $voucher->id)->get() as $payment) {
                    $payment->delete();
                }
                $paymentMethods = $request->paymentMethod;
                $values = $request->paymentMethod_value;
                $timeUnits = $request->paymentMethod_timeunit;
                $terms = $request->paymentMethod_term;
                for ($i = 0; $i < count($paymentMethods); $i++) {
                    $payment = new Payment;
                    $payment->voucher_id = $voucher->id;
                    $payment->payment_method_id = $paymentMethods[$i];
                    $payment->time_unit_id = $timeUnits[$i];
                    $payment->total = $values[$i];
                    $payment->term = $terms[$i];
                    $payment->save();
                }
                break;
            case 2:
                $issueDateSupportDocument = DateTime::createFromFormat('Y-m-d', $request->issue_date_support_document);
                $voucher->support_document = $issueDateSupportDocument->format('dmY') . '01' .
                    str_pad($request->supportdocument_establishment, 3, '0', STR_PAD_LEFT) .
                    str_pad($request->supportdocument_emissionpoint, 3, '0', STR_PAD_LEFT) .
                    str_pad($request->supportdocument_sequential, 9, '0', STR_PAD_LEFT);
                $voucher->support_document_date = $issueDateSupportDocument->format('Y-m-d');
                $voucher->save();
                $products = $request->product;
                $additionalDetail1 = $request->product_detail1;
                $additionalDetail2 = $request->product_detail2;
                $additionalDetail3 = $request->product_detail3;
                $quantities = $request->product_quantity;
                $unitPrices = $request->product_unitprice;
                $discounts = $request->product_discount;
                foreach (Detail::where('voucher_id', '=', $voucher->id)->get() as $detail) {
                    foreach ($detail->taxDetails as $taxDetail) {
                        $taxDetail->delete();
                    }
                    foreach ($detail->additionalDetails as $additionalDetail) {
                        $additionalDetail->delete();
                    }
                    $detail->delete();
                }
                for ($i = 0; $i < count($products); $i++) {
                    $product = Product::find($products[$i]);
                    $ivaTax = IvaTax::find($product->taxes()->first()->iva_tax_id);
                    //$iceTax = IceTax::find($product->taxes()->first()->ice_tax_id);
                    //$irbpnrTax = IrbpnrTax::find($product->taxes()->first()->irbpnr_tax_id);
                    $detail = new Detail;
                    $detail->voucher_id = $voucher->id;
                    $detail->product_id = $product->id;
                    $detail->quantity = $quantities[$i];
                    $detail->unit_price = $unitPrices[$i];
                    $detail->discount = $discounts[$i];
                    $detail->save();
                    $taxDetail = new TaxDetail;
                    $taxDetail->detail_id = $detail->id;
                    $taxDetail->code = $ivaTax->code;
                    $taxDetail->percentage_code = $ivaTax->auxiliary_code;
                    $taxDetail->rate = $ivaTax->rate;
                    $taxDetail->tax_base = $detail->quantity * $detail->unit_price - $detail->discount;
                    $taxDetail->value = ($detail->quantity * $detail->unit_price - $detail->discount) * $ivaTax->rate / 100.0;
                    $taxDetail->save();
                    if ($additionalDetail1[$i] !== NULL) {
                        $additionalDetail = new AdditionalDetail;
                        $additionalDetail->detail_id = $detail->id;
                        $additionalDetail->name = "Detalle adicional";
                        $additionalDetail->value = $additionalDetail1[$i];
                        $additionalDetail->save();
                    }
                    if ($additionalDetail2[$i] !== NULL) {
                        $additionalDetail = new AdditionalDetail;
                        $additionalDetail->detail_id = $detail->id;
                        $additionalDetail->name = "Detalle adicional";
                        $additionalDetail->value = $additionalDetail2[$i];
                        $additionalDetail->save();
                    }
                    if ($additionalDetail3[$i] !== NULL) {
                        $additionalDetail = new AdditionalDetail;
                        $additionalDetail->detail_id = $detail->id;
                        $additionalDetail->name = "Detalle adicional";
                        $additionalDetail->value = $additionalDetail3[$i];
                        $additionalDetail->save();
                    }
                }
                foreach (CreditNote::where('voucher_id', '=', $voucher->id)->get() as $creditNote) {
                    $creditNote->delete();
                }
                $creditNote = new CreditNote;
                $creditNote->voucher_id = $voucher->id;
                $creditNote->reason = $request->reason;
                $creditNote->save();
                break;
            case 3:
                $issueDateSupportDocument = DateTime::createFromFormat('Y-m-d', $request->issue_date_support_document);
                $voucher->support_document = $issueDateSupportDocument->format('dmY') . '01' .
                    str_pad($request->supportdocument_establishment, 3, '0', STR_PAD_LEFT) .
                    str_pad($request->supportdocument_emissionpoint, 3, '0', STR_PAD_LEFT) .
                    str_pad($request->supportdocument_sequential, 9, '0', STR_PAD_LEFT);
                $voucher->support_document_date = $issueDateSupportDocument->format('Y-m-d');
                $voucher->save();
                foreach (Payment::where('voucher_id', '=', $voucher->id)->get() as $payment) {
                    $payment->delete();
                }
                $paymentMethods = $request->paymentMethod;
                $values = $request->paymentMethod_value;
                $timeUnits = $request->paymentMethod_timeunit;
                $terms = $request->paymentMethod_term;
                for ($i = 0; $i < count($paymentMethods); $i++) {
                    $payment = new Payment;
                    $payment->voucher_id = $voucher->id;
                    $payment->payment_method_id = $paymentMethods[$i];
                    $payment->time_unit_id = $timeUnits[$i];
                    $payment->total = $values[$i];
                    $payment->term = $terms[$i];
                    $payment->save();
                }
                foreach (DebitNoteTax::where('voucher_id', '=', $voucher->id)->get() as $debitNoteTax) {
                    foreach ($debitNoteTax->debitNotes as $debitNote) {
                        $debitNote->delete();
                    }
                    $debitNoteTax->delete();
                }
                $debitReasons = $request->debit_reason;
                $debitValues = $request->debit_value;
                $taxBase = array_sum($debitValues);
                $ivaTax = IvaTax::find($request->iva_tax);
                $debitNoteTax = new DebitNoteTax;
                $debitNoteTax->voucher_id = $voucher->id;
                $debitNoteTax->code = $ivaTax->code;
                $debitNoteTax->percentage_code = $ivaTax->auxiliary_code;
                $debitNoteTax->rate = $ivaTax->rate;
                $debitNoteTax->tax_base = $taxBase;
                $debitNoteTax->save();
                for ($i = 0; $i < count($debitReasons); $i++) {
                    $debitNote = new DebitNote;
                    $debitNote->debit_note_tax_id = $debitNoteTax->id;
                    $debitNote->reason = $debitReasons[$i];
                    $debitNote->value = $debitValues[$i];
                    $debitNote->save();
                }
                break;
            case 4:
                $voucher->save();
                foreach (Waybill::where('voucher_id', '=', $voucher->id)->get() as $waybill) {
                    foreach ($waybill->addressees as $addressee) {
                        foreach ($addressee->details as $detail) {
                            foreach ($detail->additionalDetails as $additionalDetail) {
                                $additionalDetail->delete();
                            }
                            $detail->delete();
                        }
                        $addressee->delete();
                    }
                    $waybill->delete();
                }
                $waybill = new Waybill;
                $waybill->voucher_id = $voucher->id;
                $waybill->identification_type_id = $request->identification_type;
                $waybill->carrier_ruc = $request->carrier_ruc;
                $waybill->carrier_social_reason = $request->carrier_social_reason;
                $waybill->starting_address = $request->starting_address;
                $waybill->start_date_transport = $request->start_date_transport;
                $waybill->end_date_transport = $request->end_date_transport;
                $waybill->licence_plate = $request->licence_plate;
                $waybill->save();
                $addressee = new Addressee;
                $addressee->waybill_id = $waybill->id;
                $addressee->customer_id = Customer::find($request->customer)->id;
                $addressee->address = $request->address;
                $addressee->transfer_reason = $request->transfer_reason;
                $addressee->single_customs_doc = $request->single_customs_doc;
                $addressee->destination_establishment_code = $request->destination_establishment_code;
                $addressee->route = $request->route;
                $addressee->support_doc_code = $request->authorization_number;
                $addressee->save();
                $products = $request->product;
                $additionalDetail1 = $request->product_detail1;
                $additionalDetail2 = $request->product_detail2;
                $additionalDetail3 = $request->product_detail3;
                $quantities = $request->product_quantity;
                for ($i = 0; $i < count($products); $i++) {
                    $detailAddressee = new DetailAddressee;
                    $detailAddressee->addressee_id = $addressee->id;
                    $detailAddressee->product_id = Product::find($products[$i])->id;
                    $detailAddressee->quantity = $quantities[$i];
                    $detailAddressee->save();
                    if ($additionalDetail1[$i] !== NULL) {
                        $additionalDetailAddressee = new AdditionalDetailAddressee;
                        $additionalDetailAddressee->detail_addressee_id = $detailAddressee->id;
                        $additionalDetailAddressee->name = "Detalle adicional";
                        $additionalDetailAddressee->value = $additionalDetail1[$i];
                        $additionalDetailAddressee->save();
                    }
                    if ($additionalDetail2[$i] !== NULL) {
                        $additionalDetailAddressee = new AdditionalDetailAddressee;
                        $additionalDetailAddressee->detail_addressee_id = $detailAddressee->id;
                        $additionalDetailAddressee->name = "Detalle adicional";
                        $additionalDetailAddressee->value = $additionalDetail2[$i];
                        $additionalDetailAddressee->save();
                    }
                    if ($additionalDetail3[$i] !== NULL) {
                        $additionalDetailAddressee = new AdditionalDetailAddressee;
                        $additionalDetailAddressee->detail_addressee_id = $detailAddressee->id;
                        $additionalDetailAddressee->name = "Detalle adicional";
                        $additionalDetailAddressee->value = $additionalDetail3[$i];
                        $additionalDetailAddressee->save();
                    }
                }
                break;
            case 5:
                $voucher->support_document = DateTime::createFromFormat('Y-m-d', $request->issue_date_support_document)->format('dmY') .
                    str_pad(strval(VoucherType::find($request->voucher_type_support_document)->code), 2, '0', STR_PAD_LEFT) .
                    str_pad(strval($request->supportdocument_establishment), 3, '0', STR_PAD_LEFT) .
                    str_pad(strval($request->supportdocument_emissionpoint), 3, '0', STR_PAD_LEFT) .
                    str_pad(strval($request->supportdocument_sequential), 9, '0', STR_PAD_LEFT);
                $voucher->support_document_date = DateTime::createFromFormat('Y-m-d', $request->issue_date_support_document)->format('Y-m-d');
                $voucher->save();
                foreach (Retention::where('voucher_id', '=', $voucher->id)->get() as $retention) {
                    foreach ($retention->details as $detail) {
                        $detail->delete();
                    }
                    $retention->delete();
                }
                $retention = new Retention;
                $retention->voucher_id = $voucher->id;
                $retention->fiscal_period = DateTime::createFromFormat('Y-m-d', $voucher->issue_date)->format('Y-m\-\0\1');
                $retention->save();
                $tax = $request->tax;
                $description = $request->description;
                $rate = $request->value;
                $taxBase = $request->tax_base;
                for ($i = 0; $i < count($tax); $i++) {
                    $retentionDetail = new RetentionDetail;
                    $retentionDetail->retention_id = $retention->id;
                    $retentionDetail->retention_tax_description_id = $description[$i];
                    $retentionDetail->tax_base = $taxBase[$i];
                    $retentionDetail->rate = $rate[$i];
                    $retentionDetail->support_doc_code = DateTime::createFromFormat('Y-m-d', $request->issue_date_support_document)->format('dmY') .
                        str_pad(strval(VoucherType::find($request->voucher_type_support_document)->code), 2, '0', STR_PAD_LEFT) .
                        str_pad(strval($request->supportdocument_establishment), 3, '0', STR_PAD_LEFT) .
                        str_pad(strval($request->supportdocument_emissionpoint), 3, '0', STR_PAD_LEFT) .
                        str_pad(strval($request->supportdocument_sequential), 9, '0', STR_PAD_LEFT);
                    $retentionDetail->save();
                }
                break;
        }
        foreach (AdditionalField::where('voucher_id', '=', $voucher->id)->get() as $additionalField) {
            $additionalField->delete();
        }
        $names = $request->additionaldetail_name;
        $values = $request->additionaldetail_value;
        if ($names !== NULL) {
            for ($i = 0; $i < count($names); $i++) {
                $additionalFields = new AdditionalField;
                $additionalFields->voucher_id = $voucher->id;
                $additionalFields->name = $names[$i];
                $additionalFields->value = $values[$i];
                $additionalFields->save();
            }
        }
        return $voucher;
    }

    public static function acceptVoucher($voucher)
    {
        $voucher->voucher_state_id = VoucherStates::ACCEPTED;
        $voucher->save();
    }

    private static function rejectVoucher()
    {
        $voucher->voucher_state_id = VoucherStates::REJECTED;
        $voucher->save();
    }

    private static function signVoucher($voucher)
    {
        $version = $voucher->version();
        $issueDate = DateTime::createFromFormat('Y-m-d', $voucher->issue_date);
        $state = VoucherState::find(VoucherStates::ACCEPTED);
        $voucherTypeCode = str_pad(strval(VoucherType::find($voucher->voucher_type_id)->code), 2, '0', STR_PAD_LEFT);
        $establishment = str_pad(strval($voucher->emissionPoint->branch->establishment), 3, '0', STR_PAD_LEFT);
        $emissionPoint = str_pad(strval($voucher->emissionPoint->code), 3, '0', STR_PAD_LEFT);
        $sequential = str_pad(strval($voucher->sequential), 9, '0', STR_PAD_LEFT);
        $accessKey = $voucher->accessKey();
        $xml['_attributes'] = ['id' => 'comprobante', 'version' => $version];
        $xml['infoTributaria'] = [
            'ambiente'          => $voucher->environment->code,
            'tipoEmision'       => 1,
            'razonSocial'       => $voucher->emissionPoint->branch->company->social_reason,
            'nombreComercial'   => $voucher->emissionPoint->branch->company->tradename,
            'ruc'               => $voucher->emissionPoint->branch->company->ruc,
            'claveAcceso'       => $accessKey,
            'codDoc'            => $voucherTypeCode,
            'estab'             => $establishment,
            'ptoEmi'            => $emissionPoint,
            'secuencial'        => $sequential,
            'dirMatriz'         => $voucher->emissionPoint->branch->company->address,
        ];
        switch ($voucher->voucher_type_id) {
            case 1:
                $root = 'factura';
                $xml['infoFactura'] = [
                    'fechaEmision'                  => $issueDate->format('d/m/Y'),
                    'dirEstablecimiento'            => $voucher->emissionPoint->branch->address,
                    'contribuyenteEspecial'         => NULL,
                    'obligadoContabilidad'          => $voucher->emissionPoint->branch->company->keep_accounting ? 'SI' : 'NO',
                    'tipoIdentificacionComprador'   => str_pad(strval($voucher->customer->identificationType->code), 2, '0', STR_PAD_LEFT),
                    'guiaRemision'                  => NULL,
                    'razonSocialComprador'          => $voucher->customer->social_reason,
                    'identificacionComprador'       => $voucher->customer->identification,
                    'direccionComprador'            => NULL,
                    'totalSinImpuestos'             => number_format($voucher->subtotalWithoutTaxes(), 2, '.', ''),
                    'totalDescuento'                => number_format($voucher->totalDiscounts(), 2, '.', ''),
                    'totalConImpuestos'             => [
                        'totalImpuesto' => array(),
                    ],
                    'propina'                       => number_format($voucher->tip, 2, '.', ''),
                    'importeTotal'                  => number_format($voucher->total(), 2, '.', ''),
                    'moneda'                        => $voucher->currency->name,
                    'pagos'                         => [
                        'pago' => array(),
                    ],
                    'valorRetIva'                     => NULL,
                    'valorRetRenta'                   => NULL,
                ];
                if ($voucher->emissionPoint->branch->company->special_contributor === NULL) {
                    unset($xml['infoFactura']['contribuyenteEspecial']);
                } else {
                    $xml['infoFactura']['contribuyenteEspecial'] = $voucher->emissionPoint->branch->company->special_contributor;
                }
                if ($voucher->customer->address === NULL) {
                    unset($xml['infoFactura']['direccionComprador']);
                } else {
                    $xml['infoFactura']['direccionComprador'] = $voucher->customer->address;
                }
                if ($voucher->support_document === NULL) {
                    unset($xml['infoFactura']['guiaRemision']);
                } else {
                    $xml['infoFactura']['guiaRemision'] = substr($voucher->support_document, 0, 3) . '-' . substr($voucher->support_document, 3, 3) . '-' . substr($voucher->support_document, 6, 9);
                }
                $totalTaxes = array();
                foreach (Detail::where('voucher_id', '=', $voucher->id)->get() as $detail) {
                    foreach ($detail->taxDetails as $tax) {
                        $totalTaxes[$tax->code . '.' . $tax->percentage_code] = array(
                            'codigo' => $tax->code,
                            'codigoPorcentaje' => $tax->percentage_code,
                            'baseImponible' => (array_key_exists($tax->code . '.' . $tax->percentage_code, $totalTaxes) ? $totalTaxes[$tax->code . '.' . $tax->percentage_code]['baseImponible'] : 0) + $tax->tax_base,
                            'valor' => (array_key_exists($tax->code . '.' . $tax->percentage_code, $totalTaxes) ? $totalTaxes[$tax->code . '.' . $tax->percentage_code]['valor'] : 0) + $tax->value,
                        );
                    }
                }
                $voucherTaxes = array();
                foreach ($totalTaxes as $totalTax) {
                    $totalTax['baseImponible'] = number_format($totalTax['baseImponible'], 2, '.', '');
                    $totalTax['valor'] = number_format($totalTax['valor'], 2, '.', '');
                    array_push($voucherTaxes, $totalTax);
                }
                $xml['infoFactura']['totalConImpuestos']['totalImpuesto'] = $voucherTaxes;
                $voucherPayments = array();
                foreach (Payment::where('voucher_id', '=', $voucher->id)->get() as $payment) {
                    array_push($voucherPayments,
                        array(
                            'formaPago' => str_pad(strval(PaymentMethod::find($payment->payment_method_id)->code), 2, '0', STR_PAD_LEFT),
                            'total' => number_format($payment->total, 2, '.', ''),
                            'plazo' => $payment->term,
                            'unidadTiempo' => TimeUnit::find($payment->time_unit_id)->name,
                        )
                    );
                }
                $xml['infoFactura']['pagos']['pago'] = $voucherPayments;
                if ($voucher->iva_retention === NULL) {
                    unset($xml['infoFactura']['valorRetIva']);
                } else {
                    $xml['infoFactura']['valorRetIva'] = number_format($voucher->iva_retention, 2, '.', '');
                }
                if ($voucher->rent_retention === NULL) {
                    unset($xml['infoFactura']['valorRetRenta']);
                } else {
                    $xml['infoFactura']['valorRetRenta'] = number_format($voucher->rent_retention === NULL ? 0.00 : $voucher->rent_retention, 2, '.', '');
                }
                $voucherDetails = array();
                foreach (Detail::where('voucher_id', '=', $voucher->id)->get() as $detail) {
                    $detailTaxes = array();
                    foreach ($detail->taxDetails as $tax) {
                        array_push($detailTaxes,
                            array(
                                'codigo'            => $tax->code,
                                'codigoPorcentaje'  => $tax->percentage_code,
                                'tarifa'            => $tax->rate,
                                'baseImponible'     => number_format($tax->tax_base, 2, '.', ''),
                                'valor'             => number_format($tax->value, 2, '.', ''),
                            )
                        );
                    }
                    $additionalDetails = array();
                    foreach ($detail->additionalDetails as $additionalDetail) {
                        if (!array_key_exists('detAdicional', $additionalDetails)) {
                            $additionalDetails['detAdicional'] = array();
                        }
                        array_push($additionalDetails['detAdicional'],
                            array(
                                '_attributes' => ['nombre' => $additionalDetail->name, 'valor' => $additionalDetail->value]
                            )
                        );
                    }
                    $voucherDetail = array(
                        'codigoPrincipal'           => $detail->product->main_code,
                        'codigoAuxiliar'            => $detail->product->auxiliary_code,
                        'descripcion'               => $detail->product->description,
                        'cantidad'                  => $version === '1.0.0' ? number_format($detail->quantity, 2, '.', '') : $detail->quantity,
                        'precioUnitario'            => $version === '1.0.0' ? number_format($detail->unit_price, 2, '.', '') : $detail->unit_price,
                        'descuento'                 => number_format($detail->discount, 2, '.', ''),
                        'precioTotalSinImpuesto'    => number_format($detail->quantity * $detail->unit_price - $detail->discount, 2, '.', ''),
                        'detallesAdicionales'       => NULL,
                        'impuestos'                 => [
                            'impuesto' => $detailTaxes,
                        ]
                    );
                    if (count($additionalDetails) === 0) {
                        unset($voucherDetail['detallesAdicionales']);
                    } else {
                        $voucherDetail['detallesAdicionales'] = $additionalDetails;
                    }
                    array_push($voucherDetails, $voucherDetail);
                }
                $xml['detalles'] = [
                    'detalle' => $voucherDetails,
                ];
                break;
            case 2:
                $root = 'notaCredito';
                $issueDate = DateTime::createFromFormat('Y-m-d', $voucher->issue_date);
                $supportDocumentDate = DateTime::createFromFormat('Y-m-d', $voucher->support_document_date);
                $xml['infoNotaCredito'] = [
                    'fechaEmision'                  => $issueDate->format('d/m/Y'),
                    'dirEstablecimiento'            => $voucher->emissionPoint->branch->address,
                    'tipoIdentificacionComprador'   => str_pad(strval($voucher->customer->identificationType->code), 2, '0', STR_PAD_LEFT),
                    'razonSocialComprador'          => $voucher->customer->social_reason,
                    'identificacionComprador'       => $voucher->customer->identification,
                    'contribuyenteEspecial'         => NULL,
                    'obligadoContabilidad'          => $voucher->emissionPoint->branch->company->keep_accounting ? 'SI' : 'NO',
                    'codDocModificado'              => '01',
                    'numDocModificado'              => substr($voucher->support_document, 10, 3) . '-' . substr($voucher->support_document, 13, 3) . '-' . substr($voucher->support_document, 16, 9),
                    'fechaEmisionDocSustento'       => $supportDocumentDate->format('d/m/Y'),
                    'totalSinImpuestos'             => number_format($voucher->subtotalWithoutTaxes(), 2, '.', ''),
                    'valorModificacion'             => number_format($voucher->total(), 2, '.', ''),
                    'moneda'                        => $voucher->currency->name,
                    'totalConImpuestos'             => [
                        'totalImpuesto' => array(),
                    ],
                    'motivo'                        => $voucher->creditNotes->first()->reason
                ];
                if ($voucher->emissionPoint->branch->company->special_contributor === NULL) {
                    unset($xml['infoNotaCredito']['contribuyenteEspecial']);
                } else {
                    $xml['infoNotaCredito']['contribuyenteEspecial'] = $voucher->emissionPoint->branch->company->special_contributor;
                }
                $totalTaxes = array();
                foreach (Detail::where('voucher_id', '=', $voucher->id)->get() as $detail) {
                    foreach ($detail->taxDetails as $tax) {
                        $totalTaxes[$tax->code . '.' . $tax->percentage_code] = array(
                            'codigo' => $tax->code,
                            'codigoPorcentaje' => $tax->percentage_code,
                            'baseImponible' => (array_key_exists($tax->code . '.' . $tax->percentage_code, $totalTaxes) ? $totalTaxes[$tax->code . '.' . $tax->percentage_code]['baseImponible'] : 0) + $tax->tax_base,
                            'valor' => (array_key_exists($tax->code . '.' . $tax->percentage_code, $totalTaxes) ? $totalTaxes[$tax->code . '.' . $tax->percentage_code]['valor'] : 0) + $tax->value,
                        );
                    }
                }
                $voucherTaxes = array();
                foreach ($totalTaxes as $totalTax) {
                    $totalTax['baseImponible'] = number_format($totalTax['baseImponible'], 2, '.', '');
                    $totalTax['valor'] = number_format($totalTax['valor'], 2, '.', '');
                    array_push($voucherTaxes, $totalTax);
                }
                $xml['infoNotaCredito']['totalConImpuestos']['totalImpuesto'] = $voucherTaxes;
                $voucherDetails = array();
                foreach (Detail::where('voucher_id', '=', $voucher->id)->get() as $detail) {
                    $detailTaxes = array();
                    foreach ($detail->taxDetails as $tax) {
                        array_push($detailTaxes,
                            array(
                                'codigo'            => $tax->code,
                                'codigoPorcentaje'  => $tax->percentage_code,
                                'tarifa'            => $tax->rate,
                                'baseImponible'     => number_format($tax->tax_base, 2, '.', ''),
                                'valor'             => number_format($tax->value, 2, '.', ''),
                            )
                        );
                    }
                    $additionalDetails = array();
                    foreach ($detail->additionalDetails as $additionalDetail) {
                        if (!array_key_exists('detAdicional', $additionalDetails)) {
                            $additionalDetails['detAdicional'] = array();
                        }
                        array_push($additionalDetails['detAdicional'],
                            array(
                                '_attributes' => ['nombre' => $additionalDetail->name, 'valor' => $additionalDetail->value]
                            )
                        );
                    }
                    $voucherDetail = array(
                        'codigoInterno'             => $detail->product->main_code,
                        'codigoAdicional'           => $detail->product->auxiliary_code,
                        'descripcion'               => $detail->product->description,
                        'cantidad'                  => $version === '1.0.0' ? number_format($detail->quantity, 2, '.', '') : $detail->quantity,
                        'precioUnitario'            => $version === '1.0.0' ? number_format($detail->unit_price, 2, '.', '') : $detail->unit_price,
                        'descuento'                 => number_format($detail->discount, 2, '.', ''),
                        'precioTotalSinImpuesto'    => number_format($detail->quantity * $detail->unit_price - $detail->discount, 2, '.', ''),
                        'detallesAdicionales'       => NULL,
                        'impuestos'                 => [
                            'impuesto' => $detailTaxes,
                        ]
                    );
                    if (count($additionalDetails) === 0) {
                        unset($voucherDetail['detallesAdicionales']);
                    } else {
                        $voucherDetail['detallesAdicionales'] = $additionalDetails;
                    }
                    array_push($voucherDetails, $voucherDetail);
                }
                $xml['detalles'] = [
                    'detalle' => $voucherDetails,
                ];
                break;
            case 3:
                $root = 'notaDebito';
                $issueDate = DateTime::createFromFormat('Y-m-d', $voucher->issue_date);
                $xml['infoNotaDebito'] = [
                    'fechaEmision'                  => $issueDate->format('d/m/Y'),
                    'dirEstablecimiento'            => $voucher->emissionPoint->branch->address,
                    'tipoIdentificacionComprador'   => str_pad(strval($voucher->customer->identificationType->code), 2, '0', STR_PAD_LEFT),
                    'razonSocialComprador'          => $voucher->customer->social_reason,
                    'identificacionComprador'       => $voucher->customer->identification,
                    'contribuyenteEspecial'         => NULL,
                    'obligadoContabilidad'          => $voucher->emissionPoint->branch->company->keep_accounting ? 'SI' : 'NO',
                    'codDocModificado'              => '01',
                    'numDocModificado'              => substr($voucher->support_document, 10, 3) . '-' . substr($voucher->support_document, 13, 3) . '-' . substr($voucher->support_document, 16, 9),
                    'fechaEmisionDocSustento'       => $issueDate->format('d/m/Y'),
                    'totalSinImpuestos'             => number_format($voucher->debitNotesTaxes()->first()->tax_base, 2, '.', ''),
                    'impuestos'                     => [
                        'impuesto' => [
                            'codigo'            => $voucher->debitNotesTaxes()->first()->code,
                            'codigoPorcentaje'  => $voucher->debitNotesTaxes()->first()->percentage_code,
                            'tarifa'            => $voucher->debitNotesTaxes()->first()->rate,
                            'baseImponible'     => number_format($voucher->debitNotesTaxes()->first()->tax_base, 2, '.', ''),
                            'valor'             => number_format($voucher->debitNotesTaxes()->first()->tax_base * $voucher->debitNotesTaxes()->first()->rate / 100.0, 2, '.', ''),
                        ],
                    ],
                    'valorTotal'                    => number_format($voucher->debitNotesTaxes()->first()->tax_base * (1 + $voucher->debitNotesTaxes()->first()->rate / 100.0), 2, '.', ''),
                    'pagos'                         => [
                        'pago' => array(),
                    ],
                ];
                if ($voucher->emissionPoint->branch->company->special_contributor === NULL) {
                    unset($xml['infoNotaDebito']['contribuyenteEspecial']);
                } else {
                    $xml['infoNotaDebito']['contribuyenteEspecial'] = $voucher->emissionPoint->branch->company->special_contributor;
                }
                $voucherPayments = array();
                foreach (Payment::where('voucher_id', '=', $voucher->id)->get() as $payment) {
                    array_push($voucherPayments,
                        array(
                            'formaPago' => str_pad(strval(PaymentMethod::find($payment->payment_method_id)->code), 2, '0', STR_PAD_LEFT),
                            'total' => number_format($payment->total, 2, '.', ''),
                            'plazo' => $payment->term,
                            'unidadTiempo' => TimeUnit::find($payment->time_unit_id)->name,
                        )
                    );
                }
                $xml['infoNotaDebito']['pagos']['pago'] = $voucherPayments;
                $voucherReasons = array();
                foreach ($voucher->debitNotesTaxes()->first()->debitNotes()->get() as $reason) {
                    $debitReason['razon'] = $reason->reason;
                    $debitReason['valor'] = $reason->value;
                    array_push($voucherReasons, $debitReason);
                }
                $xml['motivos']['motivo'] = $voucherReasons;
                break;
            case 4:
                $root = 'guiaRemision';
                $xml['infoGuiaRemision'] = [
                    'dirEstablecimiento'                => $voucher->emissionPoint->branch->address,
                    'dirPartida'                        => $voucher->waybills()->first()->starting_address,
                    'razonSocialTransportista'          => $voucher->waybills()->first()->carrier_social_reason,
                    'tipoIdentificacionTransportista'   => str_pad(strval($voucher->waybills()->first()->identificationType->code), 2, '0', STR_PAD_LEFT),
                    'rucTransportista'                  => $voucher->waybills()->first()->carrier_ruc,
                    'obligadoContabilidad'              => $voucher->emissionPoint->branch->company->keep_accounting ? 'SI' : 'NO',
                    'contribuyenteEspecial'             => NULL,
                    'fechaIniTransporte'                => DateTime::createFromFormat('Y-m-d', $voucher->waybills()->first()->start_date_transport)->format('d/m/Y'),
                    'fechaFinTransporte'                => DateTime::createFromFormat('Y-m-d', $voucher->waybills()->first()->end_date_transport)->format('d/m/Y'),
                    'placa'                             => $voucher->waybills()->first()->licence_plate
                ];
                if ($voucher->emissionPoint->branch->company->special_contributor === NULL) {
                    unset($xml['infoGuiaRemision']['contribuyenteEspecial']);
                } else {
                    $xml['infoGuiaRemision']['contribuyenteEspecial'] = $voucher->emissionPoint->branch->company->special_contributor;
                }
                $waybill = $voucher->waybills()->first();
                if ($waybill !== NULL) {
                    $voucherAddressees = array();
                    foreach (Addressee::where('waybill_id', '=', $waybill->id)->get() as $addressee) {
                        $detailAddressees = array();
                        foreach ($addressee->details as $detail) {
                            array_push($detailAddressees,
                                array(
                                    'codigoInterno'     => $detail->product->main_code,
                                    'codigoAdicional'   => $detail->product->auxiliary_code,
                                    'descripcion'       => $detail->product->description,
                                    'cantidad'          => $version === '1.0.0' ? number_format($detail->quantity, 2, '.', '') : $detail->quantity,
                                )
                            );
                        }
                        array_push($voucherAddressees,
                            array(
                                'identificacionDestinatario'    => $addressee->customer->identification,
                                'razonSocialDestinatario'       => $addressee->customer->social_reason,
                                'dirDestinatario'               => $addressee->address,
                                'motivoTraslado'                => $addressee->transfer_reason,
                                'docAduaneroUnico'              => NULL,
                                'codEstabDestino'               => NULL,
                                'ruta'                          => $addressee->route,
                                'codDocSustento'                => substr($addressee->support_doc_code, 8, 2),
                                'numDocSustento'                => substr($addressee->support_doc_code, 24, 3) . '-' . substr($addressee->support_doc_code, 27, 3) . '-' . substr($addressee->support_doc_code, 30, 9),
                                'numAutDocSustento'             => $addressee->support_doc_code,
                                'fechaEmisionDocSustento'       => DateTime::createFromFormat('dmY', substr($addressee->support_doc_code, 0, 8))->format('d/m/Y'),
                                'detalles'                      => [
                                    'detalle' => $detailAddressees,
                                ]
                            )
                        );
                        if ($addressee->single_customs_doc === NULL) {
                            unset($voucherAddressees[count($voucherAddressees) - 1]['docAduaneroUnico']);
                        } else {
                            $voucherAddressees[count($voucherAddressees) - 1]['docAduaneroUnico'] = $addressee->single_customs_doc;
                        }
                        if ($addressee->destination_establishment_code === NULL) {
                            unset($voucherAddressees[count($voucherAddressees) - 1]['codEstabDestino']);
                        } else {
                            $voucherAddressees[count($voucherAddressees) - 1]['codEstabDestino'] = str_pad(strval($addressee->destination_establishment_code), 3, '0', STR_PAD_LEFT);
                        }
                    }
                    $xml['destinatarios'] = [
                        'destinatario' => $voucherAddressees,
                    ];
                }
                break;
            case 5:
                $root = 'comprobanteRetencion';
                $xml['infoCompRetencion'] = [
                    'fechaEmision'                      => $issueDate->format('d/m/Y'),
                    'dirEstablecimiento'                => $voucher->emissionPoint->branch->address,
                    'contribuyenteEspecial'             => NULL,
                    'obligadoContabilidad'              => $voucher->emissionPoint->branch->company->keep_accounting ? 'SI' : 'NO',
                    'tipoIdentificacionSujetoRetenido'  => str_pad(strval($voucher->customer->identificationType->code), 2, '0', STR_PAD_LEFT),
                    'razonSocialSujetoRetenido'         => $voucher->customer->social_reason,
                    'identificacionSujetoRetenido'      => $voucher->customer->identification,
                    'periodoFiscal'                     => $issueDate->format('m/Y')
                ];
                if ($voucher->emissionPoint->branch->company->special_contributor === NULL) {
                    unset($xml['infoCompRetencion']['contribuyenteEspecial']);
                } else {
                    $xml['infoCompRetencion']['contribuyenteEspecial'] = $voucher->emissionPoint->branch->company->special_contributor;
                }
                $retentionDetails = array();
                foreach ($voucher->retentions->first()->details as $detail) {
                    array_push($retentionDetails,
                        array(
                            'codigo'                    => RetentionTax::find(RetentionTaxDescription::find($detail->retention_tax_description_id)->retention_tax_id)->code,
                            'codigoRetencion'           => RetentionTaxDescription::find($detail->retention_tax_description_id)->code,
                            'baseImponible'             => number_format($detail->tax_base, 2, '.', ''),
                            'porcentajeRetener'         => number_format($detail->rate, 2, '.', ''),
                            'valorRetenido'             => number_format($detail->tax_base * $detail->rate / 100.0, 2, '.', ''),
                            'codDocSustento'            => substr($detail->support_doc_code, 8, 2),
                            'numDocSustento'            => substr($detail->support_doc_code, 10),
                            'fechaEmisionDocSustento'   => DateTime::createFromFormat('dmY', substr($detail->support_doc_code, 0, 8))->format('d/m/Y')
                        )
                    );
                }
                $xml['impuestos'] = [
                    'impuesto' => $retentionDetails,
                ];
                break;
        }
        if (count($voucher->additionalFields) > 0) {
            $voucherAdditionalFields = array();
            foreach ($voucher->additionalFields as $additionalField) {
                array_push($voucherAdditionalFields,
                    array(
                        '_attributes' => ['nombre' => $additionalField->name],
                        '_value'      => $additionalField->value,
                    )
                );
            }
            $xml['infoAdicional'] = [
                'campoAdicional' => $voucherAdditionalFields,
            ];
        }
        $xmlPath = 'xmls/' .
            $voucher->emissionPoint->branch->company->ruc . '/' .
            $state->name . '/' .
            $issueDate->format('Y/m') . '/' .
            $accessKey . '.xml';
        Storage::put($xmlPath, ArrayToXml::convert($xml, $root, false, 'UTF-8'));
        $voucher->xml = $xmlPath;
        $voucher->save();



        $xml = new \DOMDocument('1.0', 'UTF-8');
        $xml->preserveWhiteSpace = true;
		$xml->formatOutput = false;
        if ($xml->load(storage_path('app/' . $voucher->xml))) {
            $voucherDocument = $xml->getElementsByTagName($root)->item(0);
            $digestValueVoucher = self::hex2Base64(sha1($voucherDocument->C14N()));

            $cert = file_get_contents(storage_path('app/signs/' . $voucher->emissionPoint->branch->company->ruc . '_cert.pem'));
            $pkey = file_get_contents(storage_path('app/signs/' . $voucher->emissionPoint->branch->company->ruc . '_pkey.pem'));
            $subCert = substr($cert, strpos($cert, "\n") + 1, strrpos($cert, "\n") - strpos($cert, "\n") - 1);
            $subCert = substr($subCert, 0, strrpos($subCert, "\n") + 1);

            $certInformation = openssl_x509_parse($cert);
            $issuerName = '';
            foreach ($certInformation['issuer'] as $key => $value) {
                $issuerName .= $key . '=' . $value . ',';
            }
            $issuerName = substr($issuerName, 0, strlen($issuerName) - 1);

            $decodedCert = base64_decode($subCert);
            $digestValueCertificate = self::hex2Base64(sha1($decodedCert));
            $filledCert = wordwrap(str_replace("\n", "", $subCert), 76, "\n", true);

            $keyDetails = openssl_pkey_get_details(openssl_pkey_get_private($pkey));
            $modulus = wordwrap(base64_encode($keyDetails['rsa']['n']), 76, "\n", true );

            $CertificateNumber = self::generateRandomNumber(7);
            $SignatureNumber = self::generateRandomNumber(6);
            $SignedPropertiesNumber = self::generateRandomNumber(5);
            $SignedInfoNumber = self::generateRandomNumber(6);
            $SignedPropertiesIDNumber = self::generateRandomNumber(6);
            $ReferenceIDNumber = self::generateRandomNumber(6);
            $SignatureValueNumber = self::generateRandomNumber(6);
            $ObjectNumber = self::generateRandomNumber(6);

            $signature = $xml->createElement('ds:Signature');
            $signature->setAttribute('xmlns:ds', 'http://www.w3.org/2000/09/xmldsig#');
            $signature->setAttribute('xmlns:etsi', 'http://uri.etsi.org/01903/v1.3.2#');
            $signature->setAttribute('Id', 'Signature' . $SignatureNumber);
            $voucherDocument->appendChild($signature);

            $SignedInfo = $xml->createElement('ds:SignedInfo');
            $SignedInfo->setAttribute('Id', 'Signature-SignedInfo' . $SignedInfoNumber);
            $signature->appendChild($SignedInfo);
            $CanonicalizationMethod = $xml->createElement('ds:CanonicalizationMethod');
            $CanonicalizationMethod->setAttribute('Algorithm', 'http://www.w3.org/TR/2001/REC-xml-c14n-20010315');
            $SignedInfo->appendChild($CanonicalizationMethod);

            $SignatureMethod = $xml->createElement('ds:SignatureMethod');
            $SignatureMethod->setAttribute('Algorithm', 'http://www.w3.org/2000/09/xmldsig#rsa-sha1');
            $SignedInfo->appendChild($SignatureMethod);

            $Reference1 = $xml->createElement('ds:Reference');
            $Reference1->setAttribute('Id', 'SignedPropertiesID' . $SignedPropertiesIDNumber);
            $Reference1->setAttribute('Type', 'http://uri.etsi.org/01903#SignedProperties');
            $Reference1->setAttribute('URI', '#Signature' . $SignatureNumber . '-SignedProperties' . $SignedPropertiesNumber);
            $SignedInfo->appendChild($Reference1);
            $DigestMethod = $xml->createElement('ds:DigestMethod', '');
            $Reference1->appendChild($DigestMethod);
            $DigestMethod->setAttribute('Algorithm', 'http://www.w3.org/2000/09/xmldsig#sha1');
            $DigestValue1 = $xml->createElement('ds:DigestValue');
            $Reference1->appendChild($DigestValue1);

            $Reference2 = $xml->createElement('ds:Reference');
            $Reference2->setAttribute('URI', '#Certificate' . $CertificateNumber);
            $SignedInfo->appendChild($Reference2);
            $DigestMethod = $xml->createElement('ds:DigestMethod', '');
            $DigestMethod->setAttribute('Algorithm', 'http://www.w3.org/2000/09/xmldsig#sha1');
            $Reference2->appendChild($DigestMethod);
            $DigestValue2 = $xml->createElement('ds:DigestValue');
            $Reference2->appendChild($DigestValue2);

            $Reference3 = $xml->createElement('ds:Reference');
            $Reference3->setAttribute('Id', 'Reference-ID-' . $ReferenceIDNumber);
            $Reference3->setAttribute('URI', '#comprobante');
            $SignedInfo->appendChild($Reference3);
            $Transforms = $xml->createElement('ds:Transforms');
            $Reference3->appendChild($Transforms);
            $Transform = $xml->createElement('ds:Transform');
            $Transform->setAttribute('Algorithm', 'http://www.w3.org/2000/09/xmldsig#enveloped-signature');
            $Transforms->appendChild($Transform);
            $DigestMethod = $xml->createElement('ds:DigestMethod');
            $DigestMethod->setAttribute('Algorithm', 'http://www.w3.org/2000/09/xmldsig#sha1');
            $Reference3->appendChild($DigestMethod);
            $DigestValue3 = $xml->createElement('ds:DigestValue', $digestValueVoucher);
            $Reference3->appendChild($DigestValue3);

            $SignatureValue = $xml->createElement('ds:SignatureValue');
            $SignatureValue->setAttribute('Id', 'SignatureValue' . $SignatureValueNumber);
            $signature->appendChild($SignatureValue);

            $KeyInfo = $xml->createElement('ds:KeyInfo');
            $KeyInfo->setAttribute('Id', 'Certificate' . $CertificateNumber);
            $signature->appendChild($KeyInfo);
            $X509Data = $xml->createElement('ds:X509Data');
            $KeyInfo->appendChild($X509Data);
            $X509Certificate = $xml->createElement('ds:X509Certificate', "\n" . $filledCert . "\n");
            $X509Data->appendChild($X509Certificate);
            $KeyValue = $xml->createElement('ds:KeyValue');
            $KeyInfo->appendChild($KeyValue);
            $RSAKeyValue = $xml->createElement('ds:RSAKeyValue');
            $KeyValue->appendChild($RSAKeyValue);
            $Modulus = $xml->createElement('ds:Modulus', "\n" . $modulus . "\n");
            $RSAKeyValue->appendChild($Modulus);
            $Exponent = $xml->createElement('ds:Exponent', 'AQAB');
            $RSAKeyValue->appendChild($Exponent);

            $xml->save(storage_path('app/' . $voucher->xml), LIBXML_NOEMPTYTAG);
            $xml->load(storage_path('app/' . $voucher->xml), LIBXML_NOEMPTYTAG);

            $Object = $xml->createElement('ds:Object');
            $Object->setAttribute('Id', 'Signature' . $SignatureNumber . '-Object' . $ObjectNumber);
            $signature = $xml->getElementsByTagName('Signature')->item(0);
            $signature->appendChild($Object);

            $QualifyingProperties = $xml->createElement('etsi:QualifyingProperties');
            $QualifyingProperties->setAttribute('Target', '#Signature' . $SignatureNumber);
            $Object->appendChild($QualifyingProperties);

            $SignedProperties = $xml->createElement('etsi:SignedProperties');
            $SignedProperties->setAttribute('Id', 'Signature' . $SignatureNumber . '-SignedProperties' . $SignedPropertiesNumber);
            $QualifyingProperties->appendChild($SignedProperties);
            $SignedSignatureProperties = $xml->createElement('etsi:SignedSignatureProperties');
            $SignedProperties->appendChild($SignedSignatureProperties);
            $date = new DateTime('now', new DateTimeZone('America/Guayaquil'));
            $SigningTime = $xml->createElement('etsi:SigningTime', $date->format('c'));
            $SignedSignatureProperties->appendChild($SigningTime);
            $SigningCertificate = $xml->createElement('etsi:SigningCertificate');
            $SignedSignatureProperties->appendChild($SigningCertificate);
            $Cert = $xml->createElement('etsi:Cert');
            $SigningCertificate->appendChild($Cert);
            $CertDigest = $xml->createElement('etsi:CertDigest');
            $Cert->appendChild($CertDigest);
            $DigestMethod = $xml->createElement('ds:DigestMethod');
            $DigestMethod->setAttribute('Algorithm', 'http://www.w3.org/2000/09/xmldsig#sha1');
            $CertDigest->appendChild($DigestMethod);
            $DigestValue = $xml->createElement('ds:DigestValue', $digestValueCertificate);
            $CertDigest->appendChild($DigestValue);
            $IssuerSerial = $xml->createElement('etsi:IssuerSerial');
            $Cert->appendChild($IssuerSerial);
            $X509IssuerName = $xml->createElement('ds:X509IssuerName', $issuerName);
            $IssuerSerial->appendChild($X509IssuerName);
            $X509SerialNumber = $xml->createElement('ds:X509SerialNumber', $certInformation['serialNumber']);
            $IssuerSerial->appendChild($X509SerialNumber);
            $SignedDataObjectProperties = $xml->createElement('etsi:SignedDataObjectProperties');
            $SignedProperties->appendChild($SignedDataObjectProperties);
            $DataObjectFormat = $xml->createElement('etsi:DataObjectFormat');
            $DataObjectFormat->setAttribute('ObjectReference', '#Reference-ID-' . $ReferenceIDNumber);
            $SignedDataObjectProperties->appendChild($DataObjectFormat);
            $SignedDataObjectProperties = $xml->createElement('etsi:Description', 'contenido comprobante' );
            $DataObjectFormat->appendChild($SignedDataObjectProperties);
            $SignedDataObjectProperties = $xml->createElement('etsi:MimeType', 'text/xml');
            $DataObjectFormat->appendChild($SignedDataObjectProperties);

            $xml->save(storage_path('app/' . $voucher->xml), LIBXML_NOEMPTYTAG);
            $xml->load(storage_path('app/' . $voucher->xml), LIBXML_NOEMPTYTAG);

        	$reference1 = $xml->getElementsByTagName('Reference')->item(0);
        	$DigestValue1 = $reference1->getElementsByTagName('DigestValue')->item(0);
        	$text = $xml->createTextNode(self::hex2Base64(sha1($xml->getElementsByTagName('SignedProperties')->item(0)->C14N())));
        	$text = $DigestValue1->appendChild($text);

        	$reference2 = $xml->getElementsByTagName('Reference')->item(1);
        	$DigestValue2 = $reference2->getElementsByTagName('DigestValue')->item(0);
        	$text = $xml->createTextNode(self::hex2Base64(sha1($xml->getElementsByTagName('KeyInfo')->item(0)->C14N())));
        	$text = $DigestValue2->appendChild($text);

            $xml->save(storage_path('app/' . $voucher->xml), LIBXML_NOEMPTYTAG);
            $xml->load(storage_path('app/' . $voucher->xml), LIBXML_NOEMPTYTAG);

            openssl_sign($xml->getElementsByTagName('SignedInfo')->item(0)->C14N(), $signature, $pkey);
            $valorFirma = wordwrap(base64_encode($signature), 76, "\n", true);

            $SignatureValue = $xml->getElementsByTagName('SignatureValue')->item( 0 );
        	$text = $xml->createTextNode("\n" . $valorFirma . "\n");
        	$text = $SignatureValue->appendChild($text);

            $xml->save(storage_path('app/' . $voucher->xml), LIBXML_NOEMPTYTAG);
        }
    }

    public static function sendVoucher($voucher)
    {
        $voucher->voucher_state_id = VoucherStates::SENDED;
        $sequential = Voucher::where([
            ['emission_point_id', '=', $voucher->emission_point_id],
            ['voucher_type_id', '=', $voucher->voucher_type_id],
            ['environment_id', '=', $voucher->environment_id],
            ['voucher_state_id', '>=', VoucherStates::SENDED],
        ])->max('sequential') + 1;
        $voucher->sequential = $sequential;
        $voucher->save();
        self::signVoucher($voucher);
        self::moveXmlFile($voucher, VoucherStates::SENDED);
        $wsdlReceipt = '';
        $wsdlAuthorization = '';
        switch ($voucher->environment->code) {
            case 1:
                $wsdlReceipt = 'https://celcer.sri.gob.ec/comprobantes-electronicos-ws/RecepcionComprobantesOffline?wsdl';
                $wsdlAuthorization = 'https://celcer.sri.gob.ec/comprobantes-electronicos-ws/AutorizacionComprobantesOffline?wsdl';
                break;
            case 2:
                $wsdlReceipt = 'https://cel.sri.gob.ec/comprobantes-electronicos-ws/RecepcionComprobantesOffline?wsdl';
                $wsdlAuthorization = 'https://cel.sri.gob.ec/comprobantes-electronicos-ws/AutorizacionComprobantesOffline?wsdl';
                break;
        }
        $options = array(
            'connection_timeout' => 3,
        );
        $soapClientReceipt = new SoapClient($wsdlReceipt, $options);
        $xml['xml'] = file_get_contents(storage_path('app/' . $voucher->xml));
        try {
            $resultReceipt = json_decode(json_encode($soapClientReceipt->validarComprobante($xml)), True);
            info('**** RECEIPT RESULT *******************************************');
            info($resultReceipt);
            info('**** END RECEIPT RESULT ***************************************');
            switch ($resultReceipt['RespuestaRecepcionComprobante']['estado']) {
                case 'RECIBIDA':
                    $voucher->voucher_state_id = VoucherStates::RECEIVED;
                    $voucher->save();
                    self::moveXmlFile($voucher, VoucherStates::RECEIVED);
                    break;
                case 'DEVUELTA':
                    $voucher->voucher_state_id = VoucherStates::RETURNED;
                    $message = $resultReceipt['RespuestaRecepcionComprobante']['comprobantes']['comprobante']['mensajes']['mensaje']['tipo'] . ' ' .
                        $resultReceipt['RespuestaRecepcionComprobante']['comprobantes']['comprobante']['mensajes']['mensaje']['identificador'] . ': ' .
                        $resultReceipt['RespuestaRecepcionComprobante']['comprobantes']['comprobante']['mensajes']['mensaje']['mensaje'];
                    if (array_key_exists('informacionAdicional', $resultReceipt['RespuestaRecepcionComprobante']['comprobantes']['comprobante']['mensajes']['mensaje'])) {
                        $message .= '. ' . $resultReceipt['RespuestaRecepcionComprobante']['comprobantes']['comprobante']['mensajes']['mensaje']['informacionAdicional'];
                    }
                    $voucher->extra_detail = $message;
                    $voucher->save();
                    self::moveXmlFile($voucher, VoucherStates::RETURNED);
                    break;
            }
        } catch (\Exception $e) {
            info('#### ERROR IN VALIDARCOMPROBANTE WS #######################');
            info(' CODE: ' . $e->getCode());
            info(' FILE: ' . $e->getFile());
            info(' LINE: ' . $e->getLine());
            info(' MESSAGE: ' . $e->getMessage());
            info('#### END ERROR IN VALIDARCOMPROBANTE WS ###################');
        }

        if ($voucher->voucher_state_id === VoucherStates::RECEIVED) {
            $soapClientValidation = new SoapClient($wsdlAuthorization);
            $accessKey = array(
                'autorizacionComprobante' => array(
                    'claveAccesoComprobante' =>  $voucher->accessKey()
                )
            );
            try {
                $resultAuthorization = json_decode(json_encode($soapClientValidation->__soapCall("autorizacionComprobante", $accessKey)), True);
                info('**** AUTHORIZATION RESULT *********************************');
                info($resultAuthorization);
                info('**** END AUTHORIZATION RESULT *****************************');
                $xmlReponse = [
                    'estado' => $resultAuthorization['RespuestaAutorizacionComprobante']['autorizaciones']['autorizacion']['estado'],
                    'numeroAutorizacion' => NULL,
                    'fechaAutorizacion' => NULL,
                    'comprobante' => NULL,
                    'mensajes' => $resultAuthorization['RespuestaAutorizacionComprobante']['autorizaciones']['autorizacion']['mensajes'],
                ];

                switch ($resultAuthorization['RespuestaAutorizacionComprobante']['autorizaciones']['autorizacion']['estado']) {
                    case 'AUTORIZADO':
                        $xmlReponse['numeroAutorizacion'] = $resultAuthorization['RespuestaAutorizacionComprobante']['autorizaciones']['autorizacion']['numeroAutorizacion'];
                        $xmlReponse['fechaAutorizacion'] = array(
                            '_attributes' => ['class' => 'fechaAutorizacion'],
                            '_value' => $resultAuthorization['RespuestaAutorizacionComprobante']['autorizaciones']['autorizacion']['fechaAutorizacion'],
                        );
                        $xmlReponse['comprobante'] = array(
                            '_cdata' => $resultAuthorization['RespuestaAutorizacionComprobante']['autorizaciones']['autorizacion']['comprobante'],
                        );
                        $voucher->voucher_state_id = VoucherStates::AUTHORIZED;
                        $voucher->extra_detail = NULL;
                        $authorizationDate = DateTime::createFromFormat('Y-m-d\TH:i:sP', $resultAuthorization['RespuestaAutorizacionComprobante']['autorizaciones']['autorizacion']['fechaAutorizacion']);
                        $voucher->authorization_date = $authorizationDate->format('Y-m-d H:i:s');
                        break;
                    case 'NO AUTORIZADO':
                        unset($xmlReponse['numeroAutorizacion']);
                        $xmlReponse['fechaAutorizacion'] = array(
                            '_attributes' => ['class' => 'fechaAutorizacion'],
                            '_value' => $resultAuthorization['RespuestaAutorizacionComprobante']['autorizaciones']['autorizacion']['fechaAutorizacion'],
                        );
                        $xmlReponse['comprobante'] = array(
                            '_cdata' => $resultAuthorization['RespuestaAutorizacionComprobante']['autorizaciones']['autorizacion']['comprobante'],
                        );
                        $message = $resultAuthorization['RespuestaAutorizacionComprobante']['autorizaciones']['autorizacion']['mensajes']['mensaje']['tipo'] . ' ' .
                            $resultAuthorization['RespuestaAutorizacionComprobante']['autorizaciones']['autorizacion']['mensajes']['mensaje']['identificador'] . ': ' .
                            $resultAuthorization['RespuestaAutorizacionComprobante']['autorizaciones']['autorizacion']['mensajes']['mensaje']['mensaje'];
                        if (array_key_exists('informacionAdicional', $resultAuthorization['RespuestaAutorizacionComprobante']['autorizaciones']['autorizacion']['mensajes']['mensaje'])) {
                            $message .= '. ' . $resultAuthorization['RespuestaAutorizacionComprobante']['autorizaciones']['autorizacion']['mensajes']['mensaje']['informacionAdicional'];
                        }
                        $voucher->voucher_state_id = VoucherStates::UNAUTHORIZED;
                        $voucher->extra_detail = $message;
                        $authorizationDate = DateTime::createFromFormat('Y-m-d\TH:i:sP', $resultAuthorization['RespuestaAutorizacionComprobante']['autorizaciones']['autorizacion']['fechaAutorizacion']);
                        $voucher->authorization_date = $authorizationDate->format('Y-m-d H:i:s');
                        break;
                    default:
                        unset($xmlReponse['numeroAutorizacion']);
                        unset($xmlReponse['fechaAutorizacion']);
                        unset($xmlReponse['comprobante']);
                        $voucher->voucher_state_id = VoucherStates::IN_PROCESS;
                        $voucher->extra_detail = NULL;
                        break;
                }
                $voucher->save();
                $xmlPath = 'xmls/' .
                    $voucher->emissionPoint->branch->company->ruc . '/' .
                    VoucherState::find($voucher->voucher_state_id)->name . '/' .
                    DateTime::createFromFormat('Y-m-d', $voucher->issue_date)->format('Y/m') . '/' .
                    $voucher->accessKey() . '.xml';
                Storage::put($xmlPath, ArrayToXml::convert($xmlReponse, 'autorizacion', false, 'UTF-8'));
                Storage::delete($voucher->xml);
                $voucher->xml = $xmlPath;
                $voucher->save();
                if ($voucher->voucher_state_id === VoucherStates::AUTHORIZED && $voucher->environment->code === 2) {
                    MailController::sendMailNewVoucher($voucher);
                    $zipper = new Zipper;
                    $zipper->make(storage_path('app/') . 'vouchers.zip');
                    $zipper->add(storage_path('app/' . $voucher->xml));
                    $tempFolder = round((microtime(true) * 1000)) . '/';
                    Storage::makeDirectory($tempFolder);
                    $html = false;
                    PDF::loadView('vouchers.ride.' . $voucher->getViewType(), compact(['voucher', 'html']))->save(storage_path('app/' . $tempFolder) . $voucher->accessKey() . '.pdf');
                    $zipper->add(storage_path('app/' . $tempFolder));
                    $zipper->close();
                    if (File::exists(storage_path('app/' . $tempFolder))) {
                        File::deleteDirectory(storage_path('app/' . $tempFolder));
                    }
                }
            } catch (\Exception $e) {
                info('#### ERROR IN AUTORIZARCOMPROBANTE WS #######################');
                info(' CODE: ' . $e->getCode());
                info(' FILE: ' . $e->getFile());
                info(' LINE: ' . $e->getLine());
                info(' MESSAGE: ' . $e->getMessage());
                info('#### END ERROR IN AUTORIZARCOMPROBANTE WS ###################');
            }
        } elseif ($voucher->voucher_state_id === VoucherStates::RETURNED) {
            info('#### RETURNED VOUCHER #######################');
            info(' *** ' . $voucher->extra_detail . ' *** ');
            info('#### END RETURNED VOUCHER #######################');
        }
    }

    public function send(Voucher $voucher)
    {
        self::sendVoucher($voucher);
        return redirect()->route('vouchers.index')->with(['status' => 'Voucher sended successfully.']);
    }

    private static function cancelVoucher()
    {

    }

    public function html(Voucher $voucher)
    {
        $user = Auth::user();
        if (self::doesVoucherBelongToUser($voucher, $user)) {
            $html = true;
            return view('vouchers.ride.' . $voucher->getViewType(), compact(['voucher', 'html']));
        }
        return abort('404');
    }

    public function xml(Voucher $voucher)
    {
        $user = Auth::user();
        if (self::doesVoucherBelongToUser($voucher, $user)) {
            return Storage::download($voucher->xml);
        }
        return abort('404');
    }

    public function pdf(Voucher $voucher)
    {
        $user = Auth::user();
        if (self::doesVoucherBelongToUser($voucher, $user)) {
            $html = false;
            return PDF::loadView('vouchers.ride.' . $voucher->getViewType(), compact(['voucher', 'html']))->download($voucher->accessKey() . '.pdf');
        }
        return abort('404');
    }
}
