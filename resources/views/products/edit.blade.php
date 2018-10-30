@extends('layouts.app')

@section('scripts')
<script src="{{ asset('js/bootstrap-select.min.js') }}"></script>
<script type="text/javascript">
$(document).ready(function(){
    $('#company').change(function() {
        if($(this).val() != '') {
            var _token = $('input[name = "_token"]').val();
            var id = $(this).val();
            $.ajax({
                url: "{{ route('companies.branches') }}",
                method: "POST",
                data: {
                    _token: _token,
                    id: id,
                },
                success: function(result) {
                    var branches = JSON.parse(result);
                    var options = '';
                    for (var i = 0; i < branches.length; i++) {
                        options += '<option value="' + branches[i]['id'] + '">' + branches[i]['name'] + '</option>';
                    }
                    $("#branch").html(options).selectpicker('refresh');
                }
            })
        }
    });
});
</script>

@endsection
@section('styles')
<link rel="stylesheet" href="{{ asset('css/bootstrap-select.min.css') }}">
@endsection
@section('content')
<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="card">
                <div class="card-header">
                    Edit product
                    <a href="{{ route('products.index') }}" class="btn btn-sm btn-secondary float-right">Cancel</a>
                </div>

                <form action="{{ route('products.store') }}" method="post" enctype="multipart/form-data">
                    {{ csrf_field() }}

                    <div class="card-body">
                        @if ($errors->count() > 0)
                            <div class="alert alert-danger" role="alert">
                                <h5>The following errors were found:</h5>
                                <ul>
                                    @foreach($errors->all() as $error)
                                        <li>{{ $error }}</li>
                                    @endforeach
                                </ul>
                            </div>
                        @endif
                        <div class="form-group">
                            <label for="company">Company</label>
                            <select class="form-control selectpicker input-lg dynamic" id="company" name="company" data-live-search="true" data-dependent="branch" title="Select a company ...">
                                @foreach($companies as $company)
                                    <option value="{{ $company->id }}" {{ $company->id === old('company') ? "selected" : "" }}>{{ $company->tradename }} - {{ $company->social_reason }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="form-group" id="divbranch">
                            <label for="establishment">Branch</label>
                            <select class="form-control selectpicker input-lg" id="branch" name="branch" data-live-search="true" title="Select a branch ...">

                            </select>
                        </div>
                        <div class="form-group">
                            <label for="main_code">Main Code</label>
                            <input type="text" class="form-control" id="main_code" name="main_code" value="{{ old('main_code') }}">
                        </div>
                        <div class="form-group">
                            <label for="social_reason">Auxiliary Code</label>
                            <input type="text" class="form-control" id="auxiliary_code" name="auxiliary_code"  value="{{ old('auxiliary_code') }}">
                        </div>
                        <div class="form-group">
                            <label for="tradename">Unit Price</label>
                            <input type="number" step="0.01" class="form-control" id="unit_price" name="unit_price"  value="{{ old('unit_price') }}">
                        </div>
                        <div class="form-group">
                            <label for="address">Description</label>
                            <input type="text" class="form-control" id="description" name="description"  value="{{ old('description') }}">
                        </div>
                        <div class="form-group">
                            <label for="special_contributor">Stock</label>
                            <input type="number" class="form-control" id="stock" name="stock"  value="{{ old('stock') }}">
                        </div>
                    </div>

                    <div class="card-footer"><button type="submit" class="btn btn-sm btn-success">Add</button></div>

                </form>

            </div>
        </div>
    </div>
</div>
@endsection
