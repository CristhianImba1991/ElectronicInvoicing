<script src="{{ asset('js/bootstrap-select.min.js') }}"></script>
<script src="{{ asset('js/bootstrap-datepicker.min.js') }}"></script>
<script src="{{ asset('js/locales/bootstrap-datepicker.' . str_replace('_', '-', app()->getLocale()) . '.min.js') }}" charset="UTF-8"></script>
@include('vouchers.js.5_2', ['action' => $action])
<div class="col-sm-12">
    <div class="card mb-3">
        <div class="card-body">
            <h5 class="card-title">6. {{ __('view.ats_retention') }}</h5>
            <div class="form-group">
                <label for="supplier_identification_type">{{ __('view.supplier_identification_type') }}</label>
                <select class="form-control selectpicker" id="supplier_identification_type" name="supplier_identification_type" data-live-search="true" title="{{ trans_choice(__('view.select_a_model', ['model' => __('view.supplier_identification_type')]), 0) }}">
                    @foreach($supplierIdentificationTypes as $supplierIdentificationType)
                        <option value="{{ $supplierIdentificationType->id }}">{{ $supplierIdentificationType->name }}</option>
                    @endforeach
                </select>
            </div>
            <div class="form-group">
                <label for="related_party">{{ __('view.related_party') }}</label>
                <select class="form-control selectpicker" id="related_party" name="related_party" data-live-search="true" title="{{ trans_choice(__('view.select_a_model', ['model' => __('view.related_party')]), 0) }}">
                    <option value="1">{{ __('view.yes') }}</option>
                    <option value="0">{{ __('view.no') }}</option>
                </select>
            </div>
        </div>
    </div>
</div>
<div class="col-sm-12">
    <div class="card mb-3">
        <div class="card-body">
            <h5 class="card-title">7. {{ __('view.support_document') }}</h5>
            <div class="form-group">
                <label for="support_voucher">{{ __('view.support_voucher') }}</label>
                <select class="form-control selectpicker" id="support_voucher" name="support_voucher" data-live-search="true" title="{{ trans_choice(__('view.select_a_model', ['model' => __('view.support_voucher')]), 0) }}">
                    @foreach($supportVouchers as $supportVoucher)
                        <option value="{{ $supportVoucher->id }}">{{ $supportVoucher->name }}</option>
                    @endforeach
                </select>
            </div>
            <div class="form-group">
                <label for="support_document_type">{{ __('view.support_document_type') }}</label>
                <select class="form-control selectpicker" id="support_document_type" name="support_document_type" data-live-search="true" title="{{ trans_choice(__('view.select_a_model', ['model' => __('view.support_document_type')]), 0) }}">
                    @foreach($supportDocumentTypes as $supportDocumentType)
                        <option value="{{ $supportDocumentType->id }}">{{ $supportDocumentType->name }}</option>
                    @endforeach
                </select>
            </div>
            <div class="form-group">
                <label for="authorization_number">{{ __('view.authorization_number') }}</label>
                <input class="form-control" type="text" id="authorization_number" name="authorization_number" size="49" value="" >
            </div>
            <div class="form-group">
                <label for="accounting_record_date">{{ __('view.accounting_record_date') }}</label>
                <input class="form-control" id="accounting_record_date" name="accounting_record_date" readonly>
            </div>
            <div class="form-group">
                <label for="payment_type">{{ __('view.payment_type') }}</label>
                <select class="form-control selectpicker" id="payment_type" name="payment_type" data-live-search="true" title="{{ trans_choice(__('view.select_a_model', ['model' => __('view.payment_type')]), 0) }}">
                    @foreach($paymentTypes as $paymentType)
                        <option value="{{ $paymentType->id }}">{{ $paymentType->name }}</option>
                    @endforeach
                </select>
            </div>
            <div class="form-group">
                <label for="foreign_fiscal_regime_type">{{ __('view.foreign_fiscal_regime_type') }}</label>
                <select class="form-control selectpicker" id="foreign_fiscal_regime_type" name="foreign_fiscal_regime_type" data-live-search="true" data-dependent="country" title="{{ trans_choice(__('view.select_a_model', ['model' => __('view.foreign_fiscal_regime_type')]), 0) }}">
                    @foreach($foreignFiscalRegimeTypes as $foreignFiscalRegimeType)
                        <option value="{{ $foreignFiscalRegimeType->id }}">{{ $foreignFiscalRegimeType->name }}</option>
                    @endforeach
                </select>
            </div>
            <div class="form-group">
                <label for="country">{{ __('view.country') }}</label>
                <select class="form-control selectpicker" id="country" name="country" data-live-search="true" title="{{ trans_choice(__('view.select_a_model', ['model' => __('view.country')]), 0) }}">

                </select>
            </div>
            <div class="form-group">
                <label for="double_taxation_agreement">{{ __('view.double_taxation_agreement') }}</label>
                <select class="form-control selectpicker" id="double_taxation_agreement" name="double_taxation_agreement" data-live-search="true" title="{{ trans_choice(__('view.select_a_model', ['model' => __('view.double_taxation_agreement')]), 0) }}">
                    <option value="1">{{ __('view.yes') }}</option>
                    <option value="0">{{ __('view.no') }}</option>
                </select>
            </div>
            <div class="form-group">
                <label for="payment_aboard_subject_retention">{{ __('view.payment_aboard_subject_retention') }}</label>
                <select class="form-control selectpicker" id="payment_aboard_subject_retention" name="payment_aboard_subject_retention" data-live-search="true" title="{{ trans_choice(__('view.select_a_model', ['model' => __('view.payment_aboard_subject_retention')]), 0) }}">
                    <option value="1">{{ __('view.yes') }}</option>
                    <option value="0">{{ __('view.no') }}</option>
                </select>
            </div>
            <div class="form-group">
                <label for="payment_tax_regime">{{ __('view.payment_tax_regime') }}</label>
                <select class="form-control selectpicker" id="payment_tax_regime" name="payment_tax_regime" data-live-search="true" title="{{ trans_choice(__('view.select_a_model', ['model' => __('view.payment_tax_regime')]), 0) }}">
                    <option value="1">{{ __('view.yes') }}</option>
                    <option value="0">{{ __('view.no') }}</option>
                </select>
            </div>
        </div>
    </div>
</div>
<div class="col-sm-12">
    <div class="card mb-3">
        <div class="card-body">
            <h5 class="card-title">8. {{ __('view.tax') }}</h5>
            <table id="tax-table" class="display">
                <thead>
                    <tr>
                        <th>{{ __('view.tax') }}</th>
                        <th>{{ __('view.description') }}</th>
                        <th>{{ __('view.rate') }}</th>
                        <th>{{ __('view.tax_base') }}</th>
                        <th>{{ __('view.value') }}</th>
                        <th></th>
                    </tr>
                </thead>
            </table>
        </div>
    </div>
</div>
