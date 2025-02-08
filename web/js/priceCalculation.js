(function ($) {
    $(function () {
        $(document).on("click", "#calculate-price-btn-id", function (e) {
            e.preventDefault();
            var frm = $("#booking-frm-id");

            $.ajax({
                url: 'index.php?controller=Order&action=calculatePrice',
                type: 'POST',
                dataType: "json",
                data: frm.serialize(),
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
