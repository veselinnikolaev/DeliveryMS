(function ($) {
    $(function () {
    let lastAppliedFilters = {};

    // Apply Filters
    $(document).on('click', '#apply-filters', function () {
        let filters = {
            name: $('#filter-name').val(),
            phone: $('#filter-phone').val(),
            email: $('#filter-email').val()
        };
        
        // Store the last applied filters
        lastAppliedFilters = { ...filters };

        $.ajax({
            url: 'index.php?controller=Courier&action=filter',
            type: 'POST',
            data: {
                name : filters.name,
                phone_number : filters.phone,
                email : filters.email
            },
            dataType: 'json',
            success: function (response) {
                $(`#container-courier-id`).html(response);
            }
        });
    });

    // Reset Filters
    $(document).on('click', '#reset-filters', function () {
        // Restore last applied filters
        $('#filter-name').val(lastAppliedFilters.name || '');
        $('#filter-phone').val(lastAppliedFilters.phone || '');
        $('#filter-email').val(lastAppliedFilters.email || '');
        
        // Trigger apply filters to restore previous results
        $('#apply-filters').trigger('click');
    });
});
}(jQuery));
