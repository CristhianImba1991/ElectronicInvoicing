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
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
