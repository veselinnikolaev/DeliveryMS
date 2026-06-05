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
        function setupRoleChange() {
            $(document).on("click", ".change-role", function (e) {
                e.preventDefault();

                var userId = $(this).data("id");
                var userName = $(this).closest("tr").find("td:nth-child(3)").text();
                var currentRole = $(this).data("role");
                var newRole = currentRole === "admin" ? "user" : "admin";
                 
                // Запазване на ID и новата роля за потвърждението
                $("#user-id").val(userId);
                $("#new-role").val(newRole);

                // Показване на съобщението в модала
                $("#role-message").html(`
                    <p>Are you sure you want to change <strong>${userName}</strong>'s role from <strong>${currentRole}</strong> to <strong>${newRole}</strong>?</p>
                `);

                // Отваряне на модала
                $("#roleModal").modal("show");
            });

            // Потвърждаване на смяната
            $("#confirm-role-change").on("click", function () {
                var userId = $("#user-id").val();
                var newRole = $("#new-role").val();

                $.ajax({
                    url: "index.php?controller=User&action=changeRole",
                    type: "POST",
                    data: {
                        id: userId, 
                        role: newRole,
                        csrf_token: $('input[name="csrf_token"]').val() || $('meta[name="csrf_token"]').attr('content')
                    },
                    success: function (response) {
                        $(`#container-user-id`).html(response);

                        // Явно затваряне на модала
                        $("#roleModal").modal("hide");

                        $(`#user-table-id`).dataTable({
                            order: [[1, 'asc']],
                            columnDefs: [{ orderable: false, targets: [0, -1] }]
                        });
                    }
                });
            });
        }
        setupRoleChange();
    });
}(jQuery));
