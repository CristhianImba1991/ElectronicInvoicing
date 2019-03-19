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
      </tbody>
    </table>
</div>
<div class="card border-dark mb-3">
    <table class="table table-sm">
        <thead>
            <tr>
                <th class="align-bottom"><center>RAZÓN DE LA MODIFICACIÓN</center></th>
                <th class="align-bottom"><center>VALOR DE LA MODIFICACIÓN</center></th>
            </tr>
        </thead>
      <tbody>
          @foreach($voucher->debitNotesTaxes()->first()->debitNotes()->get() as $reason)
              <tr>
                <td class="align-middle">{{ $reason->reason }}</td>
                <td class="text-right align-middle">{{ number_format($reason->value, 2, '.', '') }}</td>
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
