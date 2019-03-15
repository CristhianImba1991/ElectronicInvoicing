@extends('layouts.app')

@section('scripts')
<script type="text/javascript">
    $.noConflict();
    jQuery(document).ready(function($) {
        $("#submit").click(function() {
            $.ajax({
                url: "{{ url('/manage/products/update') }}/{{ $product->id }}",
                method: "POST",
                data: $('#update_form').serialize(),
                success: function(result) {
                    var validator = JSON.parse(result);
                    if (validator['status']) {
                        window.location.href = "{{ route('products.index') }}";
                    } else {
                        $('#validation').on('show.bs.modal', function(event) {
                            var errors = '';
                            $.each(validator['messages'], function(field, message) {
                                errors += "<li>" + message + "</li>";
                            });
                            $(this).find('#modal-body').html("<ul>" + errors + "</ul>");
                        });
                        $('#validation').modal('show');
                    }
                }
            });
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
                    {{ __('view.edit_model', ['model' => trans_choice(__('view.product'), 0)]) }}
                    <a href="{{ route('products.index') }}" class="btn btn-sm btn-secondary float-right">{{ __('view.cancel') }}</a>
                </div>

                <form id="update_form">
                    {{ csrf_field() }}
                    <input type="hidden" name="_method" value="PUT">

                    <div class="card-body">

                        <div class="form-group">
                            <label for="company">{{ ucfirst(trans_choice(__('view.company'), 0)) }}</label>
                            <input class="form-control" type="text" id="company" name="company" value="{{ $product->branch->company->tradename }} - {{ $product->branch->company->social_reason }}" readonly>
                        </div>
                        <div class="form-group">
                            <label for="branch">{{ ucfirst(trans_choice(__('view.branch'), 0)) }}</label>
                            <input class="form-control" type="text" id="branch" name="branch" value="{{ $product->branch->name }}" readonly>
                        </div>
                        <div class="form-group">
                            <label for="main_code">{{ __('view.main_code') }}</label>
                            <input type="text" class="form-control" id="main_code" name="main_code" value="{{ $product->main_code }}">
                        </div>
                        <div class="form-group">
                            <label for="auxiliary_code">{{ __('view.auxiliary_code') }}</label>
                            <input type="text" class="form-control" id="auxiliary_code" name="auxiliary_code"  value="{{ $product->auxiliary_code }}">
                        </div>
                        <div class="form-group">
                            <label for="unit_price">{{ __('view.unit_price') }}</label>
                            <input type="number" step="0.01" class="form-control" id="unit_price" name="unit_price"  value="{{ $product->unit_price }}">
                        </div>
                        <div class="form-group">
                            <label for="description">{{ __('view.description') }}</label>
                            <input type="text" class="form-control" id="description" name="description"  value="{{ $product->description }}">
                        </div>
                        <div class="form-group">
                            <label for="stock">{{ __('view.stock') }}</label>
                            <input type="number" class="form-control" id="stock" name="stock"  value="{{ $product->stock }}">
                        </div>
                    </div>

                    <div class="card-footer">
                        <button id="submit" type="button" class="btn btn-sm btn-success">{{ __('view.update') }}</button>
                    </div>

                </form>

            </div>
        </div>
    </div>
</div>

@include('layouts.validation')
@endsection
