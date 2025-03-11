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
    </body>
</html>
