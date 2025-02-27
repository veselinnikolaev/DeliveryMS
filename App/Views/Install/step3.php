<div class="install-container">
    <div class="install-logo">
        <h1>DeliveryMS Installation</h1>
    </div>
    <div class="steps">
        <div class="step completed">1</div>
        <div class="step-line completed"></div>
        <div class="step completed">2</div>
        <div class="step-line completed"></div>
        <div class="step active">3</div>
        <div class="step-line"></div>
        <div class="step">4</div>
    </div>
    <div class="card shadow-sm">
        <div class="card-body">
            <h2 class="card-title text-center mb-4">Mail Configuration</h2>
            <p class="card-text mb-4">Please enter your mail server details below:</p>

            <?php if (isset($tpl['error_message'])): ?>
                <div class="alert alert-danger" role="alert">
                    <?php echo $tpl['error_message']; ?>
                </div>
            <?php endif; ?>

            <form action="<?php echo INSTALL_URL; ?>?controller=Install&action=step3" method="POST">
                <div class="mb-3">
                    <label for="mail_host" class="form-label">Mail Host</label>
                    <input type="text" class="form-control" id="mail_host" name="mail_host" placeholder="smtp.example.com" required>
                </div>
                <div class="mb-3">
                    <label for="mail_port" class="form-label">Mail Port</label>
                    <input type="number" class="form-control" id="mail_port" name="mail_port" placeholder="587" required>
                    <div class="form-text">Common ports: 25, 465, 587, 2525</div>
                </div>
                <div class="mb-3">
                    <label for="mail_username" class="form-label">Mail Username</label>
                    <input type="text" class="form-control" id="mail_username" name="mail_username" required>
                </div>
                <div class="mb-3">
                    <label for="mail_password" class="form-label">Mail Password</label>
                    <input type="password" class="form-control" id="mail_password" name="mail_password" required>
                </div>

                <div class="d-flex justify-content-between mt-4">
                    <a href="<?php echo INSTALL_URL; ?>?controller=Install&action=step2" class="btn btn-secondary">Back</a>
                    <button type="submit" class="btn btn-primary">Next Step</button>
                </div>
            </form>
        </div>
    </div>
    <div class="mt-4 text-center text-muted">
        <small>Step 3 of 4 - Mail Configuration</small>
    </div>
</div>