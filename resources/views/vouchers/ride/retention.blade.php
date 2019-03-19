@if(view()->exists('vouchers.ride.' . $voucher->emissionPoint->branch->company->ruc . '.retention'))
    @include('vouchers.ride.' . $voucher->emissionPoint->branch->company->ruc . '.retention')
@else
    @include('vouchers.ride.default.retention')
@endif
