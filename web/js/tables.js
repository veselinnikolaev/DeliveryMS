(function ($) {
    $(function () {
        $('#courier-table-id, #product-table-id, #user-table-id, #order-table-id').each(function () {
            $(this).dataTable({
                order: [[1, 'asc']],
                columnDefs: [{ orderable: false, targets: [0, -1] }]
            });
        });

        $('#order-products-table-id').dataTable({
            order: [[1, 'asc']],
            columnDefs: [{ orderable: false, targets: [0, -1] }]
        });
    });
}(jQuery));
