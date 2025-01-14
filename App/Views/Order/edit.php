<div class="row">
    <div class="col-sm-12">
        <div class="home-tab">
            <div class="d-sm-flex align-items-center justify-content-between border-bottom">
                <ul class="nav nav-tabs" role="tablist">
                    <li class="nav-item">
                        <a class="nav-link" href="<?php echo INSTALL_URL . "?controller=Order&action=list"; ?>">Order List</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link active ps-0" href="<?php echo INSTALL_URL . "?controller=Order&action=edit&id=" . $order['id']; ?>">Edit Order</a>
                    </li>
                </ul>
                <div>
                    <div class="btn-wrapper">
                        <a href="#" class="btn btn-otline-dark align-items-center"><i class="icon-share"></i> Share</a>
                        <a href="#" class="btn btn-otline-dark"><i class="icon-printer"></i> Print</a>
                    </div>
                </div>
            </div>

            <div class="card card-rounded mt-3">
                <div class="card-body">
                    <h4 class="card-title">Edit Order</h4>

                    <?php if (isset($error_message)): ?>
                        <div class="alert alert-danger"><?php echo $error_message; ?></div>
                    <?php endif; ?>

                    <form method="POST" action="<?php echo INSTALL_URL . "?controller=Order&action=edit&id=" . $order['id']; ?>">
                        <!-- Hidden order ID input -->
                        <input type="hidden" name="order_id" value="<?php echo $order['id']; ?>">

                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="customer" class="form-label">Customer</label>
                                <select name="user_id" id="userId" class="form-select" required>
                                    <option value=''>---</option>
                                    <?php
                                    foreach ($tpl['users'] as $user) {
                                        $selected = ($user['id'] == $order['user_id']) ? 'selected' : '';
                                        echo "<option value=\"{$user['id']}\" $selected>{$user['full_name']}</option>";
                                    }
                                    ?>
                                </select>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label for="address" class="form-label">Address</label>
                                <input type="text" class="form-control" id="address" name="address" value="<?php echo $order['address']; ?>" required>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="country" class="form-label">Country</label>
                                <input type="text" class="form-control" id="country" name="country" value="<?php echo $order['country']; ?>" required>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label for="region" class="form-label">Region</label>
                                <input type="text" class="form-control" id="region" name="region" value="<?php echo $order['region']; ?>" required>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-3 mb-3">
                                <label for="productPrice" class="form-label">Product Price</label>
                                <div class="input-group">
                                    <span class="input-group-text">$</span>
                                    <input type="number" step="0.01" min="0" class="form-control" id="productPrice"
                                           name="product_price" value="<?php echo $order['product_price']; ?>" required>
                                </div>
                            </div>
                            <div class="col-md-3 mb-3">
                                <label for="tax" class="form-label">Tax</label>
                                <div class="input-group">
                                    <span class="input-group-text">$</span>
                                    <input type="number" step="0.01" min="0" class="form-control" id="tax" name="tax"
                                           value="<?php echo $order['tax']; ?>" required>
                                </div>
                            </div>
                            <div class="col-md-3 mb-3">
                                <label for="shippingPrice" class="form-label">Shipping Price</label>
                                <div class="input-group">
                                    <span class="input-group-text">$</span>
                                    <input type="number" step="0.01" min="0" class="form-control" id="shippingPrice"
                                           name="shipping_price" value="<?php echo $order['shipping_price']; ?>" required>
                                </div>
                            </div>
                            <div class="col-md-3 mb-3">
                                <label for="totalAmount" class="form-label">Total Amount</label>
                                <div class="input-group">
                                    <span class="input-group-text">$</span>
                                    <input type="number" step="0.01" min="0" class="form-control" id="totalAmount"
                                           name="total_amount" value="<?php echo $order['total_amount']; ?>" required>
                                </div>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="status" class="form-label">Order Status</label>
                                <select class="form-select" id="status" name="status" required>
                                    <?php
                                    foreach (Utility::$order_status as $k => $v) {
                                        $selected = ($k == $order['status']) ? 'selected' : '';
                                        echo "<option value=\"$k\" $selected>$v</option>";
                                    }
                                    ?>
                                </select>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label for="deliveryDate" class="form-label">Delivery Date</label>
                                <input type="text" class="form-control" id="deliveryDate" name="delivery_date" value="<?php echo $order['delivery_date']; ?>">
                            </div>
                        </div>

                        <div class="col-md-6 mb-3">
                            <label for="courierId" class="form-label">Courier ID</label>
                            <select name="courier_id" id="courierId" class="form-select" required>
                                <option value=''>---</option>
                                <?php
                                foreach ($tpl['couriers'] as $courier) {
                                    $selected = ($courier['id'] == $order['courier_id']) ? 'selected' : '';
                                    echo "<option value=\"{$courier['id']}\" $selected>{$courier['courier_name']}</option>";
                                }
                                ?>
                            </select>
                        </div>

                        <div class="col-md-6 mb-3">
                            <label for="deliveryDate" class="form-label">Delivery Date</label>
                            <input type="text" class="form-control" id="deliveryDate" name="delivery_date" value="<?php echo $order['delivery_date']; ?>">
                        </div>

                        <div class="row align-items-end mb-3">
                            <div class="col-md-6">
                                <label for="productId1" class="form-label">Products</label>
                                <select name="product_id[]" id="productId1" class="form-select" required>
                                    <option value="">---</option>
                                    <?php
                                    foreach ($tpl['products'] as $product) {
                                        $selected = (in_array($product['id'], $order['product_ids'])) ? 'selected' : '';
                                        echo "<option value=\"{$product['id']}\" $selected>{$product['name']}</option>";
                                    }
                                    ?>
                                </select>
                            </div>

                            <div class="col-md-5">
                                <label for="quantity1" class="form-label">Quantity</label>
                                <input type="number" step="1" min="1" class="form-control" id="quantity1"
                                       name="quantity[]" value="<?php echo $order['quantities'][0]; ?>" required>
                            </div>

                            <div class="col-md-1 text-center d-flex justify-content-center align-items-center">
                                <button type="button" class="btn btn-light d-flex justify-content-center align-items-center rounded-circle add-row" style="width: 36px; height: 36px;">+</button>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-12">
                                <button type="submit" class="btn btn-primary">Update Order</button>
                                <a href="list.php" class="btn btn-secondary ms-2">Cancel</a>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
