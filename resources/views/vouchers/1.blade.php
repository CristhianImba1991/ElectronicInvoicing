<script src="{{ asset('js/bootstrap-select.min.js') }}"></script>
@include('vouchers.js.1', ['action' => $action])
<div class="col-sm-12">
    <div class="card mb-3">
        <div class="card-body">
            <h5 class="card-title">
                5. {{ __('view.invoice') }}
                @if(auth()->user()->can('create_products'))
                    <button type="button" class="btn btn-sm btn-success float-right" data-toggle="modal" data-target="#productModal">{{ trans_choice(__('view.new_model', ['model' => trans_choice(__('view.product'), 0)]), 0) }}</button>
                @endif
            </h5>
            <table id="invoice-table" class="display">
                <thead>
                    <tr>
                        <th>{{ ucfirst(trans_choice(__('view.product'), 0)) }}</th>
                        <th>{{ __('view.description') }}</th>
                        <th>{{ __('view.additional_detail') }}</th>
                        <th>{{ __('view.additional_detail') }}</th>
                        <th>{{ __('view.additional_detail') }}</th>
                        <th>{{ __('view.quantity') }}</th>
                        <th>{{ __('view.unit_price') }}</th>
                        <th>{{ __('view.iva_tax') }}</th>
                        <th>{{ __('view.discount') }}</th>
                        <th>{{ __('view.subtotal') }}</th>
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
            <h5 class="card-title">7. {{ __('view.additional_information') }}</h5>
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
                        <th>{{ __('view.waybill') }}</th>
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
    <div class="card mb-3">
        <div class="card-body">
            <h5 class="card-title">8. {{ __('view.retention') }}</h5>
            <table class="table table-striped">
                <tbody>
                    <tr>
                        <td>
                            <input class="form-check-input" type="checkbox" value="" id="ivaRetention" name="ivaRetention">
                            <label class="form-check-label" for="ivaRetention">{{ __('view.iva_retention') }}</label>
                        </td>
                        <td><input class="form-control" type="text" id="ivaRetentionValue" name="ivaRetentionValue" value="" readonly></td>
                    </tr>
                    <tr>
                        <td>
                            <input class="form-check-input" type="checkbox" value="" id="rentRetention" name="rentRetention">
                            <label class="form-check-label" for="rentRetention">{{ __('view.rent_retention') }}</label>
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
            <h5 class="card-title">9. {{ __('view.total') }}</h5>
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
                        <td>{{ __('view.tip') }}</td>
                        <td><input class="form-control" type="text" id="tip" name="tip" value="0.0"></td>
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
@if(auth()->user()->can('create_products'))
    <div class="modal fade" tabindex="-1" role="dialog" id="productModal">
        <div class="modal-dialog modal-dialog-centered" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <strong>{{ trans_choice(__('view.new_model', ['model' => trans_choice(__('view.product'), 0)]), 0) }}</strong>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <form id="product_form">
                    {{ csrf_field() }}
                    <div class="modal-body">
                        <div class="form-group">
                            <label for="product_company">{{ ucfirst(trans_choice(__('view.company'), 0)) }}</label>
                            <select class="form-control selectpicker input-lg dynamic" id="product_company" name="company" data-live-search="true" data-dependent="branch" title="{{ trans_choice(__('view.select_a_model', ['model' => trans_choice(__('view.company'), 0)]), 1) }}">
                                @foreach($companiesproduct as $company)
                                    <option value="{{ $company->id }}">{{ $company->tradename }} - {{ $company->social_reason }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="form-group" id="divbranch">
                            <label for="product_branch">{{ ucfirst(trans_choice(__('view.branch'), 0)) }}</label>
                            <select class="form-control selectpicker input-lg" id="product_branch" name="branch" data-live-search="true" title="{{ trans_choice(__('view.select_a_model', ['model' => trans_choice(__('view.branch'), 0)]), 1) }}">

                            </select>
                        </div>
                        <div class="form-group">
                            <label for="product_main_code">{{ __('view.main_code') }}</label>
                            <input type="text" class="form-control" id="product_main_code" name="main_code" value="">
                        </div>
                        <div class="form-group">
                            <label for="product_auxiliary_code">{{ __('view.auxiliary_code') }}</label>
                            <input type="text" class="form-control" id="product_auxiliary_code" name="auxiliary_code"  value="">
                        </div>
                        <div class="form-group">
                            <label for="product_unit_price">{{ __('view.unit_price') }}</label>
                            <input type="number" step="0.01" class="form-control" id="product_unit_price" name="unit_price"  value="">
                        </div>
                        <div class="form-group">
                            <label for="product_description">{{ __('view.description') }}</label>
                            <input type="text" class="form-control" id="product_description" name="description"  value="">
                        </div>
                        <div class="form-group">
                            <label for="product_stock">{{ __('view.stock') }}</label>
                            <input type="number" class="form-control" id="product_stock" name="stock"  value="">
                        </div>
                        <div class="form-group">
                            <label for="product_iva_tax">{{ __('view.iva_tax') }}</label>
                            <select class="form-control selectpicker" id="product_iva_tax" name="iva_tax" data-live-search="true"  title="{{ trans_choice(__('view.select_a_model', ['model' => lcfirst(__('view.iva_tax'))]), 0) }}">
                                @foreach($iva_taxes as $iva_tax)
                                    <option value="{{ $iva_tax->id }}">{{ $iva_tax->auxiliary_code }} - {{ $iva_tax->description }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="form-group">
                            <label for="product_ice_tax">{{ __('view.ice_tax') }}</label>
                            <select class="form-control selectpicker" id="product_ice_tax" name="ice_tax" data-live-search="true" title="{{ trans_choice(__('view.select_a_model', ['model' => lcfirst(__('view.ice_tax'))]), 0) }}">
                                @foreach($ice_taxes as $ice_tax)
                                    <option value="{{ $ice_tax->id }}">{{ $ice_tax->auxiliary_code }} - {{ $ice_tax->description }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="form-group">
                            <label for="product_irbpnr_tax">{{ __('view.irbpnr_tax') }}</label>
                            <select class="form-control selectpicker" id="product_irbpnr_tax" name="irbpnr_tax" data-live-search="true" title="{{ trans_choice(__('view.select_a_model', ['model' => lcfirst(__('view.irbpnr_tax'))]), 0) }}">
                                @foreach($irbpnr_taxes as $irbpnr_tax)
                                    <option value="{{ $irbpnr_tax->id }}">{{ $irbpnr_tax->auxiliary_code }} - {{ $irbpnr_tax->description }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-sm btn-secondary" data-dismiss="modal">{{ __('view.close') }}</button>
                        <button id="submit_product" type="button" class="btn btn-sm btn-success">{{ __('view.add') }}</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
@endif
