@extends('layouts.app')

@section('scripts')
<script src="{{ asset('js/jquery.dataTables.min.js') }}"></script>
<script id="modal" src="{{ asset('js/app/modal.js') }}" data-model="customer" data-table="customers"></script>
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
                    Customers
                    @if(auth()->user()->can('create_branches'))
                        <a href="{{ route('customers.create') }}" class="btn btn-sm btn-primary float-right">New</a>
                    @endif
                </div>

                <div class="card-body">
                    @if (session('status'))
                        <div class="alert alert-success" role="alert">
                            {{ session('status') }}
                        </div>
                    @endif
                    <table id="customers-table" class="display">
                        <thead>
                            <tr>
                                <th>Identification</th>
                                <th>Social reason</th>
                                <th>Email</th>
                                <th></th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($customers as $customer)
                                <tr>
                                    <td>{{ $customer->identification }}</td>
                                    <td>
                                        @if($customer->deleted_at !== NULL)
                                            {{ $customer->social_reason }}
                                        @else
                                            @if(auth()->user()->can('update_customers'))
                                                <a href="{{ route('customers.edit', $customer) }}">{{ $customer->social_reason }}</a>
                                            @elseif(auth()->user()->can('read_customers'))
                                                <a href="{{ route('customers.show', $customer) }}">{{ $customer->social_reason }}</a>
                                            @else
                                                {{ $customer->social_reason }}
                                            @endif
                                        @endif
                                    </td>
                                    <td>{{ $customer->email }}</td>
                                    <td>
                                        @if($customer->deleted_at !== NULL)
                                            @if(auth()->user()->can('delete_hard_customers'))
                                                <button type="button" class="btn btn-sm btn-success" data-toggle="modal" data-target="#customerModal"
                                                    data-title="Are you sure you want to activate the customer {{ $customer->social_reason }}?"
                                                    data-body="All customer data will be restored."
                                                    data-form="{{ route('customers.restore', $customer->id) }}"
                                                    data-method="POST"
                                                    data-class="btn btn-sm btn-success"
                                                    data-action="Activate">Activate</button>
                                                <button type="button" class="btn btn-sm btn-danger" data-toggle="modal" data-target="#customerModal"
                                                    data-title="Are you sure you want to delete the customer {{ $customer->social_reason }}?"
                                                    data-body="WARNING: All customer data will be deleted. This action can not be undone."
                                                    data-form="{{ route('customers.destroy', $customer->id) }}"
                                                    data-method="DELETE"
                                                    data-class="btn btn-sm btn-danger"
                                                    data-action="Delete">Delete</button>
                                            @endif
                                        @else
                                            @if(auth()->user()->can('delete_hard_customers'))
                                                <button type="button" class="btn btn-sm btn-warning" data-toggle="modal" data-target="#customerModal"
                                                    data-title="Are you sure you want to deactivate the customer {{ $customer->social_reason }}?"
                                                    data-body="The data of the customer will remain in the application, but the users that depend on it will not be able to access the data."
                                                    data-form="{{ route('customers.delete', $customer) }}"
                                                    data-method="DELETE"
                                                    data-class="btn btn-sm btn-warning"
                                                    data-action="Deactivate">Deactivate</button>
                                            @else
                                                <button type="button" class="btn btn-sm btn-warning" data-toggle="modal" data-target="#customerModal"
                                                    data-title="Are you sure you want to deactivate the customer {{ $customer->social_reason }}?"
                                                    data-body="The data of the customer will remain in the application, but the users that depend on it will not be able to access the data."
                                                    data-form="{{ route('customers.delete', $customer) }}"
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

@include('layouts.modal', ['model' => 'customer'])
@endsection
