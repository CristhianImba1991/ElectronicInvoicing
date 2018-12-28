<script src="{{ asset('js/bootstrap-select.min.js') }}"></script>
<script type="text/javascript">
$(document).ready(function(){
    var draftVoucher = @json($draftVoucher);
    @if(auth()->user()->can('create_products'))
        $("#product_company").selectpicker('render');
        $("#product_branch").selectpicker('render');
        $("#product_iva_tax").selectpicker('render');
        $("#product_ice_tax").selectpicker('render');
        $("#product_irbpnr_tax").selectpicker('render');
        $('#product_company').change(function() {
            if($(this).val() != '') {
                var _token = $('input[name = "_token"]').val();
                var id = $(this).val();
                $.ajax({
                    url: "{{ route('companies.branches') }}",
                    method: "POST",
                    data: {
                        _token: _token,
                        id: id,
                    },
                    success: function(result) {
                        var branches = JSON.parse(result);
                        var options = '';
                        for (var i = 0; i < branches.length; i++) {
                            options += '<option value="' + branches[i]['id'] + '">' + branches[i]['name'] + '</option>';
                        }
                        $("#product_branch").html(options).selectpicker('refresh');
                    }
                })
            }
        });
        $("#submit_product").click(function() {
            $.ajax({
                url: "{{ route('products.store') }}",
                method: "POST",
                data: $('#product_form').serialize(),
                success: function(result) {
                    var validator = JSON.parse(result);
                    if (validator['status']) {
                        $('#productModal').modal('toggle');
                        $('#product_form').trigger('reset');
                        $('#product_company').selectpicker('refresh');
                        $('#product_branch').selectpicker('refresh');
                        $('#product_iva_tax').selectpicker('refresh');
                        $('#product_ice_tax').selectpicker('refresh');
                        $('#product_irbpnr_tax').selectpicker('refresh');
                    } else {
                        $('#validation').on('show.bs.modal', function(event) {
                            var errors = '';
                            $.each(validator['messages'], function(field, message) {
                                errors += "<li>" + message + "</li>";
                            });
                            $(this).find('#modal-body').html("<ul>" + errors + "</ul>");
                        });
                        $('#validation').modal('show');
                    }
                }
            });
        });
    @endif
    function addRowProduct() {
        var _token = $('input[name = "_token"]').val();
        var id = $("#branch").val();
        if (id != '') {
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
                        '<input class="form-control" type="text" id="product_quantity[]" name="product_quantity[]" value="">',
                        '<input class="form-control" type="text" id="product_unitprice[]" name="product_unitprice[]" value="">',
                        '<input class="form-control" type="text" id="product-iva[]" name="product-iva[]" value="" readonly>',
                        '<input class="form-control" type="text" id="product_discount[]" name="product_discount[]" value="">',
                        '<input class="form-control" type="text" id="product-subtotal[]" name="product-subtotal[]" value="" readonly>',
                        '<button type="button" class="btn btn-danger btn-sm">&times;</button>',
                    ]).draw(false);
                    $("select[id ~= 'product[]']").selectpicker();
                    if ('product' in draftVoucher) {
                        if ($("select[id ~= 'product[]']").length == draftVoucher['product'].length && draftVoucher['product'].length > 0) {
                            $("select[id ~= 'product[]']").each(function() {
                                $(this).selectpicker('val', draftVoucher['product'][0]);
                                draftVoucher['product'].shift();
                            });
                        }
                    }
                }
            });
        }
    }
    var invoiceTable = $('#invoice-table').DataTable({
        paging: false,
        dom: 'Bfrtip',
        buttons: [{
            text: 'Add row',
            action: function(e, dt, node, config){
                addRowProduct();
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
                    var productQuantity = elementChanged == '' ? 1.0 : (elementChanged == 'productQuantity' ? Number(reference.val()) : Number(reference.closest('tr').find('input[id *= product_quantity]').val()));
                    productQuantity = isNaN(productQuantity) ? 1.0 : productQuantity;
                    var productUnitPrice = elementChanged == '' ? Number(taxes[0]['product']['unit_price']) : (elementChanged == 'productUnitPrice' ? Number(reference.val()) : Number(reference.closest('tr').find('input[id *= product_unitprice]').val()));
                    productUnitPrice = isNaN(productUnitPrice) ? taxes[0]['product']['unit_price'] : productUnitPrice;
                    var productIva = elementChanged == '' ? (taxes[0]['iva'] != null ? Number(taxes[0]['iva']['rate']) : 0.0) : Number(reference.closest('tr').find('input[id *= product-iva]').val());
                    productIva = isNaN(productIva) ? (taxes[0]['iva'] != null ? Number(taxes[0]['iva']['rate']) : 0.0) : productIva;
                    //var productIce = taxes[0]['ice'] != null ? Number(taxes[0]['ice']['specific_rate']) + Number(taxes[0]['ice']['ad_valorem_rate']) : 0.0;
                    var productDiscount = elementChanged == '' ? 0.0 : (elementChanged == 'productDiscount' ? Number(reference.val()) : Number(reference.closest('tr').find('input[id *= product_discount]').val()));
                    productDiscount = isNaN(productDiscount) ? 0.0 : productDiscount;
                    reference.closest('tr').find('input[id *= product-description]').val(taxes[0]['product']['description']);
                    reference.closest('tr').find('input[id *= product_quantity]').val(productQuantity.toFixed(Math.floor(productQuantity) !== productQuantity ? (productQuantity.toString().split(".")[1].length <= 2 ? 2 : 6) : 2));
                    if ('product_quantity' in draftVoucher) {
                        if (draftVoucher['product_quantity'].length > 0) {
                            if (draftVoucher['product_quantity'][0] != null) {
                                productQuantity = Number(draftVoucher['product_quantity'][0]);
                                reference.closest('tr').find('input[id *= product_quantity]').val(productQuantity.toFixed(Math.floor(productQuantity) !== productQuantity ? (productQuantity.toString().split(".")[1].length <= 2 ? 2 : 6) : 2));
                            }
                            draftVoucher['product_quantity'].shift();
                        }
                    }
                    reference.closest('tr').find('input[id *= product_unitprice]').val(productUnitPrice.toFixed(Math.floor(productUnitPrice) !== productUnitPrice ? (productUnitPrice.toString().split(".")[1].length <= 2 ? 2 : 6) : 2));
                    if ('product_unitprice' in draftVoucher) {
                        if (draftVoucher['product_unitprice'].length > 0) {
                            if (draftVoucher['product_unitprice'][0] != null) {
                                productUnitPrice = Number(draftVoucher['product_unitprice'][0]);
                                reference.closest('tr').find('input[id *= product_unitprice]').val(productUnitPrice.toFixed(Math.floor(productUnitPrice) !== productUnitPrice ? (productUnitPrice.toString().split(".")[1].length <= 2 ? 2 : 6) : 2));
                            }
                            draftVoucher['product_unitprice'].shift();
                        }
                    }
                    reference.closest('tr').find('input[id *= product-iva]').val(productIva.toFixed(2));
                    reference.closest('tr').find('input[id *= product_discount]').val(productDiscount.toFixed(2));
                    if ('product_discount' in draftVoucher) {
                        if (draftVoucher['product_discount'].length > 0) {
                            if (draftVoucher['product_discount'][0] != null) {
                                productDiscount = Number(draftVoucher['product_discount'][0]);
                                reference.closest('tr').find('input[id *= product_discount]').val(productDiscount.toFixed(2));
                            }
                            draftVoucher['product_discount'].shift();
                        }
                    }
                    var productSubtotal = (productQuantity * productUnitPrice - productDiscount) * (1 + productIva / 100.0);
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
        var quantities = $.map($('input[id *= product_quantity]'), function(option) {
            return Number(option.value);
        });
        var unitPrices = $.map($('input[id *= product_unitprice]'), function(option) {
            return Number(option.value);
        });
        var discounts = $.map($('input[id *= product_discount]'), function(option) {
            return Number(option.value);
        });
        if (id.length > 0) {
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
    }
    $('#invoice-table tbody').on('changed.bs.select', 'select[id *= product]', function(){
        loadProductData($(this), '');
    });
    $('#invoice-table tbody').on('change', 'input[id *= product_quantity]', function(){
        loadProductData($(this), 'productQuantity');
    });
    $('#invoice-table tbody').on('change', 'input[id *= product_unitprice]', function(){
        loadProductData($(this), 'productUnitPrice');
    });
    $('#invoice-table tbody').on('change', 'input[id *= product_discount]', function(){
        loadProductData($(this), 'productDiscount');
    });
    if ('product' in draftVoucher) {
        for (var i = 0; i < draftVoucher['product'].length; i++) {
            addRowProduct();
        }
    }
    $('#invoice-table tbody').on('click', 'button.btn.btn-danger.btn-sm', function(){
        invoiceTable
            .row($(this).parents('tr'))
            .remove()
            .draw();
    });
    function addRowPayment() {
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
                            '<input class="form-control" type="text" id="paymentMethod_value[]" name="paymentMethod_value[]" value="">',
                            '<select class="form-control selectpicker" id="paymentMethod_timeunit[]" name="paymentMethod_timeunit[]" data-live-search="true" title="Select a time unit ...">' + optionsTimeUnit + '</select>',
                            '<input class="form-control" type="text" id="paymentMethod_term[]" name="paymentMethod_term[]" value="0">',
                            '<button type="button" class="btn btn-danger btn-sm">&times;</button>',
                        ]).draw(false);
                        $('select[id *= paymentMethod]').selectpicker('val', 1);
                        if ('paymentMethod' in draftVoucher) {
                            if ($("select[id ~= 'paymentMethod[]']").length == draftVoucher['paymentMethod'].length && draftVoucher['paymentMethod'].length > 0) {
                                $("select[id ~= 'paymentMethod[]']").each(function() {
                                    $(this).selectpicker('val', draftVoucher['paymentMethod'][0]);
                                    draftVoucher['paymentMethod'].shift();
                                    $(this).closest('tr').find('input[id *= paymentMethod_value]').val(draftVoucher['paymentMethod_value'][0]);
                                    draftVoucher['paymentMethod_value'].shift();
                                });
                            }
                        }
                        $('select[id *= paymentMethod_timeunit]').selectpicker('val', 1);
                        if ('paymentMethod_timeunit' in draftVoucher) {
                            if ($("select[id ~= 'paymentMethod_timeunit[]']").length == draftVoucher['paymentMethod_timeunit'].length && draftVoucher['paymentMethod_timeunit'].length > 0) {
                                $("select[id ~= 'paymentMethod_timeunit[]']").each(function() {
                                    $(this).selectpicker('val', draftVoucher['paymentMethod_timeunit'][0]);
                                    draftVoucher['paymentMethod_timeunit'].shift();
                                    if ($(this).val() == '') {
                                        $(this).selectpicker('val', 1);
                                    }
                                    $(this).closest('tr').find('input[id *= paymentMethod_term]').val(draftVoucher['paymentMethod_term'][0]);
                                    draftVoucher['paymentMethod_term'].shift();
                                });
                            }
                        }
                    }
                });
            }
        });
    }
    var paymentMethodTable = $('#paymentmethod-table').DataTable({
        paging: false,
        dom: 'Bfrtip',
        searching: false,
        buttons: [{
            text: 'Add row',
            action: function(e, dt, node, config){
                addRowPayment();
            }
        }]
    });
    $('#paymentmethod-table tbody').on('click', 'button.btn.btn-danger.btn-sm', function(){
        paymentMethodTable
            .row($(this).parents('tr'))
            .remove()
            .draw();
    });
    if ('paymentMethod' in draftVoucher) {
        for (var i = 0; i < draftVoucher['paymentMethod'].length; i++) {
            addRowPayment();
        }
    }
    var additionalDetailCount = 0;
    function addRowAdditionalDetail() {
        if (additionalDetailCount < 3) {
            additionalDetailTable.row.add([
                '<input class="form-control" type="text" id="additionaldetail_name[]" name="additionaldetail_name[]" value="">',
                '<input class="form-control" type="text" id="additionaldetail_value[]" name="additionaldetail_value[]" value="">',
                '<button type="button" class="btn btn-danger btn-sm">&times;</button>',
            ]).draw(false);
            additionalDetailCount++;
            if ('additionaldetail_name' in draftVoucher) {
                if ($("input[id ~= 'additionaldetail_name[]']").length == draftVoucher['additionaldetail_name'].length && draftVoucher['additionaldetail_name'].length > 0) {
                    $("input[id ~= 'additionaldetail_name[]']").each(function() {
                        $(this).val(draftVoucher['additionaldetail_name'][0]);
                        draftVoucher['additionaldetail_name'].shift();
                    });
                }
            }
            if ('additionaldetail_value' in draftVoucher) {
                if ($("input[id ~= 'additionaldetail_value[]']").length == draftVoucher['additionaldetail_value'].length && draftVoucher['additionaldetail_value'].length > 0) {
                    $("input[id ~= 'additionaldetail_value[]']").each(function() {
                        $(this).val(draftVoucher['additionaldetail_value'][0]);
                        draftVoucher['additionaldetail_value'].shift();
                    });
                }
            }
        }
    }
    var additionalDetailTable = $('#additionaldetail-table').DataTable({
        paging: false,
        dom: 'Bfrtip',
        searching: false,
        buttons: [{
            text: 'Add row',
            action: function(e, dt, node, config){
                addRowAdditionalDetail();
            }
        }]
    });
    $('#additionaldetail-table tbody').on('click', 'button.btn.btn-danger.btn-sm', function(){
        additionalDetailTable
            .row($(this).parents('tr'))
            .remove()
            .draw();
        additionalDetailCount--;
    });
    if ('additionaldetail_name' in draftVoucher) {
        for (var i = 0; i < draftVoucher['additionaldetail_name'].length; i++) {
            addRowAdditionalDetail();
        }
    }
    $('#waybill_establishment').val(draftVoucher['waybill_establishment']);
    $('#waybill_emissionpoint').val(draftVoucher['waybill_emissionpoint']);
    $('#waybill_sequential').val(draftVoucher['waybill_sequential']);
    $('#extra_detail').val(draftVoucher['extra_detail']);
    $("#ivaRetention").change(function() {
        $('#ivaRetentionValue')
            .prop('readonly', !this.checked)
            .val('');
    });
    $("#rentRetention").change(function() {
        $('#rentRetentionValue')
            .prop('readonly', !this.checked)
            .val('');
    });
    if ('ivaRetention' in draftVoucher) {
        $('#ivaRetention').prop('checked', true);
        $('#ivaRetention').trigger('change');
        $('#ivaRetentionValue').val(draftVoucher['ivaRetentionValue']);
    }
    if ('rentRetention' in draftVoucher) {
        $('#rentRetention').prop('checked', true);
        $('#rentRetention').trigger('change');
        $('#rentRetentionValue').val(draftVoucher['rentRetentionValue']);
    }
    $('#tip').change(function() {
        updateTotal();
    });
    $('#tip').val(Number(draftVoucher['tip']).toFixed(2));
});
</script>
<div class="col-sm-12">
    <div class="card mb-3">
        <div class="card-body">
            <h5 class="card-title">
                5. Invoice
                @if(auth()->user()->can('create_products'))
                    <button type="button" class="btn btn-sm btn-success float-right" data-toggle="modal" data-target="#productModal">New product</button>
                @endif
            </h5>
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
<div class="col-sm-6">
    <div class="card mb-3">
        <div class="card-body">
            <h5 class="card-title">7. Additional information</h5>
            <table id="additionaldetail-table" class="display">
                <thead>
                    <tr>
                        <th>Name</th>
                        <th>Value</th>
                        <th></th>
                    </tr>
                </thead>
            </table>
            <table class="table table-striped">
                <thead>
                    <tr>
                        <th>Waybill</th>
                    </tr>
                </thead>
                <tbody>
                    <tr>
                        <td>
                            <div class="form-row">
                                <div class="form-group col-md-3">
                                    <input class="form-control" type="text" id="waybill_establishment" name="waybill_establishment" size="3" value="" >
                                </div>
                                <div class="form-group col-md-3">
                                    <input class="form-control" type="text" id="waybill_emissionpoint" name="waybill_emissionpoint" size="3" value="" >
                                </div>
                                <div class="form-group col-md-5">
                                    <input class="form-control" type="text" id="waybill_sequential" name="waybill_sequential" size="9" value="" >
                                </div>
                            </div>
                        </td>
                    </tr>
                </tbody>
            </table>
            <table class="table table-striped">
                <thead>
                    <tr>
                        <th>Extra detail</th>
                    </tr>
                </thead>
                <tbody>
                    <tr>
                        <td><textarea class="form-control" type="text" id="extra_detail" name="extra_detail" value=""></textarea></td>
                    </tr>
                </tbody>
            </table>
        </div>
    </div>
    <div class="card mb-3">
        <div class="card-body">
            <h5 class="card-title">8. Retentions</h5>
            <table class="table table-striped">
                <tbody>
                    <tr>
                        <td>
                            <input class="form-check-input" type="checkbox" value="" id="ivaRetention" name="ivaRetention">
                            <label class="form-check-label" for="ivaRetention">IVA retention</label>
                        </td>
                        <td><input class="form-control" type="text" id="ivaRetentionValue" name="ivaRetentionValue" value="" readonly></td>
                    </tr>
                    <tr>
                        <td>
                            <input class="form-check-input" type="checkbox" value="" id="rentRetention" name="rentRetention">
                            <label class="form-check-label" for="rentRetention">Rent retention</label>
                        </td>
                        <td><input class="form-control" type="text" id="rentRetentionValue" name="rentRetentionValue" value="" readonly></td>
                    </tr>
                </tbody>
            </table>
        </div>
    </div>
</div>
<div class="col-sm-6">
    <div class="card mb-3">
        <div class="card-body">
            <h5 class="card-title">9. Total</h5>
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
@if(auth()->user()->can('create_products'))
    <div class="modal fade" tabindex="-1" role="dialog" id="productModal">
        <div class="modal-dialog modal-dialog-centered" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <strong>New product</strong>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <form id="product_form">
                    {{ csrf_field() }}
                    <div class="modal-body">
                        <div class="form-group">
                            <label for="product_company">Company</label>
                            <select class="form-control selectpicker input-lg dynamic" id="product_company" name="company" data-live-search="true" data-dependent="branch" title="Select a company ...">
                                @foreach($companiesproduct as $company)
                                    <option value="{{ $company->id }}">{{ $company->tradename }} - {{ $company->social_reason }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="form-group" id="divbranch">
                            <label for="product_branch">Branch</label>
                            <select class="form-control selectpicker input-lg" id="product_branch" name="branch" data-live-search="true" title="Select a branch ...">

                            </select>
                        </div>
                        <div class="form-group">
                            <label for="product_main_code">Main Code</label>
                            <input type="text" class="form-control" id="product_main_code" name="main_code" value="">
                        </div>
                        <div class="form-group">
                            <label for="product_auxiliary_code">Auxiliary Code</label>
                            <input type="text" class="form-control" id="product_auxiliary_code" name="auxiliary_code"  value="">
                        </div>
                        <div class="form-group">
                            <label for="product_unit_price">Unit Price</label>
                            <input type="number" step="0.01" class="form-control" id="product_unit_price" name="unit_price"  value="">
                        </div>
                        <div class="form-group">
                            <label for="product_description">Description</label>
                            <input type="text" class="form-control" id="product_description" name="description"  value="">
                        </div>
                        <div class="form-group">
                            <label for="product_stock">Stock</label>
                            <input type="number" class="form-control" id="product_stock" name="stock"  value="">
                        </div>
                        <div class="form-group">
                            <label for="product_iva_tax">Iva taxes</label>
                            <select class="form-control selectpicker" id="product_iva_tax" name="iva_tax" data-live-search="true"  title="Select an IVA tax ...">
                                @foreach($iva_taxes as $iva_tax)
                                    <option value="{{ $iva_tax->id }}">{{ $iva_tax->auxiliary_code }} - {{ $iva_tax->description }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="form-group">
                            <label for="product_ice_tax">Ice Taxes</label>
                            <select class="form-control selectpicker" id="product_ice_tax" name="ice_tax" data-live-search="true" title="Select an ICE tax ...">
                                @foreach($ice_taxes as $ice_tax)
                                    <option value="{{ $ice_tax->id }}">{{ $ice_tax->auxiliary_code }} - {{ $ice_tax->description }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="form-group">
                            <label for="product_irbpnr_tax">Irbpnr taxes</label>
                            <select class="form-control selectpicker" id="product_irbpnr_tax" name="irbpnr_tax" data-live-search="true" title="Select an IRBPNR tax ...">
                                @foreach($irbpnr_taxes as $irbpnr_tax)
                                    <option value="{{ $irbpnr_tax->id }}">{{ $irbpnr_tax->auxiliary_code }} - {{ $irbpnr_tax->description }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-sm btn-secondary" data-dismiss="modal">Close</button>
                        <button id="submit_product" type="button" class="btn btn-sm btn-success">Add</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
@endif
