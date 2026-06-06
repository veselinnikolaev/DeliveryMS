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
    <?php
    $userRole = $_SESSION['user']['role'] ?? 'guest';
    $isAdmin = in_array($userRole, ['admin', 'root']);
    ?>

    <?php if (!empty($tpl['couriers']) && is_iterable($tpl['couriers'])): ?>
        <?php foreach ($tpl['couriers'] as $courier): ?>
            <tr>
                <?php if ($isAdmin): ?>
                    <td>
                        <div class="form-check form-check-flat mt-0">
                            <label class="form-check-label">
                                <input type="checkbox" class="form-check-input courier-checkbox" data-id="<?= htmlspecialchars((string)$courier['id']) ?>">
                            </label>
                        </div>
                    </td>
                <?php endif; ?>
                <td><?= htmlspecialchars((string)$courier['id']); ?></td>
                <td><?= htmlspecialchars((string)$courier['name']); ?></td>
                <td><?= htmlspecialchars((string)$courier['email']); ?></td>
                <td><?= htmlspecialchars((string)($courier['phone_number'] ?? 'N/A')); ?></td>
                <td><?= htmlspecialchars((string)($courier['address'] ?? 'N/A')); ?></td>
                <td><?= htmlspecialchars((string)($courier['country'] ?? 'N/A')); ?></td>
                <td><?= htmlspecialchars((string)($courier['region'] ?? 'N/A')); ?></td>
                <td style="text-align: right;">
                    <a class="btn btn-light btn-circle" href="<?= INSTALL_URL ?>?controller=User&action=profile&id=<?= $courier['id'] ?>">
                        <i class="fa fa-eye"></i>
                    </a>
                    <?php if ($isAdmin): ?>
                        <a class="btn btn-info btn-circle" href="<?= INSTALL_URL ?>?controller=Courier&action=edit&id=<?= $courier['id'] ?>">
                            <i class="fa fa-pencil-square-o"></i>
                        </a>
                    <?php endif; ?>
                    <a class="btn btn-danger btn-circle delete-courier" href="#" data-id="<?= $courier['id'] ?>">
                        <i class="fa fa-trash-o"></i>
                    </a>
                </td>
            </tr>
        <?php endforeach; ?>
    <?php else: ?>
        <tr>
            <td colspan="<?= $isAdmin ? 9 : 8 ?>" class="text-center">No couriers found.</td>
        </tr>
    <?php endif; ?>
    </tbody>
</table>
