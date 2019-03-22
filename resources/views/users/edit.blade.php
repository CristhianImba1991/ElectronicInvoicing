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
        $("#submit").click(function() {
            $.ajax({
                url: "{{ url('/manage/users/update') }}/{{ $user->id }}",
                method: "POST",
                data: $('#update_form').serialize(),
                success: function(result) {
                    var validator = JSON.parse(result);
                    if (validator['status']) {
                        window.location.href = "{{ route('users.index') }}";
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
                    {{ __('view.edit_model', ['model' => trans_choice(__('view.user'), 0)]) }}
                    <a href="{{ route('users.index') }}" class="btn btn-sm btn-secondary float-right">{{ __('view.cancel') }}</a>
                </div>

                <form id="update_form">
                    {{ csrf_field() }}
                    <input type="hidden" name="_method" value="PUT">

                    <div class="card-body">

                        <div class="form-group">
                            <label for="current_role">{{ __('view.current_role') }}</label>
                            <input class="form-control" type="text" id="current_role" name="current_role" value="{{ strtoupper(__(implode(', ', json_decode(json_encode($user->getRoleNames()), true)))) }}" readonly>
                        </div>
                        @if(!$user->hasRole('customer'))
                            <div class="form-group">
                                <label for="role">{{ __('view.role') }}</label>
                                <select class="form-control selectpicker" id="role" name="role" data-live-search="true" data-dependent="branch" title="{{ trans_choice(__('view.select_a_model', ['model' => strtolower(__('view.role'))]), 0) }}">
                                    @foreach($roles as $role)
                                        <option value="{{ $role->name }}">{{ strtoupper($role->name) }}</option>
                                    @endforeach
                                </select>
                            </div>
                        @endif
                        <div class="form-group">
                            <label for="name">{{ __('view.name') }}</label>
                            <input class="form-control" type="text" id="name" name="name" value="{{ $user->name }}">
                        </div>
                        <div class="form-group">
                            <label for="email">{{ __('view.email') }}</label>
                            @role('admin')
                                <input class="form-control" type="email" id="email" name="email" value="{{ $user->email }}">
                            @else
                                <input class="form-control" type="email" id="email" name="email" value="{{ $user->email }}" readonly>
                            @endrole
                        </div>
                        <ul class="list-group">
                            <label>{{ __('view.currently_allowed_to') }}</label>
                            @if($user->hasRole('admin'))
                                <li class="list-group-item">{{ __('view.all') }}</li>
                            @else
                                @forelse(\ElectronicInvoicing\Http\Controllers\CompanyUser::getCompaniesAllowedToUser($user) as $company)
                                    <li class="list-group-item">{{ $company->tradename }} - {{ $company->social_reason }}
                                        @if(!$user->hasRole('owner'))
                                            <ul class="list-group">
                                                @foreach(\ElectronicInvoicing\Http\Controllers\CompanyUser::getBranchesAllowedToUser($user) as $branch)
                                                    @if($company->id === $branch->company_id)
                                                        <li class="list-group-item">{{ $branch->name }}
                                                            <ul class="list-group">
                                                                @foreach($user->emissionPoints()->get() as $emissionPoint)
                                                                    @if($branch->id === $emissionPoint->branch_id)
                                                                        <li class="list-group-item">{{ $emissionPoint->code }}</li>
                                                                    @endif
                                                                @endforeach
                                                            </ul>
                                                        </li>
                                                    @endif
                                                @endforeach
                                            </ul>
                                        @endif
                                    </li>
                                @empty
                                    <li class="list-group-item">{{ __('view.none') }}</li>
                                @endforelse
                            @endif
                        </ul>
                        @if(!$user->hasRole('customer'))
                            <div class="form-group">
                                <label for="company">{{ ucfirst(trans_choice(__('view.company'), 0)) }}</label>
                                <select class="form-control selectpicker input-lg dynamic" id="company" name="company[]" multiple data-actions-box="true" data-live-search="true" data-dependent="branch" title="{{ trans_choice(__('view.select_one_or_more_model', ['model' => trans_choice(__('view.company'), 1)]), 1) }}">
                                    @foreach($companies as $company)
                                        <option value="{{ $company->id }}">{{ $company->tradename }} - {{ $company->social_reason }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="form-group">
                                <label for="branch">{{ ucfirst(trans_choice(__('view.branch'), 0)) }}</label>
                                <select class="form-control selectpicker input-lg dynamic" id="branch" name="branch[]" multiple data-actions-box="true" data-live-search="true" data-dependent="branch" title="{{ trans_choice(__('view.select_one_or_more_model', ['model' => trans_choice(__('view.branch'), 1)]), 1) }}">

                                </select>
                            </div>
                            <div class="form-group">
                                <label for="emission_point">{{ ucfirst(trans_choice(__('view.emission_point'), 0)) }}</label>
                                <select class="form-control selectpicker input-lg" id="emission_point" name="emission_point[]" multiple data-actions-box="true" data-live-search="true" data-dependent="branch" title="{{ trans_choice(__('view.select_one_or_more_model', ['model' => trans_choice(__('view.emission_point'), 1)]), 1) }}">

                                </select>
                            </div>
                        @endif

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
