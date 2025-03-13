(function ($) {
    $(function () {
        function setupExportButton(entity) {
// Share button functionality - Open modal
            $(`#share-${entity}s`).on('click', function (e) {
                e.preventDefault();
                $(`#${entity}sShareModal`).modal('show');
            });
            // Export format selection
            $(`.export-format-${entity}s`).on('click', function () {
                var format = $(this).data('format');
                exportDisplayedEntities(format);
                $(`#${entity}sShareModal`).modal('hide');
            });
            // Function to export the currently displayed products
            function exportDisplayedEntities(format) {
                // Extract column headers from the table
                var headers = [];
                $(`#${entity}-table-id thead th`).each(function (index, th) {
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
                var entities = [];
                $(`#${entity}-table-id tbody tr`).each(function () {
                    var entityObj = {};
                    var cellIndex = 0;
                    $(this).find('td').each(function (index, td) {
                        // Skip checkbox column
                        if (!$(td).find('input[type="checkbox"]').length) {
                            // Skip the action column (last column)
                            if (index < $(this).parent().find('td').length - 1 && cellIndex < headers.length) {
                                var cellValue = $(td).text().trim();
                                entityObj[headers[cellIndex].key] = cellValue;
                                cellIndex++;
                            }
                        }
                    });
                    // Only add if we have valid data
                    if (Object.keys(entityObj).length > 0) {
                        entities.push(entityObj);
                    }
                });

                // Show alert if there are no entities to export
                if (entities.length === 0) {
                    alert('No entities to export.');
                    return;
                }

                // Create form for POST submission with the extracted data
                var $form = $('<form>', {
                    'method': 'POST',
                    'action': `index.php?controller=${entity.charAt(0).toUpperCase() + entity.slice(1)}&action=export`,
                    'target': '_blank'
                });
                // Add product data as JSON
                $('<input>').attr({
                    type: 'hidden',
                    name: `${entity}Data`,
                    value: JSON.stringify(entities)
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
        }

        ['courier', 'product', 'user', 'order'].forEach(entity => setupExportButton(entity));
    });
}(jQuery));