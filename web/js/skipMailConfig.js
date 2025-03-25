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
                    url: "index.php?controller=Install&action=step4", // Target step4
                    type: "POST",
                    data: {skip_mail: true}, // Sending a flag to indicate skipping
                    success: function (response) {
                        // Redirect to step4 after successful processing
                        window.location.href = "index.php?controller=Install&action=step5";
                    },
                    error: function () {
                        alert("An error occurred while skipping mail configuration.");
                    }
                });
            });
        }
        setupSkipButton();
    });
}(jQuery));
