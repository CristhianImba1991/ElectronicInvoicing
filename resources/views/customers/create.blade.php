@extends('layouts.app')

@section('scripts')
<script src="{{ asset('js/bootstrap-select.min.js') }}"></script>
<script type="text/javascript">
    $.noConflict();
    jQuery(document).ready(function($) {
        $("#submit").click(function() {
            $.ajax({
                url: "{{ route('customers.store') }}",
                method: "POST",
                data: $('#create_form').serialize(),
                success: function(result) {
                    var validator = JSON.parse(result);
                    if (validator['status']) {
                        window.location.href = "{{ route('customers.index') }}";
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
        <div class="col-md-12">
            <div class="card">
                <div class="card-header">
                    {{ trans_choice(__('view.new_model', ['model' => trans_choice(__('view.customer'), 0)]), 0) }}
                    <a href="{{ route('customers.index') }}" class="btn btn-sm btn-secondary float-right">{{ __('view.cancel') }}</a>
                </div>

                <form id="create_form">
                    {{ csrf_field() }}

                    <div class="card-body">

                        <div class="form-group">
                            <label for="company">{{ ucfirst(trans_choice(__('view.company'), 0)) }}</label>
                            <select class="form-control selectpicker" id="company" name="company" data-live-search="true" title="{{ trans_choice(__('view.select_a_model', ['model' => trans_choice(__('view.company'), 0)]), 1) }}">
                                @foreach($companies as $company)
                                    <option value="{{ $company->id }}">{{ $company->tradename }} - {{ $company->social_reason }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="form-group">
                            <label for="identification_type">{{ __('view.identification_type') }}</label>
                            <select class="form-control selectpicker" id="identification_type" name="identification_type" data-live-search="true" title="{{ trans_choice(__('view.select_a_model', ['model' => strtolower(__('view.identification_type'))]), 0) }}">
                                @foreach($identificationTypes as $identificationType)
                                    <option value="{{ $identificationType->id }}">{{ $identificationType->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="form-group">
                            <label for="identification">{{ __('view.identification') }}</label>
                            <input class="form-control" type="text" id="identification" name="identification" value="">
                        </div>
                        <div class="form-group">
                            <label for="social_reason">{{ __('view.social_reason') }}</label>
                            <input class="form-control" type="text" id="social_reason" name="social_reason" value="">
                        </div>
                        <div class="form-group">
                            <label for="name">{{ __('view.address') }}</label>
                            <input class="form-control" type="text" id="address" name="address" value="">
                        </div>
                        <div class="form-group">
                            <label for="phone">{{ __('view.phone') }}</label>
                            <input class="form-control" type="text" id="phone" name="phone" value="">
                        </div>
                        <div class="form-group">
                            <label for="email">{{ __('view.email') }}</label>
                            <input class="form-control" type="email" id="email" name="email" value="" multiple>
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
