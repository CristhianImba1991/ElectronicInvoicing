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
                        '<select class="form-control selectpicker" id="product[]" name="product[]" data-live-search="true" title="Select a product ...">' + options + '</select>',
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
                    //updateTotal();
                }
            });
        }
    }
});
</script>
