(function ($) {
    $(function () {
        const form = $("#settings-form");
        const inputs = form.find(".settings-input");
        const saveBtn = $("#save-btn");
        const undoBtn = $("#undo-btn");
        const originalValues = {};

        // Store original values
        inputs.each(function () {
            originalValues[this.name] = $(this).val();
        });

        // Enable buttons on input change
        inputs.on("input", function () {
            saveBtn.prop("disabled", false);
            undoBtn.prop("disabled", false);
        });

        // Undo functionality
        undoBtn.on("click", function () {
            inputs.each(function () {
                $(this).val(originalValues[this.name]);
            });
            saveBtn.prop("disabled", true);
            undoBtn.prop("disabled", true);
        });

        // Handle form submission via AJAX
        form.on("submit", function (e) {
            e.preventDefault();

            $.ajax({
                url: form.attr("action"),
                type: "POST",
                dataType: "json",
                data: form.serialize(),
                success: function (data) {
                    if (data.success) {
                        // Update original values to new values
                        inputs.each(function () {
                            originalValues[this.name] = $(this).val();
                        });
                        saveBtn.prop("disabled", true);
                        undoBtn.prop("disabled", true);
                    } else {
                        alert("Failed to update settings.");
                    }
                },
                error: function (error) {
                    console.error("Error:", error);
                },
            });
        });
    });
})(jQuery);