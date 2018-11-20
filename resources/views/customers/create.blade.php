@extends('layouts.app')

@section('scripts')
<script src="{{ asset('js/bootstrap-select.min.js') }}"></script>
@endsection

@section('styles')
<link rel="stylesheet" href="{{ asset('css/bootstrap-select.min.css') }}">
@endsection

@section('content')
<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="card">
                <div class="card-header">
                    New customer
                    <a href="{{ route('customers.index') }}" class="btn btn-sm btn-secondary float-right">Cancel</a>
                </div>

                <form action="{{ route('customers.store') }}" method="post">
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
                            <select class="form-control selectpicker" id="company" name="company" data-live-search="true" title="Select a company ...">
                                @foreach($companies as $company)
                                    <option value="{{ $company->id }}" {{ $company->id === old('company') ? "selected" : "" }}>{{ $company->tradename }} - {{ $company->social_reason }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="form-group">
                            <label for="identification_type">Identification type</label>
                            <select class="form-control selectpicker" id="identification_type" name="identification_type" data-live-search="true" title="Select a identification type ...">
                                @foreach($identificationTypes as $identificationType)
                                    <option value="{{ $identificationType->id }}" {{ $identificationType->id === old('identification_type') ? "selected" : "" }}>{{ $identificationType->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="form-group">
                            <label for="identification">Identification</label>
                            <input class="form-control" type="text" id="identification" name="identification" value="{{ old('identification') }}">
                        </div>
                        <div class="form-group">
                            <label for="social_reason">Social reason</label>
                            <input class="form-control" type="text" id="social_reason" name="social_reason" value="{{ old('social_reason') }}">
                        </div>
                        <div class="form-group">
                            <label for="name">Address</label>
                            <input class="form-control" type="text" id="address" name="address" value="{{ old('address') }}">
                        </div>
                        <div class="form-group">
                            <label for="phone">Phone</label>
                            <input class="form-control" type="text" id="phone" name="phone" value="{{ old('phone') }}">
                        </div>
                        <div class="form-group">
                            <label for="email">Email</label>
                            <input class="form-control" type="email" id="email" name="email" value="{{ old('email') }}" multiple>
                        </div>

                    </div>

                    <div class="card-footer">
                        <button type="submit" class="btn btn-sm btn-success">Add</button>
                    </div>

                </form>

            </div>
        </div>
    </div>
</div>
@endsection
