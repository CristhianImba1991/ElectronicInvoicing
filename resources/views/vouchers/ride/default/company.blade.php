<center><img src="{{ $html ? url('storage/logo/images/' . $voucher->emissionPoint->branch->company->logo) : public_path('storage/logo/images/' . $voucher->emissionPoint->branch->company->logo) }}" height="60%" alt="Company logo"></center>
<div style="padding: 2px 2px 0px 2px" class="card border-dark">
    <table class="table table-sm">
      <tbody>
        <tr>
          <th class="text-center align-middle" colspan="2">{{ $voucher->emissionPoint->branch->company->social_reason }}</th>
        </tr>
        <tr>
          <th class="align-middle" colspan="2">{{ $voucher->emissionPoint->branch->company->tradename }}</th>
        </tr>
        <tr>
          <th class="align-middle">DIRECCIÓN MATRIZ</th>
          <td class="align-middle">{{ $voucher->emissionPoint->branch->company->address }}</td>
        </tr>
        <tr>
          <th class="align-middle">DIRECCIÓN SUCURSAL</th>
          <td class="align-middle">{{ $voucher->emissionPoint->branch->address }}</td>
        </tr>
        @if($voucher->emissionPoint->branch->company->special_contributor !== NULL)
            <tr>
              <th class="align-middle">CONTRIBUYENTE ESPECIAL</th>
              <td class="align-middle">{{ $voucher->emissionPoint->branch->company->special_contributor }}</td>
            </tr>
        @endif
        <tr>
          <th class="align-middle">OBLIGADO A LLEVAR CONTABILIDAD</th>
          <td class="align-middle">{{ $voucher->emissionPoint->branch->company->keep_accounting ? 'SI' : 'NO' }}</td>
        </tr>
      </tbody>
    </table>
</div>
