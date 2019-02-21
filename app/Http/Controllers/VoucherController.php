<?php

namespace ElectronicInvoicing\Http\Controllers;

use DateTime;
use DateTimeZone;
use ElectronicInvoicing\{
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
use ElectronicInvoicing\StaticClasses\VoucherStates;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
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

    private static function isValidRequest(Request $request)
    {
        $date = new DateTime('now', new DateTimeZone('America/Guayaquil'));
        $rules = [
            'company' => 'required|numeric|exists:companies,id',
            'branch' => 'required|numeric|exists:branches,id',
            'emission_point' => 'required|numeric|exists:emission_points,id',
            'customer' => 'required|numeric|exists:customers,id',
            'currency' => 'required|numeric|exists:currencies,id',
            'issue_date' => 'required|date|before_or_equal:' . $date->format('Y/m/d'),
            'environment' => 'required|numeric|exists:environments,id',
            'voucher_type' => 'required|numeric|exists:voucher_types,id'
        ];
        if ($request->voucher_type !== NULL) {
            switch ($request->voucher_type) {
                case 1:
                    $rules['product'] = 'required|array|min:1';
                    $rules['product.*'] = 'distinct|exists:products,id';
                    $rules['product_quantity'] = 'required|array|min:1';
                    $rules['product_quantity.*'] = 'required|numeric|gte:0';
                    $rules['product_unitprice'] = 'required|array|min:1';
                    $rules['product_unitprice.*'] = 'required|numeric|gte:0';
                    $rules['product_discount'] = 'required|array|min:1';
                    $rules['product_discount.*'] = 'required|numeric|gte:0';
                    $rules['paymentMethod'] = 'required|array|min:1';
                    $rules['paymentMethod.*'] = 'exists:payment_methods,id';
                    $rules['paymentMethod_value'] = 'required|array|min:1';
                    $rules['paymentMethod_value.*'] = 'required|numeric|gte:0';
                    $rules['paymentMethod_timeunit'] = 'required|array|min:1';
                    $rules['paymentMethod_timeunit.*'] = 'exists:time_units,id';
                    $rules['paymentMethod_term'] = 'required|array|min:1';
                    $rules['paymentMethod_term.*'] = 'required|numeric|gte:0';
                    $rules['additionaldetail_name'] = 'array|max:3';
                    $rules['additionaldetail_name.*'] = 'required|string|max:30';
                    $rules['additionaldetail_value'] = 'array|max:3';
                    $rules['additionaldetail_value.*'] = 'required|string|max:300';
                    $rules['waybill_establishment'] = 'required_with:waybill_emissionpoint,waybill_sequential|nullable|integer|min:1|max:999';
                    $rules['waybill_emissionpoint'] = 'required_with:waybill_establishment,waybill_sequential|nullable|integer|min:1|max:999';
                    $rules['waybill_sequential'] = 'required_with:waybill_establishment,waybill_emissionpoint|nullable|integer|min:1|max:999999999';
                    $rules['extra_detail'] = 'nullable|string';
                    $rules['ivaRetentionValue'] = 'nullable|numeric|min:0';
                    $rules['rentRetentionValue'] = 'nullable|numeric|min:0';
                    $rules['tip'] = 'required|numeric|min:0';
                    break;
                case 2:
                    $rules['product'] = 'required|array|min:1';
                    $rules['product.*'] = 'distinct|exists:products,id';
                    $rules['product_quantity'] = 'required|array|min:1';
                    $rules['product_quantity.*'] = 'required|numeric|gte:0';
                    $rules['product_unitprice'] = 'required|array|min:1';
                    $rules['product_unitprice.*'] = 'required|numeric|gte:0';
                    $rules['product_discount'] = 'required|array|min:1';
                    $rules['product_discount.*'] = 'required|numeric|gte:0';
                    $rules['additionaldetail_name'] = 'array|max:3';
                    $rules['additionaldetail_name.*'] = 'required|string|max:30';
                    $rules['additionaldetail_value'] = 'array|max:3';
                    $rules['additionaldetail_value.*'] = 'required|string|max:300';
                    $rules['supportdocument_establishment'] = 'required|integer|min:1|max:999';
                    $rules['supportdocument_emissionpoint'] = 'required|integer|min:1|max:999';
                    $rules['supportdocument_sequential'] = 'required|integer|min:1|max:999999999';
                    $rules['issue_date_support_document'] = 'required|date|before_or_equal:issue_date';
                    $rules['reason'] = 'required|string|max:300';
                    $rules['extra_detail'] = 'nullable|string';
                    break;
                case 3:
                    $rules['debit_reason'] = 'required|array|min:1';
                    $rules['debit_reason.*'] = 'required|string|max:300';
                    $rules['debit_value'] = 'required|array|min:1';
                    $rules['debit_value.*'] = 'required|numeric|gte:0';
                    $rules['paymentMethod'] = 'required|array|min:1';
                    $rules['paymentMethod.*'] = 'exists:payment_methods,id';
                    $rules['paymentMethod_value'] = 'required|array|min:1';
                    $rules['paymentMethod_value.*'] = 'required|numeric|gte:0';
                    $rules['paymentMethod_timeunit'] = 'required|array|min:1';
                    $rules['paymentMethod_timeunit.*'] = 'exists:time_units,id';
                    $rules['paymentMethod_term'] = 'required|array|min:1';
                    $rules['paymentMethod_term.*'] = 'required|numeric|gte:0';
                    $rules['supportdocument_establishment'] = 'required|integer|min:1|max:999';
                    $rules['supportdocument_emissionpoint'] = 'required|integer|min:1|max:999';
                    $rules['supportdocument_sequential'] = 'required|integer|min:1|max:999999999';
                    $rules['issue_date_support_document'] = 'required|date|before_or_equal:issue_date';
                    $rules['additionaldetail_name'] = 'array|max:3';
                    $rules['additionaldetail_name.*'] = 'required|string|max:30';
                    $rules['additionaldetail_value'] = 'array|max:3';
                    $rules['additionaldetail_value.*'] = 'required|string|max:300';
                    $rules['extra_detail'] = 'nullable|string';
                    $rules['iva_tax'] = 'required|exists:iva_taxes,id';
                    break;
                case 4:
                    $rules['product'] = 'required|array|min:1';
                    $rules['product.*'] = 'distinct|exists:products,id';
                    $rules['product_quantity'] = 'required|array|min:1';
                    $rules['product_quantity.*'] = 'required|numeric|gte:0';
                    $rules['identification_type'] = 'required|exists:identification_types,id';
                    $rules['carrier_ruc'] = 'required|max:20';
                    $rules['carrier_social_reason'] = 'required|max:300';
                    $rules['licence_plate'] = 'required|max:20';
                    $rules['starting_address'] = 'required|max:300';
                    $rules['start_date_transport'] = 'required|date|before_or_equal:end_date_transport';
                    $rules['end_date_transport'] = 'required|date|after_or_equal:start_date_transport';
                    $rules['additionaldetail_name'] = 'array|max:3';
                    $rules['additionaldetail_name.*'] = 'required|string|max:30';
                    $rules['additionaldetail_value'] = 'array|max:3';
                    $rules['additionaldetail_value.*'] = 'required|string|max:300';
                    $rules['extra_detail'] = 'nullable|string';
                    $rules['authorization_number'] = 'required|digits:49';
                    $rules['single_customs_doc'] = 'nullable|string|max:20';
                    $rules['address'] = 'required|string|max:300';
                    $rules['transfer_reason'] = 'required|string|max:300';
                    $rules['destination_establishment_code'] = 'required|min:1|max:999|integer';
                    $rules['route'] = 'required|string|max:300';
                    break;
                case 5:
                    $rules['tax'] = 'required|array|min:1';
                    $rules['tax.*'] = 'exists:retention_taxes,id';
                    $rules['description'] = 'required|array|min:1';
                    $rules['description.*'] = 'distinct|exists:retention_tax_descriptions,id';
                    $rules['value'] = 'required|array|min:1';
                    $rules['value.*'] = 'required|numeric|gte:0';
                    $rules['tax_base'] = 'required|array|min:1';
                    $rules['tax_base.*'] = 'required|numeric|gte:0';
                    $rules['additionaldetail_name'] = 'array|max:3';
                    $rules['additionaldetail_name.*'] = 'required|string|max:30';
                    $rules['additionaldetail_value'] = 'array|max:3';
                    $rules['additionaldetail_value.*'] = 'required|string|max:300';
                    $rules['extra_detail'] = 'nullable|string';
                    $rules['voucher_type_support_document'] = 'required|exists:voucher_types,id';
                    $rules['supportdocument_establishment'] = 'required|nullable|integer|min:1|max:999';
                    $rules['supportdocument_emissionpoint'] = 'required|nullable|integer|min:1|max:999';
                    $rules['supportdocument_sequential'] = 'required|nullable|integer|min:1|max:999999999';
                    $rules['issue_date_support_document'] = 'required|date|before_or_equal:' . $date->format('Y/m/d');
                    break;
            }
        }
        return Validator::make($request->all(), $rules, array());
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
        //MailController::sendMailNewVoucher(Voucher::find(13));
        //MailController::sendMailNewUser(User::find(25), str_random(8));
        $validator = self::isValidRequest($request);
        $isValid = !$validator->fails();
        if ($isValid) {
            if ($request->method() === 'PUT') {
                $this->update($request, $state, $voucher);
                $request->session()->flash('status', 'Voucher updated successfully.');
            } else {
                $this->store($request, $state);
                $request->session()->flash('status', 'Voucher added successfully.');
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
            $vouchers = Voucher::all();
        } elseif ($user->hasRole('owner')) {
            $branches = CompanyUser::getBranchesAllowedToUser($user, false);
            $emissionPoints = collect();
            foreach ($branches as $branch) {
                foreach ($branch->emissionPoints()->get() as $emissionPoint) {
                    $emissionPoints->push($emissionPoint);
                }
            }
            $vouchers = Voucher::whereIn('emission_point_id', $emissionPoints->pluck('id'))->get();
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
            $vouchers = Voucher::whereIn('emission_point_id', $emissionPoints->pluck('id'))->get();
        } elseif ($user->hasRole('employee')) {
            $vouchers = Voucher::where('user_id', $user->id)->get();
        } elseif ($user->hasRole('customer')) {
            $vouchers = Voucher::whereIn('customer_id', $user->customers->pluck('id'))->where('voucher_state_id', VoucherStates::AUTHORIZED)->where('environment_id', 2)->get();
        } else {
            $vouchers = collect();
        }
        if ($user->hasRole('admin')) {
            $companies = Company::all();
        } else {
            $companies = $user->hasRole('customer') ? Company::all() : CompanyUser::getCompaniesAllowedToUser($user);
        }
        return view('vouchers.index', compact(['companies', 'vouchers']));
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
        $voucherTypes = VoucherType::all();
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
        return true;//redirect()->route('home')->with(['status' => 'Voucher added successfully.']);
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
        $action = 'edit';
        $companies = $user->hasRole('admin') ? Company::all() : CompanyUser::getCompaniesAllowedToUser($user);
        $currencies = Currency::all();
        $environments = Environment::all();
        $identificationTypes = IdentificationType::all();
        $voucherTypes = VoucherType::all();
        return view('vouchers.edit', compact(['action', 'companies', 'currencies', 'voucher', 'environments', 'identificationTypes', 'voucherTypes']));
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
        $voucherTypes = VoucherType::all();
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
                $validator = self::isValidRequest($request);
                $isValid = !$validator->fails();
                if ($isValid) {
                    DraftJson::getInstance()->deleteDraftVoucher($user, $voucherId);
                    self::saveVoucher($request, VoucherStates::SAVED);
                    $request->session()->flash('status', 'Draft voucher saved successfully.');
                }
                $messages = $validator->messages()->messages();
                break;
            case VoucherStates::ACCEPTED:
                $validator = self::isValidRequest($request);
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
                $validator = self::isValidRequest($request);
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

    public static function getVouchers(User $user)
    {
        if ($user->hasRole('admin')) {
            $vouchers = Voucher::all();
        } elseif ($user->hasRole('owner')) {
            $branches = CompanyUser::getBranchesAllowedToUser($user, false);
            $emissionPoints = collect();
            foreach ($branches as $branch) {
                foreach ($branch->emissionPoints()->get() as $emissionPoint) {
                    $emissionPoints->push($emissionPoint);
                }
            }
            $vouchers = Voucher::whereIn('emission_point_id', $emissionPoints->pluck('id'))->get();
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
            $vouchers = Voucher::whereIn('emission_point_id', $emissionPoints->pluck('id'))->get();
        } elseif ($user->hasRole('employee')) {
            $vouchers = Voucher::where('user_id', $user->id)->get();
        } elseif ($user->hasRole('customer')) {
            $vouchers = Voucher::whereIn('customer_id', $user->customers->pluck('id'))->where('voucher_state_id', VoucherStates::AUTHORIZED)->where('environment_id', 2)->get();
        } else {
            $vouchers = collect();
        }
        return $vouchers->splice(-5);
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
        if ($action === 'create') {
            switch ($id) {
                case 1:
                    $companiesproduct = $user->hasRole('admin') ? Company::all() : CompanyUser::getCompaniesAllowedToUser($user);
                    $iva_taxes = IvaTax::all()->sortBy(['auxiliary_code']);
                    $ice_taxes = IceTax::all()->sortBy(['auxiliary_code']);
                    $irbpnr_taxes = IrbpnrTax::all()->sortBy(['auxiliary_code']);
                    return view('vouchers.' . $id, compact(['action', 'companiesproduct', 'iva_taxes', 'ice_taxes', 'irbpnr_taxes']));
                    break;
                case 2:
                    $companiesproduct = $user->hasRole('admin') ? Company::all() : CompanyUser::getCompaniesAllowedToUser($user);
                    $iva_taxes = IvaTax::all()->sortBy(['auxiliary_code']);
                    $ice_taxes = IceTax::all()->sortBy(['auxiliary_code']);
                    $irbpnr_taxes = IrbpnrTax::all()->sortBy(['auxiliary_code']);
                    return view('vouchers.' . $id, compact(['action', 'companiesproduct', 'iva_taxes', 'ice_taxes', 'irbpnr_taxes']));
                    break;
                case 3:
                    $iva_taxes = IvaTax::all()->sortBy(['auxiliary_code']);
                    return view('vouchers.' . $id, compact(['action', 'iva_taxes']));
                    break;
                case 4:
                    $identificationTypes = IdentificationType::where('code', '!=', 7)->get();
                    $companiesproduct = $user->hasRole('admin') ? Company::all() : CompanyUser::getCompaniesAllowedToUser($user);
                    $iva_taxes = IvaTax::all()->sortBy(['auxiliary_code']);
                    $ice_taxes = IceTax::all()->sortBy(['auxiliary_code']);
                    $irbpnr_taxes = IrbpnrTax::all()->sortBy(['auxiliary_code']);
                    return view('vouchers.' . $id, compact(['action', 'companiesproduct', 'iva_taxes', 'ice_taxes', 'irbpnr_taxes', 'identificationTypes']));
                    break;
                case 5:
                    $voucherTypes = VoucherType::all();
                    return view('vouchers.' . $id, compact(['action', 'voucherTypes']));
                    break;
            }
        } else {
            switch ($id) {
                case 1:
                    $companiesproduct = $user->hasRole('admin') ? Company::all() : CompanyUser::getCompaniesAllowedToUser($user);
                    $iva_taxes = IvaTax::all()->sortBy(['auxiliary_code']);
                    $ice_taxes = IceTax::all()->sortBy(['auxiliary_code']);
                    $irbpnr_taxes = IrbpnrTax::all()->sortBy(['auxiliary_code']);
                    $voucher = Voucher::find($voucherId);
                    return view('vouchers.' . $id, compact(['action', 'companiesproduct', 'voucher', 'iva_taxes', 'ice_taxes', 'irbpnr_taxes']));
                    break;
                case 2:
                    $companiesproduct = $user->hasRole('admin') ? Company::all() : CompanyUser::getCompaniesAllowedToUser($user);
                    $iva_taxes = IvaTax::all()->sortBy(['auxiliary_code']);
                    $ice_taxes = IceTax::all()->sortBy(['auxiliary_code']);
                    $irbpnr_taxes = IrbpnrTax::all()->sortBy(['auxiliary_code']);
                    $voucher = Voucher::find($voucherId);
                    return view('vouchers.' . $id, compact(['action', 'companiesproduct', 'voucher', 'iva_taxes', 'ice_taxes', 'irbpnr_taxes']));
                    break;
                case 3:
                    $iva_taxes = IvaTax::all()->sortBy(['auxiliary_code']);
                    $voucher = Voucher::find($voucherId);
                    return view('vouchers.' . $id, compact(['action', 'iva_taxes', 'voucher']));
                    break;
                case 4:
                    $identificationTypes = IdentificationType::where('code', '!=', 7)->get();
                    $companiesproduct = $user->hasRole('admin') ? Company::all() : CompanyUser::getCompaniesAllowedToUser($user);
                    $iva_taxes = IvaTax::all()->sortBy(['auxiliary_code']);
                    $ice_taxes = IceTax::all()->sortBy(['auxiliary_code']);
                    $irbpnr_taxes = IrbpnrTax::all()->sortBy(['auxiliary_code']);
                    $voucher = Voucher::find($voucherId);
                    return view('vouchers.' . $id, compact(['action', 'companiesproduct', 'iva_taxes', 'ice_taxes', 'irbpnr_taxes', 'identificationTypes', 'voucher']));
                    break;
                case 5:
                    $voucherTypes = VoucherType::all();
                    $voucher = Voucher::find($voucherId);
                    return view('vouchers.' . $id, compact(['action', 'voucherTypes', 'voucher']));
                    break;
            }
        }
        return view('vouchers.' . $id);
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
                return view('vouchers.' . $id, compact(['action', 'companiesproduct', 'draftVoucher', 'iva_taxes', 'ice_taxes', 'irbpnr_taxes']));
                break;
            case 2:
                $companiesproduct = $user->hasRole('admin') ? Company::all() : CompanyUser::getCompaniesAllowedToUser($user);
                $iva_taxes = IvaTax::all()->sortBy(['auxiliary_code']);
                $ice_taxes = IceTax::all()->sortBy(['auxiliary_code']);
                $irbpnr_taxes = IrbpnrTax::all()->sortBy(['auxiliary_code']);
                return view('vouchers.' . $id, compact(['action', 'companiesproduct', 'draftVoucher', 'iva_taxes', 'ice_taxes', 'irbpnr_taxes']));
                break;
            case 3:
                $iva_taxes = IvaTax::all()->sortBy(['auxiliary_code']);
                return view('vouchers.' . $id, compact(['action', 'iva_taxes']));
                break;
            case 4:
                $identificationTypes = IdentificationType::where('code', '!=', 7)->get();
                $companiesproduct = $user->hasRole('admin') ? Company::all() : CompanyUser::getCompaniesAllowedToUser($user);
                $iva_taxes = IvaTax::all()->sortBy(['auxiliary_code']);
                $ice_taxes = IceTax::all()->sortBy(['auxiliary_code']);
                $irbpnr_taxes = IrbpnrTax::all()->sortBy(['auxiliary_code']);
                return view('vouchers.' . $id, compact(['action', 'companiesproduct', 'iva_taxes', 'ice_taxes', 'irbpnr_taxes', 'identificationTypes']));
                break;
            case 5:
                $voucherTypes = VoucherType::all();
                return view('vouchers.' . $id, compact(['action', 'draftVoucher', 'voucherTypes']));
                break;
        }
    }

    private static function generateRandomNumericCode()
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

    private static function saveVoucher($request, $state, $isUpdate = false, $id = null)
    {
        $company = Company::find($request->company);
        $branch = Branch::find($request->branch);
        $emissionPoint = EmissionPoint::find($request->emission_point);
        $customer = Customer::find($request->customer);
        $currency = Currency::find($request->currency);
        $environment = Environment::find($request->environment);
        $voucherType = VoucherType::find($request->voucher_type);
        $issueDate = DateTime::createFromFormat('Y/m/d', $request->issue_date);
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
        }

        $voucher->emission_point_id = $emissionPoint->id;
        $voucher->voucher_type_id = $voucherType->id;
        $voucher->environment_id = $environment->id;
        $voucher->numeric_code = self::generateRandomNumericCode();
        $voucher->customer_id = $customer->id;
        $voucher->issue_date = $issueDate->format('Y-m-d');
        $voucher->currency_id = $currency->id;
        $voucher->tip = NULL;
        $voucher->extra_detail = $request->extra_detail;
        $voucher->user_id = Auth::user()->id;



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
                $quantities = $request->product_quantity;
                $unitPrices = $request->product_unitprice;
                $discounts = $request->product_discount;
                foreach (Detail::where('voucher_id', '=', $voucher->id)->get() as $detail) {
                    foreach ($detail->taxDetails as $taxDetail) {
                        $taxDetail->delete();
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
                $issueDateSupportDocument = DateTime::createFromFormat('Y/m/d', $request->issue_date_support_document);
                $voucher->support_document = $issueDateSupportDocument->format('dmY') . '01' .
                    str_pad($request->supportdocument_establishment, 3, '0', STR_PAD_LEFT) .
                    str_pad($request->supportdocument_emissionpoint, 3, '0', STR_PAD_LEFT) .
                    str_pad($request->supportdocument_sequential, 9, '0', STR_PAD_LEFT);
                $voucher->support_document_date = $issueDateSupportDocument->format('Y-m-d');
                $voucher->save();
                $products = $request->product;
                $quantities = $request->product_quantity;
                $unitPrices = $request->product_unitprice;
                $discounts = $request->product_discount;
                foreach (Detail::where('voucher_id', '=', $voucher->id)->get() as $detail) {
                    foreach ($detail->taxDetails as $taxDetail) {
                        $taxDetail->delete();
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
                }
                $creditNote = new CreditNote;
                $creditNote->voucher_id = $voucher->id;
                $creditNote->reason = $request->reason;
                $creditNote->save();
                break;
            case 3:
                $issueDateSupportDocument = DateTime::createFromFormat('Y/m/d', $request->issue_date_support_document);
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
                $quantities = $request->product_quantity;
                for ($i = 0; $i < count($products); $i++) {
                    $detailAddressee = new DetailAddressee;
                    $detailAddressee->addressee_id = $addressee->id;
                    $detailAddressee->product_id = Product::find($products[$i])->id;
                    $detailAddressee->quantity = $quantities[$i];
                    $detailAddressee->save();
                }
                break;
            case 5:
                $voucher->support_document = DateTime::createFromFormat('Y/m/d', $request->issue_date_support_document)->format('dmY') .
                    str_pad(strval(VoucherType::find($request->voucher_type_support_document)->code), 2, '0', STR_PAD_LEFT) .
                    str_pad(strval($request->supportdocument_establishment), 3, '0', STR_PAD_LEFT) .
                    str_pad(strval($request->supportdocument_emissionpoint), 3, '0', STR_PAD_LEFT) .
                    str_pad(strval($request->supportdocument_sequential), 9, '0', STR_PAD_LEFT);
                $voucher->support_document_date = DateTime::createFromFormat('Y/m/d', $request->issue_date_support_document)->format('Y-m-d');
                $voucher->save();
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
                    $retentionDetail->support_doc_code = DateTime::createFromFormat('Y/m/d', $request->issue_date_support_document)->format('dmY') .
                        str_pad(strval(VoucherType::find($request->voucher_type_support_document)->code), 2, '0', STR_PAD_LEFT) .
                        str_pad(strval($request->supportdocument_establishment), 3, '0', STR_PAD_LEFT) .
                        str_pad(strval($request->supportdocument_emissionpoint), 3, '0', STR_PAD_LEFT) .
                        str_pad(strval($request->supportdocument_sequential), 9, '0', STR_PAD_LEFT);
                    $retentionDetail->save();
                }
                break;
        }
        foreach ($voucher->additionalFields() as $additionalField) {
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

    private static function acceptVoucher($voucher)
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
        $version = '1.0.0';
        foreach (Detail::where('voucher_id', '=', $voucher->id)->get() as $detail) {
            if (strlen(substr(strrchr(strval(floatval($detail->quantity)), "."), 1)) > 2 || strlen(substr(strrchr(strval(floatval($detail->unit_price)), "."), 1)) > 2) {
                $version = '1.1.0';
                break;
            }
        }
        $waybill = Waybill::where('voucher_id', '=', $voucher->id)->first();
        if ($waybill !== NULL) {
            $addressee = Addressee::where('waybill_id', '=', $waybill->id)->first();
            foreach (DetailAddressee::where('addressee_id', '=', $addressee->id)->get() as $detailAddressee) {
                if (strlen(substr(strrchr(strval(floatval($detailAddressee->quantity)), "."), 1)) > 2) {
                    $version = '1.1.0';
                    break;
                }
            }
        }
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
                    'valRetIva'                     => NULL,
                    'valRetRenta'                   => NULL,
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
                    unset($xml['infoFactura']['valRetIva']);
                } else {
                    $xml['infoFactura']['valRetIva'] = number_format($voucher->iva_retention, 2, '.', '');
                }
                if ($voucher->rent_retention === NULL) {
                    unset($xml['infoFactura']['valRetRenta']);
                } else {
                    $xml['infoFactura']['valRetRenta'] = number_format($voucher->rent_retention, 2, '.', '');
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
                    array_push($voucherDetails,
                        array(
                            'codigoPrincipal'           => $detail->product->main_code,
                            'codigoAuxiliar'            => $detail->product->auxiliary_code,
                            'descripcion'               => $detail->product->description,
                            'cantidad'                  => $version === '1.0.0' ? number_format($detail->quantity, 2, '.', '') : $detail->quantity,
                            'precioUnitario'            => $version === '1.0.0' ? number_format($detail->unit_price, 2, '.', '') : $detail->unit_price,
                            'descuento'                 => number_format($detail->discount, 2, '.', ''),
                            'precioTotalSinImpuesto'    => number_format($detail->quantity * $detail->unit_price - $detail->discount, 2, '.', ''),
                            'impuestos'                 => [
                                'impuesto' => $detailTaxes,
                            ]
                        )
                    );
                }
                $xml['detalles'] = [
                    'detalle' => $voucherDetails,
                ];
                break;
            case 2:
                $root = 'notaCredito';
                $issueDate = DateTime::createFromFormat('Y-m-d', $voucher->issue_date);
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
                    'fechaEmisionDocSustento'       => $issueDate->format('d/m/Y'),
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
                    array_push($voucherDetails,
                        array(
                            'codigoInterno'             => $detail->product->main_code,
                            'codigoAdicional'           => $detail->product->auxiliary_code,
                            'descripcion'               => $detail->product->description,
                            'cantidad'                  => $version === '1.0.0' ? number_format($detail->quantity, 2, '.', '') : $detail->quantity,
                            'precioUnitario'            => $version === '1.0.0' ? number_format($detail->unit_price, 2, '.', '') : $detail->unit_price,
                            'descuento'                 => number_format($detail->discount, 2, '.', ''),
                            'precioTotalSinImpuesto'    => number_format($detail->quantity * $detail->unit_price - $detail->discount, 2, '.', ''),
                            'impuestos'                 => [
                                'impuesto' => $detailTaxes,
                            ]
                        )
                    );
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
                                'codEstabDestino'               => str_pad(strval($addressee->destination_establishment_code), 3, '0', STR_PAD_LEFT),
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
                            'porcentajeRetener'         => number_format($detail->value, 2, '.', ''),
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

    private static function sendVoucher($voucher)
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
        $wsdlValidation = '';
        switch ($voucher->environment->code) {
            case 1:
                $wsdlReceipt = 'https://celcer.sri.gob.ec/comprobantes-electronicos-ws/RecepcionComprobantesOffline?wsdl';
                $wsdlValidation = 'https://celcer.sri.gob.ec/comprobantes-electronicos-ws/AutorizacionComprobantesOffline?wsdl';
                break;
            case 2:
                $wsdlReceipt = 'https://cel.sri.gob.ec/comprobantes-electronicos-ws/RecepcionComprobantesOffline?wsdl';
                $wsdlValidation = 'https://cel.sri.gob.ec/comprobantes-electronicos-ws/AutorizacionComprobantesOffline?wsdl';
                break;
        }
        $options = array(
            'connection_timeout' => 3,
        );
        $soapClientReceipt = new SoapClient($wsdlReceipt, $options);
        $xml['xml'] = file_get_contents(storage_path('app/' . $voucher->xml));
        $resultReceipt = json_decode(json_encode($soapClientReceipt->validarComprobante($xml)), True);
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

        if ($voucher->voucher_state_id === VoucherStates::RECEIVED) {
            $soapClientValidation = new SoapClient($wsdlValidation);
            $accessKey = array(
                'autorizacionComprobante' => array(
                    'claveAccesoComprobante' =>  $voucher->accessKey()
                )
            );
            $resultValidation = json_decode(json_encode($soapClientValidation->__soapCall("autorizacionComprobante", $accessKey)), True);
            $xmlReponse = [
                'estado' => $resultValidation['RespuestaAutorizacionComprobante']['autorizaciones']['autorizacion']['estado'],
                'numeroAutorizacion' => NULL,
                'fechaAutorizacion' => array(
                    '_attributes' => ['class' => 'fechaAutorizacion'],
                    '_value' => $resultValidation['RespuestaAutorizacionComprobante']['autorizaciones']['autorizacion']['fechaAutorizacion'],
                ),
                'comprobante' => array(
                    '_cdata' => $resultValidation['RespuestaAutorizacionComprobante']['autorizaciones']['autorizacion']['comprobante'],
                ),
                'mensajes' => $resultValidation['RespuestaAutorizacionComprobante']['autorizaciones']['autorizacion']['mensajes'],
            ];

            switch ($resultValidation['RespuestaAutorizacionComprobante']['autorizaciones']['autorizacion']['estado']) {
                case 'AUTORIZADO':
                    $xmlReponse['numeroAutorizacion'] = $resultValidation['RespuestaAutorizacionComprobante']['autorizaciones']['autorizacion']['numeroAutorizacion'];
                    $voucher->voucher_state_id = VoucherStates::AUTHORIZED;
                    break;
                case 'NO AUTORIZADO':
                    unset($xmlReponse['numeroAutorizacion']);
                    $voucher->voucher_state_id = VoucherStates::UNAUTHORIZED;
                    break;
                default:
                    $voucher->voucher_state_id = VoucherStates::IN_PROCESS;
                    break;
            }
            $authorizationDate = DateTime::createFromFormat('Y-m-d\TH:i:sP', $resultValidation['RespuestaAutorizacionComprobante']['autorizaciones']['autorizacion']['fechaAutorizacion']);
            $voucher->authorization_date = $authorizationDate->format('Y-m-d H:i:s');
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
            if ($voucher->voucher_state_id === VoucherStates::AUTHORIZED) {
                MailController::sendMailNewVoucher($voucher);
            }
        } elseif ($voucher->voucher_state_id === VoucherStates::RETURNED) {
            info(' *** ' . $voucher->extra_detail . ' *** ');
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
        $html = true;
        switch ($voucher->voucher_type_id) {
            case 1: $voucherType = 'invoice'; break;
            case 2: $voucherType = 'credit_note'; break;
            case 3: $voucherType = 'debit_note'; break;
            case 4: $voucherType = 'waybill'; break;
            case 5: $voucherType = 'retention'; break;
        }
        return view('vouchers.ride.' . $voucherType, compact(['voucher', 'html']));
    }

    public function xml(Voucher $voucher)
    {
        return Storage::download($voucher->xml);
    }

    public function pdf(Voucher $voucher)
    {
        $html = false;
        switch ($voucher->voucher_type_id) {
            case 1: $voucherType = 'invoice'; break;
            case 2: $voucherType = 'credit_note'; break;
            case 3: $voucherType = 'debit_note'; break;
            case 4: $voucherType = 'waybill'; break;
            case 5: $voucherType = 'retention'; break;
        }
        return PDF::loadView('vouchers.ride.' . $voucherType, compact(['voucher', 'html']))->download($voucher->accessKey() . '.pdf');
    }
}
