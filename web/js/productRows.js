(function ($) {
    $(function () {
        $(document).on("click", ".add-row", function () {
            const $currentRow = $(this).closest('.product-row');
            const $newRow = $currentRow.clone();

            $newRow.find('select').val('');
            $newRow.find('input[type="number"]').val('');

            $currentRow.find('.add-row')
                .removeClass('add-row')
                .addClass('remove-row')
                .text('-')
                .removeClass('btn-light')
                .addClass('btn-danger');

            $('#productRows').append($newRow);
        });

        $(document).on("click", ".remove-row", function () {
            $(this).closest('.product-row').remove();
            validateTotalQuantity();
        });

        $(document).on('change', 'select[name="product_id[]"]', function () {
            updateQuantityMax(this);
            validateTotalQuantity();
        });

        $(document).on('input', 'input[name="quantity[]"]', function () {
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

            $("select[name='product_id[]']").each(function () {
                let productId = $(this).val();
                let $row = $(this).closest('.product-row');
                let $quantityInput = $row.find('input[name="quantity[]"]');
                let quantity = parseInt($quantityInput.val()) || 0;
                let maxQuantity = $(this).find('option:selected').data('max-quantity') || 1;

                if (!productQuantities[productId]) {
                    productQuantities[productId] = { total: 0, max: maxQuantity, inputs: [] };
                }

                productQuantities[productId].total += quantity;
                productQuantities[productId].inputs.push($quantityInput);
            });

            Object.keys(productQuantities).forEach(productId => {
                let productData = productQuantities[productId];
                let totalUsed = productData.total;
                let maxAllowed = productData.max;

                productData.inputs.forEach($input => {
                    let currentValue = parseInt($input.val()) || 0;
                    let availableStock = maxAllowed - (totalUsed - currentValue);
                    availableStock = Math.max(0, availableStock);
                    $input.attr('min', totalUsed > maxAllowed ? 0 : 1);
                    $input.attr('max', availableStock);
                    if (currentValue > availableStock) {
                        $input.val(availableStock);
                    }
                });
            });
        }
    });
}(jQuery));
