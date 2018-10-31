<script type="text/javascript">
$(document).ready(function(){
    var invoiceTable = $('#invoice-table').DataTable({
        paging: false,
        dom: 'Bfrtip',
        buttons: [{
            text: 'Add row',
            action: function(e, dt, node, config){
                
                invoiceTable.row.add([
                    '<select class="form-control selectpicker" id="product[]" name="product[]" data-live-search="true" title="Select a product ...">' +
                        '<option value="1">VALUE</option>' +
                    '</select>',
                    '',
                    '',
                    '',
                    '',
                    '',
                    '',
                    '<button type="button" class="btn btn-danger btn-sm"><strong>X</strong></button>',
                ]).draw(false);
                $('select[id *= product]').selectpicker();
            }
        }]
    });
    $('#invoice-table tbody').on('click', 'button.btn-danger', function(){
        invoiceTable
            .row($(this).parents('tr') )
            .remove()
            .draw();
    } );
});
</script>
<div class="col-sm-12">
    <div class="card mb-3">
        <div class="card-body">
            <h5 class="card-title">5. INVOICE</h5>
            <table id="invoice-table" class="display">
                <thead>
                    <tr>
                        <th>Main code</th>
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
