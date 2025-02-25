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
                    data: {id: userId, role: newRole},
                    success: function (response) {
                        $("#user-table-id").replaceWith($(response).find("#user-table-id"));

                        // Явно затваряне на модала
                        $("#roleModal").modal("hide");
                    }
                });
            });
        }
        setupRoleChange();
    });
}(jQuery));
