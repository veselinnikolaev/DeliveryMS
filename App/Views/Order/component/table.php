<table class="table select-table" id="order-table-id">
    <thead>
        <tr>
            <th>
                <div class="form-check form-check-flat mt-0">
                    <label class="form-check-label">
                        <input type="checkbox" class="form-check-input">
                    </label>
                </div>
            </th>
            <th>Order ID</th>
            <th>Customer</th>
            <th>Courier</th>
            <th>Delivery Date</th>
            <th>Total Price</th>
            <th>Status</th>
            <th style="text-align: right;">Actions</th>
        </tr>
    </thead>
    <tbody>
        <?php foreach ($tpl['orders'] as $order) { ?>
            <tr>
                <td>
                    <div class="form-check form-check-flat mt-0">
                        <label class="form-check-label">
                            <input type="checkbox" class="form-check-input">
                        </label>
                    </div>
                </td>
                <td><?php echo htmlspecialchars($order['id']); ?></td>
                <td><?php echo htmlspecialchars($order['customer_name']); ?></td>
                <td><?php echo htmlspecialchars($order['courier_name']); ?></td>
                <td><?php echo htmlspecialchars(date('Y-m-d', strtotime($order['delivery_date']))); ?></td>
                <td><?php echo Utility::getDisplayableAmount(htmlspecialchars($order['total_amount'])); ?></td>
                <td><?php
                    foreach (Utility::$order_status as $k => $v) {
                        if ($k == $order['status']) {
                            echo $v;
                        }
                    }
                    ?></td>
                <td style="text-align: right;">
                    <a class="btn btn-light btn-circle mdc-ripple-upgraded" href="<?php echo INSTALL_URL; ?>?controller=Order&action=details&id=<?php echo $order['id'] ?>">
                        <i class="fa fa-eye" aria-hidden="true"></i>
                    </a>
                    <?php if ($_SESSION['user']['role'] == 'admin') { ?>
                    <a class="btn btn-light btn-circle mdc-ripple-upgraded" href="<?php echo INSTALL_URL; ?>?controller=Order&action=edit&order_id=<?php echo $order['id'] ?>">
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
