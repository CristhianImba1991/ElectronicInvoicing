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
        <div class="col-md-8">
            <div class="card">
                <div class="card-header">
                    Users
                    @if(auth()->user()->can('create_users'))
                        <a href="{{ route('users.create') }}" class="btn btn-sm btn-primary float-right">New</a>
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
                                <th>Name</th>
                                <th>Email</th>
                                <th>Role</th>
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
                                    <td>{{ strtoupper(implode(', ', json_decode(json_encode($user->getRoleNames()), true))) }}</td>
                                    <td>
                                        @if(\Auth::user()->id !== $user->id)
                                            @if($user->deleted_at !== NULL)
                                                @if(auth()->user()->can('delete_hard_users'))
                                                    <button type="button" class="btn btn-sm btn-success" data-toggle="modal" data-target="#confirmation"
                                                        data-title="Are you sure you want to activate the user {{ $user->name }}?"
                                                        data-body="All user data will be restored."
                                                        data-form="{{ route('users.restore', $user->id) }}"
                                                        data-method="POST"
                                                        data-class="btn btn-sm btn-success"
                                                        data-action="Activate">Activate</button>
                                                    <button type="button" class="btn btn-sm btn-danger" data-toggle="modal" data-target="#confirmation"
                                                        data-title="Are you sure you want to delete the user {{ $user->name }}?"
                                                        data-body="WARNING: All user data will be deleted. This action can not be undone."
                                                        data-form="{{ route('users.destroy', $user->id) }}"
                                                        data-method="DELETE"
                                                        data-class="btn btn-sm btn-danger"
                                                        data-action="Delete">Delete</button>
                                                @endif
                                            @else
                                                @if(auth()->user()->can('delete_hard_users'))
                                                    <button type="button" class="btn btn-sm btn-warning" data-toggle="modal" data-target="#confirmation"
                                                        data-title="Are you sure you want to deactivate the user {{ $user->name }}?"
                                                        data-body="The data of the user will remain in the application, but the users that depend on it will not be able to access the data."
                                                        data-form="{{ route('users.delete', $user) }}"
                                                        data-method="DELETE"
                                                        data-class="btn btn-sm btn-warning"
                                                        data-action="Deactivate">Deactivate</button>
                                                @else
                                                    <button type="button" class="btn btn-sm btn-warning" data-toggle="modal" data-target="#confirmation"
                                                        data-title="Are you sure you want to deactivate the user {{ $user->name }}?"
                                                        data-body="The data of the user will remain in the application, but the users that depend on it will not be able to access the data."
                                                        data-form="{{ route('users.delete', $user) }}"
                                                        data-method="DELETE"
                                                        data-class="btn btn-sm btn-warning"
                                                        data-action="Delete">Delete</button>
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
