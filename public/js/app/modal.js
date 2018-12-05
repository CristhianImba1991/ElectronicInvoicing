var model = document.getElementById("modal").getAttribute("data-model");
var table = document.getElementById("modal").getAttribute("data-table");
$.noConflict();
jQuery(document).ready(function($) {
    $('#' + table + '-table').DataTable();
    $('#' + model + 'Modal').on('show.bs.modal', function(event) {
        $(this).find('#modal-title').text($(event.relatedTarget).data('title'))
        $(this).find('#modal-body').text($(event.relatedTarget).data('body'))
        $(this).find("#modal-form").attr("action", $(event.relatedTarget).data('form'))
        $(this).find("#form-method").val($(event.relatedTarget).data('method'))
        $(this).find('#submit-action').attr("class", $(event.relatedTarget).data('class'))
        $(this).find('#submit-action').text($(event.relatedTarget).data('action'))
    });
});
