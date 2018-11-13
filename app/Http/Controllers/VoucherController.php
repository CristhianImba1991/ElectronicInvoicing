<?php

namespace ElectronicInvoicing\Http\Controllers;

use DateTime;
use ElectronicInvoicing\{AdditionalField, Branch, Company, Currency, Customer, Detail, EmissionPoint, Environment, IceTax, IdentificationType, IrbpnrTax, IvaTax, Payment, Product, TaxDetail, Voucher, VoucherState, VoucherType};
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Spatie\ArrayToXml\ArrayToXml;
use Storage;

class VoucherController extends Controller
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
        //
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        $user = Auth::user();
        $companies = Company::all();
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
        if (intval($state) >= 1) {
            $voucher = self::saveVoucher($request, $state);
            if (intval($state) >= 2) {
                self::acceptVoucher($voucher);
                if (intval($state) >= 4) {
                    self::sendVoucher($voucher);
                }
            }
        }

        /*$voucher = [
            '_attributes' => ['id' => 'comprobante', 'version' => '1.0.0'],
            'infoTributaria' => [
                'ambiente'          => 1,
                'tipoEmision'       => 1,
                'razonSocial'       => 'Distribuidora de Suministros Nacional S.A.',
                'nombreComercial'   => 'Empresa Importadora y Exportadora de Piezas',
                'ruc'               => '1792146739001',
                'claveAcceso'       => '2110201101179214673900110020010000000011234567813',
                'codDoc'            => '01',
                'estab'             => '002',
                'ptoEmi'            => '001',
                'secuencial'        => '000000001',
                'dirMatriz'         => 'Enrique Guerrero Portilla OE1-34 AV. Galo Plaza Lasso',
            ],
        ];
        Storage::put('xmls/1792146739001/AUTHORIZED/2018/10/2110201101179214673900110020010000000011234567813.xml', ArrayToXml::convert($voucher, 'factura', false, 'UTF-8'));*/
        return 'TRUE';
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
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \ElectronicInvoicing\Voucher  $voucher
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, Voucher $voucher)
    {
        //
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

    private static function generateRandomNumericCode()
    {
        for ($i = 0; $i < 8; $i++) {
            $numericCode[$i] = rand(0, 9);
        }
    	return implode($numericCode);
    }

    private static function getCheckDigit($numericCode)
    {
        $summation = 0;
        $factor = 3;
        foreach (str_split($numericCode) as $number) {
            $summation += intval($number) * $factor--;
            $factor = $factor == 1 ? 7 : $factor;
        }
        $checkDigit = 11 - $summation % 11;
        return $checkDigit == 10 ? 1 : ($checkDigit == 11 ? 0 : $checkDigit);
    }

    private static function saveVoucher($request, $state)
    {
        $company = Company::find($request->company);
        $branch = Branch::find($request->branch);
        $emissionPoint = EmissionPoint::find($request->emission_point);
        $customer = Customer::find($request->customer);
        $emission = 'NORMAL';
        $currency = Currency::find($request->currency);
        $environment = Environment::find($request->environment);
        $voucherType = VoucherType::find($request->voucher_type);
        $issueDate = DateTime::createFromFormat('Y-m-d', $request->issue_date);
        $voucherState = VoucherState::find($state);
        $sequential = Voucher::where([
            ['emission_point_id', '=', $emissionPoint->id],
            ['voucher_type_id', '=', $voucherType->id],
            ['environment_id', '=', $environment->id],
            ['voucher_state_id', $voucherState->id < 5 ? '<' : '>=', 5],
        ])->max('sequential') + 1;

        $voucher = new Voucher;
        $voucher->emission_point_id = $emissionPoint->id;
        $voucher->voucher_type_id = $voucherType->id;
        $voucher->environment_id = $environment->id;
        $voucher->voucher_state_id = $voucherState->id;
        $voucher->sequential = $sequential;
        $voucher->numeric_code = self::generateRandomNumericCode();
        $voucher->customer_id = $customer->id;
        $voucher->issue_date = $issueDate->format('Y-m-d');
        $voucher->currency_id = $currency->id;
        $voucher->tip = $request->tip;
        $voucher->iva_retention = $request->ivaRetentionValue;
        $voucher->rent_retention = $request->rentRetentionValue;
        $voucher->extra_detail = $request->extra_detail;
        $voucher->user_id = Auth::user()->id;
        $voucher->save();

        $products = $request->product;
        $quantities = $request->product_quantity;
        $unitPrices = $request->product_unitprice;
        $discounts = $request->product_discount;
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

        $names = $request->additionaldetail_name;
        $values = $request->additionaldetail_value;
        for ($i = 0; $i < count($names); $i++) {
            $additionalFields = new AdditionalField;
            $additionalFields->voucher_id = $voucher->id;
            $additionalFields->name = $names[$i];
            $additionalFields->value = $values[$i];
            $additionalFields->save();
        }

        return $voucher;
    }

    private static function acceptVoucher($voucher)
    {
        
    }

    private static function rejectVoucher()
    {

    }

    private static function sendVoucher()
    {

    }

    private static function cancelVoucher()
    {

    }
}
