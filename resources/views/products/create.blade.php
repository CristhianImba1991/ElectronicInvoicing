@extends('layouts.app')

@section('scripts')
<script src="{{ asset('js/bootstrap-select.min.js') }}"></script>
<script type="text/javascript">
    $.noConflict();
    jQuery(document).ready(function($) {
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
                        for (var i = 0; i < branches.length; i++) {
                            options += '<option value="' + branches[i]['id'] + '">' + branches[i]['name'] + '</option>';
                        }
                        $("#branch").html(options).selectpicker('refresh');
                    }
                })
            }
        });
        $("#submit").click(function() {
            $.ajax({
                url: "{{ route('products.store') }}",
                method: "POST",
                data: $('#create_form').serialize(),
                success: function(result) {
                    var validator = JSON.parse(result);
                    if (validator['status']) {
                        window.location.href = "{{ route('products.index') }}";
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
                    {{ __('view.new_model', ['model' => trans_choice(__('view.product'), 0)]) }}
                    <a href="{{ route('products.index') }}" class="btn btn-sm btn-secondary float-right">{{ __('view.cancel') }}</a>
                </div>

                <form id="create_form">
                    {{ csrf_field() }}

                    <div class="card-body">

                        <div class="form-group">
                            <label for="company">{{ ucfirst(trans_choice(__('view.company'), 0)) }}</label>
                            <select class="form-control selectpicker input-lg dynamic" id="company" name="company" data-live-search="true" data-dependent="branch" title="{{ __('view.select_a_company') }}">
                                @foreach($companies as $company)
                                    <option value="{{ $company->id }}">{{ $company->tradename }} - {{ $company->social_reason }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="form-group" id="divbranch">
                            <label for="establishment">{{ ucfirst(trans_choice(__('view.branch'), 0)) }}</label>
                            <select class="form-control selectpicker input-lg" id="branch" name="branch" data-live-search="true" title="{{ __('view.select_a_branch') }}">

                            </select>
                        </div>
                        <div class="form-group">
                            <label for="main_code">{{ __('view.main_code') }}</label>
                            <input type="text" class="form-control" id="main_code" name="main_code" value="">
                        </div>
                        <div class="form-group">
                            <label for="social_reason">{{ __('view.auxiliary_code') }}</label>
                            <input type="text" class="form-control" id="auxiliary_code" name="auxiliary_code"  value="">
                        </div>
                        <div class="form-group">
                            <label for="tradename">{{ __('view.unit_price') }}</label>
                            <input type="number" step="0.01" class="form-control" id="unit_price" name="unit_price"  value="">
                        </div>
                        <div class="form-group">
                            <label for="address">{{ __('view.description') }}</label>
                            <input type="text" class="form-control" id="description" name="description"  value="">
                        </div>
                        <div class="form-group">
                            <label for="special_contributor">{{ __('view.stock') }}</label>
                            <input type="number" class="form-control" id="stock" name="stock"  value="">
                        </div>
                        <div class="form-group">
                            <label for="iva_taxes">{{ __('view.iva_taxes') }}</label>
                            <select class="form-control selectpicker input-lg dynamic" id="iva_tax" name="iva_tax" data-live-search="true"  title="{{ __('view.select_an_iva_tax') }}">
                                @foreach($iva_taxes as $iva_tax)
                                    <option value="{{ $iva_tax->id }}">{{ $iva_tax->auxiliary_code }} - {{ $iva_tax->description }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="form-group">
                            <label for="ice_taxes">{{ __('view.ice_taxes') }}</label>
                            <select class="form-control selectpicker input-lg dynamic" id="ice_tax" name="ice_tax" data-live-search="true" title="{{ __('view.select_an_ice_tax') }}">
                                @foreach($ice_taxes as $ice_tax)
                                    <option value="{{ $ice_tax->id }}">{{ $ice_tax->auxiliary_code }} - {{ $ice_tax->description }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="form-group">
                            <label for="irbpnr_taxes">{{ __('view.irbpnr_taxes') }}</label>
                            <select class="form-control selectpicker input-lg dynamic" id="irbpnr_tax" name="irbpnr_tax" data-live-search="true" title="{{ __('view.select_an_irbpnr_tax') }}">
                                @foreach($irbpnr_taxes as $irbpnr_tax)
                                    <option value="{{ $irbpnr_tax->id }}">{{ $irbpnr_tax->auxiliary_code }} - {{ $irbpnr_tax->description }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>

                    <div class="card-footer">
                        <button id="submit" type="button" class="btn btn-sm btn-success">{{ __('view.add') }}</button>
                    </div>

                </form>

            </div>
        </div>
    </div>
</div>

@include('layouts.validation')
@endsection
