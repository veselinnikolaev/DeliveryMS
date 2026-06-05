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
        $(document).on("click", "#profileImageWrapper", function (e) {
            e.preventDefault();
            $("#profilePicInput").trigger("click");
        });

        $(document).on("change", "#profilePicInput", function (e) {
            e.preventDefault();

            var file = this.files[0];
            if (!file)
                return; // Предпазва от грешки, ако няма файл

            var formData = new FormData();
            formData.append("profile_picture", file);
            var userId = $("#user_id").val();
            formData.append("user_id", userId);
            // Add CSRF token to FormData
            var csrfToken = $('input[name="csrf_token"]').val() || $('meta[name="csrf_token"]').attr('content');
            if (csrfToken) {
                formData.append("csrf_token", csrfToken);
            }

            $.ajax({
                url: "index.php?controller=User&action=uploadProfilePicture",
                type: "POST",
                data: formData,
                processData: false, // Задължително за FormData
                contentType: false, // Задължително за FormData
                dataType: "json",
                success: function (response) {
                    if (response.status === "success" && response.photo_path) {
                        $('[id="profileImage"]').attr("src", response.photo_path);
                    } else {
                        alert("Error: " + (response.message || "Unknown error"));
                    }
                },
                error: function (xhr, status, error) {
                    console.error("Upload error:", status, error);
                    alert("An error occurred while uploading the image.");
                }
            });
        });
    });
}(jQuery));
