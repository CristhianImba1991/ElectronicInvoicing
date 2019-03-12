@extends('layouts.app')

@section('scripts')
<script type="text/javascript">
    $.noConflict();
    jQuery(document).ready(function($) {
        $("#submit").click(function() {
            $.ajax({
                url: "{{ route('companies.store') }}",
                method: "POST",
                enctype: 'multipart/form-data',
                cache: false,
                contentType: false,
                processData: false,
                data: new FormData($('#create_form')[0]),
                success: function(result) {
                    var validator = JSON.parse(result);
                    if (validator['status']) {
                        window.location.href = "{{ route('companies.index') }}";
                    } else {
                        $('#validation').on('show.bs.modal', function(event) {
                            var errors = '';
                            $.each(validator['messages'], function(field, message) {
                                errors += "<li>" + message + "</li>";
                            });
                            $(this).find('#modal-body').html("<ul>" + errors + "</ul>");
                        });
                        $('#validation').modal('show');
                        $('#password').val('');
                    }
                }
            });
        });
    });
</script>
@endsection

@section('content')
<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="card">
                <div class="card-header">
                    {{ trans_choice(__('view.new_model', ['model' => trans_choice(__('view.company'), 0)]), 1) }}
                    <a href="{{ route('companies.index') }}" class="btn btn-sm btn-secondary float-right">{{ __('view.cancel') }}</a>
                </div>

                <form id="create_form">
                    {{ csrf_field() }}

                    <div class="card-body">

                        <div class="form-group">
                            <label for="ruc">{{ __('view.ruc') }}</label>
                            <input type="text" class="form-control" id="ruc" name="ruc" value="">
                        </div>
                        <div class="form-group">
                            <label for="social_reason">{{ __('view.social_reason') }}</label>
                            <input type="text" class="form-control" id="social_reason" name="social_reason"  value="">
                        </div>
                        <div class="form-group">
                            <label for="tradename">{{ __('view.tradename') }}</label>
                            <input type="text" class="form-control" id="tradename" name="tradename"  value="">
                        </div>
                        <div class="form-group">
                            <label for="address">{{ __('view.address') }}</label>
                            <input type="text" class="form-control" id="address" name="address"  value="">
                        </div>
                        <div class="form-group">
                            <label for="special_contributor">{{ __('view.special_contributor') }}</label>
                            <input type="text" class="form-control" id="special_contributor" name="special_contributor"  value="">
                        </div>
                        <div class="form-check">
                            <input class="form-check-input" type="checkbox" value="" id="keep_accounting" name="keep_accounting">
                            <label class="form-check-label" for="keep_accounting">{{ __('view.keep_accounting') }}</label>
                        </div>
                        <div class="form-group">
                            <label for="phone">{{ __('view.phone') }}</label>
                            <input type="text" class="form-control" id="phone" name="phone" value="">
                        </div>
                        <div class="form-group">
                            <div class="custom-file">
                                <input type="file" class="custom-file-input" id="logo" name="logo">
                                <label for="logo" class="custom-file-label">{{ __('view.logo') }}</label>
                            </div>
                        </div>
                        <div class="form-group">
                            <div class="custom-file">
                                <input type="file" class="custom-file-input" id="sign" name="sign">
                                <label for="sign" class="custom-file-label">{{ __('view.sign') }}</label>
                            </div>
                        </div>
                        <div class="form-group">
                            <label for="password">{{ __('view.password') }}</label>
                            <input type="password" class="form-control" id="password" name="password" value="">
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
