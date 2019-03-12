@extends('layouts.app')

@section('scripts')
<script src="{{ asset('js/bootstrap-select.min.js') }}"></script>
<script type="text/javascript">
    $.noConflict();
    jQuery(document).ready(function($){
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
                url: "{{ route('emission_points.store') }}",
                method: "POST",
                data: $('#create_form').serialize(),
                success: function(result) {
                    var validator = JSON.parse(result);
                    if (validator['status']) {
                        window.location.href = "{{ route('emission_points.index') }}";
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
                    {{ trans_choice(__('view.new_model', ['model' => trans_choice(__('view.emission_point'), 0)]), 0) }}
                    <a href="{{ route('emission_points.index') }}" class="btn btn-sm btn-secondary float-right">{{ __('view.cancel') }}</a>
                </div>

                <form id="create_form">
                    {{ csrf_field() }}

                    <div class="card-body">

                        <div class="form-group">
                            <label for="company">{{ ucfirst(trans_choice(__('view.company'), 0)) }}</label>
                            <select class="form-control selectpicker input-lg dynamic" id="company" name="company" data-live-search="true" data-dependent="branch" title="{{ trans_choice(__('view.select_a_model', ['model' => trans_choice(__('view.company'), 0)]), 1) }}">
                                @foreach($companies as $company)
                                    <option value="{{ $company->id }}">{{ $company->tradename }} - {{ $company->social_reason }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="form-group" id="divbranch">
                            <label for="establishment">{{ ucfirst(trans_choice(__('view.branch'), 0)) }}</label>
                            <select class="form-control selectpicker input-lg" id="branch" name="branch" data-live-search="true" title="{{ trans_choice(__('view.select_a_model', ['model' => trans_choice(__('view.branch'), 0)]), 1) }}">

                            </select>
                        </div>
                        <div class="form-group">
                            <label for="code">{{ __('view.code') }}</label>
                            <input class="form-control" type="number" id="code" name="code" value="1">
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
