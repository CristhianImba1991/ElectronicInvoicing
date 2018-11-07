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
        if (id != '') {
            $.ajax({
                url: "{{ route('products.taxes') }}",
                method: "POST",
                data: {
                    _token: _token,
                    id: id,
                },
                success: function(result) {
                    var taxes = JSON.parse(result);
                    var productQuantity = elementChanged == '' ? 1.0 : (elementChanged == 'productQuantity' ? Number(reference.val()) : Number(reference.closest('tr').find('input[id *= product-quantity]').val()));
                    productQuantity = isNaN(productQuantity) ? 1.0 : productQuantity;
                    var productUnitPrice = elementChanged == '' ? Number(taxes[0]['product']['unit_price']) : (elementChanged == 'productUnitPrice' ? Number(reference.val()) : Number(reference.closest('tr').find('input[id *= product-unitprice]').val()));
                    productUnitPrice = isNaN(productUnitPrice) ? taxes[0]['product']['unit_price'] : productUnitPrice;
                    var productIva = elementChanged == '' ? (taxes[0]['iva'] != null ? Number(taxes[0]['iva']['rate']) : 0.0) : Number(reference.closest('tr').find('input[id *= product-iva]').val());
                    productIva = isNaN(productIva) ? (taxes[0]['iva'] != null ? Number(taxes[0]['iva']['rate']) : 0.0) : productIva;
                    var productIce = taxes[0]['ice'] != null ? Number(taxes[0]['ice']['specific_rate']) + Number(taxes[0]['ice']['ad_valorem_rate']) : 0.0;
                    var productDiscount = elementChanged == '' ? 0.0 : (elementChanged == 'productDiscount' ? Number(reference.val()) : Number(reference.closest('tr').find('input[id *= product-discount]').val()));
                    productDiscount = isNaN(productDiscount) ? 0.0 : productDiscount;
                    var productSubtotal = (productQuantity * productUnitPrice - productDiscount) * (1 + productIva / 100.0) * (1 + productIce / 100.0);
                    reference.closest('tr').find('input[id *= product-description]').val(taxes[0]['product']['description']);
                    reference.closest('tr').find('input[id *= product-quantity]').val(productQuantity.toFixed(Math.floor(productQuantity) !== productQuantity ? (productQuantity.toString().split(".")[1].length <= 2 ? 2 : 6) : 2));
                    reference.closest('tr').find('input[id *= product-unitprice]').val(productUnitPrice.toFixed(Math.floor(productUnitPrice) !== productUnitPrice ? (productUnitPrice.toString().split(".")[1].length <= 2 ? 2 : 6) : 2));
                    reference.closest('tr').find('input[id *= product-iva]').val(productIva.toFixed(2));
                    reference.closest('tr').find('input[id *= product-discount]').val(productDiscount.toFixed(2));
                    reference.closest('tr').find('input[id *= product-subtotal]').val(productSubtotal.toFixed(2));
                    updateTotal();
                }
            });
        }
    }
    function updateTotal() {
        var details = $('select[id *= product]');
        var _token = $('input[name = "_token"]').val();
        var id = $.map(details, function(option) {
            return option.value;
        });
        var quantities = $.map($('input[id *= product-quantity]'), function(option) {
            return Number(option.value);
        });
        var unitPrices = $.map($('input[id *= product-unitprice]'), function(option) {
            return Number(option.value);
        });
        var discounts = $.map($('input[id *= product-discount]'), function(option) {
            return Number(option.value);
        });
        $.ajax({
            url: "{{ route('products.taxes') }}",
            method: "POST",
            data: {
                _token: _token,
                id: id,
            },
            success: function(result) {
                const arrayToObject = (array) => array.reduce((object, item) => {
                    object[item.id] = item
                    return object
                }, {});
                var products = arrayToObject(JSON.parse(result));
                var ivaSubtotal = 0.0;
                var iva0Subtotal = 0.0;
                var notSubjectIvaSubtotal = 0.0;
                var exemptIvaSubtotal = 0.0;
                var iceValue = 0.0;
                var irbpnrValue = 0.0;
                var ivaValue = 0.0;
                for (var i = 0; i < id.length; i++) {
                    if (id[i] != "") {
                        if (products[id[i]]['iva'] != null) {
                            switch (products[id[i]]['iva']['auxiliary_code']) {
                                case 0: iva0Subtotal += quantities[i] * unitPrices[i] - discounts[i]; break;
                                case 2:
                                    ivaSubtotal += quantities[i] * unitPrices[i] - discounts[i];
                                    ivaValue += (quantities[i] * unitPrices[i] - discounts[i]) * Number(products[id[i]]['iva']['rate']) / 100.0;
                                    break;
                                case 3:
                                    ivaSubtotal += quantities[i] * unitPrices[i] - discounts[i];
                                    ivaValue += (quantities[i] * unitPrices[i] - discounts[i]) * Number(products[id[i]]['iva']['rate']) / 100.0;
                                    break;
                                case 6: notSubjectIvaSubtotal += quantities[i] * unitPrices[i] - discounts[i]; break;
                                case 7: exemptIvaSubtotal += quantities[i] * unitPrices[i] - discounts[i]; break;
                            }
                        }
                    }
                }
                var subtotal = iva0Subtotal + ivaSubtotal + notSubjectIvaSubtotal + exemptIvaSubtotal;
                var totalDiscount = discounts.reduce(function(a, b) {
                    return a + b;
                }, 0.0);
                var tip = Number($('#tip').val());
                tip = isNaN(tip) ? 0.0 : tip;
                var total = subtotal + iceValue + irbpnrValue + ivaValue + tip;
                $('#iva0subtotal').val(iva0Subtotal.toFixed(2));
                $('#ivasubtotal').val(ivaSubtotal.toFixed(2));
                $('#notsubjectivasubtotal').val(notSubjectIvaSubtotal.toFixed(2));
                $('#exemptivasubtotal').val(exemptIvaSubtotal.toFixed(2));
                $('#subtotal').val(subtotal.toFixed(2));
                $('#totaldiscount').val(totalDiscount.toFixed(2));
                //$('#icevalue').val(iceValue.toFixed(2));
                //$('#irbpnrvalue').val(irbpnrValue.toFixed(2));
                $('#ivavalue').val(ivaValue.toFixed(2));
                $('#tip').val(tip.toFixed(2));
                $('#total').val(total.toFixed(2));
            }
        });
    }
    $('#invoice-table tbody').on('changed.bs.select', 'select[id *= product]', function(){
        loadProductData($(this), '');
    });
    $('#invoice-table tbody').on('change', 'input[id *= product-quantity]', function(){
        loadProductData($(this), 'productQuantity');
    });
    $('#invoice-table tbody').on('change', 'input[id *= product-unitprice]', function(){
        loadProductData($(this), 'productUnitPrice');
    });
    $('#invoice-table tbody').on('change', 'input[id *= product-discount]', function(){
        loadProductData($(this), 'productDiscount');
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
    $('#tip').change(function() {
        updateTotal();
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
                        <td><input class="form-control" type="text" id="notsubjectivasubtotal" name="notsubjectivasubtotal" value="" readonly></td>
                    </tr>
                    <tr>
                        <td>Exempt from IVA subtotal</td>
                        <td><input class="form-control" type="text" id="exemptivasubtotal" name="exemptivasubtotal" value="" readonly></td>
                    </tr>
                    <tr>
                        <td>Subtotal</td>
                        <td><input class="form-control" type="text" id="subtotal" name="subtotal" value="" readonly></td>
                    </tr>
                    <tr>
                        <td>Total discount</td>
                        <td><input class="form-control" type="text" id="totaldiscount" name="totaldiscount" value="" readonly></td>
                    </tr>
                    <!--<tr>
                        <td>ICE value</td>
                        <td><input class="form-control" type="text" id="icevalue" name="icevalue" value="" readonly></td>
                    </tr>
                    <tr>
                        <td>IRBPNR value</td>
                        <td><input class="form-control" type="text" id="irbpnrvalue" name="irbpnrvalue" value="" readonly></td>
                    </tr>-->
                    <tr>
                        <td>IVA 12% value</td>
                        <td><input class="form-control" type="text" id="ivavalue" name="ivavalue" value="" readonly></td>
                    </tr>
                    <tr>
                        <td>Tip</td>
                        <td><input class="form-control" type="text" id="tip" name="tip" value="0.0"></td>
                    </tr>
                    <tr>
                        <td>Total</td>
                        <td><input class="form-control" type="text" id="total" name="total" value="" readonly></td>
                    </tr>
                </tbody>
            </table>
        </div>
    </div>
</div>
