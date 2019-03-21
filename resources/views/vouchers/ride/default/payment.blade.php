<div style="padding: 2px 2px 0px 2px" class="card border-dark mb-3">
    <table class="table table-sm">
        <thead>
            <th class="align-middle">FORMA DE PAGO</th>
            <th class="align-middle">VALOR</th>
        </thead>
      <tbody>
        @foreach ($voucher->payments as $payment)
            <tr>
              <td class="align-middle">{{ \ElectronicInvoicing\PaymentMethod::find($payment->payment_method_id)->name }}</td>
              <td class="align-middle">{{ number_format($payment->total, 2, '.', '') }}</td>
            </tr>
        @endforeach
      </tbody>
    </table>
</div>
