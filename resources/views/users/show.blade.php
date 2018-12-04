@extends('layouts.app')

@section('scripts')
@endsection

@section('content')
<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="card">
                <div class="card-header">
                    View user
                    <a href="{{ route('users.index') }}" class="btn btn-sm btn-secondary float-right">Back</a>
                </div>

                <div class="card-body">
                    <div class="form-group">
                        <label for="role">Role</label>
                        <input class="form-control" type="text" id="role" name="role" value="{{ strtoupper(implode(', ', json_decode(json_encode($user->getRoleNames()), true))) }}" readonly>
                    </div>
                    <div class="form-group">
                        <label for="name">Name</label>
                        <input class="form-control" type="text" id="name" name="name" value="{{ $user->name }}" readonly>
                    </div>
                    <div class="form-group">
                        <label for="email">Email</label>
                        <input class="form-control" type="text" id="email" name="email" value="{{ $user->email }}" readonly>
                    </div>
                    <ul class="list-group">
                        <label>Allowed to</label>
                        @if($user->hasRole('admin'))
                            <li class="list-group-item">All</li>
                        @else
                            @forelse(\ElectronicInvoicing\Http\Controllers\CompanyUser::getCompaniesAllowedToUser($user) as $company)
                                <li class="list-group-item">{{ $company->tradename }} - {{ $company->social_reason }}
                                    <ul class="list-group">
                                        @foreach(\ElectronicInvoicing\Http\Controllers\CompanyUser::getBranchesAllowedToUser($user) as $branch)
                                            @if($company->id === $branch->company_id)
                                                <li class="list-group-item">{{ $branch->name }}
                                                    <ul class="list-group">
                                                        @foreach($user->emissionPoints()->get() as $emissionPoint)
                                                            @if($branch->id === $emissionPoint->branch_id)
                                                                <li class="list-group-item">{{ $emissionPoint->code }}</li>
                                                            @endif
                                                        @endforeach
                                                    </ul>
                                                </li>
                                            @endif
                                        @endforeach
                                    </ul>
                                </li>
                            @empty
                                <li class="list-group-item">None</li>
                            @endforelse
                        @endif
                    </ul>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
