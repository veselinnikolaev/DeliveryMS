<div class="row">
    <div class="col-sm-12">
        <div class="home-tab">
            <div class="card card-rounded mt-3">
                <div class="card-body">
                    <h4 class="card-title">Register</h4>

                    <?php if (isset($error_message)): ?>
                        <div class="alert alert-danger"><?php echo $error_message; ?></div>
                    <?php endif; ?>

                    <form class="forms-sample" method="POST" action="<?php echo INSTALL_URL; ?>?controller=Auth&action=register">
                        <input type="hidden" name="send" value="1" />

                        <div class="row">
                            <div class="form-group col-md-6 mb-3">
                                <label for="fullName" class="form-label">Full Name*</label>
                                <input type="text" class="form-control" id="fullName" name="name" required>
                            </div>
                            <div class="form-group col-md-6 mb-3">
                                <label for="address" class="form-label">Address</label>
                                <input type="text" class="form-control" id="address" name="address">
                            </div>
                        </div>

                        <div class="row">
                            <div class="form-group col-md-6 mb-3">
                                <label for="email" class="form-label">Email*</label>
                                <input type="email" class="form-control" id="email" name="email" required>
                            </div>
                            <div class="form-group col-md-6 mb-3">
                                <label for="country" class="form-label">Country</label>
                                <input type="text" class="form-control" id="country" name="country">
                            </div>
                        </div>

                        <div class="row">
                            <div class="form-group col-md-6 mb-3">
                                <label for="phoneNumber" class="form-label">Phone Number*</label>
                                <input type="tel" pattern="^\d{10}$" class="form-control" id="phoneNumber" name="phone_number" required>
                            </div>
                            <div class="form-group col-md-6 mb-3">
                                <label for="region" class="form-label">Region</label>
                                <input type="text" class="form-control" id="region" name="region">
                            </div>
                        </div>

                        <div class="row">
                            <div class="form-group col-md-6 mb-3">
                                <label for="password" class="form-label">Password*</label>
                                <div class="position-relative">
                                    <input type="password" class="form-control pe-5" id="password" name="password" required>
                                    <i class="password-toggle-icon fa fa-eye" data-target="password"></i>
                                </div>
                            </div>
                        </div>

                        <div class="row">
                            <div class="form-group col-md-6 mb-3">
                                <label for="repeatPassword" class="form-label">Repeat Password*</label>
                                <div class="position-relative">
                                    <input type="password" class="form-control pe-5" id="repeatPassword" name="repeat_password" required>
                                    <i class="password-toggle-icon fa fa-eye" data-target="repeatPassword"></i>
                                </div>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-12">
                                <button type="submit" class="btn btn-primary text-white me-0">Register</button>
                                <a href="<?php echo INSTALL_URL; ?>" class="btn btn-outline-dark">Cancel</a>
                            </div>
                        </div>
                    </form>

                    <p class="mt-3">Already have an account? <a href="<?php echo INSTALL_URL; ?>?controller=Auth&action=login">Log in here</a></p>
                </div>
            </div>
        </div>
    </div>
</div>
