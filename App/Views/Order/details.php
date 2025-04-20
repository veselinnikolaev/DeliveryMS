<div class="container-scroller">
    <div class="row">
        <div class="col-sm-12">
            <div class="home-tab">
                <div class="d-sm-flex align-items-center justify-content-between border-bottom">
                    <ul class="nav nav-tabs" role="tablist">
                        <li class="nav-item">
                            <a class="nav-link" href="<?php echo INSTALL_URL; ?>?controller=Order&action=list">
                                <i class="mdi mdi-format-list-bulleted me-1"></i>Order List
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link active ps-0" href="#">
                                <i class="mdi mdi-information-outline me-1"></i>Order Details
                            </a>
                        </li>
                    </ul>
                </div>

                <div class="card card-rounded mt-3">
                    <div class="card-body">
                        <div class="d-flex align-items-center mb-3">
                            <i class="mdi mdi-package-variant-closed text-primary me-2" style="font-size: 24px;"></i>
                            <h4 class="card-title mb-0">Order Details</h4>
                        </div>

                        <div class="row">
                            <div class="col-md-6">
                                <div class="info-item mb-3">
                                    <i class="mdi mdi-pound text-info me-2"></i>
                                    <strong>Order ID:</strong> <?php echo htmlspecialchars($tpl['order']['id']); ?>
                                </div>
                                <div class="info-item mb-3">
                                    <i class="mdi mdi-account text-success me-2"></i>
                                    <strong>Customer:</strong> <?php echo htmlspecialchars($tpl['customer']['name']); ?>
                                </div>
                                <div class="info-item mb-3">
                                    <i class="mdi mdi-map-marker text-danger me-2"></i>
                                    <strong>Address:</strong> <?php echo htmlspecialchars($tpl['order']['address']); ?>
                                </div>
                                <div class="info-item mb-3">
                                    <i class="mdi mdi-flag text-warning me-2"></i>
                                    <strong>Country:</strong> <?php echo htmlspecialchars($tpl['order']['country']); ?>
                                </div>
                                <div class="info-item mb-3">
                                    <i class="mdi mdi-map text-info me-2"></i>
                                    <strong>Region:</strong> <?php echo htmlspecialchars($tpl['order']['region']); ?>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="info-item mb-3">
                                    <i class="mdi mdi-barcode text-primary me-2"></i>
                                    <strong>Tracking Number:</strong> <?php echo htmlspecialchars($tpl['order']['tracking_number']); ?>
                                </div>
                                <div class="info-item mb-3">
                                    <i class="mdi mdi-truck-delivery text-success me-2"></i>
                                    <strong>Courier:</strong> <?php echo htmlspecialchars($tpl['courier']['name']); ?>
                                </div>
                                <div class="info-item mb-3">
                                    <i class="mdi mdi-calendar-clock text-warning me-2"></i>
                                    <strong>Delivery Date:</strong> 
                                    <?php
                                    if (!empty($tpl['order']['delivery_date'])) {
                                        echo htmlspecialchars(date($tpl['date_format'], $tpl['order']['delivery_date']));
                                    } else {
                                        echo '<span class="text-muted">Not scheduled</span>';
                                    }
                                    ?>
                                </div>
                                <div class="info-item mb-3">
                                    <i class="mdi mdi-checkbox-marked-circle text-info me-2"></i>
                                    <strong>Status:</strong> 
                                    <span>
                                        <?php echo htmlspecialchars(Utility::$order_status[$tpl['order']['status']]); ?>
                                    </span>
                                </div>
                                <div class="info-item mb-3">
                                    <i class="mdi mdi-cash-multiple text-success me-2"></i>
                                    <strong>Total Price:</strong> <?php echo Utility::getDisplayableAmount(htmlspecialchars(number_format($tpl['order']['total_amount'], 2))); ?>
                                </div>
                            </div>
                        </div>
                        <hr>
                        <div class="d-flex align-items-center mb-3">
                            <i class="mdi mdi-cart-outline text-primary me-2" style="font-size: 24px;"></i>
                            <h5 class="mb-0">Products</h5>
                        </div>
                        <div class="table-responsive">
                            <table class="table table-hover" id="order-products-table-id">
                                <thead>
                                    <tr>
                                        <th>Product Name</th>
                                        <th>Quantity</th>
                                        <th>Price</th>
                                        <th>Subtotal</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($tpl['products'] as $product) { ?>
                                        <tr>
                                            <td>
                                                <div class="d-flex align-items-center">
                                                    <i class="mdi mdi-package text-primary me-2"></i>
                                                    <?php echo htmlspecialchars($product['name']); ?>
                                                </div>
                                            </td>
                                            <td><?php echo htmlspecialchars($product['quantity']); ?></td>
                                            <td><?php echo Utility::getDisplayableAmount(htmlspecialchars(number_format($product['price'], 2))); ?></td>
                                            <td><?php echo Utility::getDisplayableAmount(htmlspecialchars(number_format($product['subtotal'], 2))); ?></td>
                                        </tr>
                                    <?php } ?>
                                </tbody>
                            </table>
                        </div>
                        <?php if ($tpl['order']['status'] == 'shipped' && !empty($tpl['order']['courier_id'])) { ?>
                            <div class="row mb-4 mt-4">
                                <div class="col-12">
                                    <div class="card card-dashboard">
                                        <div class="card-header bg-white">
                                            <div class="d-flex align-items-center">
                                                <i class="mdi mdi-truck-fast text-primary me-2" style="font-size: 24px;"></i>
                                                <h5 class="card-title mb-0">Delivery Tracking</h5>
                                            </div>
                                        </div>
                                        <div class="card-body">
                                            <div id="deliveryMap" style="height: 400px; border-radius: 10px;"></div>
                                            <div class="mt-4">
                                                <div class="row">
                                                    <div class="col-md-6">
                                                        <div class="d-flex align-items-center p-3 bg-light rounded">
                                                            <i class="mdi mdi-clock-outline text-primary me-3" style="font-size: 24px;"></i>
                                                            <div>
                                                                <p class="mb-1 text-muted">Estimated Delivery</p>
                                                                <h6 class="mb-0" id="estimatedTime">Loading...</h6>
                                                            </div>
                                                        </div>
                                                    </div>
                                                    <div class="col-md-6">
                                                        <div class="d-flex align-items-center p-3 bg-light rounded">
                                                            <i class="mdi mdi-crosshairs-gps text-success me-3" style="font-size: 24px;"></i>
                                                            <div>
                                                                <p class="mb-1 text-muted">Courier Location</p>
                                                                <h6 class="mb-0" id="courierStatus">Loading...</h6>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        <?php } ?>
                        <div class="mt-4">
                            <a href="<?php echo $_SESSION['previous_url']; ?>" class="btn btn-outline-primary">
                                <i class="mdi mdi-arrow-left me-1"></i>Back
                            </a>
                            <?php if ($_SESSION['user']['id'] === $tpl['order']['user_id'] && in_array($tpl['order']['status'], ['pending', 'cancelled'])) { ?>
                                <a href="<?php echo INSTALL_URL; ?>?controller=Order&action=pay&order_id=<?php echo $tpl['order']['id']; ?>" 
                                   class="btn btn-success">
                                    <i class="mdi mdi-credit-card me-1"></i>Pay
                                </a>
                            <?php } ?>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>