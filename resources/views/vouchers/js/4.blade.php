<script type="text/javascript">
$(document).ready(function(){
    @if($action === 'draft')
        var voucher = @json($draftVoucher);
    @elseif($action === 'edit')
        var voucher = {
            "product": @json(\ElectronicInvoicing\Waybill::where('voucher_id', '=', $voucher->id)->first()->addressees()->first()->details()->get()->pluck('product_id')),
            "product_additionalDetails": @json(\ElectronicInvoicing\Waybill::where('voucher_id', '=', $voucher->id)->first()->addressees()->first()->details()->with('additionalDetails')->get()),
            "product_quantity": @json(\ElectronicInvoicing\Waybill::where('voucher_id', '=', $voucher->id)->first()->addressees()->first()->details()->get()->pluck('quantity')),
            "identification_type": @json(\ElectronicInvoicing\Waybill::where('voucher_id', '=', $voucher->id)->first()->identification_type_id),
            "carrier_ruc": @json(\ElectronicInvoicing\Waybill::where('voucher_id', '=', $voucher->id)->first()->carrier_ruc),
            "carrier_social_reason": @json(\ElectronicInvoicing\Waybill::where('voucher_id', '=', $voucher->id)->first()->carrier_social_reason),
            "licence_plate": @json(\ElectronicInvoicing\Waybill::where('voucher_id', '=', $voucher->id)->first()->licence_plate),
            "starting_address": @json(\ElectronicInvoicing\Waybill::where('voucher_id', '=', $voucher->id)->first()->starting_address),
            "start_date_transport": @json(\ElectronicInvoicing\Waybill::where('voucher_id', '=', $voucher->id)->first()->start_date_transport),
            "end_date_transport": @json(\ElectronicInvoicing\Waybill::where('voucher_id', '=', $voucher->id)->first()->end_date_transport),
            "additionaldetail_name": @json($voucher->additionalFields()->get()->pluck('name')),
            "additionaldetail_value": @json($voucher->additionalFields()->get()->pluck('value')),
            "extra_detail": @json($voucher->extra_detail),
            "authorization_number": @json(\ElectronicInvoicing\Waybill::where('voucher_id', '=', $voucher->id)->first()->addressees()->first()->support_doc_code),
            "single_customs_doc": @json(\ElectronicInvoicing\Waybill::where('voucher_id', '=', $voucher->id)->first()->addressees()->first()->single_customs_doc),
            "address": @json(\ElectronicInvoicing\Waybill::where('voucher_id', '=', $voucher->id)->first()->addressees()->first()->address),
            "transfer_reason": @json(\ElectronicInvoicing\Waybill::where('voucher_id', '=', $voucher->id)->first()->addressees()->first()->transfer_reason),
            "destination_establishment_code": @json(\ElectronicInvoicing\Waybill::where('voucher_id', '=', $voucher->id)->first()->addressees()->first()->destination_establishment_code),
            "route": @json(\ElectronicInvoicing\Waybill::where('voucher_id', '=', $voucher->id)->first()->addressees()->first()->route),
        };
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
                        options += '<option value="' + products[i]['id'] + '">' + products[i]['main_code'] + ' (' + products[i]['auxiliary_code'] + ') - ' + products[i]['description'] + '</option>';
                    }
                    waybillTable.row.add([
                        '<select class="form-control selectpicker" id="product[]" name="product[]" data-live-search="true" title="{{ trans_choice(__("view.select_a_model", ["model" => trans_choice(__("view.product"), 0)]), 0) }}">' + options + '</select>',
                        '<input class="form-control" type="text" id="product-description[]" name="product-description[]" value="" readonly>',
                        '<input class="form-control" type="text" id="product_detail1[]" name="product_detail1[]" value="">',
                        '<input class="form-control" type="text" id="product_detail2[]" name="product_detail2[]" value="">',
                        '<input class="form-control" type="text" id="product_detail3[]" name="product_detail3[]" value="">',
                        '<input class="form-control" type="text" id="product_quantity[]" name="product_quantity[]" value="">',
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
    var waybillTable = $('#waybill-table').DataTable({
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
                    reference.closest('tr').find('input[id *= product-description]').val(taxes[0]['product']['description']);
                    @if($action === 'edit')
                        for (var i = 0; i < voucher['product_additionalDetails'][0]['additional_details'].length && i < 3; i++) {
                            reference.closest('tr').find('input[id *= product_detail' + (i + 1) + ']').val(voucher['product_additionalDetails'][0]['additional_details'][i]['value']);
                        }
                        voucher['product_additionalDetails'].shift();
                    @elseif($action === 'draft')
                        reference.closest('tr').find('input[id *= product_detail1]').val(voucher['product_detail1'][0]);
                        reference.closest('tr').find('input[id *= product_detail2]').val(voucher['product_detail2'][0]);
                        reference.closest('tr').find('input[id *= product_detail3]').val(voucher['product_detail3'][0]);
                        voucher['product_detail1'].shift();
                        voucher['product_detail2'].shift();
                        voucher['product_detail3'].shift();
                    @endif
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
                }
            });
        }
    }
    $('#waybill-table tbody').on('changed.bs.select', 'select[id *= product]', function(){
        loadProductData($(this), '');
    });
    $('#waybill-table tbody').on('change', 'input[id *= product_quantity]', function(){
        loadProductData($(this), 'productQuantity');
    });
    @if($action === 'edit' || $action === 'draft')
        if ('product' in voucher) {
            for (var i = 0; i < voucher['product'].length; i++) {
                addRowProduct();
            }
        }
    @endif
    $("#identification_type").selectpicker();
    @if($action === 'edit' || $action === 'draft')
        $("#identification_type").selectpicker('val', voucher['identification_type']);
        $("#carrier_ruc").val(voucher['carrier_ruc']);
        $("#carrier_social_reason").val(voucher['carrier_social_reason']);
        $("#licence_plate").val(voucher['licence_plate']);
        $("#starting_address").val(voucher['starting_address']);
    @endif
    $('#start_date_transport').datepicker({
        autoclose: true,
        todayBtn: 'linked',
        todayHighlight: true,
        endDate: '0d',
        language: '{{ str_replace('_', '-', app()->getLocale()) }}',
        format: 'yyyy-mm-dd',
        daysOfWeekHighlighted: "0,6"
    });
    $('#end_date_transport').datepicker({
        autoclose: true,
        todayBtn: 'linked',
        todayHighlight: true,
        endDate: '0d',
        language: '{{ str_replace('_', '-', app()->getLocale()) }}',
        format: 'yyyy-mm-dd',
        daysOfWeekHighlighted: "0,6"
    });
    @if($action === 'edit' || $action === 'draft')
        $('#start_date_transport').datepicker('update', voucher['start_date_transport']);
        $('#end_date_transport').datepicker('update', voucher['end_date_transport']);
    @endif
    function addRowAdditionalDetail() {
        if (additionalDetailTable.rows().eq(0).length < 15) {
            additionalDetailTable.row.add([
                '<input class="form-control" type="text" id="additionaldetail_name[]" name="additionaldetail_name[]" value="">',
                '<input class="form-control" type="text" id="additionaldetail_value[]" name="additionaldetail_value[]" value="">',
                '<button type="button" class="btn btn-danger btn-sm">&times;</button>',
            ]).draw(false);
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
    });
    @if($action === 'create')
        var _token = $('input[name = "_token"]').val();
        var id = $('#customer').val();
        if (id != '') {
            $.ajax({
                url: "{{ route('customers.customer') }}",
                method: "POST",
                data: {
                    _token: _token,
                    id: id,
                },
                success: function(result) {
                    var customer = JSON.parse(result);
                    if (customer[0]['email'] != null) {
                        var emails = customer[0]['email'].split(',');
                        var emailsString = '';
                        for (var i = 0; i < emails.length; i++) {
                            emailsString += (i === 0 ? '(P) ' : ', ') + emails[i];
                        }
                    }
                    var table = $("input[id ~= 'additionaldetail_name[]']");
                    if (table.length > 0) {
                        table.each(function (index) {
                            if ($(this).val() === 'Dirección' || $(this).val() === 'E-mail' || $(this).val() === 'Teléfono') {
                                additionalDetailTable
                                    .row($(this).parents('tr'))
                                    .remove()
                                    .draw();
                            }
                        })
                    }
                    if (additionalDetailTable.rows().eq(0).length + 1 <= 15) {
                        if (customer[0]['address'] != null) {
                            additionalDetailTable.row.add([
                                '<input class="form-control" type="text" id="additionaldetail_name[]" name="additionaldetail_name[]" value="Dirección">',
                                '<input class="form-control" type="text" id="additionaldetail_value[]" name="additionaldetail_value[]" value="' + customer[0]['address'] + '">',
                                '<button type="button" class="btn btn-danger btn-sm">&times;</button>',
                            ]).draw(false);
                        }
                    }
                    if (additionalDetailTable.rows().eq(0).length + 1 <= 15) {
                        if (customer[0]['phone'] != null) {
                            additionalDetailTable.row.add([
                                '<input class="form-control" type="text" id="additionaldetail_name[]" name="additionaldetail_name[]" value="Teléfono">',
                                '<input class="form-control" type="text" id="additionaldetail_value[]" name="additionaldetail_value[]" value="' + customer[0]['phone'] + '">',
                                '<button type="button" class="btn btn-danger btn-sm">&times;</button>',
                            ]).draw(false);
                        }
                    }
                    if (additionalDetailTable.rows().eq(0).length + 1 <= 15) {
                        if (customer[0]['email'] != null) {
                            additionalDetailTable.row.add([
                                '<input class="form-control" type="text" id="additionaldetail_name[]" name="additionaldetail_name[]" value="E-mail">',
                                '<input class="form-control" type="text" id="additionaldetail_value[]" name="additionaldetail_value[]" value="' + emailsString + '">',
                                '<button type="button" class="btn btn-danger btn-sm">&times;</button>',
                            ]).draw(false);
                        }
                    }
                }
            })
        }
    @elseif($action === 'edit' || $action === 'draft')
        if ('additionaldetail_name' in voucher) {
            for (var i = 0; i < voucher['additionaldetail_name'].length; i++) {
                addRowAdditionalDetail();
            }
        }
        $('#extra_detail').val(voucher['extra_detail']);
        $('#authorization_number').val(voucher['authorization_number']);
        $('#single_customs_doc').val(voucher['single_customs_doc']);
        $('#address').val(voucher['address']);
        $('#transfer_reason').val(voucher['transfer_reason']);
        $('#destination_establishment_code').val(voucher['destination_establishment_code']);
        $('#route').val(voucher['route']);
    @endif
});
</script>
