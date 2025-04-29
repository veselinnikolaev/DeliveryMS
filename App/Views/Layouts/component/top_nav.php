<?php
$unread_count = 0;
if (!empty($tpl['notifications'])) {
    $unread_count = count(array_filter($tpl['notifications'], fn($n) => !$n['is_seen']));
}
?>
<nav class="navbar default-layout col-lg-12 col-12 p-0 d-flex align-items-top flex-row fixed-top">
    <div class="text-center navbar-brand-wrapper d-flex align-items-center justify-content-start">
        <div class="me-3">
            <button class="navbar-toggler navbar-toggler align-self-center" type="button" data-bs-toggle="minimize">
                <span class="icon-menu"></span>
            </button>
        </div>
        <div>
            <a class="navbar-brand brand-logo" href="<?php echo INSTALL_URL; ?>">
                <img src="web/assets/images/logo.svg" alt="logo">
            </a>
            <a class="navbar-brand brand-logo-mini" href="<?php echo INSTALL_URL; ?>">
                <img src="web/assets/images/logo-mini.svg" alt="logo">
            </a>
        </div>
    </div>

    <div class="navbar-menu-wrapper d-flex align-items-top"> 
        <ul class="navbar-nav ms-auto">
            <?php if (!isset($_SESSION['user'])): ?>
                <li class="nav-item d-flex align-items-center me-2">
                    <a href="<?php echo INSTALL_URL; ?>?controller=Auth&action=login" class="btn btn-primary d-flex align-items-center">
                        <i class="mdi mdi-login me-2"></i>
                        Login
                    </a>
                </li>
                <li class="nav-item d-flex align-items-center">
                    <a href="<?php echo INSTALL_URL; ?>?controller=Auth&action=register" class="btn btn-outline-primary d-flex align-items-center">
                        <i class="mdi mdi-account-plus me-2"></i>
                        Register
                    </a>
                </li>
            <?php else: ?>
                <?php if (isset($_SESSION['user']) && $_SESSION['user']['role'] === 'courier'): ?>
                    <li class="nav-item d-flex align-items-center">
                        <div id="global-tracking-indicator" style="display: none;" 
                             class="d-flex align-items-center me-3 px-3 py-2 rounded">
                            <div class="d-flex align-items-center">
                                <i class="mdi mdi-crosshairs-gps me-2 tracking-pulse"></i>
                                <span class="tracking-text">Location Tracking Active</span>
                            </div>
                        </div>
                    </li>
                <?php endif; ?>
                <li class="nav-item dropdown">       
                    <a class="nav-link count-indicator" id="countDropdown" href="#" data-bs-toggle="dropdown" aria-expanded="false">
                        <i class="icon-bell"></i>
                        <?php if ($unread_count > 0): ?>
                            <span class="count"></span> <!-- Show count of unread -->
                        <?php endif; ?>
                    </a>
                    <div class="dropdown-menu dropdown-menu-right navbar-dropdown preview-list pb-0" aria-labelledby="countDropdown">
                        <a class="dropdown-item py-3" href="<?php echo INSTALL_URL; ?>?controller=Notification&action=index">
                            <p class="mb-0 font-weight-medium float-left">
                                You have <?= $unread_count ?> unread notifications
                            </p>
                            <span class="badge badge-pill badge-primary float-right">View all</span>
                        </a>
                        <div class="dropdown-divider"></div>

                        <?php
                        // Limit the notifications to 5
                        $notificationsToDisplay = array_slice($tpl['notifications'], 0, 5);
                        ?>

                        <?php if (count($notificationsToDisplay) > 0): ?>
                            <?php foreach ($notificationsToDisplay as $notif): ?>
                                <a class="dropdown-item preview-item notification-item <?php echo $notif['is_seen'] == 0 ? 'unseen' : ''; ?>" 
                                   data-id="<?= $notif['id'] ?>" 
                                   href="<?= $notif['link'] ?? '#' ?>">
                                    <div class="preview-thumbnail">
                                        <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="#333333" style="width: 16px; height: 16px; max-width: 16px; max-height: 16px; border-radius: 0;" viewBox="0 0 16 16">
                                        <path d="M0 1.5A.5.5 0 0 1 .5 1H2a.5.5 0 0 1 .485.379L2.89 3H14.5a.5.5 0 0 1 .491.592l-1.5 8A.5.5 0 0 1 13 12H4a.5.5 0 0 1-.491-.408L2.01 3.607 1.61 2H.5a.5.5 0 0 1-.5-.5zM3.102 4l1.313 7h8.17l1.313-7H3.102zM5 12a2 2 0 1 0 0 4 2 2 0 0 0 0-4zm7 0a2 2 0 1 0 0 4 2 2 0 0 0 0-4zm-7 1a1 1 0 1 1 0 2 1 1 0 0 1 0-2zm7 0a1 1 0 1 1 0 2 1 1 0 0 1 0-2z"/>
                                        </svg>
                                    </div>
                                    <div class="preview-item-content flex-grow py-2">
                                        <p class="preview-subject ellipsis font-weight-medium text-dark">
                                            <?= htmlspecialchars($notif['message']) ?>
                                        </p>
                                        <p class="fw-light small-text mb-0">
                                            <?= date($tpl['date_format'], $notif['created_at']) ?>
                                        </p>
                                    </div>
                                </a>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <p class="dropdown-item text-center text-muted">No new notifications</p>
                        <?php endif; ?>
                    </div>
                </li>   
                <li class="nav-item dropdown d-none d-lg-block user-dropdown">
                    <a class="nav-link" id="UserDropdown" href="#" data-bs-toggle="dropdown" aria-expanded="false">
                        <?php if (!empty($_SESSION['user']['photo_path'])): ?>
                            <img id="profileImage" src="<?php echo htmlspecialchars($_SESSION['user']['photo_path']); ?>" 
                                 alt="Profile Photo" class="img-xs rounded-circle">
                             <?php else: ?>
                            <div class="img-xs rounded-circle d-flex align-items-center justify-content-center bg-light" 
                                 style="width: 32px; height: 32px; display: flex; align-items: center; justify-content: center;">
                                <i class="mdi mdi-account" style="font-size: 16px; color: #6c757d;"></i>
                            </div>
                        <?php endif; ?>

                    </a>
                    <div class="dropdown-menu dropdown-menu-right navbar-dropdown" aria-labelledby="UserDropdown">
                        <div class="dropdown-header text-center d-flex flex-column align-items-center">
                            <?php if (!empty($_SESSION['user']['photo_path'])): ?>
                                <img id="profileImage" src="<?php echo htmlspecialchars($_SESSION['user']['photo_path']); ?>" 
                                     alt="Profile Photo" class="rounded-circle" style="width: 80px; height: 80px; object-fit: cover;">
                                 <?php else: ?>
                                <div class="d-flex align-items-center justify-content-center bg-light rounded-circle" 
                                     style="width: 64px; height: 64px;">
                                    <i class="mdi mdi-account" style="font-size: 32px; color: #6c757d;"></i>
                                </div>
                            <?php endif; ?>
                            <p class="mb-1 mt-3 font-weight-semibold"><?php echo $_SESSION['user']['name']; ?></p>
                            <p class="fw-light text-muted mb-0"><?php echo $_SESSION['user']['email']; ?></p>
                        </div>

                        <a class="dropdown-item" href="<?php echo INSTALL_URL; ?>?controller=User&action=profile&id=<?php echo $_SESSION['user']['id']; ?>"><i class="dropdown-item-icon mdi mdi-account-outline text-primary me-2"></i> My Profile</a>
                        <a class="dropdown-item" href="<?php echo INSTALL_URL; ?>?controller=Notification&action=index"><i class="dropdown-item-icon mdi mdi-message-text-outline text-primary me-2"></i> Notifications</a>
                        <a id="sign-out" class="dropdown-item" href="<?php echo INSTALL_URL; ?>?controller=Auth&action=logout"><i class="dropdown-item-icon mdi mdi-power text-primary me-2"></i>Sign Out</a>
                    </div>
                </li>
            </ul>
            <button class="navbar-toggler navbar-toggler-right d-lg-none align-self-center" type="button" data-bs-toggle="offcanvas">
                <span class="mdi mdi-menu"></span>
            </button>
        <?php endif; ?>
    </div>
</nav>
