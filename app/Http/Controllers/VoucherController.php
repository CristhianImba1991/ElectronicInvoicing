<?php

namespace ElectronicInvoicing\Http\Controllers;

use ElectronicInvoicing\{Company, Currency, Environment, IdentificationType, Product, Voucher, VoucherType};
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
        return $request;
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
}
