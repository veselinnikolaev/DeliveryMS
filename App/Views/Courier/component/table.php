<table class="table select-table" id="courier-table-id">
    <thead>
        <tr>
            <th>
                <div class="form-check form-check-flat mt-0">
                    <label class="form-check-label">
                        <input type="checkbox" class="form-check-input">
                    </label>
                </div>
            </th>
            <th>Courier ID</th>
            <th>Courier Name</th>
            <th>Phone Number</th>
            <th>Email</th>
            <th style="text-align: right;"></th>
        </tr>
    </thead>
    <tbody>
        <?php if(empty($tpl['couriers'])) { } else { foreach ($tpl['couriers'] as $courier) { ?>
            <tr>
                <td>
                    <div class="form-check form-check-flat mt-0">
                        <label class="form-check-label">
                            <input type="checkbox" class="form-check-input">
                        </label>
                    </div>
                </td>
                <td><?php echo htmlspecialchars($courier['id']); ?></td>
                <td><?php echo htmlspecialchars($courier['courier_name']); ?></td>
                <td><?php echo htmlspecialchars($courier['phone_number']); ?></td>
                <td><?php echo htmlspecialchars($courier['email']); ?></td>
                <td style="text-align: right;">
                    <a class="btn btn-light btn-circle mdc-ripple-upgraded" href="<?php echo INSTALL_URL; ?>?controller=Courier&action=edit&id=<?php echo $courier['id'] ?>">
                        <i class="fa fa-pencil-square-o" aria-hidden="true"></i>
                    </a>
                    <a class="btn btn-danger btn-circle delete-courier" href="#" data-id="<?php echo $courier['id']; ?>">
                        <i class="fa fa-trash-o" aria-hidden="true"></i>
                    </a>
                </td>
            </tr>
        <?php } ?>
        <?php } ?>
    </tbody>
</table>