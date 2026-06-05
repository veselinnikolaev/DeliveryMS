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
        function setupSkipButton() {
            $(document).on("click", `.skip-mail-config`, function (e) {
                e.preventDefault();
                $(`#skipMailConfig`).modal('show');
            });

            // Изпращане на POST заявка при натискане на "Skip" в модала
            $(document).on("click", "#skip-mail-config-button", function (e) {
                e.preventDefault();

                $.ajax({
                    url: "index.php?controller=Install&action=step4",
                    type: "POST",
                    headers: {
                        "X-CSRF-Token": $('meta[name="csrf_token"]').attr('content')
                    },
                    data: {
                        skip_mail: true
                    },
                    success: function (response) {
                        window.location.href = "index.php?controller=Install&action=step5";
                    },
                    error: function (xhr) {
                        console.log(xhr.status, xhr.responseText);
                        alert("An error occurred while skipping mail configuration.");
                    }
                });
            });
        }
        setupSkipButton();
    });
}(jQuery));
