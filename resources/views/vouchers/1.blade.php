<script type="text/javascript">
$(document).ready(function(){
    var invoiceTable = $('#invoice-table').DataTable({
        "paging": false
    });
    $('#add_product').on('click', function() {
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
        ]).draw(false);
        $('select[id*=product]').selectpicker();
    });
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
                        <th>Auxiliary code</th>
                        <th>Quantity</th>
                        <th>Description</th>
                        <th>Unit price</th>
                        <th>Discount</th>
                        <th>Total price</th>
                    </tr>
                </thead>
            </table>
            <div class="form-group">
                <button type="button" id="add_product" name="add_product" class="btn btn-sm">Add product</button>
            </div>
        </div>
    </div>
</div>
