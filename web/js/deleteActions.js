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
        function setupDeleteButton(entity) {
            $(document).on("click", `.delete-${entity}`, function (e) {
                e.preventDefault();
                var id = $(this).attr("data-id");
                $(`#delete-btn-${entity}-id`).attr('data-id', id);
                $(`#delete${entity.charAt(0).toUpperCase() + entity.slice(1)}`).modal('show');
            });

            $(document).on("click", `#delete-btn-${entity}-id`, function (e) {
                e.preventDefault();
                var id = $(this).attr('data-id');
                var $this = $(this);

                $.ajax({
                    url: `index.php?controller=${entity.charAt(0).toUpperCase() + entity.slice(1)}&action=delete`,
                    type: 'POST',
                    data: {
                        id: id,
                        csrf_token: $('input[name="csrf_token"]').val() || $('meta[name="csrf_token"]').attr('content')
                    },
                    success: function (res) {
                        $(`#container-${entity}-id`).html(res);
                        $this.attr('data-id', '');
                        $(`#delete${entity.charAt(0).toUpperCase() + entity.slice(1)}`).modal('hide');

                        $(`#${entity}-table-id`).dataTable({
                            order: [[1, 'asc']],
                            columnDefs: [{orderable: false, targets: [0, -1]}]
                        });
                    }
                });
            });
        }

        ['courier', 'product', 'user', 'order'].forEach(entity => setupDeleteButton(entity));


        $(document).on("click", "#deleteAccount", function (e) {
            e.preventDefault();
            var id = $(this).attr("data-id");
            $("#confirmDeleteBtn").attr('data-id', id);
            $("#deleteAccountModal").modal('show');
        });

        $(document).on("click", "#confirmDeleteBtn", function (e) {
            e.preventDefault();
            var id = $(this).attr('data-id');
            var confrim = $('#confirm').val();
            var $this = $(this);

            // Check if the confirmation text is correct
            if (confirm === "CONFIRM") {
                $.ajax({
                    url: "index.php?controller=User&action=delete",
                    type: 'POST',
                    data: {
                        id: id,
                        csrf_token: $('input[name="csrf_token"]').val() || $('meta[name="csrf_token"]').attr('content')
                    },
                    success: function (res) {
                        $this.attr('data-id', '');
                        $("#deleteAccountModal").modal('hide');
                        // Redirect to index.php on success
                        window.location.href = "index.php";
                    }
                });
            } else {
                // Optionally show an error message if confirmation text is incorrect
                alert("Please type CONFIRM to delete your account");
            }
        });
    });
}(jQuery));
