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
                    New company
                    <a href="{{ route('companies.index') }}" class="btn btn-sm btn-secondary float-right">Cancel</a>
                </div>

                <form id="create_form">
                    {{ csrf_field() }}

                    <div class="card-body">

                        <div class="form-group">
                            <label for="ruc">RUC</label>
                            <input type="text" class="form-control" id="ruc" name="ruc" value="">
                        </div>
                        <div class="form-group">
                            <label for="social_reason">Social reason</label>
                            <input type="text" class="form-control" id="social_reason" name="social_reason"  value="">
                        </div>
                        <div class="form-group">
                            <label for="tradename">Tradename</label>
                            <input type="text" class="form-control" id="tradename" name="tradename"  value="">
                        </div>
                        <div class="form-group">
                            <label for="address">Address</label>
                            <input type="text" class="form-control" id="address" name="address"  value="">
                        </div>
                        <div class="form-group">
                            <label for="special_contributor">Special contributor</label>
                            <input type="text" class="form-control" id="special_contributor" name="special_contributor"  value="">
                        </div>
                        <div class="form-check">
                            <input class="form-check-input" type="checkbox" value="" id="keep_accounting" name="keep_accounting">
                            <label class="form-check-label" for="keep_accounting">Keep accounting</label>
                        </div>
                        <div class="form-group">
                            <label for="phone">Phone</label>
                            <input type="text" class="form-control" id="phone" name="phone" value="">
                        </div>
                        <div class="form-group">
                            <label for="logo">Logo</label>
                            <input type="file" class="form-control-file" id="logo" name="logo">
                        </div>
                        <div class="form-group">
                            <label for="sign">Sign</label>
                            <input type="file" class="form-control-file" id="sign" name="sign">
                        </div>
                        <div class="form-group">
                            <label for="password">Password</label>
                            <input type="password" class="form-control" id="password" name="password" value="">
                        </div>
                    </div>

                    <div class="card-footer">
                        <button id="submit" type="button" class="btn btn-sm btn-success">Add</button>
                    </div>

                </form>
            </div>
        </div>
    </div>
</div>

@include('layouts.validation')
@endsection
