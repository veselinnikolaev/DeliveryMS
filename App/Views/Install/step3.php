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
                    <input type="text" class="form-control" id="mailHost" name="mail_host" placeholder="smtp.example.com" 
                           value="<?php
                           if (MAIL_HOST != '{mail_host}') {
                               echo MAIL_HOST;
                           }
                           ?>"  required>
                </div>
                <div class="mb-3">
                    <label for="mail_port" class="form-label">Mail Port</label>
                    <input type="number" class="form-control" id="mailPort" name="mail_port" placeholder="587"
                           value="<?php
                           if (MAIL_PORT != '{mail_port}') {
                               echo MAIL_PORT;
                           }
                           ?>" required>
                    <div class="form-text">Common ports: 25, 465, 587, 2525</div>
                </div>
                <div class="mb-3">
                    <label for="mail_username" class="form-label">Mail Username</label>
                    <input type="text" class="form-control" id="mailUsername" name="mail_username" 
                           value="<?php
                           if (MAIL_USERNAME != '{mail_username}') {
                               echo MAIL_USERNAME;
                           }
                           ?>" required>
                </div>
                <div class="mb-3">
                    <label for="mail_password" class="form-label">Mail Password</label>
                    <div class="position-relative">
                        <input type="password" class="form-control" id="mailPassword" name="mail_password" required>
                        <i class="password-toggle-icon fa fa-eye" data-target="mailPassword"></i>
                    </div>
                </div>

                <div class="d-flex justify-content-between mt-4">
                    <a href="<?php
                    if (!INSTALLED) {
                        echo INSTALL_URL . "?controller=Install&action=step2";
                    } else {
                        echo INSTALL_URL;
                    }
                    ?>" class="btn btn-secondary">Back</a>
                       <?php
                       if (!INSTALLED) {
                           echo '<a class="btn btn-warning skip-mail-config">Skip</a>';
                       }
                       ?>
                    <button type="submit" class="btn btn-primary">Next Step</button>
                </div>
            </form>
        </div>
    </div>
    <div class="mt-4 text-center text-muted">
        <small>Step 3 of 4 - Mail Configuration</small>
    </div>
</div>

<div class="modal fade" id="skipMailConfig" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Skip Mail Configuration</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <p>Are you sure you want to skip mail configuration?</p>
                <p>This means you won’t be able to send system emails until configured later.</p>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-warning" id="skip-mail-config-button">Skip</button>
                <button type="button" class="btn btn-light" data-bs-dismiss="modal">Close</button>
            </div>
        </div>
    </div>
</div>