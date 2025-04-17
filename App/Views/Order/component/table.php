<table class="table select-table" id="order-table-id">
    <thead>
        <tr>
            <?php if (in_array($_SESSION['user']['role'], ['admin', 'root', 'courier'])) { ?>
                <th>
                    <div class="form-check form-check-flat mt-0">
                        <label class="form-check-label">
                            <input type="checkbox" class="form-check-input" id="select-all-orders">
                        </label>
                    </div>
                </th>
            <?php } ?>
            <th>Order ID</th>
            <th>Tracking Number</th>
            <th>Customer</th>
            <th>Courier</th>
            <th>Delivery Date</th>
            <th>Total Price</th>
            <th>Address</th>
            <th>Country</th>
            <th>Region</th>
            <th>Status</th>
            <th style="text-align: right;">Actions</th>
        </tr>
    </thead>
    <tbody>
        <?php foreach ($tpl['orders'] as $order) { ?>
            <tr>
                <?php if (in_array($_SESSION['user']['role'], ['admin', 'root', 'courier'])) { ?>
                    <td>
                        <div class="form-check form-check-flat mt-0">
                            <label class="form-check-label">
                                <input type="checkbox" class="form-check-input order-checkbox" data-id="<?php echo $order['id']; ?>">
                            </label>
                        </div>
                    </td>
                <?php } ?>
                <td><?php echo htmlspecialchars($order['id']); ?></td>
                <td><?php echo htmlspecialchars($order['tracking_number']); ?></td>
                <td><?php echo htmlspecialchars($order['customer_name']); ?></td>
                <td><?php echo htmlspecialchars($order['courier_name']); ?></td>
                <td><?php echo htmlspecialchars($order['delivery_date']); ?></td>
                <td><?php echo Utility::getDisplayableAmount(htmlspecialchars($order['total_amount'])); ?></td>
                <td><?php echo htmlspecialchars($order['address']); ?></td>
                <td><?php echo htmlspecialchars($order['country']); ?></td>
                <td><?php echo htmlspecialchars($order['region']); ?></td>
                <td><?php
                    echo htmlspecialchars(Utility::$order_status[$order['status']]);
                    ?></td>
                <td style="text-align: right;">
                    <?php if ($_SESSION['user']['role'] === 'courier' && $order['status'] == 'shipped') { ?>
                        <a class="btn btn-success btn-circle mdc-ripple-upgraded change-status" 
                           href="#" data-id="<?php echo $order['id'] ?>" data-status="delivered">
                            <i class="fa fa-check" aria-hidden="true" title="Mark as Delivered"></i>
                        </a>
                        <a class="btn btn-warning btn-circle mdc-ripple-upgraded change-status"
                           href="#" data-id="<?php echo $order['id'] ?>" data-status="returned">
                            <i class="fa fa-undo" aria-hidden="true" title="Mark as Returned"></i>
                        </a>
                    <?php } ?>
                    <a class="btn btn-light btn-circle mdc-ripple-upgraded" href="<?php echo INSTALL_URL; ?>?controller=Order&action=details&id=<?php echo $order['id'] ?>">
                        <i class="fa fa-eye" aria-hidden="true"></i>
                    </a>
                    <?php if (in_array($_SESSION['user']['role'], ['admin', 'root'])) { ?>
                        <a class="btn btn-info btn-circle mdc-ripple-upgraded" href="<?php echo INSTALL_URL; ?>?controller=Order&action=edit&order_id=<?php echo $order['id'] ?>">
                            <i class="fa fa-pencil-square-o" aria-hidden="true"></i>
                        </a>
                        <a class="btn btn-danger btn-circle delete-order" href="#" data-id="<?php echo $order['id']; ?>">
                            <i class="fa fa-trash-o" aria-hidden="true"></i>
                        </a>
                    <?php } ?>
                </td>
            </tr>
        <?php } ?>
    </tbody>
</table>
