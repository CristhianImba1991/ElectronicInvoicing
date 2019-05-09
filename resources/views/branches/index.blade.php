@extends('layouts.app')

@section('scripts')
<script src="{{ asset('js/jquery.dataTables.min.js') }}"></script>
<script id="modal" src="{{ asset('js/app/modal.js') }}"></script>
<script type="text/javascript">
$(document).ready(function(){
    $('#branches').DataTable({
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
                    {{ ucfirst(trans_choice(__('view.branch'), 1)) }}
                    @if(auth()->user()->can('create_branches'))
                        <a href="{{ route('branches.create') }}" class="btn btn-sm btn-primary float-right">{{ trans_choice(__('view.new'), 1) }}</a>
                    @endif
                </div>

                <div class="card-body">
                    @if (session('status'))
                        <div class="alert alert-success" role="alert">
                            {{ session('status') }}
                        </div>
                    @endif
                    <table id="branches" class="display">
                        <thead>
                            <tr>
                                <th>{{ ucfirst(trans_choice(__('view.company'), 0)) }}</th>
                                <th>{{ ucfirst(__('view.establishment')) }}</th>
                                <th>{{ ucfirst(__('view.name')) }}</th>
                                <th></th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($branches as $branch)
                                <tr>
                                    <td>{{ $branch->company->tradename }}</td>
                                    <td>{{ $branch->establishment }}</td>
                                    <td>
                                        @if($branch->deleted_at !== NULL)
                                            {{ $branch->name }}
                                        @else
                                            @if(auth()->user()->can('update_branches'))
                                                <a href="{{ route('branches.edit', $branch) }}">{{ $branch->name }}</a>
                                            @elseif(auth()->user()->can('read_branches'))
                                                <a href="{{ route('branches.show', $branch) }}">{{ $branch->name }}</a>
                                            @else
                                                {{ $branch->name }}
                                            @endif
                                        @endif
                                    </td>
                                    <td>
                                        @if($branch->deleted_at !== NULL)
                                            @if(auth()->user()->can('delete_hard_branches'))
                                                <button type="button" class="btn btn-sm btn-success" data-toggle="modal" data-target="#confirmation"
                                                    data-title="{{ trans_choice(__('view.are_you_sure_you_want_to_activate_the_model', ['model' => trans_choice(__('view.branch'), 0), 'name' => $branch->name]), 1) }}"
                                                    data-body="{{ trans_choice(__('view.all_model_data_will_be_restored', ['model' => trans_choice(__('view.branch'), 0)]), 1) }}"
                                                    data-form="{{ route('branches.restore', $branch->id) }}"
                                                    data-method="POST"
                                                    data-class="btn btn-sm btn-success"
                                                    data-action="{{ __('view.activate') }}">{{ __('view.activate') }}</button>
                                                <button type="button" class="btn btn-sm btn-danger" data-toggle="modal" data-target="#confirmation"
                                                    data-title="{{ trans_choice(__('view.are_you_sure_you_want_to_delete_the_model', ['model' => trans_choice(__('view.branch'), 0), 'name' => $branch->name]), 1) }}"
                                                    data-body="{{ trans_choice(__('view.warning_all_model_data_will_be_deleted_this_action_can_not_be_undone', ['model' => trans_choice(__('view.branch'), 0)]), 1) }}"
                                                    data-form="{{ route('branches.destroy', $branch->id) }}"
                                                    data-method="DELETE"
                                                    data-class="btn btn-sm btn-danger"
                                                    data-action="{{ __('view.delete') }}">{{ __('view.delete') }}</button>
                                            @endif
                                        @else
                                            @if(auth()->user()->can('delete_hard_branches'))
                                                <button type="button" class="btn btn-sm btn-warning" data-toggle="modal" data-target="#confirmation"
                                                    data-title="{{ trans_choice(__('view.are_you_sure_you_want_to_deactivate_the_model', ['model' => trans_choice(__('view.branch'), 0), 'name' => $branch->name]), 1) }}"
                                                    data-body="{{ trans_choice(__('view.the_data_of_the_model_will_remain_in_the_application', ['model' => trans_choice(__('view.branch'), 0)]), 1) }}"
                                                    data-form="{{ route('branches.delete', $branch) }}"
                                                    data-method="DELETE"
                                                    data-class="btn btn-sm btn-warning"
                                                    data-action="{{ __('view.deactivate') }}">{{ __('view.deactivate') }}</button>
                                            @else
                                                <button type="button" class="btn btn-sm btn-warning" data-toggle="modal" data-target="#confirmation"
                                                    data-title="{{ trans_choice(__('view.are_you_sure_you_want_to_deactivate_the_model', ['model' => trans_choice(__('view.branch'), 0), 'name' => $branch->name]), 1) }}"
                                                    data-body="{{ trans_choice(__('view.the_data_of_the_model_will_remain_in_the_application', ['model' => trans_choice(__('view.branch'), 0)]), 1) }}"
                                                    data-form="{{ route('branches.delete', $branch) }}"
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
