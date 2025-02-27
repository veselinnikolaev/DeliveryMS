<div class="row">
    <div class="col-sm-12">
        <div class="home-tab">
            <div class="card card-rounded mt-3">
                <div class="card-body">
                    <h4 class="card-title">Create New Courier</h4>
                    
                    <?php if(isset($error_message)): ?>
                        <div class="alert alert-danger"><?php echo $error_message; ?></div>
                    <?php endif; ?>

                    <form class="forms-sample" method="POST" action="<?php echo INSTALL_URL; ?>?controller=Courier&action=create">
                        <input type="hidden" name="send" value="1" />
                        <div class="row">
                            <div class="form-group col-md-6 mb-3">
                                <label for="courierName" class="form-label">Courier Name</label>
                                <input type="text" class="form-control" id="courierName" name="name" required>
                            </div>
                        </div>
                        <div class="row">
                            <div class="form-group col-md-6 mb-3">
                                <label for="phoneNumber" class="form-label">Phone Number</label>
                                <input type="tel" pattern="^\d{10}$" class="form-control" id="phoneNumber" name="phone_number" required>
                            </div>
                        </div>
                        <div class="row">
                            <div class="form-group col-md-6 mb-3">
                                <label for="email" class="form-label">Email</label>
                                <input type="email" class="form-control" id="email" name="email" required>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-12">
                                <button type="submit" class="btn btn-primary text-white me-0">Create Courier</button>
                                <a href="<?php echo INSTALL_URL; ?>?controller=Courier&action=list" class="btn btn-outline-dark">Cancel</a>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
