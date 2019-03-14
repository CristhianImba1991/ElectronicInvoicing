<script src="{{ asset('js/bootstrap-select.min.js') }}"></script>
<script src="{{ asset('js/bootstrap-datepicker.min.js') }}"></script>
@include('vouchers.js.3', ['action' => $action])
<div class="col-sm-12">
    <div class="card mb-3">
        <div class="card-body">
            <h5 class="card-title">5. {{ __('view.debit_note') }}</h5>
            <table id="debitNote-table" class="display">
                <thead>
                    <tr>
                        <th>{{ __('view.reason') }}</th>
                        <th>{{ __('view.value') }}</th>
                        <th></th>
                    </tr>
                </thead>
            </table>
        </div>
    </div>
</div>
<div class="col-sm-12">
    <div class="card mb-3">
        <div class="card-body">
            <h5 class="card-title">6. {{ __('view.payment_method') }}</h5>
            <table id="paymentmethod-table" class="display">
                <thead>
                    <tr>
                        <th>{{ __('view.method') }}</th>
                        <th>{{ __('view.value') }}</th>
                        <th>{{ __('view.time_unit') }}</th>
                        <th>{{ __('view.term') }}</th>
                        <th></th>
                    </tr>
                </thead>
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
                <input class="form-control" id="voucher_type_support_document" name="voucher_type_support_document" value="{{ strtoupper(__('view.invoice')) }}" readonly>
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
    <div class="card mb-3">
        <div class="card-body">
            <h5 class="card-title">8. {{ __('view.additional_information') }}</h5>
            <table id="additionaldetail-table" class="display">
                <thead>
                    <tr>
                        <th>{{ __('view.name') }}</th>
                        <th>{{ __('view.value') }}</th>
                        <th></th>
                    </tr>
                </thead>
            </table>
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
            <h5 class="card-title">9. {{ __('view.total') }}</h5>
            <div class="form-group">
                <label for="iva_tax">{{ __('view.iva_tax') }}</label>
                <select class="form-control selectpicker" id="iva_tax" name="iva_tax" data-live-search="true"  title="{{ trans_choice(__('view.select_a_model', ['model' => lcfirst(__('view.iva_tax'))]), 0) }}">
                    @foreach($iva_taxes as $iva_tax)
                        <option value="{{ $iva_tax->id }}">{{ $iva_tax->auxiliary_code }} - {{ $iva_tax->description }}</option>
                    @endforeach
                </select>
            </div>
            <table class="table table-striped">
                <tbody>
                    <tr>
                        <td>{{ __('view.iva_12_subtotal') }}</td>
                        <td><input class="form-control" type="text" id="ivasubtotal" name="ivasubtotal" value="" readonly></td>
                    </tr>
                    <tr>
                        <td>{{ __('view.iva_0_subtotal') }}</td>
                        <td><input class="form-control" type="text" id="iva0subtotal" name="iva0subtotal" value="" readonly></td>
                    </tr>
                    <tr>
                        <td>{{ __('view.not_subject_to_iva_subtotal') }}</td>
                        <td><input class="form-control" type="text" id="notsubjectivasubtotal" name="notsubjectivasubtotal" value="" readonly></td>
                    </tr>
                    <tr>
                        <td>{{ __('view.exempt_from_iva_subtotal') }}</td>
                        <td><input class="form-control" type="text" id="exemptivasubtotal" name="exemptivasubtotal" value="" readonly></td>
                    </tr>
                    <tr>
                        <td>{{ __('view.subtotal') }}</td>
                        <td><input class="form-control" type="text" id="subtotal" name="subtotal" value="" readonly></td>
                    </tr>
                    <tr>
                        <td>{{ __('view.total_discount') }}</td>
                        <td><input class="form-control" type="text" id="totaldiscount" name="totaldiscount" value="" readonly></td>
                    </tr>
                    <!--<tr>
                        <td>{{ __('view.ice_value') }}</td>
                        <td><input class="form-control" type="text" id="icevalue" name="icevalue" value="" readonly></td>
                    </tr>
                    <tr>
                        <td>{{ __('view.irbpnr_value') }}</td>
                        <td><input class="form-control" type="text" id="irbpnrvalue" name="irbpnrvalue" value="" readonly></td>
                    </tr>-->
                    <tr>
                        <td>{{ __('view.iva_12_value') }}</td>
                        <td><input class="form-control" type="text" id="ivavalue" name="ivavalue" value="" readonly></td>
                    </tr>
                    <tr>
                        <td>{{ __('view.total') }}</td>
                        <td><input class="form-control" type="text" id="total" name="total" value="" readonly></td>
                    </tr>
                </tbody>
            </table>
        </div>
    </div>
</div>
