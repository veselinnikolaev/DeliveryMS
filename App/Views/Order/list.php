<div class="container-scroller">
    <div class="row">
        <div class="col-sm-12">
            <div>
                <div class="d-sm-flex align-items-center justify-content-between border-bottom">
                    <div>
                        <div class="btn-wrapper">
                            <a href="#" class="btn btn-outline-dark align-items-center"><i class="icon-share"></i> Share</a>
                            <a href="#" class="btn btn-outline-dark align-items-center"><i class="icon-printer"></i> Print</a>
                            <?php if ($_SESSION['user']['role'] == 'admin') { ?>
                            <a href="<?php echo INSTALL_URL; ?>?controller=Order&action=create" class="btn btn-primary text-white me-0"><i class="icon-plus"></i> New Order</a>
                            <?php } ?>
                        </div>
                    </div>
                </div>

                <div class="card card-rounded mt-3">
                    <div class="card-body">
                        <div class="table-responsive" id="container-order-id">
                            <?php
                            include 'component/table.php';
                            ?>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="modal fade" id="deleteOrder" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel" aria-hidden="true">
  <div class="modal-dialog" role="document">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title">Delete Order</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <div class="modal-body">
        <p>Are you sure you want to delete this order?</p>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-danger" data-id="" id="delete-btn-order-id">Delete</button>
        <button type="button" class="btn btn-light" data-bs-dismiss="modal">Close</button>
      </div>
    </div>
  </div>
</div>
