$(document).ready(function () {
    // Share button functionality - Open modal
    $('#share-products').on('click', function (e) {
        e.preventDefault();
        $('#shareModal').modal('show');
    });

    // Export format selection
    $('.export-format').on('click', function () {
        var format = $(this).data('format');
        exportDisplayedProducts(format);
        $('#shareModal').modal('hide');
    });

    // Function to export the currently displayed products
    function exportDisplayedProducts(format) {
        // Extract column headers from the table
        var headers = [];
        $('#product-table-id thead th').each(function (index, th) {
            var headerText = $(th).text().trim();
            // Skip checkbox column and empty action column
            if (!$(th).find('input[type="checkbox"]').length && headerText !== '') {
                // Convert header text to lowercase key with underscores
                var key = headerText.toLowerCase().replace(/\s+/g, '_');
                headers.push({
                    text: headerText,
                    key: key
                });
            }
        });

        // Extract data from rows
        var products = [];
        $('#product-table-id tbody tr').each(function () {
            var product = {};
            var cellIndex = 0;

            $(this).find('td').each(function (index, td) {
                // Skip checkbox column
                if (!$(td).find('input[type="checkbox"]').length) {
                    // Skip the action column (last column)
                    if (index < $(this).parent().find('td').length - 1 && cellIndex < headers.length) {
                        var cellValue = $(td).text().trim();
                        product[headers[cellIndex].key] = cellValue;
                        cellIndex++;
                    }
                }
            });

            // Only add if we have valid data
            if (Object.keys(product).length > 0) {
                products.push(product);
            }
        });

        // Debug - log the data structure
        console.log("Products to export:", products);

        // Create form for POST submission with the extracted data
        var $form = $('<form>', {
            'method': 'POST',
            'action': 'index.php?controller=Product&action=export',
            'target': '_blank'
        });

        // Add product data as JSON
        $('<input>').attr({
            type: 'hidden',
            name: 'productData',
            value: JSON.stringify(products)
        }).appendTo($form);

        // Add format
        $('<input>').attr({
            type: 'hidden',
            name: 'format',
            value: format
        }).appendTo($form);

        // Append form to body, submit it, and remove it
        $form.appendTo('body').submit().remove();
    }
});