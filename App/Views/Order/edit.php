<div class="row">
    <div class="col-sm-12">
        <div class="home-tab">
            <div class="d-sm-flex align-items-center justify-content-between border-bottom">
                <ul class="nav nav-tabs" role="tablist">
                    <li class="nav-item">
                        <a class="nav-link" href="<?php echo INSTALL_URL; ?>?controller=Order&action=list">Order List</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link active ps-0" href="#">Edit Order</a>
                    </li>
                </ul>
            </div>

            <div class="card card-rounded mt-3">
                <div class="card-body">
                    <h4 class="card-title">Edit Order</h4>

                    <?php if (isset($error_message)): ?>
                        <div class="alert alert-danger"><?php echo $error_message; ?></div>
                    <?php endif; ?>

                    <form method="POST" id="booking-frm-id" action="<?php echo INSTALL_URL; ?>?controller=Order&action=edit">
                        <input type="hidden" name="id" value="<?php echo $order['id']; ?>" />
                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="customer" class="form-label">Customer</label>
                                    <select name="user_id" id="userId" class="form-select" required>
                                        <?php
                                        foreach ($tpl['users'] as $user) {
                                            $selected = ($user['id'] == $order['user_id']) ? 'selected' : '';
                                            echo "<option value=\"{$user['id']}\" $selected>{$user['name']}</option>";
                                        }
                                        ?>
                                    </select>
                                </div>
                                <div class="mb-3">
                                    <label for="address" class="form-label">Address</label>
                                    <input type="text" class="form-control" id="address" name="address" value="<?php echo $order['address']; ?>" required>
                                </div>
                                <div class="mb-3">
                                    <label for="country" class="form-label">Country</label>
                                    <input type="text" class="form-control" id="country" name="country" value="<?php echo $order['country']; ?>" required>
                                </div>
                                <div class="mb-3">
                                    <label for="region" class="form-label">Region</label>
                                    <input type="text" class="form-control" id="region" name="region" value="<?php echo $order['region']; ?>" required>
                                </div>
                                <div class="mb-3">
                                    <label for="courierId" class="form-label">Courier</label>
                                    <select name="courier_id" id="courierId" class="form-select" required>
                                        <?php
                                        foreach ($tpl['couriers'] as $courier) {
                                            $selected = ($courier['id'] == $order['courier_id']) ? 'selected' : '';
                                            echo "<option value=\"{$courier['id']}\" $selected>{$courier['name']}</option>";
                                        }
                                        ?>
                                    </select>
                                </div>
                                <div class="mb-3">
                                    <label for="deliveryDate" class="form-label">Delivery Date</label>
                                    <input type="date" class="form-control" id="deliveryDate" name="delivery_date" 
                                           value="<?php echo date('Y-m-d', $order['delivery_date']); ?>" required>
                                </div>  
                            </div>
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="productPrice" class="form-label">Product Price</label>
                                    <div class="input-group">
                                        <span class="input-group-text"><?php echo $tpl['currency']; ?></span>
                                        <input type="text" class="form-control" id="productPrice" name="product_price" value="<?php echo $order['product_price']; ?>" readonly>
                                    </div>
                                </div>
                                <div class="mb-3">
                                    <label for="tax" class="form-label">Tax</label>
                                    <div class="input-group">
                                        <span class="input-group-text"><?php echo $tpl['currency']; ?></span>
                                        <input type="text" class="form-control" id="tax" name="tax" value="<?php echo $order['tax']; ?>" required readonly>
                                    </div>
                                </div>
                                <div class="mb-3">
                                    <label for="shippingPrice" class="form-label">Shipping Price</label>
                                    <div class="input-group">
                                        <span class="input-group-text"><?php echo $tpl['currency']; ?></span>
                                        <input type="теьт" class="form-control" id="shippingPrice"  name="shipping_price" value="<?php echo $order['shipping_price']; ?>" required readonly>
                                    </div>
                                </div> 
                                <div class="mb-3">
                                    <label for="totalAmount" class="form-label">Total Amount</label>
                                    <div class="input-group">
                                        <span class="input-group-text"><?php echo $tpl['currency']; ?></span>
                                        <input type="text" class="form-control" id="totalAmount" name="total_amount" value="<?php echo $order['total_amount']; ?>" readonly>
                                    </div>
                                </div> 
                                <div class="mb-3">
                                    <label for="status" class="form-label">Order Status</label>
                                    <select class="form-select" id="status" name="status" required>
                                        <?php
                                        foreach (Utility::$order_status as $k => $v) {
                                            $selected = ($k == $order['status']) ? 'selected' : '';
                                            echo "<option value=\"{$k}\" $selected>{$v}</option>";
                                        }
                                        ?>
                                    </select>
                                </div>
                                <div id="productRows">
                                    <?php foreach ($tpl['orderProducts'] as $index => $orderProduct): ?>
                                        <div class="row align-items-end mb-3 product-row">
                                            <div class="col-md-6">
                                                <label for="productIds" class="form-label">Products</label>
                                                <select name="product_id[]" class="form-select" required>
                                                    <option value="">---</option>
                                                    <?php foreach ($tpl['products'] as $productOption): ?>
                                                        <option value="<?php echo $productOption['id']; ?>" data-max-quantity="<?php echo $productOption['stock'] + $tpl['productQuantities'][$productOption['id']]; ?>" <?php echo ($productOption['id'] == $orderProduct['product_id']) ? 'selected' : ''; ?>>
                                                            <?php echo $productOption['name']; ?>
                                                        </option>
                                                    <?php endforeach; ?>
                                                </select>
                                            </div>
                                            <div class="col-md-4">
                                                <label for="quantities" class="form-label">Quantity</label>
                                                <input type="number" step="1" min="1" class="form-control" name="quantity[]" value="<?php echo $orderProduct['quantity']; ?>" required>
                                            </div>
                                            <div class="col-md-1 text-center d-flex justify-content-center align-items-center">
                                                <?php if ($index === count($tpl['orderProducts']) - 1): ?>
                                                    <button type="button" class="btn btn-light d-flex justify-content-center align-items-center rounded-circle add-row" style="width: 36px; height: 36px;">+</button>
                                                <?php else: ?>
                                                    <button type="button" class="btn btn-danger d-flex justify-content-center align-items-center rounded-circle remove-row" style="width: 36px; height: 36px;">−</button>
                                                <?php endif; ?>
                                            </div>
                                        </div>
                                    <?php endforeach; ?>
                                </div>
                            </div>
                            <div class="col-md-6">
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-12">
                                <button type="submit" class="btn btn-primary text-white me-0" name="send">Save Changes</button>
                                <a href="javascript:" id="calculate-price-btn-id" class="btn btn-primary text-white me-0">Calculate Price</a>
                                <a href="<?php echo $_SERVER['HTTP_REFERER']; ?>" class="btn btn-outline-dark">Cancel</a>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
