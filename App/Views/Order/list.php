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
                            <form id="order-filter-form">
                                <div class="row">
                                    <div class="col-md-4 mb-3">
                                        <label for="filter-customer" class="form-label">Customer Name</label>
                                        <input type="text" class="form-control" id="filter-customer" placeholder="Search by customer name">
                                    </div>
                                    <div class="col-md-4 mb-3">
                                        <label for="filter-courier" class="form-label">Courier Name</label>
                                        <input type="text" class="form-control" id="filter-courier" placeholder="Search by courier name">
                                    </div>
                                    <div class="col-md-4 mb-3">
                                        <label for="filter-status" class="form-label">Status</label>
                                        <select class="form-control" id="filter-status">
                                            <option value="">Select Status</option>
                                            <?php foreach (Utility::$order_status as $k => $v) { ?>
                                                <option value="<?php echo $k; ?>"><?php echo $v; ?></option>
                                            <?php } ?>
                                        </select>
                                    </div>
                                    <div class="col-md-4 mb-3">
                                        <label for="filter-tracking" class="form-label">Tracking Number</label>
                                        <input type="text" class="form-control" id="filter-tracking" placeholder="Search by tracking number">
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
                                        <label for="filter-date-from" class="form-label">Order Date From</label>
                                        <input type="date" class="form-control" id="filter-date-from">
                                    </div>
                                    <div class="col-md-4 mb-3">
                                        <label for="filter-date-to" class="form-label">Order Date To</label>
                                        <input type="date" class="form-control" id="filter-date-to">
                                    </div>
                                    <div class="col-md-4 mb-3">
                                        <label for="filter-delivery-date" class="form-label">Delivery Date</label>
                                        <input type="date" class="form-control" id="filter-delivery-date">
                                    </div>
                                    <div class="col-md-4 mb-3">
                                        <label for="filter-price-min" class="form-label">Min Total Price (inclusive)</label>
                                        <div class="input-group">
                                            <span class="input-group-text"><?php echo $tpl['currency']; ?></span>
                                            <input type="number" step="0.01" min="0" class="form-control" id="filter-price-min" placeholder="Minimum price">
                                        </div>
                                    </div>
                                    <div class="col-md-4 mb-3">
                                        <label for="filter-price-max" class="form-label">Max Total Price (inclusive)</label>
                                        <div class="input-group">
                                            <span class="input-group-text"><?php echo $tpl['currency']; ?></span>
                                            <input type="number" step="0.01" min="0" class="form-control" id="filter-price-max" placeholder="Maximum price">
                                        </div>
                                    </div>
                                </div>
                                <div class="d-flex justify-content-end gap-2 mt-3">
                                    <button type="button" class="btn btn-light" id="reset-filters-order">
                                        <i class="icon-refresh"></i> Reset
                                    </button>
                                    <button type="button" class="btn btn-primary" id="apply-filters-order">
                                        <i class="icon-search"></i> Apply Filters
                                    </button>
                                </div>
                            </form>
                        </div>
                    </div>
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
