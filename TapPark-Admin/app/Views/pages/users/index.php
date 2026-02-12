<?php $globalPerPage = session('app_settings')['records_per_page'] ?? 25; ?>
<div class="container-fluid">
    <!-- Enhanced Page Header -->
    <div class="card mb-4 border-0 shadow-sm">
        <div class="card-body py-4">
            <div class="d-flex justify-content-between align-items-center flex-wrap gap-3">
                <div>
                    <h2 class="mb-2 fw-bold">
                        <i class="fas fa-users-cog me-3 text-primary"></i>User Management
                    </h2>
                    <p class="mb-0 text-muted">Manage subscribers, walk-in guests, and staff members</p>
                </div>
                <div class="dropdown">
                    <button type="button" class="btn btn-maroon dropdown-toggle" data-bs-toggle="dropdown" aria-expanded="false" data-bs-display="static">
                        <i class="fas fa-plus me-2"></i>Add User
                    </button>
                    <ul class="dropdown-menu dropdown-menu-end shadow border-0 mt-2">
                        <li><a class="dropdown-item py-2" href="#" id="addSubscriberBtn">
                            <i class="fas fa-user-plus me-2 text-primary"></i>Add Subscriber
                        </a></li>
                        <li><hr class="dropdown-divider"></li>
                        <li><a class="dropdown-item py-2" href="#" id="addAdminBtn">
                            <i class="fas fa-user-shield me-2 text-dark"></i>Add Admin
                        </a></li>
                        <li><a class="dropdown-item py-2" href="#" id="addAttendantBtn">
                            <i class="fas fa-user-tie me-2 text-info"></i>Add Attendant
                        </a></li>
                    </ul>
                </div>
            </div>
        </div>
    </div>

    <!-- Enhanced Stats Row -->
    <div class="row mb-4 g-3">
        <div class="col-xl-3 col-lg-3 col-md-6 col-sm-6">
            <div class="card stats-card-modern h-100 border-0 shadow-sm hover-lift stats-card-maroon">
                <div class="card-body text-white position-relative overflow-hidden p-4">
                    <div class="d-flex align-items-center justify-content-between mb-3">
                        <div class="flex-grow-1">
                            <h5 class="mb-2 fw-semibold" style="font-size: 0.95rem; opacity: 0.9;" id="labelTotalUsers">Total Subscribers</h5>
                            <h2 class="mb-0 fw-bold text-white" id="statTotalUsers" style="font-size: 2.5rem; line-height: 1.2;">-</h2>
                        </div>
                        <div class="stats-icon" style="font-size: 2.5rem; opacity: 0.2;">
                            <i class="fas fa-users"></i>
                        </div>
                    </div>
                    <p class="mb-0 text-white" style="opacity: 0.8; font-size: 0.9rem;" id="descTotalUsers">
                        <i class="fas fa-users me-2"></i>Registered
                    </p>
                </div>
            </div>
        </div>
        <div class="col-xl-3 col-lg-3 col-md-6 col-sm-6">
            <div class="card stats-card-modern h-100 border-0 shadow-sm hover-lift stats-card-gray">
                <div class="card-body text-white position-relative overflow-hidden p-4">
                    <div class="d-flex align-items-center justify-content-between mb-3">
                        <div class="flex-grow-1">
                            <h5 class="mb-2 fw-semibold" style="font-size: 0.95rem; opacity: 0.9;" id="labelOnlineUsers">Online</h5>
                            <h2 class="mb-0 fw-bold text-white" id="statOnlineUsers" style="font-size: 2.5rem; line-height: 1.2;">-</h2>
                        </div>
                        <div class="stats-icon" style="font-size: 2.5rem; opacity: 0.2;">
                            <i class="fas fa-circle"></i>
                        </div>
                    </div>
                    <p class="mb-0 text-white" style="opacity: 0.8; font-size: 0.9rem;" id="descOnlineUsers">
                        <i class="fas fa-circle me-2"></i>Login Status
                    </p>
                </div>
            </div>
        </div>
        <div class="col-xl-3 col-lg-3 col-md-6 col-sm-6">
            <div class="card stats-card-modern h-100 border-0 shadow-sm hover-lift stats-card-gray">
                <div class="card-body text-white position-relative overflow-hidden p-4">
                    <div class="d-flex align-items-center justify-content-between mb-3">
                        <div class="flex-grow-1">
                            <h5 class="mb-2 fw-semibold" style="font-size: 0.95rem; opacity: 0.9;" id="labelActiveUsers">Active</h5>
                            <h2 class="mb-0 fw-bold text-white" id="statActiveUsers" style="font-size: 2.5rem; line-height: 1.2;">-</h2>
                        </div>
                        <div class="stats-icon" style="font-size: 2.5rem; opacity: 0.2;">
                            <i class="fas fa-check-circle"></i>
                        </div>
                    </div>
                    <p class="mb-0 text-white" style="opacity: 0.8; font-size: 0.9rem;" id="descActiveUsers">
                        <i class="fas fa-check-circle me-2"></i>Active
                    </p>
                </div>
            </div>
        </div>
        <div class="col-xl-3 col-lg-3 col-md-6 col-sm-6">
            <div class="card stats-card-modern h-100 border-0 shadow-sm hover-lift stats-card-maroon">
                <div class="card-body text-white position-relative overflow-hidden p-4">
                    <div class="d-flex align-items-center justify-content-between mb-3">
                        <div class="flex-grow-1">
                            <h5 class="mb-2 fw-semibold" style="font-size: 0.95rem; opacity: 0.9;" id="labelInactiveUsers">Inactive</h5>
                            <h2 class="mb-0 fw-bold text-white" id="statInactiveUsers" style="font-size: 2.5rem; line-height: 1.2;">-</h2>
                        </div>
                        <div class="stats-icon" style="font-size: 2.5rem; opacity: 0.2;">
                            <i class="fas fa-pause-circle"></i>
                        </div>
                    </div>
                    <p class="mb-0 text-white" style="opacity: 0.8; font-size: 0.9rem;" id="descInactiveUsers">
                        <i class="fas fa-pause-circle me-2"></i>Inactive
                    </p>
                </div>
            </div>
        </div>
    </div>

    <!-- Shared Filters Component -->
    <?= view('partials/filters/shared_filters') ?>

    <!-- Tabs Navigation -->
    <ul class="nav nav-tabs mb-4" id="usersTabs" role="tablist">
        <li class="nav-item" role="presentation">
            <button class="nav-link active" id="subscribers-tab" data-bs-toggle="tab" data-bs-target="#subscribers" type="button" role="tab" aria-controls="subscribers" aria-selected="true">
                <i class="fas fa-user-check me-2"></i>Subscribers
            </button>
        </li>
        <li class="nav-item" role="presentation">
            <button class="nav-link" id="admins-tab" data-bs-toggle="tab" data-bs-target="#admins" type="button" role="tab" aria-controls="admins" aria-selected="false">
                <i class="fas fa-user-shield me-2"></i>Admins
            </button>
        </li>
        <li class="nav-item" role="presentation">
            <button class="nav-link" id="attendants-tab" data-bs-toggle="tab" data-bs-target="#attendants" type="button" role="tab" aria-controls="attendants" aria-selected="false">
                <i class="fas fa-user-tie me-2"></i>Attendants
            </button>
        </li>
        <li class="nav-item" role="presentation">
            <button class="nav-link" id="walk-in-guests-tab" data-bs-toggle="tab" data-bs-target="#walk-in-guests" type="button" role="tab" aria-controls="walk-in-guests" aria-selected="false">
                <i class="fas fa-user-clock me-2"></i>Walk-in Guests
            </button>
        </li>
    </ul>

    <!-- Tab Content -->
    <div class="tab-content" id="usersTabsContent">
        <!-- Subscribers Tab -->
        <div class="tab-pane fade show active" id="subscribers" role="tabpanel" aria-labelledby="subscribers-tab">
            <!-- Users Table -->
            <div class="card shadow-sm">
                <div class="card-header">
                    <div class="d-flex justify-content-between align-items-center flex-wrap gap-3">
                        <div>
                            <h5 class="mb-0">Subscribers List</h5>
                            <small class="text-muted" id="tableInfo">Loading subscribers...</small>
                        </div>
                        <div class="d-flex align-items-center gap-2">
                            <label class="mb-0">Per Page:</label>
                            <select class="form-select form-select-sm" style="min-width: 80px; width: auto;" id="perPageSelect">
                                <option value="10" <?= $globalPerPage == 10 ? 'selected' : '' ?>>10</option>
                                <option value="25" <?= $globalPerPage == 25 ? 'selected' : '' ?>>25</option>
                                <option value="50" <?= $globalPerPage == 50 ? 'selected' : '' ?>>50</option>
                                <option value="100" <?= $globalPerPage == 100 ? 'selected' : '' ?>>100</option>
                            </select>
                            <button class="btn btn-success btn-sm" id="exportUsersBtn">
                                <i class="fas fa-file-excel me-2"></i>Export Subscribers to CSV
                            </button>
                        </div>
                    </div>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-hover" id="usersTable">
                            <thead class="table-light">
                                <tr>
                                    <th class="sortable" data-column="user_id">ID <i class="fas fa-sort text-muted ms-1"></i></th>
                                    <th class="sortable" data-column="name">Name <i class="fas fa-sort text-muted ms-1"></i></th>
                                    <th class="sortable" data-column="email">Email <i class="fas fa-sort text-muted ms-1"></i></th>
                                    <th class="sortable" data-column="hour_balance">Hour Balance <i class="fas fa-sort text-muted ms-1"></i></th>
                                    <th class="sortable" data-column="status">Status <i class="fas fa-sort text-muted ms-1"></i></th>
                                    <th class="sortable" data-column="online">Login Status <i class="fas fa-sort text-muted ms-1"></i></th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody id="userTableBody">
                                <tr>
                                    <td colspan="7" class="text-center py-5">
                                        <div class="spinner-border text-primary" role="status">
                                            <span class="visually-hidden">Loading...</span>
                                        </div>
                                        <p class="mt-2 text-muted">Loading subscribers...</p>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>

                    <!-- Pagination -->
                    <div class="d-flex justify-content-between align-items-center mt-3">
                        <div id="paginationInfo"></div>
                        <nav>
                            <ul class="pagination mb-0" id="paginationControls"></ul>
                        </nav>
                    </div>
                </div>
            </div>
        </div>

        <!-- Walk-in Guests Tab -->
        <!-- Admins Tab -->
        <div class="tab-pane fade" id="admins" role="tabpanel" aria-labelledby="admins-tab">
            <div class="card shadow-sm">
                <div class="card-header">
                    <div class="d-flex justify-content-between align-items-center flex-wrap gap-3">
                        <div>
                            <h5 class="mb-0">Admins List</h5>
                            <small class="text-muted" id="adminsTableInfo">Loading admins...</small>
                        </div>
                        <div class="d-flex align-items-center gap-2">
                            <label class="mb-0">Per Page:</label>
                            <select class="form-select form-select-sm" style="min-width: 80px; width: auto;" id="adminsPerPageSelect">
                                <option value="10" <?= $globalPerPage == 10 ? 'selected' : '' ?>>10</option>
                                <option value="25" <?= $globalPerPage == 25 ? 'selected' : '' ?>>25</option>
                                <option value="50" <?= $globalPerPage == 50 ? 'selected' : '' ?>>50</option>
                                <option value="100" <?= $globalPerPage == 100 ? 'selected' : '' ?>>100</option>
                            </select>
                            <button class="btn btn-outline-success btn-sm" id="exportAdminsBtn">
                                <i class="fas fa-file-csv me-1"></i>Export Admins to CSV
                            </button>
                        </div>
                    </div>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-hover" id="adminsTable">
                            <thead class="table-light">
                                <tr>
                                    <th width="5%">ID</th>
                                    <th width="20%">Admin Name</th>
                                    <th width="15%">Role</th>
                                    <th width="15%">Assigned Area</th>
                                    <th width="15%">Status</th>
                                    <th width="15%">Online</th>
                                    <th width="15%">Actions</th>
                                </tr>
                            </thead>
                            <tbody id="adminsTableBody">
                                <tr>
                                    <td colspan="7" class="text-center py-5">
                                        <div class="spinner-border text-primary" role="status">
                                            <span class="visually-hidden">Loading...</span>
                                        </div>
                                        <p class="mt-2 text-muted">Loading admins...</p>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                    
                    <!-- Pagination -->
                    <div class="d-flex justify-content-between align-items-center mt-3">
                        <div id="adminsPaginationInfo"></div>
                        <nav>
                            <ul class="pagination mb-0" id="adminsPaginationControls"></ul>
                        </nav>
                    </div>
                </div>
            </div>
        </div>

        <!-- Attendants Tab -->
        <div class="tab-pane fade" id="attendants" role="tabpanel" aria-labelledby="attendants-tab">
            <div class="card shadow-sm">
                <div class="card-header">
                    <div class="d-flex justify-content-between align-items-center flex-wrap gap-3">
                        <div>
                            <h5 class="mb-0">Attendants List</h5>
                            <small class="text-muted" id="attendantsTableInfo">Loading attendants...</small>
                        </div>
                        <div class="d-flex align-items-center gap-2">
                            <label class="mb-0">Per Page:</label>
                            <select class="form-select form-select-sm" style="min-width: 80px; width: auto;" id="attendantsPerPageSelect">
                                <option value="10" <?= $globalPerPage == 10 ? 'selected' : '' ?>>10</option>
                                <option value="25" <?= $globalPerPage == 25 ? 'selected' : '' ?>>25</option>
                                <option value="50" <?= $globalPerPage == 50 ? 'selected' : '' ?>>50</option>
                                <option value="100" <?= $globalPerPage == 100 ? 'selected' : '' ?>>100</option>
                            </select>
                            <button class="btn btn-outline-success btn-sm" id="exportAttendantsBtn">
                                <i class="fas fa-file-csv me-1"></i>Export Attendants to CSV
                            </button>
                        </div>
                    </div>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-hover" id="attendantsTable">
                            <thead class="table-light">
                                <tr>
                                    <th width="5%">ID</th>
                                    <th width="20%">Attendant Name</th>
                                    <th width="15%">Role</th>
                                    <th width="15%">Assigned Area</th>
                                    <th width="15%">Status</th>
                                    <th width="15%">Online</th>
                                    <th width="15%">Actions</th>
                                </tr>
                            </thead>
                            <tbody id="attendantsTableBody">
                                <tr>
                                    <td colspan="7" class="text-center py-5">
                                        <div class="spinner-border text-primary" role="status">
                                            <span class="visually-hidden">Loading...</span>
                                        </div>
                                        <p class="mt-2 text-muted">Loading attendants...</p>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                    
                    <!-- Pagination -->
                    <div class="d-flex justify-content-between align-items-center mt-3">
                        <div id="attendantsPaginationInfo"></div>
                        <nav>
                            <ul class="pagination mb-0" id="attendantsPaginationControls"></ul>
                        </nav>
                    </div>
                </div>
            </div>
        </div>

        <!-- Walk-in Guests Tab -->
        <div class="tab-pane fade" id="walk-in-guests" role="tabpanel" aria-labelledby="walk-in-guests-tab">
            <!-- Walk-in Guests Table -->
            <div class="card shadow-sm">
                <div class="card-header">
                    <div class="d-flex justify-content-between align-items-center flex-wrap gap-3">
                        <div>
                            <h5 class="mb-0">Walk-in Guests</h5>
                            <small class="text-muted" id="guestTableInfo">Loading walk-in guests...</small>
                        </div>
                        <div class="d-flex align-items-center gap-2">
                            <label class="mb-0">Per Page:</label>
                            <select class="form-select form-select-sm" style="min-width: 80px; width: auto;" id="guestPerPageSelect">
                                <option value="10" <?= $globalPerPage == 10 ? 'selected' : '' ?>>10</option>
                                <option value="25" <?= $globalPerPage == 25 ? 'selected' : '' ?>>25</option>
                                <option value="50" <?= $globalPerPage == 50 ? 'selected' : '' ?>>50</option>
                                <option value="100" <?= $globalPerPage == 100 ? 'selected' : '' ?>>100</option>
                            </select>
                            <button class="btn btn-success btn-sm" id="exportGuestsBtn">
                                <i class="fas fa-file-excel me-2"></i>Export Guests to CSV
                            </button>
                        </div>
                    </div>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-hover" id="guestsTable">
                            <thead class="table-light">
                                <tr>
                                    <th class="sortable" data-column="guest_booking_id">ID <i class="fas fa-sort text-muted ms-1"></i></th>
                                    <th class="sortable" data-column="guest_name">Guest Name <i class="fas fa-sort text-muted ms-1"></i></th>
                                    <th class="sortable" data-column="vehicle_info">Vehicle <i class="fas fa-sort text-muted ms-1"></i></th>
                                    <th class="sortable" data-column="attendant_name">Attendant <i class="fas fa-sort text-muted ms-1"></i></th>
                                    <th class="sortable" data-column="reservation_info">Reservation <i class="fas fa-sort text-muted ms-1"></i></th>
                                    <th class="sortable" data-column="created_at">Created <i class="fas fa-sort text-muted ms-1"></i></th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody id="guestsTableBody">
                                <tr>
                                    <td colspan="7" class="text-center py-5">
                                        <div class="spinner-border text-primary" role="status">
                                            <span class="visually-hidden">Loading...</span>
                                        </div>
                                        <p class="mt-2 text-muted">Loading walk-in guests...</p>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>

                    <!-- Pagination -->
                    <div class="d-flex justify-content-between align-items-center mt-3">
                        <div id="guestPaginationInfo"></div>
                        <nav>
                            <ul class="pagination mb-0" id="guestPaginationControls"></ul>
                        </nav>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Load external CSS -->
<link rel="stylesheet" href="<?= base_url('assets/css/users.css') ?>">

<!-- Set base URL for JavaScript -->
<script>
window.APP_BASE_URL = '<?= base_url() ?>';
</script>

<!-- Load external JavaScript -->
<script src="<?= base_url('assets/js/users.js') ?>"></script>
