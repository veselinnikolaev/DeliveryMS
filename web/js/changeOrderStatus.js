(function ($) {
    $(function () {
        function setupOrderStatusChange() {
            $(document).on("click", ".change-status", function (e) {
                e.preventDefault();

                var orderId = $(this).data("id");
                var status = $(this).data("status");

                // Запазване на ID и новата роля за потвърждението
                $("#confirmChangeStatusBtn").attr('data-ids', orderId)
                        .attr('data-status', status);

                // Отваряне на модала
                $("#changeStatusModal").modal("show");
            });

            // Потвърждаване на смяната
            $("#confirmChangeStatusBtn").on("click", function () {
                var orderId = $(this).data("ids");
                var status = $(this).data("status");

                $.ajax({
                    url: "index.php?controller=Order&action=changeStatus",
                    type: "POST",
                    data: {ids: orderId, status: status},
                    success: function (response) {
                        $(`#container-order-id`).html(response);

                        // Явно затваряне на модала
                        $("#changeStatusModal").modal("hide");

                        $(`#order-table-id`).dataTable({
                            order: [[1, 'asc']],
                            columnDefs: [{orderable: false, targets: [0, -1]}]
                        });
                    }
                });
            });
        }
        setupOrderStatusChange();
    });
}(jQuery));
