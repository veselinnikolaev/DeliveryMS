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

                    <form method="POST" id="booking-frm-id" action="<?php echo INSTALL_URL; ?>?controller=Order&action=edit&order_id=<?php echo $order['id']; ?>">
                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-3">
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
                                        <option value=''>---</option>
                                        <?php
                                        foreach ($tpl['couriers'] as $courier) {
                                            $selected = ($courier['id'] == $order['courier_id']) ? 'selected' : '';
                                            echo "<option value=\"{$courier['id']}\" $selected>{$courier['courier_name']}</option>";
                                        }
                                        ?>
                                    </select>
                                </div>
                                <div class="mb-3">
                                    <label for="deliveryDate" class="form-label">Delivery Date</label>
                                    <input type="text" class="form-control" id="deliveryDate" name="delivery_date" value="<?php echo date('Y-m-d', strtotime($order['delivery_date'])); ?>" required>
                                </div>
                            </div>
                            <div class="col-md-6">
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-12">
                                <button type="submit" class="btn btn-primary" name="send">Save Changes</button>
                                <a href="javascript:" id="calculate-price-btn-id" class="btn btn-secondary ms-2">Calculate Price</a>
                                <a href="<?php echo INSTALL_URL; ?>?controller=Order&action=list" class="btn btn-secondary ms-2">Cancel</a>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
