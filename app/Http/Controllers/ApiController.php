<?php

namespace ElectronicInvoicing\Http\Controllers;

use Carbon\Carbon;
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
    VoucherState,
    VoucherType,
};
use ElectronicInvoicing\StaticClasses\{ValidationRule, VoucherStates};
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
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
                'tokenType' => 'Bearer',
                'expireDate' => Carbon::parse($personalAccessToken->token->expires_at)->toDateTimeString(),
                'accessToken' => $personalAccessToken->accessToken
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
            //VoucherController::sendVoucher($voucher);
            return response()->json([
                'code' => 200,
                'message' => trans_choice(__('message.model_added_successfully', ['model' => trans_choice(__('view.voucher'), 0)]), 0),
                'voucher' => [
                    'accessKey' => $voucher->accessKey(),
                    'state' => __(VoucherState::find($voucher->voucher_state_id)->name),
                    'authorizationDate' => $voucher->authorization_date,
                    'extraDetail' => $voucher->extra_detail,
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
}
