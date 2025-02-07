<table class="table select-table" id="setting-table-id">
    <thead>
        <tr>
            <th>
                <div class="form-check form-check-flat mt-0">
                    <label class="form-check-label">
                        <input type="checkbox" class="form-check-input">
                    </label>
                </div>
            </th>
            <th>Setting ID</th>
            <th>Tax Rate</th>
            <th>Shipping Rate</th>
            <th>Currency Code</th>
            <th>Email Sending</th>
            <th>Delivery Time in Days</th>
            <th style="text-align: right;"></th>
        </tr>
    </thead>
    <tbody>
        <?php foreach ($tpl['settings'] as $setting) { ?>
            <tr>
                <td>
                    <div class="form-check form-check-flat mt-0">
                        <label class="form-check-label">
                            <input type="checkbox" class="form-check-input">
                        </label>
                    </div>
                </td>
                <td><?php echo htmlspecialchars($setting['id']); ?></td>
                <td><?php echo htmlspecialchars($setting['tax_rate']); ?></td>
                <td><?php echo htmlspecialchars($setting['shipping_rate']); ?></td>
                <td><?php echo htmlspecialchars($setting['currency_code']); ?></td>
                <td><?php echo htmlspecialchars($setting['email_sending']); ?></td>
                <td><?php echo htmlspecialchars($setting['delivery_time_days']); ?></td>
                <td style="text-align: right;">
                    <a class="btn btn-light btn-circle mdc-ripple-upgraded" href="<?php echo INSTALL_URL; ?>?controller=Settings&action=edit&id=<?php echo $setting['id'] ?>">
                        <i class="fa fa-pencil-square-o" aria-hidden="true"></i>
                    </a>
                </td>
            </tr>
        <?php } ?>
    </tbody>
</table>