@extends('layouts.app')

@section('content')
<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="card">
                <div class="card-header">
                    View company
                    <a href="{{ route('companies.index') }}" class="btn btn-sm btn-secondary float-right">Back</a>
                </div>

                <div class="card-body">
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
                        <input type="text" class="form-control" id="main_code" name="main_code" value="{{ $product->main_code }}" readonly>
                    </div>
                    <div class="form-group">
                        <label for="social_reason">Auxiliary Code</label>
                        <input type="text" class="form-control" id="auxiliary_code" name="auxiliary_code"  value="{{ $product->auxiliary_code }}" readonly>
                    </div>
                    <div class="form-group">
                        <label for="tradename">Unit Price</label>
                        <input type="number" step="0.01" class="form-control" id="unit_price" name="unit_price"  value="{{ $product->unit_price }}" readonly>
                    </div>
                    <div class="form-group">
                        <label for="address">Description</label>
                        <input type="text" class="form-control" id="description" name="description"  value="{{ $product->description }}" readonly>
                    </div>
                    <div class="form-group">
                        <label for="special_contributor">Stock</label>
                        <input type="number" class="form-control" id="stock" name="stock"  value="{{ $product->stock }}" readonly>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
