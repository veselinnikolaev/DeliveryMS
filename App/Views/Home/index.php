<?php
// Extract data from the template array
$isLoggedIn = !empty($_SESSION['user']);
$currency = $isLoggedIn ? $tpl['currency'] : '';
$user_role = $isLoggedIn ? $tpl['user_role'] : '';
$notifications = $isLoggedIn ? $tpl['notifications'] : [];
?>
<div class="container-fluid">
    <div class="row">
        <!-- Main content -->
        <main class="col-md-9 ms-sm-auto col-lg-10 px-md-4">
            <?php if (!$isLoggedIn): ?>
                <!-- Non-logged in welcome page -->
                <div class="container py-5">
                    <div class="row justify-content-center">
                        <div class="col-md-10 text-center">
                            <img src="web/assets/images/logo.svg" alt="Logo" class="img-fluid mb-4"
                                style="max-width: 150px;">
                            <h1 class="display-4 fw-bold mb-4">Welcome to Our Platform</h1>
                            <p class="lead mb-5">Manage your orders, track deliveries, and shop with ease. Please log in to
                                access your dashboard.</p>

                            <div class="row justify-content-center">
                                <div class="col-md-8">
                                    <div class="card shadow-lg border-0">
                                        <div class="card-body p-5">
                                            <div class="row">
                                                <div class="col-md-6 mb-4 mb-md-0">
                                                    <div class="d-flex flex-column h-100">
                                                        <h3 class="h4 mb-3">Already have an account?</h3>
                                                        <p class="text-muted mb-4">Sign in to access your dashboard, view
                                                            orders, and more.</p>
                                                        <a href="<?= INSTALL_URL ?>?controller=Auth&action=login"
                                                            class="btn btn-primary btn-lg mt-auto">
                                                            <i class="fas fa-sign-in-alt me-2"></i> Login
                                                        </a>
                                                    </div>
                                                </div>
                                                <div class="col-md-6">
                                                    <div class="d-flex flex-column h-100">
                                                        <h3 class="h4 mb-3">New to our platform?</h3>
                                                        <p class="text-muted mb-4">Create an account to start shopping,
                                                            place orders, and more.</p>
                                                        <a href="<?= INSTALL_URL ?>?controller=Auth&action=register"
                                                            class="btn btn-outline-primary btn-lg mt-auto">
                                                            <i class="fas fa-user-plus me-2"></i> Register
                                                        </a>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <div class="row mt-5">
                                <div class="col-md-4 mb-4">
                                    <div class="card card-dashboard h-100">
                                        <div class="card-body text-center p-4">
                                            <div class="stat-icon bg-light-primary mx-auto mb-3">
                                                <i class="fas fa-shopping-cart"></i>
                                            </div>
                                            <h4>Shop Online</h4>
                                            <p class="text-muted">Browse our extensive catalog of products and shop from the
                                                comfort of your home.</p>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-4 mb-4">
                                    <div class="card card-dashboard h-100">
                                        <div class="card-body text-center p-4">
                                            <div class="stat-icon bg-light-success mx-auto mb-3">
                                                <i class="fas fa-truck"></i>
                                            </div>
                                            <h4>Fast Delivery</h4>
                                            <p class="text-muted">Track your orders in real-time and get them delivered
                                                right to your doorstep.</p>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-4 mb-4">
                                    <div class="card card-dashboard h-100">
                                        <div class="card-body text-center p-4">
                                            <div class="stat-icon bg-light-warning mx-auto mb-3">
                                                <i class="fas fa-headset"></i>
                                            </div>
                                            <h4>24/7 Support</h4>
                                            <p class="text-muted">Our customer support team is always ready to assist you
                                                with any queries.</p>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            <?php else: ?>
                <div
                    class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
                    <h1 class="h2">Dashboard</h1>
                    <div class="btn-toolbar mb-2 mb-md-0">
                        <div class="dropdown">
                            <button class="btn btn-outline-secondary dropdown-toggle position-relative" type="button"
                                id="notificationDropdown" data-bs-toggle="dropdown" aria-expanded="false">
                                <i class="fas fa-bell"></i>
                                <?php if (count($notifications) > 0): ?>
                                    <span
                                        class="position-absolute top-0 start-100 translate-middle badge rounded-pill bg-danger notification-badge">
                                        <?= count($notifications) ?>
                                    </span>
                                <?php endif; ?>
                            </button>
                            <ul class="dropdown-menu dropdown-menu-end" aria-labelledby="notificationDropdown">
                                <?php if (count($notifications) > 0): ?>
                                    <?php foreach ($notifications as $notification): ?>
                                        <li><a class="dropdown-item" href="#">
                                                <?= htmlspecialchars($notification['message']) ?>
                                            </a></li>
                                    <?php endforeach; ?>
                                <?php else: ?>
                                    <li><a class="dropdown-item" href="#">No new notifications</a></li>
                                <?php endif; ?>
                            </ul>
                        </div>
                    </div>
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
                                                            <?= date('M d, Y', $delivery['delivery_date']) ?>
                                                        </td>
                                                        <td>
                                                            <a href="<?= INSTALL_URL ?>?controller=Order&action=view&id=<?= $delivery['id'] ?>"
                                                                class="btn btn-sm btn-primary">
                                                                <i class="fas fa-eye"></i> View
                                                            </a>
                                                            <?php if ($delivery['status'] == 'shipped'): ?>
                                                                <a href="<?= INSTALL_URL ?>?controller=Order&action=changeStatus&ids=<?= $delivery['id'] ?>&status=delivered"
                                                                    class="btn btn-sm btn-success">
                                                                    <i class="fas fa-check"></i> Mark Delivered
                                                                </a>
                                                            <?php endif; ?>
                                                        </td>
                                                    </tr>
                                                <?php endforeach; ?>
                                            </tbody>
                                        </table>
                                    </div>
                                </div>
                                <div class="card-footer bg-white text-center">
                                    <a href="<?= INSTALL_URL ?>?controller=Order&action=list" class="btn btn-sm btn-primary">View All
                                        Deliveries</a>
                                </div>
                            </div>
                        </div>
                    </div>

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
                                                            <?= date('M d, Y', $order['created_at']) ?>
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
                                                            <a href="<?= INSTALL_URL ?>?controller=Order&action=view&id=<?= $order['id'] ?>"
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
                                    <a href="<?= INSTALL_URL ?>?controller=Order&action=myOrders"
                                        class="btn btn-sm btn-primary">View All Orders</a>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="row mb-4">
                        <div class="col-12">
                            <div class="card card-dashboard">
                                <div class="card-header bg-white">
                                    <h5 class="card-title mb-0">Quick Actions</h5>
                                </div>
                                <div class="card-body">
                                    <div class="row">
                                        <div class="col-md-4 mb-3">
                                            <a href="<?= INSTALL_URL ?>?controller=Product&action=shop"
                                                class="btn btn-primary w-100">
                                                <i class="fas fa-shopping-cart me-2"></i> Shop Now
                                            </a>
                                        </div>
                                        <div class="col-md-4 mb-3">
                                            <a href="<?= INSTALL_URL ?>?controller=Cart" class="btn btn-info w-100">
                                                <i class="fas fa-shopping-basket me-2"></i> View Cart
                                            </a>
                                        </div>
                                        <div class="col-md-4 mb-3">
                                            <a href="<?= INSTALL_URL ?>?controller=User&action=profile" class="btn btn-secondary w-100">
                                                <i class="fas fa-user-edit me-2"></i> Edit Profile
                                            </a>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                <?php endif; ?>
            <?php endif; ?>
        </main>
    </div>
</div>

<!-- Bootstrap Bundle with Popper -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<!-- Chart.js -->
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

<?php if ($user_role == 'admin' || $user_role == 'root'): ?>
    <script>
        // Sales Chart
        const salesData = <?= json_encode($tpl['sales_data']) ?>;
        const ctx = document.getElementById('salesChart').getContext('2d');

        new Chart(ctx, {
            type: 'line',
            data: {
                labels: salesData.map(item => item.date),
                datasets: [{
                    label: 'Sales',
                    data: salesData.map(item => item.total),
                    backgroundColor: 'rgba(13, 110, 253, 0.1)',
                    borderColor: '#0d6efd',
                    borderWidth: 2,
                    tension: 0.3,
                    fill: true,
                    pointBackgroundColor: '#0d6efd',
                    pointBorderColor: '#fff',
                    pointBorderWidth: 2,
                    pointRadius: 4,
                    pointHoverRadius: 6
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        display: false
                    },
                    tooltip: {
                        callbacks: {
                            label: function (context) {
                                return `${context.dataset.label}: ${context.parsed.y} ${<?= json_encode($currency) ?>}`;
                            }
                        }
                    }
                },
                scales: {
                    y: {
                        beginAtZero: true,
                        grid: {
                            drawBorder: false
                        },
                        ticks: {
                            callback: function (value) {
                                return value + ' ' + <?= json_encode($currency) ?>;
                            }
                        }
                    },
                    x: {
                        grid: {
                            display: false
                        }
                    }
                }
            }
        });
    </script>
<?php endif; ?>

<?php if ($user_role == 'courier'): ?>
    <script>
        // Placeholder for map integration
        // In a real application, you would integrate with Google Maps or another mapping service
        setTimeout(() => {
            document.getElementById('deliveryMap').innerHTML = `
                        <div class="text-center">
                            <i class="fas fa-map-marked-alt" style="font-size: 60px; color: #6c757d;"></i>
                            <p class="mt-3">Map integration would be implemented here with actual delivery locations.</p>
                            <p class="text-muted">For a production environment, integrate with Google Maps API or similar service.</p>
                        </div>
                    `;
        }, 1000);
    </script>
<?php endif; ?>