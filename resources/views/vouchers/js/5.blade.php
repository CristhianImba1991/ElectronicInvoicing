<script type="text/javascript">
$(document).ready(function(){
    $("#retention_type").change(function() {
        if($(this).val() != '' && $(this).val() != null) {
            $("#retention-information").html('');
            $.ajax({
                url: "{{ url('/manage/vouchers/retention') }}/" + $(this).val(),
                method: "GET",
                success: function(result) {
                    $("#retention-information").html(result);
                }
            })
        }
    });
    $('#retention_type').selectpicker('val', 1);
});
</script>
