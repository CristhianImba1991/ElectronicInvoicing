@extends('layouts.app')

@section('content')
<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="card">
                <div class="card-header">
                    New company
                    <a href="{{ route('companies.index') }}" class="btn btn-sm btn-secondary float-right">Cancel</a>
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
                            <label for="ruc">RUC</label>
                            <input type="text" class="form-control" id="ruc" name="ruc" value="{{ old('ruc') }}">
                        </div>
                        <div class="form-group">
                            <label for="social_reason">Social reason</label>
                            <input type="text" class="form-control" id="social_reason" name="social_reason"  value="{{ old('social_reason') }}">
                        </div>
                        <div class="form-group">
                            <label for="tradename">Tradename</label>
                            <input type="text" class="form-control" id="tradename" name="tradename"  value="{{ old('tradename') }}">
                        </div>
                        <div class="form-group">
                            <label for="address">Address</label>
                            <input type="text" class="form-control" id="address" name="address"  value="{{ old('address') }}">
                        </div>
                        <div class="form-group">
                            <label for="special_contributor">Special contributor</label>
                            <input type="text" class="form-control" id="special_contributor" name="special_contributor"  value="{{ old('special_contributor') }}">
                        </div>
                        <div class="form-check">
                            <input class="form-check-input" type="checkbox" value="" id="keep_accounting" name="keep_accounting">
                            <label class="form-check-label" for="keep_accounting">Keep accounting</label>
                        </div>
                        <div class="form-group">
                            <label for="phone">Phone</label>
                            <input type="text" class="form-control" id="phone" name="phone" value="{{ old('phone') }}">
                        </div>
                        <div class="form-group">
                            <label for="logo">Logo</label>
                            <input type="file" class="form-control-file" id="logo" name="logo">
                        </div>
                        <div class="form-group">
                            <label for="sign">Sign</label>
                            <input type="file" class="form-control-file" id="sign" name="sign">
                        </div>
                        <div class="form-group">
                            <label for="password">Password</label>
                            <input type="password" class="form-control" id="password" name="password" value="">
                        </div>
                    </div>

                    <div class="card-footer"><button type="submit" class="btn btn-sm btn-success">Add</button></div>

                </form>

            </div>
        </div>
    </div>
</div>
@endsection
