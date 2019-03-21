<div style="padding: 2px 2px 0px 2px" class="card border-dark">
    <table class="table table-sm">
      <tbody>
          @php
            $ivaBreakdown = $voucher->ivaBreakdown();
          @endphp
        <tr>
          <td>SUBTOTAL IVA 12%</td>
          <td class="text-right">{{ number_format($ivaBreakdown['2'] + $ivaBreakdown['3'], 2, '.', '') }}</td>
        </tr>
        <tr>
          <td>SUBTOTAL IVA 0%</td>
          <td class="text-right">{{ number_format($ivaBreakdown['0'], 2, '.', '') }}</td>
        </tr>
        <tr>
          <td>SUBTOTAL NO OBJETO IVA</td>
          <td class="text-right">{{ number_format($ivaBreakdown['6'], 2, '.', '') }}</td>
        </tr>
        <tr>
          <td>SUBTOTAL EXENTO IVA</td>
          <td class="text-right">{{ number_format($ivaBreakdown['7'], 2, '.', '') }}</td>
        </tr>
        <tr>
          <td>SUBTOTAL SIN IMPUESTOS</td>
          <td class="text-right">{{ number_format($voucher->subtotalWithoutTaxes(), 2, '.', '') }}</td>
        </tr>
        @if($voucher->totalDiscounts() !== NULL)
            <tr>
              <td>DESCUENTO</td>
              <td class="text-right">{{ number_format($voucher->totalDiscounts(), 2, '.', '') }}</td>
            </tr>
        @endif
        <tr>
          <td>IVA 12%</td>
          <td class="text-right">{{ number_format($voucher->iva(), 2, '.', '') }}</td>
        </tr>
        @if($voucher->tip !== NULL)
            <tr>
              <td>PROPINA</td>
              <td class="text-right">{{ number_format($voucher->tip, 2, '.', '') }}</td>
            </tr>
        @endif
        <tr>
          <td>TOTAL</td>
          <td class="text-right">{{ number_format($voucher->total(), 2, '.', '') }}</td>
        </tr>
      </tbody>
    </table>
</div>
