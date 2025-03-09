<table class="table select-table" id="courier-table-id">
    <thead>
        <tr>
            <?php if (in_array($_SESSION['user']['role'], ['admin', 'root'])) { ?>
                <th>
                    <div class="form-check form-check-flat mt-0">
                        <label class="form-check-label">
                            <input type="checkbox" class="form-check-input" id="select-all-couriers">
                        </label>
                    </div>
                </th>
            <?php } ?>
            <th>Courier ID</th>
            <th>Courier Name</th>
            <th>Phone Number</th>
            <th>Email</th>
            <th style="text-align: right;"></th>
        </tr>
    </thead>
    <tbody>
        <?php
        if (empty($tpl['couriers'])) {
            
        } else {
            foreach ($tpl['couriers'] as $courier) {
                ?>
                <tr>
                    <?php if (in_array($_SESSION['user']['role'], ['admin', 'root'])) { ?>
                        <td>
                            <div class="form-check form-check-flat mt-0">
                                <label class="form-check-label">
                                    <input type="checkbox" class="form-check-input courier-checkbox" data-id="<?php echo $courier['id'] ?>">
                                </label>
                            </div>
                        </td>
                    <?php } ?>
                    <td><?php echo htmlspecialchars($courier['id']); ?></td>
                    <td><?php echo htmlspecialchars($courier['name']); ?></td>
                    <td><?php echo htmlspecialchars($courier['phone_number']); ?></td>
                    <td><?php echo htmlspecialchars($courier['email']); ?></td>
                    <td style="text-align: right;">
                        <a class="btn btn-info btn-circle mdc-ripple-upgraded" href="<?php echo INSTALL_URL; ?>?controller=Courier&action=edit&id=<?php echo $courier['id'] ?>">
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