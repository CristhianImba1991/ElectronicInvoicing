@extends('vouchers.ride.default.voucher')

@section('body')
<div style="padding: 2px 2px 0px 2px" class="card border-dark mb-3">
    <table class="table table-sm">
      <tbody>
        <tr>
          <td class="align-middle"><b>IDENTIFICACIÓN (TRANSPORTISTA): </b></td>
          <td class="align-middle" colspan="3">{{ $voucher->waybills()->first()->carrier_ruc }}</td>
        </tr>
        <tr>
          <td class="align-middle"><b>RAZÓN SOCIAL /  NOMBRES Y APELLIDOS: </b></td>
          <td class="align-middle" colspan="3">{{ $voucher->waybills()->first()->carrier_social_reason }}</td>
        </tr>
        <tr>
          <td class="align-middle" colspan="4"></td>
        </tr>
        <tr>
          <td class="align-middle"><b>PLACA: </b></td>
          <td class="align-middle" colspan="3">{{ $voucher->waybills()->first()->licence_plate }}</td>
        </tr>
        <tr>
          <td class="align-middle"><b>PUNTO DE PARTIDA: </b></td>
          <td class="align-middle" colspan="3">{{ $voucher->waybills()->first()->starting_address }}</td>
        </tr>
        <tr>
          <td class="align-middle"><b>FECHA INICIO TRANSPORTE: </b></td>
          <td class="align-middle">{{ \DateTime::createFromFormat('Y-m-d', $voucher->waybills()->first()->start_date_transport)->format('d/m/Y') }}</td>
          <td class="align-middle"><b>FECHA FIN TRANSPORTE: </b></td>
          <td class="align-middle">{{ \DateTime::createFromFormat('Y-m-d', $voucher->waybills()->first()->end_date_transport)->format('d/m/Y') }}</td>
        </tr>
      </tbody>
    </table>
</div>
<div style="padding: 2px 2px 0px 2px" class="card border-dark mb-3">
    <table class="table table-sm">
      <tbody>
        @foreach(\ElectronicInvoicing\Addressee::where('waybill_id', '=', $voucher->waybills()->first()->id)->get() as $addressee)
            <tr>
              <td class="align-middle"><b>COMPROBANTE QUE SE MODIFICA</b></td>
              <td class="align-middle"><b>FACTURA: </b>{{ substr($addressee->support_doc_code, 24, 3) . '-' . substr($addressee->support_doc_code, 27, 3) . '-' . substr($addressee->support_doc_code, 30, 9) }}</td>
              <td class="align-middle"><b>FECHA DE EMISIÓN: </b>{{ \DateTime::createFromFormat('dmY', substr($addressee->support_doc_code, 0, 8))->format('d/m/Y') }}</td>
            </tr>
            <tr>
              <td class="align-middle"><b>NÚMERO DE AUTORIZACIÓN: </b></td>
              <td class="align-middle" colspan="2">{{ $addressee->support_doc_code }}</td>
            </tr>
            <tr>
              <td class="align-middle" colspan="3"></td>
            </tr>
            <tr>
              <td class="align-middle"><b>MOTIVO TRASLADO: </b></td>
              <td class="align-middle" colspan="2">{{ $addressee->transfer_reason }}</td>
            </tr>
            <tr>
              <td class="align-middle"><b>DESTINO (PUNTO DE LLEGADA): </b></td>
              <td class="align-middle" colspan="2">{{ $addressee->address }}</td>
            </tr>
            <tr>
              <td class="align-middle"><b>IDENTIFICACIÓN (DESTINATARIO): </b></td>
              <td class="align-middle" colspan="2">{{ $addressee->customer->identification }}</td>
            </tr>
            <tr>
              <td class="align-middle"><b>RAZÓN SOCIAL / NOMBRES Y APELLIDOS: </b></td>
              <td class="align-middle" colspan="2">{{ $addressee->customer->social_reason }}</td>
            </tr>
            <tr>
              <td class="align-middle"><b>DOCUMENTO ADUANERO: </b></td>
              <td class="align-middle" colspan="2">{{ $addressee->single_customs_doc }}</td>
            </tr>
            <tr>
              <td class="align-middle"><b>CÓDIGO ESTABLECIMIENTO DESTINO: </b></td>
              <td class="align-middle" colspan="2">{{ str_pad(strval($addressee->destination_establishment_code), 3, '0', STR_PAD_LEFT) }}</td>
            </tr>
            <tr>
              <td class="align-middle"><b>RUTA: </b></td>
              <td class="align-middle" colspan="2">{{ $addressee->route }}</td>
            </tr>
            <tr>
              <td class="align-middle" colspan="3">
                <div class="card border-dark mb-3">
                    <table class="table table-sm">
                        <thead>
                            <tr>
                                <th class="align-bottom"><center>Cant.</center></th>
                                <th class="align-bottom"><center>Descripción</center></th>
                                <th class="align-bottom"><center>Cod. Principal</center></th>
                                <th class="align-bottom"><center>Cod. Auxiliar</center></th>
                            </tr>
                        </thead>
                      <tbody>
                          @foreach($addressee->details as $detail)
                          <tr>
                            <td class="align-middle">{{ $voucher->version() === '1.0.0' ? number_format($detail->quantity, 2, '.', '') : $detail->quantity }}</td>
                            <td class="align-middle">{{ $detail->product->description }}</td>
                            <td class="align-middle">{{ $detail->product->main_code }}</td>
                            <td class="align-middle">{{ $detail->product->auxiliary_code }}</td>
                          </tr>
                          @endforeach
                      </tbody>
                    </table>
                </div>
              </td>
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
