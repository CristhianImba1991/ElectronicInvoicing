@extends('layouts.app')

@section('scripts')
<script src="{{ asset('js/jquery.dataTables.min.js') }}"></script>
<script id="modal" src="{{ asset('js/app/modal.js') }}"></script>
<script type="text/javascript">
$(document).ready(function(){
    @can('create_vouchers')
        $('#draft-vouchers').DataTable({
            "order": [[ 3, 'desc' ]]
        });
    @endcan
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
                    {{ __('view.draft_model', ['model' => trans_choice(__('view.voucher'), 1)]) }}
                    <a href="{{ route('home') }}" class="btn btn-sm btn-secondary float-right">{{ __('view.back') }}</a>
                </div>

                <div class="card-body">
                    @if (session('status'))
                        <div class="alert alert-success" role="alert">
                            {{ session('status') }}
                        </div>
                    @endif
                    <table id="draft-vouchers" class="display">
                        <thead>
                            <tr>
                                <th>{{ ucfirst(trans_choice(__('view.company'), 0)) }}</th>
                                <th>{{ __('view.environment') }}</th>
                                <th>{{ __('view.voucher_type') }}</th>
                                <th>{{ __('view.created_at') }}</th>
                                <th>{{ __('view.updated_at') }}</th>
                                <th></th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($draftVouchers as $draftVoucher)
                                <tr>
                                    <td>{{ ($draftVoucher['company'] ? $draftVoucher['company']->tradename : '') }}</td>
                                    <td>{{ ($draftVoucher['environment'] ? $draftVoucher['environment']->name : '') }}</td>
                                    <td>{{ ($draftVoucher['voucher_type'] ? $draftVoucher['voucher_type']->name : '') }}</td>
                                    <td>{{ \DateTime::createFromFormat('Y-m-d H:i:s.u', $draftVoucher['created_at']['date'])->format('Y-m-d H:i:s') }}</td>
                                    <td>{{ \DateTime::createFromFormat('Y-m-d H:i:s.u', $draftVoucher['updated_at']['date'])->format('Y-m-d H:i:s') }}</td>
                                    <td>
                                        <a href="{{ route('vouchers.edit_draft', $draftVoucher['id']) }}" class="btn btn-sm btn-info">{{ __('view.edit') }}</a>
                                        <button type="button" class="btn btn-sm btn-danger" data-toggle="modal" data-target="#confirmation"
                                            data-title="{{ trans_choice(__('view.are_you_sure_you_want_to_delete_the_model', ['model' => trans_choice(__('view.voucher'), 0), 'name' => $draftVoucher['id']]), 0) }}"
                                            data-body="{{ trans_choice(__('view.warning_all_model_data_will_be_deleted_this_action_can_not_be_undone', ['model' => trans_choice(__('view.voucher'), 0)]), 0) }}"
                                            data-form="{{ route('vouchers.destroy_draft', $draftVoucher['id']) }}"
                                            data-method="DELETE"
                                            data-class="btn btn-sm btn-danger"
                                            data-action="{{ __('view.delete') }}">{{ __('view.delete') }}</button>
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
