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
        $(document).on('click', '.notification-item', function () {
            let notificationId = $(this).data("id");
            let countBadge = $(".count");

            $.ajax({
                url: "index.php?controller=Notification&action=markAsSeen",
                type: "POST",
                data: {
                    id: notificationId,
                    csrf_token: $('input[name="csrf_token"]').val() || $('meta[name="csrf_token"]').attr('content')
                },
                dataType: "json",
                success: function (response) {
                    if (response.status === "success") {
                        countBadge.text((i, val) => Math.max(0, val - 1)); // Decrease unread count
                    } else {
                        alert("Error occured!");
                    }
                },
                error: function () {
                    console.error("Failed to mark notification as seen.");
                }
            });
        });

        $(document).on('click', '.mark-seen', function () {
            let notificationId = $(this).data("id");
            let notificationItem = $(this).closest("li"); // Find the parent <li> element
            let countBadge = $(".count");

            $.ajax({
                url: "index.php?controller=Notification&action=markAsSeen",
                type: "POST",
                data: {
                    id: notificationId,
                    csrf_token: $('input[name="csrf_token"]').val() || $('meta[name="csrf_token"]').attr('content')
                },
                dataType: "json",
                success: function (response) {
                    if (response.status === "success") {
                        // Decrease unread count
                        countBadge.text((i, val) => Math.max(0, val - 1));

                        // Change the class to light (indicating it's seen)
                        notificationItem.removeClass("list-group-item-warning").addClass("list-group-item-light");

                        // Remove the "Mark as Seen" button
                        notificationItem.find(".mark-seen").remove();
                    } else {
                        alert("Error occurred!");
                    }
                },
                error: function () {
                    console.error("Failed to mark notification as seen.");
                }
            });
        });

        $(document).on('click', "#markAllSeen", function () {
            $.ajax({
                url: "index.php?controller=Notification&action=markAllSeen",
                type: "POST",
                data: {
                    csrf_token: $('input[name="csrf_token"]').val() || $('meta[name="csrf_token"]').attr('content')
                },
                dataType: "json",
                success: function (response) {
                    if (response.status === "success") {
                        // Update the UI
                        $(".list-group-item-warning").removeClass("list-group-item-warning").addClass("list-group-item-light");
                        $(".mark-seen").remove(); // Remove all "Mark as Seen" buttons
                    } else {
                        alert("Error occured!");
                    }
                },
                error: function (xhr, status, error) {
                    console.error("AJAX Error:", status, error);
                    alert("Something went wrong! Please try again.");
                }
            });
        });
    });
}(jQuery));
