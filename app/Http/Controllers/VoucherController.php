<?php

namespace ElectronicInvoicing\Http\Controllers;

use DateTime;
use DateTimeZone;
use ElectronicInvoicing\{AdditionalField, Branch, Company, Currency, Customer, Detail, EmissionPoint, Environment, IceTax, IdentificationType, IrbpnrTax, IvaTax, Payment, PaymentMethod, Product, TaxDetail, TimeUnit, Voucher, VoucherState, VoucherType};
use ElectronicInvoicing\StaticClasses\VoucherStates;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use SoapClient;
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

    private static function getCheckDigit($accessKey)
    {
        $summation = 0;
        $factor = 7;
        foreach (str_split($accessKey) as $number) {
            $summation += intval($number) * $factor--;
            $factor = $factor == 1 ? 7 : $factor;
        }
        $checkDigit = 11 - $summation % 11;
        return $checkDigit == 10 ? 1 : ($checkDigit == 11 ? 0 : $checkDigit);
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

    private static function saveVoucher($request, $state)
    {
        $company = Company::find($request->company);
        $branch = Branch::find($request->branch);
        $emissionPoint = EmissionPoint::find($request->emission_point);
        $customer = Customer::find($request->customer);
        $currency = Currency::find($request->currency);
        $environment = Environment::find($request->environment);
        $voucherType = VoucherType::find($request->voucher_type);
        $issueDate = DateTime::createFromFormat('Y-m-d', $request->issue_date);
        $voucherState = VoucherState::find(VoucherStates::SAVED);
        $sequential = Voucher::where([
            ['emission_point_id', '=', $emissionPoint->id],
            ['voucher_type_id', '=', $voucherType->id],
            ['environment_id', '=', $environment->id],
            ['voucher_state_id', '<', VoucherStates::SENDED],
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
        if ($request->waybill_establishment !== NULL && $request->waybill_emissionpoint !== NULL && $request->waybill_sequential !== NULL) {
            $voucher->support_document = str_pad($request->waybill_establishment, 3, '0', STR_PAD_LEFT) . str_pad($request->waybill_emissionpoint, 3, '0', STR_PAD_LEFT) . str_pad($request->waybill_sequential, 9, '0', STR_PAD_LEFT);
        }
        $voucher->support_document_date = NULL;
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

    private static function signVoucher($voucher)
    {
        $version = '1.0.0';
        foreach ($voucher->details as $detail) {
            if (strlen(substr(strrchr(strval(floatval($detail->quantity)), "."), 1)) > 2 || strlen(substr(strrchr(strval(floatval($detail->unit_price)), "."), 1)) > 2) {
                $version = '1.1.0';
                break;
            }
        }
        $issueDate = DateTime::createFromFormat('Y-m-d', $voucher->issue_date);
        $state = VoucherState::find(VoucherStates::ACCEPTED);
        $voucherTypeCode = str_pad(strval(VoucherType::find($voucher->voucher_type_id)->code), 2, '0', STR_PAD_LEFT);
        $establishment = str_pad(strval($voucher->emissionPoint->branch->establishment), 3, '0', STR_PAD_LEFT);
        $emissionPoint = str_pad(strval($voucher->emissionPoint->code), 3, '0', STR_PAD_LEFT);
        $sequential = str_pad(strval($voucher->sequential), 9, '0', STR_PAD_LEFT);
        $numericCode = str_pad(strval($voucher->numeric_code), 8, '0', STR_PAD_LEFT);
        $accessKey = $issueDate->format('dmY') . $voucherTypeCode . $voucher->emissionPoint->branch->company->ruc . $voucher->environment->code . $establishment . $emissionPoint . $sequential . $numericCode . '1';
        $accessKey = $accessKey . self::getCheckDigit($accessKey);
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
        $xml['infoFactura'] = [
            'fechaEmision'                  => $issueDate->format('d/m/Y'),
            'contribuyenteEspecial'         => $voucher->emissionPoint->branch->company->special_contributor,
            'obligadoContabilidad'          => $voucher->emissionPoint->branch->company->keep_accounting ? 'SI' : 'NO',
            'tipoIdentificacionComprador'   => str_pad(strval($voucher->customer->identificationType->code), 2, '0', STR_PAD_LEFT),
            'guiaRemision'                  => $voucher->support_document,
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
        foreach ($voucher->details as $detail) {
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
        foreach ($voucher->payments as $payment) {
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
        foreach ($voucher->details as $detail) {
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
        if (count($voucher->aditionalFields) > 0) {
            $voucherAdditionalFields = array();
            foreach ($voucher->aditionalFields as $additionalField) {
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
        Storage::put($xmlPath, ArrayToXml::convert($xml, 'factura', false, 'UTF-8'));
        $voucher->xml = $xmlPath;
        $voucher->save();



        $xml = new \DOMDocument('1.0', 'UTF-8');
        $xml->preserveWhiteSpace = true;
		$xml->formatOutput = false;
        if ($xml->load(storage_path('app/' . $voucher->xml))) {
            $voucherDocument = $xml->getElementsByTagName('factura')->item(0);
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
            $signature->setAttribute( 'xmlns:ds', 'http://www.w3.org/2000/09/xmldsig#' );
            $signature->setAttribute( 'xmlns:etsi', 'http://uri.etsi.org/01903/v1.3.2#' );
            $signature->setAttribute( 'Id', 'Signature' . $SignatureNumber );
            $voucherDocument->appendChild($signature);

            $SignedInfo = $xml->createElement('ds:SignedInfo');
            $SignedInfo->setAttribute( 'Id', 'Signature-SignedInfo' . $SignedInfoNumber );
            $signature->appendChild( $SignedInfo );
            $CanonicalizationMethod = $xml->createElement('ds:CanonicalizationMethod');
            $CanonicalizationMethod->setAttribute( 'Algorithm', 'http://www.w3.org/TR/2001/REC-xml-c14n-20010315' );
            $SignedInfo->appendChild( $CanonicalizationMethod );

            $SignatureMethod = $xml->createElement('ds:SignatureMethod');
            $SignatureMethod->setAttribute( 'Algorithm', 'http://www.w3.org/2000/09/xmldsig#rsa-sha1' );
            $SignedInfo->appendChild( $SignatureMethod );

            $Reference1 = $xml->createElement( 'ds:Reference' );
            $Reference1->setAttribute( 'Id', 'SignedPropertiesID' . $SignedPropertiesIDNumber );
            $Reference1->setAttribute( 'Type', 'http://uri.etsi.org/01903#SignedProperties' );
            $Reference1->setAttribute( 'URI', '#Signature' . $SignatureNumber . '-SignedProperties' . $SignedPropertiesNumber );
            $SignedInfo->appendChild( $Reference1 );
            $DigestMethod = $xml->createElement( 'ds:DigestMethod', '' );
            $Reference1->appendChild( $DigestMethod );
            $DigestMethod->setAttribute( 'Algorithm', 'http://www.w3.org/2000/09/xmldsig#sha1' );
            $DigestValue1 = $xml->createElement( 'ds:DigestValue' );
            $Reference1->appendChild( $DigestValue1 );

            $Reference2 = $xml->createElement( 'ds:Reference' );
            $Reference2->setAttribute( 'URI', '#Certificate' . $CertificateNumber );
            $SignedInfo->appendChild( $Reference2 );
            $DigestMethod = $xml->createElement( 'ds:DigestMethod', '' );
            $DigestMethod->setAttribute( 'Algorithm', 'http://www.w3.org/2000/09/xmldsig#sha1' );
            $Reference2->appendChild( $DigestMethod );
            $DigestValue2 = $xml->createElement( 'ds:DigestValue' );
            $Reference2->appendChild( $DigestValue2 );

            $Reference3 = $xml->createElement( 'ds:Reference' );
            $Reference3->setAttribute( 'Id', 'Reference-ID-' . $ReferenceIDNumber );
            $Reference3->setAttribute( 'URI', '#comprobante' );
            $SignedInfo->appendChild( $Reference3 );
            $Transforms = $xml->createElement( 'ds:Transforms' );
            $Reference3->appendChild( $Transforms );
            $Transform = $xml->createElement( 'ds:Transform' );
            $Transform->setAttribute( 'Algorithm', 'http://www.w3.org/2000/09/xmldsig#enveloped-signature' );
            $Transforms->appendChild( $Transform );
            $DigestMethod = $xml->createElement( 'ds:DigestMethod' );
            $DigestMethod->setAttribute( 'Algorithm', 'http://www.w3.org/2000/09/xmldsig#sha1' );
            $Reference3->appendChild( $DigestMethod );
            $DigestValue3 = $xml->createElement( 'ds:DigestValue', $digestValueVoucher );
            $Reference3->appendChild( $DigestValue3 );

            $SignatureValue = $xml->createElement( 'ds:SignatureValue' );
            $SignatureValue->setAttribute( 'Id', 'SignatureValue' . $SignatureValueNumber );
            $signature->appendChild( $SignatureValue );

            $KeyInfo = $xml->createElement( "ds:KeyInfo" );
            $KeyInfo->setAttribute( 'Id', 'Certificate' . $CertificateNumber );
            $signature->appendChild( $KeyInfo );
            $X509Data = $xml->createElement( "ds:X509Data" );
            $KeyInfo->appendChild( $X509Data );
            $X509Certificate = $xml->createElement( "ds:X509Certificate", "\n" . $filledCert . "\n" );
            $X509Data->appendChild( $X509Certificate );
            $KeyValue = $xml->createElement( "ds:KeyValue" );
            $KeyInfo->appendChild( $KeyValue );
            $RSAKeyValue = $xml->createElement( "ds:RSAKeyValue" );
            $KeyValue->appendChild( $RSAKeyValue );
            $Modulus = $xml->createElement( "ds:Modulus", "\n" . $modulus . "\n" );
            $RSAKeyValue->appendChild( $Modulus );
            $Exponent = $xml->createElement( "ds:Exponent", "AQAB" );
            $RSAKeyValue->appendChild( $Exponent );

            $xml->save(storage_path('app/' . $voucher->xml), LIBXML_NOEMPTYTAG);
            $xml->load(storage_path('app/' . $voucher->xml), LIBXML_NOEMPTYTAG);

            $Object = $xml->createElement("ds:Object");
            $Object->setAttribute( 'Id', 'Signature' . $SignatureNumber . '-Object' . $ObjectNumber );
            $signature = $xml->getElementsByTagName( 'Signature' )->item( 0 );
            $signature->appendChild( $Object );

            $QualifyingProperties = $xml->createElement( "etsi:QualifyingProperties" );
            $QualifyingProperties->setAttribute( 'Target', '#Signature' . $SignatureNumber );
            $Object->appendChild($QualifyingProperties);

            $SignedProperties = $xml->createElement( "etsi:SignedProperties" );
            $SignedProperties->setAttribute( 'Id', 'Signature' . $SignatureNumber . "-SignedProperties" . $SignedPropertiesNumber );
            $QualifyingProperties->appendChild( $SignedProperties );
            $SignedSignatureProperties = $xml->createElement( "etsi:SignedSignatureProperties" );
            $SignedProperties->appendChild( $SignedSignatureProperties );
            $date = new DateTime( "now", new DateTimeZone( 'America/Guayaquil' ) );
            $SigningTime = $xml->createElement( "etsi:SigningTime", $date->format( 'c' ) );
            $SignedSignatureProperties->appendChild( $SigningTime );
            $SigningCertificate = $xml->createElement( "etsi:SigningCertificate" );
            $SignedSignatureProperties->appendChild( $SigningCertificate );
            $Cert = $xml->createElement( "etsi:Cert" );
            $SigningCertificate->appendChild( $Cert );
            $CertDigest = $xml->createElement( "etsi:CertDigest" );
            $Cert->appendChild( $CertDigest );
            $DigestMethod = $xml->createElement( "ds:DigestMethod" );
            $DigestMethod->setAttribute( 'Algorithm', 'http://www.w3.org/2000/09/xmldsig#sha1' );
            $CertDigest->appendChild( $DigestMethod );
            $DigestValue = $xml->createElement( "ds:DigestValue", $digestValueCertificate );
            $CertDigest->appendChild( $DigestValue );
            $IssuerSerial = $xml->createElement( "etsi:IssuerSerial" );
            $Cert->appendChild( $IssuerSerial );
            $X509IssuerName = $xml->createElement( "ds:X509IssuerName", $issuerName );
            $IssuerSerial->appendChild( $X509IssuerName );
            $X509SerialNumber = $xml->createElement( "ds:X509SerialNumber", $certInformation['serialNumber'] );
            $IssuerSerial->appendChild( $X509SerialNumber );
            $SignedDataObjectProperties = $xml->createElement( "etsi:SignedDataObjectProperties" );
            $SignedProperties->appendChild( $SignedDataObjectProperties );
            $DataObjectFormat = $xml->createElement( "etsi:DataObjectFormat" );
            $DataObjectFormat->setAttribute( "ObjectReference", "#Reference-ID-" . $ReferenceIDNumber );
            $SignedDataObjectProperties->appendChild( $DataObjectFormat );
            $SignedDataObjectProperties = $xml->createElement( "etsi:Description", 'contenido comprobante' );
            $DataObjectFormat->appendChild( $SignedDataObjectProperties );
            $SignedDataObjectProperties = $xml->createElement( "etsi:MimeType", 'text/xml' );
            $DataObjectFormat->appendChild( $SignedDataObjectProperties );

            $xml->save(storage_path('app/' . $voucher->xml), LIBXML_NOEMPTYTAG);
            $xml->load(storage_path('app/' . $voucher->xml), LIBXML_NOEMPTYTAG);

            $primerDigestRef = self::hex2Base64( sha1( $xml->getElementsByTagName( "SignedProperties" )->item( 0 )->C14N() ) );
        	$segundoDigestRef = self::hex2Base64( sha1( $xml->getElementsByTagName( "KeyInfo" )->item( 0 )->C14N() ) );

        	$reference1 = $xml->getElementsByTagName( "Reference" )->item( 0 );
        	$DigestValue1 = $reference1->getElementsByTagName( "DigestValue" )->item( 0 );
        	$text = $xml->createTextNode( $primerDigestRef );
        	$text = $DigestValue1->appendChild( $text );

        	$reference2 = $xml->getElementsByTagName( "Reference" )->item( 1 );
        	$DigestValue2 = $reference2->getElementsByTagName( "DigestValue" )->item( 0 );
        	$text = $xml->createTextNode( $segundoDigestRef );
        	$text = $DigestValue2->appendChild( $text );

            $xml->save(storage_path('app/' . $voucher->xml), LIBXML_NOEMPTYTAG);
            $xml->load(storage_path('app/' . $voucher->xml), LIBXML_NOEMPTYTAG);

            openssl_sign( $xml->getElementsByTagName( "SignedInfo" )->item( 0 )->C14N(), $signature, $pkey );
            $valorFirma = wordwrap( base64_encode( $signature ), 76, "\n", true );

            $SignatureValue = $xml->getElementsByTagName( "SignatureValue" )->item( 0 );
        	$text = $xml->createTextNode( "\n" . $valorFirma . "\n" );
        	$text = $SignatureValue->appendChild( $text );

            $xml->save(storage_path('app/' . $voucher->xml), LIBXML_NOEMPTYTAG);
        }
    }

    private static function rejectVoucher()
    {

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
                break;
            case 'DEVUELTA':
                $voucher->voucher_state_id = VoucherStates::RETURNED;
                $voucher->save();
                $message = $resultReceipt['RespuestaRecepcionComprobante']['comprobantes']['comprobante']['mensajes']['mensaje']['tipo'] . ' ' .
                    $resultReceipt['RespuestaRecepcionComprobante']['comprobantes']['comprobante']['mensajes']['mensaje']['identificador'] . ': ' .
                    $resultReceipt['RespuestaRecepcionComprobante']['comprobantes']['comprobante']['mensajes']['mensaje']['mensaje'];
                if (array_key_exists('informacionAdicional', $resultReceipt['RespuestaRecepcionComprobante']['comprobantes']['comprobante']['mensajes']['mensaje'])) {
                    $message .= '. ' . $resultReceipt['RespuestaRecepcionComprobante']['comprobantes']['comprobante']['mensajes']['mensaje']['informacionAdiccional'];
                }
                dd($message);
                break;
        }

        $soapClientValidation = new SoapClient($wsdlValidation);
        $voucherAccessKey = DateTime::createFromFormat('Y-m-d', $voucher->issue_date)->format('dmY') .
            str_pad(strval(VoucherType::find($voucher->voucher_type_id)->code), 2, '0', STR_PAD_LEFT) .
            $voucher->emissionPoint->branch->company->ruc .
            $voucher->environment->code .
            str_pad(strval($voucher->emissionPoint->branch->establishment), 3, '0', STR_PAD_LEFT) .
            str_pad(strval($voucher->emissionPoint->code), 3, '0', STR_PAD_LEFT) .
            str_pad(strval($voucher->sequential), 9, '0', STR_PAD_LEFT) .
            str_pad(strval($voucher->numeric_code), 8, '0', STR_PAD_LEFT) .
            '1';
        $accessKey = array(
            'autorizacionComprobante' => array(
                'claveAccesoComprobante' =>  $voucherAccessKey . self::getCheckDigit($voucherAccessKey)
            )
        );
        $resultValidation = $soapClientValidation->__soapCall("autorizacionComprobante", $accessKey);
        //$resultValidation = $soapClientValidation->autorizacionComprobante($voucherAccessKey . self::getCheckDigit($voucherAccessKey));
        dd($resultValidation);
    }

    private static function cancelVoucher()
    {

    }
}
