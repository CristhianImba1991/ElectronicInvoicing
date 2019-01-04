@extends('layouts.app')

@section('scripts')
<script src="{{ asset('js/bootstrap-select.min.js') }}"></script>
<script src="{{ asset('js/jquery.dataTables.min.js') }}"></script>
<script src="{{ asset('js/bootstrap-datepicker.min.js') }}"></script>
@include('vouchers.js.create_edit', ['action' => 'edit'])
@endsection

@section('styles')
<link rel="stylesheet" href="{{ asset('css/bootstrap-select.min.css') }}">
<link rel="stylesheet" href="{{ asset('css/jquery.dataTables.min.css') }}">
<link rel="stylesheet" href="{{ asset('css/bootstrap-datepicker3.min.css') }}">
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
                                            <label for="currency">Currency</label>
                                            <select class="form-control selectpicker" id="currency" name="currency" data-live-search="true" data-dependent="branch" title="Select a currency ...">
                                                @foreach($currencies as $currency)
                                                    <option value="{{ $currency->id }}" {{ $currency->id === old('currency') ? "selected" : "" }}>{{ $currency->name }}</option>
                                                @endforeach
                                            </select>
                                        </div>
                                        <div class="form-group">
                                            <label for="issue_date">Issue date</label>
                                            <input class="form-control" id="issue_date" name="issue_date" readonly>
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
                            <button type="button" id="draft" class="btn btn-sm btn-secondary">Draft</button>
                            <button type="button" id="save" class="btn btn-sm btn-info">Save</button>
                        @endcan
                        @can('send_vouchers')
                            <button type="button" id="accept" class="btn btn-sm btn-primary">Save and accept</button>
                            <button type="button" id="send" class="btn btn-sm btn-success">Save, accept and send</button>
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
                <form id="customer_create">
                    {{ csrf_field() }}
                    <div class="modal-body">
                        <div class="form-group">
                            <label for="customer_company">Company</label>
                            <select class="form-control selectpicker" id="customer_company" name="company" data-live-search="true" data-dependent="branch" title="Select a company ...">
                                @foreach($companies as $company)
                                    <option value="{{ $company->id }}">{{ $company->tradename }} - {{ $company->social_reason }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="form-group">
                            <label for="customer_identification_type">Identification type</label>
                            <select class="form-control selectpicker" id="customer_identification_type" name="identification_type" data-live-search="true" title="Select a identification type ...">
                                @foreach($identificationTypes as $identificationType)
                                    <option value="{{ $identificationType->id }}" >{{ $identificationType->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="form-group">
                            <label for="customer_identification">Identification</label>
                            <input class="form-control" type="text" id="customer_identification" name="identification" value="">
                        </div>
                        <div class="form-group">
                            <label for="customer_social_reason">Social reason</label>
                            <input class="form-control" type="text" id="customer_social_reason" name="social_reason" value="">
                        </div>
                        <div class="form-group">
                            <label for="customer_name">Address</label>
                            <input class="form-control" type="text" id="customer_address" name="address" value="">
                        </div>
                        <div class="form-group">
                            <label for="customer_phone">Phone</label>
                            <input class="form-control" type="text" id="customer_phone" name="phone" value="">
                        </div>
                        <div class="form-group">
                            <label for="customer_email">Email</label>
                            <input class="form-control" type="email" id="customer_email" name="email" value="" multiple>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-sm btn-secondary" data-dismiss="modal">Close</button>
                        <button id="submit_customer" type="button" class="btn btn-sm btn-success">Add</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
@endif

@include('layouts.validation')
@endsection
