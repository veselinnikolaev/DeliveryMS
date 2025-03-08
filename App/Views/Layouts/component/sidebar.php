<nav class="sidebar sidebar-offcanvas" id="sidebar">
    <ul class="nav">
        <?php if (isset($_SESSION['user'])): ?>
            <li class="nav-item active">
                <a class="nav-link" href="<?php echo INSTALL_URL; ?>">
                    <i class="mdi mdi-chart-line menu-icon"></i>
                    <span class="menu-title">Dashboard</span>
                </a>
            </li>
            <?php if (in_array($_SESSION['user']['role'], ['admin', 'root'])): ?>
                <li class="nav-item nav-category">Forms and Data</li>
                <li class="nav-item">
                    <a class="nav-link" data-bs-toggle="collapse" href="#orders" aria-expanded="false" aria-controls="orders">
                        <i class="menu-icon mdi mdi-cart-outline"></i>
                        <span class="menu-title">Orders</span>
                        <i class="menu-arrow"></i>
                    </a>
                    <div class="collapse" id="orders">
                        <ul class="nav flex-column sub-menu">
                            <li class="nav-item"><a class="nav-link" href="<?php INSTALL_URL; ?>?controller=Order&action=list">List Orders</a></li>
                            <li class="nav-item"><a class="nav-link" href="<?php INSTALL_URL; ?>?controller=Order&action=create">Create Order</a></li>
                        </ul>
                    </div>
                </li>
                <li class="nav-item">
                    <a class="nav-link" data-bs-toggle="collapse" href="#products" aria-expanded="false" aria-controls="products">
                        <i class="menu-icon mdi mdi-cube-outline"></i>
                        <span class="menu-title">Products</span>
                        <i class="menu-arrow"></i>
                    </a>
                    <div class="collapse" id="products">
                        <ul class="nav flex-column sub-menu">
                            <li class="nav-item"> <a class="nav-link" href="<?php INSTALL_URL; ?>?controller=Product&action=list">List Products</a></li>
                            <li class="nav-item"> <a class="nav-link" href="<?php INSTALL_URL; ?>?controller=Product&action=create">Create Product</a></li>
                        </ul>
                    </div>
                </li>
                <li class="nav-item">
                    <a class="nav-link" data-bs-toggle="collapse" href="#users" aria-expanded="false" aria-controls="users">
                        <i class="menu-icon mdi mdi-account-group"></i>
                        <span class="menu-title">Users</span>
                        <i class="menu-arrow"></i>
                    </a>
                    <div class="collapse" id="users">
                        <ul class="nav flex-column sub-menu">
                            <li class="nav-item"> <a class="nav-link" href="<?php INSTALL_URL; ?>?controller=User&action=list">List Users</a></li>
                            <li class="nav-item"> <a class="nav-link" href="<?php INSTALL_URL; ?>?controller=User&action=create">Create User</a></li>
                        </ul>
                    </div>
                </li>
                <li class="nav-item">
                    <a class="nav-link" data-bs-toggle="collapse" href="#couriers" aria-expanded="false" aria-controls="couriers">
                        <i class="menu-icon mdi mdi-truck"></i>
                        <span class="menu-title">Couriers</span>
                        <i class="menu-arrow"></i>
                    </a>
                    <div class="collapse" id="couriers">
                        <ul class="nav flex-column sub-menu">
                            <li class="nav-item"> <a class="nav-link" href="<?php INSTALL_URL; ?>?controller=Courier&action=list">List Couriers</a></li>
                            <li class="nav-item"> <a class="nav-link" href="<?php INSTALL_URL; ?>?controller=Courier&action=create">Create Courier</a></li>
                        </ul>
                    </div>
                </li>
                <li class="nav-item nav-category">Control</li>
                <li class="nav-item">
                    <a class="nav-link" href="<?php INSTALL_URL; ?>?controller=Settings&action=index">
                        <i class="menu-icon mdi mdi-cog spin-wheel"></i>
                        <span class="menu-title">Settings</span>
                    </a>
                </li>
            <?php else: ?>
                <li class="nav-item nav-category">Orders</li>
                <li class="nav-item">
                    <a class="nav-link" href="<?php INSTALL_URL; ?>?controller=Order&action=list&user_id=<?php echo $_SESSION['user']['id']; ?>">
                        <i class="menu-icon mdi mdi-clipboard-list"></i>
                        <span class="menu-title">My Orders</span>
                    </a>
                </li>
            <?php endif; ?>
        <?php endif; ?>
    </ul>
</nav>