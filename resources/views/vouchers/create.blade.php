@extends('layouts.app')

@section('scripts')
<script src="{{ asset('js/bootstrap-select.min.js') }}"></script>
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
                        }
                    })
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
            })
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
});
</script>
@endsection

@section('styles')
<link rel="stylesheet" href="{{ asset('css/bootstrap-select.min.css') }}">
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

                <form action="#" method="post">
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
                                        <h5 class="card-title">3. Customer information</h5>
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
                                            <label for="environment">Environment</label>
                                            <select class="form-control selectpicker" id="environment" name="environment" data-live-search="true" data-dependent="branch" title="Select a environment ...">
                                                @foreach($environments as $environment)
                                                    <option value="{{ $environment->id }}" {{ $environment->id === old('environment') ? "selected" : "" }}>{{ $environment->name }}</option>
                                                @endforeach
                                            </select>
                                        </div>
                                        <div class="form-group">
                                            <label for="voucher_type">Voucher type</label>
                                            <select class="form-control selectpicker" id="voucher_type" name="voucher_type" data-live-search="true" data-dependent="branch" title="Select a voucher_type ...">
                                                @foreach($voucherTypes as $voucherType)
                                                    <option value="{{ $voucherType->id }}" {{ $voucherType->id === old('voucher_type') ? "selected" : "" }}>{{ $voucherType->name }}</option>
                                                @endforeach
                                            </select>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                    </div>

                    <div class="card-footer">
                        <button type="submit" class="btn btn-sm btn-success">Add</button>
                    </div>

                </form>

            </div>
        </div>
    </div>
</div>
@endsection
