<div class="row">
    <div class="col-12">
        <div class="card">
            <div class="card-header bg-primary text-white">
                <h5 class="mb-0">Delivery Tracking Control Panel</h5>
            </div>
            <div class="card-body">
                <div class="tracking-status-panel mb-4">
                    <h6>Tracking Status</h6>
                    <div class="d-flex align-items-center gap-3">
                        <div id="tracking-indicator" class="badge bg-secondary">Not tracking</div>
                        <button id="toggle-tracking" class="btn btn-primary">
                            <i class="mdi mdi-crosshairs-gps me-1"></i>
                            Start Tracking
                        </button>
                    </div>
                </div>

                <div class="active-deliveries mb-4">
                    <h6>Active Deliveries</h6>
                    <div class="table-responsive">
                        <table class="table table-hover">
                            <thead>
                                <tr>
                                    <th>Order ID</th>
                                    <th>Delivery Address</th>
                                    <th>Status</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($tpl['active_orders'] as $order): ?>
                                <tr>
                                    <td>#<?php echo $order['id']; ?></td>
                                    <td><?php echo htmlspecialchars($order['address'] . ', ' . $order['region'] . ', ' . $order['country']); ?></td>
                                    <td>
                                        <span class="badge bg-info">In Transit</span>
                                    </td>
                                    <td>
                                        <a href="<?php INSTALL_URL; ?>?controller=Order&action=details&id=<?php echo $order['id']; ?>" 
                                           class="btn btn-sm btn-outline-primary">
                                            View Details
                                        </a>
                                    </td>
                                </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                </div>

                <div id="tracking-map" style="height: 400px; border-radius: 10px;"></div>
            </div>
        </div>
    </div>
</div>