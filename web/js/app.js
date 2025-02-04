(function ($) {
    $(function () {

        $('#courier-table-id').dataTable({
            order: [[1, 'asc']],
            columnDefs: [
                {orderable: false, targets: [0, -1]}
            ]
        });

        $('#product-table-id').dataTable({
            order: [[1, 'asc']],
            columnDefs: [
                {orderable: false, targets: [0, -1]}
            ]
        });

        $('#user-table-id').dataTable({
            order: [[1, 'asc']],
            columnDefs: [
                {orderable: false, targets: [0, -1]}
            ]
        });

        $('#order-table-id').dataTable({
            order: [[1, 'asc']],
            columnDefs: [
                {orderable: false, targets: [0, -1]}
            ]
        });

        $('#order-products-table-id').dataTable();

        $('#deliveryDate').datepicker({});

        $(document).delegate("#calculate-price-btn-id", "click", function (e) {
            e.preventDefault();

            var frm = $("#booking-frm-id");

            $.ajax({
                url: 'index.php?controller=Order&action=calculatePrice', // Server endpoint (PHP file)
                type: 'POST',
                dataType: "json",
                data: frm.serialize(),
                success: function (data) {
                    if ($("#productPrice").length > 0) {
                        $("#productPrice").val(data.product_price);
                    }
                    if ($("#shippingPrice").length > 0) {
                        $("#shippingPrice").val(data.shipping_price);
                    }
                    if ($("#totalPrice").length > 0) {
                        $("#totalPrice").val(data.total);
                    }
                    if ($("#tax").length > 0) {
                        $("#tax").val(data.tax);
                    }
                }
            });
        }).delegate(".delete-curier", "click", function (e) {
            e.preventDefault();

            var $id = $(this).attr('data-id');

            $("#delete-btn-curier-id").attr('data-id', $id);

            $('#deleteCurier').modal('show');
        }).delegate("#delete-btn-curier-id", 'click', function (e) {
            e.preventDefault();

            var $id = $(this).attr('data-id');
            var $this = $(this);

            $.ajax({
                url: 'index.php?controller=Courier&action=delete', // Server endpoint (PHP file)
                type: 'POST',
                data: {
                    id: $id
                },
                success: function (res) {
                    $('#container-curier-id').html(res);

                    $this.attr('data-id', '');

                    $('#deleteCurier').modal('hide');
                }
            });
        }).delegate(".delete-product", "click", function (e) {
            e.preventDefault();

            var $id = $(this).attr("data-id");

            $("#delete-btn-product-id").attr('data-id', $id);

            $('#deleteProduct').modal('show');
        }).delegate("#delete-btn-product-id", 'click', function (e) {
            e.preventDefault();

            var $id = $(this).attr('data-id');
            var $this = $(this);

            $.ajax({
                url: 'index.php?controller=Product&action=delete', // Server endpoint (PHP file)
                type: 'POST',
                data: {
                    id: $id
                },
                success: function (res) {
                    $('#container-product-id').html(res);

                    $this.attr('data-id', '');

                    $('#deleteProduct').modal('hide');
                }
            });
        }).delegate(".delete-user", "click", function (e) {
            e.preventDefault();

            var $id = $(this).attr('data-id');

            $("#delete-btn-user-id").attr('data-id', $id);

            $('#deleteUser').modal('show');
        }).delegate("#delete-btn-user-id", 'click', function (e) {
            e.preventDefault();

            var $id = $(this).attr('data-id');
            var $this = $(this);

            $.ajax({
                url: 'index.php?controller=User&action=delete', // Server endpoint (PHP file)
                type: 'POST',
                data: {
                    id: $id
                },
                success: function (res) {
                    $('#container-user-id').html(res);

                    $this.attr('data-id', '');

                    $('#deleteUser').modal('hide');
                }
            });
        }).delegate(".delete-order", "click", function (e) {
            e.preventDefault();

            var $id = $(this).attr('data-id');

            $("#delete-btn-order-id").attr('data-id', $id);

            $('#deleteOrder').modal('show');
        }).delegate("#delete-btn-order-id", 'click', function (e) {
            e.preventDefault();

            var $id = $(this).attr('data-id');
            var $this = $(this);

            $.ajax({
                url: 'index.php?controller=Order&action=delete',
                type: 'POST',
                data: {
                    id: $id
                },
                success: function (res) {
                    $('#container-order-id').html(res);

                    $this.attr('data-id', '');

                    $('#deleteOrder').modal('hide');
                }
            });
        }).delegate(".add-row", "click", function () {
            const $currentRow = $(this).closest('.product-row');
            const $newRow = $currentRow.clone();

            // Clear inputs in the cloned row
            $newRow.find('select').val('');
            $newRow.find('input[type="number"]').val('');

            // Update buttons
            $currentRow.find('.add-row')
                    .removeClass('add-row')
                    .addClass('remove-row')
                    .text('-')
                    .removeClass('btn-light')
                    .addClass('btn-danger');

            // Append the new row to the parent container
            $('#productRows').append($newRow);
        }).delegate(".remove-row", "click", function () {
            $(this).closest('.product-row').remove();
            validateTotalQuantity();
        }).delegate('select[name="product_id[]"]', 'change', function () {
            updateQuantityMax(this);
            validateTotalQuantity();
        })
                .delegate('input[name="quantity[]"]', 'input', function () {
                    validateTotalQuantity();
                });

        $('select[name="product_id[]"]').each(function () {
            updateQuantityMax(this);
        });

        function updateQuantityMax(selectElement) {
            var selectedOption = $(selectElement).find('option:selected');
            var maxQuantity = selectedOption.data('max-quantity') || 1;
            var quantityInput = $(selectElement).closest('.product-row').find('input[name="quantity[]"]');
            quantityInput.attr('max', maxQuantity);
        }

        function validateTotalQuantity() {
            let productQuantities = {};

            // Collect product quantities and calculate totals
            $("select[name='product_id[]']").each(function () {
                let productId = $(this).val();
                let $row = $(this).closest('.product-row');
                let $quantityInput = $row.find('input[name="quantity[]"]');
                let quantity = parseInt($quantityInput.val()) || 0;
                let maxQuantity = $(this).find('option:selected').data('max-quantity') || 1;

                if (!productQuantities[productId]) {
                    productQuantities[productId] = {total: 0, max: maxQuantity, inputs: []};
                }

                productQuantities[productId].total += quantity;
                productQuantities[productId].inputs.push($quantityInput);
            });

            // Adjust each input field dynamically based on the total used
            Object.keys(productQuantities).forEach(productId => {
                let productData = productQuantities[productId];
                let totalUsed = productData.total;
                let maxAllowed = productData.max;

                productData.inputs.forEach($input => {
                    let currentValue = parseInt($input.val()) || 0;
                    let availableStock = maxAllowed - (totalUsed - currentValue);

                    // Ensure availableStock never goes below 0
                    availableStock = Math.max(0, availableStock);

                    // If total exceeds max, allow reducing the quantity by setting min = 0
                    if (totalUsed > maxAllowed) {
                        $input.attr('min', 0);
                    } else {
                        $input.attr('min', Math.min(1, availableStock)); // If possible, min should be 1
                    }

                    // Max should be limited to availableStock
                    $input.attr('max', availableStock);

                    // Prevent input values from exceeding new max
                    if (currentValue > availableStock) {
                        $input.val(availableStock);
                    }
                });
            });
        }
    });
}(jQuery));