@extends('layouts.app')

@section('styles')
<link rel="stylesheet" href="{{ asset('css/bootstrap-select.min.css') }}">
@endsection
@section('content')
<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="card">
                <div class="card-header">
                    Edit product
                    <a href="{{ route('products.index') }}" class="btn btn-sm btn-secondary float-right">Cancel</a>
                </div>

                <form action="{{ route('products.update', $product) }}" method="post" enctype="multipart/form-data">
                    {{ csrf_field() }}

                    <div class="card-body">
                        @if ($errors->count() > 0)
                            <div class="alert alert-danger" role="alert">
                                <h5>The following errors were found:</h5>
                                <ul>
                                    @foreach($errors->all() as $error)
                                        <li>{{ $error }}</li>
                                    @endforeach
                                </ul>
                            </div>
                        @endif
                        <div class="form-group">
                            <label for="company">Company</label>
                            <input class="form-control" type="text" id="company" name="company" value="{{ $product->branch->company->tradename }} - {{ $product->branch->company->social_reason }}" readonly>
                        </div>
                        <div class="form-group">
                            <label for="branch">Branch</label>
                            <input class="form-control" type="text" id="branch" name="branch" value="{{ $product->branch->name }}" readonly>
                        </div>
                        <div class="form-group">
                            <label for="main_code">Main Code</label>
                            <input type="text" class="form-control" id="main_code" name="main_code" value="{{ $product->main_code }}">
                        </div>
                        <div class="form-group">
                            <label for="social_reason">Auxiliary Code</label>
                            <input type="text" class="form-control" id="auxiliary_code" name="auxiliary_code"  value="{{ $product->auxiliary_code }}">
                        </div>
                        <div class="form-group">
                            <label for="tradename">Unit Price</label>
                            <input type="number" step="0.01" class="form-control" id="unit_price" name="unit_price"  value="{{ $product->unit_price }}">
                        </div>
                        <div class="form-group">
                            <label for="address">Description</label>
                            <input type="text" class="form-control" id="description" name="description"  value="{{ $product->description }}">
                        </div>
                        <div class="form-group">
                            <label for="special_contributor">Stock</label>
                            <input type="number" class="form-control" id="stock" name="stock"  value="{{ $product->stock }}">
                        </div>
                    </div>

                    <div class="card-footer"><button type="submit" class="btn btn-sm btn-success">Update</button></div>

                </form>

            </div>
        </div>
    </div>
</div>
@endsection
