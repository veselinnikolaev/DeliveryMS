<?php
// Extract data from the template array
$isLoggedIn = !empty($_SESSION['user']);
$currency = $isLoggedIn ? $tpl['currency'] : '';
$user_role = $isLoggedIn ? $tpl['user_role'] : '';
$notifications = $isLoggedIn ? $tpl['notifications'] : [];
?>
<div class="container-scroller">
    <div class="main-panel">
        <div class="content-wrapper">
            <?php if (!$isLoggedIn): ?>
                <!-- Non-logged in welcome page - full width with max-width constraint -->
                <div class="container py-5 my-5">
                    <div class="row justify-content-center">
                        <div class="col-lg-12">
                            <!-- Login/Register section -->
                            <div class="row justify-content-center mb-5">
                                <div class="col-md-10 col-lg-8">
                                    <div class="card card-dashboard shadow-sm rounded-4">
                                        <div class="card-body p-4">
                                            <div class="row">
                                                <div class="col-md-6 mb-4 mb-md-0 border-end">
                                                    <div class="py-2">
                                                        <h3 class="h4 mb-3">Have an account?</h3>
                                                        <p class="text-muted mb-4">Sign in to access your dashboard, view orders, and manage payments.</p>
                                                        <a href="<?= INSTALL_URL ?>?controller=Auth&action=login"
                                                           class="btn btn-primary w-100">
                                                            <i class="mdi mdi-login-variant me-2"></i> Log in
                                                        </a>
                                                    </div>
                                                </div>
                                                <div class="col-md-6">
                                                    <div class="py-2">
                                                        <h3 class="h4 mb-3">New to our platform?</h3>
                                                        <p class="text-muted mb-4">Create an account to start managing your orders and tracking deliveries.</p>
                                                        <a href="<?= INSTALL_URL ?>?controller=Auth&action=register"
                                                           class="btn btn-outline-primary w-100">
                                                            <i class="mdi mdi-account-plus me-2"></i> Register
                                                        </a>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- Feature cards -->
                            <div class="row justify-content-center g-4">
                                <div class="col-md-4 mb-4">
                                    <div class="card card-dashboard h-100">
                                        <div class="card-body text-center p-4">
                                            <div class="stat-icon bg-light-primary mx-auto mb-3">
                                                <i class="mdi mdi-clipboard-text-outline"></i>
                                            </div>
                                            <h4 class="mb-3">Manage Orders</h4>
                                            <p class="text-muted">View your order history, check status updates, and manage payments all in one place.</p>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-4 mb-4">
                                    <div class="card card-dashboard h-100">
                                        <div class="card-body text-center p-4">
                                            <div class="stat-icon bg-light-success mx-auto mb-3">
                                                <i class="mdi mdi-truck-delivery"></i>
                                            </div>
                                            <h4 class="mb-3">Track Deliveries</h4>
                                            <p class="text-muted">Follow your deliveries in real-time with accurate status updates from our courier team.</p>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-4 mb-4">
                                    <div class="card card-dashboard h-100">
                                        <div class="card-body text-center p-4">
                                            <div class="stat-icon bg-light-warning mx-auto mb-3">
                                                <i class="mdi mdi-headset"></i>
                                            </div>
                                            <h4 class="mb-3">24/7 Support</h4>
                                            <p class="text-muted">Our support team is always ready to assist you with any questions about your orders.</p>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            <?php else: ?>
                <!-- Logged-in user dashboard -->
                <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
                    <h1 class="h2">Dashboard</h1>
                </div>

                <?php if ($user_role == 'admin' || $user_role == 'root'): ?>
                    <!-- Admin Dashboard -->
                    <div class="row mb-4">
                        <div class="col-md-6 col-xl-3 mb-4">
                            <div class="card card-dashboard h-100">
                                <div class="card-body">
                                    <div class="d-flex align-items-center">
                                        <div class="stat-icon bg-light-primary me-3">
                                            <i class="fas fa-shopping-cart"></i>
                                        </div>
                                        <div>
                                            <h6 class="card-title mb-0">Total Orders</h6>
                                            <h2 class="mt-2 mb-0">
                                                <?= $tpl['total_orders'] ?>
                                            </h2>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-6 col-xl-3 mb-4">
                            <div class="card card-dashboard h-100">
                                <div class="card-body">
                                    <div class="d-flex align-items-center">
                                        <div class="stat-icon bg-light-warning me-3">
                                            <i class="fas fa-clock"></i>
                                        </div>
                                        <div>
                                            <h6 class="card-title mb-0">Pending Orders</h6>
                                            <h2 class="mt-2 mb-0">
                                                <?= $tpl['pending_orders'] ?>
                                            </h2>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-6 col-xl-3 mb-4">
                            <div class="card card-dashboard h-100">
                                <div class="card-body">
                                    <div class="d-flex align-items-center">
                                        <div class="stat-icon bg-light-success me-3">
                                            <i class="fas fa-check-circle"></i>
                                        </div>
                                        <div>
                                            <h6 class="card-title mb-0">Completed Orders</h6>
                                            <h2 class="mt-2 mb-0">
                                                <?= $tpl['completed_orders'] ?>
                                            </h2>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-6 col-xl-3 mb-4">
                            <div class="card card-dashboard h-100">
                                <div class="card-body">
                                    <div class="d-flex align-items-center">
                                        <div class="stat-icon bg-light-info me-3">
                                            <i class="fas fa-users"></i>
                                        </div>
                                        <div>
                                            <h6 class="card-title mb-0">Total Users</h6>
                                            <h2 class="mt-2 mb-0">
                                                <?= $tpl['total_users'] ?>
                                            </h2>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="row mb-4">
                        <div class="col-lg-8 mb-4">
                            <div class="card card-dashboard h-100">
                                <div class="card-header bg-white">
                                    <h5 class="card-title mb-0">Sales Overview (Last 30 Days)</h5>
                                </div>
                                <div class="card-body">
                                    <canvas id="salesChart" height="250"></canvas>
                                </div>
                            </div>
                        </div>
                        <div class="col-lg-4 mb-4">
                            <div class="card card-dashboard h-100">
                                <div class="card-header bg-white">
                                    <h5 class="card-title mb-0">Recent Orders</h5>
                                </div>
                                <div class="card-body p-0">
                                    <div class="table-responsive">
                                        <table class="table table-hover mb-0">
                                            <thead>
                                                <tr>
                                                    <th>ID</th>
                                                    <th>Customer</th>
                                                    <th>Amount</th>
                                                    <th>Status</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                <?php foreach ($tpl['recent_orders'] as $order): ?>
                                                    <tr>
                                                        <td>#
                                                            <?= $order['id'] ?>
                                                        </td>
                                                        <td>
                                                            <?= htmlspecialchars($order['customer_name']) ?>
                                                        </td>
                                                        <td>
                                                            <?= $order['formatted_total'] ?>
                                                        </td>
                                                        <td>
                                                            <?php
                                                            $statusClass = '';
                                                            switch ($order['status']) {
                                                                case 'pending':
                                                                    $statusClass = 'bg-warning text-dark';
                                                                    break;
                                                                case 'processing':
                                                                    $statusClass = 'bg-info text-white';
                                                                    break;
                                                                case 'shipped':
                                                                    $statusClass = 'bg-primary text-white';
                                                                    break;
                                                                case 'delivered':
                                                                    $statusClass = 'bg-success text-white';
                                                                    break;
                                                                case 'completed':
                                                                    $statusClass = 'bg-success text-white';
                                                                    break;
                                                                case 'cancelled':
                                                                    $statusClass = 'bg-danger text-white';
                                                                    break;
                                                            }
                                                            ?>
                                                            <span class="status-badge <?= $statusClass ?>">
                                                                <?= ucfirst($order['status']) ?>
                                                            </span>
                                                        </td>
                                                    </tr>
                                                <?php endforeach; ?>
                                            </tbody>
                                        </table>
                                    </div>
                                </div>
                                <div class="card-footer bg-white text-center">
                                    <a href="<?= INSTALL_URL ?>?controller=Order&action=list" class="btn btn-sm btn-primary">View All
                                        Orders</a>
                                </div>
                            </div>
                        </div>
                    </div>

                <?php elseif ($user_role == 'courier'): ?>
                    <!-- Courier Dashboard -->
                    <div class="row mb-4">
                        <div class="col-md-4 mb-4">
                            <div class="card card-dashboard h-100">
                                <div class="card-body">
                                    <div class="d-flex align-items-center">
                                        <div class="stat-icon bg-light-primary me-3">
                                            <i class="fas fa-truck"></i>
                                        </div>
                                        <div>
                                            <h6 class="card-title mb-0">Assigned Orders</h6>
                                            <h2 class="mt-2 mb-0">
                                                <?= $tpl['assigned_orders'] ?>
                                            </h2>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-4 mb-4">
                            <div class="card card-dashboard h-100">
                                <div class="card-body">
                                    <div class="d-flex align-items-center">
                                        <div class="stat-icon bg-light-success me-3">
                                            <i class="fas fa-check-circle"></i>
                                        </div>
                                        <div>
                                            <h6 class="card-title mb-0">Delivered Orders</h6>
                                            <h2 class="mt-2 mb-0">
                                                <?= $tpl['delivered_orders'] ?>
                                            </h2>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-4 mb-4">
                            <div class="card card-dashboard h-100">
                                <div class="card-body">
                                    <div class="d-flex align-items-center">
                                        <div class="stat-icon bg-light-warning me-3">
                                            <i class="fas fa-hourglass-half"></i>
                                        </div>
                                        <div>
                                            <h6 class="card-title mb-0">Pending Deliveries</h6>
                                            <h2 class="mt-2 mb-0">
                                                <?= $tpl['pending_deliveries'] ?>
                                            </h2>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="row mb-4">
                        <div class="col-12">
                            <div class="card card-dashboard">
                                <div class="card-header bg-white">
                                    <h5 class="card-title mb-0">Recent Deliveries</h5>
                                </div>
                                <div class="card-body p-0">
                                    <div class="table-responsive">
                                        <table class="table table-hover mb-0">
                                            <thead>
                                                <tr>
                                                    <th>Order ID</th>
                                                    <th>Customer</th>
                                                    <th>Address</th>
                                                    <th>Delivery Date</th>
                                                    <th>Actions</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                <?php foreach ($tpl['recent_deliveries'] as $delivery): ?>
                                                    <tr>
                                                        <td>#
                                                            <?= $delivery['id'] ?>
                                                        </td>
                                                        <td>
                                                            <?= htmlspecialchars($delivery['customer_name']) ?>
                                                        </td>
                                                        <td title="<?= htmlspecialchars($delivery['address']) ?>"><?= htmlspecialchars($delivery['address_short']) ?></td>
                                                        <td>
                                                            <?= date($tpl['date_format'], $delivery['delivery_date']) ?>
                                                        </td>
                                                        <td>
                                                            <a href="<?= INSTALL_URL ?>?controller=Order&action=details&id=<?= $delivery['id'] ?>"
                                                               class="btn btn-sm btn-primary">
                                                                <i class="fas fa-eye"></i> View
                                                            </a>
                                                            <!--
                                                                <?php if ($delivery['status'] == 'shipped'): ?>
                                                                <a href="<?= INSTALL_URL ?>?controller=Order&action=changeStatus&ids=<?= $delivery['id'] ?>&status=delivered"
                                                                   class="btn btn-sm btn-success">
                                                                    <i class="fas fa-check"></i> Mark Delivered
                                                                </a>
                                                            <?php endif; ?>
                                                            -->
                                                        </td>
                                                    </tr>
                                                <?php endforeach; ?>
                                            </tbody>
                                        </table>
                                    </div>
                                </div>
                                <div class="card-footer bg-white text-center">
                                    <a href="<?= INSTALL_URL ?>?controller=Order&action=list&courier_id=<?= $_SESSION['user']['id'] ?>" class="btn btn-sm btn-primary">View All
                                        Deliveries</a>
                                </div>
                            </div>
                        </div>
                    </div>
                    <!--
                                        <div class="row mb-4">
                                            <div class="col-12">
                                                <div class="card card-dashboard">
                                                    <div class="card-header bg-white">
                                                        <h5 class="card-title mb-0">Delivery Map</h5>
                                                    </div>
                                                    <div class="card-body">
                                                        <div id="deliveryMap"
                                                             style="height: 400px; background-color: #f8f9fa; border-radius: 10px; display: flex; align-items: center; justify-content: center;">
                                                            <p class="text-muted">Map loading... Please wait.</p>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                    -->
                <?php elseif ($user_role == 'user'): ?>
                    <!-- User Dashboard -->
                    <div class="row mb-4">
                        <div class="col-md-4 mb-4">
                            <div class="card card-dashboard h-100">
                                <div class="card-body">
                                    <div class="d-flex align-items-center">
                                        <div class="stat-icon bg-light-primary me-3">
                                            <i class="fas fa-shopping-bag"></i>
                                        </div>
                                        <div>
                                            <h6 class="card-title mb-0">My Orders</h6>
                                            <h2 class="mt-2 mb-0">
                                                <?= $tpl['my_orders'] ?>
                                            </h2>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-4 mb-4">
                            <div class="card card-dashboard h-100">
                                <div class="card-body">
                                    <div class="d-flex align-items-center">
                                        <div class="stat-icon bg-light-warning me-3">
                                            <i class="fas fa-clock"></i>
                                        </div>
                                        <div>
                                            <h6 class="card-title mb-0">Pending Orders</h6>
                                            <h2 class="mt-2 mb-0">
                                                <?= $tpl['pending_orders'] ?>
                                            </h2>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-4 mb-4">
                            <div class="card card-dashboard h-100">
                                <div class="card-body">
                                    <div class="d-flex align-items-center">
                                        <div class="stat-icon bg-light-success me-3">
                                            <i class="fas fa-check-circle"></i>
                                        </div>
                                        <div>
                                            <h6 class="card-title mb-0">Completed Orders</h6>
                                            <h2 class="mt-2 mb-0">
                                                <?= $tpl['completed_orders'] ?>
                                            </h2>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="row mb-4">
                        <div class="col-12">
                            <div class="card card-dashboard">
                                <div class="card-header bg-white">
                                    <h5 class="card-title mb-0">Recent Orders</h5>
                                </div>
                                <div class="card-body p-0">
                                    <div class="table-responsive">
                                        <table class="table table-hover mb-0">
                                            <thead>
                                                <tr>
                                                    <th>Order ID</th>
                                                    <th>Date</th>
                                                    <th>Amount</th>
                                                    <th>Status</th>
                                                    <th>Actions</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                <?php foreach ($tpl['recent_orders'] as $order): ?>
                                                    <tr>
                                                        <td>#
                                                            <?= $order['id'] ?>
                                                        </td>
                                                        <td>
                                                            <?= date($tpl['date_format'], $order['created_at']) ?>
                                                        </td>
                                                        <td>
                                                            <?= $order['formatted_total'] ?>
                                                        </td>
                                                        <td>
                                                            <?php
                                                            $statusClass = '';
                                                            switch ($order['status']) {
                                                                case 'pending':
                                                                    $statusClass = 'bg-warning text-dark';
                                                                    break;
                                                                case 'processing':
                                                                    $statusClass = 'bg-info text-white';
                                                                    break;
                                                                case 'shipped':
                                                                    $statusClass = 'bg-primary text-white';
                                                                    break;
                                                                case 'delivered':
                                                                    $statusClass = 'bg-success text-white';
                                                                    break;
                                                                case 'completed':
                                                                    $statusClass = 'bg-success text-white';
                                                                    break;
                                                                case 'cancelled':
                                                                    $statusClass = 'bg-danger text-white';
                                                                    break;
                                                            }
                                                            ?>
                                                            <span class="status-badge <?= $statusClass ?>">
                                                                <?= $order['status_text'] ?>
                                                            </span>
                                                        </td>
                                                        <td>
                                                            <a href="<?= INSTALL_URL ?>?controller=Order&action=details&id=<?= $order['id'] ?>"
                                                               class="btn btn-sm btn-primary">
                                                                <i class="fas fa-eye"></i> View
                                                            </a>
                                                        </td>
                                                    </tr>
                                                <?php endforeach; ?>
                                            </tbody>
                                        </table>
                                    </div>
                                </div>
                                <div class="card-footer bg-white text-center">
                                    <a href="<?= INSTALL_URL ?>?controller=Order&action=list&user_id=<?= $_SESSION['user']['id'] ?>"
                                       class="btn btn-sm btn-primary">View All Orders</a>
                                </div>
                            </div>
                        </div>
                    </div>
                <?php endif; ?>
            </div>
        <?php endif; ?>
    </div>
</div>