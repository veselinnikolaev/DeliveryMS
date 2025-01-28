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
                                <input type="text" class="form-control" id="courierName" name="courier_name" required>
                            </div>
                        </div>
                        <div class="row">
                            <div class="form-group col-md-6 mb-3">
                                <label for="phoneNumber" class="form-label">Contact Number</label>
                                <input type="text" class="form-control" id="contactNumber" name="phone_number" required>
                            </div>
                        </div>
                        <div class="row">
                            <div class="form-group col-md-6 mb-3">
                                <label for="email" class="form-label">Email</label>
                                <input type="text" class="form-control" id="contactNumber" name="email" required>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-12">
                                <button type="submit" class="btn btn-primary me-2">Create Courier</button>
                                <a href="<?php echo INSTALL_URL; ?>?controller=Courier&action=list" class="btn btn-light">Cancel</a>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
