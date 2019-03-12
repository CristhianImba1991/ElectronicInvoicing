<script type="text/javascript">
$(document).ready(function(){
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
                    creditNoteTable.row.add([
                        '<select class="form-control selectpicker" id="product[]" name="product[]" data-live-search="true" title="{{ __("view.select_a_product") }}">' + options + '</select>',
                        '<input class="form-control" type="text" id="product-description[]" name="product-description[]" value="" readonly>',
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
    var creditNoteTable = $('#creditNote-table').DataTable({
        paging: false,
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
                    var total = subtotal + iceValue + irbpnrValue + ivaValue;
                    $('#iva0subtotal').val(iva0Subtotal.toFixed(2));
                    $('#ivasubtotal').val(ivaSubtotal.toFixed(2));
                    $('#notsubjectivasubtotal').val(notSubjectIvaSubtotal.toFixed(2));
                    $('#exemptivasubtotal').val(exemptIvaSubtotal.toFixed(2));
                    $('#subtotal').val(subtotal.toFixed(2));
                    $('#totaldiscount').val(totalDiscount.toFixed(2));
                    //$('#icevalue').val(iceValue.toFixed(2));
                    //$('#irbpnrvalue').val(irbpnrValue.toFixed(2));
                    $('#ivavalue').val(ivaValue.toFixed(2));
                    $('#total').val(total.toFixed(2));
                }
            });
        }
    }
    $('#creditNote-table tbody').on('changed.bs.select', 'select[id *= product]', function(){
        loadProductData($(this), '');
    });
    $('#creditNote-table tbody').on('change', 'input[id *= product_quantity]', function(){
        loadProductData($(this), 'productQuantity');
    });
    $('#creditNote-table tbody').on('change', 'input[id *= product_unitprice]', function(){
        loadProductData($(this), 'productUnitPrice');
    });
    $('#creditNote-table tbody').on('change', 'input[id *= product_discount]', function(){
        loadProductData($(this), 'productDiscount');
    });
    @if($action === 'edit' || $action === 'draft')
        if ('product' in voucher) {
            for (var i = 0; i < voucher['product'].length; i++) {
                addRowProduct();
            }
        }
    @endif
    $('#creditNote-table tbody').on('click', 'button.btn.btn-danger.btn-sm', function(){
        creditNoteTable
            .row($(this).parents('tr'))
            .remove()
            .draw();
        updateTotal();
    });
    $('#issue_date_support_document').datepicker({
        autoclose: true,
        todayBtn: 'linked',
        todayHighlight: true,
        endDate: '0d',
        format: 'yyyy/mm/dd',
        daysOfWeekHighlighted: "0,6"
    });
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
        $('#extra_detail').val(voucher['extra_detail']);
    @endif
});
</script>
