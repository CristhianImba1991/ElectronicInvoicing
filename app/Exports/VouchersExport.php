<?php

namespace ElectronicInvoicing\Exports;

use ElectronicInvoicing\Voucher;
use Maatwebsite\Excel\Concerns\FromCollection;

class VouchersExport implements FromCollection
{
    public function __construct($voucherCorrection)
    {
        $this->voucherCollection = $voucherCorrection;
    }

    /**
    * @return \Illuminate\Support\Collection
    */
    public function collection()
    {
        $this->voucherCollection->prepend([
            'COMPANY RUC',
            'COMPANY SOCIAL REASON',
            'COMPANY TRADENAME',
            'BRANCH ESTABLISHMENT',
            'EMISSION POINT',
            'USER NAME',
            'USER EMAIL',
            'CUSTOMER IDENTIFICATION TYPE',
            'CUSTOMER IDENTIFICATION',
            'CUSTOMER SOCIAL REASON',
            'CUSTOMER ADDRESS',
            'CUSTOMER PHONE',
            'CUSTOMER EMAIL',
            'VOUCHER NUMBER',
            'VOUCHER ENVIRONMENT',
            'VOUCHER STATE',
            'VOUCHER TYPE',
            'VOUCHER CURRENCY',
            'VOUCHER ISSUE DATE',
            'VOUCHER AUTHORIZATION DATE',
            'ACCESS KEY',
            'VOUCHER TIP',
            'VOUCHER IVA RETENTION',
            'VOUCHER RENT RETENTION',
            'VOUCHER SUPPORT DOCUMENT',
            'VOUCHER SUPPORT DOCUMENT DATE',
            'VOUCHER ADDITIONAL FIELDS',
            'VOUCHER PAYMENTS',
            'PRODUCT MAIN CODE',
            'PRODUCT AUXILIARY CODE',
            'PRODUCT DESCRIPTION',
            'PRODUCT ADDITIONAL DETAIL',
            'PRODUCT ADDITIONAL DETAIL',
            'PRODUCT ADDITIONAL DETAIL',
            'PRODUCT QUANTITY',
            'PRODUCT UNIT PRICE',
            'PRODUCT DISCOUNT',
            'PRODUCT TAX DESCRIPTION',
            'PRODUCT TAX RATE',
            'PRODUCT TAX BASE',
            'PRODUCT TAX VALUE',
            'CREDIT REASON',
            'DEBIT NOTE TAX DESCRIPTION',
            'DEBIT NOTE TAX RATE',
            'DEBIT NOTE TAX BASE',
            'DEBIT NOTE TAX VALUE',
            'DEBIT NOTE REASON',
            'WAYBILL CARRIER IDENTIFICATION TYPE',
            'WAYBILL CARRIER IDENTIFICATION',
            'WAYBILL CARRIER SOCIAL REASON',
            'WAYBILL STARTING ADDRESS',
            'WAYBILL START DATE TRANSPORT',
            'WAYBILL END DATE TRANSPORT',
            'WAYBILL LICENCE PLATE',
            'ADDRESSEE ADDRESS',
            'ADDRESSEE TRANSFER REASON',
            'ADDRESSEE SINGLE CUSTOMS DOC',
            'ADDRESSEE DESTINATION ESTABLISHMENT',
            'ADDRESSEE ROUTE',
            'ADDRESSEE SUPPORT DOCUMENT',
            'RETENTION TAX',
            'RETENTION DESCRIPTION',
            'RETENTION RATE',
            'RETENTION BASE',
            'RETENTION VALUE',
            'RETENTION FISCAL PERIOD',
            'RETENTION SUPPORT DOCUMENT',
        ]);
        return $this->voucherCollection;
    }
}
