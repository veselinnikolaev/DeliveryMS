<div class="install-container">
    <div class="install-logo">
        <h1>DeliveryMS Installation</h1>
    </div>
    <div class="steps">
        <div class="step completed">1</div>
        <div class="step-line completed"></div>
        <div class="step completed">2</div>
        <div class="step-line completed"></div>
        <div class="step completed">3</div>
        <div class="step-line completed"></div>
        <div class="step completed">4</div>
    </div>
    <div class="card shadow-sm">
        <div class="card-body text-center">
            <div class="check-icon">
                <svg xmlns="http://www.w3.org/2000/svg" width="64" height="64" fill="currentColor" class="bi bi-check-circle" viewBox="0 0 16 16">
                    <path d="M8 15A7 7 0 1 1 8 1a7 7 0 0 1 0 14zm0 1A8 8 0 1 0 8 0a8 8 0 0 0 0 16z"/>
                    <path d="M10.97 4.97a.235.235 0 0 0-.02.022L7.477 9.417 5.384 7.323a.75.75 0 0 0-1.06 1.06L6.97 11.03a.75.75 0 0 0 1.079-.02l3.992-4.99a.75.75 0 0 0-1.071-1.05z"/>
                </svg>
            </div>
            <h2 class="card-title text-center mb-3">Installation Complete!</h2>
            <p class="card-text mb-4">Your application has been successfully installed and configured. You are all set!</p>

            <div class="alert alert-success mb-4" role="alert">
                <h4 class="alert-heading">Configuration files created</h4>
                <p>All configuration files have been created and saved successfully. Your database and mail server connections are properly set up.</p>
            </div>

            <div class="mb-4">
                <h5>Next Steps</h5>
                <ul class="list-group list-group-flush text-start">
                    <li class="list-group-item bg-transparent">Login to your admin dashboard using your email and password</li>
                    <li class="list-group-item bg-transparent">Configure your application settings</li>
                    <li class="list-group-item bg-transparent">Start adding content to your site</li>
                </ul>
            </div>

            <div class="d-grid gap-2">
                <a href="<?php echo INSTALL_URL; ?>" class="btn btn-primary btn-lg">Visit site</a>
            </div>
        </div>
    </div>
    <div class="mt-4 text-center text-muted">
        <small>Installation completed successfully! Thank you for choosing our application.</small>
    </div>
</div>