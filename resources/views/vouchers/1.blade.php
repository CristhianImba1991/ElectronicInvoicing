<script src="{{ asset('js/bootstrap-select.min.js') }}"></script>
@include('vouchers.js.1', ['action' => $action])
<div class="col-sm-12">
    <div class="card mb-3">
        <div class="card-body">
            <h5 class="card-title">
                5. Invoice
                @if(auth()->user()->can('create_products'))
                    <button type="button" class="btn btn-sm btn-success float-right" data-toggle="modal" data-target="#productModal">New product</button>
                @endif
            </h5>
            <table id="invoice-table" class="display">
                <thead>
                    <tr>
                        <th>Product</th>
                        <th>Description</th>
                        <th>Quantity</th>
                        <th>U. price</th>
                        <th>IVA</th>
                        <th>Discount</th>
                        <th>Subtotal</th>
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
            <h5 class="card-title">7. Additional information</h5>
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
                        <th>Waybill</th>
                    </tr>
                </thead>
                <tbody>
                    <tr>
                        <td>
                            <div class="form-row">
                                <div class="form-group col-md-3">
                                    <input class="form-control" type="text" id="waybill_establishment" name="waybill_establishment" size="3" value="" >
                                </div>
                                <div class="form-group col-md-3">
                                    <input class="form-control" type="text" id="waybill_emissionpoint" name="waybill_emissionpoint" size="3" value="" >
                                </div>
                                <div class="form-group col-md-5">
                                    <input class="form-control" type="text" id="waybill_sequential" name="waybill_sequential" size="9" value="" >
                                </div>
                            </div>
                        </td>
                    </tr>
                </tbody>
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
    <div class="card mb-3">
        <div class="card-body">
            <h5 class="card-title">8. Retentions</h5>
            <table class="table table-striped">
                <tbody>
                    <tr>
                        <td>
                            <input class="form-check-input" type="checkbox" value="" id="ivaRetention" name="ivaRetention">
                            <label class="form-check-label" for="ivaRetention">IVA retention</label>
                        </td>
                        <td><input class="form-control" type="text" id="ivaRetentionValue" name="ivaRetentionValue" value="" readonly></td>
                    </tr>
                    <tr>
                        <td>
                            <input class="form-check-input" type="checkbox" value="" id="rentRetention" name="rentRetention">
                            <label class="form-check-label" for="rentRetention">Rent retention</label>
                        </td>
                        <td><input class="form-control" type="text" id="rentRetentionValue" name="rentRetentionValue" value="" readonly></td>
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
                    <tr>
                        <td>Total discount</td>
                        <td><input class="form-control" type="text" id="totaldiscount" name="totaldiscount" value="" readonly></td>
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
                        <td>Tip</td>
                        <td><input class="form-control" type="text" id="tip" name="tip" value="0.0"></td>
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
@if(auth()->user()->can('create_products'))
    <div class="modal fade" tabindex="-1" role="dialog" id="productModal">
        <div class="modal-dialog modal-dialog-centered" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <strong>New product</strong>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <form id="product_form">
                    {{ csrf_field() }}
                    <div class="modal-body">
                        <div class="form-group">
                            <label for="product_company">Company</label>
                            <select class="form-control selectpicker input-lg dynamic" id="product_company" name="company" data-live-search="true" data-dependent="branch" title="Select a company ...">
                                @foreach($companiesproduct as $company)
                                    <option value="{{ $company->id }}">{{ $company->tradename }} - {{ $company->social_reason }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="form-group" id="divbranch">
                            <label for="product_branch">Branch</label>
                            <select class="form-control selectpicker input-lg" id="product_branch" name="branch" data-live-search="true" title="Select a branch ...">

                            </select>
                        </div>
                        <div class="form-group">
                            <label for="product_main_code">Main Code</label>
                            <input type="text" class="form-control" id="product_main_code" name="main_code" value="">
                        </div>
                        <div class="form-group">
                            <label for="product_auxiliary_code">Auxiliary Code</label>
                            <input type="text" class="form-control" id="product_auxiliary_code" name="auxiliary_code"  value="">
                        </div>
                        <div class="form-group">
                            <label for="product_unit_price">Unit Price</label>
                            <input type="number" step="0.01" class="form-control" id="product_unit_price" name="unit_price"  value="">
                        </div>
                        <div class="form-group">
                            <label for="product_description">Description</label>
                            <input type="text" class="form-control" id="product_description" name="description"  value="">
                        </div>
                        <div class="form-group">
                            <label for="product_stock">Stock</label>
                            <input type="number" class="form-control" id="product_stock" name="stock"  value="">
                        </div>
                        <div class="form-group">
                            <label for="product_iva_tax">Iva taxes</label>
                            <select class="form-control selectpicker" id="product_iva_tax" name="iva_tax" data-live-search="true"  title="Select an IVA tax ...">
                                @foreach($iva_taxes as $iva_tax)
                                    <option value="{{ $iva_tax->id }}">{{ $iva_tax->auxiliary_code }} - {{ $iva_tax->description }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="form-group">
                            <label for="product_ice_tax">Ice Taxes</label>
                            <select class="form-control selectpicker" id="product_ice_tax" name="ice_tax" data-live-search="true" title="Select an ICE tax ...">
                                @foreach($ice_taxes as $ice_tax)
                                    <option value="{{ $ice_tax->id }}">{{ $ice_tax->auxiliary_code }} - {{ $ice_tax->description }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="form-group">
                            <label for="product_irbpnr_tax">Irbpnr taxes</label>
                            <select class="form-control selectpicker" id="product_irbpnr_tax" name="irbpnr_tax" data-live-search="true" title="Select an IRBPNR tax ...">
                                @foreach($irbpnr_taxes as $irbpnr_tax)
                                    <option value="{{ $irbpnr_tax->id }}">{{ $irbpnr_tax->auxiliary_code }} - {{ $irbpnr_tax->description }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-sm btn-secondary" data-dismiss="modal">Close</button>
                        <button id="submit_product" type="button" class="btn btn-sm btn-success">Add</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
@endif
