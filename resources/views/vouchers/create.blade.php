@extends('layouts.app')

@section('scripts')
<script src="{{ asset('js/bootstrap-select.min.js') }}"></script>
<script src="{{ asset('js/jquery.dataTables.min.js') }}"></script>
<script type="text/javascript">
$(document).ready(function(){
    $('#company').change(function() {
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
                    });
                    $('#invoice-table').DataTable().clear().draw();
                    $('#paymentmethod-table').DataTable().clear().draw();
                }
            })
        }
    });
    $('#branch').change(function() {
        if($(this).val() != '') {
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
            });
            $('#invoice-table').DataTable().clear().draw();
            $('#paymentmethod-table').DataTable().clear().draw();
        }
    });
    $('#customer').change(function() {
        if($(this).val() != '') {
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
    $('#environment').selectpicker('val', 2);
    $('#voucher_type').change(function() {
        if($(this).val() != '') {
            $("#voucher-information").html('');
            $.ajax({
                url: "{{ url('/manage/vouchers') }}/" + $(this).val(),
                method: "GET",
                success: function(result) {
                    $("#voucher-information").html(result);
                }
            })
        }
    });
    @can('create_vouchers')
        $('#save').on('click', function() {
            $('#voucher-form').attr('action', "{{ route('vouchers.store', 'save') }}").submit();
        });
        $('#sign').on('click', function() {
            $('#voucher-form').attr('action', "{{ route('vouchers.store', 'sign') }}").submit();
        });
    @endcan
    @can('send_vouchers')
        $('#send').on('click', function() {
            $('#voucher-form').attr('action', "{{ route('vouchers.store', 'send') }}").submit();
        });
    @endcan
});
</script>
@endsection

@section('styles')
<link rel="stylesheet" href="{{ asset('css/bootstrap-select.min.css') }}">
<link href="{{ asset('css/jquery.dataTables.min.css') }}" rel="stylesheet">
@endsection

@section('content')
<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="card">
                <div class="card-header">
                    New voucher
                    <a href="{{ route('home') }}" class="btn btn-sm btn-secondary float-right">Cancel</a>
                </div>

                <form id="voucher-form" method="post">
                    {{ csrf_field() }}

                    <div class="card-body">
                        @if ($errors->count() > 0)
                            <div class="alert alert-danger" role="alert">
                                <h5>The following errors were found:</h5>
                                <ul>
                                    @foreach($errors->all() as $error)
                                        <li>{{ $error }}</li>
                                    @endforeach
                                </ul>
                            </div>
                        @endif

                        <div class="row">
                            <div class="col-sm-6 text-center">
                                <div class="card mb-3">
                                    <div class="card-body">
                                        <img id="company_logo" name="company_logo" class="img-fluid img-thumbnail" src="" alt="Company logo">
                                    </div>
                                </div>
                            </div>
                            <div class="col-sm-6">
                                <div class="card mb-3">
                                    <div class="card-body">
                                        <h5 class="card-title">1. Company</h5>
                                        <div class="form-group">
                                            <label for="company">Company</label>
                                            <select class="form-control selectpicker input-lg dynamic" id="company" name="company" data-live-search="true" data-dependent="branch" title="Select a company ...">
                                                @foreach($companies as $company)
                                                    <option value="{{ $company->id }}" {{ $company->id === old('company') ? "selected" : "" }}>{{ $company->tradename }} - {{ $company->social_reason }}</option>
                                                @endforeach
                                            </select>
                                        </div>
                                        <div class="form-group">
                                            <label for="branch">Branch</label>
                                            <select class="form-control selectpicker input-lg dynamic" id="branch" name="branch" data-live-search="true" data-dependent="branch" title="Select a branch ...">

                                            </select>
                                        </div>
                                        <div class="form-group">
                                            <label for="emission_point">Emission point</label>
                                            <select class="form-control selectpicker input-lg" id="emission_point" name="emission_point" data-live-search="true" data-dependent="branch" title="Select a emission point ...">

                                            </select>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="col-sm-12">
                                <div class="card mb-3">
                                    <div class="card-body">
                                        <h5 class="card-title">2. Company information</h5>
                                        <div class="form-group">
                                            <label for="company_ruc">RUC</label>
                                            <input class="form-control" type="text" id="company_ruc" name="company_ruc" value="" readonly>
                                        </div>
                                        <div class="form-group">
                                            <label for="company_name">Tradename - Social reason</label>
                                            <input class="form-control" type="text" id="company_name" name="company_name" value="" readonly>
                                        </div>
                                        <div class="form-group">
                                            <label for="company_address">Company address</label>
                                            <input class="form-control" type="text" id="company_address" name="company_address" value="" readonly>
                                        </div>
                                        <div class="form-group">
                                            <label for="branch_address">Branch address</label>
                                            <input class="form-control" type="text" id="branch_address" name="branch_address" value="" readonly>
                                        </div>
                                        <div class="form-group">
                                            <label for="company_special_contributor">Special contributor</label>
                                            <input class="form-control" type="text" id="company_special_contributor" name="company_special_contributor" value="" readonly>
                                        </div>
                                        <div class="form-group">
                                            <label for="company_keep_accounting">Keep accounting</label>
                                            <input class="form-control" type="text" id="company_keep_accounting" name="company_keep_accounting" value="" readonly>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="col-sm-12">
                                <div class="card mb-3">
                                    <div class="card-body">
                                        <h5 class="card-title">
                                            3. Customer information
                                            @if(auth()->user()->can('create_customers'))
                                                <button type="button" class="btn btn-sm btn-success float-right" data-toggle="modal" data-target="#customerModal">New customer</button>
                                            @endif
                                        </h5>
                                        <div class="form-group">
                                            <label for="customer">Customer</label>
                                            <select class="form-control selectpicker input-lg dynamic" id="customer" name="customer" data-live-search="true" data-dependent="branch" title="Select a customer ...">

                                            </select>
                                        </div>
                                        <div class="form-group">
                                            <label for="customer_identification">Identification</label>
                                            <input class="form-control" type="text" id="customer_identification" name="customer_identification" value="" readonly>
                                        </div>
                                        <div class="form-group">
                                            <label for="customer_address">Address</label>
                                            <input class="form-control" type="text" id="customer_address" name="customer_address" value="" readonly>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="col-sm-12">
                                <div class="card mb-3">
                                    <div class="card-body">
                                        <h5 class="card-title">4. Voucher information</h5>
                                        <div class="form-group">
                                            <label for="emission">Emission</label>
                                            <input class="form-control" type="text" id="emission" name="emission" value="NORMAL" readonly>
                                        </div>
                                        <div class="form-group">
                                            <label for="currency">Currency</label>
                                            <select class="form-control selectpicker" id="currency" name="currency" data-live-search="true" data-dependent="branch" title="Select a currency ...">
                                                @foreach($currencies as $currency)
                                                    <option value="{{ $currency->id }}" {{ $currency->id === old('currency') ? "selected" : "" }}>{{ $currency->name }}</option>
                                                @endforeach
                                            </select>
                                        </div>
                                        <div class="form-group">
                                            <label for="issue_date">Issue date</label>
                                            <input class="form-control" type="date" id="issue_date" name="issue_date">
                                        </div>
                                        <div class="form-group">
                                            <label for="environment">Environment</label>
                                            <select class="form-control selectpicker" id="environment" name="environment" data-live-search="true" data-dependent="branch" title="Select a environment ...">
                                                @foreach($environments as $environment)
                                                    <option value="{{ $environment->id }}" {{ $environment->id === old('environment') ? "selected" : "" }}>{{ $environment->name }}</option>
                                                @endforeach
                                            </select>
                                        </div>
                                        <div class="form-group">
                                            <label for="voucher_type">Voucher type</label>
                                            <select class="form-control selectpicker" id="voucher_type" name="voucher_type" data-live-search="true" data-dependent="branch" title="Select a voucher type ...">
                                                @foreach($voucherTypes as $voucherType)
                                                    <option value="{{ $voucherType->id }}" {{ $voucherType->id === old('voucher_type') ? "selected" : "" }}>{{ $voucherType->name }}</option>
                                                @endforeach
                                            </select>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div id="voucher-information" class="row">

                        </div>

                    </div>

                    <div class="card-footer">
                        @can('create_vouchers')
                            <button type="button" id="save" class="btn btn-sm btn-dark">Save</button>
                            <button type="button" id="sign" class="btn btn-sm btn-info">Sign</button>
                        @endcan
                        @can('send_vouchers')
                            <button type="button" id="send" class="btn btn-sm btn-primary">Send</button>
                        @endcan
                    </div>

                </form>

            </div>
        </div>
    </div>
</div>

@if(auth()->user()->can('create_customers'))
    <div class="modal fade" tabindex="-1" role="dialog" id="customerModal">
        <div class="modal-dialog modal-dialog-centered" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <strong>New customer</strong>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <form id="customer-create">
                    {{ csrf_field() }}
                    <div class="modal-body">
                        @if ($errors->count() > 0)
                            <div class="alert alert-danger" role="alert">
                                <h5>The following errors were found:</h5>
                                <ul>
                                    @foreach($errors->all() as $error)
                                        <li>{{ $error }}</li>
                                    @endforeach
                                </ul>
                            </div>
                        @endif
                        <div class="form-group">
                            <label for="company">Company</label>
                            <select class="form-control selectpicker" id="company" name="company" data-live-search="true" data-dependent="branch" title="Select a company ...">
                                @foreach($companies as $company)
                                    <option value="{{ $company->id }}" {{ $company->id === old('company') ? "selected" : "" }}>{{ $company->tradename }} - {{ $company->social_reason }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="form-group">
                            <label for="identification_type">Identification type</label>
                            <select class="form-control selectpicker" id="identification_type" name="identification_type" data-live-search="true" title="Select a identification type ...">
                                @foreach($identificationTypes as $identificationType)
                                    <option value="{{ $identificationType->id }}" {{ $identificationType->id === old('identification_type') ? "selected" : "" }}>{{ $identificationType->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="form-group">
                            <label for="identification">Identification</label>
                            <input class="form-control" type="text" id="identification" name="identification" value="{{ old('identification') }}">
                        </div>
                        <div class="form-group">
                            <label for="social_reason">Social reason</label>
                            <input class="form-control" type="text" id="social_reason" name="social_reason" value="{{ old('social_reason') }}">
                        </div>
                        <div class="form-group">
                            <label for="name">Address</label>
                            <input class="form-control" type="text" id="address" name="address" value="{{ old('address') }}">
                        </div>
                        <div class="form-group">
                            <label for="phone">Phone</label>
                            <input class="form-control" type="text" id="phone" name="phone" value="{{ old('phone') }}">
                        </div>
                        <div class="form-group">
                            <label for="email">Email</label>
                            <input class="form-control" type="email" id="email" name="email" value="{{ old('email') }}" multiple>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-sm btn-secondary" data-dismiss="modal">Close</button>
                        <button type="submit" class="btn btn-sm btn-success">Add</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
@endif
@endsection
