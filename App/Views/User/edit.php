<div class="row">
    <div class="col-sm-12">
        <div class="home-tab">
            <div class="card card-rounded mt-3">
                <div class="card-body">
                    <h4 class="card-title">Edit User</h4>
                    
                    <?php if(isset($error_message)): ?>
                        <div class="alert alert-danger"><?php echo $error_message; ?></div>
                    <?php endif; ?>
                    <form class="forms-sample" method="POST" action="<?php echo INSTALL_URL; ?>?controller=User&action=edit">
                        <input type="hidden" name="id" value="<?php echo $tpl['id']; ?>" />
                        <div class="row">
                            <div class="form-group col-md-6 mb-3">
                                <label for="fullName" class="form-label">Full Name</label>
                                <input type="text" class="form-control" id="fullName" name="name" value="<?php echo $tpl['name'] ?>" required>
                            </div>
                        </div>   
                        <div class="row">
                            <div class="form-group col-md-6 mb-3">
                                <label for="email" class="form-label">Email</label>
                                <input type="email" class="form-control" id="email" name="email" value="<?php echo $tpl['email'] ?>" required>
                            </div>
                        </div>
                        <div class="row">
                            <div class="form-group col-md-6 mb-3">
                                <label for="phoneNumber" class="form-label">Phone Number</label>
                                <input type="tel" pattern="^\d{10}$" class="form-control" id="phoneNumber" name="phone_number" value="<?php echo $tpl['phone_number'] ?>" required>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-12">
                                <button type="submit" class="btn btn-primary text-white me-0">Edit User</button>
                                <a href="<?php echo INSTALL_URL; ?>?controller=User&action=list" class="btn btn-outline-dark">Cancel</a>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
