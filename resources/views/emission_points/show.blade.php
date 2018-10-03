@extends('layouts.app')

@section('scripts')
@endsection

@section('content')
<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="card">
                <div class="card-header">
                    View emission point
                    <a href="{{ route('emission_points.index') }}" class="btn btn-sm btn-secondary float-right">Back</a>
                </div>

                <div class="card-body">
                    <div class="form-group">
                        <label for="company">Company</label>
                        <input class="form-control" type="text" id="company" name="company" value="{{ $emissionPoint->branch->company->tradename }} - {{ $emissionPoint->branch->company->social_reason }}" readonly>
                    </div>
                    <div class="form-group">
                        <label for="branch">Branch</label>
                        <input class="form-control" type="text" id="branch" name="branch" value="{{ $emissionPoint->branch->name }}" readonly>
                    </div>
                    <div class="form-group">
                        <label for="emissionpoint">Emission point</label>
                        <input class="form-control" type="text" id="emissionpoint" name="emissionpoint" value="{{ $emissionPoint->code }}" readonly>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
