<script type="text/javascript">
$(document).ready(function(){
    $('#supplier_identification_type').selectpicker();
    $('#related_party').selectpicker();
    $('#support_voucher').selectpicker();
    $('#support_document_type').selectpicker();
    $('#accounting_record_date').datepicker({
        autoclose: true,
        todayBtn: 'linked',
        todayHighlight: true,
        endDate: '0d',
        language: '{{ str_replace("_", "-", app()->getLocale()) }}',
        format: 'yyyy-mm-dd',
        daysOfWeekHighlighted: "0,6"
    });
    $('#payment_type').selectpicker();
    $('#foreign_fiscal_regime_type').change(function() {
        if($(this).val() != '' && $(this).val() != null) {
            var _token = $('input[name = "_token"]').val();
            var id = $(this).val();
            $.ajax({
                url: "{{ route('foreignFiscalRegimeTypes.countries') }}",
                method: "POST",
                data: {
                    _token: _token,
                    id: id,
                },
                success: function(result) {
                    var countries = JSON.parse(result);
                    var options = '';
                    for (var i = 0; i < countries.length; i++) {
                        options += '<option value="' + countries[i]['id'] + '">' + countries[i]['name'] + (id != 2 ? '' : ' - ' + countries[i]['tax_haven_name']) + '</option>';
                    }
                    $("#country").html(options).selectpicker('refresh');
                }
            });
        }
    });
    $('#foreign_fiscal_regime_type').selectpicker();
    $('#country').selectpicker();
    $('#double_taxation_agreement').selectpicker();
    $('#payment_aboard_subject_retention').selectpicker();
    $('#payment_tax_regime').selectpicker();
    function addRowTax() {
        var _token = $('input[name = "_token"]').val();
        $.ajax({
            url: "{{ route('supportDocument.taxes') }}",
            method: "POST",
            data: {
                _token: _token
            },
            success: function(result) {
                var options = '';
                var taxes = JSON.parse(result);
                for (var i = 0; i < taxes.length; i++) {
                    options += '<option value="' + taxes[i]['id'] + '">' + taxes[i]['name'] + '</option>';
                }
                taxTable.row.add([
                    '<select class="form-control selectpicker" id="tax[]" name="tax[]" data-live-search="true" title="{{ trans_choice(__("view.select_a_model", ["model" => strtolower(__("view.tax"))]), 0) }}">' + options + '</select>',
                    '<select class="form-control selectpicker" id="description[]" name="description[]" data-live-search="true" title="{{ trans_choice(__("view.select_a_model", ["model" => strtolower(__("view.tax_description"))]), 1) }}"></select>',
                    '<input class="form-control" type="number" id="rate[]" name="rate[]" min="0.00" max="100.00" value="0.00" step="0.01" lang="en">',
                    '<input class="form-control" type="text" id="tax_base[]" name="tax_base[]" value="0.00">',
                    '<input class="form-control" type="text" id="value[]" name="value[]" value="0.00" readonly>',
                    '<button type="button" class="btn btn-danger btn-sm">&times;</button>',
                ]).draw(false);
                $('select[id *= tax]').selectpicker();
                $('select[id *= description]').selectpicker();
            }
        });
    }
    var taxTable = $('#tax-table').DataTable({
        paging: false,
        dom: 'Bfrtip',
        buttons: [{
            text: '{{ __("view.add_row") }}',
            action: function(e, dt, node, config){
                addRowTax();
            }
        }]
    });
});
</script>
