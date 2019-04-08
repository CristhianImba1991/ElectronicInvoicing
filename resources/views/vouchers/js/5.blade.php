<script type="text/javascript">
$(document).ready(function(){
    @if($action === 'draft')
        var voucher = @json($draftVoucher);
    @elseif($action === 'edit')
        var voucher = {
            "tax": @json(\ElectronicInvoicing\RetentionTaxDescription::whereIn('id', $voucher->retentions()->first()->details()->pluck('retention_tax_description_id'))->get()->pluck('retention_tax_id')),
            "description": @json($voucher->retentions()->first()->details()->pluck('retention_tax_description_id')),
            "value": @json($voucher->retentions()->first()->details()->pluck('rate')),
            "tax_base": @json($voucher->retentions()->first()->details()->pluck('tax_base')),
            "additionaldetail_name": @json($voucher->additionalFields()->get()->pluck('name')),
            "additionaldetail_value": @json($voucher->additionalFields()->get()->pluck('value')),
            "extra_detail": @json($voucher->extra_detail),
            "voucher_type_support_document": @json(\ElectronicInvoicing\VoucherType::where('code', $voucher->support_document[8] . $voucher->support_document[9])->first()->id),
            "supportdocument_establishment": @json($voucher->support_document !== NULL ? substr($voucher->support_document, 10, 3) : ''),
            "supportdocument_emissionpoint": @json($voucher->support_document !== NULL ? substr($voucher->support_document, 13, 3) : ''),
            "supportdocument_sequential": @json($voucher->support_document !== NULL ? substr($voucher->support_document, 16, 9) : ''),
            "issue_date_support_document": @json(substr($voucher->support_document, 4, 4)) + '/' + @json(substr($voucher->support_document, 2, 2)) + '/' + @json(substr($voucher->support_document, 0, 2))
        };
    @endif
    function addRowTax() {
        var _token = $('input[name = "_token"]').val();
        $.ajax({
            url: "{{ route('retentionTaxes.taxes') }}",
            method: "POST",
            data: {
                _token: _token
            },
            success: function(result) {
                var options = '';
                var taxes = JSON.parse(result);
                for (var i = 0; i < taxes.length; i++) {
                    options += '<option value="' + taxes[i]['id'] + '">' + taxes[i]['tax'] + '</option>';
                }
                retentionTable.row.add([
                    '<select class="form-control selectpicker" id="tax[]" name="tax[]" data-live-search="true" title="{{ trans_choice(__("view.select_a_model", ["model" => strtolower(__("view.tax"))]), 0) }}">' + options + '</select>',
                    '<select class="form-control selectpicker" id="description[]" name="description[]" data-live-search="true" title="{{ trans_choice(__("view.select_a_model", ["model" => strtolower(__("view.tax_description"))]), 1) }}"></select>',
                    '<input class="form-control" type="number" id="value[]" name="value[]" min="0.00" max="100.00" value="0.00" step="0.01">',
                    '<input class="form-control" type="text" id="tax_base[]" name="tax_base[]" value="0.00">',
                    '<input class="form-control" type="text" id="retained-value[]" name="retained-value[]" value="0.00" readonly>',
                    '<button type="button" class="btn btn-danger btn-sm">&times;</button>',
                ]).draw(false);
                @if($action === 'create')
                    $('select[id *= tax]').selectpicker();
                    $('select[id *= description]').selectpicker();
                @elseif($action === 'edit' || $action === 'draft')
                    $("select[id ~= 'tax[]']").selectpicker();
                    if ('tax' in voucher) {
                        if ($("select[id ~= 'tax[]']").length == voucher['tax'].length && voucher['tax'].length > 0) {
                            $("select[id ~= 'tax[]']").each(function() {
                                $(this).selectpicker('val', voucher['tax'][0]);
                                voucher['tax'].shift();
                            });
                        }
                    }
                @endif
                updateTotal();
            }
        });
    }
    var retentionTable = $('#retention-table').DataTable({
        paging: false,
        dom: 'Bfrtip',
        buttons: [{
            text: '{{ __("view.add_row") }}',
            action: function(e, dt, node, config){
                addRowTax();
            }
        }]
    });
    $('#retention-table tbody').on('changed.bs.select', 'select[id *= tax]', function(){
        var _token = $('input[name = "_token"]').val();
        var reference = $(this);
        var id = reference.closest('tr').find('select[id *= tax]').val();
        if (id != '') {
            $.ajax({
                url: "{{ route('retentionTaxes.taxDescriptions') }}",
                method: "POST",
                data: {
                    _token: _token,
                    id: id,
                },
                success: function(result) {
                    var options = '';
                    var taxDescriptions = JSON.parse(result);
                    for (var i = 0; i < taxDescriptions.length; i++) {
                        options += '<option value="' + taxDescriptions[i]['id'] + '">' + taxDescriptions[i]['code'] + ' - ' + taxDescriptions[i]['description'] + '</option>';
                    }
                    reference.closest('tr').find('select[id *= description]').html(options).selectpicker('refresh');
                    $("select[id ~= 'description[]']").selectpicker();
                    @if($action === 'edit' || $action === 'draft')
                        if ('description' in voucher) {
                            if (voucher['description'].length > 0) {
                                reference.closest('tr').find('select[id *= description]').selectpicker('val', voucher['description'][0]);
                                voucher['description'].shift();
                            }
                        }
                    @endif
                    updateTotal();
                }
            });
        }
    });
    $('#retention-table tbody').on('changed.bs.select', 'select[id *= description]', function(){
        var _token = $('input[name = "_token"]').val();
        var reference = $(this);
        var id = reference.closest('tr').find('select[id *= description]').val();
        if (id != '') {
            $.ajax({
                url: "{{ route('retentionTaxDescriptions.taxDescription') }}",
                method: "POST",
                data: {
                    _token: _token,
                    id: id,
                },
                success: function(result) {
                    var options = '';
                    var taxDescription = JSON.parse(result);
                    reference.closest('tr').find('input[id *= value]').attr({
                        "min" : taxDescription[0]['min_rate'],
                        "max" : taxDescription[0]['max_rate'],
                        "value" : taxDescription[0]['rate']
                    });
                    @if($action === 'edit' || $action === 'draft')
                        reference.closest('tr').find('input[id *= value]').val(voucher['value'][0]);
                        reference.closest('tr').find('input[id *= tax_base]').val(voucher['tax_base'][0]);
                        voucher['value'].shift();
                        voucher['tax_base'].shift();
                    @endif
                    updateRetainedValue(reference);
                }
            });
        }
    });
    function updateRetainedValue(reference) {
        var value = Number(reference.closest('tr').find('input[id *= value]').val());
        value = isNaN(value) ? 0.0 : value;
        var taxBase = Number(reference.closest('tr').find('input[id *= tax_base]').val())
        taxBase = isNaN(taxBase) ? 0.0 : taxBase;
        retainedValue = value * taxBase / 100.0;
        reference.closest('tr').find('input[id *= value]').val(value.toFixed(2));
        reference.closest('tr').find('input[id *= tax_base]').val(taxBase.toFixed(2));
        reference.closest('tr').find('input[id *= retained-value]').val(retainedValue.toFixed(2));
        updateTotal();
    }
    function updateTotal() {
        var retained_values = $.map($('input[id *= retained-value]'), function(option) {
            return Number(option.value);
        });
        var total_retained = retained_values.reduce(function(a, b) {
            return a + b;
        }, 0.0);
        $('#retention_total').val(total_retained.toFixed(2));
    }
    $('#retention-table tbody').on('change', 'input[id *= value]', function(){
        updateRetainedValue($(this));
    });
    $('#retention-table tbody').on('change', 'input[id *= tax_base]', function(){
        updateRetainedValue($(this));
    });
    $('#retention-table tbody').on('click', 'button.btn.btn-danger.btn-sm', function(){
        retentionTable
            .row($(this).parents('tr'))
            .remove()
            .draw();
        updateTotal();
    });
    @if($action === 'edit' || $action === 'draft')
        if ('tax' in voucher) {
            for (var i = 0; i < voucher['tax'].length; i++) {
                addRowTax();
            }
        }
    @endif
    $('#issue_date').change(function() {
        var issueDate = new Date($('#issue_date').val());
        if (!isNaN(issueDate.getTime())) {
            $('#fiscal_period').val(issueDate.getUTCFullYear() + '/' + ("00" + (issueDate.getUTCMonth() + 1)).slice(-2));
        }
    });
    var issueDate = new Date($('#issue_date').val());
    if (!isNaN(issueDate.getTime())) {
        $('#fiscal_period').val(issueDate.getUTCFullYear() + '/' + ("00" + (issueDate.getUTCMonth() + 1)).slice(-2));
    }
    $('#issue_date_support_document').datepicker({
        autoclose: true,
        todayBtn: 'linked',
        todayHighlight: true,
        endDate: '0d',
        language: '{{ str_replace('_', '-', app()->getLocale()) }}',
        format: 'yyyy-mm-dd',
        daysOfWeekHighlighted: "0,6"
    });
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
    @endif
    $('#voucher_type_support_document').selectpicker();
    @if($action === 'edit' || $action === 'draft')
        $('#voucher_type_support_document').selectpicker('val', voucher['voucher_type_support_document']);
        $('#supportdocument_establishment').val(Number(voucher['supportdocument_establishment']));
        $('#supportdocument_emissionpoint').val(Number(voucher['supportdocument_emissionpoint']));
        $('#supportdocument_sequential').val(Number(voucher['supportdocument_sequential']));
        $('#issue_date_support_document').datepicker('update', voucher['issue_date_support_document']);
    @endif
});
</script>
