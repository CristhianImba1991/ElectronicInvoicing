<?php

namespace ElectronicInvoicing\Http\Controllers;

use Carbon\Carbon;
use DateTime;
use ElectronicInvoicing\{
    Branch,
    Company,
    Customer,
    EmissionPoint,
    Environment,
    IdentificationType,
    IvaTax,
    PaymentMethod,
    Product,
    RetentionTax,
    RetentionTaxDescription,
    Voucher,
    VoucherState,
    VoucherType,
};
use ElectronicInvoicing\StaticClasses\{ValidationRule, VoucherStates};
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\File;
use PDF;
use Validator;

class ApiController extends Controller
{
    public function login(Request $request)
    {
        $validator = Validator::make($request->only('email', 'password'), [
            'email' => 'required|string|email',
            'password' => 'required|string'
        ]);
        $isValid = !$validator->fails();
        if (!$isValid) {
            return response()->json([
                'code' => 422,
                'message' => 'The request was well-formed but was unable to be followed due to semantic errors.',
                'errors' => [
                    'error' => 'Unprocessable Entity',
                    'info' => $validator->messages()->messages()
                ]
            ], 422);
        }

        $credentials = request(['email', 'password']);
        $attemptLogin = Auth::attempt($credentials);
        $user = $request->user();
        if (!$attemptLogin || !$user->hasRole('api')) {
            return response()->json([
                'code' => 401,
                'message' => 'Authentication is required but it has failed or has not yet been provided. The user does not have the necessary credentials.',
                'errors' => [
                    'error' => 'Unauthorized',
                    'info' => 'These credentials do not match our records.'
                ]
            ], 401);
        }

        $personalAccessToken = $user->createToken('Personal Access Token');
        $accessToken = $personalAccessToken->token;
        $accessToken->expires_at = Carbon::now()->addWeeks(1);
        $accessToken->save();

        return response()->json([
            'code' => 200,
            'message' => 'Successful login.',
            'token' => [
                'token_type' => 'Bearer',
                'expire_date' => Carbon::parse($personalAccessToken->token->expires_at)->toDateTimeString(),
                'access_token' => $personalAccessToken->accessToken
            ]
        ], 200);
    }

    public function logout(Request $request)
    {
        $request->user()->token()->revoke();
        return response()->json([
            'code' => 200,
            'message' => 'Successful logout.'
        ], 200);
    }

    public function sendVoucher(Request $request)
    {
        $validator = VoucherController::isValidRequest($request, VoucherStates::SENDED);
        $isValid = !$validator->fails();
        if ($isValid) {
            self::changeToIdsVoucher($request);
            $voucher = VoucherController::saveVoucher($request, VoucherStates::SENDED);
            VoucherController::acceptVoucher($voucher);
            VoucherController::sendVoucher($voucher);
            return response()->json([
                'code' => 200,
                'message' => trans_choice(__('message.model_added_successfully', ['model' => trans_choice(__('view.voucher'), 0)]), 0),
                'voucher' => [
                    'access_key' => $voucher->accessKey(),
                    'state' => __(VoucherState::find($voucher->voucher_state_id)->name),
                    'authorization_date' => $voucher->authorization_date,
                    'extra_detail' => $voucher->extra_detail,
                ]
            ], 200);
        } else {
            return response()->json([
                'code' => 422,
                'message' => 'The request was well-formed but was unable to be followed due to semantic errors.',
                'errors' => [
                    'error' => 'Unprocessable Entity',
                    'info' => $validator->messages()->messages()
                ]
            ], 422);
        }
    }

    public function createProduct(Request $request)
    {
        $validator = Validator::make($request->all(), ValidationRule::makeRule('product', $request));
        $isValid = !$validator->fails();
        if ($isValid) {
            self::changeToIdsProduct($request);
            (new ProductController)->store($request);
            return response()->json([
                'code' => 200,
                'message' => trans_choice(__('message.model_added_successfully', ['model' => trans_choice(__('view.product'), 0)]), 0)
            ], 200);
        } else {
            return response()->json([
                'code' => 422,
                'message' => 'The request was well-formed but was unable to be followed due to semantic errors.',
                'errors' => [
                    'error' => 'Unprocessable Entity',
                    'info' => $validator->messages()->messages()
                ]
            ], 422);
        }
    }

    public function createCustomer(Request $request)
    {
        $validator = Validator::make($request->all(), ValidationRule::makeRule('customer', $request));
        $isValid = !$validator->fails();
        if ($isValid) {
            self::changeToIdsCustomer($request);
            (new CustomerController)->store($request);
            return response()->json([
                'code' => 200,
                'message' => trans_choice(__('message.model_added_successfully', ['model' => trans_choice(__('view.customer'), 0)]), 0)
            ], 200);
        } else {
            return response()->json([
                'code' => 422,
                'message' => 'The request was well-formed but was unable to be followed due to semantic errors.',
                'errors' => [
                    'error' => 'Unprocessable Entity',
                    'info' => $validator->messages()->messages()
                ]
            ], 422);
        }
    }

    private static function validateRequest(Request $request)
    {
        return Validator::make($request->only('access_key'), [
            'access_key' => [
                'required',
                'digits:49',
                'validaccesskey',
                function ($attribute, $value, $fail) {
                    if (strlen($value) === 49) {
                        $failMessages = [];
                        $validAccessKeyDate = Validator::make(['access_key_date' => substr($value, 0, 8)], [
                            'access_key_date' => 'date_format:dmY'
                        ]);
                        if ($validAccessKeyDate->fails()) {
                            array_push($failMessages, $validAccessKeyDate->messages()->messages());
                        }
                        $validAccessKeyVoucherType = Validator::make(['access_key_voucher_type' => substr($value, 8, 2)], [
                            'access_key_voucher_type' => 'exists:voucher_types,code'
                        ]);
                        if ($validAccessKeyVoucherType->fails()) {
                            array_push($failMessages, $validAccessKeyVoucherType->messages()->messages());
                        }
                        $validAccessKeyVoucherCompany = Validator::make(['access_key_company' => substr($value, 10, 13)], [
                            'access_key_company' => 'validruc|exists:companies,ruc'
                        ]);
                        if ($validAccessKeyVoucherCompany->fails()) {
                            array_push($failMessages, $validAccessKeyVoucherCompany->messages()->messages());
                        }
                        $validAccessKeyVoucherEnvironment = Validator::make(['access_key_environment' => substr($value, 23, 1)], [
                            'access_key_environment' => 'exists:environments,code'
                        ]);
                        if ($validAccessKeyVoucherEnvironment->fails()) {
                            array_push($failMessages, $validAccessKeyVoucherEnvironment->messages()->messages());
                        }
                        $validAccessKeyVoucherBranch = Validator::make(['access_key_branch' => (integer) substr($value, 24, 3)], [
                            'access_key_branch' => 'min:1|max:999|integer|exists:branches,establishment'
                        ]);
                        if ($validAccessKeyVoucherBranch->fails()) {
                            array_push($failMessages, $validAccessKeyVoucherBranch->messages()->messages());
                        }
                        $validAccessKeyVoucherEmissionPoint = Validator::make(['access_key_emission_point' => (integer) substr($value, 27, 3)], [
                            'access_key_emission_point' => 'min:1|max:999|integer|exists:emission_points,code'
                        ]);
                        if ($validAccessKeyVoucherEmissionPoint->fails()) {
                            array_push($failMessages, $validAccessKeyVoucherEmissionPoint->messages()->messages());
                        }
                        $validAccessKeyVoucherSequential = Validator::make(['access_key_sequential' => (integer) substr($value, 30, 9)], [
                            'access_key_sequential' => 'min:1|max:999999999|integer'
                        ]);
                        if ($validAccessKeyVoucherSequential->fails()) {
                            array_push($failMessages, $validAccessKeyVoucherSequential->messages()->messages());
                        }
                        $validAccessKeyVoucherNumericCode = Validator::make(['access_key_numeric_code' => substr($value, 39, 8)], [
                            'access_key_numeric_code' => 'min:0|max:99999999|integer'
                        ]);
                        if ($validAccessKeyVoucherNumericCode->fails()) {
                            array_push($failMessages, $validAccessKeyVoucherNumericCode->messages()->messages());
                        }
                        $validAccessKeyVoucherEmissionType = Validator::make(['access_key_emission_type' => substr($value, 47, 1)], [
                            'access_key_emission_type' => 'min:1|max:1|integer'
                        ]);
                        if ($validAccessKeyVoucherEmissionType->fails()) {
                            array_push($failMessages, $validAccessKeyVoucherEmissionType->messages()->messages());
                        }
                        $validAccessKeyVoucherCheckDigit = Validator::make(['access_key_check_digit' => substr($value, 48, 1)], [
                            'access_key_check_digit' => 'min:0|max:9|integer'
                        ]);
                        if ($validAccessKeyVoucherCheckDigit->fails()) {
                            array_push($failMessages, $validAccessKeyVoucherCheckDigit->messages()->messages());
                        }
                        if (count($failMessages) > 0) {
                            $fail($failMessages);
                        }
                    }
                }
            ]
        ]);
    }

    private static function getVoucherQuery(Request $request)
    {
         return Voucher::join('emission_points', 'emission_points.id', '=', 'vouchers.emission_point_id')
            ->join('branches', 'branches.id', '=', 'emission_points.branch_id')
            ->join('companies', 'companies.id', '=', 'branches.company_id')
            ->join('environments', 'environments.id', '=', 'vouchers.environment_id')
            ->join('voucher_types', 'voucher_types.id', '=', 'vouchers.voucher_type_id')
            ->select('vouchers.*')
            ->where('vouchers.issue_date', DateTime::createFromFormat('dmY', substr($request->access_key, 0, 8))->format('Y-m-d'))
            ->where('voucher_types.code', substr($request->access_key, 8, 2))
            ->where('companies.ruc', substr($request->access_key, 10, 13))
            ->where('environments.code', substr($request->access_key, 23, 1))
            ->where('branches.establishment', substr($request->access_key, 24, 3))
            ->where('emission_points.code', substr($request->access_key, 27, 3))
            ->where('vouchers.sequential', substr($request->access_key, 30, 9))
            ->where('vouchers.numeric_code', substr($request->access_key, 39, 8));
    }

    public function getPdf(Request $request)
    {
        $validator = self::validateRequest($request);
        $isValid = !$validator->fails();
        if ($isValid) {
            $query = self::getVoucherQuery($request);;
            if ($query->exists()) {
                $voucher = $query->first();
                $html = false;
                PDF::loadView('vouchers.ride.' . $voucher->getViewType(), compact(['voucher', 'html']))->save($voucher->accessKey() . '.pdf');
                return response()->download(public_path($voucher->accessKey() . '.pdf'), $voucher->accessKey() . '.pdf', [
                        'Content-Type' => 'application/pdf',
                        'Cache-Control' => 'no-cache, private'
                    ])->deleteFileAfterSend();
            } else {
                return response()->json([
                    'code' => 404,
                    'message' => 'The requested resource could not be found.',
                    'errors' => [
                        'error' => 'Not Found',
                        'info' => 'The requested voucher could not be found.'
                    ]
                ], 404);
            }

        }
        return response()->json([
            'code' => 422,
            'message' => 'The request was well-formed but was unable to be followed due to semantic errors.',
            'errors' => [
                'error' => 'Unprocessable Entity',
                'info' => $validator->messages()->messages()
            ]
        ], 422);
    }

    public function getXml(Request $request)
    {
        $validator = self::validateRequest($request);
        $isValid = !$validator->fails();
        if ($isValid) {
            $query = self::getVoucherQuery($request);;
            if ($query->exists()) {
                $voucher = $query->first();
                $html = false;info(storage_path('app/' . $voucher->xml));
                return response()->download(storage_path('app/' . $voucher->xml), basename($voucher->xml), [
                        'Content-Type' => 'application/xml',
                        'Cache-Control' => 'no-cache, private'
                    ]);
            } else {
                return response()->json([
                    'code' => 404,
                    'message' => 'The requested resource could not be found.',
                    'errors' => [
                        'error' => 'Not Found',
                        'info' => 'The requested voucher could not be found.'
                    ]
                ], 404);
            }

        }
        return response()->json([
            'code' => 422,
            'message' => 'The request was well-formed but was unable to be followed due to semantic errors.',
            'errors' => [
                'error' => 'Unprocessable Entity',
                'info' => $validator->messages()->messages()
            ]
        ], 422);
    }

    private static function changeToIdsVoucher(Request $request)
    {
        $request->company = Company::where('ruc', '=', $request->company)->first()->id;
        $request->branch = Branch::where([['company_id', '=', $request->company], ['establishment', '=', $request->branch]])->first()->id;
        $request->emission_point = EmissionPoint::where([['branch_id', '=', $request->branch], ['code', '=', $request->emission_point]])->first()->id;
        $request->customer = Customer::join('company_customers', 'customers.id', '=', 'company_customers.customer_id')->where([['customers.identification', '=', $request->customer], ['company_customers.company_id', '=', $request->company]])->first()->id;
        $request->environment = Environment::where('code', '=', $request->environment)->first()->id;
        $request->voucher_type = VoucherType::where('code', '=', $request->voucher_type)->first()->id;
        switch ($request->voucher_type) {
            case 1:
                $product = [];
                for ($i = 0; $i < count($request->product); $i++) {
                    array_push($product, Product::where([['branch_id', '=', $request->branch], ['main_code', '=', $request->product[$i]]])->first()->id);
                }
                $request->product = $product;
                $paymentMethod = [];
                for ($i = 0; $i < count($request->paymentMethod); $i++) {
                    array_push($paymentMethod, PaymentMethod::where('code', '=', $request->paymentMethod[$i])->first()->id);
                }
                $request->paymentMethod = $paymentMethod;
                break;
            case 2:
                $product = [];
                for ($i = 0; $i < count($request->product); $i++) {
                    array_push($product, Product::where([['branch_id', '=', $request->branch], ['main_code', '=', $request->product[$i]]])->first()->id);
                }
                $request->product = $product;
                break;
            case 3:
                $paymentMethod = [];
                for ($i = 0; $i < count($request->paymentMethod); $i++) {
                    array_push($paymentMethod, PaymentMethod::where('code', '=', $request->paymentMethod[$i])->first()->id);
                }
                $request->paymentMethod = $paymentMethod;
                $request->iva_tax = IvaTax::where('auxiliary_code', '=', $request->iva_tax)->first()->id;
                break;
            case 4:
                $product = [];
                for ($i = 0; $i < count($request->product); $i++) {
                    array_push($product, Product::where([['branch_id', '=', $request->branch], ['main_code', '=', $request->product[$i]]])->first()->id);
                }
                $request->product = $product;
                $request->identification_type = IdentificationType::where('code', '=', $request->identification_type)->first()->id;
                break;
            case 5:
                $tax = [];
                for ($i = 0; $i < count($request->tax); $i++) {
                    array_push($tax, RetentionTax::where('code', '=', $request->tax[$i])->first()->id);
                }
                $request->tax = $tax;
                $description = [];
                for ($i = 0; $i < count($request->description); $i++) {
                    array_push($description, RetentionTaxDescription::where('code', '=', $request->description[$i])->first()->id);
                }
                $request->description = $description;
                $request->voucher_type_support_document = VoucherType::where('code', '=', $request->voucher_type_support_document)->first()->id;
                break;
        }
    }

    private static function changeToIdsProduct(Request $request)
    {
        $request->company = Company::where('ruc', '=', $request->company)->first()->id;
        $request->branch = Branch::where([['company_id', '=', $request->company], ['establishment', '=', $request->branch]])->first()->id;
    }

    private static function changeToIdsCustomer(Request $request)
    {
        $request->company = Company::where('ruc', '=', $request->company)->first()->id;
        $request->identification_type = IdentificationType::where('code', '=', $request->identification_type)->first()->id;
    }
}
