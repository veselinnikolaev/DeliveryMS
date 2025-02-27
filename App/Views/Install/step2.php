<div class="install-container">
    <div class="install-logo">
        <h1>DeliveryMS Installation</h1>
    </div>
    <div class="steps">
        <div class="step completed">1</div>
        <div class="step-line completed"></div>
        <div class="step active">2</div>
        <div class="step-line"></div>
        <div class="step">3</div>
        <div class="step-line"></div>
        <div class="step">4</div>
    </div>
    <div class="card shadow-sm">
        <div class="card-body">
            <h2 class="card-title text-center mb-4">Create Admin Account</h2>
            <p class="card-text mb-4">Please create an administrator account for the application:</p>

            <?php if (!empty($error_message)): ?>
                <div class="alert alert-danger" role="alert">
                    <?php echo $error_message; ?>
                </div>
            <?php endif; ?>

            <form action="<?php echo INSTALL_URL; ?>?controller=Install&action=step2" method="POST">
                <div class="mb-3">
                    <label for="admin_name" class="form-label">Admin Name</label>
                    <input type="text" class="form-control" id="admin_name" name="admin_name" required>
                </div>
                <div class="mb-3">
                    <label for="admin_email" class="form-label">Admin Email</label>
                    <input type="email" class="form-control" id="admin_email" name="admin_email" required>
                    <div class="form-text">This email will be used for login and important notifications</div>
                </div>
                <div class="mb-3">
                    <label for="admin_password" class="form-label">Admin Password</label>
                    <div class="position-relative">
                        <input type="password" class="form-control" id="adminPassword" name="admin_password" required>
                        <i class="password-toggle-icon fa fa-eye" data-target="adminPassword"></i>
                    </div>
                </div>
                <div class="mb-3">
                    <label for="admin_password_confirm" class="form-label">Confirm Password</label>
                    <div class="position-relative">
                        <input type="password" class="form-control" id="adminPasswordConfirm" name="admin_password_confirm" required>
                        <i class="password-toggle-icon fa fa-eye" data-target="adminPasswordConfirm"></i>
                    </div>
                </div>

                <div class="d-flex justify-content-between mt-4">
                    <a href="<?php echo INSTALL_URL; ?>?controller=Install&action=step1" class="btn btn-secondary">Back</a>
                    <button type="submit" class="btn btn-primary">Next Step</button>
                </div>
            </form>
        </div>
    </div>
    <div class="mt-4 text-center text-muted">
        <small>Step 2 of 4 - Admin Account Creation</small>
    </div>
</div>
<script>
    document.querySelector('form').addEventListener('submit', function (e) {
        const password = document.getElementById('admin_password').value;
        const confirm = document.getElementById('admin_password_confirm').value;

        if (password !== confirm) {
            e.preventDefault();
            alert('Passwords do not match.');
            return false;
        }

        return true;
    });
</script>