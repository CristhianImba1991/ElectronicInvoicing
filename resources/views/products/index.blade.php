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
                    Products
                    @if(auth()->user()->can('create_products'))
                        <a href="{{ route('products.create') }}" class="btn btn-sm btn-primary float-right">New</a>
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
                                <th>Main Code</th>
                                <th>Auxiliary Code</th>
                                <th>Stock</th>
                                <th>Branch</th>
                                <th>Company</th>
                                <th></th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($products as $product)
                                <tr>
                                    <td>
                                        @if($product->deleted_at !== NULL)
                                            {{ $product->main_code }}
                                        @else
                                            @if(auth()->user()->can('update_products'))
                                                <a href="{{ route('products.edit', $product) }}">{{ $product->main_code }}</a>
                                            @elseif(auth()->user()->can('read_products'))
                                                <a href="{{ route('products.show', $product) }}">{{ $product->main_code }}</a>
                                            @else
                                                {{ $product->main_code }}
                                            @endif
                                        @endif
                                    </td>
                                    <td>{{ $product->auxiliary_code }}</td>
                                    <td>{{ $product->stock }}</td>
                                    <td>{{ $product->branch->name }}</td>
                                    <td>{{ $product->branch->company->tradename }}</td>
                                    <td>
                                        @if($product->deleted_at !== NULL)
                                            @if(auth()->user()->can('delete_hard_products'))
                                                <button type="button" class="btn btn-sm btn-success" data-toggle="modal" data-target="#confirmation"
                                                    data-title="Are you sure you want to activate the product {{ $product->main_code }}?"
                                                    data-body="All product data will be restored."
                                                    data-form="{{ route('products.restore', $product->id) }}"
                                                    data-method="POST"
                                                    data-class="btn btn-sm btn-success"
                                                    data-action="Activate">Activate</button>
                                                <button type="button" class="btn btn-sm btn-danger" data-toggle="modal" data-target="#confirmation"
                                                    data-title="Are you sure you want to delete the product {{ $product->main_code }}?"
                                                    data-body="WARNING: All product data will be deleted. This action can not be undone."
                                                    data-form="{{ route('products.destroy', $product->id) }}"
                                                    data-method="DELETE"
                                                    data-class="btn btn-sm btn-danger"
                                                    data-action="Delete">Delete</button>
                                            @endif
                                        @else
                                            @if(auth()->user()->can('delete_hard_products'))
                                                <button type="button" class="btn btn-sm btn-warning" data-toggle="modal" data-target="#confirmation"
                                                    data-title="Are you sure you want to deactivate the product {{ $product->main_code }}?"
                                                    data-body="The data of the product will remain in the application, but the users that depend on it will not be able to access the data. If you want to restore it, contact the administrator."
                                                    data-form="{{ route('products.delete', $product) }}"
                                                    data-method="DELETE"
                                                    data-class="btn btn-sm btn-warning"
                                                    data-action="Deactivate">Deactivate</button>
                                            @else
                                                <button type="button" class="btn btn-sm btn-warning" data-toggle="modal" data-target="#confirmation"
                                                    data-title="Are you sure you want to deactivate the product {{ $product->main_code }}?"
                                                    data-body="The data of the product will remain in the application, but the users that depend on it will not be able to access the data. If you want to restore it, contact the administrator."
                                                    data-form="{{ route('products.delete', $product) }}"
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
