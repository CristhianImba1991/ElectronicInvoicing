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
                    Companies
                    @if(auth()->user()->can('create_companies'))
                        <a href="{{ route('companies.create') }}" class="btn btn-sm btn-primary float-right">New</a>
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
                                <th></th>
                                <th>RUC</th>
                                <th>Tradename - Social reason</th>
                                @if(auth()->user()->can('delete_hard_companies'))
                                    <th></th>
                                @endif
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($companies as $company)
                                <tr>
                                    <td align="center"><img class="img-thumbnail" src="{{ url('storage/logo/thumbnail/'.$company->logo) }}" alt="{{ $company->tradename }}"></td>
                                    <td>{{ $company->ruc }}</td>
                                    <td>
                                        @if($company->deleted_at !== NULL)
                                            {{ $company->tradename }} - {{ $company->social_reason }}
                                        @else
                                            @if(auth()->user()->can('update_companies'))
                                                <a href="{{ route('companies.edit', $company) }}">{{ $company->tradename }} - {{ $company->social_reason }}</a>
                                            @elseif(auth()->user()->can('read_companies'))
                                                <a href="{{ route('companies.show', $company) }}">{{ $company->tradename }} - {{ $company->social_reason }}</a>
                                            @else
                                                {{ $company->tradename }} - {{ $company->social_reason }}
                                            @endif
                                        @endif
                                    </td>
                                    @if(auth()->user()->can('delete_hard_companies'))
                                        <td>
                                            @if($company->deleted_at !== NULL)
                                                @if(auth()->user()->can('delete_hard_companies'))
                                                    <button type="button" class="btn btn-sm btn-success" data-toggle="modal" data-target="#confirmation"
                                                        data-title="Are you sure you want to activate the company {{ $company->tradename }} - {{ $company->social_reason }}?"
                                                        data-body="All company data will be restored."
                                                        data-form="{{ route('companies.restore', $company->id) }}"
                                                        data-method="POST"
                                                        data-class="btn btn-sm btn-success"
                                                        data-action="Activate">Activate</button>
                                                    <button type="button" class="btn btn-sm btn-danger" data-toggle="modal" data-target="#confirmation"
                                                        data-title="Are you sure you want to delete the company {{ $company->tradename }} - {{ $company->social_reason }}?"
                                                        data-body="WARNING: All company data will be deleted. This action can not be undone."
                                                        data-form="{{ route('companies.destroy', $company->id) }}"
                                                        data-method="DELETE"
                                                        data-class="btn btn-sm btn-danger"
                                                        data-action="Delete">Delete</button>
                                                @endif
                                            @else
                                                @if(auth()->user()->can('delete_hard_companies'))
                                                    <button type="button" class="btn btn-sm btn-warning" data-toggle="modal" data-target="#confirmation"
                                                        data-title="Are you sure you want to deactivate the company {{ $company->tradename }} - {{ $company->social_reason }}?"
                                                        data-body="The data of the company will remain in the application, but the users that depend on it will not be able to access the data. If you want to restore it, contact the administrator."
                                                        data-form="{{ route('companies.delete', $company) }}"
                                                        data-method="DELETE"
                                                        data-class="btn btn-sm btn-warning"
                                                        data-action="Deactivate">Deactivate</button>
                                                @endif
                                            @endif
                                        </td>
                                    @endif
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
