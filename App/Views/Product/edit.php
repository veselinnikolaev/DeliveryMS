<div class="row">
    <div class="col-sm-12">
        <div class="home-tab">
            <div class="card card-rounded mt-3">
                <div class="card-body">
                    <h4 class="card-title">Edit Product</h4>

                    <?php if (isset($error_message)): ?>
                        <div class="alert alert-danger"><?php echo $error_message; ?></div>
                    <?php endif; ?>
                    <form class="forms-sample" method="POST" action="<?php echo INSTALL_URL; ?>?controller=Product&action=edit">
                        <input type="hidden" name="id" value="<?php echo $tpl['id']; ?>" />
                        <div class="row">
                            <div class="form-group col-md-6 mb-3">
                                <label for="name" class="form-label">Product Name</label>
                                <input type="text" class="form-control" id="name" name="name" value="<?php echo $tpl['name'] ?>" required>
                            </div>
                        </div>
                        <div class="row">
                            <div class="form-group col-md-6 mb-3">
                                <label for="description" class="form-label">Description</label>
                                <input type="text" class="form-control" id="description" name="description" value="<?php echo $tpl['description'] ?>" required>
                            </div>
                        </div>
                        <div class="row">
                            <div class="form-group col-md-6 mb-3">
                                <label for="price" class="form-label">Price</label>
                                <div class="input-group">
                                    <span class="input-group-text"><?php echo $tpl['currency']; ?></span>
                                    <input type="number" step="0.01" min="0.01" class="form-control" id="price" name="price" value="<?php echo $tpl['price'] ?>" required>
                                </div>
                            </div>
                        </div>
                        <div class="row">
                            <div class="form-group col-md-6 mb-3">
                                <label for="stock" class="form-label">Stock</label>
                                <input type="number" step="1" min="1" class="form-control" id="stock" name="stock" value="<?php echo $tpl['stock'] ?>" required>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-12">
                                <button type="submit" class="btn btn-primary text-white me-0">Edit Product</button>
                                <a href="<?php echo INSTALL_URL; ?>?controller=Product&action=list" class="btn btn-outline-dark">Cancel</a>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>