@extends('layouts.app')

@section('scripts')
<script src="{{ asset('js/jquery.dataTables.min.js') }}"></script>
<script id="modal" src="{{ asset('js/app/modal.js') }}"></script>
@endsection

@section('styles')
<link href="{{ asset('css/jquery.dataTables.min.css') }}" rel="stylesheet">
@endsection

@section('content')
<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-12">
            <div class="card">
                <div class="card-header">
                    {{ ucfirst(trans_choice(__('view.user'), 1)) }}
                    @if(auth()->user()->can('create_users'))
                        <a href="{{ route('users.create') }}" class="btn btn-sm btn-primary float-right">{{ trans_choice(__('view.new'), 0) }}</a>
                    @endif
                </div>

                <div class="card-body">
                    @if (session('status'))
                        <div class="alert alert-success" role="alert">
                            {{ session('status') }}
                        </div>
                    @endif
                    <table id="table" class="display">
                        <thead>
                            <tr>
                                <th>{{ __('view.name') }}</th>
                                <th>{{ __('view.email') }}</th>
                                <th>{{ __('view.role') }}</th>
                                <th></th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($users as $user)
                                <tr>
                                    <td>{{ $user->name }}</td>
                                    <td>
                                        @if($user->deleted_at !== NULL)
                                            {{ $user->email }}
                                        @else
                                            @if(auth()->user()->can('update_users'))
                                                <a href="{{ route('users.edit', $user) }}">{{ $user->email }}</a>
                                            @elseif(auth()->user()->can('read_users'))
                                                <a href="{{ route('users.show', $user) }}">{{ $user->email }}</a>
                                            @else
                                                {{ $user->email }}
                                            @endif
                                        @endif
                                    </td>
                                    <td>{{  strtoupper(__(implode(', ', json_decode(json_encode($user->getRoleNames()), true)))) }}</td>
                                    <td>
                                        @if(\Auth::user()->id !== $user->id)
                                            @if($user->deleted_at !== NULL)
                                                @if(auth()->user()->can('delete_hard_users'))
                                                    <button type="button" class="btn btn-sm btn-success" data-toggle="modal" data-target="#confirmation"
                                                        data-title="{{ trans_choice(__('view.are_you_sure_you_want_to_activate_the_model', ['model' => trans_choice(__('view.user'), 0), 'name' => $user->name]), 0) }}"
                                                        data-body="{{ trans_choice(__('view.all_model_data_will_be_restored', ['model' => trans_choice(__('view.user'), 0)]), 0) }}"
                                                        data-form="{{ route('users.restore', $user->id) }}"
                                                        data-method="POST"
                                                        data-class="btn btn-sm btn-success"
                                                        data-action="{{ __('view.activate') }}">{{ __('view.activate') }}</button>
                                                    <button type="button" class="btn btn-sm btn-danger" data-toggle="modal" data-target="#confirmation"
                                                        data-title="{{ trans_choice(__('view.are_you_sure_you_want_to_delete_the_model', ['model' => trans_choice(__('view.user'), 0), 'name' => $user->name]), 0) }}"
                                                        data-body="{{ trans_choice(__('view.warning_all_model_data_will_be_deleted_this_action_can_not_be_undone', ['model' => trans_choice(__('view.user'), 0)]), 0) }}"
                                                        data-form="{{ route('users.destroy', $user->id) }}"
                                                        data-method="DELETE"
                                                        data-class="btn btn-sm btn-danger"
                                                        data-action="{{ __('view.delete') }}">{{ __('view.delete') }}</button>
                                                @endif
                                            @else
                                                @if(auth()->user()->can('delete_hard_users'))
                                                    <button type="button" class="btn btn-sm btn-warning" data-toggle="modal" data-target="#confirmation"
                                                        data-title="{{ trans_choice(__('view.are_you_sure_you_want_to_deactivate_the_model', ['model' => trans_choice(__('view.user'), 0), 'name' => $user->name]), 0) }}"
                                                        data-body="{{ trans_choice(__('view.the_data_of_the_model_will_remain_in_the_application', ['model' => trans_choice(__('view.user'), 0)]), 0) }}"
                                                        data-form="{{ route('users.delete', $user) }}"
                                                        data-method="DELETE"
                                                        data-class="btn btn-sm btn-warning"
                                                        data-action="{{ __('view.deactivate') }}">{{ __('view.deactivate') }}</button>
                                                @else
                                                    <button type="button" class="btn btn-sm btn-warning" data-toggle="modal" data-target="#confirmation"
                                                        data-title="{{ trans_choice(__('view.are_you_sure_you_want_to_deactivate_the_model', ['model' => trans_choice(__('view.user'), 0), 'name' => $user->name]), 0) }}"
                                                        data-body="{{ trans_choice(__('view.the_data_of_the_model_will_remain_in_the_application', ['model' => trans_choice(__('view.user'), 0)]), 0) }}"
                                                        data-form="{{ route('users.delete', $user) }}"
                                                        data-method="DELETE"
                                                        data-class="btn btn-sm btn-warning"
                                                        data-action="{{ __('view.delete') }}">{{ __('view.delete') }}</button>
                                                @endif
                                            @endif
                                        @endif

                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

@include('layouts.confirmation')
@endsection
