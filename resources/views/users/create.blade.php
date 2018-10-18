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
                    for (var i = 0; i < branches.length; i++) {
                        options += '<option value="' + branches[i]['id'] + '">' + branches[i]['company']['tradename'] + ': ' + branches[i]['name'] + '</option>';
                    }
                    $("#branch").html(options).selectpicker('refresh');
                    $("#emission_point").html('').selectpicker('refresh');
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
                    for (var i = 0; i < emissionPoints.length; i++) {
                        options += '<option value="' + emissionPoints[i]['id'] + '">' + emissionPoints[i]['branch']['company']['tradename'] + ' (' + emissionPoints[i]['branch']['name'] + '): ' + emissionPoints[i]['code'] + '</option>';
                    }
                    $("#emission_point").html(options).selectpicker('refresh');
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
                    New user
                    <a href="{{ route('users.index') }}" class="btn btn-sm btn-secondary float-right">Cancel</a>
                </div>

                <form action="{{ route('users.store') }}" method="post">
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

                        <div class="form-group">
                            <label for="role">Role</label>
                            <select class="form-control selectpicker" id="role" name="role" data-live-search="true" data-dependent="branch" title="Select one role ...">
                                @foreach($roles as $role)
                                    <option value="{{ $role->name }}" {{ $role->name === old('role') ? "selected" : "" }}>{{ strtoupper($role->name) }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="form-group">
                            <label for="name">Name</label>
                            <input class="form-control" type="text" id="name" name="name" value="{{ old('name') }}">
                        </div>
                        <div class="form-group">
                            <label for="email">Email</label>
                            <input class="form-control" type="email" id="email" name="email" value="{{ old('email') }}">
                        </div>
                        <div class="form-group">
                            <label for="password">Password</label>
                            <input class="form-control" type="password" id="password" name="password" value="">
                        </div>
                        <div class="form-group">
                            <label for="password_confirmation">Confirm password</label>
                            <input class="form-control" type="password" id="password_confirmation" name="password_confirmation" value="">
                        </div>
                        <div class="form-group">
                            <label for="company">Company</label>
                            <select class="form-control selectpicker input-lg dynamic" id="company" name="company[]" multiple data-actions-box="true" data-live-search="true" data-dependent="branch" title="Select one o more companies ...">
                                @foreach($companies as $company)
                                    <option value="{{ $company->id }}" {{ $company->id === old('company') ? "selected" : "" }}>{{ $company->tradename }} - {{ $company->social_reason }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="form-group">
                            <label for="branch">Branch</label>
                            <select class="form-control selectpicker input-lg dynamic" id="branch" name="branch[]" multiple data-actions-box="true" data-live-search="true" data-dependent="branch" title="Select one o more branches ...">

                            </select>
                        </div>
                        <div class="form-group">
                            <label for="emission_point">Emission point</label>
                            <select class="form-control selectpicker input-lg" id="emission_point" name="emission_point[]" multiple data-actions-box="true" data-live-search="true" data-dependent="branch" title="Select one o more emission points ...">

                            </select>
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
