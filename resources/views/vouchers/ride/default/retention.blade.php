@extends('vouchers.ride.default.voucher')

@section('body')
<div class="card border-dark mb-3">
    <table class="table table-sm">
      <tbody>
        <tr>
          <td class="align-middle"><b>RAZÓN SOCIAL / NOMBRES Y APELLIDOS: </b>{{ $voucher->customer->social_reason }}</td>
          <td class="align-middle"><b>IDENTIFICACIÓN: </b>{{ $voucher->customer->identification }}</td>
        </tr>
        <tr>
          <td colspan="2" class="align-middle"><b>FECHA DE EMISIÓN: </b>{{ $voucher->issue_date }}</td>
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
                <th class="align-bottom"><center>Comprobante</center></th>
                <th class="align-bottom"><center>Número</center></th>
                <th class="align-bottom"><center>Fecha emisión</center></th>
                <th class="align-bottom"><center>Ejercicio fiscal</center></th>
                <th class="align-bottom"><center>Base imponible</center></th>
                <th class="align-bottom"><center>Impuesto</center></th>
                <th class="align-bottom"><center>Porcentaje retención</center></th>
                <th class="align-bottom"><center>Valor retenido</center></th>
            </tr>
        </thead>
      <tbody>
          @foreach($voucher->retentions->first()->details as $detail)
              <tr>
                <td class="align-middle">{{ \ElectronicInvoicing\VoucherType::where('code', '=', substr($detail->support_doc_code, 8, 2))->first()->name }}</td>
                <td class="align-middle">{{ substr($detail->support_doc_code, 10) }}</td>
                <td class="align-middle">{{ \DateTime::createFromFormat('dmY', substr($detail->support_doc_code, 0, 8))->format('d/m/Y') }}</td>
                <td class="text-center align-middle">{{ \DateTime::createFromFormat('Y-m-d', $voucher->retentions->first()->fiscal_period)->format('m/Y') }}</td>
                <td class="text-center align-middle">{{ number_format($detail->tax_base, 2, '.', '') }}</td>
                <td class="text-center align-middle">{{ \ElectronicInvoicing\RetentionTax::find(\ElectronicInvoicing\RetentionTaxDescription::find($detail->retention_tax_description_id)->retention_tax_id)->tax }}</td>
                <td class="text-center align-middle">{{ number_format($detail->value, 2, '.', '') }}</td>
                <td class="text-right align-middle">{{ number_format($detail->tax_base * $detail->value / 100.0, 2, '.', '') }}</td>
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
            <td>@include('vouchers.ride.default.additionalinformation')</td>
        </tr>
    </tbody>
</table>
@endsection
