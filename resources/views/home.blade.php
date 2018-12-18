@extends('layouts.app')

@section('scripts')
<script src="{{ asset('js/jquery.dataTables.min.js') }}"></script>
<script id="modal" src="{{ asset('js/app/modal.js') }}"></script>
<script type="text/javascript">
$(document).ready(function(){
    $('#vouchers').DataTable({
        "info": false,
        "order": [[ 2, 'asc' ]],
        "paging": false,
        "searching": false
    });
    @can('create_vouchers')
        $('#draft-vouchers').DataTable({
            "info": false,
            "order": [[ 0, 'desc' ]],
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
                                                <th>Voucher</th>
                                                <th>Customer</th>
                                                <th>Info</th>
                                            </tr>
                                        </thead>
                                    </table>
                                    @can('create_vouchers')
                                        <br>
                                        <h6 class="card-title">
                                            Draft vouchers
                                            <button type="button" class="btn btn-sm btn-secondary float-right">All draft vouchers</button>
                                        </h6>
                                        <table id="draft-vouchers" class="display">
                                            <thead>
                                                <tr>
                                                    <th>ID</th>
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
                                                        <td><a href="{{ route('vouchers.edit_draft', $draftVoucher['id']) }}">{{ $draftVoucher['id'] }}</a></td>
                                                        <td>{{ ($draftVoucher['company'] ? $draftVoucher['company']->tradename . ' - ' . $draftVoucher['company']->social_reason : '') }}</td>
                                                        <td>{{ ($draftVoucher['environment'] ? $draftVoucher['environment']->name : '') }}</td>
                                                        <td>{{ ($draftVoucher['voucher_type'] ? $draftVoucher['voucher_type']->name : '') }}</td>
                                                        <td>{{ \DateTime::createFromFormat('Y-m-d H:i:s.u', $draftVoucher['created_at']['date'])->format('Y-m-d H:i:s') }}</td>
                                                        <td>{{ \DateTime::createFromFormat('Y-m-d H:i:s.u', $draftVoucher['updated_at']['date'])->format('Y-m-d H:i:s') }}</td>
                                                        <td>
                                                            <button type="button" class="btn btn-sm btn-danger" data-toggle="modal" data-target="#confirmation"
                                                                data-title="Are you sure you want to delete the draft voucher {{ $draftVoucher['id'] }}?"
                                                                data-body="WARNING: All voucher data will be deleted. This action can not be undone. Remember that the identifiers of the other draft vouchers will be updated."
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
