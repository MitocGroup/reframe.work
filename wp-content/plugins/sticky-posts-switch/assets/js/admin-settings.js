jQuery(document).ready(function($) {
    $('.color-picker').wpColorPicker();

    $('#sticky-post-sortable').sortable({
        axis: 'y',
        cancel: ".ui-state-disabled",
        update: function (event, ui) {
            var sortOrder = $('#sticky-post-sortable').sortable('toArray').toString();
            $('#sticky-post-sort-order').val(sortOrder);
        }
    });
});