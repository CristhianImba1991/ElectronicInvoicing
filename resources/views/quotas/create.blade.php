@extends('layouts.app')
@section('scripts')
<script type="text/javascript">
    $.noConflict();
    jQuery(document).ready(function($) {
      $("#submit").click(function() {
          $.ajax({
              url: "{{ route('quotas.store') }}",
              method: "POST",
              data: $('#create_form').serialize(),
              success: function(result) {
                  var validator = JSON.parse(result);
                  if (validator['status']) {
                      window.location.href = "{{ route('quotas.index') }}";
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
@section('content')

<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-12">
            <div class="card">
                <div class="card-header">
                  {{ trans_choice(__('view.new_model', ['model' => trans_choice(__('view.quota'), 0)]), 1) }}

                  <a href="{{ route('quotas.index') }}" class="btn btn-sm btn-secondary float-right">{{ __('view.cancel') }}</a>

                </div>
                  <form id="create_form">
                    {{csrf_field() }}
                      <div class="card-body">

                      <div class="form-group">
                        <th>{{__('view.description')}}</th>
                          <input type="text" class="form-control" id="description" name="description" value="">
                      </div>
                      <div class="form-group">
                        <th>{{__('view.max_users_owner')}}</th>
                          <input type="text" class="form-control" id="max_users_owner" name="max_users_owner"  value="">
                      </div>
                      <div class="form-group">
                        <th>{{__('view.max_users_supervisor')}}</th>
                          <input type="text" class="form-control" id="max_users_supervisor" name="max_users_supervisor"  value="">
                      </div>
                      <div class="form-group">
                        <th>{{__('view.max_users_employee')}}</th>
                          <input type="text" class="form-control" id="max_users_employee" name="max_users_employee"  value="">
                      </div>
                      <div class="form-group">
                        <th>{{__('view.max_branches')}}</th>
                          <input type="text" class="form-control" id="max_branches" name="max_branches"  value="">
                      </div>
                      <div class="form-group">
                        <th>{{__('view.max_emission_points')}}</th>
                          <input type="text" class="form-control" id="max_emission_points" name="max_emission_points" value="">
                      </div>
                          <div class="card-footer">
                              <button id="submit" type="button" class="btn btn-sm btn-success">{{ __('view.add') }}</button>
                          </div>

                  </div>
            </form>
        </div>
    </div>
</div>

@include('layouts.validation')
@endsection
