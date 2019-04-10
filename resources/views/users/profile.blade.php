@extends('layouts.app')

@section('scripts')
<script src="{{ asset('js/bootstrap-select.min.js') }}"></script>
<script type="text/javascript">
    $.noConflict();
    jQuery(document).ready(function($){
        $("#submit").click(function() {
            $.ajax({
                url: "{{ url('/manage/users/update') }}/{{ $user->id }}",
                method: "POST",
                data: $('#profile_form').serialize(),
                success: function(result) {
                    var validator = JSON.parse(result);
                    if (validator['status']) {
                        window.location.href = "{{ route('home') }}";
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

                <form id="profile_form">
                    {{ csrf_field() }}
                    <input type="hidden" name="_method" value="PUT">

                    <div class="card-body">

                        <div class="form-group">
                            <label for="current_role">{{ __('view.current_role') }}</label>
                            <input class="form-control" type="text" id="current_role" name="current_role" value="{{ strtoupper(__(implode(', ', json_decode(json_encode($user->getRoleNames()), true)))) }}" readonly>
                        </div>
                        <div class="form-group">
                            <label for="name">{{ __('view.name') }}</label>
                            <input class="form-control" type="text" id="name" name="name" value="{{ $user->name }}">
                        </div>
                        <div class="form-group">
                            <label for="email">{{ __('view.email') }}</label>
                            <input class="form-control" type="email" id="email" name="email" value="{{ $user->email }}" readonly>
                        </div>
                        <div class="form-group">
                            <label for="password">{{ __('view.password') }}</label>
                            <input class="form-control" type="password" id="password" name="password" value="">
                        </div>
                        <div class="form-group">
                            <label for="password_confirmation">{{ __('view.confirm_password') }}</label>
                            <input class="form-control" type="password" id="password_confirmation" name="password_confirmation" value="">
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
