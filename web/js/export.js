$(document).ready(function () {
    // Share button functionality - Open modal
    $('#share-products').on('click', function (e) {
        e.preventDefault();
        $('#shareModal').modal('show');
    });

    // Export format selection
    $('.export-format').on('click', function () {
        var format = $(this).data('format');
        exportProducts(format);
        $('#shareModal').modal('hide');
    });

    // Function to handle exporting
    function exportProducts(format) {
        // Get current filters
        var filters = getProductFilters();
        filters.format = format;

        // Create form for POST submission
        var $form = $('<form>', {
            'method': 'POST',
            'action': 'index.php?controller=Product&action=export',
            'target': '_blank'
        });

        // Add filter parameters as hidden inputs
        $.each(filters, function (key, value) {
            $('<input>').attr({
                type: 'hidden',
                name: key,
                value: value
            }).appendTo($form);
        });

        // Append form to body, submit it, and remove it
        $form.appendTo('body').submit().remove();
    }

    // Function to get current product filters
    function getProductFilters() {
        return {
            name: $('#filter-name').val(),
            description: $('#filter-description').val(),
            price_min: $('#filter-price-min').val(),
            price_max: $('#filter-price-max').val(),
            stock_min: $('#filter-stock-min').val(),
            stock_max: $('#filter-stock-max').val()
        };
    }
});