@extends('layouts.app')

@section('content')
<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-12">
            <div class="card">
                <div class="card-header">
                    <a href="{{ route('quotas.index') }}" class="btn btn-sm btn-secondary float-right">{{ __('view.back') }}</a>
                </div>

                <div class="card-body">
                    <div class="form-group">
                        <label for="description">Descripción</label>
                        <input type="text" class="form-control" id="description" name="description" value="{{ $quotas->description }}" readonly>
                    </div>
                    <div class="form-group">
                        <label for="max_users_owner">Número máximo de Propietarios</label>
                        <input type="text" class="form-control" id="max_users_owner" name="max_users_owner"  value="{{ $quotas->max_users_owner }}" readonly>
                    </div>
                    <div class="form-group">
                        <label for="max_users_supervisor">Número máximo de Supervisores</label>
                        <input type="text" class="form-control" id="max_users_supervisor" name="max_users_supervisor"  value="{{ $quotas->max_users_supervisor }}" readonly>
                    </div>
                    <div class="form-group">
                        <label for="max_users_employee">Número máximo de Empleados</label>
                        <input type="text" class="form-control" id="max_users_employee" name="max_users_employee"  value="{{ $quotas->max_users_employee }}" readonly>
                    </div>
                    <div class="form-group">
                        <label for="max_branches">Número máximo de marcas</label>
                        <input type="text" class="form-control" id="max_branches" name="max_branches"  value="{{ $quotas->max_branches }}" readonly>
                    </div>
                    <div class="form-group">
                        <label for="max_emission_points">Número máximo de puntos de emisión</label>
                        <input type="text" class="form-control" id="max_emission_points" name="max_emission_points" value="{{ $quotas->max_emission_points }}" readonly>
                    </div>

                </div>
            </div>
        </div>
    </div>
</div>
@endsection
