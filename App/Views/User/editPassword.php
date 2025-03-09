<div class="row">
    <div class="col-sm-12">
        <div class="home-tab">
            <div class="d-sm-flex align-items-center justify-content-between border-bottom">
                <ul class="nav nav-tabs" role="tablist">
                    <li class="nav-item">
                        <a class="nav-link" href="<?php echo INSTALL_URL; ?>?controller=User&action=profile&id=<?php echo isset($_GET['id']) ? htmlspecialchars($_GET['id']) : $tpl['id']; ?>">My Profile</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link active ps-0" href="#">Change Password</a>
                    </li>
                </ul>
            </div>
            <div class="card card-rounded mt-3">
                <div class="card-body">
                    <h4 class="card-title">Change Password</h4>

                    <?php if (isset($tpl['error_message'])): ?>
                        <div class="alert alert-danger"><?php echo $tpl['error_message']; ?></div>
                    <?php endif; ?>

                    <form class="forms-sample" method="POST" action="<?php echo INSTALL_URL; ?>?controller=User&action=editPassword">
                        <input type="hidden" name="id" value="<?php echo isset($_GET['id']) ? htmlspecialchars($_GET['id']) : $tpl['id']; ?>">

                        <div class="row">
                            <div class="form-group col-md-6 mb-3">
                                <label for="password" class="form-label">New Password*</label>
                                <div class="position-relative">
                                    <input type="password" class="form-control pe-5" id="password" name="password" required>
                                    <i class="password-toggle-icon fa fa-eye" data-target="password"></i>
                                </div>
                            </div>
                        </div>

                        <div class="row">
                            <div class="form-group col-md-6 mb-3">
                                <label for="repeatPassword" class="form-label">Repeat New Password*</label>
                                <div class="position-relative">
                                    <input type="password" class="form-control pe-5" id="repeatPassword" name="repeat_password" required>
                                    <i class="password-toggle-icon fa fa-eye" data-target="repeatPassword"></i>
                                </div>
                            </div>
                        </div>

                        <div class="row mt-4">
                            <div class="col-12">
                                <button type="submit" class="btn btn-primary text-white me-2">Update Password</button>
                                <a href="<?php echo INSTALL_URL; ?>?controller=User&action=profile&id=<?php echo isset($_GET['id']) ? htmlspecialchars($_GET['id']) : $tpl['id']; ?>" class="btn btn-outline-dark">Cancel</a>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>