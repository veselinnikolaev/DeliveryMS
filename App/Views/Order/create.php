<div class="row">
    <div class="col-sm-12">
        <div class="home-tab">
            <div class="d-sm-flex align-items-center justify-content-between border-bottom">
                <ul class="nav nav-tabs" role="tablist">
                    <li class="nav-item">
                        <a class="nav-link" href="<?php echo INSTALL_URL; ?>?controller=Order&action=list">Order List</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link active ps-0" href="<?php echo INSTALL_URL; ?>?controller=Order&action=create">Create Order</a>
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
                    <h4 class="card-title">Create New Order</h4>

                    <?php if (isset($error_message)): ?>
                        <div class="alert alert-danger"><?php echo $error_message; ?></div>
                    <?php endif; ?>

                    <form method="POST" id="booking-frm-id" action="<?php echo INSTALL_URL; ?>?controller=Order&action=create">
                        <input type="hidden" name="send" value="1" />
                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="customer" class="form-label">Customer</label>
                                    <select name="user_id" id="userId" class="form-select" required>
                                        <option value=''>---</option>
                                        <?php
                                        foreach ($tpl['users'] as $user) {
                                            echo "<option value=\"{$user['id']}\" 
                                                data-address=\"" . htmlspecialchars($user['address']) . "\" 
                                                data-country=\"" . htmlspecialchars($user['country']) . "\" 
                                                data-region=\"" . htmlspecialchars($user['region']) . "\">
                                                {$user['name']}</option>";
                                        }
                                        ?>
                                    </select>
                                </div>
                                <div class="mb-3">
                                    <label for="address" class="form-label">Address</label>
                                    <input type="text" class="form-control" id="address" name="address" required>
                                </div>
                                <div class="mb-3">
                                    <label for="country" class="form-label">Country</label>
                                    <input type="text" class="form-control" id="country" name="country" required>
                                </div>
                                <div class="mb-3">
                                    <label for="region" class="form-label">Region</label>
                                    <input type="text" class="form-control" id="region" name="region" required>
                                </div>
                                <div class="mb-3">
                                    <label for="courierId" class="form-label">Courier</label>
                                    <select name="courier_id" id="courierId" class="form-select" required>
                                        <option value=''>---</option>
                                        <?php
                                        foreach ($tpl['couriers'] as $courier) {
                                            echo "<option value=\"{$courier['id']}\">{$courier['name']}</option>";
                                        }
                                        ?>
                                    </select>
                                </div>
                                <div class="mb-3">
                                    <label for="deliveryDate" class="form-label">Delivery Date</label>
                                    <input type="date" class="form-control" id="deliveryDate" name="delivery_date" value="">
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="productPrice" class="form-label">Product Price</label>
                                    <div class="input-group">
                                        <span class="input-group-text"><?php echo $tpl['currency']; ?></span>
                                        <input type="text" class="form-control" id="productPrice" name="product_price" readonly>
                                    </div>
                                </div>
                                <div class="mb-3">
                                    <label for="tax" class="form-label">Tax</label>
                                    <div class="input-group">
                                        <span class="input-group-text"><?php echo $tpl['currency']; ?></span>
                                        <input type="number" step="0.01" min="0" class="form-control" id="tax" name="tax" required>
                                    </div>
                                </div>
                                <div class="mb-3">
                                    <label for="shippingPrice" class="form-label">Shipping Price</label>
                                    <div class="input-group">
                                        <span class="input-group-text"><?php echo $tpl['currency']; ?></span>
                                        <input type="number" step="0.01" min="0" class="form-control" id="shippingPrice"  name="shipping_price" required>
                                    </div>
                                </div> 
                                <div class="mb-3">
                                    <label for="totalPrice" class="form-label">Total Price</label>
                                    <div class="input-group">
                                        <span class="input-group-text"><?php echo $tpl['currency']; ?></span>
                                        <input type="text" class="form-control" id="totalPrice" name="total_price" readonly>
                                    </div>
                                </div> 
                                <div class="mb-3">
                                    <label for="status" class="form-label">Order Status</label>
                                    <select class="form-select" id="status" name="status" required>
                                        <option value=''>---</option>
                                        <?php
                                        foreach (Utility::$order_status as $k => $v) {
                                            ?>
                                            <option value="<?php echo $k; ?>"><?php echo $v; ?></option>
                                            <?php
                                        }
                                        ?>
                                    </select>
                                </div>
                                <div id="productRows">
                                    <div class="row align-items-end mb-3 product-row">
                                        <div class="col-md-6">
                                            <label for="productIds" class="form-label">Products</label>
                                            <select name="product_id[]" id="productIds" class="form-select" required>
                                                <option value="">---</option>
                                                <?php
                                                foreach ($tpl['products'] as $product) {
                                                    echo "<option value=\"{$product['id']}\" data-max-quantity=\"{$product['stock']}\">{$product['name']}</option>";
                                                }
                                                ?>
                                            </select>
                                        </div>   
                                        <div class="col-md-4">
                                            <label for="quantities" class="form-label">Quantity</label>
                                            <input type="number" step="1" min="1" class="form-control" id="quantities"
                                                   name="quantity[]" required>
                                        </div>

                                        <div class="col-md-1 text-center d-flex justify-content-center align-items-center">
                                            <button type="button" class="btn btn-light d-flex justify-content-center align-items-center rounded-circle add-row" style="width: 36px; height: 36px;">+</button>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-12">
                                <button type="submit" class="btn btn-primary text-white me-0">Create Order</button>
                                <a href="javascript:" id="calculate-price-btn-id" class="btn btn-primary text-white me-0">Calculate Price</a>
                                <a href="<?php echo INSTALL_URL; ?>?controller=Order&action=list" class="btn btn-outline-dark">Cancel</a>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>