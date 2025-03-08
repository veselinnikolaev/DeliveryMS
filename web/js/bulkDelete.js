(function ($) {
    $(function () {
        function setupBulkDeleteButton(entity) {
            $(document).on("change", `.${entity}-checkbox`, function () {
                let selectedCount = $(`.${entity}-checkbox:checked`).length;
                $(`#selected-count-${entity}s`).text(selectedCount);

                if (selectedCount > 0) {
                    $(`#bulk-delete-${entity}s-btn`).removeClass("d-none");
                } else {
                    $(`#bulk-delete-${entity}s-btn`).addClass("d-none");
                }
            });

            // Select/Deselect all checkboxes
            $(document).on("change", `#select-all-${entity}s`, function () {
                $(`.${entity}-checkbox:not(:disabled)`).prop("checked", this.checked).trigger("change");
            });


            // Show modal and store selected IDs
            $(document).on("click", `#bulk-delete-${entity}s-btn`, function () {
                let selectedIds = $(`.${entity}-checkbox:checked`).map(function () {
                    return $(this).data("id");
                }).get();

                // Store IDs in delete button
                $(`#delete-btn-${entity}s-ids`).attr("data-ids", selectedIds.join(","));
                $(`#delete${entity.charAt(0).toUpperCase() + entity.slice(1)}s`).modal("show");
            });

            // Confirm deletion and send AJAX request
            $(document).on("click", `#delete-btn-${entity}s-ids`, function () {
                let selectedIds = $(this).attr("data-ids").split(",");

                $.ajax({
                    url: `index.php?controller=${entity.charAt(0).toUpperCase() + entity.slice(1)}&action=bulkDelete`,
                    type: "POST",
                    data: {ids: selectedIds},
                    success: function (res) {
                        $(`#container-${entity}-id`).html(res);
                        $(`#delete${entity.charAt(0).toUpperCase() + entity.slice(1)}s`).modal("hide");

                        // Uncheck all checkboxes
                        $(`.${entity}-checkbox`).prop("checked", false);

                        // Reset selected count
                        $(`#selected-count-${entity}s`).text(0);

                        // Hide bulk delete button
                        $(`#bulk-delete-${entity}s-btn`).addClass("d-none");

                        // Refresh table
                        $(`#${entity}-table-id`).dataTable({
                            order: [[1, "asc"]],
                            columnDefs: [{orderable: false, targets: [0, -1]}]
                        });
                    }
                });
            });
        }
        ['courier', 'product', 'user', 'order'].forEach(entity => setupBulkDeleteButton(entity));
    });
})(jQuery);
