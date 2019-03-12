@extends('layouts.app')

@section('content')
<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="card">
                <div class="card-header">
                    {{ __('view.view_model', ['model' => trans_choice(__('view.customer'), 0)]) }}
                    <a href="{{ route('customers.index') }}" class="btn btn-sm btn-secondary float-right">{{ __('view.back') }}</a>
                </div>

                <div class="card-body">
                    <div class="form-group">
                        <label for="company">{{ ucfirst(trans_choice(__('view.company'), 0)) }}</label>
                        <input class="form-control" type="text" id="company" name="company" value="{{ $customer->companies()->first()->tradename }} - {{ $customer->companies()->first()->social_reason }}" readonly>
                    </div>
                    <div class="form-group">
                        <label for="identification">{{ __('view.identification') }}</label>
                        <input class="form-control" type="text" id="identification" name="identification" value="{{ $customer->identificationType->name }}: {{ $customer->identification }}" readonly>
                    </div>
                    <div class="form-group">
                        <label for="social_reason">{{ __('view.social_reason') }}</label>
                        <input class="form-control" type="text" id="social_reason" name="social_reason" value="{{ $customer->social_reason }}" readonly>
                    </div>
                    <div class="form-group">
                        <label for="address">{{ __('view.address') }}</label>
                        <input class="form-control" type="text" id="address" name="address" value="{{ $customer->address }}" readonly>
                    </div>
                    <div class="form-group">
                        <label for="phone">{{ __('view.phone') }}</label>
                        <input class="form-control" type="text" id="phone" name="phone" value="{{ $customer->phone }}" readonly>
                    </div>
                    <div class="form-group">
                        <label for="email">{{ __('view.email') }}</label>
                        <input class="form-control" type="text" id="email" name="email" value="{{ $customer->email }}" readonly>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
