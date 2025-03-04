<div class="container-scroller">
  <div class="row">
    <div class="col-sm-12">
      <div class="">
        <div class="d-sm-flex align-items-center justify-content-between border-bottom">
          <div>
            <div class="btn-wrapper">
              <a href="#" class="btn btn-outline-dark align-items-center"><i class="icon-share"></i> Share</a>
              <a href="#" class="btn btn-outline-dark align-items-center"><i class="icon-printer"></i> Print</a>
              <a href="<?php echo INSTALL_URL; ?>?controller=User&action=create"
                class="btn btn-primary text-white me-0"><i class="icon-plus"></i> New User</a>
            </div>
          </div>
        </div>

        <div class="card card-rounded mt-3">
          <div class="card shadow-sm mb-4">
            <div class="card-header bg-gradient-light py-3">
              <div class="d-flex justify-content-between align-items-center">
                <h5 class="card-title mb-0 font-weight-bold text-primary">Advanced Filters</h5>
                <button type="button" class="btn btn-primary btn-sm rounded-pill px-3 d-flex align-items-center"
                  data-bs-toggle="collapse" data-bs-target="#filters-container" aria-expanded="false"
                  aria-controls="filters-container">
                  <i class="fa fa-filter me-2"></i>
                  <span>Toggle Filters</span>
                </button>
              </div>
            </div>
            <div class="collapse show card-body" id="filters-container">
              <form id="user-filter-form">
                <div class="row">
                  <div class="col-md-4 mb-3">
                    <label for="filter-name" class="form-label">Name</label>
                    <input type="text" class="form-control" id="filter-name" placeholder="Search by name">
                  </div>
                  <div class="col-md-4 mb-3">
                    <label for="filter-email" class="form-label">Email</label>
                    <input type="text" class="form-control" id="filter-email" placeholder="Search by email">
                  </div>
                  <div class="col-md-4 mb-3">
                    <label for="filter-phone" class="form-label">Phone Number</label>
                    <input type="text" class="form-control" id="filter-phone" placeholder="Search by phone">
                  </div>
                  <div class="col-md-4 mb-3">
                    <label for="filter-role" class="form-label">Role</label>
                    <select class="form-control" id="filter-role">
                      <option value="">Select Role</option>
                      <option value="user">User</option>
                      <option value="admin">Admin</option>
                    </select>
                  </div>
                  <div class="col-md-4 mb-3">
                    <label for="filter-address" class="form-label">Address</label>
                    <input type="text" class="form-control" id="filter-address" placeholder="Search by address">
                  </div>
                  <div class="col-md-4 mb-3">
                    <label for="filter-country" class="form-label">Country</label>
                    <input type="text" class="form-control" id="filter-country" placeholder="Search by country">
                  </div>
                  <div class="col-md-4 mb-3">
                    <label for="filter-region" class="form-label">Region</label>
                    <input type="text" class="form-control" id="filter-region" placeholder="Search by region">
                  </div>
                </div>
                <div class="d-flex justify-content-end gap-2">
                  <button type="button" class="btn btn-light" id="reset-filters-user">
                    <i class="icon-refresh"></i> Reset
                  </button>
                  <button type="button" class="btn btn-primary" id="apply-filters-user">
                    <i class="icon-search"></i> Apply Filters
                  </button>
                </div>
              </form>
            </div>
          </div>
          <div class="card-body">
            <div class="table-responsive" id="container-user-id">
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

<div class="modal fade" id="deleteUser" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel"
  aria-hidden="true">
  <div class="modal-dialog" role="document">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title">Delete User</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close">
        </button>
      </div>
      <div class="modal-body">
        <p>Are you sure you want to delete this user?</p>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-danger" data-id="" id="delete-btn-user-id">Delete</button>
        <button type="button" class="btn btn-light" data-bs-dismiss="modal">Close</button>
      </div>
    </div>
  </div>
</div>

<!-- Role Change Modal -->
<div class="modal fade" id="roleModal" tabindex="-1" role="dialog" aria-labelledby="roleModalLabel" aria-hidden="true">
  <div class="modal-dialog" role="document">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="roleModalLabel">Change User Role</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <div class="modal-body">
        <p id="role-message"></p>
        <form id="role-form">
          <input type="hidden" id="user-id" name="id" value="">
          <input type="hidden" id="new-role" name="role" value="">
        </form>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-primary" id="confirm-role-change">Confirm</button>
        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
      </div>
    </div>
  </div>
</div>