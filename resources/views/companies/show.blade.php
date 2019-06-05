@extends('layouts.app')

@section('content')
<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-12">
            <div class="card">
                <div class="card-header">
                    {{ __('view.view_model', ['model' => trans_choice(__('view.company'), 0)]) }}
                    <a href="{{ route('companies.index') }}" class="btn btn-sm btn-secondary float-right">{{ __('view.back') }}</a>
                </div>

                <div class="card-body">
                    <div class="form-group">
                        <label for="ruc">{{ __('view.ruc') }}</label>
                        <input type="text" class="form-control" id="ruc" name="ruc" value="{{ $company->ruc }}" readonly>
                    </div>
                    <div class="form-group">
                        <label for="social_reason">{{ __('view.social_reason') }}</label>
                        <input type="text" class="form-control" id="social_reason" name="social_reason"  value="{{ $company->social_reason }}" readonly>
                    </div>
                    <div class="form-group">
                        <label for="tradename">{{ __('view.tradename') }}</label>
                        <input type="text" class="form-control" id="tradename" name="tradename"  value="{{ $company->tradename }}" readonly>
                    </div>
                    <div class="form-group">
                        <label for="address">{{ __('view.address') }}</label>
                        <input type="text" class="form-control" id="address" name="address"  value="{{ $company->address }}" readonly>
                    </div>
                    <div class="form-group">
                        <label for="special_contributor">{{ __('view.special_contributor') }}</label>
                        <input type="text" class="form-control" id="special_contributor" name="special_contributor"  value="{{ $company->special_contributor }}" readonly>
                    </div>
                    <div class="form-check">
                        @if ($company->keep_accounting)
                            <input class="form-check-input" checked="checked" type="checkbox" id="keep_accounting" name="keep_accounting" onclick="return false;" readonly>
                        @else
                            <input class="form-check-input" type="checkbox" id="keep_accounting" name="keep_accounting" onclick="return false;" readonly>
                        @endif
                        <label class="form-check-label" for="keep_accounting">{{ __('view.keep_accounting') }}</label>
                    </div>
                    <div class="form-group">
                        <label for="phone">{{ __('view.phone') }}</label>
                        <input type="text" class="form-control" id="phone" name="phone" value="{{ $company->phone }}" readonly>
                    </div>
                    <div class="form-group">
                        <label for="current_logo">{{ __('view.logo') }}</label><br>
                        <img class="img-fluid img-thumbnail" src="{{ url('storage/logo/images/'.$company->logo) }}" alt="">
                    </div>
                    <div class="form-group">
                        <label for="quota">{{ ucfirst(trans_choice(__('view.quota'), 0)) }}</label>
                        <select class="form-control selectpicker input-lg dynamic" id="quota" name="quota" data-live-search="true" title="{{ trans_choice(__('view.select_a_model', ['model' => trans_choice(__('view.quota'), 0)]), 1) }}">
                          @foreach ($quota as $quotas)
                          <option value="{{ $quotas->id }}">{{ $quotas->description }}</option>
                          @endforeach
                        </select>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
