<div class="install-container">
    <div class="install-logo">
        <h1>DeliveryMS Installation</h1>
    </div>
    <div class="card shadow-sm">
        <div class="card-body">
            <h2 class="card-title text-center mb-4">Welcome to the Installation Wizard</h2>
            <p class="card-text">This wizard will guide you through the installation process of the application. Please make sure you have the following information ready:</p>
            <ul class="list-group list-group-flush mb-4">
                <li class="list-group-item">Database connection details (hostname, username, password)</li>
                <li class="list-group-item">Admin account information</li>
                <li class="list-group-item">Mail server configuration</li>
            </ul>
            <p class="card-text">The installation process consists of 4 steps and should take about 5 minutes to complete.</p>
            <div class="d-grid gap-2">
                <a href="<?php echo INSTALL_URL; ?>?controller=Install&action=step1" class="btn btn-primary btn-lg">Start Installation</a>
            </div>
        </div>
    </div>
    <div class="mt-4 text-center text-muted">
        <small>If you need help, please refer to the <a href="#">documentation</a> or contact <a href="#">support</a>.</small>
    </div>
</div>