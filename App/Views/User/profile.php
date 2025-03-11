<div class="container-scroller">
    <div class="row">
        <div class="col-sm-12">
            <div class="home-tab">
                <div class="d-sm-flex align-items-center justify-content-between border-bottom">
                    <ul class="nav nav-tabs" role="tablist">
                        <li class="nav-item">
                            <a class="nav-link" href="<?php echo INSTALL_URL; ?>">Dashboard</a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link active ps-0" href="#">My Profile</a>
                        </li>
                    </ul>
                </div>
                <div class="card card-rounded mt-3">
                    <div class="card-body">
                        <div class="d-flex justify-content-between align-items-center mb-4">
                            <h4 class="card-title mb-0">User Profile</h4>
                            <div>
                                <a href="<?php echo INSTALL_URL; ?>?controller=User&action=editPassword&id=<?php echo $tpl['user']['id']; ?>" class="btn btn-outline-primary btn-sm me-2">
                                    <i class="mdi mdi-key-variant me-1"></i> Change Password
                                </a>
                                <?php if ($_SESSION['user']['id'] == $tpl['user']['id']) { ?>
                                    <a href="<?php echo INSTALL_URL; ?>?controller=Auth&action=logout" class="btn btn-outline-danger btn-sm">
                                        <i class="mdi mdi-logout me-1"></i> Sign Out
                                    </a>
                                <?php } ?>
                            </div>
                        </div>

                        <div class="row">
                            <!-- Left column with photo -->
                            <div class="col-md-4 text-center mb-4 mb-md-0">
                                <input type="file" id="profilePicInput" style="display: none;" accept="image/*">
                                <input type="hidden" id="user_id" value="<?php echo $tpl['user']['id']; ?>">
                                <div class="profile-image-container mb-3" id="profileImageWrapper">
                                    <?php if (!empty($tpl['user']['photo_path'])): ?>
                                        <img id="profileImage" src="<?php echo htmlspecialchars($tpl['user']['photo_path']); ?>" 
                                             alt="Profile Photo" class="rounded-circle profile-img">
                                         <?php else: ?>
                                        <div id="profileImagePlaceholder" class="placeholder-image rounded-circle d-flex align-items-center justify-content-center bg-light">
                                            <i class="mdi mdi-account" style="font-size: 80px;"></i>
                                        </div>
                                    <?php endif; ?>
                                </div>

                                <h5 class="mb-1"><?php echo htmlspecialchars($tpl['user']['name']); ?></h5>
                                <p class="text-muted"><?php echo htmlspecialchars($tpl['user']['role']); ?></p>

                                <div class="mt-3">
                                    <a href="<?php echo INSTALL_URL; ?>?controller=User&action=edit&id=<?php echo $tpl['user']['id']; ?>" 
                                       class="btn btn-primary">
                                        <i class="mdi mdi-pencil me-1"></i> Edit Profile
                                    </a>
                                    <?php if ($tpl['user']['role'] !== 'root'): ?>
                                        <button type="button" class="btn btn-danger mt-2" id="deleteAccount" data-id="<?php echo $tpl['user']['id']; ?>">
                                            <i class="mdi mdi-delete me-1"></i> Delete Account
                                        </button>
                                    <?php endif; ?>
                                </div>
                            </div>

                            <!-- Right column with user details -->
                            <div class="col-md-8">
                                <div class="card shadow-sm">
                                    <div class="card-body">
                                        <h5 class="card-title mb-4">Personal Information</h5>

                                        <div class="row mb-3">
                                            <div class="col-md-4">
                                                <p class="text-muted mb-0">Name</p>
                                            </div>
                                            <div class="col-md-8">
                                                <p class="mb-0"><?php echo htmlspecialchars($tpl['user']['name']); ?></p>
                                            </div>
                                        </div>

                                        <div class="row mb-3">
                                            <div class="col-md-4">
                                                <p class="text-muted mb-0">Email</p>
                                            </div>
                                            <div class="col-md-8">
                                                <p class="mb-0"><?php echo htmlspecialchars($tpl['user']['email']); ?></p>
                                            </div>
                                        </div>

                                        <div class="row mb-3">
                                            <div class="col-md-4">
                                                <p class="text-muted mb-0">Phone Number</p>
                                            </div>
                                            <div class="col-md-8">
                                                <p class="mb-0"><?php echo!empty($tpl['user']['phone_number']) ? htmlspecialchars($tpl['user']['phone_number']) : '<span class="text-muted">Not provided</span>'; ?></p>
                                            </div>
                                        </div>

                                        <div class="row mb-3">
                                            <div class="col-md-4">
                                                <p class="text-muted mb-0">Member Since</p>
                                            </div>
                                            <div class="col-md-8">
                                                <p class="mb-0"><?php echo date('F j, Y', $tpl['user']['created_at']); ?></p>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <div class="card shadow-sm mt-4">
                                    <div class="card-body">
                                        <h5 class="card-title mb-4">Address Information</h5>

                                        <div class="row mb-3">
                                            <div class="col-md-4">
                                                <p class="text-muted mb-0">Address</p>
                                            </div>
                                            <div class="col-md-8">
                                                <p class="mb-0"><?php echo!empty($tpl['user']['address']) ? htmlspecialchars($tpl['user']['address']) : '<span class="text-muted">Not provided</span>'; ?></p>
                                            </div>
                                        </div>

                                        <div class="row mb-3">
                                            <div class="col-md-4">
                                                <p class="text-muted mb-0">Country</p>
                                            </div>
                                            <div class="col-md-8">
                                                <p class="mb-0"><?php echo!empty($tpl['user']['country']) ? htmlspecialchars($tpl['user']['country']) : '<span class="text-muted">Not provided</span>'; ?></p>
                                            </div>
                                        </div>

                                        <div class="row mb-3">
                                            <div class="col-md-4">
                                                <p class="text-muted mb-0">Region</p>
                                            </div>
                                            <div class="col-md-8">
                                                <p class="mb-0"><?php echo!empty($tpl['user']['region']) ? htmlspecialchars($tpl['user']['region']) : '<span class="text-muted">Not provided</span>'; ?></p>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Delete Account Confirmation Modal -->
<div class="modal fade" id="deleteAccountModal" tabindex="-1" aria-labelledby="deleteAccountModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="deleteAccountModalLabel">Confirm Account Deletion</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div class="alert alert-danger">
                    <i class="mdi mdi-alert-circle me-2"></i>
                    Warning: This action cannot be undone. All your data will be permanently deleted.
                </div>
                <p>Please type CONFIRM to continue:</p>
                <input type="text" class="form-control" id="confirm" placeholder="CONFIRM">
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancel</button>
                <a href="<?php echo INSTALL_URL; ?>?controller=User&action=delete&id=<?php echo $tpl['user']['id']; ?>" 
                   class="btn btn-danger" id="confirmDeleteBtn" data-id="" disabled>
                    Delete Account
                </a>
            </div>
        </div>
    </div>
</div>

<!-- Custom CSS -->
<style>
    .profile-image-container {
        width: 180px;
        height: 180px;
        margin: 0 auto;
        overflow: hidden;
        border-radius: 50%; /* ✅ Прави контейнера кръгъл */
        display: flex;
        align-items: center;
        justify-content: center;
        cursor: pointer; /* ✅ Позволява кликане */
        background-color: #f8f9fa; /* ✅ Лек фон за placeholder */
    }

    .profile-img {
        width: 100%;
        height: 100%;
        object-fit: cover;
        border-radius: 50%; /* ✅ Гарантира, че изображението също е кръгло */
    }

    .placeholder-image {
        width: 100%;
        height: 100%;
        font-size: 80px; /* ✅ По-голяма икона */
        color: #6c757d;
        border-radius: 50%; /* ✅ Placeholder-ът също става кръгъл */
        display: flex;
        align-items: center;
        justify-content: center;
    }
</style>