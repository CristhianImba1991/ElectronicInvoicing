@extends('layouts.app')

@section('scripts')
<script src="{{ asset('js/jquery.dataTables.min.js') }}"></script>
<script id="modal" src="{{ asset('js/app/modal.js') }}"></script>
@endsection

@section('styles')
<link href="{{ asset('css/jquery.dataTables.min.css') }}" rel="stylesheet">
@endsection

@section('content')
<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="card">
                <div class="card-header">
                    {{ ucfirst(trans_choice(__('view.product'), 1)) }}
                    @if(auth()->user()->can('create_products'))
                        <a href="{{ route('products.create') }}" class="btn btn-sm btn-primary float-right">{{ trans_choice(__('view.new'), 0) }}</a>
                    @endif
                </div>

                <div class="card-body">
                    @if (session('status'))
                        <div class="alert alert-success" role="alert">
                            {{ session('status') }}
                        </div>
                    @endif
                    <table id="table" class="display">
                        <thead>
                            <tr>
                                <th>{{ __('view.main_code') }}</th>
                                <th>{{ __('view.auxiliary_code') }}</th>
                                <th>{{ __('view.stock') }}</th>
                                <th>{{ ucfirst(trans_choice(__('view.branch'), 0)) }}</th>
                                <th>{{ ucfirst(trans_choice(__('view.company'), 0)) }}</th>
                                <th></th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($products as $product)
                                <tr>
                                    <td>
                                        @if($product->deleted_at !== NULL)
                                            {{ $product->main_code }}
                                        @else
                                            @if(auth()->user()->can('update_products'))
                                                <a href="{{ route('products.edit', $product) }}">{{ $product->main_code }}</a>
                                            @elseif(auth()->user()->can('read_products'))
                                                <a href="{{ route('products.show', $product) }}">{{ $product->main_code }}</a>
                                            @else
                                                {{ $product->main_code }}
                                            @endif
                                        @endif
                                    </td>
                                    <td>{{ $product->auxiliary_code }}</td>
                                    <td>{{ $product->stock }}</td>
                                    <td>{{ $product->branch->name }}</td>
                                    <td>{{ $product->branch->company->tradename }}</td>
                                    <td>
                                        @if($product->deleted_at !== NULL)
                                            @if(auth()->user()->can('delete_hard_products'))
                                                <button type="button" class="btn btn-sm btn-success" data-toggle="modal" data-target="#confirmation"
                                                    data-title="{{ trans_choice(__('view.are_you_sure_you_want_to_activate_the_model', ['model' => trans_choice(__('view.product'), 0), 'name' => $product->main_code]), 0) }}"
                                                    data-body="{{ trans_choice(__('view.all_model_data_will_be_restored', ['model' => trans_choice(__('view.product'), 0)]), 0) }}"
                                                    data-form="{{ route('products.restore', $product->id) }}"
                                                    data-method="POST"
                                                    data-class="btn btn-sm btn-success"
                                                    data-action="{{ __('view.activate') }}">{{ __('view.activate') }}</button>
                                                <button type="button" class="btn btn-sm btn-danger" data-toggle="modal" data-target="#confirmation"
                                                    data-title="{{ trans_choice(__('view.are_you_sure_you_want_to_delete_the_model', ['model' => trans_choice(__('view.product'), 0), 'name' => $product->main_code]), 0) }}"
                                                    data-body="{{ trans_choice(__('view.warning_all_model_data_will_be_deleted_this_action_can_not_be_undone', ['model' => trans_choice(__('view.product'), 0)]), 0) }}"
                                                    data-form="{{ route('products.destroy', $product->id) }}"
                                                    data-method="DELETE"
                                                    data-class="btn btn-sm btn-danger"
                                                    data-action="{{ __('view.delete') }}">{{ __('view.delete') }}</button>
                                            @endif
                                        @else
                                            @if(auth()->user()->can('delete_hard_products'))
                                                <button type="button" class="btn btn-sm btn-warning" data-toggle="modal" data-target="#confirmation"
                                                    data-title="{{ trans_choice(__('view.are_you_sure_you_want_to_deactivate_the_model', ['model' => trans_choice(__('view.product'), 0), 'name' => $product->main_code]), 0) }}"
                                                    data-body="{{ trans_choice(__('view.the_data_of_the_model_will_remain_in_the_application', ['model' => trans_choice(__('view.product'), 0)]), 0) }}"
                                                    data-form="{{ route('products.delete', $product) }}"
                                                    data-method="DELETE"
                                                    data-class="btn btn-sm btn-warning"
                                                    data-action="{{ __('view.deactivate') }}">{{ __('view.deactivate') }}</button>
                                            @else
                                                <button type="button" class="btn btn-sm btn-warning" data-toggle="modal" data-target="#confirmation"
                                                    data-title="{{ trans_choice(__('view.are_you_sure_you_want_to_deactivate_the_model', ['model' => trans_choice(__('view.product'), 0), 'name' => $product->main_code]), 0) }}"
                                                    data-body="{{ trans_choice(__('view.the_data_of_the_model_will_remain_in_the_application', ['model' => trans_choice(__('view.product'), 0)]), 0) }}"
                                                    data-form="{{ route('products.delete', $product) }}"
                                                    data-method="DELETE"
                                                    data-class="btn btn-sm btn-warning"
                                                    data-action="{{ __('view.delete') }}">{{ __('view.delete') }}</button>
                                            @endif
                                        @endif
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

@include('layouts.confirmation')
@endsection
