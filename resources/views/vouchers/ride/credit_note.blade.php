@if(view()->exists('vouchers.ride.' . $voucher->emissionPoint->branch->company->ruc . '.credit_note'))
    @include('vouchers.ride.' . $voucher->emissionPoint->branch->company->ruc . '.credit_note')
@else
    @include('vouchers.ride.default.credit_note')
@endif
