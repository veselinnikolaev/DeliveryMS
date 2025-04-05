(function ($) {
    $(function () {
        function setupBulkOrderStatusChangeButtons() {
            // Track selected orders
            $(document).on("change", ".order-checkbox", function () {
                let selectedCount = $(".order-checkbox:checked").length;
                $(".selected-count-orders").text(selectedCount);

                if (selectedCount > 0) {
                    $(".bulk-change-orders-btn").removeClass("d-none");
                } else {
                    $(".bulk-change-orders-btn").addClass("d-none");
                }
            });

            // Select/Deselect all orders
            $(document).on("change", "#select-all-orders", function () {
                $(".order-checkbox:not(:disabled)").prop("checked", this.checked).trigger("change");
            });

            // Show modal and store selected IDs for delivered
            $(document).on("click", "#bulk-change-orders-btn", function () {
                let selectedIds = $(".order-checkbox:checked").map(function () {
                    return $(this).data("id");
                }).get();

                // Store IDs for delivering
                $("#confirmChangeStatusBtn").attr("data-ids", selectedIds.join(","))
                        .attr('data-status', $(this).data("status"));
                $("#changeStatusModal").modal("show");
            });

            // Confirm marking as delivered and send AJAX request
            $(document).on("click", "#confirmChangeStatusBtn", function () {
                let selectedIds = $(this).attr("data-ids").split(",");

                $.ajax({
                    url: 'index.php?controller=Order&action=changeStatus',
                    type: "POST",
                    data: {ids: selectedIds, status: $(this).data("status")},
                    success: function (res) {
                        $("#order-table-id").html(res);
                        $("#changeStatusModal").modal("hide");

                        // Uncheck all checkboxes
                        $(".order-checkbox").prop("checked", false);

                        // Reset selected count
                        $("#selected-count-orders").text(0);

                        // Hide bulk action buttons
                        $("#bulk-change-orders-btn").addClass("d-none");

                        // Refresh table (if needed)
                        $("#order-table-id").dataTable({
                            order: [[1, "asc"]],
                            columnDefs: [{orderable: false, targets: [0, -1]}]
                        });
                    }
                });
            });
        }

        // Initialize the bulk actions for orders
        setupBulkOrderStatusChangeButtons();
    });
})(jQuery);
