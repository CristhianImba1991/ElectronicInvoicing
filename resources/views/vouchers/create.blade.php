@extends('layouts.app')

@section('scripts')
<script src="{{ asset('js/bootstrap-select.min.js') }}"></script>
<script src="{{ asset('js/jquery.dataTables.min.js') }}"></script>
<script src="{{ asset('js/bootstrap-datepicker.min.js') }}"></script>
<script src="{{ asset('js/bootstrap-tokenfield.min.js') }}"></script>
@include('vouchers.js.create_edit', ['action' => 'create'])
@endsection

@section('styles')
<link rel="stylesheet" href="{{ asset('css/bootstrap-select.min.css') }}">
<link rel="stylesheet" href="{{ asset('css/jquery.dataTables.min.css') }}">
<link rel="stylesheet" href="{{ asset('css/bootstrap-datepicker3.min.css') }}">
<link rel="stylesheet" href="{{ asset('css/bootstrap-tokenfield.min.css') }}">
<link rel="stylesheet" href="{{ asset('css/tokenfield-typeahead.min.css') }}">
@endsection

@section('content')
<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-12">
            <div class="card">
                <div class="card-header">
                    {{ trans_choice(__('view.new_model', ['model' => trans_choice(__('view.voucher'), 0)]), 0) }}
                    <a href="{{ redirect()->getUrlGenerator()->previous() }}" class="btn btn-sm btn-secondary float-right">{{ __('view.cancel') }}</a>
                </div>

                <form id="voucher-form" method="post">
                    {{ csrf_field() }}

                    <div class="card-body">

                        <div class="row">
                            <div class="col-sm-6 text-center">
                                <div class="card mb-3">
                                    <div class="card-body">
                                        <img id="company_logo" name="company_logo" class="img-fluid img-thumbnail" src="" alt="{{ __('view.logo') }}">
                                    </div>
                                </div>
                            </div>
                            <div class="col-sm-6">
                                <div class="card mb-3">
                                    <div class="card-body">
                                        <h5 class="card-title">1. {{ ucfirst(trans_choice(__('view.company'), 0)) }}</h5>
                                        <div class="form-group">
                                            <label for="company">{{ ucfirst(trans_choice(__('view.company'), 0)) }}</label>
                                            <select class="form-control selectpicker input-lg dynamic" id="company" name="company" data-live-search="true" data-dependent="branch" title="{{ trans_choice(__('view.select_a_model', ['model' => trans_choice(__('view.company'), 0)]), 1) }}">
                                                @foreach($companies as $company)
                                                    <option value="{{ $company->id }}">{{ $company->tradename }} - {{ $company->social_reason }}</option>
                                                @endforeach
                                            </select>
                                        </div>
                                        <div class="form-group">
                                            <label for="branch">{{ ucfirst(trans_choice(__('view.branch'), 0)) }}</label>
                                            <select class="form-control selectpicker input-lg dynamic" id="branch" name="branch" data-live-search="true" data-dependent="branch" title="{{ trans_choice(__('view.select_a_model', ['model' => trans_choice(__('view.branch'), 0)]), 1) }}">

                                            </select>
                                        </div>
                                        <div class="form-group">
                                            <label for="emission_point">{{ ucfirst(trans_choice(__('view.emission_point'), 0)) }}</label>
                                            <select class="form-control selectpicker input-lg" id="emission_point" name="emission_point" data-live-search="true" data-dependent="branch" title="{{ trans_choice(__('view.select_a_model', ['model' => trans_choice(__('view.emission_point'), 0)]), 0) }}">

                                            </select>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="col-sm-12">
                                <div class="card mb-3">
                                    <div class="card-body">
                                        <h5 class="card-title">2. {{ trans_choice(__('view.model_information', ['model' => trans_choice(__('view.company'), 0)]), 1) }}</h5>
                                        <div class="form-group">
                                            <label for="company_ruc">{{ __('view.ruc') }}</label>
                                            <input class="form-control" type="text" id="company_ruc" name="company_ruc" value="" readonly>
                                        </div>
                                        <div class="form-group">
                                            <label for="company_name">{{ __('view.tradename') }} - {{ __('view.social_reason') }}</label>
                                            <input class="form-control" type="text" id="company_name" name="company_name" value="" readonly>
                                        </div>
                                        <div class="form-group">
                                            <label for="company_address">{{ trans_choice(__('view.model_address', ['model' => trans_choice(__('view.company'), 0)]), 1) }}</label>
                                            <input class="form-control" type="text" id="company_address" name="company_address" value="" readonly>
                                        </div>
                                        <div class="form-group">
                                            <label for="branch_address">{{ trans_choice(__('view.model_address', ['model' => trans_choice(__('view.branch'), 0)]), 1) }}</label>
                                            <input class="form-control" type="text" id="branch_address" name="branch_address" value="" readonly>
                                        </div>
                                        <div class="form-group">
                                            <label for="company_special_contributor">{{ __('view.special_contributor') }}</label>
                                            <input class="form-control" type="text" id="company_special_contributor" name="company_special_contributor" value="" readonly>
                                        </div>
                                        <div class="form-group">
                                            <label for="company_keep_accounting">{{ __('view.keep_accounting') }}</label>
                                            <input class="form-control" type="text" id="company_keep_accounting" name="company_keep_accounting" value="" readonly>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="col-sm-12">
                                <div class="card mb-3">
                                    <div class="card-body">
                                        <h5 class="card-title">
                                            3. {{ trans_choice(__('view.model_information', ['model' => trans_choice(__('view.customer'), 0)]), 0) }}
                                            @if(auth()->user()->can('create_customers'))
                                                <button type="button" class="btn btn-sm btn-success float-right" data-toggle="modal" data-target="#customerModal">{{ trans_choice(__('view.new_model', ['model' => trans_choice(__('view.customer'), 0)]), 0) }}</button>
                                            @endif
                                        </h5>
                                        <div class="form-group">
                                            <label for="customer">{{ ucfirst(trans_choice(__('view.customer'), 0)) }}</label>
                                            <select class="form-control selectpicker input-lg dynamic" id="customer" name="customer" data-live-search="true" data-dependent="branch" title="{{ trans_choice(__('view.select_a_model', ['model' => trans_choice(__('view.customer'), 0)]), 0) }}">

                                            </select>
                                        </div>
                                        <div class="form-group">
                                            <label for="customer_identification">{{ __('view.identification') }}</label>
                                            <input class="form-control" type="text" id="customer_identification" name="customer_identification" value="" readonly>
                                        </div>
                                        <div class="form-group">
                                            <label for="customer_address">{{ __('view.address') }}</label>
                                            <input class="form-control" type="text" id="customer_address" name="customer_address" value="" readonly>
                                        </div>
                                        <div class="form-group">
                                            <label for="customer_email">{{ __('view.email') }}</label>
                                            <input class="form-control" type="text" id="customer_email" name="customer_email" value="" readonly>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="col-sm-12">
                                <div class="card mb-3">
                                    <div class="card-body">
                                        <h5 class="card-title">4. {{ trans_choice(__('view.model_information', ['model' => trans_choice(__('view.voucher'), 0)]), 0) }}</h5>
                                        <div class="form-group">
                                            <label for="currency">{{ __('view.currency') }}</label>
                                            <select class="form-control selectpicker" id="currency" name="currency" data-live-search="true" data-dependent="branch" title="{{ trans_choice(__('view.select_a_model', ['model' => strtolower(__('view.currency'))]), 1) }}">
                                                @foreach($currencies as $currency)
                                                    <option value="{{ $currency->id }}">{{ $currency->name }}</option>
                                                @endforeach
                                            </select>
                                        </div>
                                        <div class="form-group">
                                            <label for="issue_date">{{ __('view.issue_date') }}</label>
                                            <input class="form-control" id="issue_date" name="issue_date" readonly>
                                        </div>
                                        <div class="form-group">
                                            <label for="environment">{{ __('view.environment') }}</label>
                                            <select class="form-control selectpicker" id="environment" name="environment" data-live-search="true" data-dependent="branch" title="{{ trans_choice(__('view.select_a_model', ['model' => strtolower(__('view.environment'))]), 0) }}">
                                                @foreach($environments as $environment)
                                                    <option value="{{ $environment->id }}">{{ $environment->name }}</option>
                                                @endforeach
                                            </select>
                                        </div>
                                        <div class="form-group">
                                            <label for="voucher_type">{{ __('view.voucher_type') }}</label>
                                            <select class="form-control selectpicker" id="voucher_type" name="voucher_type" data-live-search="true" data-dependent="branch" title="{{ trans_choice(__('view.select_a_model', ['model' => strtolower(__('view.voucher_type'))]), 0) }}">
                                                @foreach($voucherTypes as $voucherType)
                                                    <option value="{{ $voucherType->id }}">{{ $voucherType->name }}</option>
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
                            <button type="button" id="draft" class="btn btn-sm btn-secondary">{{ __('view.draft') }}</button>
                            <button type="button" id="save" class="btn btn-sm btn-info">{{ __('view.save') }}</button>
                        @endcan
                        @can('send_vouchers')
                            <button type="button" id="accept" class="btn btn-sm btn-primary">{{ __('view.save_and_accept') }}</button>
                            <button type="button" id="send" class="btn btn-sm btn-success">{{ __('view.save_accept_and_send') }}</button>
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
                    <strong>{{ trans_choice(__('view.new_model', ['model' => trans_choice(__('view.customer'), 0)]), 0) }}</strong>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <form id="customer_create">
                    {{ csrf_field() }}
                    <div class="modal-body">
                        <div class="form-group">
                            <label for="customer_company">{{ ucfirst(trans_choice(__('view.company'), 0)) }}</label>
                            <select class="form-control selectpicker" id="customer_company" name="company" data-live-search="true" data-dependent="branch" title="{{ trans_choice(__('view.select_a_model', ['model' => trans_choice(__('view.company'), 0)]), 1) }}">
                                @foreach($companies as $company)
                                    <option value="{{ $company->id }}">{{ $company->tradename }} - {{ $company->social_reason }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="form-group">
                            <label for="customer_identification_type">{{ __('view.identification_type') }}</label>
                            <select class="form-control selectpicker" id="customer_identification_type" name="identification_type" data-live-search="true" title="{{ trans_choice(__('view.select_a_model', ['model' => strtolower(__('view.identification_type'))]), 0) }}">
                                @foreach($identificationTypes as $identificationType)
                                    <option value="{{ $identificationType->id }}" >{{ $identificationType->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="form-group">
                            <label for="customer_identification">{{ __('view.identification') }}</label>
                            <input class="form-control" type="text" id="customer_identification" name="identification" value="">
                        </div>
                        <div class="form-group">
                            <label for="customer_social_reason">{{ __('view.social_reason') }}</label>
                            <input class="form-control" type="text" id="customer_social_reason" name="social_reason" value="">
                        </div>
                        <div class="form-group">
                            <label for="customer_name">{{ __('view.address') }}</label>
                            <input class="form-control" type="text" id="customer_address" name="address" value="">
                        </div>
                        <div class="form-group">
                            <label for="customer_phone">{{ __('view.phone') }}</label>
                            <input class="form-control" type="text" id="customer_phone" name="phone" value="">
                        </div>
                        <div class="form-group">
                            <label for="customer_email">{{ __('view.email') }}</label>
                            <input class="form-control" type="email" id="customer_email" name="email" value="" multiple>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-sm btn-secondary" data-dismiss="modal">{{ __('view.close') }}</button>
                        <button id="submit_customer" type="button" class="btn btn-sm btn-success">{{ __('view.add') }}</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
@endif

@include('layouts.validation')

@if(auth()->user()->can('create_vouchers') || auth()->user()->can('send_vouchers'))
    <div class="modal fade" tabindex="-1" role="dialog" id="loadingModal">
        <div class="modal-dialog modal-dialog-centered" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <strong>{{ __('view.processing_voucher') }}</strong>
                </div>
                <div class="modal-body">
                    <p>{{ __('view.please_wait_while_your_voucher_is_processed') }}</p>
                </div>
            </div>
        </div>
    </div>
@endif
@endsection
