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
          <td class="align-middle" colspan="2"><b>FECHA DE EMISIÓN: </b>{{ $voucher->issue_date }}</td>
        </tr>
        <tr>
          <td class="align-middle" colspan="2"><hr></td>
        </tr>
        <tr>
          <td class="align-middle"><b>COMPROBANTE QUE SE MODIFICA</b></td>
          <td class="align-middle"><b>FACTURA: </b>{{ substr($voucher->support_document, 10, 3) . '-' . substr($voucher->support_document, 13, 3) . '-' . substr($voucher->support_document, 16, 9) }}</td>
        </tr>
        <tr>
          <td class="align-middle"><b>FECHA DE EMISIÓN (COMPROBANTE A MODIFICAR): </b></td>
          <td class="align-middle">{{ \DateTime::createFromFormat('Y-m-d', $voucher->issue_date)->format('d/m/Y') }}</td>
        </tr>
        <tr>
          <td class="align-middle" colspan="2"><b>RAZÓN: </b>{{ $voucher->creditNotes->first()->reason }}</td>
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
          @foreach(\ElectronicInvoicing\Detail::where('voucher_id', '=', $voucher->id)->get() as $detail)
              <tr>
                <td class="align-middle">{{ $detail->product->main_code }}</td>
                <td class="align-middle">{{ $detail->product->auxiliary_code }}</td>
                <td class="text-center align-middle">{{ $voucher->version() === '1.0.0' ? number_format($detail->quantity, 2, '.', '') : $detail->quantity }}</td>
                <td class="align-middle">{{ $detail->product->description }}</td>
                <td class="text-right align-middle">{{ $voucher->version() === '1.0.0' ? number_format($detail->unit_price, 2, '.', '') : $detail->unit_price }}</td>
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
            <td>@include('vouchers.ride.additionalinformation')</td>
            <td>@include('vouchers.ride.total')</td>
        </tr>
    </tbody>
</table>
@endsection
