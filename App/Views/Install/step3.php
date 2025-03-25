<div class="install-container">
    <div class="install-logo">
        <h1>DeliveryMS Installation</h1>
    </div>
    <div class="steps">
        <div class="step completed">1</div>
        <div class="step-line"></div>
        <div class="step completed">2</div>
        <div class="step-line"></div>
        <div class="step active">3</div>
        <div class="step-line"></div>
        <div class="step">4</div>
        <div class="step-line"></div>
        <div class="step">5</div>
    </div>
    <div class="card shadow-sm mx-auto">
        <div class="card-body">
            <h2 class="card-title text-center mb-4">PayPal Account Settings</h2>
            <p class="card-text mb-4">Please enter your PayPal business account details:</p>

            <form action="<?php echo INSTALL_URL; ?>?controller=Install&action=step3" method="POST">
                <div class="mb-3">
                    <label for="paypal_business_email" class="form-label">PayPal Business Email</label>
                    <input type="email" class="form-control" id="paypalBusinessEmail" name="paypal_email" 
                           value="<?php
                           if (!empty($tpl['paypal_email'])) {
                               echo $tpl['paypal_email'];
                           }
                           ?>" required>
                    <div class="form-text">The email address associated with your PayPal business account</div>
                </div>
                <div class="mb-3">
                    <div class="alert alert-info" role="alert">
                        <i class="fa fa-info-circle me-2"></i>
                        Don't have a PayPal Business account yet? 
                        <a href="https://www.paypal.com/bg/business/getting-started" target="_blank" class="alert-link">
                            Click here to create one
                        </a>
                    </div>
                </div>

                <div class="d-flex justify-content-between mt-4">
                    <a href="<?php echo INSTALL_URL; ?>?controller=Install&action=step2" class="btn btn-secondary">Back</a>
                    <button type="submit" class="btn btn-primary">Next Step</button>
                </div>
            </form>
        </div>
    </div>
    <div class="mt-4 text-center text-muted">
        <small>Step 3 of 5 - PayPal Configuration</small>
    </div>
</div>
