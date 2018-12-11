@extends('layouts.app')

@section('scripts')
<script type="text/javascript">
    $.noConflict();
    jQuery(document).ready(function($) {
        $("#submit").click(function() {
            $.ajax({
                url: "{{ url('/manage/customers/update') }}/{{ $customer->id }}",
                method: "POST",
                data: $('#update_form').serialize(),
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

@section('content')
<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="card">
                <div class="card-header">
                    Edit customer
                    <a href="{{ route('customers.index') }}" class="btn btn-sm btn-secondary float-right">Cancel</a>
                </div>

                <form id="update_form">
                    {{ csrf_field() }}
                    <input type="hidden" name="_method" value="PUT">

                    <div class="card-body">

                        <div class="form-group">
                            <label for="ruc">Company</label>
                            <input class="form-control" type="text" id="ruc" name="ruc" value="{{ $customer->companies()->first()->tradename }} - {{ $customer->companies()->first()->social_reason }}" readonly>
                            <input type="hidden" name="company" value="{{ $customer->companies()->first()->id }}">
                        </div>
                        <div class="form-group">
                            <label for="identification_type_name">Identification type</label>
                            <input class="form-control" type="text" id="identification_type_name" name="identification_type_name" value="{{ $customer->identificationType->name }}" readonly>
                            <input type="hidden" name="identification_type" value="{{ $customer->identificationType->id }}">
                        </div>
                        <div class="form-group">
                            <label for="identification">Identification</label>
                            <input class="form-control" type="text" id="identification" name="identification" value="{{ $customer->identification }}" readonly>
                        </div>
                        <div class="form-group">
                            <label for="social_reason">Social reason</label>
                            <input class="form-control" type="text" id="social_reason" name="social_reason" value="{{ $customer->social_reason }}">
                        </div>
                        <div class="form-group">
                            <label for="address">Address</label>
                            <input class="form-control" type="text" id="address" name="address" value="{{ $customer->address }}">
                        </div>
                        <div class="form-group">
                            <label for="phone">Phone</label>
                            <input class="form-control" type="text" id="phone" name="phone" value="{{ $customer->phone }}">
                        </div>
                        <div class="form-group">
                            <label for="email">Email</label>
                            <input class="form-control" type="text" id="email" name="email" value="{{ $customer->email }}">
                        </div>
                    </div>

                    <div class="card-footer">
                        <button id="submit" type="button" class="btn btn-success btn-sm">Update</button>
                    </div>

                </form>
            </div>
        </div>
    </div>
</div>

@include('layouts.validation')
@endsection
