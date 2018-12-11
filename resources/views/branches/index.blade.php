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
                    Branches
                    @if(auth()->user()->can('create_branches'))
                        <a href="{{ route('branches.create') }}" class="btn btn-sm btn-primary float-right">New</a>
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
                                <th>Company</th>
                                <th>Establishment</th>
                                <th>Name</th>
                                <th></th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($branches as $branch)
                                <tr>
                                    <td>{{ $branch->company->tradename }}</td>
                                    <td>{{ $branch->establishment }}</td>
                                    <td>
                                        @if($branch->deleted_at !== NULL)
                                            {{ $branch->name }}
                                        @else
                                            @if(auth()->user()->can('update_branches'))
                                                <a href="{{ route('branches.edit', $branch) }}">{{ $branch->name }}</a>
                                            @elseif(auth()->user()->can('read_branches'))
                                                <a href="{{ route('branches.show', $branch) }}">{{ $branch->name }}</a>
                                            @else
                                                {{ $branch->name }}
                                            @endif
                                        @endif
                                    </td>
                                    <td>
                                        @if($branch->deleted_at !== NULL)
                                            @if(auth()->user()->can('delete_hard_branches'))
                                                <button type="button" class="btn btn-sm btn-success" data-toggle="modal" data-target="#confirmation"
                                                    data-title="Are you sure you want to activate the branch {{ $branch->name }}?"
                                                    data-body="All branch data will be restored."
                                                    data-form="{{ route('branches.restore', $branch->id) }}"
                                                    data-method="POST"
                                                    data-class="btn btn-sm btn-success"
                                                    data-action="Activate">Activate</button>
                                                <button type="button" class="btn btn-sm btn-danger" data-toggle="modal" data-target="#confirmation"
                                                    data-title="Are you sure you want to delete the branch {{ $branch->name }}?"
                                                    data-body="WARNING: All branch data will be deleted. This action can not be undone."
                                                    data-form="{{ route('branches.destroy', $branch->id) }}"
                                                    data-method="DELETE"
                                                    data-class="btn btn-sm btn-danger"
                                                    data-action="Delete">Delete</button>
                                            @endif
                                        @else
                                            @if(auth()->user()->can('delete_hard_branches'))
                                                <button type="button" class="btn btn-sm btn-warning" data-toggle="modal" data-target="#confirmation"
                                                    data-title="Are you sure you want to deactivate the branch {{ $branch->name }}?"
                                                    data-body="The data of the branch will remain in the application, but the users that depend on it will not be able to access the data. If you want to restore it, contact the administrator."
                                                    data-form="{{ route('branches.delete', $branch) }}"
                                                    data-method="DELETE"
                                                    data-class="btn btn-sm btn-warning"
                                                    data-action="Deactivate">Deactivate</button>
                                            @else
                                                <button type="button" class="btn btn-sm btn-warning" data-toggle="modal" data-target="#confirmation"
                                                    data-title="Are you sure you want to deactivate the branch {{ $branch->name }}?"
                                                    data-body="The data of the branch will remain in the application, but the users that depend on it will not be able to access the data. If you want to restore it, contact the administrator."
                                                    data-form="{{ route('branches.delete', $branch) }}"
                                                    data-method="DELETE"
                                                    data-class="btn btn-sm btn-warning"
                                                    data-action="Delete">Delete</button>
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
