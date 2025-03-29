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
            <th>Name</th>
            <th>Email</th>
            <th>Phone Number</th>
            <th>Address</th>
            <th>Country</th>
            <th>Region</th>
            <th style="text-align: right;">Actions</th>
        </tr>
    </thead>
    <tbody>
        <?php foreach ($tpl['couriers'] as $courier) { ?>
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
                <td><?php echo htmlspecialchars($courier['email']); ?></td>
                <td><?php echo htmlspecialchars($courier['phone_number'] ?? 'N/A'); ?></td>
                <td><?php echo htmlspecialchars($courier['address'] ?? 'N/A'); ?></td>
                <td><?php echo htmlspecialchars($courier['country'] ?? 'N/A'); ?></td>
                <td><?php echo htmlspecialchars($courier['region'] ?? 'N/A'); ?></td>
                <td style="text-align: right;">
                    <a class="btn btn-light btn-circle mdc-ripple-upgraded" href="<?php echo INSTALL_URL; ?>?controller=User&action=profile&id=<?php echo $courier['id']; ?>">
                        <i class="fa fa-eye" aria-hidden="true"></i>
                    </a>
                    <?php if (in_array($_SESSION['user']['role'], ['admin', 'root'])) { ?>
                        <a class="btn btn-info btn-circle mdc-ripple-upgraded" href="<?php echo INSTALL_URL; ?>?controller=Courier&action=edit&id=<?php echo $courier['id']; ?>">
                            <i class="fa fa-pencil-square-o" aria-hidden="true"></i>
                        </a>
                    <?php } ?>              
                    <a class="btn btn-danger btn-circle delete-courier" href="#" data-id="<?php echo $courier['id']; ?>">
                        <i class="fa fa-trash-o" aria-hidden="true"></i>
                    </a>
                </td>
            </tr>
        <?php } ?>
    </tbody>
</table>