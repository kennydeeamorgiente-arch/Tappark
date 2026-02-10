<div class="container-fluid">
    <!-- Enhanced Page Header -->
    <div class="card mb-4 border-0 shadow-sm">
        <div class="card-body py-4">
            <div class="d-flex justify-content-between align-items-center flex-wrap gap-3">
                <div>
                    <h2 class="mb-2 fw-bold">
                        <i class="fas fa-user-tie me-3 text-primary"></i>User Management
                    </h2>
                    <p class="mb-0 text-muted">Manage admin users and parking attendants</p>
                </div>
                <button class="btn btn-maroon" id="addAttendantBtn">
                    <i class="fas fa-user-plus me-2"></i>Add User
                </button>
            </div>
        </div>
    </div>

    <!-- Enhanced Stats Row -->
    <div class="row mb-4 g-3">
        <div class="col-xl-3 col-lg-3 col-md-6 col-sm-6">
            <div class="card stats-card-modern h-100 border-0 shadow-sm stats-card-maroon">
                <div class="card-body text-white position-relative overflow-hidden p-4">
                    <div class="d-flex align-items-center justify-content-between">
                        <div class="flex-grow-1">
                            <h5 class="mb-1 opacity-75 fw-semibold">Total Users</h5>
                            <h2 class="mb-0 fw-bold text-white" id="statTotalStaff">-</h2>
                            <small class="opacity-75 d-block">
                                <i class="fas fa-user-tie me-1"></i>
                                All users
                            </small>
                        </div>
                        <div class="stats-icon">
                            <i class="fas fa-user-tie fa-2x opacity-75"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-xl-3 col-lg-3 col-md-6 col-sm-6">
            <div class="card stats-card-modern h-100 border-0 shadow-sm stats-card-maroon">
                <div class="card-body text-white position-relative overflow-hidden p-4">
                    <div class="d-flex align-items-center justify-content-between">
                        <div class="flex-grow-1">
                            <h5 class="mb-1 opacity-75 fw-semibold">Active Users</h5>
                            <h2 class="mb-0 fw-bold text-white" id="statActiveStaff">-</h2>
                            <small class="opacity-75 d-block">
                                <i class="fas fa-check-circle me-1"></i>
                                Currently active
                            </small>
                        </div>
                        <div class="stats-icon">
                            <i class="fas fa-check-circle fa-2x opacity-75"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-xl-3 col-lg-3 col-md-6 col-sm-6">
            <div class="card stats-card-modern h-100 border-0 shadow-sm stats-card-gray">
                <div class="card-body text-white position-relative overflow-hidden p-4">
                    <div class="d-flex align-items-center justify-content-between">
                        <div class="flex-grow-1">
                            <h5 class="mb-1 opacity-75 fw-semibold">Online Now</h5>
                            <h2 class="mb-0 fw-bold text-white" id="statOnlineStaff">-</h2>
                            <small class="opacity-75 d-block">
                                <i class="fas fa-circle me-1"></i>
                                Online status
                            </small>
                        </div>
                        <div class="stats-icon">
                            <i class="fas fa-circle fa-2x opacity-75"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-xl-3 col-lg-3 col-md-6 col-sm-6">
            <div class="card stats-card-modern h-100 border-0 shadow-sm stats-card-maroon">
                <div class="card-body text-white position-relative overflow-hidden p-4">
                    <div class="d-flex align-items-center justify-content-between">
                        <div class="flex-grow-1">
                            <h5 class="mb-1 opacity-75 fw-semibold">Inactive</h5>
                            <h2 class="mb-0 fw-bold text-white" id="statInactiveStaff">-</h2>
                            <small class="opacity-75 d-block">
                                <i class="fas fa-pause-circle me-1"></i>
                                Inactive status
                            </small>
                        </div>
                        <div class="stats-icon">
                            <i class="fas fa-pause-circle fa-2x opacity-75"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Shared Filters Component -->
    <?= view('partials/filters/shared_filters') ?>

    <!-- Staff List Table -->
    <div class="card shadow-sm">
        <div class="card-header">
            <div class="d-flex justify-content-between align-items-center flex-wrap gap-3">
                <div>
                    <h5 class="mb-0">Staff List</h5>
                    <small class="text-muted" id="tableInfo">Loading staff members...</small>
                </div>
                <div class="d-flex align-items-center gap-2">
                    <label class="mb-0">Per Page:</label>
                    <select class="form-select form-select-sm" style="min-width: 80px; width: auto;" id="perPageSelect">
                        <option value="10">10</option>
                        <option value="25" selected>25</option>
                        <option value="50">50</option>
                        <option value="100">100</option>
                    </select>
                    <button class="btn btn-success btn-sm" id="exportAttendantsBtn">
                        <i class="fas fa-file-excel me-2"></i>Export Staff to CSV
                    </button>
                </div>
            </div>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-hover" id="attendantsTable">
                    <thead class="table-light">
                        <tr>
                            <th class="sortable" data-column="user_id">ID <i class="fas fa-sort text-muted ms-1"></i></th>
                            <th class="sortable" data-column="name">Name <i class="fas fa-sort text-muted ms-1"></i></th>
                            <th class="sortable" data-column="email">Email <i class="fas fa-sort text-muted ms-1"></i></th>
                            <th class="sortable" data-column="role">Role <i class="fas fa-sort text-muted ms-1"></i></th>
                            <th class="sortable" data-column="area">Assigned Area <i class="fas fa-sort text-muted ms-1"></i></th>
                            <th class="sortable" data-column="status">Status <i class="fas fa-sort text-muted ms-1"></i></th>
                            <th class="sortable" data-column="online">Online <i class="fas fa-sort text-muted ms-1"></i></th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody id="attendantTableBody">
                        <tr>
                            <td colspan="8" class="text-center py-5">
                                <div class="spinner-border text-primary" role="status">
                                    <span class="visually-hidden">Loading...</span>
                                </div>
                                <p class="mt-2 text-muted">Loading staff members...</p>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
        <div class="card-footer">
            <div class="d-flex justify-content-between align-items-center">
                <div id="paginationInfo"></div>
                <nav>
                    <ul class="pagination mb-0" id="paginationControls"></ul>
                </nav>
            </div>
        </div>
    </div>
</div>

<!-- Load CSS -->
<link rel="stylesheet" href="<?= base_url('assets/css/attendants.css') ?>">

<!-- Load JavaScript -->
<script src="<?= base_url('assets/js/attendants.js') ?>"></script>
