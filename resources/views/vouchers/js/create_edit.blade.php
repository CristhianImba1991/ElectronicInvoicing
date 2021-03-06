<script type="text/javascript">
$(document).ready(function(){
    @if($action === 'edit')
        var voucher = {
            "branch": "{{ $voucher->emissionPoint->branch->id }}",
            "emission_point": "{{ $voucher->emissionPoint->id }}",
            "customer": "{{ $voucher->customer->id }}"
        }
    @elseif($action === 'draft')
        var draftVoucher = @json($draftVoucher);
    @endif
    @if($action === 'edit' || $action === 'draft')
        var optionSelected = {
            "branch": false,
            "emission_point": false,
            "customer": false
        };
        function selectOption(option) {
            if (!optionSelected[option]) {
                @if($action === 'edit')
                    $('#' + option).selectpicker('val', voucher[option]);
                @elseif($action === 'draft')
                    $('#' + option).selectpicker('val', draftVoucher[option]);
                @endif
                optionSelected[option] = true;
            }
        }
    @endif
    $('body').on('hidden.bs.modal', function () {
        if($('.modal.show').length > 0) {
            $('body').addClass('modal-open');
        }
    });
    $('#company').change(function() {
        if($(this).val() != '' && $(this).val() != null) {
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
                    var company = '';
                    for (var i = 0; i < branches.length; i++) {
                        options += '<option value="' + branches[i]['id'] + '">' + branches[i]['name'] + '</option>';
                        if (i == 0) {
                            company = branches[i]['company'];
                        }
                    }
                    $("#company_logo").attr("src", "{{ url('storage/logo/images') }}/" + company['logo']);
                    $("#company_ruc").val(company['ruc']);
                    $("#company_name").val(company['tradename'] + " - " + company['social_reason']);
                    $("#company_address").val(company['address']);
                    $("#company_special_contributor").val(company['special_contributor']);
                    $("#company_keep_accounting").val(company['keep_accounting'] === 1 ? 'YES' : 'NO');
                    $("#branch").html(options).selectpicker('refresh');
                    $("#emission_point").html('').selectpicker('refresh');
                    $.ajax({
                        url: "{{ route('companies.customers') }}",
                        method: "POST",
                        data: {
                            _token: _token,
                            id: id,
                        },
                        success: function(result) {
                            var customers = JSON.parse(result);
                            var options = '';
                            for (var i = 0; i < customers.length; i++) {
                                options += '<option value="' + customers[i]['id'] + '">' + customers[i]['social_reason'] + '</option>';
                            }
                            $("#customer").html(options).selectpicker('refresh');
                            $("#customer_identification").val('');
                            $("#customer_address").val('');
                        }
                        @if($action === 'edit' || $action === 'draft')
                            ,
                            complete: function(jqXHR, textStatus){
                                if (textStatus === 'success') {
                                    selectOption('customer');
                                }
                            }
                        @endif
                    });
                    $('#invoice-table').DataTable().clear().draw();
                    $('#paymentmethod-table').DataTable().clear().draw();
                }
                @if($action === 'edit' || $action === 'draft')
                    ,
                    complete: function(jqXHR, textStatus){
                        if (textStatus === 'success') {
                            selectOption('branch');
                        }
                    }
                @endif
            })
        }
    });
    $('#branch').change(function() {
        if($(this).val() != '' && $(this).val() != null) {
            var _token = $('input[name = "_token"]').val();
            var id = $(this).val();
            $.ajax({
                url: "{{ route('branches.emissionPoints') }}",
                method: "POST",
                data: {
                    _token: _token,
                    id: id,
                },
                success: function(result) {
                    var emissionPoints = JSON.parse(result);
                    var options = '';
                    var branch = '';
                    for (var i = 0; i < emissionPoints.length; i++) {
                        options += '<option value="' + emissionPoints[i]['id'] + '">' + emissionPoints[i]['code'] + '</option>';
                        if (i == 0) {
                            branch = emissionPoints[i]['branch'];
                        }
                    }
                    $("#branch_address").val(branch['address']);
                    $("#emission_point").html(options).selectpicker('refresh');
                }
                @if($action === 'edit' || $action === 'draft')
                    ,
                    complete: function(jqXHR, textStatus){
                        if (textStatus === 'success') {
                            selectOption('emission_point');
                        }
                    }
                @endif
            });
            $('#invoice-table').DataTable().clear().draw();
            $('#creditNote-table').DataTable().clear().draw();
            $('#waybill-table').DataTable().clear().draw();
            $('#paymentmethod-table').DataTable().clear().draw();
        }
    });
    @if(auth()->user()->can('create_customers'))
        $("#submit_customer").click(function() {
            $.ajax({
                url: "{{ route('customers.store') }}",
                method: "POST",
                data: $('#customer_create').serialize(),
                success: function(result) {
                    var validator = JSON.parse(result);
                    if (validator['status']) {
                        if ($('#company').val() != '') {
                            $.ajax({
                                url: "{{ route('companies.customers') }}",
                                method: "POST",
                                data: {
                                    _token: $('input[name = "_token"]').val(),
                                    id: $('#company').val(),
                                },
                                success: function(result) {
                                    var customers = JSON.parse(result);
                                    var options = '';
                                    for (var i = 0; i < customers.length; i++) {
                                        options += '<option value="' + customers[i]['id'] + '">' + customers[i]['social_reason'] + '</option>';
                                    }
                                    $("#customer").html(options).selectpicker('refresh');
                                    $("#customer_identification").val('');
                                    $("#customer_address").val('');
                                }
                            });
                        }
                        $('#customerModal').modal('toggle');
                        $('#customer_create').trigger('reset');
                        $('#customer_company').selectpicker('refresh');
                        $('#customer_identification_type').selectpicker('refresh');
                        $('#customerModal input[id = customer_email]').tokenfield('setTokens', []);
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
        $('#customerModal input[id = customer_email]')
            .on('tokenfield:createtoken', function (e) {
                var data = e.attrs.value.split('|')
                e.attrs.value = data[1] || data[0]
                e.attrs.label = data[1] ? data[0] + ' (' + data[1] + ')' : data[0]
            })
            .on('tokenfield:createdtoken', function (e) {
                var re = /\S+@\S+\.\S+/
                var valid = re.test(e.attrs.value)
                if (!valid) {
                    $(e.relatedTarget).addClass('invalid')
                }
            })
            .on('tokenfield:edittoken', function (e) {
                if (e.attrs.label !== e.attrs.value) {
                    var label = e.attrs.label.split(' (')
                    e.attrs.value = label[0] + '|' + e.attrs.value
                }
            })
            .tokenfield({
                beautify: false,
                createTokensOnBlur: true,
                inputType: 'email',
            });
    @endif
    $('#customer').change(function() {
        if($(this).val() != '' && $(this).val() != null) {
            var _token = $('input[name = "_token"]').val();
            var id = $(this).val();
            $.ajax({
                url: "{{ route('customers.customer') }}",
                method: "POST",
                data: {
                    _token: _token,
                    id: id,
                },
                success: function(result) {
                    var customer = JSON.parse(result);
                    $("#customer_identification").val(customer[0]['identification_type']['name'] + ": " + customer[0]['identification']);
                    $("#customer_address").val(customer[0]['address']);
                    if (customer[0]['email'] != null) {
                        var emails = customer[0]['email'].split(',');
                        var emailsString = '';
                        for (var i = 0; i < emails.length; i++) {
                            emailsString += (i === 0 ? '(P) ' : ', ') + emails[i];
                        }
                        $("#customer_email").val(emailsString);
                    }
                    @if($action === 'create')
                        var additionalDetailTable = $('#additionaldetail-table').DataTable();
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
                        if (additionalDetailTable.rows().eq(0) != null) {
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
                    @endif
                }
            })
        }
    });
    $('#currency').selectpicker('val', 1);
    $('#issue_date').datepicker({
        autoclose: true,
        todayBtn: 'linked',
        todayHighlight: true,
        endDate: '0d',
        language: '{{ str_replace("_", "-", app()->getLocale()) }}',
        format: 'yyyy-mm-dd',
        daysOfWeekHighlighted: "0,6"
    });
    $('#environment').selectpicker('val', 2);
    $('#voucher_type').change(function() {
        if($(this).val() != '' && $(this).val() != null) {
            $("#voucher-information").html('');
            $.ajax({
                @if($action === 'create')
                    url: "{{ url('/manage/vouchers') }}/" + $(this).val(),
                @elseif($action === 'edit')
                    url: "{{ url('/voucher') }}/" + $(this).val() + "/edit/{{ $voucher->id }}",
                @elseif($action === 'draft')
                    url: "{{ url('/manage/vouchers') }}/" + $(this).val() + "/draft/" + draftVoucher['id'],
                @endif
                method: "GET",
                success: function(result) {
                    $("#voucher-information").html(result);
                }
            })
        }
    });
    @if($action === 'edit')
        $('#company').selectpicker('val', "{{ $voucher->emissionPoint->branch->company->id }}");
        $('#issue_date').datepicker('update', "{{ \DateTime::createFromFormat('Y-m-d', $voucher->issue_date)->format('Y-m-d') }}");
        $('#environment').selectpicker('val', "{{ $voucher->environment_id }}");
        @if($voucher->voucher_type_id !== null)
            $('#voucher_type').selectpicker('val', "{{ $voucher->voucher_type_id }}");
        @endif
    @elseif($action === 'draft')
        $('#company').selectpicker('val', draftVoucher['company']);
        $('#issue_date').datepicker('update', draftVoucher['issue_date']);
        $('#environment').selectpicker('val', draftVoucher['environment']);
        if (draftVoucher['voucher_type'] != null) {
            $('#voucher_type').selectpicker('val', draftVoucher['voucher_type']);
        }
    @endif
    @if(in_array($action, array('create', 'edit', 'draft')))
        @if(auth()->user()->can('create_vouchers') || auth()->user()->can('send_vouchers'))
            function submit(url) {
                $.ajax({
                    url: url,
                    method: "POST",
                    data: $('#voucher-form').serialize(),
                    beforeSend: function(jqXHR, settings) {
                        $('#loadingModal').modal('show');
                    },
                    success: function(result) {
                        $('#loadingModal').on('shown.bs.modal', function (e) {
                            $("#loadingModal").modal('hide');
                        });
                        var validator = JSON.parse(result);
                        if (validator['status']) {
                            window.location.href = "{{ route('home') }}";
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
            }
        @endif
    @endif
    @if($action === 'create')
        @can('create_vouchers')
            $('#draft').on('click', function() {
                submit("{{ route('vouchers.store_draft') }}");
            });
            $('#save').on('click', function() {
                submit("{{ route('vouchers.store', \ElectronicInvoicing\StaticClasses\VoucherStates::SAVED) }}");
            });
        @endcan
        @can('send_vouchers')
            $('#accept').on('click', function() {
                submit("{{ route('vouchers.store', \ElectronicInvoicing\StaticClasses\VoucherStates::ACCEPTED) }}");
            });
            $('#send').on('click', function() {
                submit("{{ route('vouchers.store', \ElectronicInvoicing\StaticClasses\VoucherStates::SENDED) }}");
            });
        @endcan
    @elseif($action === 'edit')
        @can('create_vouchers')
            $('#save').on('click', function() {
                submit("{{ route('vouchers.update', [\ElectronicInvoicing\StaticClasses\VoucherStates::SAVED, $voucher->id]) }}");
            });
        @endcan
        @can('send_vouchers')
            $('#accept').on('click', function() {
                submit("{{ route('vouchers.update', [\ElectronicInvoicing\StaticClasses\VoucherStates::ACCEPTED, $voucher->id]) }}");
            });
            $('#send').on('click', function() {
                submit("{{ route('vouchers.update', [\ElectronicInvoicing\StaticClasses\VoucherStates::SENDED, $voucher->id]) }}");
            });
        @endcan
    @elseif($action === 'draft')
        @can('create_vouchers')
            $('#draft').on('click', function() {
                submit("{{ url('/manage/vouchers')  . '/' .  \ElectronicInvoicing\StaticClasses\VoucherStates::DRAFT . '/update_draft/' }}" + draftVoucher['id']);
            });
            $('#save').on('click', function() {
                submit("{{ url('/manage/vouchers')  . '/' .  \ElectronicInvoicing\StaticClasses\VoucherStates::SAVED . '/update_draft/' }}" + draftVoucher['id']);
            });
        @endcan
        @can('send_vouchers')
            $('#accept').on('click', function() {
                submit("{{ url('/manage/vouchers')  . '/' .  \ElectronicInvoicing\StaticClasses\VoucherStates::ACCEPTED . '/update_draft/' }}" + draftVoucher['id']);
            });
            $('#send').on('click', function() {
                submit("{{ url('/manage/vouchers')  . '/' .  \ElectronicInvoicing\StaticClasses\VoucherStates::SENDED . '/update_draft/' }}" + draftVoucher['id']);
            });
        @endcan
    @endif
});
</script>
