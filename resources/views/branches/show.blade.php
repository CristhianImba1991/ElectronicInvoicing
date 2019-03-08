@extends('layouts.app')

@section('content')
<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="card">
                <div class="card-header">
                    {{ __('view.view_model', ['model' => trans_choice(__('view.branch'), 0)]) }}
                    <a href="{{ route('branches.index') }}" class="btn btn-sm btn-secondary float-right">{{ __('view.back') }}</a>
                </div>

                <div class="card-body">
                    <div class="form-group">
                        <label for="ruc">{{ ucfirst(trans_choice(__('view.company'), 0)) }}</label>
                        <input class="form-control" type="text" id="ruc" name="ruc" value="{{ $branch->company->tradename }} - {{ $branch->company->social_reason }}" readonly>
                    </div>
                    <div class="form-group">
                        <label for="establishment">{{ ucfirst(__('view.establishment')) }}</label>
                        <input class="form-control" type="text" id="establishment" name="establishment" value="{{ $branch->establishment }}" readonly>
                    </div>
                    <div class="form-group">
                        <label for="name">{{ ucfirst(__('view.name')) }}</label>
                        <input class="form-control" type="text" id="name" name="name" value="{{ $branch->name }}" readonly>
                    </div>
                    <div class="form-group">
                        <label for="address">{{ ucfirst(__('view.address')) }}</label>
                        <input class="form-control" type="text" id="address" name="address" value="{{ $branch->address }}" readonly>
                    </div>
                    <div class="form-group">
                        <label for="phone">{{ ucfirst(__('view.phone')) }}</label>
                        <input class="form-control" type="text" id="phone" name="phone" value="{{ $branch->phone }}" readonly>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
