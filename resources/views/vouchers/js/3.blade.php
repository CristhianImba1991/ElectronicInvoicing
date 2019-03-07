<script type="text/javascript">
$(document).ready(function(){
    function addRowDebit() {
        debitNoteTable.row.add([
            '<input class="form-control" type="text" id="debit_reason[]" name="debit_reason[]" value="">',
            '<input class="form-control" type="text" id="debit_value[]" name="debit_value[]" value="0">',
            '<button type="button" class="btn btn-danger btn-sm">&times;</button>',
        ]).draw(false);
    }
    var debitNoteTable = $('#debitNote-table').DataTable({
        paging: false,
        dom: 'Bfrtip',
        buttons: [{
            text: 'Add row',
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
            $(this).val(Number($(this).val()).toFixed(2));
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
    @if($action === 'edit' || $action === 'draft')
        if ('additionaldetail_name' in voucher) {
            for (var i = 0; i < voucher['additionaldetail_name'].length; i++) {
                addRowAdditionalDetail();
            }
        }
        $('#extra_detail').val(voucher['extra_detail']);
    @endif
    $("#iva_tax").selectpicker('render');
    $('#iva_tax').change(function() {
        updateTotal();
    });
});
</script>