<script src="{{ asset('js/bootstrap-select.min.js') }}"></script>
@include('vouchers.js.5', ['action' => $action])
<div class="col-sm-12">
    <div class="card mb-3">
        <div class="card-body">
            <h5 class="card-title">5. {{ trans_choice(__('view.retention_type'), 0) }}</h5>
            <div class="form-group">
                <select class="form-control selectpicker" id="retention_type" name="retention_type" data-live-search="true" data-dependent="branch" title="{{ trans_choice(__('view.select_a_model', ['model' => strtolower(trans_choice(__('view.retention_type'), 0))]), 0) }}">
                    @foreach($retentionTypes as $retentionType)
                        <option value="{{ $retentionType->id }}">{{ $retentionType->name }}</option>
                    @endforeach
                </select>
            </div>
        </div>
    </div>
</div>
<div class="col">
    <div id="retention-information" class="row">

    </div>
</div>
