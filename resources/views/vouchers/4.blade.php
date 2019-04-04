<script src="{{ asset('js/bootstrap-select.min.js') }}"></script>
<script src="{{ asset('js/bootstrap-datepicker.min.js') }}"></script>
<script src="{{ asset('js/locales/bootstrap-datepicker.' . str_replace('_', '-', app()->getLocale()) . '.min.js') }}" charset="UTF-8"></script>
@include('vouchers.js.4', ['action' => $action])
<div class="col-sm-12">
    <div class="card mb-3">
        <div class="card-body">
            <h5 class="card-title">
                5. {{ __('view.waybill') }}
                @if(auth()->user()->can('create_products'))
                    <button type="button" class="btn btn-sm btn-success float-right" data-toggle="modal" data-target="#productModal">{{ trans_choice(__('view.new_model', ['model' => trans_choice(__('view.product'), 0)]), 0) }}</button>
                @endif
            </h5>
            <table id="waybill-table" class="display">
                <thead>
                    <tr>
                        <th>{{ ucfirst(trans_choice(__('view.product'), 0)) }}</th>
                        <th>{{ __('view.description') }}</th>
                        <th>{{ __('view.additional_detail') }}</th>
                        <th>{{ __('view.additional_detail') }}</th>
                        <th>{{ __('view.additional_detail') }}</th>
                        <th>{{ __('view.quantity') }}</th>
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
            <h5 class="card-title">6. {{ __('view.carrier') }}</h5>
            <div class="form-group">
                <label for="identification_type">{{ __('view.carrier_identification_type') }}</label>
                <select class="form-control selectpicker" id="identification_type" name="identification_type" data-live-search="true" title="{{ trans_choice(__('view.select_a_model', ['model' => strtolower(__('view.identification_type'))]), 0) }}">
                    @foreach($identificationTypes as $identificationType)
                        <option value="{{ $identificationType->id }}">{{ $identificationType->name }}</option>
                    @endforeach
                </select>
            </div>
            <div class="form-group">
                <label for="carrier_ruc">{{ __('view.carrier_identification') }}</label>
                <input class="form-control" id="carrier_ruc" name="carrier_ruc" value="">
            </div>
            <div class="form-group">
                <label for="carrier_social_reason">{{ __('view.carrier_social_reason') }}</label>
                <input class="form-control" id="carrier_social_reason" name="carrier_social_reason" value="">
            </div>
            <div class="form-group">
                <label for="licence_plate">{{ __('view.licence_plate') }}</label>
                <input class="form-control" id="licence_plate" name="licence_plate" value="">
            </div>
            <div class="form-group">
                <label for="starting_address">{{ __('view.starting_address') }}</label>
                <input class="form-control" id="starting_address" name="starting_address" value="">
            </div>
            <div class="form-group">
                <label for="start_date_transport">{{ __('view.start_date_transport') }}</label>
                <input class="form-control" id="start_date_transport" name="start_date_transport" readonly>
            </div>
            <div class="form-group">
                <label for="end_date_transport">{{ __('view.end_date_transport') }}</label>
                <input class="form-control" id="end_date_transport" name="end_date_transport" readonly>
            </div>
        </div>
    </div>
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
            <h5 class="card-title">8. {{ __('view.support_document') }}</h5>
            <div class="form-group">
                <label for="voucher_type_support_document">{{ __('view.voucher_type_of_the_support_document') }}</label>
                <input class="form-control" id="voucher_type_support_document" name="voucher_type_support_document" value="{{ strtoupper(__('view.invoice')) }}" readonly>
            </div>
            <div class="form-group">
                <label for="authorization_number">{{ __('view.authorization_number') }}</label>
                <input class="form-control" id="authorization_number" name="authorization_number">
            </div>
            <div class="form-group">
                <label for="single_customs_doc">{{ __('view.single_customs_document') }}</label>
                <input class="form-control" id="single_customs_doc" name="single_customs_doc" >
            </div>
        </div>
    </div>
    <div class="card mb-3">
        <div class="card-body">
            <h5 class="card-title">9. {{ __('view.destination') }}</h5>
            <div class="form-group">
                <label for="address">{{ __('view.address') }}</label>
                <input class="form-control" id="address" name="address" value="">
            </div>
            <div class="form-group">
                <label for="transfer_reason">{{ __('view.transfer_reason') }}</label>
                <input class="form-control" id="transfer_reason" name="transfer_reason" value="">
            </div>
            <div class="form-group">
                <label for="destination_establishment_code">{{ __('view.destination_establishment_code') }}</label>
                <input class="form-control" id="destination_establishment_code" name="destination_establishment_code" value="">
            </div>
            <div class="form-group">
                <label for="route">{{ __('view.route') }}</label>
                <input class="form-control" id="route" name="route" value="">
            </div>
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
