<!DOCTYPE html>
<html>
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
        <title>DeliveryMS Admin Dashboard</title>
        <!-- plugins:css -->
        <link rel="stylesheet" href="web/assets/vendors/feather/feather.css">
        <link rel="stylesheet" href="web/assets/vendors/mdi/css/materialdesignicons.min.css">
        <link rel="stylesheet" href="web/assets/vendors/ti-icons/css/themify-icons.css">
        <link rel="stylesheet" href="web/assets/vendors/font-awesome/css/font-awesome.min.css">
        <link rel="stylesheet" href="web/assets/vendors/typicons/typicons.css">
        <link rel="stylesheet" href="web/assets/vendors/simple-line-icons/css/simple-line-icons.css">
        <link rel="stylesheet" href="web/assets/vendors/css/vendor.bundle.base.css">
        <link rel="stylesheet" href="web/assets/vendors/bootstrap-datepicker/bootstrap-datepicker.min.css">
        <!-- endinject -->
        <!-- Plugin css for this page -->
        <link rel="stylesheet" href="web/assets/vendors/datatables.net-bs4/dataTables.bootstrap4.css">
        <link rel="stylesheet" type="text/css" href="web/assets/js/select.dataTables.min.css">
        <link rel="stylesheet" type="text/css" href="web/assets/vendors/select2-bootstrap-theme/select2-bootstrap.min.css">
        <!-- End plugin css for this page -->
        <!-- inject:css -->
        <link rel="stylesheet" href="web/assets/css/style.css">
        <link rel="stylesheet" href="web/css/style.css">
        <!-- endinject -->
        <link rel="shortcut icon" href="web/assets/images/favicon.png" />
        <!-- Font Awesome -->
        <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css" rel="stylesheet">
        <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.3/dist/leaflet.css" />
    </head>
    <body>
        <div class="container-scroller">
            <?php
            include 'App/Views/Layouts/component/top_nav.php';
            ?>
            <div class="container-fluid page-body-wrapper pt-0 proBanner-padding-top">
                <?php
                include 'App/Views/Layouts/component/sidebar.php';
                ?>
                <div class="main-panel">
                    <div class="content-wrapper">
                        <?php
                        include $viewPath;
                        ?>
                    </div>
                </div>
            </div>
        </div>
        <script src="web/assets/vendors/js/vendor.bundle.base.js"></script>
        <script src="web/assets/vendors/bootstrap-datepicker/bootstrap-datepicker.min.js"></script>
        <!-- endinject -->
        <!-- Plugin js for this page -->
        <script src="web/assets/vendors/chart.js/chart.umd.js"></script>
        <script src="web/assets/vendors/progressbar.js/progressbar.min.js"></script>
        <!-- End plugin js for this page -->
        <!-- inject:js -->
        <script src="web/assets/js/off-canvas.js"></script>
        <script src="web/assets/js/template.js"></script>
        <script src="web/assets/js/settings.js"></script>
        <script src="web/assets/js/hoverable-collapse.js"></script>
        <script src="web/assets/js/todolist.js"></script>
        <!-- endinject -->
        <!-- Custom js for this page-->
        <script src="web/assets/js/jquery.cookie.js" type="text/javascript"></script>
        <script src="web/assets/js/dashboard.js"></script>

        <!-- Chart.js -->
        <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

        <script src="web/assets/vendors/datatables.net/jquery.dataTables.js"></script>
        <script src="web/assets/vendors/datatables.net-bs4/dataTables.bootstrap4.js"></script>
        <script src="web/js/tables.js"></script>
        <script src="web/js/datepicker.js"></script>
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
        <script src="web/js/orderTracking.js"></script>
        <script src="https://unpkg.com/leaflet@1.9.3/dist/leaflet.js"></script>
        <?php if ($_REQUEST['controller'] === 'Home' && $_REQUEST['action'] === 'index') { ?>
            <script src="web/js/chart.js"></script>
            <script>
                window.salesData = <?= json_encode($tpl['sales_data']) ?>;
                window.currency = <?= json_encode($currency) ?>;
            </script>
        <?php } ?>
        <?php if (!empty($tpl['order']) && $tpl['order']['status'] == 'shipped' && !empty($tpl['order']['courier_id'])) { ?>
            <script>
                window.orderTracking = new OrderTracking({
                    orderId: <?php echo json_encode($tpl['order']['id']); ?>,
                    courierId: <?php echo json_encode($tpl['order']['courier_id']); ?>,
                    deliveryAddress: <?php echo json_encode($tpl['order']['address']); ?>,
                    deliveryRegion: <?php echo json_encode($tpl['order']['region']); ?>,
                    deliveryCountry: <?php echo json_encode($tpl['order']['country']); ?>,
                    mapMarkers: {
                        courier: '<i class="mdi mdi-truck-delivery text-primary" style="font-size: 24px;"></i>',
                        destination: '<i class="mdi mdi-map-marker text-danger" style="font-size: 24px;"></i>'
                    }
                });
            </script>
        <?php } ?>
    </body>
</html>
