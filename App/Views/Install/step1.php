<div class="install-container">
    <div class="install-logo">
        <h1>DeliveryMS Installation</h1>
    </div>
    <div class="steps">
        <div class="step completed">1</div>
        <div class="step-line"></div>
        <div class="step">2</div>
        <div class="step-line"></div>
        <div class="step">3</div>
        <div class="step-line"></div>
        <div class="step">4</div>
    </div>
    <div class="card shadow-sm">
        <div class="card-body">
            <h2 class="card-title text-center mb-4">Database Configuration</h2>
            <p class="card-text mb-4">Please enter your database connection details below:</p>

            <form action="<?php echo INSTALL_URL; ?>?controller=Install&action=step1" method="POST">
                <div class="mb-3">
                    <label for="hostname" class="form-label">Database Hostname</label>
                    <input type="text" class="form-control" id="hostname" name="hostname" placeholder="localhost" required>
                    <div class="form-text">Usually "localhost" or an IP address</div>
                </div>
                <div class="mb-3">
                    <label for="username" class="form-label">Connection Username</label>
                    <input type="text" class="form-control" id="username" name="username" required>
                </div>
                <div class="mb-3">
                    <label for="password" class="form-label">Connection Password</label>
                    <input type="password" class="form-control" id="password" name="password">
                </div>
                <div class="mb-3">
                    <label for="database" class="form-label">Database Name</label>
                    <input type="text" class="form-control" id="database" name="database" required>
                </div>

                <div class="d-flex justify-content-between mt-4">
                    <a href="<?php echo INSTALL_URL; ?>?controller=Install&action=step0" class="btn btn-secondary">Back</a>
                    <button type="submit" class="btn btn-primary">Next Step</button>
                </div>
            </form>
        </div>
    </div>
    <div class="mt-4 text-center text-muted">
        <small>Step 1 of 4 - Database Configuration</small>
    </div>
</div>