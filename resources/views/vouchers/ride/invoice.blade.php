@extends('vouchers.ride.voucher')

@section('body')
<div class="card border-dark mb-3">
    <table class="table table-sm">
      <tbody>
        <tr>
          <td class="align-middle"><b>RAZÓN SOCIAL / NOMBRES Y APELLIDOS: </b>{{ $voucher->customer->social_reason }}</td>
          <td class="align-middle"><b>IDENTIFICACIÓN: </b>{{ $voucher->customer->identification }}</td>
        </tr>
        <tr>
          <td class="align-middle"><b>FECHA DE EMISIÓN: </b>{{ $voucher->issue_date }}</td>
          <td class="align-middle"><b>GUÍA DE REMISIÓN: </b>{{ $voucher->support_document }}</td>
        </tr>
        <tr>
          <td class="align-middle" colspan="2"><b>DIRECCIÓN: </b>{{ $voucher->customer->address }}</td>
        </tr>
      </tbody>
    </table>
</div>
<div class="card border-dark mb-3">
    <table class="table table-sm">
        <thead>
            <tr>
                <th class="align-bottom"><center>Cod. Principal</center></th>
                <th class="align-bottom"><center>Cod. Auxiliar</center></th>
                <th class="align-bottom"><center>Cant.</center></th>
                <th class="align-bottom"><center>Descripción</center></th>
                <th class="align-bottom"><center>Precio Unitario</center></th>
                <th class="align-bottom"><center>Descuento</center></th>
                <th class="align-bottom"><center>Subtotal</center></th>
            </tr>
        </thead>
      <tbody>
          @php
              $version = false;
              foreach ($voucher->details as $detail) {
                  if (strlen(substr(strrchr(strval(floatval($detail->quantity)), "."), 1)) > 2 || strlen(substr(strrchr(strval(floatval($detail->unit_price)), "."), 1)) > 2) {
                      $version = true;
                      break;
                  }
              }
          @endphp
          @foreach($voucher->details as $detail)
              <tr>
                <td class="align-middle">{{ $detail->product->main_code }}</td>
                <td class="align-middle">{{ $detail->product->auxiliary_code }}</td>
                <td class="text-center align-middle">{{ $version ? $detail->quantity : number_format($detail->quantity, 2, '.', '') }}</td>
                <td class="align-middle">{{ $detail->product->description }}</td>
                <td class="text-right align-middle">{{ $version ? $detail->unit_price : number_format($detail->unit_price, 2, '.', '') }}</td>
                <td class="text-right align-middle">{{ number_format($detail->discount, 2, '.', '') }}</td>
                <td class="text-right align-middle">{{ $detail->quantity * $detail->unit_price - $detail->discount }}</td>
              </tr>
          @endforeach
      </tbody>
    </table>
</div>
@endsection

@section('footer')
<table class="table table-borderless">
    <tbody>
        <tr>
            <td>@include('vouchers.ride.additionalinformation') @include('vouchers.ride.payment')</td>
            <td>@include('vouchers.ride.total')</td>
        </tr>
    </tbody>
</table>
@endsection
