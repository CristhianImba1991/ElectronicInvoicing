@extends('layouts.app')

@section('scripts')
<script src="{{ asset('js/jquery.dataTables.min.js') }}"></script>
<script type="text/javascript">
$.noConflict();
jQuery(document).ready(function($) {
    $('#users-table').DataTable();
    $('#userModal').on('show.bs.modal', function(event) {
        $(this).find('#modal-title').text($(event.relatedTarget).data('title'))
        $(this).find('#modal-body').text($(event.relatedTarget).data('body'))
        $(this).find("#modal-form").attr("action", $(event.relatedTarget).data('form'))
        $(this).find("#form-method").val($(event.relatedTarget).data('method'))
        $(this).find('#submit-action').attr("class", $(event.relatedTarget).data('class'))
        $(this).find('#submit-action').text($(event.relatedTarget).data('action'))
    });
});
</script>
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
                    <table id="users-table" class="display">
                        <thead>
                            <tr>
                                <th>Name</th>
                                <th>Email</th>
                                <th>Role</th>
                                <th></th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($users as $user)
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
                                        @if($user->deleted_at !== NULL)
                                            @if(auth()->user()->can('delete_hard_users'))
                                                <button type="button" class="btn btn-sm btn-success" data-toggle="modal" data-target="#userModal"
                                                    data-title="Are you sure you want to activate the user {{ $user->name }}?"
                                                    data-body="All user data will be restored."
                                                    data-form="{{ route('users.restore', $user->id) }}"
                                                    data-method="POST"
                                                    data-class="btn btn-sm btn-success"
                                                    data-action="Activate">Activate</button>
                                                <button type="button" class="btn btn-sm btn-danger" data-toggle="modal" data-target="#userModal"
                                                    data-title="Are you sure you want to delete the user {{ $user->name }}?"
                                                    data-body="WARNING: All user data will be deleted. This action can not be undone."
                                                    data-form="{{ route('users.destroy', $user->id) }}"
                                                    data-method="DELETE"
                                                    data-class="btn btn-sm btn-danger"
                                                    data-action="Delete">Delete</button>
                                            @endif
                                        @else
                                            @if(auth()->user()->can('delete_hard_users'))
                                                <button type="button" class="btn btn-sm btn-warning" data-toggle="modal" data-target="#userModal"
                                                    data-title="Are you sure you want to deactivate the user {{ $user->name }}?"
                                                    data-body="The data of the user will remain in the application, but the users that depend on it will not be able to access the data."
                                                    data-form="{{ route('users.delete', $user) }}"
                                                    data-method="DELETE"
                                                    data-class="btn btn-sm btn-warning"
                                                    data-action="Deactivate">Deactivate</button>
                                            @else
                                                <button type="button" class="btn btn-sm btn-warning" data-toggle="modal" data-target="#userModal"
                                                    data-title="Are you sure you want to deactivate the user {{ $user->name }}?"
                                                    data-body="The data of the user will remain in the application, but the users that depend on it will not be able to access the data."
                                                    data-form="{{ route('users.delete', $user) }}"
                                                    data-method="DELETE"
                                                    data-class="btn btn-sm btn-warning"
                                                    data-action="Delete">Delete</button>
                                            @endif
                                        @endif
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="4"><center>No records found.</center></td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="modal fade" tabindex="-1" role="dialog" id="userModal">
    <div class="modal-dialog modal-dialog-centered" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <strong><p id="modal-title"></p></strong>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body"><p id="modal-body"></p></div>
            <div class="modal-footer">
                <button type="button" class="btn btn-sm btn-secondary" data-dismiss="modal">Close</button>
                <form id="modal-form" action="#" method="post">
                    {{ csrf_field() }}
                    <input id="form-method" type="hidden" name="_method" value="" />
                    <button id="submit-action" type="submit" class=""></button>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection
