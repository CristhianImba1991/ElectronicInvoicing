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
        format: 'yyyy/mm/dd',
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
        $('#issue_date').datepicker('update', "{{ $voucher->issue_date }}");
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
                    success: function(result) {
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
                $('#voucher-form').attr('action', "{{ route('vouchers.store', \ElectronicInvoicing\StaticClasses\VoucherStates::DRAFT) }}").submit();
                //submit("{{ route('vouchers.store', \ElectronicInvoicing\StaticClasses\VoucherStates::DRAFT) }}");
            });
            $('#save').on('click', function() {
                //$('#voucher-form').attr('action', "{{ route('vouchers.store', \ElectronicInvoicing\StaticClasses\VoucherStates::SAVED) }}").submit();
                submit("{{ route('vouchers.store', \ElectronicInvoicing\StaticClasses\VoucherStates::SAVED) }}");
            });
        @endcan
        @can('send_vouchers')
            $('#accept').on('click', function() {
                //$('#voucher-form').attr('action', "{{ route('vouchers.store', \ElectronicInvoicing\StaticClasses\VoucherStates::ACCEPTED) }}").submit();
                submit("{{ route('vouchers.store', \ElectronicInvoicing\StaticClasses\VoucherStates::ACCEPTED) }}");
            });
            $('#send').on('click', function() {
                //$('#voucher-form').attr('action', "{{ route('vouchers.store', \ElectronicInvoicing\StaticClasses\VoucherStates::SENDED) }}").submit();
                submit("{{ route('vouchers.store', \ElectronicInvoicing\StaticClasses\VoucherStates::SENDED) }}");
            });
        @endcan
    @elseif($action === 'edit')
        @can('create_vouchers')
            $('#save').on('click', function() {
                $('#voucher-form').attr('action', "{{ route('vouchers.update', [\ElectronicInvoicing\StaticClasses\VoucherStates::SAVED, $voucher->id]) }}").submit();
            });
        @endcan
        @can('send_vouchers')
            $('#accept').on('click', function() {
                $('#voucher-form').attr('action', "{{ route('vouchers.update', [\ElectronicInvoicing\StaticClasses\VoucherStates::ACCEPTED, $voucher->id]) }}").submit();
            });
            $('#send').on('click', function() {
                $('#voucher-form').attr('action', "{{ route('vouchers.update', [\ElectronicInvoicing\StaticClasses\VoucherStates::SENDED, $voucher->id]) }}").submit();
            });
        @endcan
    @elseif($action === 'draft')
        @can('create_vouchers')
            $('#draft').on('click', function() {
                $('#voucher-form').attr('action', "{{ url('/manage/vouchers')  . '/' .  \ElectronicInvoicing\StaticClasses\VoucherStates::DRAFT . '/update_draft/' }}" + draftVoucher['id']).submit();
            });
            $('#save').on('click', function() {
                $('#voucher-form').attr('action', "{{ url('/manage/vouchers')  . '/' .  \ElectronicInvoicing\StaticClasses\VoucherStates::SAVED . '/update_draft/' }}" + draftVoucher['id']).submit();
            });
        @endcan
        @can('send_vouchers')
            $('#accept').on('click', function() {
                $('#voucher-form').attr('action', "{{ url('/manage/vouchers')  . '/' .  \ElectronicInvoicing\StaticClasses\VoucherStates::ACCEPTED . '/update_draft/' }}" + draftVoucher['id']).submit();
            });
            $('#send').on('click', function() {
                $('#voucher-form').attr('action', "{{ url('/manage/vouchers')  . '/' .  \ElectronicInvoicing\StaticClasses\VoucherStates::SENDED . '/update_draft/' }}" + draftVoucher['id']).submit();
            });
        @endcan
    @endif
});
</script>
