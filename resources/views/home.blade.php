@extends('layouts.app')

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

                    <!--<a href="{{ route('grantallprivileges') }}">SA</a>-->

                    @if(auth()->user()->can('read_companies') || auth()->user()->can('read_branches') || auth()->user()->can('read_emission_points') || auth()->user()->can('read_customers') || auth()->user()->can('read_users') || auth()->user()->can('read_products'))
                        <h4>Site administration</h4>

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
                            @can('read_customers')
                                <a href="#" class="list-group-item list-group-item-action" style="border: none">Customers</a>
                            @endcan
                            @can('read_users')
                                <a href="#" class="list-group-item list-group-item-action" style="border: none">Users</a>
                            @endcan
                            @can('read_products')
                                <a href="#" class="list-group-item list-group-item-action" style="border: none">Products</a>
                            @endcan
                        </div>
                    @endif

                </div>
            </div>
        </div>
    </div>
</div>
@endsection
