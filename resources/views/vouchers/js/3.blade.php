<script type="text/javascript">
$(document).ready(function(){
    @if($action === 'draft')
        var voucher = @json($draftVoucher);
    @elseif($action === 'edit')
        var voucher = {
            "debit_reason": @json(\ElectronicInvoicing\DebitNoteTax::where('voucher_id', $voucher->id)->first()->debitNotes()->get()->pluck('reason')),
            "debit_value": @json(\ElectronicInvoicing\DebitNoteTax::where('voucher_id', $voucher->id)->first()->debitNotes()->get()->pluck('value')),
            "paymentMethod": @json($voucher->payments()->get()->pluck('payment_method_id')),
            "paymentMethod_value": @json($voucher->payments()->get()->pluck('total')),
            "paymentMethod_timeunit": @json($voucher->payments()->get()->pluck('time_unit_id')),
            "paymentMethod_term": @json($voucher->payments()->get()->pluck('term')),
            "supportdocument_establishment": @json($voucher->support_document !== NULL ? substr($voucher->support_document, 10, 3) : ''),
            "supportdocument_emissionpoint": @json($voucher->support_document !== NULL ? substr($voucher->support_document, 13, 3) : ''),
            "supportdocument_sequential": @json($voucher->support_document !== NULL ? substr($voucher->support_document, 16, 9) : ''),
            "issue_date_support_document": @json(substr($voucher->support_document, 4, 4)) + '/' + @json(substr($voucher->support_document, 2, 2)) + '/' + @json(substr($voucher->support_document, 0, 2)),
            "additionaldetail_name": @json($voucher->additionalFields()->get()->pluck('name')),
            "additionaldetail_value": @json($voucher->additionalFields()->get()->pluck('value')),
            "extra_detail": @json($voucher->extra_detail),
            "iva_tax": @json(\ElectronicInvoicing\DebitNoteTax::where('voucher_id', $voucher->id)->first()->percentage_code),
        };
    @endif
    function addRowDebit() {
        debitNoteTable.row.add([
            '<input class="form-control" type="text" id="debit_reason[]" name="debit_reason[]" value="">',
            '<input class="form-control" type="text" id="debit_value[]" name="debit_value[]" value="0.00">',
            '<button type="button" class="btn btn-danger btn-sm">&times;</button>',
        ]).draw(false);
        @if($action === 'edit' || $action === 'draft')
            if ('debit_reason' in voucher) {
                if ($("input[id ~= 'debit_reason[]']").length == voucher['debit_reason'].length && voucher['debit_reason'].length > 0) {
                    $("input[id ~= 'debit_reason[]']").each(function() {
                        $(this).val(voucher['debit_reason'][0]);
                        voucher['debit_reason'].shift();
                    });
                }
                if ($("input[id ~= 'debit_value[]']").length == voucher['debit_value'].length && voucher['debit_value'].length > 0) {
                    $("input[id ~= 'debit_value[]']").each(function() {
                        $(this).val(voucher['debit_value'][0]);
                        voucher['debit_value'].shift();
                    });
                }
            }
        @endif
    }
    var debitNoteTable = $('#debitNote-table').DataTable({
        paging: false,
        dom: 'Bfrtip',
        buttons: [{
            text: '{{ __("view.add_row") }}',
            action: function(e, dt, node, config){
                addRowDebit();
            }
        }]
    });
    function updateTotal() {
        var value = $.map($('input[id *= debit_value]'), function(option) {
            return Number(option.value);
        });
        var ivaTax = $('select[id = iva_tax]').val();
        var _token = $('input[name = "_token"]').val();
        if (ivaTax !== '') {
            $.ajax({
                url: "{{ route('ivataxes') }}",
                method: "POST",
                data: {
                    _token: _token,
                    id: ivaTax,
                },
                success: function(result) {
                    var tax = JSON.parse(result);
                    var ivaSubtotal = 0.0;
                    var iva0Subtotal = 0.0;
                    var notSubjectIvaSubtotal = 0.0;
                    var exemptIvaSubtotal = 0.0;
                    var partialTotal = 0.0;
                    var iceValue = 0.0;
                    var irbpnrValue = 0.0;
                    var ivaValue = 0.0;
                    for (var i = 0; i < value.length; i++) {
                        partialTotal += value[i];
                    }
                    switch (ivaTax) {
                        case '1': iva0Subtotal = partialTotal; break;
                        case '2': case '3':
                            ivaSubtotal = partialTotal;
                            ivaValue = partialTotal * Number(tax['rate']) / 100.0;
                            break;
                        case '4': notSubjectIvaSubtotal = partialTotal; break;
                        case '5': exemptIvaSubtotal = partialTotal; break;
                    }
                    var subtotal = iva0Subtotal + ivaSubtotal + notSubjectIvaSubtotal + exemptIvaSubtotal;
                    var total = subtotal + iceValue + irbpnrValue + ivaValue;
                    $('#iva0subtotal').val(iva0Subtotal.toFixed(2));
                    $('#ivasubtotal').val(ivaSubtotal.toFixed(2));
                    $('#notsubjectivasubtotal').val(notSubjectIvaSubtotal.toFixed(2));
                    $('#exemptivasubtotal').val(exemptIvaSubtotal.toFixed(2));
                    $('#subtotal').val(subtotal.toFixed(2));
                    //$('#icevalue').val(iceValue.toFixed(2));
                    //$('#irbpnrvalue').val(irbpnrValue.toFixed(2));
                    $('#ivavalue').val(ivaValue.toFixed(2));
                    $('#total').val(total.toFixed(2));
                }
            });
        }
    }
    $('#debitNote-table tbody').on('change', 'input[id *= debit_value]', function(){
        $('input[id *= debit_value]').each(function() {
            var value = Number($(this).val());
            value = isNaN(value) ? 0.00 : value;
            $(this).val(value.toFixed(2));
        });
        updateTotal();
    });
    $('#debitNote-table tbody').on('click', 'button.btn.btn-danger.btn-sm', function(){
        debitNoteTable
            .row($(this).parents('tr'))
            .remove()
            .draw();
        updateTotal();
    });
    @if($action === 'edit' || $action === 'draft')
        if ('debit_reason' in voucher) {
            for (var i = 0; i < voucher['debit_reason'].length; i++) {
                addRowDebit();
            }
        }
    @endif
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
    $('#issue_date_support_document').datepicker({
        autoclose: true,
        todayBtn: 'linked',
        todayHighlight: true,
        endDate: '0d',
        format: 'yyyy/mm/dd',
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
                    var emails = customer[0]['email'].split(',');
                    var emailsString = '';
                    for (var i = 0; i < emails.length; i++) {
                        emailsString += (i === 0 ? '(P) ' : ', ') + emails[i];
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
    @if($action === 'edit' || $action === 'draft')
        $('#supportdocument_establishment').val(Number(voucher['supportdocument_establishment']));
        $('#supportdocument_emissionpoint').val(Number(voucher['supportdocument_emissionpoint']));
        $('#supportdocument_sequential').val(Number(voucher['supportdocument_sequential']));
        $('#issue_date_support_document').datepicker('update', voucher['issue_date_support_document']);
        $('#reason').val(voucher['reason']);
    @endif
    $("#iva_tax").selectpicker('render');
    $('#iva_tax').change(function() {
        updateTotal();
    });
    @if($action === 'edit' || $action === 'draft')
        $('#iva_tax').selectpicker('val', voucher['iva_tax']);
    @endif
});
</script>
