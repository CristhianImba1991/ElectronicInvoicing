<div style="padding: 2px 2px 0px 2px" class="card border-dark">
    <table class="table table-sm">
      <tbody>
        <tr>
          <td class="align-middle">SUBTOTAL SIN IMPUESTOS</td>
          <td class="text-right align-middle">{{ number_format($voucher->subtotalWithoutTaxes(), 2, '.', '') }}</td>
        </tr>
        @if($voucher->totalDiscounts() !== NULL)
            <tr>
              <td class="align-middle">DESCUENTO</td>
              <td class="text-right align-middle">{{ number_format($voucher->totalDiscounts(), 2, '.', '') }}</td>
            </tr>
        @endif
        <tr>
          <td class="align-middle">IVA 12%</td>
          <td class="text-right align-middle">{{ number_format($voucher->iva(), 2, '.', '') }}</td>
        </tr>
        <tr>
          <td class="align-middle">TOTAL</td>
          <td class="text-right align-middle">{{ number_format($voucher->total(), 2, '.', '') }}</td>
        </tr>
      </tbody>
    </table>
</div>
