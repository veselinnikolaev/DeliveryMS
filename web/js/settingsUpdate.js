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
        var form = $("#settings-form");
        var saveBtn = $("#save-btn");
        var undoBtn = $("#undo-btn");
        var inputs = $(".settings-input");

        // Store initial values for undo functionality
        var initialValues = {};
        inputs.each(function () {
            initialValues[$(this).attr("id")] = $(this).val();
        });

        // Enable buttons when input changes
        inputs.on("input", function () {
            var changed = false;
            inputs.each(function () {
                if ($(this).val() !== initialValues[$(this).attr("id")]) {
                    changed = true;
                }
            });
            saveBtn.prop("disabled", !changed);
            undoBtn.prop("disabled", !changed);
        });

        // Undo functionality
        undoBtn.on("click", function () {
            inputs.each(function () {
                $(this).val(initialValues[$(this).attr("id")]);
            });
            saveBtn.prop("disabled", true);
            undoBtn.prop("disabled", true);
        });

        // AJAX form submission
        form.on("submit", function (e) {
            e.preventDefault();
            var formData = form.serialize();
            // Add CSRF token to form data
            var csrfToken = $('input[name="csrf_token"]').val() || $('meta[name="csrf_token"]').attr('content');
            if (csrfToken) {
                formData += '&csrf_token=' + encodeURIComponent(csrfToken);
            }
            
            $.ajax({
                url: "index.php?controller=Settings&action=index",
                type: "POST",
                dataType: "json",
                data: formData,
                success: function (response) {
                    if (response.success) {
                        alert(response.message);
                        // Update initial values after successful save
                        inputs.each(function () {
                            initialValues[$(this).attr("id")] = $(this).val();
                        });
                        saveBtn.prop("disabled", true);
                        undoBtn.prop("disabled", true);
                    } else {
                        alert("Failed to update settings.");
                    }
                },
                error: function () {
                    alert("An error occurred. Please try again.");
                }
            });
        });
    });
}(jQuery));
