<?php

namespace ElectronicInvoicing\Providers;

use ElectronicInvoicing\Voucher;
use Illuminate\Support\ServiceProvider;
use Validator;

class UniqueSupportDocumentServiceProvider extends ServiceProvider
{
    /**
     * Bootstrap services.
     *
     * @return void
     */
    public function boot()
    {
        Validator::extend('uniquesupportdocument', function ($attribute, $value, $parameters, $validator) {
            return !Voucher::join('customers', 'customers.id', '=', 'vouchers.customer_id')
                ->join('retentions', 'vouchers.id', '=', 'retentions.voucher_id')
                ->join('retention_details', 'retentions.id', '=', 'retention_details.retention_id')
                ->where('vouchers.voucher_type_id', 5)
                ->whereNotIn('vouchers.voucher_state_id', [7, 10, 11])
                ->whereRaw('concat(customers.identification, retention_details.support_doc_code) = ?', [$value])
                ->exists();
        });
    }

    /**
     * Register services.
     *
     * @return void
     */
    public function register()
    {
        //
    }
}
