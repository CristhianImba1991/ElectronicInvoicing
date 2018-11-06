<script type="text/javascript">
$(document).ready(function(){
    var invoiceTable = $('#invoice-table').DataTable({
        paging: false,
        dom: 'Bfrtip',
        buttons: [{
            text: 'Add row',
            action: function(e, dt, node, config){
                var _token = $('input[name = "_token"]').val();
                var id = $("#branch").val();
                $.ajax({
                    url: "{{ route('branches.products') }}",
                    method: "POST",
                    data: {
                        _token: _token,
                        id: id,
                    },
                    success: function(result) {
                        var options = '';
                        var products = JSON.parse(result);
                        for (var i = 0; i < products.length; i++) {
                            options += '<option value="' + products[i]['id'] + '">' + products[i]['main_code'] + '</option>';
                        }
                        invoiceTable.row.add([
                            '<select class="form-control selectpicker" id="product[]" name="product[]" data-live-search="true" title="Select a product ...">' + options + '</select>',
                            '<input class="form-control" type="text" id="product-description[]" name="product-description[]" value="" readonly>',
                            '<input class="form-control" type="text" id="product-quantity[]" name="product-quantity[]" value="">',
                            '<input class="form-control" type="text" id="product-unitprice[]" name="product-unitprice[]" value="">',
                            '<input class="form-control" type="text" id="product-iva[]" name="product-iva[]" value="" readonly>',
                            '<input class="form-control" type="text" id="product-discount[]" name="product-discount[]" value="">',
                            '<input class="form-control" type="text" id="product-subtotal[]" name="product-subtotal[]" value="" readonly>',
                            '<button type="button" class="btn btn-danger btn-sm"><strong>X</strong></button>',
                        ]).draw(false);
                        $('select[id *= product]').selectpicker();
                    }
                });
            }
        }]
    });
    function loadProductData(reference, elementChanged) {
        var _token = $('input[name = "_token"]').val();
        var id = reference.closest('tr').find('select[id *= product]').val();
        $.ajax({
            url: "{{ route('products.taxes') }}",
            method: "POST",
            data: {
                _token: _token,
                id: id,
            },
            success: function(result) {
                var taxes = JSON.parse(result);
                var quantity = elementChanged == '' ? 1.0 : (elementChanged == 'quantity' ? Number(reference.val()) : Number(reference.closest('tr').find('input[id *= product-quantity]').val()));
                quantity = isNaN(quantity) ? 1.0 : quantity;
                var unitprice = elementChanged == '' ? Number(taxes[0]['product']['unit_price']) : (elementChanged == 'unitprice' ? Number(reference.val()) : Number(reference.closest('tr').find('input[id *= product-unitprice]').val()));
                unitprice = isNaN(unitprice) ? taxes[0]['product']['unit_price'] : unitprice;
                var iva = elementChanged == '' ? (taxes[0]['iva'] != null ? Number(taxes[0]['iva']['rate']) : 0.0) : Number(reference.closest('tr').find('input[id *= product-iva]').val());
                iva = isNaN(iva) ? (taxes[0]['iva'] != null ? Number(taxes[0]['iva']['rate']) : 0.0) : iva;
                var ice = taxes[0]['ice'] != null ? Number(taxes[0]['ice']['specific_rate']) + Number(taxes[0]['ice']['ad_valorem_rate']) : 0.0;
                var discount = elementChanged == '' ? 0.0 : (elementChanged == 'discount' ? Number(reference.val()) : Number(reference.closest('tr').find('input[id *= product-discount]').val()));
                discount = isNaN(discount) ? 0.0 : discount;
                var subtotal = (quantity * unitprice - discount) * (1 + iva / 100.0) * (1 + ice / 100.0)
                reference.closest('tr').find('input[id *= product-description]').val(taxes[0]['product']['description']);
                reference.closest('tr').find('input[id *= product-quantity]').val(quantity);
                reference.closest('tr').find('input[id *= product-unitprice]').val(unitprice);
                reference.closest('tr').find('input[id *= product-iva]').val(iva);
                reference.closest('tr').find('input[id *= product-discount]').val(discount);
                reference.closest('tr').find('input[id *= product-subtotal]').val(subtotal);
            }
        });
    }
    $('#invoice-table tbody').on('changed.bs.select', 'select[id *= product]', function(){
        loadProductData($(this), '');
    });
    $('#invoice-table tbody').on('change', 'input[id *= product-quantity]', function(){
        loadProductData($(this), 'quantity');
    });
    $('#invoice-table tbody').on('change', 'input[id *= product-unitprice]', function(){
        loadProductData($(this), 'unitprice');
    });
    $('#invoice-table tbody').on('change', 'input[id *= product-discount]', function(){
        loadProductData($(this), 'discount');
    });
    $('#invoice-table tbody').on('click', 'button.btn.btn-danger.btn-sm', function(){
        invoiceTable
            .row($(this).parents('tr'))
            .remove()
            .draw();
    });
    var paymentMethodTable = $('#paymentmethod-table').DataTable({
        paging: false,
        dom: 'Bfrtip',
        buttons: [{
            text: 'Add row',
            action: function(e, dt, node, config){
                var _token = $('input[name = "_token"]').val();
                $.ajax({
                    url: "{{ route('paymentmethods') }}",
                    method: "GET",
                    success: function(result) {
                        var optionsPaymentMethod = '';
                        var paymentMethods = JSON.parse(result);
                        for (var i = 0; i < paymentMethods.length; i++) {
                            optionsPaymentMethod += '<option value="' + paymentMethods[i]['id'] + '">' + paymentMethods[i]['name'] + '</option>';
                        }
                        $.ajax({
                            url: "{{ route('timeunits') }}",
                            method: "GET",
                            success: function(result) {
                                var optionsTimeUnit = '';
                                var timeunits = JSON.parse(result);
                                for (var i = 0; i < timeunits.length; i++) {
                                    optionsTimeUnit += '<option value="' + timeunits[i]['id'] + '">' + timeunits[i]['name'] + '</option>';
                                }
                                paymentMethodTable.row.add([
                                    '<select class="form-control selectpicker" id="paymentMethod[]" name="paymentMethod[]" data-live-search="true" title="Select a payment method ...">' + optionsPaymentMethod + '</select>',
                                    '<input class="form-control" type="text" id="paymentMethod-value[]" name="paymentMethod-value[]" value="">',
                                    '<select class="form-control selectpicker" id="paymentMethod-timeunit[]" name="paymentMethod-timeunit[]" data-live-search="true" title="Select a time unit ...">' + optionsTimeUnit + '</select>',
                                    '<input class="form-control" type="text" id="paymentMethod-term[]" name="paymentMethod-term[]" value="0">',
                                    '<button type="button" class="btn btn-danger btn-sm"><strong>X</strong></button>',
                                ]).draw(false);
                                $('select[id *= paymentMethod]').selectpicker('val', 1);
                                $('select[id *= paymentMethod-timeunit]').selectpicker('val', 1);
                            }
                        });
                    }
                });
            }
        }]
    });
    $('#paymentmethod-table tbody').on('click', 'button.btn.btn-danger.btn-sm', function(){
        paymentMethodTable
            .row($(this).parents('tr'))
            .remove()
            .draw();
    });
});
</script>
<div class="col-sm-12">
    <div class="card mb-3">
        <div class="card-body">
            <h5 class="card-title">5. Invoice</h5>
            <table id="invoice-table" class="display">
                <thead>
                    <tr>
                        <th>Product</th>
                        <th>Description</th>
                        <th>Quantity</th>
                        <th>U. price</th>
                        <th>IVA</th>
                        <th>Discount</th>
                        <th>Subtotal</th>
                        <th></th>
                    </tr>
                </thead>
            </table>
        </div>
    </div>
</div>
<div class="col-sm-12">
    <div class="card mb-3">
        <div class="card-body">
            <h5 class="card-title">6. Payment method</h5>
            <table id="paymentmethod-table" class="display">
                <thead>
                    <tr>
                        <th>Method</th>
                        <th>Value</th>
                        <th>Time</th>
                        <th>Term</th>
                        <th></th>
                    </tr>
                </thead>
            </table>
        </div>
    </div>
</div>
<div class="col-sm-12">
    <div class="card mb-3">
        <div class="card-body">
            <h5 class="card-title">7. Total</h5>
            <table class="table table-striped">
                <tbody>
                    <tr>
                        <td>IVA 12% subtotal</td>
                        <td><input class="form-control" type="text" id="ivasubtotal" name="ivasubtotal" value="" readonly></td>
                    </tr>
                    <tr>
                        <td>IVA 0% subtotal</td>
                        <td><input class="form-control" type="text" id="iva0subtotal" name="iva0subtotal" value="" readonly></td>
                    </tr>
                    <tr>
                        <td>Not subject to IVA subtotal</td>
                        <td><input class="form-control" type="text" id="notsubjectivasubtotal" name="iva0subtotal" value="" readonly></td>
                    </tr>
                    <tr>
                        <td>Exempt from IVA subtotal</td>
                        <td><input class="form-control" type="text" id="exemptivasubtotal" name="iva0subtotal" value="" readonly></td>
                    </tr>
                    <tr>
                        <td>Subtotal</td>
                        <td><input class="form-control" type="text" id="subtotal" name="iva0subtotal" value="" readonly></td>
                    </tr>
                    <tr>
                        <td>Total discount</td>
                        <td><input class="form-control" type="text" id="totaldiscount" name="iva0subtotal" value="" readonly></td>
                    </tr>
                    <tr>
                        <td>ICE value</td>
                        <td><input class="form-control" type="text" id="icevalue" name="iva0subtotal" value="" readonly></td>
                    </tr>
                    <tr>
                        <td>IRBPNR value</td>
                        <td><input class="form-control" type="text" id="irbpnrvalue" name="iva0subtotal" value="" readonly></td>
                    </tr>
                    <tr>
                        <td>IVA 12% value</td>
                        <td><input class="form-control" type="text" id="ivavalue" name="iva0subtotal" value="" readonly></td>
                    </tr>
                    <tr>
                        <td>Tip</td>
                        <td><input class="form-control" type="text" id="tip" name="iva0subtotal" value=""></td>
                    </tr>
                    <tr>
                        <td>Total</td>
                        <td><input class="form-control" type="text" id="total" name="iva0subtotal" value="" readonly></td>
                    </tr>
                </tbody>
            </table>
        </div>
    </div>
</div>
