(function ($) {
    $(function () {
        function setupPrint(entity) {
            // Print button click event
            $(`#print-${entity}s`).on('click', function (e) {
                e.preventDefault();
                printDisplayedEntities();
            });

            function printDisplayedEntities() {
                // Extract column headers
                let headers = [];
                $(`#${entity}-table-id thead th`).each(function () {
                    let headerText = $(this).text().trim();
                    if (!$(this).find('input[type="checkbox"]').length && headerText !== '') {
                        headers.push({
                            text: headerText,
                            key: headerText.toLowerCase().replace(/\s+/g, '_')
                        });
                    }
                });

                // Extract table data
                let entities = [];
                $(`#${entity}-table-id tbody tr`).each(function () {
                    let entityObj = {};
                    let cellIndex = 0;
                    $(this).find('td').each(function (index, td) {
                        if (!$(td).find('input[type="checkbox"]').length) {
                            if (index < $(this).parent().find('td').length - 1 && cellIndex < headers.length) {
                                entityObj[headers[cellIndex].key] = $(td).text().trim();
                                cellIndex++;
                            }
                        }
                    });
                    if (Object.keys(entityObj).length > 0) {
                        entities.push(entityObj);
                    }
                });

                // Stop if there's no data to print
                if (entities.length === 0) {
                    alert('No entities to print.');
                    return;
                }

                // Create a temporary hidden iframe for printing
                let printFrame = $('<iframe>', {
                    name: 'print-frame',
                    style: 'position:absolute;width:0;height:0;left:-9999px;top:-9999px;'
                }).appendTo('body');

                // Send AJAX request to fetch printable content
                $.ajax({
                    url: `index.php?controller=${entity.charAt(0).toUpperCase() + entity.slice(1)}&action=print`,
                    type: 'POST',
                    data: {[`${entity}Data`]: JSON.stringify(entities)},
                    success: function (response) {
                        let frame = window.frames['print-frame'];
                        frame.document.open();
                        frame.document.write(response);
                        frame.document.close();
                        setTimeout(() => {
                            frame.focus();
                            frame.print();
                            setTimeout(() => printFrame.remove(), 1000);
                        }, 500);
                    },
                    error: function () {
                        alert('Error preparing the print view.');
                    }
                });
            }
        }

        // Initialize for all entities
        ['courier', 'product', 'user', 'order'].forEach(setupPrint);
    });
}(jQuery));
