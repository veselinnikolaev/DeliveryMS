<h2 class = "mb-4">Your Notifications</h2>

<button id = "markAllSeen" class = "btn btn-primary mb-3">Mark All as Seen</button>

<ul class = "list-group">
    <?php foreach ($notifications as $notif):
        ?>
        <li class="list-group-item d-flex justify-content-between align-items-center 
            <?= $notif['is_seen'] ? 'list-group-item-light' : 'list-group-item-warning' ?>">
            <div>
                <strong><?= htmlspecialchars($notif['message']) ?></strong>
                <?php
                if (!empty($notif['link'])) {
                    echo "<a href=\"{$notif['link']}\">link</a>";
                }
                ?>
                <br>
                <small class="text-muted"><?= date($tpl['date_format'] . ' H:i', $notif['created_at']) ?></small>
            </div>
            <div>
                <?php if (!$notif['is_seen']): ?>
                    <button class="btn btn-sm btn-success mark-seen" data-id="<?= $notif['id'] ?>">Mark as Seen</button>
                <?php endif; ?>
            </div>
        </li>
    <?php endforeach; ?>
</ul>

