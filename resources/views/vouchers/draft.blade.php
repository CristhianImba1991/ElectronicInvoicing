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
        <div class="col-md-8">
            <div class="card">
                <div class="card-header">
                    Draft vouchers
                    <a href="{{ route('home') }}" class="btn btn-sm btn-secondary float-right">Back</a>
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
                                <th>Company</th>
                                <th>Environment</th>
                                <th>Voucher type</th>
                                <th>Created at</th>
                                <th>Updated at</th>
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
                                        <a href="{{ route('vouchers.edit_draft', $draftVoucher['id']) }}" class="btn btn-sm btn-info">Edit</a>
                                        <button type="button" class="btn btn-sm btn-danger" data-toggle="modal" data-target="#confirmation"
                                            data-title="Are you sure you want to delete the draft voucher {{ $draftVoucher['id'] }}?"
                                            data-body="WARNING: All voucher data will be deleted. This action can not be undone."
                                            data-form="{{ route('vouchers.destroy_draft', $draftVoucher['id']) }}"
                                            data-method="DELETE"
                                            data-class="btn btn-sm btn-danger"
                                            data-action="Delete">Delete</button>
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
