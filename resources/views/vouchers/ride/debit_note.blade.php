@if(view()->exists('vouchers.ride.' . $voucher->emissionPoint->branch->company->ruc . '.debit_note'))
    @include('vouchers.ride.' . $voucher->emissionPoint->branch->company->ruc . '.debit_note')
@else
    @include('vouchers.ride.default.debit_note')
@endif
