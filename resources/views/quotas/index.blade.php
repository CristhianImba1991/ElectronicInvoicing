@extends('layouts.app')

@section('scripts')
<script src="{{ asset('js/jquery.dataTables.min.js') }}"></script>
<script id="modal" src="{{ asset('js/app/modal.js') }}"></script>
<script type="text/javascript">
$(document).ready(function(){
    $('#quotas').DataTable({
        "order": [[ 0, 'asc' ]]
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
                    {{ ucfirst(trans_choice(__('view.quota'), 1)) }}

                        <a href="{{ route('quotas.create') }}" class="btn btn-sm btn-primary float-right">{{ trans_choice(__('view.new'), 1) }}</a>

                </div>

                <div class="card-body">
                    @if (session('status'))
                        <div class="alert alert-success" role="alert">
                            {{ session('status') }}
                        </div>
                    @endif
                    <table id="quotas" class="display">
                        <thead>
                            <tr>
                                <th>{{__('view.description')}}</th>
                                <th>{{__('view.max_users_owner')}}</th>
                                <th>{{__('view.max_users_supervisor')}}</th>
                                <th>{{__('view.max_users_employee')}}</th>
                                <th>{{__('view.max_branches')}}</th>
                                <th>{{__('view.max_emission_points')}}</th>


                                    <th></th>

                            </tr>
                        </thead>
                        <tbody>
                            @foreach($quotas as $quota)
                                <tr>
                                    <td>

                                      <a href="{{ route('quotas.edit', $quota) }}"> {{ $quota->description }}</a>
                                      </td>
                                    <td>{{ $quota->max_users_owner }}</td>
                                    <td>{{ $quota->max_users_supervisor }}</td>
                                    <td>{{ $quota->max_users_employee }}</td>
                                    <td>{{ $quota->max_branches }}</td>
                                    <td>{{ $quota->max_emission_points }}</td>
                                    <td>

                                      <button type="button" class="btn btn-sm btn-danger" data-toggle="modal" data-target="#confirmation"
                                          data-title="{{ trans_choice(__('view.are_you_sure_you_want_to_delete_the_model', ['model' => trans_choice(__('view.quota'), 0), 'name' => $quota->description]), 1) }}"
                                          data-body="{{ trans_choice(__('view.warning_all_model_data_will_be_deleted_this_action_can_not_be_undone', ['model' => trans_choice(__('view.quota'), 0)]), 1) }}"
                                          data-form="{{ route('quotas.delete', $quota)}}"
                                          data-method="DELETE"
                                          data-class="btn btn-sm btn-danger"
                                          data-action="{{ __('view.delete') }}">{{ __('view.delete') }}</button>

                                      </form>
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
