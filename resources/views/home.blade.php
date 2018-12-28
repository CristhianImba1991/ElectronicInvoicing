@extends('layouts.app')

@section('scripts')
<script src="{{ asset('js/jquery.dataTables.min.js') }}"></script>
<script id="modal" src="{{ asset('js/app/modal.js') }}"></script>
<script type="text/javascript">
$(document).ready(function(){
    $('#vouchers').DataTable({
        "info": false,
        "order": [[ 5, 'desc' ], [ 0, 'desc' ]],
        "paging": false,
        "searching": false
    });
    @can('create_vouchers')
        $('#draft-vouchers').DataTable({
            "info": false,
            "order": [[ 3, 'desc' ]],
            "paging": false,
            "searching": false
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
                <div class="card-header">Dashboard</div>

                <div class="card-body">
                    @if (session('status'))
                        <div class="alert alert-success" role="alert">
                            {{ session('status') }}
                        </div>
                    @endif

                    <div class="row">
                        <div class="col-sm-12">
                            <div class="card mb-3">
                                <div class="card-body">
                                    <h5 class="card-title">Last vouchers</h5>
                                    <table id="vouchers" class="display">
                                        <thead>
                                            <tr>
                                                <th>ID</th>
                                                <th>Type</th>
                                                <th>State</th>
                                                <th>Voucher</th>
                                                <th>Customer</th>
                                                <th>Issue date</th>
                                                <th></th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @foreach($vouchers as $voucher)
                                                <tr>
                                                    <td>{{ $voucher->id }}</td>
                                                    <td>{{ \ElectronicInvoicing\VoucherType::find($voucher->voucher_type_id)->name }}</td>
                                                    <td>
                                                        @switch($voucher->voucher_state_id)
                                                            @case(\ElectronicInvoicing\StaticClasses\VoucherStates::ACCEPTED)
                                                                <span class="badge badge-info">
                                                                @break
                                                            @case(\ElectronicInvoicing\StaticClasses\VoucherStates::REJECTED)
                                                                <span class="badge badge-dark">
                                                                @break
                                                            @case(\ElectronicInvoicing\StaticClasses\VoucherStates::SENDED)
                                                                <span class="badge badge-light">
                                                                @break
                                                            @case(\ElectronicInvoicing\StaticClasses\VoucherStates::RECEIVED)
                                                                <span class="badge badge-secondary">
                                                                @break
                                                            @case(\ElectronicInvoicing\StaticClasses\VoucherStates::RETURNED)
                                                                <span class="badge badge-warning">
                                                                @break
                                                            @case(\ElectronicInvoicing\StaticClasses\VoucherStates::AUTHORIZED)
                                                                <span class="badge badge-success">
                                                                @break
                                                            @case(\ElectronicInvoicing\StaticClasses\VoucherStates::IN_PROCESS)
                                                                <span class="badge badge-primary">
                                                                @break
                                                            @case(\ElectronicInvoicing\StaticClasses\VoucherStates::UNAUTHORIZED)
                                                                <span class="badge badge-danger">
                                                                @break
                                                            @default
                                                                <span class="badge">
                                                        @endswitch
                                                        {{ \ElectronicInvoicing\VoucherState::find($voucher->voucher_state_id)->name }}</span>
                                                    </td>
                                                    <td>{{ str_pad(strval($voucher->emissionPoint->branch->establishment), 3, '0', STR_PAD_LEFT) }}-{{ str_pad(strval($voucher->emissionPoint->code), 3, '0', STR_PAD_LEFT) }}-{{ str_pad(strval($voucher->sequential), 9, '0', STR_PAD_LEFT) }}</td>
                                                    <td>{{ $voucher->customer->social_reason }}</td>
                                                    <td>{{ $voucher->issue_date }}</td>
                                                    <th></th>
                                                </tr>
                                            @endforeach
                                        </tbody>
                                    </table>
                                    @can('create_vouchers')
                                        <br>
                                        <h6 class="card-title">
                                            Draft vouchers
                                            <a href="{{ route('vouchers.index_draft') }}" class="btn btn-sm btn-secondary float-right">All draft vouchers</a>
                                        </h6>
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
                                    @endcan
                                </div>
                            </div>
                        </div>
                        <div class="col-sm-6">
                            <div class="card mb-3">
                                <div class="card-body">
                                    <h5 class="card-title">Vouchers</h5>
                                    <div class="list-group">
                                        @can('create_vouchers')
                                            <a href="{{ route('vouchers.create') }}" class="list-group-item list-group-item-action" style="border: none">New voucher</a>
                                        @endcan
                                        @can('report_vouchers')
                                            <a href="{{ route('vouchers.index') }}" class="list-group-item list-group-item-action" style="border: none">Reports</a>
                                        @endcan
                                    </div>
                                </div>
                            </div>
                        </div>
                        @if(auth()->user()->can('read_companies') || auth()->user()->can('read_branches') || auth()->user()->can('read_emission_points') || auth()->user()->can('read_customers') || auth()->user()->can('read_users') || auth()->user()->can('read_products'))
                            <div class="col-sm-6">
                                <div class="card mb-3">
                                    <div class="card-body">
                                        <h5 class="card-title">Site administration</h5>
                                        <div class="list-group">
                                            @can('read_companies')
                                                <a href="{{ route('companies.index') }}" class="list-group-item list-group-item-action" style="border: none">Companies</a>
                                            @endcan
                                            @can('read_branches')
                                                <a href="{{ route('branches.index') }}" class="list-group-item list-group-item-action" style="border: none">Branches</a>
                                            @endcan
                                            @can('read_emission_points')
                                                <a href="{{ route('emission_points.index') }}" class="list-group-item list-group-item-action" style="border: none">Emission points</a>
                                            @endcan
                                            @can('read_users')
                                                <a href="{{ route('users.index') }}" class="list-group-item list-group-item-action" style="border: none">Users</a>
                                            @endcan
                                            @can('read_customers')
                                                <a href="{{ route('customers.index') }}" class="list-group-item list-group-item-action" style="border: none">Customers</a>
                                            @endcan
                                            @can('read_products')
                                                <a href="{{ route('products.index') }}" class="list-group-item list-group-item-action" style="border: none">Products</a>
                                            @endcan
                                        </div>
                                    </div>
                                </div>
                            </div>
                        @endif
                    </div>

                </div>
            </div>
        </div>
    </div>
</div>

@include('layouts.confirmation')
@endsection
