<table class="table select-table" id="user-table-id">
    <thead>
        <tr>
            <th>
                <div class="form-check form-check-flat mt-0">
                    <label class="form-check-label">
                        <input type="checkbox" class="form-check-input">
                    </label>
                </div>
            </th>
            <th>User ID</th>
            <th>Full Name</th>
            <th>Email</th>
            <th>Phone Number</th>
            <th style="text-align: right;"></th>
        </tr>
    </thead>
    <tbody>
        <?php foreach ($tpl['users'] as $user) { ?>
            <tr>
                <td>
                    <div class="form-check form-check-flat mt-0">
                        <label class="form-check-label">
                            <input type="checkbox" class="form-check-input">
                        </label>
                    </div>
                </td>
                <td><?php echo htmlspecialchars($user['id']); ?></td>
                <td><?php echo htmlspecialchars($user['full_name']); ?></td>
                <td><?php echo htmlspecialchars($user['email']); ?></td>
                <td><?php echo htmlspecialchars($user['phone_number']); ?></td>
                <td style="text-align: right;">
                    <a class="btn btn-light btn-circle mdc-ripple-upgraded" href="<?php echo INSTALL_URL; ?>?controller=User&action=edit&id=<?php echo $user['id'] ?>">
                        <i class="fa fa-pencil-square-o" aria-hidden="true"></i>
                    </a>
                    <a class="btn btn-danger btn-circle delete-user" href="#" data-id="<?php echo $user['id']; ?>">
                        <i class="fa fa-trash-o" aria-hidden="true"></i>
                    </a>
                </td>
            </tr>
        <?php } ?>
    </tbody>
</table>