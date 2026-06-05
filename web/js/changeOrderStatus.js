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
                    data: {
                        ids: orderId, 
                        status: status,
                        csrf_token: $('input[name="csrf_token"]').val() || $('meta[name="csrf_token"]').attr('content')
                    },
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
