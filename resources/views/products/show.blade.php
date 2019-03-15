@extends('layouts.app')

@section('content')
<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="card">
                <div class="card-header">
                    {{ __('view.view_model', ['model' => trans_choice(__('view.product'), 0)]) }}
                    <a href="{{ route('companies.index') }}" class="btn btn-sm btn-secondary float-right">{{ __('view.back') }}</a>
                </div>

                <div class="card-body">
                    <div class="form-group">
                        <label for="company">{{ ucfirst(trans_choice(__('view.company'), 0)) }}</label>
                        <input class="form-control" type="text" id="company" name="company" value="{{ $product->branch->company->tradename }} - {{ $product->branch->company->social_reason }}" readonly>
                    </div>
                    <div class="form-group">
                        <label for="branch">{{ ucfirst(trans_choice(__('view.branch'), 0)) }}</label>
                        <input class="form-control" type="text" id="branch" name="branch" value="{{ $product->branch->name }}" readonly>
                    </div>
                    <div class="form-group">
                        <label for="main_code">{{ __('view.main_code') }}</label>
                        <input type="text" class="form-control" id="main_code" name="main_code" value="{{ $product->main_code }}" readonly>
                    </div>
                    <div class="form-group">
                        <label for="auxiliary_code">{{ __('view.auxiliary_code') }}</label>
                        <input type="text" class="form-control" id="auxiliary_code" name="auxiliary_code"  value="{{ $product->auxiliary_code }}" readonly>
                    </div>
                    <div class="form-group">
                        <label for="unit_price">{{ __('view.unit_price') }}</label>
                        <input type="number" step="0.01" class="form-control" id="unit_price" name="unit_price"  value="{{ $product->unit_price }}" readonly>
                    </div>
                    <div class="form-group">
                        <label for="description">{{ __('view.description') }}</label>
                        <input type="text" class="form-control" id="description" name="description"  value="{{ $product->description }}" readonly>
                    </div>
                    <div class="form-group">
                        <label for="stock">{{ __('view.stock') }}</label>
                        <input type="number" class="form-control" id="stock" name="stock"  value="{{ $product->stock }}" readonly>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
