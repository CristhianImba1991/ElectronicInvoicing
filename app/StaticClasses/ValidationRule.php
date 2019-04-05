<?php

namespace ElectronicInvoicing\StaticClasses;

use DateTime;
use DateTimeZone;
use ElectronicInvoicing\{Company, Customer};
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class ValidationRule
{
    public static function makeRule($model, Request $request, $state = NULL)
    {
        switch ($model) {
            case 'branch':
                if ($request->method() === 'PUT') {
                    $rules = [
                        'company' => 'required|exists:companies,id',
                        'establishment' => 'required|min:1|max:999|integer',
                        'name' => 'required|max:300',
                        'address' => 'required|max:300',
                        'phone' => 'required|max:30',
                    ];
                } else {
                    $rules = [
                        'company' => 'required|exists:companies,id',
                        //'establishment' => 'required|min:1|max:999|integer|uniquemultiple:branches,company_id,' . $request->company . ',establishment,' . $request->establishment,
                        'establishment' => ['required', 'min:1', 'max:999', 'integer', 'uniquemultiple:branches,company_id,"' . $request->company . '",establishment,"' . $request->establishment . '"'],
                        'name' => 'required|max:300',
                        'address' => 'required|max:300',
                        'phone' => 'required|max:30',
                    ];
                }
                break;
            case 'company':
                if ($request->method() === 'PUT') {
                    $rules = [
                        'ruc' => 'required|digits:13|validruc',
                        'social_reason' => 'required|max:300',
                        'tradename' => 'required|max:300',
                        'address' => 'required|max:300',
                        'special_contributor' => 'max:13',
                        //'keep_accounting',
                        'phone' => 'required|max:30',
                        'logo' => 'mimes:jpeg,jpg,png|max:2048',
                        'sign' => 'mimetypes:application/x-pkcs12,application/octet-stream|mimes:p12,bin|max:32',
                        'password' => 'required_with:sign',
                    ];
                    if ($request->has('sign')) {
                        //$rules['sign'] .= '|validsign:' . $request->password;
                        $rules['sign'] = ['mimetypes:application/x-pkcs12,application/octet-stream', 'mimes:p12,bin', 'max:32', 'validsign:"' . $request->password . '"'];
                    }
                } else {
                    $rules = [
                        'ruc' => 'required|digits:13|unique:companies|validruc',
                        'social_reason' => 'required|max:300',
                        'tradename' => 'required|max:300',
                        'address' => 'required|max:300',
                        'special_contributor' => 'max:13',
                        //'keep_accounting',
                        'phone' => 'required|max:30',
                        'logo' => 'mimes:jpeg,jpg,png|max:2048',
                        //'sign' => 'required|mimetypes:application/x-pkcs12,application/octet-stream|mimes:p12,bin|max:32|validsign:' . $request->password,
                        'sign' => ['required', 'mimetypes:application/x-pkcs12,application/octet-stream', 'mimes:p12,bin', 'max:32', 'validsign:"' . $request->password . '"'],
                        'password' => 'required',
                    ];
                }
                break;
            case 'customer':
                $isApiRequest = Str::startsWith($request->decodedPath(), 'api/auth');
                if ($request->method() === 'PUT') {
                    $rules = [
                        'company' => $isApiRequest ? 'required|digits:13|validruc|exists:companies,ruc' : 'required|exists:companies,id',
                        'identification_type' => $isApiRequest ? 'required|exists:identification_types,code' : 'required|exists:identification_types,id',
                        'identification' => 'required|exists:customers,identification|max:20' . ($request['identification_type'] == ($isApiRequest ? 4 : 1) ? '|validruc' : ($request['identification_type'] == ($isApiRequest ? 5 : 2) ? '|validcedula' : '')),
                        'social_reason' => 'required|max:300',
                        'address' => 'required|max:300',
                        'phone' => 'max:30',
                        'email' => 'required|max:300|validemailmultiple',
                    ];
                } else {
                    $rules = [
                        'company' => $isApiRequest ? 'required|digits:13|validruc|exists:companies,ruc' : 'required|exists:companies,id',
                        'identification_type' => $isApiRequest ? 'required|exists:identification_types,code' : 'required|exists:identification_types,id',
                        'identification' => 'required|max:20',
                        'social_reason' => 'required|max:300',
                        'address' => 'required|max:300',
                        'phone' => 'max:30',
                        'email' => 'required|max:300|validemailmultiple',
                    ];
                    if ($isApiRequest) {
                        $company = Company::where('ruc', '=', $request->company)->first();
                        $customer = Customer::join('company_customers', 'customers.id', '=', 'company_customers.customer_id')->where([['customers.identification', '=', $request->identification], ['company_customers.company_id', '=', ($company === NULL ? $company : $company->id)]])->first();
                        $rules['identification'] = ['required', 'max:20', $request['identification_type'] == ($isApiRequest ? 4 : 1) ? 'validruc' : ($request['identification_type'] == ($isApiRequest ? 5 : 2) ? 'validcedula' : ''), 'uniquecustomer:company_customers,company_id,"' . ($company === NULL ? $company : $company->id) . '",customer_id,"' . ($customer === null ? -1 : $customer->id) . '"'];
                    } else {
                        $customer = Customer::join('company_customers', 'customers.id', '=', 'company_customers.customer_id')->where([['customers.identification', '=', $request->identification], ['company_customers.company_id', '=', $request->company]])->first();
                        $rules['identification'] = ['required', 'max:20', $request['identification_type'] == ($isApiRequest ? 4 : 1) ? 'validruc' : ($request['identification_type'] == ($isApiRequest ? 5 : 2) ? 'validcedula' : ''), 'uniquecustomer:company_customers,company_id,"' . $request->company . '",customer_id,"' . ($customer === null ? -1 : $customer->id) . '"'];
                    }
                }
                break;
            case 'emission_point':
                if ($request->method() === 'PUT') {
                    $rules = [
                        'company' => 'required|exists:companies,id',
                        'branch' => 'required|exists:branches,id',
                        'code' => 'required|min:1|max:999|integer',
                    ];
                } else {
                    $rules = [
                        'company' => 'required|exists:companies,id',
                        'branch' => 'required|exists:branches,id',
                        //'code' => 'required|min:1|max:999|integer|uniquemultiple:emission_points,branch_id,' . $request->branch . ',code,' . $request->code,
                        'code' => ['required', 'min:1', 'max:999', 'integer', 'uniquemultiple:emission_points,branch_id,"' . $request->branch . '",code,"' . $request->code. '"'],
                    ];
                }
                break;
            case 'product':
                $isApiRequest = Str::startsWith($request->decodedPath(), 'api/auth');
                if ($request->method() === 'PUT') {
                    $rules = [
                        'main_code' => 'required|max:25',
                        'auxiliary_code' => 'required|max:25',
                        'unit_price' => 'required|gt:0',
                        'description'=> 'required|max:300',
                        'stock' => 'required|numeric|gt:0',
                        'iva_tax' => $isApiRequest ? 'required|exists:iva_taxes,auxiliary_code' : 'required|exists:iva_taxes,id',
                        'ice_tax' => $isApiRequest ? 'nullable|exists:ice_taxes,auxiliary_code' : 'nullable|exists:ice_taxes,id',
                        'irbpnr_tax' => $isApiRequest ? 'nullable|exists:irbpnr_taxes,auxiliary_code' : 'nullable|exists:irbpnr_taxes,id',
                    ];
                } else {
                    $rules = [
                        'company' => $isApiRequest ? 'required|digits:13|validruc|exists:companies,ruc' : 'required|exists:companies,id',
                        'branch' => $isApiRequest ? 'required|min:1|max:999|integer|exists:branches,establishment' : 'required|exists:branches,id',
                        //'main_code' => 'required|max:25|uniquemultiple:products,branch_id,' . $request->branch . ',main_code,' . $request->main_code,
                        'main_code' => ['required', 'max:25', 'uniquemultiple:products,branch_id,"' . $request->branch . '",main_code,"' . $request->main_code . '"'],
                        'auxiliary_code' => 'required|max:25',
                        'unit_price' => 'required|gt:0',
                        'description'=> 'required|max:300',
                        'stock' => 'required|numeric|gt:0',
                        'iva_tax' => $isApiRequest ? 'required|exists:iva_taxes,auxiliary_code' : 'required|exists:iva_taxes,id',
                        'ice_tax' => $isApiRequest ? 'nullable|exists:ice_taxes,auxiliary_code' : 'nullable|exists:ice_taxes,id',
                        'irbpnr_tax' => $isApiRequest ? 'nullable|exists:irbpnr_taxes,auxiliary_code' : 'nullable|exists:irbpnr_taxes,id',
                    ];
                }
                break;
            case 'user':
                if ($request->method() === 'PUT') {
                    $rules = [
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
                    ];
                } else {
                    $rules = [
                        'role' => 'required|exists:roles,name|string',
                        'name' => 'required|max:255',
                        'email' => 'required|email|max:255|unique:users',
                        'password' => 'required|min:6|confirmed',
                        'company' => 'required_unless:role,api,admin|min:1',
                        'company.*' => 'exists:companies,id',
                        'branch' => 'required_unless:role,admin,api,owner|min:1',
                        'branch.*' => 'exists:branches,id',
                        'emission_point' => 'required_unless:role,admin,api,owner|min:1',
                        'emission_point.*' => 'exists:emission_points,id',
                    ];
                }
                break;
            case 'voucher':
                $isApiRequest = Str::startsWith($request->decodedPath(), 'api/auth');
                $date = new DateTime('now', new DateTimeZone('America/Guayaquil'));
                $rules = [
                    'company' => $isApiRequest ? 'required|digits:13|validruc|exists:companies,ruc' : 'required|numeric|exists:companies,id',
                    'branch' => $isApiRequest ? 'required|min:1|max:999|integer|exists:branches,establishment' : 'required|numeric|exists:branches,id',
                    'emission_point' => $isApiRequest ? 'required|min:1|max:999|integer|exists:emission_points,code' : 'required|numeric|exists:emission_points,id',
                    'customer' => $isApiRequest ? 'required|exists:customers,identification|max:20' : 'required|numeric|exists:customers,id',
                    'currency' => 'required|numeric|exists:currencies,id',
                    //'issue_date' => 'required|date|before_or_equal:' . $date->format('Y/m/d'),
                    'issue_date' => ['required', 'date_format:Y-m-d', 'before_or_equal:"' . $date->format('Y-m-d') . '"'],
                    'environment' => $isApiRequest ? 'required|numeric|exists:environments,code' : 'required|numeric|exists:environments,id',
                    'voucher_type' => $isApiRequest ? 'required|numeric|exists:voucher_types,code' : 'required|numeric|exists:voucher_types,id'
                ];
                if ($state !== NULL) {
                    if ($state >= VoucherStates::SENDED) {
                        $rules['company'] .= $isApiRequest ? '|sign_not_expired:ruc' : '|sign_not_expired:id';
                    }
                }
                if ($request->voucher_type !== NULL) {
                    switch ($request->voucher_type) {
                        case 1:
                            $rules['product'] = 'required|array|min:1';
                            $rules['product.*'] = $isApiRequest ? 'distinct|exists:products,main_code' : 'distinct|exists:products,id';
                            $rules['product_detail1'] = 'array';
                            $rules['product_detail1.*'] = 'nullable|string|max:300';
                            $rules['product_detail2'] = 'array';
                            $rules['product_detail2.*'] = 'nullable|string|max:300';
                            $rules['product_detail3'] = 'array';
                            $rules['product_detail3.*'] = 'nullable|string|max:300';
                            $rules['product_quantity'] = 'required|array|min:1';
                            $rules['product_quantity.*'] = 'required|numeric|gte:0';
                            $rules['product_unitprice'] = 'required|array|min:1';
                            $rules['product_unitprice.*'] = 'required|numeric|gte:0';
                            $rules['product_discount'] = 'required|array|min:1';
                            $rules['product_discount.*'] = 'required|numeric|gte:0';
                            $rules['paymentMethod'] = 'required|array|min:1';
                            $rules['paymentMethod.*'] = $isApiRequest ? 'exists:payment_methods,code' : 'exists:payment_methods,id';
                            $rules['paymentMethod_value'] = 'required|array|min:1';
                            $rules['paymentMethod_value.*'] = 'required|numeric|gte:0';
                            $rules['paymentMethod_timeunit'] = 'required|array|min:1';
                            $rules['paymentMethod_timeunit.*'] = 'exists:time_units,id';
                            $rules['paymentMethod_term'] = 'required|array|min:1';
                            $rules['paymentMethod_term.*'] = 'required|numeric|gte:0';
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
                            $rules['product.*'] = $isApiRequest ? 'distinct|exists:products,main_code' : 'distinct|exists:products,id';
                            $rules['product_quantity'] = 'required|array|min:1';
                            $rules['product_quantity.*'] = 'required|numeric|gte:0';
                            $rules['product_unitprice'] = 'required|array|min:1';
                            $rules['product_unitprice.*'] = 'required|numeric|gte:0';
                            $rules['product_discount'] = 'required|array|min:1';
                            $rules['product_discount.*'] = 'required|numeric|gte:0';
                            $rules['supportdocument_establishment'] = 'required|integer|min:1|max:999';
                            $rules['supportdocument_emissionpoint'] = 'required|integer|min:1|max:999';
                            $rules['supportdocument_sequential'] = 'required|integer|min:1|max:999999999';
                            $rules['issue_date_support_document'] = 'required|date_format:Y-m-d|before_or_equal:issue_date';
                            $rules['reason'] = 'required|string|max:300';
                            $rules['extra_detail'] = 'nullable|string';
                            break;
                        case 3:
                            $rules['debit_reason'] = 'required|array|min:1';
                            $rules['debit_reason.*'] = 'required|string|max:300';
                            $rules['debit_value'] = 'required|array|min:1';
                            $rules['debit_value.*'] = 'required|numeric|gte:0';
                            $rules['paymentMethod'] = 'required|array|min:1';
                            $rules['paymentMethod.*'] = $isApiRequest ? 'exists:payment_methods,code' : 'exists:payment_methods,id';
                            $rules['paymentMethod_value'] = 'required|array|min:1';
                            $rules['paymentMethod_value.*'] = 'required|numeric|gte:0';
                            $rules['paymentMethod_timeunit'] = 'required|array|min:1';
                            $rules['paymentMethod_timeunit.*'] = 'exists:time_units,id';
                            $rules['paymentMethod_term'] = 'required|array|min:1';
                            $rules['paymentMethod_term.*'] = 'required|numeric|gte:0';
                            $rules['supportdocument_establishment'] = 'required|integer|min:1|max:999';
                            $rules['supportdocument_emissionpoint'] = 'required|integer|min:1|max:999';
                            $rules['supportdocument_sequential'] = 'required|integer|min:1|max:999999999';
                            $rules['issue_date_support_document'] = 'required|date_format:Y-m-d|before_or_equal:issue_date';
                            $rules['extra_detail'] = 'nullable|string';
                            $rules['iva_tax'] = $isApiRequest ? 'required|exists:iva_taxes,auxiliary_code' : 'required|exists:iva_taxes,id';
                            break;
                        case 4:
                            $rules['product'] = 'required|array|min:1';
                            $rules['product.*'] = $isApiRequest ? 'distinct|exists:products,main_code' : 'distinct|exists:products,id';
                            $rules['product_quantity'] = 'required|array|min:1';
                            $rules['product_quantity.*'] = 'required|numeric|gte:0';
                            $rules['identification_type'] = $isApiRequest ? 'required|exists:identification_types,code' : 'required|exists:identification_types,id';
                            $rules['carrier_ruc'] = 'required|max:20';
                            $rules['carrier_social_reason'] = 'required|max:300';
                            $rules['licence_plate'] = 'required|max:20';
                            $rules['starting_address'] = 'required|max:300';
                            $rules['start_date_transport'] = 'required|date_format:Y-m-d|before_or_equal:end_date_transport';
                            $rules['end_date_transport'] = 'required|date_format:Y-m-d|after_or_equal:start_date_transport';
                            $rules['extra_detail'] = 'nullable|string';
                            $rules['authorization_number'] = 'required|digits:49';
                            $rules['single_customs_doc'] = 'nullable|string|max:20';
                            $rules['address'] = 'required|string|max:300';
                            $rules['transfer_reason'] = 'required|string|max:300';
                            $rules['destination_establishment_code'] = 'nullable|min:1|max:999|integer';
                            $rules['route'] = 'required|string|max:300';
                            break;
                        case 5:
                            $rules['tax'] = 'required|array|min:1';
                            $rules['tax.*'] = $isApiRequest ? 'exists:retention_taxes,code' : 'exists:retention_taxes,id';
                            $rules['description'] = 'required|array|min:1';
                            $rules['description.*'] = $isApiRequest ? 'distinct|exists:retention_tax_descriptions,code' : 'distinct|exists:retention_tax_descriptions,id';
                            $rules['value'] = 'required|array|min:1';
                            $rules['value.*'] = 'required|numeric|gte:0';
                            $rules['tax_base'] = 'required|array|min:1';
                            $rules['tax_base.*'] = 'required|numeric|gte:0';
                            $rules['extra_detail'] = 'nullable|string';
                            $rules['voucher_type_support_document'] = $isApiRequest ? 'required|exists:voucher_types,code' : 'required|exists:voucher_types,id';
                            $rules['supportdocument_establishment'] = 'required|nullable|integer|min:1|max:999';
                            $rules['supportdocument_emissionpoint'] = 'required|nullable|integer|min:1|max:999';
                            $rules['supportdocument_sequential'] = 'required|nullable|integer|min:1|max:999999999';
                            //$rules['issue_date_support_document'] = 'required|date|before_or_equal:' . $date->format('Y/m/d');
                            $rules['issue_date_support_document'] = ['required', 'date_format:Y-m-d', 'before_or_equal:"' . $date->format('Y-m-d') . '"'];
                            break;
                    }
                    $rules['additionaldetail_name'] = 'array|max:15';
                    $rules['additionaldetail_name.*'] = 'required|string|max:30';
                    $rules['additionaldetail_value'] = 'array|max:15';
                    $rules['additionaldetail_value.*'] = 'required|string|max:300';
                }
                break;
            default:
                $rules = [];
                break;
        }
        return $rules;
    }
}
