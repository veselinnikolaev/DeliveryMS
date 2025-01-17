<nav class="sidebar sidebar-offcanvas" id="sidebar">
    <ul class="nav">
        <li class="nav-item active">
            <a class="nav-link" href="index.html">
                <i class="mdi mdi-chart-line menu-icon"></i>
                <span class="menu-title">Dashboard</span>
            </a>
        </li>
        <li class="nav-item nav-category">Forms and Datas</li>
        <li class="nav-item">
            <a class="nav-link" data-bs-toggle="collapse" href="#orders" aria-expanded="false" aria-controls="orders">
                <i class="menu-icon mdi mdi-table"></i>
                <span class="menu-title">Orders</span>
                <i class="menu-arrow"></i>
            </a>
            <div class="collapse" id="orders">
                <ul class="nav flex-column sub-menu">
                    <li class="nav-item"><a class="nav-link" href="<?php INSTALL_URL; ?>?controller=Order&action=list">List Orders</a></li>
                </ul>
            </div>
            <div class="collapse" id="orders">
                <ul class="nav flex-column sub-menu">
                    <li class="nav-item"><a class="nav-link" href="<?php INSTALL_URL; ?>?controller=Order&action=create">Create Order</a></li>
                </ul>
            </div>
        </li>
        <li class="nav-item">
            <a class="nav-link" data-bs-toggle="collapse" href="#products" aria-expanded="false" aria-controls="products">
                <i class="menu-icon mdi mdi-table"></i>
                <span class="menu-title">Products</span>
                <i class="menu-arrow"></i>
            </a>
            <div class="collapse" id="products">
                <ul class="nav flex-column sub-menu">
                    <li class="nav-item"> <a class="nav-link" href="<?php INSTALL_URL; ?>?controller=Product&action=list">List Products</a></li>
                </ul>
            </div>
            <div class="collapse" id="products">
                <ul class="nav flex-column sub-menu">
                    <li class="nav-item"> <a class="nav-link" href="<?php INSTALL_URL; ?>?controller=Product&action=create">Create Product</a></li>
                </ul>
            </div>
        </li>
        <li class="nav-item">
            <a class="nav-link" data-bs-toggle="collapse" href="#users" aria-expanded="false" aria-controls="users">
                <i class="menu-icon mdi mdi-card-text-outline"></i>
                <span class="menu-title">Users</span>
                <i class="menu-arrow"></i>
            </a>
            <div class="collapse" id="users">
                <ul class="nav flex-column sub-menu">
                    <li class="nav-item"> <a class="nav-link" href="<?php INSTALL_URL; ?>?controller=User&action=list">List Users</a></li>
                </ul>
                <ul class="nav flex-column sub-menu">
                    <li class="nav-item"> <a class="nav-link" href="<?php INSTALL_URL; ?>?controller=User&action=create">Create User</a></li>
                </ul>
            </div>
        </li>
        <li class="nav-item">
            <a class="nav-link" data-bs-toggle="collapse" href="#couriers" aria-expanded="false" aria-controls="couriers">
                <i class="menu-icon mdi mdi-table"></i>
                <span class="menu-title">Couriers</span>
                <i class="menu-arrow"></i>
            </a>
            <div class="collapse" id="couriers">
                <ul class="nav flex-column sub-menu">
                    <li class="nav-item"> <a class="nav-link" href="<?php INSTALL_URL; ?>?controller=Courier&action=list">List Couriers</a></li>
                </ul>
            </div>
            <div class="collapse" id="couriers">
                <ul class="nav flex-column sub-menu">
                    <li class="nav-item"> <a class="nav-link" href="<?php INSTALL_URL; ?>?controller=Courier&action=create">Create Courier</a></li>
                </ul>
            </div>
        </li>
        <li class="nav-item nav-category">Control</li>
        <li class="nav-item">
            <a class="nav-link" href="#settings">
                <i class="menu-icon mdi mdi-card-text-outline"></i>
                <span class="menu-title">Settings</span>
            </a>
        </li>
    </ul>
</nav>