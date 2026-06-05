/**
 * CSRF TOKEN REQUIREMENT FOR AJAX POST REQUESTS
 * 
 * For every AJAX POST request in this project, always include the CSRF token by reading it from 
 * $('input[name="csrf_token"]').val() if a form is present on the page, or from 
 * $('meta[name="csrf_token"]').attr('content') as a fallback. 
 * 
 * The meta tag <meta name="csrf_token" content="<?= Security::getCsrfToken() ?>"> is always present 
 * in the layout head. Never send a POST request without including csrf_token in the request data 
 * or as the X-CSRF-Token header for XML HTTP requests. 
 * 
 * The backend validates CSRF on every POST in Core\Controller::validateCsrfOnPost().
 */

(function ($) {
    $(function () {
        $(document).on("click", "#calculate-price-btn-id", function (e) {
            e.preventDefault();
            var frm = $("#booking-frm-id");

            $.ajax({
                url: 'index.php?controller=Order&action=calculatePrice',
                type: 'POST',
                dataType: "json",
                data: {
                    ...Object.fromEntries(new URLSearchParams(frm.serialize())),
                    csrf_token: $('input[name="csrf_token"]').val() || $('meta[name="csrf_token"]').attr('content')
                },
                success: function (data) {
                    $("#productPrice").val(data.product_price || '');
                    $("#shippingPrice").val(data.shipping_price || '');
                    $("#totalPrice").val(data.total || '');
                    $("#tax").val(data.tax || '');
                }
            });
        });
    });
}(jQuery));
