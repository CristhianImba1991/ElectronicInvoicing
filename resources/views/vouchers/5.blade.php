<script src="{{ asset('js/bootstrap-select.min.js') }}"></script>
<script src="{{ asset('js/bootstrap-datepicker.min.js') }}"></script>
<script src="{{ asset('js/locales/bootstrap-datepicker.' . str_replace("_", "-", app()->getLocale()) . '.min.js') }}" charset="UTF-8"></script>
@include('vouchers.js.5', ['action' => $action])
<div class="col-sm-12">
    <div class="card mb-3">
        <div class="card-body">
            <h5 class="card-title">5. {{ __('view.retention') }}</h5>
            <table id="retention-table" class="display">
                <thead>
                    <tr>
                        <th>{{ __('view.tax') }}</th>
                        <th>{{ __('view.description') }}</th>
                        <th>{{ __('view.rate') }}</th>
                        <th>{{ __('view.tax_base') }}</th>
                        <th>{{ __('view.retained_value') }}</th>
                        <th></th>
                    </tr>
                </thead>
            </table>
            <div class="form-group">
                <label for="retention_total">{{ __('view.total_retained') }}</label>
                <input class="form-control" id="retention_total" name="retention_total" readonly>
            </div>
        </div>
    </div>
</div>
<div class="col-sm-6">
    <div class="card mb-3">
        <div class="card-body">
            <h5 class="card-title">6. {{ __('view.additional_information') }}</h5>
            <table id="additionaldetail-table" class="display">
                <thead>
                    <tr>
                        <th>{{ __('view.name') }}</th>
                        <th>{{ __('view.value') }}</th>
                        <th></th>
                    </tr>
                </thead>
            </table>
            <div class="form-group">
                <label for="fiscal_period">{{ __('view.fiscal_period') }}</label>
                <input class="form-control" id="fiscal_period" name="fiscal_period" readonly>
            </div>
            <table class="table table-striped">
                <thead>
                    <tr>
                        <th>{{ __('view.extra_detail') }}</th>
                    </tr>
                </thead>
                <tbody>
                    <tr>
                        <td><textarea class="form-control" type="text" id="extra_detail" name="extra_detail" value=""></textarea></td>
                    </tr>
                </tbody>
            </table>
        </div>
    </div>
</div>
<div class="col-sm-6">
    <div class="card mb-3">
        <div class="card-body">
            <h5 class="card-title">7. {{ __('view.support_document') }}</h5>
            <div class="form-group">
                <label for="voucher_type_support_document">{{ __('view.voucher_type_of_the_support_document') }}</label>
                <select class="form-control selectpicker" id="voucher_type_support_document" name="voucher_type_support_document" data-live-search="true" data-dependent="branch" title="{{ trans_choice(__('view.select_a_model', ['model' => lcfirst(__('view.voucher_type'))]), 0) }}">
                    @foreach($voucherTypes as $voucherType)
                        <option value="{{ $voucherType->id }}">{{ $voucherType->name }}</option>
                    @endforeach
                </select>
            </div>
            <table class="table table-striped">
                <thead>
                    <tr>
                        <th>{{ __('view.support_document') }}</th>
                    </tr>
                </thead>
                <tbody>
                    <tr>
                        <td>
                            <div class="form-row">
                                <div class="form-group col-md-3">
                                    <input class="form-control" type="text" id="supportdocument_establishment" name="supportdocument_establishment" size="3" value="" >
                                </div>
                                <div class="form-group col-md-3">
                                    <input class="form-control" type="text" id="supportdocument_emissionpoint" name="supportdocument_emissionpoint" size="3" value="" >
                                </div>
                                <div class="form-group col-md-5">
                                    <input class="form-control" type="text" id="supportdocument_sequential" name="supportdocument_sequential" size="9" value="" >
                                </div>
                            </div>
                        </td>
                    </tr>
                </tbody>
            </table>
            <div class="form-group">
                <label for="issue_date_support_document">{{ __('view.issue_date_of_the_support_document') }}</label>
                <input class="form-control" id="issue_date_support_document" name="issue_date_support_document" readonly>
            </div>
        </div>
    </div>
</div>
