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
                        <label for="ruc">RUC</label>
                        <input type="text" class="form-control" id="ruc" name="ruc" value="{{ $company->ruc }}" readonly>
                    </div>
                    <div class="form-group">
                        <label for="social_reason">Social reason</label>
                        <input type="text" class="form-control" id="social_reason" name="social_reason"  value="{{ $company->social_reason }}" readonly>
                    </div>
                    <div class="form-group">
                        <label for="tradename">Tradename</label>
                        <input type="text" class="form-control" id="tradename" name="tradename"  value="{{ $company->tradename }}" readonly>
                    </div>
                    <div class="form-group">
                        <label for="address">Address</label>
                        <input type="text" class="form-control" id="address" name="address"  value="{{ $company->address }}" readonly>
                    </div>
                    <div class="form-group">
                        <label for="special_contributor">Special contributor</label>
                        <input type="text" class="form-control" id="special_contributor" name="special_contributor"  value="{{ $company->special_contributor }}" readonly>
                    </div>
                    <div class="form-check">
                        @if ($company->keep_accounting)
                            <input class="form-check-input" checked="checked" type="checkbox" id="keep_accounting" name="keep_accounting" onclick="return false;" readonly>
                        @else
                            <input class="form-check-input" type="checkbox" id="keep_accounting" name="keep_accounting" onclick="return false;" readonly>
                        @endif
                        <label class="form-check-label" for="keep_accounting">Keep accounting</label>
                    </div>
                    <div class="form-group">
                        <label for="phone">Phone</label>
                        <input type="text" class="form-control" id="phone" name="phone" value="{{ $company->phone }}" readonly>
                    </div>
                    <div class="form-group">
                        <label for="current_logo">Logo</label><br>
                        <img class="img-fluid img-thumbnail" src="{{ url('storage/logo/images/'.$company->logo) }}" alt="">
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
