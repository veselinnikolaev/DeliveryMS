(function ($) {
    $(function () {
        // Apply Filters
        $(document).on('click', '#apply-filters-courier', function () {
            let filters = {
                name: $('#filter-name').val(),
                phone: $('#filter-phone').val(),
                email: $('#filter-email').val(),
                address: $('#filter-address').val(),
                country: $('#filter-country').val(),
                region: $('#filter-region').val()
            };

            $.ajax({
                url: 'index.php?controller=Courier&action=filter',
                type: 'POST',
                data: filters,
                success: function (response) {
                    $(`#container-courier-id`).html(response);

                    $(`#courier-table-id`).dataTable({
                        order: [[1, 'asc']],
                        columnDefs: [{orderable: false, targets: [0, -1]}]
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
            $('#filter-address').val('');
            $('#filter-country').val('');
            $('#filter-region').val('');


            // Trigger apply filters to restore previous results
            $('#apply-filters-courier').trigger('click');
        });

        $(document).on('click', '#apply-filters-user', function () {
            let roles = [];
            $('.form-check-input:checked').each(function () {
                roles.push($(this).val());
            });

            if (roles.length === 0) {
                alert('Please select at least one role before applying filters.');
                return;
            }

            let filters = {
                name: $('#filter-name').val(),
                phone: $('#filter-phone').val(),
                email: $('#filter-email').val(),
                roles: roles,
                address: $('#filter-address').val(),
                country: $('#filter-country').val(),
                region: $('#filter-region').val()
            };

            $.ajax({
                url: 'index.php?controller=User&action=filter',
                type: 'POST',
                data: filters,
                success: function (response) {
                    $(`#container-user-id`).html(response);

                    $(`#user-table-id`).dataTable({
                        order: [[1, 'asc']],
                        columnDefs: [{orderable: false, targets: [0, -1]}]
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
            $('.form-check-input').prop('checked', true);

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
                data: filters,
                success: function (response) {
                    $(`#container-product-id`).html(response);

                    $(`#product-table-id`).dataTable({
                        order: [[1, 'asc']],
                        columnDefs: [{orderable: false, targets: [0, -1]}]
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

        $(document).on('click', '#apply-filters-order', function () {
            let filters = {
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

            $.ajax({
                url: 'index.php?controller=Order&action=filter',
                type: 'POST',
                data: filters,
                success: function (response) {
                    $('#container-order-id').html(response);

                    $('#order-table-id').dataTable({
                        order: [[1, 'asc']],
                        columnDefs: [{orderable: false, targets: [0, -1]}]
                    });
                }
            });
        });

        $(document).on('click', '#reset-filters-order', function () {
            $('#filter-customer').val('');
            $('#filter-courier').val('');
            $('#filter-status').val('');
            $('#filter-tracking').val('');
            $('#filter-country').val('');
            $('#filter-region').val('');
            $('#filter-date-from').val('');
            $('#filter-date-to').val('');
            $('#filter-price-min').val('');
            $('#filter-price-max').val('');

            $('#apply-filters-order').trigger('click');
        });
    });
}(jQuery));
