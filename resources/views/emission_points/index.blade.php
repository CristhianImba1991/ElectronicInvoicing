@extends('layouts.app')

@section('scripts')
<script src="{{ asset('js/jquery.dataTables.min.js') }}"></script>
<script id="modal" src="{{ asset('js/app/modal.js') }}"></script>
<script type="text/javascript">
$(document).ready(function(){
    $('#emission_points').DataTable({
        "order": [[ 0, 'asc' ], [ 1, 'asc' ], [ 2, 'asc' ]]
    });
});
</script>
@endsection

@section('styles')
<link href="{{ asset('css/jquery.dataTables.min.css') }}" rel="stylesheet">
@endsection

@section('content')
<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-12">
            <div class="card">
                <div class="card-header">
                    {{ ucfirst(trans_choice(__('view.emission_point'), 1)) }}
                    @if(auth()->user()->can('create_emission_points'))
                        <a href="{{ route('emission_points.create') }}" class="btn btn-sm btn-primary float-right">{{ trans_choice(__('view.new'), 0) }}</a>
                    @endif
                </div>

                <div class="card-body">
                    @if (session('status'))
                        <div class="alert alert-success" role="alert">
                            {{ session('status') }}
                        </div>
                    @endif
                    <table id="emission_points" class="display">
                        <thead>
                            <tr>
                                <th>{{ ucfirst(trans_choice(__('view.company'), 0)) }}</th>
                                <th>{{ __('view.establishment') }}</th>
                                <th>{{ __('view.code') }}</th>
                                <th></th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($emissionPoints as $emissionPoint)
                                <tr>
                                    <td>{{ $emissionPoint->branch->company->tradename }}</td>
                                    <td>{{ $emissionPoint->branch->establishment }}</td>
                                    <td>
                                        @if($emissionPoint->deleted_at !== NULL)
                                            {{ $emissionPoint->code }}
                                        @else
                                            @if(auth()->user()->can('read_emission_points'))
                                                <a href="{{ route('emission_points.show', $emissionPoint) }}">{{ $emissionPoint->code }}</a>
                                            @else
                                                {{ $emissionPoint->name }}
                                            @endif
                                        @endif
                                    </td>
                                    <td>
                                        @if($emissionPoint->deleted_at !== NULL)
                                            @if(auth()->user()->can('delete_hard_emission_points'))
                                                <button type="button" class="btn btn-sm btn-success" data-toggle="modal" data-target="#confirmation"
                                                    data-title="{{ trans_choice(__('view.are_you_sure_you_want_to_activate_the_model', ['model' => trans_choice(__('view.emission_point'), 0), 'name' => $emissionPoint->code]), 0) }}"
                                                    data-body="{{ trans_choice(__('view.all_model_data_will_be_restored', ['model' => trans_choice(__('view.emission_point'), 0)]), 0) }}"
                                                    data-form="{{ route('emission_points.restore', $emissionPoint->id) }}"
                                                    data-method="POST"
                                                    data-class="btn btn-sm btn-success"
                                                    data-action="{{ __('view.activate') }}">{{ __('view.activate') }}</button>
                                                <button type="button" class="btn btn-sm btn-danger" data-toggle="modal" data-target="#confirmation"
                                                    data-title="{{ trans_choice(__('view.are_you_sure_you_want_to_delete_the_model', ['model' => trans_choice(__('view.emission_point'), 0), 'name' => $emissionPoint->code]), 0) }}"
                                                    data-body="{{ trans_choice(__('view.warning_all_model_data_will_be_deleted_this_action_can_not_be_undone', ['model' => trans_choice(__('view.emission_point'), 0)]), 0) }}"
                                                    data-form="{{ route('emission_points.destroy', $emissionPoint->id) }}"
                                                    data-method="DELETE"
                                                    data-class="btn btn-sm btn-danger"
                                                    data-action="{{ __('view.delete') }}">{{ __('view.delete') }}</button>
                                            @endif
                                        @else
                                            @if(auth()->user()->can('delete_hard_emission_points'))
                                                <button type="button" class="btn btn-sm btn-warning" data-toggle="modal" data-target="#confirmation"
                                                    data-title="{{ trans_choice(__('view.are_you_sure_you_want_to_deactivate_the_model', ['model' => trans_choice(__('view.emission_point'), 0), 'name' => $emissionPoint->code]), 0) }}"
                                                    data-body="{{ trans_choice(__('view.the_data_of_the_model_will_remain_in_the_application', ['model' => trans_choice(__('view.emission_point'), 0)]), 0) }}"
                                                    data-form="{{ route('emission_points.delete', $emissionPoint) }}"
                                                    data-method="DELETE"
                                                    data-class="btn btn-sm btn-warning"
                                                    data-action="{{ __('view.deactivate') }}">{{ __('view.deactivate') }}</button>
                                            @else
                                                <button type="button" class="btn btn-sm btn-warning" data-toggle="modal" data-target="#confirmation"
                                                    data-title="{{ trans_choice(__('view.are_you_sure_you_want_to_deactivate_the_model', ['model' => trans_choice(__('view.emission_point'), 0), 'name' => $emissionPoint->code]), 0) }}"
                                                    data-body="{{ trans_choice(__('view.the_data_of_the_model_will_remain_in_the_application', ['model' => trans_choice(__('view.emission_point'), 0)]), 0) }}"
                                                    data-form="{{ route('emission_points.delete', $emissionPoint) }}"
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
