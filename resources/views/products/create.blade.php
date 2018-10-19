@extends('layouts.app')

@section('content')
<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="card">
                <div class="card-header">
                    New product
                    <a href="{{ route('products.index') }}" class="btn btn-sm btn-secondary float-right">Cancel</a>
                </div>

                <form action="{{ route('companies.store') }}" method="post" enctype="multipart/form-data">
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
                            <label for="main_code">Main Code</label>
                            <input type="text" class="form-control" id="main_code" name="main_code" value="{{ old('ruc') }}">
                        </div>
                        <div class="form-group">
                            <label for="social_reason">Auxiliary Code</label>
                            <input type="text" class="form-control" id="social_reason" name="main_code"  value="{{ old('social_reason') }}">
                        </div>
                        <div class="form-group">
                            <label for="tradename">Unit Price</label>
                            <input type="number" class="form-control" id="tradename" name="tradename"  value="{{ old('tradename') }}">
                        </div>
                        <div class="form-group">
                            <label for="address">Description</label>
                            <input type="text" class="form-control" id="address" name="address"  value="{{ old('address') }}">
                        </div>
                        <div class="form-group">
                            <label for="special_contributor">Stock</label>
                            <input type="number" class="form-control" id="special_contributor" name="special_contributor"  value="{{ old('special_contributor') }}">
                        </div>
                    </div>

                    <div class="card-footer"><button type="submit" class="btn btn-sm btn-success">Add</button></div>

                </form>

            </div>
        </div>
    </div>
</div>
@endsection
