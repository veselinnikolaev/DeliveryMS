<div class="container-scroller">
    <div class="row">
        <div class="col-sm-12">
            <div class="">
                <div class="d-sm-flex align-items-center justify-content-between border-bottom">
                    <div>
                        <div class="btn-wrapper">
                            <a id="share-couriers" class="btn btn-outline-dark align-items-center"><i class="icon-share"></i>
                                Share</a>
                            <a id="print-couriers" class="btn btn-outline-dark align-items-center"><i class="icon-printer"></i>
                                Print</a>
                            <a href="<?php echo INSTALL_URL; ?>?controller=Courier&action=create"
                               class="btn btn-primary text-white me-0"><i class="icon-plus"></i> New Courier</a>
                        </div>
                    </div>
                </div>
                <div class="card card-rounded mt-3">
                    <div class="card shadow-sm mb-4">
                        <div class="card-header bg-gradient-light py-3">
                            <div class="d-flex justify-content-between align-items-center">
                                <h5 class="card-title mb-0 font-weight-bold text-primary">Advanced Filters</h5>
                                <button type="button"
                                        class="btn btn-primary btn-sm rounded-pill px-3 d-flex align-items-center"
                                        data-bs-toggle="collapse" data-bs-target="#filters-container" aria-expanded="false"
                                        aria-controls="filters-container">
                                    <i class="fa fa-filter me-2"></i>
                                    <span>Toggle Filters</span>
                                </button>
                            </div>
                        </div>
                        <div class="collapse show card-body" id="filters-container">
                            <form id="courier-filter-form">
                                <div class="card p-4">
                                    <div class="row">
                                        <div class="col-md-4 mb-3">
                                            <label for="filter-name" class="form-label">Courier Name</label>
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
                                            <label for="filter-country" class="form-label">Country</label>
                                            <input type="text" class="form-control" id="filter-country" placeholder="Search by country">
                                        </div>
                                        <div class="col-md-4 mb-3">
                                            <label for="filter-region" class="form-label">Region</label>
                                            <input type="text" class="form-control" id="filter-region" placeholder="Search by region">
                                        </div>
                                        <div class="col-md-4 mb-3">
                                            <label for="filter-address" class="form-label">Address</label>
                                            <input type="text" class="form-control" id="filter-address" placeholder="Search by address">
                                        </div>
                                    </div>
                                    <div class="d-flex justify-content-end gap-2">
                                        <button type="button" class="btn btn-light" id="reset-filters-courier">
                                            <i class="icon-refresh"></i> Reset
                                        </button>
                                        <button type="button" class="btn btn-primary" id="apply-filters-courier">
                                            <i class="icon-search"></i> Apply Filters
                                        </button>
                                    </div>
                            </form>
                        </div>
                    </div>
                </div>
                <div class="card-body">
                    <?php if (in_array($_SESSION['user']['role'], ['admin', 'root'])) { ?>
                        <div class="d-flex justify-content-between align-items-center">
                            <div>
                                <button id="bulk-delete-couriers-btn" class="btn btn-danger d-none">
                                    <i class="fa fa-trash"></i> Delete Selected (<span id="selected-count-couriers">0</span>)
                                </button>
                            </div>
                        </div>
                    <?php } ?>
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

<div class="modal fade" id="deleteCourier" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel"
     aria-hidden="true">
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

<div class="modal fade" id="deleteCouriers" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel"
     aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Delete Couriers</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close">
                </button>
            </div>
            <div class="modal-body">
                <p>Are you sure you want to delete these couriers?</p>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-danger" data-ids="" id="delete-btn-couriers-ids">Delete</button>
                <button type="button" class="btn btn-light" data-bs-dismiss="modal">Close</button>
            </div>
        </div>
    </div>
</div>

<!-- Share Modal -->
<div class="modal fade" id="couriersShareModal" tabindex="-1" role="dialog" aria-labelledby="shareModalLabel" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Export Products</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div class="d-grid gap-3">
                    <button type="button" class="btn btn-outline-primary export-format-couriers" data-format="pdf">
                        <i class="icon-file-pdf"></i> Export as PDF
                    </button>
                    <button type="button" class="btn btn-outline-success export-format-couriers" data-format="excel">
                        <i class="icon-file-excel"></i> Export as Excel
                    </button>
                    <button type="button" class="btn btn-outline-info export-format-couriers" data-format="csv">
                        <i class="icon-file-text"></i> Export as CSV
                    </button>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-light" data-bs-dismiss="modal">Close</button>
            </div>
        </div>
    </div>
</div>
