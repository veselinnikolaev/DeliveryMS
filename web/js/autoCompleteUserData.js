(function ($) {
    $(function () {
        $(document).on('change', '#userId', function () {
            const selectedOption = $(this).find('option:selected');

            // Only proceed if an actual user is selected
            if ($(this).val() !== '') {
                // Get data from data attributes
                const address = selectedOption.data('address') || '';
                const country = selectedOption.data('country') || '';
                const region = selectedOption.data('region') || '';

                // Set the form field values
                $('#address').val(address);
                $('#country').val(country);
                $('#region').val(region);
            } else {
                // Clear the fields if no user is selected
                $('#address').val('');
                $('#country').val('');
                $('#region').val('');
            }
        });
    });
}(jQuery));


