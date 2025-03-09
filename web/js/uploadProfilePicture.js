(function ($) {
    $(function () {
        $(document).on('click', "#profileImageWrapper", function (e) {
            e.preventDefault();
            if (e.target !== $("#profilePicInput")[0]) {
                $("#profilePicInput").trigger('click');
            }
        });

        $(document).on('change', "#profilePicInput", function (e) {
            e.preventDefault();

            var file = this.files[0];
            if (file) {
                var formData = new FormData();
                formData.append("profile_picture", file);
                formData.append("user_id", $("#user_id").val());

                $.ajax({
                    url: "index.php?controller=User&action=uploadProfilePicture",
                    type: "POST",
                    data: formData,
                    dataType: "json",
                    success: function (response) {
                        if (response.status === "success") {
                            $("#profileImage").attr("src", response.photo_path);
                        } else {
                            alert("Error: " + response.message);
                        }
                    },
                    error: function () {
                        alert("An error occurred while uploading the image.");
                    }
                });
            }
        });
    });
}(jQuery));
