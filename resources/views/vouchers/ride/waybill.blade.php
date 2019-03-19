@if(view()->exists('vouchers.ride.' . $voucher->emissionPoint->branch->company->ruc . '.waybill'))
    @include('vouchers.ride.' . $voucher->emissionPoint->branch->company->ruc . '.waybill')
@else
    @include('vouchers.ride.default.waybill')
@endif
