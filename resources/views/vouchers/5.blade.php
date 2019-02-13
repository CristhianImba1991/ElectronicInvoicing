<script src="{{ asset('js/bootstrap-select.min.js') }}"></script>
<script src="{{ asset('js/bootstrap-datepicker.min.js') }}"></script>
@include('vouchers.js.5', ['action' => $action])
<div class="col-sm-12">
    <div class="card mb-3">
        <div class="card-body">
            <h5 class="card-title">5. Retention</h5>
            <table id="retention-table" class="display">
                <thead>
                    <tr>
                        <th>Tax</th>
                        <th>Description</th>
                        <th>Rate</th>
                        <th>Tax base</th>
                        <th>Retained value</th>
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
            <h5 class="card-title">6. Additional information</h5>
            <table id="additionaldetail-table" class="display">
                <thead>
                    <tr>
                        <th>Name</th>
                        <th>Value</th>
                        <th></th>
                    </tr>
                </thead>
            </table>
            <div class="form-group">
                <label for="fiscal_period">Fiscal period</label>
                <input class="form-control" id="fiscal_period" name="fiscal_period" readonly>
            </div>
            <table class="table table-striped">
                <thead>
                    <tr>
                        <th>Extra detail</th>
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
            <h5 class="card-title">7. Support document</h5>
            <div class="form-group">
                <label for="voucher_type_support_document">Voucher type of the support document</label>
                <select class="form-control selectpicker" id="voucher_type_support_document" name="voucher_type_support_document" data-live-search="true" data-dependent="branch" title="Select a voucher type ...">
                    @foreach($voucherTypes as $voucherType)
                        <option value="{{ $voucherType->id }}">{{ $voucherType->name }}</option>
                    @endforeach
                </select>
            </div>
            <table class="table table-striped">
                <thead>
                    <tr>
                        <th>Voucher number</th>
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
                <label for="issue_date_support_document">Issue date of the support document</label>
                <input class="form-control" id="issue_date_support_document" name="issue_date_support_document" readonly>
            </div>
        </div>
    </div>
</div>
