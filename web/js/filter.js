(function ($) {
    $(function () {
        // Apply Filters
        $(document).on('click', '#apply-filters-courier', function () {
            let filters = {
                name: $('#filter-name').val(),
                phone: $('#filter-phone').val(),
                email: $('#filter-email').val()
            };

            $.ajax({
                url: 'index.php?controller=Courier&action=filter',
                type: 'POST',
                data: {
                    name: filters.name,
                    phone_number: filters.phone,
                    email: filters.email
                },
                success: function (response) {
                    $(`#container-courier-id`).html(response);

                    $(`#courier-table-id`).dataTable({
                        order: [[1, 'asc']],
                        columnDefs: [{ orderable: false, targets: [0, -1] }]
                    });
                }
            });
        });

        // Reset Filters
        $(document).on('click', '#reset-filters-courier', function () {
            // Restore last applied filters
            $('#filter-name').val('');
            $('#filter-phone').val('');
            $('#filter-email').val('');

            // Trigger apply filters to restore previous results
            $('#apply-filters-courier').trigger('click');
        });

        $(document).on('click', '#apply-filters-user', function () {
            let filters = {
                name: $('#filter-name').val(),
                phone: $('#filter-phone').val(),
                email: $('#filter-email').val(),
                role: $('#filter-role').val().toLowerCase(),
                address: $('#filter-address').val(),
                country: $('#filter-country').val(),
                region: $('#filter-region').val()
            };

            $.ajax({
                url: 'index.php?controller=User&action=filter',
                type: 'POST',
                data: {
                    name: filters.name,
                    phone_number: filters.phone,
                    email: filters.email,
                    role: filters.role,
                    address: filters.address,
                    country: filters.country,
                    region: filters.region
                },
                success: function (response) {
                    $(`#container-user-id`).html(response);

                    $(`#user-table-id`).dataTable({
                        order: [[1, 'asc']],
                        columnDefs: [{ orderable: false, targets: [0, -1] }]
                    });
                }
            });
        });

        $(document).on('click', '#reset-filters-user', function () {
            // Restore last applied filters
            $('#filter-name').val('');
            $('#filter-phone').val('');
            $('#filter-email').val('');
            $('#filter-role').val('');
            $('#filter-address').val('');
            $('#filter-country').val('');
            $('#filter-region').val('');


            // Trigger apply filters to restore previous results
            $('#apply-filters-user').trigger('click');
        });

        $(document).on('click', '#apply-filters-product', function () {
            let filters = {
                name: $('#filter-name').val(),
                description: $('#filter-description').val(),
                minPrice: $('#filter-price-min').val(),
                maxPrice: $('#filter-price-max').val(),
                minStock: $('#filter-stock-min').val(),
                maxStock: $('#filter-stock-max').val()
            };

            $.ajax({
                url: 'index.php?controller=Product&action=filter',
                type: 'POST',
                data: {
                    name: filters.name,
                    description: filters.description,
                    minPrice: filters.minPrice,
                    maxPrice: filters.maxPrice,
                    minStock: filters.minStock,
                    maxStock: filters.maxStock
                },
                success: function (response) {
                    $(`#container-product-id`).html(response);

                    $(`#product-table-id`).dataTable({
                        order: [[1, 'asc']],
                        columnDefs: [{ orderable: false, targets: [0, -1] }]
                    });
                }
            });
        });

        $(document).on('click', '#reset-filters-product', function () {
            // Restore last applied filters
            $('#filter-name').val('');
            $('#filter-description').val('');
            $('#filter-price-min').val('');
            $('#filter-price-max').val('');
            $('#filter-stock-min').val('');
            $('#filter-stock-max').val('');


            // Trigger apply filters to restore previous results
            $('#apply-filters-product').trigger('click');
        });
    });
}(jQuery));
