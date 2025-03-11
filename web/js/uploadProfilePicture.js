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
