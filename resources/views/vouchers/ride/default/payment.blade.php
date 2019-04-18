<div style="padding: 2px 2px 0px 2px" class="card border-dark mb-3">
    <table class="table table-sm">
        <thead>
            <th class="align-bottom">Forma de pago</th>
            <th class="align-bottom"><center>Valor</center></th>
            <th class="align-bottom"><center>Unidad de tiempo<center></th>
            <th class="align-bottom"><center>Plazo</center></th>
        </thead>
      <tbody>
        @foreach ($voucher->payments as $payment)
            <tr>
              <td class="align-middle">{{ \ElectronicInvoicing\PaymentMethod::find($payment->payment_method_id)->name }}</td>
              <td class="text-right align-middle">{{ number_format($payment->total, 2, '.', '') }}</td>
              <td class="text-center align-middle">{{ \ElectronicInvoicing\TimeUnit::find($payment->time_unit_id)->name }}</td>
              <td class="text-right align-middle">{{ $payment->term }}</td>
            </tr>
        @endforeach
      </tbody>
    </table>
</div>
