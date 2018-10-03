@extends('layouts.app')

@section('scripts')
@endsection

@section('content')
<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="card">
                <div class="card-header">
                    View branch
                    <a href="{{ route('branches.index') }}" class="btn btn-sm btn-secondary float-right">Back</a>
                </div>

                <div class="card-body">
                    <div class="form-group">
                        <label for="ruc">Company</label>
                        <input class="form-control" type="text" id="ruc" name="ruc" value="{{ $branch->company->tradename }} - {{ $branch->company->social_reason }}" readonly>
                    </div>
                    <div class="form-group">
                        <label for="establishment">Establishment</label>
                        <input class="form-control" type="text" id="establishment" name="establishment" value="{{ $branch->establishment }}" readonly>
                    </div>
                    <div class="form-group">
                        <label for="name">Name</label>
                        <input class="form-control" type="text" id="name" name="name" value="{{ $branch->name }}" readonly>
                    </div>
                    <div class="form-group">
                        <label for="address">Address</label>
                        <input class="form-control" type="text" id="address" name="address" value="{{ $branch->address }}" readonly>
                    </div>
                    <div class="form-group">
                        <label for="phone">Phone</label>
                        <input class="form-control" type="text" id="phone" name="phone" value="{{ $branch->phone }}" readonly>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
