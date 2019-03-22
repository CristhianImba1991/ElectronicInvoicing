@extends('layouts.app')

@section('scripts')
<script type="text/javascript">
    $.noConflict();
    jQuery(document).ready(function($) {
        $("#submit").click(function() {
            $.ajax({
                url: "{{ url('/manage/branches/update') }}/{{ $branch->id }}",
                method: "POST",
                data: $('#update_form').serialize(),
                success: function(result) {
                    var validator = JSON.parse(result);
                    if (validator['status']) {
                        window.location.href = "{{ route('branches.index') }}";
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
        <div class="col-md-12">
            <div class="card">
                <div class="card-header">
                    {{ __('view.edit_model', ['model' => trans_choice(__('view.branch'), 0)]) }}
                    <a href="{{ route('branches.index') }}" class="btn btn-sm btn-secondary float-right">{{ __('view.cancel') }}</a>
                </div>
                <form id="update_form">
                    {{ csrf_field() }}
                    <input type="hidden" name="_method" value="PUT">

                    <div class="card-body">

                        <div class="form-group">
                            <label for="company_branch">{{ ucfirst(trans_choice(__('view.company'), 0)) }}</label>
                            <input class="form-control" type="text" id="company_branch" name="company_branch" value="{{ $branch->company->tradename }} - {{ $branch->company->social_reason }}" readonly>
                            <input type="hidden" name="company" value="{{ $branch->company->id }}">
                        </div>
                        <div class="form-group">
                            <label for="establishment">{{ ucfirst(__('view.establishment')) }}</label>
                            <input class="form-control" type="text" id="establishment" name="establishment" value="{{ $branch->establishment }}" readonly>
                        </div>
                        <div class="form-group">
                            <label for="name">{{ ucfirst(__('view.name')) }}</label>
                            <input class="form-control" type="text" id="name" name="name" value="{{ $branch->name }}">
                        </div>
                        <div class="form-group">
                            <label for="address">{{ ucfirst(__('view.address')) }}</label>
                            <input class="form-control" type="text" id="address" name="address" value="{{ $branch->address }}">
                        </div>
                        <div class="form-group">
                            <label for="phone">{{ ucfirst(__('view.phone')) }}</label>
                            <input class="form-control" type="text" id="phone" name="phone" value="{{ $branch->phone }}">
                        </div>
                    </div>

                    <div class="card-footer">
                        <button id="submit" type="button" class="btn btn-success btn-sm">{{ __('view.update') }}</button>
                    </div>

                </form>
            </div>
        </div>
    </div>
</div>

@include('layouts.validation')
@endsection
