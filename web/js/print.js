(function ($) {
    $(function () {
        function setupPrint(entity) {
            // Print button functionality
            $(`#print-${entity}s`).on('click', function (e) {
                e.preventDefault();
                printDisplayedEntities();
            });

            // Function to handle printing of currently displayed entities
            function printDisplayedEntities() {
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

                // Create a hidden iframe for printing
                var printFrame = $('<iframe>', {
                    name: 'print-frame',
                    class: 'print-frame',
                    style: 'position:absolute;width:0;height:0;left:-500px;top:-500px;'
                }).appendTo('body');

                // Send a POST request to get printable content
                $.ajax({
                    url: `index.php?controller=${entity.charAt(0).toUpperCase() + entity.slice(1)}&action=print`,
                    type: 'POST',
                    data: {
                        [`${entity}Data`]: JSON.stringify(entities)
                    },
                    success: function (response) {
                        var printContent = response;
                        // Write the content to the iframe and print it
                        var frame = window.frames['print-frame'];
                        frame.document.write(printContent);
                        frame.document.close();
                        // Wait for all resources to load before printing
                        setTimeout(function () {
                            frame.focus();
                            frame.print();
                            // Remove the iframe after printing
                            setTimeout(function () {
                                printFrame.remove();
                            }, 1000);
                        }, 500);
                    },
                    error: function (xhr, status, error) {
                        console.error('Error getting print view:', error);
                        alert('An error occurred while preparing the print view.');
                    }
                });
            }
        }

        ['courier', 'product', 'user', 'order'].forEach(entity => setupPrint(entity));

        // The following filter functions are kept for backward compatibility
        // Function to get current product filters
        function getProductFilters() {
            return {
                name: $('#filter-name').val(),
                description: $('#filter-description').val(),
                minPrice: $('#filter-price-min').val(),
                maxPrice: $('#filter-price-max').val(),
                minStock: $('#filter-stock-min').val(),
                maxStock: $('#filter-stock-max').val()
            };
        }

        function getUserFilters() {
            return {
                name: $('#filter-name').val(),
                phone: $('#filter-phone').val(),
                email: $('#filter-email').val(),
                role: $('#filter-role').val(),
                address: $('#filter-address').val(),
                country: $('#filter-country').val(),
                region: $('#filter-region').val()
            };
        }

        function getOrderFilters() {
            return {
                customerName: $('#filter-customer').val(),
                courierName: $('#filter-courier').val(),
                status: $('#filter-status').val(),
                trackingNumber: $('#filter-tracking').val(),
                country: $('#filter-country').val(),
                region: $('#filter-region').val(),
                orderDateFrom: $('#filter-date-from').val(),
                orderDateTo: $('#filter-date-to').val(),
                minTotalPrice: $('#filter-price-min').val(),
                maxTotalPrice: $('#filter-price-max').val()
            };
        }

        function getCourierFilters() {
            return {
                name: $('#filter-name').val(),
                phone_number: $('#filter-phone').val(),
                email: $('#filter-email').val()
            };
        }

        function getFiltersByEntity(entity) {
            var filterFunctions = {
                'product': getProductFilters,
                'user': getUserFilters,
                'order': getOrderFilters,
                'courier': getCourierFilters
            };

            return filterFunctions[entity] ? filterFunctions[entity]() : {};
        }
    });
}(jQuery));