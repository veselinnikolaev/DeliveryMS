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
            $.ajax({
                url: "index.php?controller=Settings&action=index",
                type: "POST",
                dataType: "json",
                data: form.serialize(),
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
