<!DOCTYPE html>
<html>
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
        <title>DeliveryMS Admin Dashboard</title>

        <!-- ===== Core Vendor CSS (including Bootstrap likely) ===== -->
        <link rel="stylesheet" href="web/assets/vendors/css/vendor.bundle.base.css">

        <!-- ===== Plugin CSS ===== -->
        <link rel="stylesheet" href="web/assets/vendors/feather/feather.css">
        <link rel="stylesheet" href="web/assets/vendors/mdi/css/materialdesignicons.min.css">
        <link rel="stylesheet" href="web/assets/vendors/ti-icons/css/themify-icons.css">
        <link rel="stylesheet" href="web/assets/vendors/font-awesome/css/font-awesome.min.css"> <!-- Consider using the newer Font Awesome 6 below instead if redundant -->
        <link rel="stylesheet" href="web/assets/vendors/typicons/typicons.css">
        <link rel="stylesheet" href="web/assets/vendors/simple-line-icons/css/simple-line-icons.css">
        <link rel="stylesheet" href="web/assets/vendors/bootstrap-datepicker/bootstrap-datepicker.min.css">
        <link rel="stylesheet" href="web/assets/vendors/datatables.net-bs4/dataTables.bootstrap4.css">
        <link rel="stylesheet" type="text/css" href="web/assets/js/select.dataTables.min.css"> <!-- Often related to DataTables -->
        <link rel="stylesheet" type="text/css" href="web/assets/vendors/select2-bootstrap-theme/select2-bootstrap.min.css"> <!-- Often for styled selects -->

        <!-- ===== Leaflet CSS (Required for Map) ===== -->
        <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.3/dist/leaflet.css" />

        <!-- ===== Font Awesome 6 (if needed and not covered by vendor bundle) ===== -->
        <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css" rel="stylesheet">

        <!-- ===== Template Main CSS ===== -->
        <link rel="stylesheet" href="web/assets/css/style.css">

        <!-- ===== Your Custom CSS Overrides (Load Last) ===== -->
        <link rel="stylesheet" href="web/css/style.css">

        <!-- ===== Favicon ===== -->
        <link rel="shortcut icon" href="web/assets/images/favicon.png" />

        <!-- NOTE: Moved all JS to bottom, except potentially tiny config scripts -->
        <script src="web/js/initTrackingIndicator.js"></script> <!-- If this MUST run early, keep it here. Otherwise, move to bottom -->

    </head>

    <body> <!-- Added body tag for clarity -->
        <div class="container-scroller">
            <?php include 'App/Views/Layouts/component/top_nav.php'; ?>
            <div class="container-fluid page-body-wrapper pt-0 proBanner-padding-top">
                <?php include 'App/Views/Layouts/component/sidebar.php'; ?>
                <div class="main-panel">
                    <div class="content-wrapper">
                        <?php include $viewPath; ?>
                    </div>
                    <!-- Assuming footer might go here or outside main-panel depending on template -->
                </div>
            </div>
        </div>

        <!-- ============================================= -->
        <!--                 SCRIPT LOADING                -->
        <!-- ============================================= -->

        <!-- ===== Core Vendor JS (jQuery, Bootstrap, etc.) MUST BE FIRST ===== -->
        <script src="web/assets/vendors/js/vendor.bundle.base.js"></script>

        <!-- ===== Plugin JS (Depending on Core Vendor JS) ===== -->
        <script src="web/assets/vendors/bootstrap-datepicker/bootstrap-datepicker.min.js"></script>
        <script src="web/assets/vendors/chart.js/chart.umd.js"></script>
        <script src="web/assets/vendors/progressbar.js/progressbar.min.js"></script>
        <script src="web/assets/vendors/datatables.net/jquery.dataTables.js"></script> <!-- Core DataTables -->
        <script src="web/assets/vendors/datatables.net-bs4/dataTables.bootstrap4.js"></script> <!-- Bootstrap integration for DataTables -->
        <script src="web/assets/js/jquery.cookie.js" type="text/javascript"></script> <!-- Depends on jQuery -->

        <!-- ===== Leaflet JS (Core Map Library) ===== -->
        <script src="https://unpkg.com/leaflet@1.9.3/dist/leaflet.js"></script>

        <!-- ===== Template Specific JS ===== -->
        <script src="web/assets/js/off-canvas.js"></script>
        <script src="web/assets/js/template.js"></script> <!-- This might initialize plugins -->
        <script src="web/assets/js/settings.js"></script>
        <script src="web/assets/js/hoverable-collapse.js"></script>
        <script src="web/assets/js/todolist.js"></script>

        <!-- ===== Your Custom Application JS (Load Last) ===== -->
        <script src="web/assets/js/dashboard.js"></script> <!-- May initialize things or expect DOM ready -->
        <script src="web/js/tables.js"></script> <!-- Likely initializes DataTables -->
        <script src="web/js/priceCalculation.js"></script>
        <script src="web/js/deleteActions.js"></script>
        <script src="web/js/productRows.js"></script>
        <script src="web/js/settingsUpdate.js"></script>
        <script src="web/js/changeRole.js"></script>
        <script src="web/js/filter.js"></script>
        <script src="web/js/passwordEye.js"></script>
        <script src="web/js/skipMailConfig.js"></script>
        <script src="web/js/autoCompleteUserData.js"></script>
        <script src="web/js/bulkDelete.js"></script>
        <script src="web/js/uploadProfilePicture.js"></script>
        <script src="web/js/export.js"></script>
        <script src="web/js/print.js"></script>
        <script src="web/js/markNotificationAsSeen.js"></script>
        <script src="web/js/changeOrderStatus.js"></script>
        <script src="web/js/bulkStatusChange.js"></script>

        <!-- Inline script for chart data (Needs to be before chart.js if chart.js uses it immediately) -->
        <?php if (!empty($tpl['sales_data']) && !empty($currency)): // Added check for $currency too ?>
            <script>
                // Ensure these are defined globally before chart.js runs
                window.salesData = <?= json_encode($tpl['sales_data']) ?>;
                window.currency = <?= json_encode($currency) ?>;
            </script>
            <script src="web/js/chart.js"></script> <!-- Initialize charts -->
        <?php endif; ?>

        <!-- Your Map-Specific JS (Depends on Leaflet & jQuery) -->
        <script src="web/js/courierTracking.js"></script>
        <script src="web/js/trackingControl.js"></script>

        <!-- Consider moving initTrackingIndicator.js here if it doesn't absolutely need to be in <head> -->
        <!-- <script src="web/js/initTrackingIndicator.js"></script> -->

    </body>
</html>