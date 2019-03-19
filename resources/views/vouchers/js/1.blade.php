<script type="text/javascript">
$(document).ready(function(){
    @if($action === 'draft')
        var voucher = @json($draftVoucher);
    @elseif($action === 'edit')
        var voucher = {
            "product": @json(Detail::where('voucher_id', '=', $voucher->id)->get()->pluck('product_id')),
            "product_quantity": @json(Detail::where('voucher_id', '=', $voucher->id)->get()->pluck('quantity')),
            "product_unitprice": @json(Detail::where('voucher_id', '=', $voucher->id)->get()->pluck('unit_price')),
            "product_discount": @json(Detail::where('voucher_id', '=', $voucher->id)->get()->pluck('discount')),
            "paymentMethod": @json($voucher->payments()->get()->pluck('payment_method_id')),
            "paymentMethod_value": @json($voucher->payments()->get()->pluck('total')),
            "paymentMethod_timeunit": @json($voucher->payments()->get()->pluck('time_unit_id')),
            "paymentMethod_term": @json($voucher->payments()->get()->pluck('term')),
            "additionaldetail_name": @json($voucher->additionalFields()->get()->pluck('name')),
            "additionaldetail_value": @json($voucher->additionalFields()->get()->pluck('value')),
            "waybill_establishment": @json($voucher->support_document !== NULL ? substr($voucher->support_document, 0, 3) : ''),
            "waybill_emissionpoint": @json($voucher->support_document !== NULL ? substr($voucher->support_document, 3, 3) : ''),
            "waybill_sequential": @json($voucher->support_document !== NULL ? substr($voucher->support_document, 6, 9) : ''),
            "extra_detail": @json($voucher->extra_detail),
            "tip": @json($voucher->tip)
        };
        @if($voucher->iva_retention !== NULL)
            voucher['ivaRetention'] = null;
            voucher['ivaRetentionValue'] = @json($voucher->iva_retention);
        @endif
        @if($voucher->rent_retention !== NULL)
            voucher['rentRetention'] = null;
            voucher['rentRetentionValue'] = @json($voucher->rent_retention);
        @endif
    @endif
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
                        '<select class="form-control selectpicker" id="product[]" name="product[]" data-live-search="true" title="{{ trans_choice(__("view.select_a_model", ["model" => trans_choice(__("view.product"), 0)]), 0) }}">' + options + '</select>',
                        '<input class="form-control" type="text" id="product-description[]" name="product-description[]" value="" readonly>',
                        '<input class="form-control" type="text" id="product_detail1[]" name="product_detail1[]" value="">',
                        '<input class="form-control" type="text" id="product_detail2[]" name="product_detail2[]" value="">',
                        '<input class="form-control" type="text" id="product_detail3[]" name="product_detail3[]" value="">',
                        '<input class="form-control" type="text" id="product_quantity[]" name="product_quantity[]" value="">',
                        '<input class="form-control" type="text" id="product_unitprice[]" name="product_unitprice[]" value="">',
                        '<input class="form-control" type="text" id="product-iva[]" name="product-iva[]" value="" readonly>',
                        '<input class="form-control" type="text" id="product_discount[]" name="product_discount[]" value="">',
                        '<input class="form-control" type="text" id="product-subtotal[]" name="product-subtotal[]" value="" readonly>',
                        '<button type="button" class="btn btn-danger btn-sm">&times;</button>',
                    ]).draw(false);
                    @if($action === 'create')
                        $('select[id *= product]').selectpicker();
                    @elseif($action === 'edit' || $action === 'draft')
                        $("select[id ~= 'product[]']").selectpicker();
                        if ('product' in voucher) {
                            if ($("select[id ~= 'product[]']").length == voucher['product'].length && voucher['product'].length > 0) {
                                $("select[id ~= 'product[]']").each(function() {
                                    $(this).selectpicker('val', voucher['product'][0]);
                                    voucher['product'].shift();
                                });
                            }
                        }
                    @endif
                }
            });
        }
    }
    var invoiceTable = $('#invoice-table').DataTable({
        paging: false,
        //responsive: true,
        dom: 'Bfrtip',
        buttons: [{
            text: '{{ __("view.add_row") }}',
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
                    @if($action === 'edit' || $action === 'draft')
                        if ('product_quantity' in voucher) {
                            if (voucher['product_quantity'].length > 0) {
                                if (voucher['product_quantity'][0] != null) {
                                    productQuantity = Number(voucher['product_quantity'][0]);
                                    reference.closest('tr').find('input[id *= product_quantity]').val(productQuantity.toFixed(Math.floor(productQuantity) !== productQuantity ? (productQuantity.toString().split(".")[1].length <= 2 ? 2 : 6) : 2));
                                }
                                voucher['product_quantity'].shift();
                            }
                        }
                    @endif
                    reference.closest('tr').find('input[id *= product_unitprice]').val(productUnitPrice.toFixed(Math.floor(productUnitPrice) !== productUnitPrice ? (productUnitPrice.toString().split(".")[1].length <= 2 ? 2 : 6) : 2));
                    @if($action === 'edit' || $action === 'draft')
                        if ('product_unitprice' in voucher) {
                            if (voucher['product_unitprice'].length > 0) {
                                if (voucher['product_unitprice'][0] != null) {
                                    productUnitPrice = Number(voucher['product_unitprice'][0]);
                                    reference.closest('tr').find('input[id *= product_unitprice]').val(productUnitPrice.toFixed(Math.floor(productUnitPrice) !== productUnitPrice ? (productUnitPrice.toString().split(".")[1].length <= 2 ? 2 : 6) : 2));
                                }
                                voucher['product_unitprice'].shift();
                            }
                        }
                    @endif
                    reference.closest('tr').find('input[id *= product-iva]').val(productIva.toFixed(2));
                    reference.closest('tr').find('input[id *= product_discount]').val(productDiscount.toFixed(2));
                    @if($action === 'edit' || $action === 'draft')
                        if ('product_discount' in voucher) {
                            if (voucher['product_discount'].length > 0) {
                                if (voucher['product_discount'][0] != null) {
                                    productDiscount = Number(voucher['product_discount'][0]);
                                    reference.closest('tr').find('input[id *= product_discount]').val(productDiscount.toFixed(2));
                                }
                                voucher['product_discount'].shift();
                            }
                        }
                    @endif
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
                                    case 2: case 3:
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
    @if($action === 'edit' || $action === 'draft')
        if ('product' in voucher) {
            for (var i = 0; i < voucher['product'].length; i++) {
                addRowProduct();
            }
        }
    @endif
    $('#invoice-table tbody').on('click', 'button.btn.btn-danger.btn-sm', function(){
        invoiceTable
            .row($(this).parents('tr'))
            .remove()
            .draw();
        updateTotal();
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
                            '<select class="form-control selectpicker" id="paymentMethod[]" name="paymentMethod[]" data-live-search="true" title="{{ trans_choice(__("view.select_a_model", ["model" => strtolower(__("view.payment_method"))]), 0) }}">' + optionsPaymentMethod + '</select>',
                            '<input class="form-control" type="text" id="paymentMethod_value[]" name="paymentMethod_value[]" value="">',
                            '<select class="form-control selectpicker" id="paymentMethod_timeunit[]" name="paymentMethod_timeunit[]" data-live-search="true" title="{{ trans_choice(__("view.select_a_model", ["model" => strtolower(__("view.time_unit"))]), 1) }}">' + optionsTimeUnit + '</select>',
                            '<input class="form-control" type="text" id="paymentMethod_term[]" name="paymentMethod_term[]" value="0">',
                            '<button type="button" class="btn btn-danger btn-sm">&times;</button>',
                        ]).draw(false);
                        @if($action === 'create')
                            $('select[id *= paymentMethod]').selectpicker();
                            $("select[id ~= 'paymentMethod[]']").each(function() {
                                $(this).selectpicker('val', $(this).val() == '' ? 1 : $(this).val());
                            });
                            $('select[id *= paymentMethod_timeunit]').selectpicker();
                            $("select[id ~= 'paymentMethod_timeunit[]']").each(function() {
                                $(this).selectpicker('val', $(this).val() == '' ? 1 : $(this).val());
                            });
                        @elseif($action === 'edit' || $action === 'draft')
                        $('select[id *= paymentMethod]').selectpicker('val', 1);
                            if ('paymentMethod' in voucher) {
                                if ($("select[id ~= 'paymentMethod[]']").length == voucher['paymentMethod'].length && voucher['paymentMethod'].length > 0) {
                                    $("select[id ~= 'paymentMethod[]']").each(function() {
                                        $(this).selectpicker('val', voucher['paymentMethod'][0]);
                                        voucher['paymentMethod'].shift();
                                        $(this).closest('tr').find('input[id *= paymentMethod_value]').val(Number(voucher['paymentMethod_value'][0]).toFixed(2));
                                        voucher['paymentMethod_value'].shift();
                                    });
                                }
                            }
                            $('select[id *= paymentMethod_timeunit]').selectpicker('val', 1);
                            if ('paymentMethod_timeunit' in voucher) {
                                if ($("select[id ~= 'paymentMethod_timeunit[]']").length == voucher['paymentMethod_timeunit'].length && voucher['paymentMethod_timeunit'].length > 0) {
                                    $("select[id ~= 'paymentMethod_timeunit[]']").each(function() {
                                        $(this).selectpicker('val', voucher['paymentMethod_timeunit'][0]);
                                        voucher['paymentMethod_timeunit'].shift();
                                        if ($(this).val() == '') {
                                            $(this).selectpicker('val', 1);
                                        }
                                        $(this).closest('tr').find('input[id *= paymentMethod_term]').val(voucher['paymentMethod_term'][0]);
                                        voucher['paymentMethod_term'].shift();
                                    });
                                }
                            }
                        @endif
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
            text: '{{ __("view.add_row") }}',
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
    @if($action === 'edit' || $action === 'draft')
        if ('paymentMethod' in voucher) {
            for (var i = 0; i < voucher['paymentMethod'].length; i++) {
                addRowPayment();
            }
        }
    @endif
    var additionalDetailCount = 0;
    function addRowAdditionalDetail() {
        if (additionalDetailCount < 3) {
            additionalDetailTable.row.add([
                '<input class="form-control" type="text" id="additionaldetail_name[]" name="additionaldetail_name[]" value="">',
                '<input class="form-control" type="text" id="additionaldetail_value[]" name="additionaldetail_value[]" value="">',
                '<button type="button" class="btn btn-danger btn-sm">&times;</button>',
            ]).draw(false);
            additionalDetailCount++;
            @if($action === 'edit' || $action === 'draft')
                if ('additionaldetail_name' in voucher) {
                    if ($("input[id ~= 'additionaldetail_name[]']").length == voucher['additionaldetail_name'].length && voucher['additionaldetail_name'].length > 0) {
                        $("input[id ~= 'additionaldetail_name[]']").each(function() {
                            $(this).val(voucher['additionaldetail_name'][0]);
                            voucher['additionaldetail_name'].shift();
                        });
                    }
                }
                if ('additionaldetail_value' in voucher) {
                    if ($("input[id ~= 'additionaldetail_value[]']").length == voucher['additionaldetail_value'].length && voucher['additionaldetail_value'].length > 0) {
                        $("input[id ~= 'additionaldetail_value[]']").each(function() {
                            $(this).val(voucher['additionaldetail_value'][0]);
                            voucher['additionaldetail_value'].shift();
                        });
                    }
                }
            @endif
        }
    }
    var additionalDetailTable = $('#additionaldetail-table').DataTable({
        paging: false,
        dom: 'Bfrtip',
        searching: false,
        buttons: [{
            text: '{{ __("view.add_row") }}',
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
    @if($action === 'edit' || $action === 'draft')
        if ('additionaldetail_name' in voucher) {
            for (var i = 0; i < voucher['additionaldetail_name'].length; i++) {
                addRowAdditionalDetail();
            }
        }
        $('#waybill_establishment').val(voucher['waybill_establishment']);
        $('#waybill_emissionpoint').val(voucher['waybill_emissionpoint']);
        $('#waybill_sequential').val(voucher['waybill_sequential']);
        $('#extra_detail').val(voucher['extra_detail']);
    @endif
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
    @if($action === 'edit' || $action === 'draft')
        if ('ivaRetention' in voucher) {
            $('#ivaRetention').prop('checked', true);
            $('#ivaRetention').trigger('change');
            $('#ivaRetentionValue').val(Number(voucher['ivaRetentionValue']).toFixed(2));
        }
        if ('rentRetention' in voucher) {
            $('#rentRetention').prop('checked', true);
            $('#rentRetention').trigger('change');
            $('#rentRetentionValue').val(Number(voucher['rentRetentionValue']).toFixed(2));
        }
    @endif
    $('#tip').change(function() {
        updateTotal();
    });
    @if($action === 'edit' || $action === 'draft')
        $('#tip').val(Number(voucher['tip']).toFixed(2));
    @endif
});
</script>
