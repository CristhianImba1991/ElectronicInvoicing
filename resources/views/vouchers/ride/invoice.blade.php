@if(view()->exists('vouchers.ride.' . $voucher->emissionPoint->branch->company->ruc . '.invoice'))
    @include('vouchers.ride.' . $voucher->emissionPoint->branch->company->ruc . '.invoice')
@else
    @include('vouchers.ride.default.invoice')
@endif
