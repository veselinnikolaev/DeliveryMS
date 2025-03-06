<div class="container-scroller">
    <div class="row">
        <div class="col-sm-12">
            <div class="home-tab">
                <div class="d-sm-flex align-items-center justify-content-between border-bottom">
                    <ul class="nav nav-tabs" role="tablist">
                        <li class="nav-item">
                            <a class="nav-link" href="<?php echo INSTALL_URL; ?>?controller=Order&action=list">Order List</a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link active ps-0" href="#">Order Details</a>
                        </li>
                    </ul>
                </div>
                <div class="card card-rounded mt-3">
                    <div class="card-body">
                        <h4 class="card-title">Order Details</h4>
                        <div class="row">
                            <div class="col-md-6">
                                <p><strong>Order ID:</strong> <?php echo htmlspecialchars($tpl['order']['id']); ?></p>
                                <p><strong>Customer:</strong> <?php echo htmlspecialchars($tpl['customer']['name']); ?></p>
                                <p><strong>Address:</strong> <?php echo htmlspecialchars($tpl['order']['address']); ?></p>
                                <p><strong>Country:</strong> <?php echo htmlspecialchars($tpl['order']['country']); ?></p>
                                <p><strong>Region:</strong> <?php echo htmlspecialchars($tpl['order']['region']); ?></p>
                            </div>
                            <div class="col-md-6">
                                <p><strong>Tracking Number:</strong> <?php echo htmlspecialchars($tpl['order']['tracking_number']); ?></p>
                                <p><strong>Courier:</strong> <?php echo htmlspecialchars($tpl['courier']['name']); ?></p>
                                <p><strong>Delivery Date:</strong> <?php echo htmlspecialchars(date('Y-m-d', strtotime($tpl['order']['delivery_date']))); ?></p>
                                <p><strong>Status:</strong> <?php echo htmlspecialchars($tpl['order']['status']); ?></p>
                                <p><strong>Total Price:</strong> <?php echo Utility::getDisplayableAmount(htmlspecialchars(number_format($tpl['order']['total_amount'], 2))); ?></p>
                            </div>
                        </div>
                        <hr>
                        <h5>Products</h5>
                        <div class="table-responsive">
                            <table class="table select-table" id="order-products-table-id">
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
                                            <td><?php echo htmlspecialchars($product['name']); ?></td>
                                            <td><?php echo htmlspecialchars($product['quantity']); ?></td>
                                            <td><?php echo Utility::getDisplayableAmount(htmlspecialchars(number_format($product['price'], 2))); ?></td>
                                            <td><?php echo Utility::getDisplayableAmount(htmlspecialchars(number_format($product['subtotal'], 2))); ?></td>
                                        </tr>
                                    <?php } ?>
                                </tbody>
                            </table>
                        </div>
                        <div class="mt-3">
                            <a href="<?php echo INSTALL_URL; ?>?controller=Order&action=list" class="btn btn-outline-dark">Back to Order List</a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
