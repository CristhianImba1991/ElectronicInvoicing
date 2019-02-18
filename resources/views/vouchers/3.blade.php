<script src="{{ asset('js/bootstrap-select.min.js') }}"></script>
<script src="{{ asset('js/bootstrap-datepicker.min.js') }}"></script>
@include('vouchers.js.3', ['action' => $action])
<div class="col-sm-12">
    <div class="card mb-3">
        <div class="card-body">
            <h5 class="card-title">5. Debit note</h5>
            <table id="debitNote-table" class="display">
                <thead>
                    <tr>
                        <th>Reason</th>
                        <th>Value</th>
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
            <h5 class="card-title">6. Payment method</h5>
            <table id="paymentmethod-table" class="display">
                <thead>
                    <tr>
                        <th>Method</th>
                        <th>Value</th>
                        <th>Time</th>
                        <th>Term</th>
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
            <h5 class="card-title">7. Support document</h5>
            <div class="form-group">
                <label for="voucher_type_support_document">Voucher type of the support document</label>
                <input class="form-control" id="voucher_type_support_document" name="voucher_type_support_document" value="FACTURA" readonly>
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
    <div class="card mb-3">
        <div class="card-body">
            <h5 class="card-title">8. Additional information</h5>
            <table id="additionaldetail-table" class="display">
                <thead>
                    <tr>
                        <th>Name</th>
                        <th>Value</th>
                        <th></th>
                    </tr>
                </thead>
            </table>
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
            <h5 class="card-title">9. Total</h5>
            <div class="form-group">
                <label for="iva_tax">Iva taxes</label>
                <select class="form-control selectpicker" id="iva_tax" name="iva_tax" data-live-search="true"  title="Select an IVA tax ...">
                    @foreach($iva_taxes as $iva_tax)
                        <option value="{{ $iva_tax->id }}">{{ $iva_tax->auxiliary_code }} - {{ $iva_tax->description }}</option>
                    @endforeach
                </select>
            </div>
            <table class="table table-striped">
                <tbody>
                    <tr>
                        <td>IVA 12% subtotal</td>
                        <td><input class="form-control" type="text" id="ivasubtotal" name="ivasubtotal" value="" readonly></td>
                    </tr>
                    <tr>
                        <td>IVA 0% subtotal</td>
                        <td><input class="form-control" type="text" id="iva0subtotal" name="iva0subtotal" value="" readonly></td>
                    </tr>
                    <tr>
                        <td>Not subject to IVA subtotal</td>
                        <td><input class="form-control" type="text" id="notsubjectivasubtotal" name="notsubjectivasubtotal" value="" readonly></td>
                    </tr>
                    <tr>
                        <td>Exempt from IVA subtotal</td>
                        <td><input class="form-control" type="text" id="exemptivasubtotal" name="exemptivasubtotal" value="" readonly></td>
                    </tr>
                    <tr>
                        <td>Subtotal</td>
                        <td><input class="form-control" type="text" id="subtotal" name="subtotal" value="" readonly></td>
                    </tr>
                    <!--<tr>
                        <td>ICE value</td>
                        <td><input class="form-control" type="text" id="icevalue" name="icevalue" value="" readonly></td>
                    </tr>
                    <tr>
                        <td>IRBPNR value</td>
                        <td><input class="form-control" type="text" id="irbpnrvalue" name="irbpnrvalue" value="" readonly></td>
                    </tr>-->
                    <tr>
                        <td>IVA 12% value</td>
                        <td><input class="form-control" type="text" id="ivavalue" name="ivavalue" value="" readonly></td>
                    </tr>
                    <tr>
                        <td>Total</td>
                        <td><input class="form-control" type="text" id="total" name="total" value="" readonly></td>
                    </tr>
                </tbody>
            </table>
        </div>
    </div>
</div>
