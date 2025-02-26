(function ($) {
    $(function () {
        $(document).on("click", '.password-toggle-icon', function () {
            // Get the target input field
            const targetId = $(this).attr('data-target');
            const inputField = $('#' + targetId);

            // Toggle input type
            if (inputField.attr('type') === 'password') {
                inputField.attr('type', 'text');
                $(this).removeClass('fa-eye').addClass('fa-eye-slash');
            } else {
                inputField.attr('type', 'password');
                $(this).removeClass('fa-eye-slash').addClass('fa-eye');
            }
        });
    });
}(jQuery));

