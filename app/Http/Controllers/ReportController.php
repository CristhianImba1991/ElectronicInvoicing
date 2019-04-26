<?php

namespace ElectronicInvoicing\Http\Controllers;

use Chumper\Zipper\Zipper;
use ElectronicInvoicing\{
    IdentificationType,
    IvaTax,
    Payment,
    PaymentMethod,
    RetentionTax,
    RetentionTaxDescription,
    TimeUnit,
    User,
    VoucherState,
    VoucherType
};
use ElectronicInvoicing\Exports\VouchersExport;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\File;
use Laracsv\Export;
use League\Csv\Writer;
use Maatwebsite\Excel\Facades\Excel;
use PDF;

class ReportController extends Controller
{
    private static function getVoucherQueryBuilder()
    {
        return Voucher::join('emission_points', 'emission_points.id', '=', 'vouchers.emission_point_id')
            ->join('branches', 'branches.id', '=', 'emission_points.branch_id')
            ->join('companies', 'companies.id', '=', 'branches.company_id')
            ->join('users', 'users.id', '=', 'vouchers.user_id')
            ->join('customers', 'customers.id', '=', 'vouchers.customer_id')
            ->join('identification_types', 'identification_types.id', '=', 'customer.identification_type_id')
            ->join('environments', 'environments.id', '=', 'vouchers.environment_id')
            ->join('voucher_states', 'voucher_states.id', '=', 'vouchers.voucher_state_id')
            ->join('voucher_types', 'voucher_types.id', '=', 'vouchers.voucher_type_id')
            ->join('currencies', 'currencies.id', '=', 'vouchers.currency_id')
            ->join('additional_fields', 'vouchers.id', '=', 'additional_fields.voucher_id')
            ->select('vouchers.*');
    }

    private static function getFilteredVouchersAllowedToUserQueryBuilder(User $user, Request $criteria)
    {
        $query = VoucherController::getVouchersAllowedToUserQueryBuilder($user);
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

    private static function getVouchersCollection($vouchers)
    {
        $voucherCollection = collect();
        foreach ($vouchers as $voucher) {
            $additionalFields = NULL;
            $payments = NULL;
            if ($voucher->additionalFields()->exists()) {
                $additionalFields = json_encode(array_combine($voucher->additionalFields()->pluck('name')->toArray(), $voucher->additionalFields()->pluck('value')->toArray()), JSON_UNESCAPED_UNICODE);
            }
            if ($voucher->payments()->exists()) {
                $paymentBase = ['METHOD', 'VALUE', 'TIEM UNIT', 'TERM'];
                $paymentsArray = [];
                foreach (Payment::where('voucher_id', '=', $voucher->id)->get() as $payment) {
                    array_push($paymentsArray, array_combine($paymentBase, [
                        PaymentMethod::find($payment->payment_method_id)->name,
                        $payment->total,
                        TimeUnit::find($payment->time_unit_id)->name,
                        $payment->term
                    ]));
                }
                $payments = json_encode($paymentsArray, JSON_UNESCAPED_UNICODE);
            }
            $voucherBase = [
                'company_ruc' => $voucher->emissionPoint->branch->company->ruc,
                'company_social_reason' => $voucher->emissionPoint->branch->company->social_reason,
                'company_tradename' => $voucher->emissionPoint->branch->company->tradename,
                'branch_establishment' => str_pad(strval($voucher->emissionPoint->branch->establishment), 3, '0', STR_PAD_LEFT),
                'emission_point_code' => str_pad(strval($voucher->emissionPoint->code), 3, '0', STR_PAD_LEFT),
                'user_name' => $voucher->user->name,
                'user_email' => $voucher->user->email,
                'customer_identification_type' => $voucher->customer->identificationType->name,
                'customer_identification' => $voucher->customer->identification,
                'customer_social_reason' => $voucher->customer->social_reason,
                'customer_address' => $voucher->customer->address,
                'customer_phone' => $voucher->customer->phone,
                'customer_email' => $voucher->customer->email,
                'voucher_number' => str_pad(strval($voucher->emissionPoint->branch->establishment), 3, '0', STR_PAD_LEFT) . '-' .
                    str_pad(strval($voucher->emissionPoint->code), 3, '0', STR_PAD_LEFT) . '-' .
                    str_pad(strval($voucher->sequential), 9, '0', STR_PAD_LEFT),
                'voucher_environment' => $voucher->environment->name,
                'voucher_state' => __(VoucherState::find($voucher->voucher_state_id)->name),
                'voucher_type' => VoucherType::find($voucher->voucher_type_id)->name,
                'voucher_currency' => $voucher->currency->name,
                'voucher_issue_date' => $voucher->issue_date,
                'voucher_authorization_date' => $voucher->authorization_date,
                'voucher_access_key' => $voucher->accessKey(),
                'voucher_tip' => $voucher->tip,
                'voucher_iva_retention' => $voucher->iva_retention,
                'voucher_rent_retention' => $voucher->rent_retention,
                'voucher_support_document' => $voucher->support_document,
                'voucher_support_document_date' => $voucher->support_document_date,
                'voucher_additional_fields' => $additionalFields,
                'voucher_payments' => $payments,

                'product_main_code' => NULL,
                'product_auxiliary_code' => NULL,
                'product_description' => NULL,
                'product_additional_detail1' => NULL,
                'product_additional_detail2' => NULL,
                'product_additional_detail3' => NULL,
                'product_quantity' => NULL,
                'product_unit_price' => NULL,
                'product_discount' => NULL,
                'product_tax_description' => NULL,
                'product_tax_rate' => NULL,
                'product_tax_base' => NULL,
                'product_tax_value' => NULL,

                'credit_reason' => $voucher->creditNotes()->exists() ? $voucher->creditNotes()->first()->reason : NULL,

                'debit_note_tax_description' => $voucher->debitNotesTaxes()->exists() ? IvaTax::where('auxiliary_code', '=', $voucher->debitNotesTaxes()->first()->percentage_code)->first()->description : NULL,
                'debit_note_tax_rate' => $voucher->debitNotesTaxes()->exists() ? $voucher->debitNotesTaxes()->first()->rate : NULL,
                'debit_note_tax_base' => $voucher->debitNotesTaxes()->exists() ? $voucher->debitNotesTaxes()->first()->tax_base : NULL,
                'debit_note_reason' => NULL,
                'debit_note_value' => NULL,

                'waybill_carrier_identification_type' => $voucher->waybills()->exists() ? IdentificationType::find($voucher->waybills()->first()->identification_type_id)->name : NULL,
                'waybill_carrier_identification' => $voucher->waybills()->exists() ? $voucher->waybills()->first()->carrier_ruc : NULL,
                'waybill_carrier_social_reason' => $voucher->waybills()->exists() ? $voucher->waybills()->first()->carrier_social_reason : NULL,
                'waybill_starting_address' => $voucher->waybills()->exists() ? $voucher->waybills()->first()->starting_address : NULL,
                'waybill_start_date_transport' => $voucher->waybills()->exists() ? $voucher->waybills()->first()->start_date_transport : NULL,
                'waybill_end_date_transport' => $voucher->waybills()->exists() ? $voucher->waybills()->first()->end_date_transport : NULL,
                'waybill_licence_plate' => $voucher->waybills()->exists() ? $voucher->waybills()->first()->licence_plate : NULL,
                'addressee_address' => $voucher->waybills()->exists() ? ($voucher->waybills()->first()->addressees()->exists() ? $voucher->waybills()->first()->addressees()->first()->address : NULL) : NULL,
                'addressee_transfer_reason' => $voucher->waybills()->exists() ? ($voucher->waybills()->first()->addressees()->exists() ? $voucher->waybills()->first()->addressees()->first()->transfer_reason : NULL) : NULL,
                'addressee_single_customs_document' => $voucher->waybills()->exists() ? ($voucher->waybills()->first()->addressees()->exists() ? $voucher->waybills()->first()->addressees()->first()->single_customs_doc : NULL) : NULL,
                'addressee_destination_establishment_code' => $voucher->waybills()->exists() ? ($voucher->waybills()->first()->addressees()->exists() ? $voucher->waybills()->first()->addressees()->first()->destination_establishment_code : NULL) : NULL,
                'addressee_route' => $voucher->waybills()->exists() ? ($voucher->waybills()->first()->addressees()->exists() ? $voucher->waybills()->first()->addressees()->first()->route : NULL) : NULL,
                'addressee_support_document' => $voucher->waybills()->exists() ? ($voucher->waybills()->first()->addressees()->exists() ? $voucher->waybills()->first()->addressees()->first()->support_doc_code : NULL) : NULL,

                'retention_tax' => NULL,
                'retention_description' => NULL,
                'retention_rate' => NULL,
                'retention_base' => NULL,
                'retention_value' => NULL,
                'retention_fiscal_period' => $voucher->retentions()->exists() ? $voucher->retentions()->first()->fiscal_period : NULL,
                'retention_support_document' => NULL,
            ];
            switch ($voucher->voucher_type_id) {
                case 1:
                    foreach ($voucher->details()->get() as $detail) {
                        $voucherInvoice = (new \ArrayObject($voucherBase))->getArrayCopy();
                        $voucherInvoice['product_main_code'] = $detail->product->main_code;
                        $voucherInvoice['product_auxiliary_code'] = $detail->product->auxiliary_code;
                        $voucherInvoice['product_description'] = $detail->product->description;
                        for ($i = 0; $i < 3 && $i < $detail->additionalDetails()->get()->count(); $i++) {
                            $voucherInvoice['product_additional_detail' . ($i + 1)] = $detail->additionalDetails()->get()[$i]->value;
                        }
                        $voucherInvoice['product_quantity'] = $detail->quantity;
                        $voucherInvoice['product_unit_price'] = $detail->unit_price;
                        $voucherInvoice['product_discount'] = $detail->discount;
                        $voucherInvoice['product_tax_description'] = IvaTax::where('auxiliary_code', '=', $detail->taxDetails()->first()->percentage_code)->first()->description;
                        $voucherInvoice['product_tax_rate'] = $detail->taxDetails()->first()->rate;
                        $voucherInvoice['product_tax_base'] = $detail->taxDetails()->first()->tax_base;
                        $voucherInvoice['product_tax_value'] = $detail->taxDetails()->first()->value;
                        $voucherCollection->push($voucherInvoice);
                    }
                    break;
                case 2:
                    foreach ($voucher->details()->get() as $detail) {
                        $voucherCreditNote = (new \ArrayObject($voucherBase))->getArrayCopy();
                        $voucherCreditNote['product_main_code'] = $detail->product->main_code;
                        $voucherCreditNote['product_auxiliary_code'] = $detail->product->auxiliary_code;
                        $voucherCreditNote['product_description'] = $detail->product->description;
                        for ($i = 0; $i < 3 && $i < $detail->additionalDetails()->get()->count(); $i++) {
                            $voucherCreditNote['product_additional_detail' . ($i + 1)] = $detail->additionalDetails()->get()[$i]->value;
                        }
                        $voucherCreditNote['product_quantity'] = $detail->quantity;
                        $voucherCreditNote['product_unit_price'] = $detail->unit_price;
                        $voucherCreditNote['product_discount'] = $detail->discount;
                        $voucherCreditNote['product_tax_description'] = IvaTax::where('auxiliary_code', '=', $detail->taxDetails()->first()->percentage_code)->first()->description;
                        $voucherCreditNote['product_tax_rate'] = $detail->taxDetails()->first()->rate;
                        $voucherCreditNote['product_tax_base'] = $detail->taxDetails()->first()->tax_base;
                        $voucherCreditNote['product_tax_value'] = $detail->taxDetails()->first()->value;
                        $voucherCollection->push($voucherCreditNote);
                    }
                    break;
                case 3:
                    foreach ($voucher->debitNotesTaxes()->first()->debitNotes()->get() as $debitNote) {
                        $voucherDebitNote = (new \ArrayObject($voucherBase))->getArrayCopy();
                        $voucherDebitNote['debit_note_reason'] = $debitNote->reason;
                        $voucherDebitNote['debit_note_value'] = $debitNote->value;
                        $voucherCollection->push($voucherDebitNote);
                    }
                    break;
                case 4:
                    foreach ($voucher->waybills()->first()->addressees()->first()->details()->get() as $detailAddressee) {
                        $voucherWaybill = (new \ArrayObject($voucherBase))->getArrayCopy();
                        $voucherWaybill['product_main_code'] = $detailAddressee->product->main_code;
                        $voucherWaybill['product_auxiliary_code'] = $detailAddressee->product->auxiliary_code;
                        $voucherWaybill['product_description'] = $detailAddressee->product->description;
                        for ($i = 0; $i < 3 && $i < $detailAddressee->additionalDetails()->get()->count(); $i++) {
                            $voucherWaybill['product_additional_detail' . ($i + 1)] = $detailAddressee->additionalDetails()->get()[$i]->value;
                        }
                        $voucherWaybill['product_quantity'] = $detailAddressee->quantity;
                        $voucherCollection->push($voucherWaybill);
                    }
                    break;
                case 5:
                    foreach ($voucher->retentions()->first()->details()->get() as $retentionDetail) {
                        $voucherRetention = (new \ArrayObject($voucherBase))->getArrayCopy();
                        $voucherRetention['retention_tax'] = RetentionTax::find(RetentionTaxDescription::find($retentionDetail->retention_tax_description_id)->retention_tax_id)->tax;
                        $voucherRetention['retention_description'] = RetentionTaxDescription::find($retentionDetail->retention_tax_description_id)->description;
                        $voucherRetention['retention_rate'] = $retentionDetail->rate;
                        $voucherRetention['retention_base'] = $retentionDetail->tax_base;
                        $voucherRetention['retention_value'] = $retentionDetail->tax_base * $retentionDetail->rate / 100.0;
                        $voucherRetention['retention_support_document'] = $retentionDetail->support_doc_code;
                        $voucherCollection->push($voucherRetention);
                    }
                    break;
            }
        }
        return $voucherCollection;
    }

    public static function download(User $user, Request $filter, $type)
    {
        $vouchers = self::getFilteredVouchersAllowedToUserQueryBuilder($user, $filter)->get();
        $contentType = 'text/plain';
        $headers = [
            'Content-Type' => $contentType,
            'Cache-Control' => 'no-cache, private',
            'File-Name' => 'eireport_' . round((microtime(true) * 1000)) . '.' . $type
        ];
        switch ($type) {
            case 'csv':
                $contentType = 'application/csv';
                $voucherCollection = self::getVouchersCollection($vouchers);
                return response()->stream(function () use ($voucherCollection) {
                    $writer = Writer::createFromStream(fopen('php://output', 'w'));
                    $csvExporter = new Export($writer);
                    $csvExporter->build($voucherCollection, [
                        'company_ruc' => 'COMPANY RUC',
                        'company_social_reason' => 'COMPANY SOCIAL REASON',
                        'company_tradename' => 'COMPANY TRADENAME',
                        'branch_establishment' => 'BRANCH ESTABLISHMENT',
                        'emission_point_code' => 'EMISSION POINT',
                        'user_name' => 'USER NAME',
                        'user_email' => 'USER EMAIL',
                        'customer_identification_type' => 'CUSTOMER IDENTIFICATION TYPE',
                        'customer_identification' => 'CUSTOMER IDENTIFICATION',
                        'customer_social_reason' => 'CUSTOMER SOCIAL REASON',
                        'customer_address' => 'CUSTOMER ADDRESS',
                        'customer_phone' => 'CUSTOMER PHONE',
                        'customer_email' => 'CUSTOMER EMAIL',
                        'voucher_number' => 'VOUCHER NUMBER',
                        'voucher_environment' => 'VOUCHER ENVIRONMENT',
                        'voucher_state' => 'VOUCHER STATE',
                        'voucher_type' => 'VOUCHER TYPE',
                        'voucher_currency' => 'VOUCHER CURRENCY',
                        'voucher_issue_date' => 'VOUCHER ISSUE DATE',
                        'voucher_authorization_date' => 'VOUCHER AUTHORIZATION DATE',
                        'voucher_access_key' => 'ACCESS KEY',
                        'voucher_tip' => 'VOUCHER TIP',
                        'voucher_iva_retention' => 'VOUCHER IVA RETENTION',
                        'voucher_rent_retention' => 'VOUCHER RENT RETENTION',
                        'voucher_support_document' => 'VOUCHER SUPPORT DOCUMENT',
                        'voucher_support_document_date' => 'VOUCHER SUPPORT DOCUMENT DATE',
                        'voucher_additional_fields' => 'VOUCHER ADDITIONAL FIELDS',
                        'voucher_payments' => 'VOUCHER PAYMENTS',
                        'product_main_code' => 'PRODUCT MAIN CODE',
                        'product_auxiliary_code' => 'PRODUCT AUXILIARY CODE',
                        'product_description' => 'PRODUCT DESCRIPTION',
                        'product_additional_detail1' => 'PRODUCT ADDITIONAL DETAIL',
                        'product_additional_detail2' => 'PRODUCT ADDITIONAL DETAIL',
                        'product_additional_detail3' => 'PRODUCT ADDITIONAL DETAIL',
                        'product_quantity' => 'PRODUCT QUANTITY',
                        'product_unit_price' => 'PRODUCT UNIT PRICE',
                        'product_discount' => 'PRODUCT DISCOUNT',
                        'product_tax_description' => 'PRODUCT TAX DESCRIPTION',
                        'product_tax_rate' => 'PRODUCT TAX RATE',
                        'product_tax_base' => 'PRODUCT TAX BASE',
                        'product_tax_value' => 'PRODUCT TAX VALUE',
                        'credit_reason' => 'CREDIT REASON',
                        'debit_note_tax_description' => 'DEBIT NOTE TAX DESCRIPTION',
                        'debit_note_tax_rate' => 'DEBIT NOTE TAX RATE',
                        'debit_note_tax_base' => 'DEBIT NOTE TAX BASE',
                        'debit_note_tax_value' => 'DEBIT NOTE TAX VALUE',
                        'debit_note_reason' => 'DEBIT NOTE REASON',
                        'waybill_carrier_identification_type' => 'WAYBILL CARRIER IDENTIFICATION TYPE',
                        'waybill_carrier_identification' => 'WAYBILL CARRIER IDENTIFICATION',
                        'waybill_carrier_social_reason' => 'WAYBILL CARRIER SOCIAL REASON',
                        'waybill_starting_address' => 'WAYBILL STARTING ADDRESS',
                        'waybill_start_date_transport' => 'WAYBILL START DATE TRANSPORT',
                        'waybill_end_date_transport' => 'WAYBILL END DATE TRANSPORT',
                        'waybill_licence_plate' => 'WAYBILL LICENCE PLATE',
                        'addressee_address' => 'ADDRESSEE ADDRESS',
                        'addressee_transfer_reason' => 'ADDRESSEE TRANSFER REASON',
                        'addressee_single_customs_document' => 'ADDRESSEE SINGLE CUSTOMS DOC',
                        'addressee_destination_establishment_code' => 'ADDRESSEE DESTINATION ESTABLISHMENT',
                        'addressee_route' => 'ADDRESSEE ROUTE',
                        'addressee_support_document' => 'ADDRESSEE SUPPORT DOCUMENT',
                        'retention_tax' => 'RETENTION TAX',
                        'retention_description' => 'RETENTION DESCRIPTION',
                        'retention_rate' => 'RETENTION RATE',
                        'retention_base' => 'RETENTION BASE',
                        'retention_value' => 'RETENTION VALUE',
                        'retention_fiscal_period' => 'RETENTION FISCAL PERIOD',
                        'retention_support_document' => 'RETENTION SUPPORT DOCUMENT',
                    ]);
                }, 200, $headers);
                break;
            case 'xls':
                $contentType = 'application/vnd.ms-excel';
                $voucherCollection = self::getVouchersCollection($vouchers);
                return Excel::download(new VouchersExport($voucherCollection), 'vouchers.xlsx', NULL, $headers);
                break;
            case 'zip':
                $contentType = 'application/zip';
                $zipper = new Zipper;
                $zipper->make('vouchers.zip');
                foreach ($vouchers as $voucher) {
                    if ($voucher->xml !== NULL) {
                        $zipper->add(storage_path('app/' . $voucher->xml));
                    }
                    $html = false;
                    PDF::loadView('vouchers.ride.' . $voucher->getViewType(), compact(['voucher', 'html']))->save($headers['File-Name'] . '/' . $voucher->accessKey() . '.pdf');
                }
                if (File::exists($headers['File-Name'] . '/')) {
                    $zipper->add($headers['File-Name'] . '/');
                    $zipper->close();
                    File::deleteDirectory($headers['File-Name'] . '/');
                }
                return response()->download('vouchers.zip', 'vouchers.zip', $headers)->deleteFileAfterSend();
                break;
        }
    }
}
