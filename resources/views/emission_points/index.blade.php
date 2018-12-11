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
                    Emission points
                    @if(auth()->user()->can('create_emission_points'))
                        <a href="{{ route('emission_points.create') }}" class="btn btn-sm btn-primary float-right">New</a>
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
                                <th>Code</th>
                                <th></th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($emissionPoints as $emissionPoint)
                                <tr>
                                    <td>{{ $emissionPoint->branch->company->tradename }}</td>
                                    <td>{{ $emissionPoint->branch->establishment }}</td>
                                    <td>
                                        @if($emissionPoint->deleted_at !== NULL)
                                            {{ $emissionPoint->code }}
                                        @else
                                            @if(auth()->user()->can('read_emission_points'))
                                                <a href="{{ route('emission_points.show', $emissionPoint) }}">{{ $emissionPoint->code }}</a>
                                            @else
                                                {{ $emissionPoint->name }}
                                            @endif
                                        @endif
                                    </td>
                                    <td>
                                        @if($emissionPoint->deleted_at !== NULL)
                                            @if(auth()->user()->can('delete_hard_emission_points'))
                                                <button type="button" class="btn btn-sm btn-success" data-toggle="modal" data-target="#confirmation"
                                                    data-title="Are you sure you want to activate the emission point {{ $emissionPoint->code }}?"
                                                    data-body="All emission point data will be restored."
                                                    data-form="{{ route('emission_points.restore', $emissionPoint->id) }}"
                                                    data-method="POST"
                                                    data-class="btn btn-sm btn-success"
                                                    data-action="Activate">Activate</button>
                                                <button type="button" class="btn btn-sm btn-danger" data-toggle="modal" data-target="#confirmation"
                                                    data-title="Are you sure you want to delete the emission point {{ $emissionPoint->code }}?"
                                                    data-body="WARNING: All emission point data will be deleted. This action can not be undone."
                                                    data-form="{{ route('emission_points.destroy', $emissionPoint->id) }}"
                                                    data-method="DELETE"
                                                    data-class="btn btn-sm btn-danger"
                                                    data-action="Delete">Delete</button>
                                            @endif
                                        @else
                                            @if(auth()->user()->can('delete_hard_emission_points'))
                                                <button type="button" class="btn btn-sm btn-warning" data-toggle="modal" data-target="#confirmation"
                                                    data-title="Are you sure you want to deactivate the emission point {{ $emissionPoint->code }}?"
                                                    data-body="The data of the emission point will remain in the application, but the users that depend on it will not be able to access the data. If you want to restore it, contact the administrator."
                                                    data-form="{{ route('emission_points.delete', $emissionPoint) }}"
                                                    data-method="DELETE"
                                                    data-class="btn btn-sm btn-warning"
                                                    data-action="Deactivate">Deactivate</button>
                                            @else
                                                <button type="button" class="btn btn-sm btn-warning" data-toggle="modal" data-target="#confirmation"
                                                    data-title="Are you sure you want to deactivate the emission point {{ $emissionPoint->code }}?"
                                                    data-body="The data of the emission point will remain in the application, but the users that depend on it will not be able to access the data. If you want to restore it, contact the administrator."
                                                    data-form="{{ route('emission_points.delete', $emissionPoint) }}"
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
