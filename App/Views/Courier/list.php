<div class="container-scroller">
    <div class="row">
        <div class="col-sm-12">
            <div class="">
                <div class="d-sm-flex align-items-center justify-content-between border-bottom">
                    <div>
                        <div class="btn-wrapper">
                            <a href="#" class="btn btn-outline-dark align-items-center"><i class="icon-share"></i> Share</a>
                            <a href="#" class="btn btn-outline-dark align-items-center"><i class="icon-printer"></i> Print</a>
                            <a href="<?php echo INSTALL_URL; ?>?controller=Courier&action=create" class="btn btn-primary text-white me-0"><i class="icon-plus"></i> New Courier</a>
                        </div>
                    </div>
                </div>
                <div class="card card-rounded mb-3">
                <div class="card card-header bg-light">
                  <div class="d-flex justify-content-between align-items-center">
                    <h5 class="mb-0">Advanced Filters</h5>
                    <button type="button" class="btn btn-sm btn-outline-primary" 
                            data-bs-toggle="collapse" 
                            data-bs-target="#filters-container"
                            aria-expanded="true" 
                            aria-controls="filters-container">
                      <i class="icon-filter"></i> Toggle Filters
                    </button>
                  </div>
                </div>
                <div class="collapse show card-body" id="filters-container">
                    <form id="courier-filter-form">
                      <div class="row">
                        <div class="col-md-4 mb-3">
                          <label for="filter-name" class="form-label">Courier Name</label>
                          <input type="text" class="form-control" id="filter-name" placeholder="Search by name">
                        </div>
                        <div class="col-md-4 mb-3">
                          <label for="filter-phone" class="form-label">Phone Number</label>
                          <input type="text" class="form-control" id="filter-phone" placeholder="Search by phone">
                        </div>
                        <div class="col-md-4 mb-3">
                          <label for="filter-email" class="form-label">Email</label>
                          <input type="email" class="form-control" id="filter-email" placeholder="Search by email">
                        </div>
                      </div>                                     
                      <div class="d-flex justify-content-end gap-2">
                        <button type="button" class="btn btn-light" id="reset-filters">
                          <i class="icon-refresh"></i> Reset
                        </button>
                        <button type="button" class="btn btn-primary" id="apply-filters">
                          <i class="icon-search"></i> Apply Filters
                        </button>
                      </div>
                    </form>
                  </div>
                </div>
                <div class="card card-rounded mt-3">
                    <div class="card-body">
                        <div class="table-responsive" id="container-courier-id">
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

<div class="modal fade" id="deleteCourier" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel" aria-hidden="true">
  <div class="modal-dialog" role="document">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title">Delete Courier</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close">
        </button>
      </div>
      <div class="modal-body">
        <p>Are you sure you want to delete this courier?</p>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-danger" data-id="" id="delete-btn-courier-id">Delete</button>
        <button type="button" class="btn btn-light" data-bs-dismiss="modal">Close</button>
      </div>
    </div>
  </div>
</div>