<script type="text/javascript">
$(document).ready(function(){
    var invoiceTable = $('#invoice-table').DataTable({
        paging: false,
        dom: 'Bfrtip',
        buttons: [{
            text: 'Add row',
            action: function(e, dt, node, config){
                var _token = $('input[name = "_token"]').val();
                $.ajax({
                    url: "{{ url('/resource/branch/products') }}/" + $("#branch").val(),
                    method: "GET",
                    success: function(result) {
                        var options = '';
                        var products = JSON.parse(result);
                        for (var i = 0; i < products.length; i++) {
                            options += '<option value="' + products[i]['id'] + '">' + products[i]['name'] + '</option>';
                        }
                        invoiceTable.row.add([
                            '<select class="form-control selectpicker" id="product[]" name="product[]" data-live-search="true" title="Select a product ...">' + options + '</select>',
                            '<input class="form-control" type="text" id="product-description[]" name="product-description[]" value="H" readonly>',
                            '<input class="form-control" type="text" id="product-quantity[]" name="product-quantity[]" value="">',
                            '<input class="form-control" type="text" id="product-unitprice[]" name="product-unitprice[]" value="">',
                            '<label id="product-iva[]" name="product-iva[]"></label>',
                            '<input class="form-control" type="text" id="product-discount[]" name="product-discount[]" value="">',
                            '<label id="product-subtotal[]" name="product-subtotal[]"></label>',
                            '<button type="button" class="btn btn-danger btn-sm"><strong>X</strong></button>',
                        ]).draw(false);
                        $('select[id *= product]').selectpicker();
                    }
                });
            }
        }]
    });
    $('#invoice-table tbody').on('changed.bs.select', 'select[id *= product]', function(){
        //console.log($(this).closest('tr').find('input[id *= product-description]').val());
        //$(this).closest('tr').find('input[id *= product-description]').val('HOLA MUNDO');
    });
    $('#invoice-table tbody').on('click', 'button.btn.btn-danger.btn-sm', function(){
        invoiceTable
            .row($(this).parents('tr'))
            .remove()
            .draw();
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
