<?php $globalPerPage = session('app_settings')['records_per_page'] ?? 25; ?>
<div class="container-fluid">
    <!-- Enhanced Page Header -->
    <div class="card mb-4 border-0 shadow-sm">
        <div class="card-body py-4">
            <div class="d-flex justify-content-between align-items-center flex-wrap gap-3">
                <div>
                    <h2 class="mb-2 fw-bold">
                        <i class="fas fa-crown me-3 text-primary"></i>Subscription Management
                    </h2>
                    <p class="mb-0 text-muted">Manage subscription plans and pricing</p>
                </div>
                <button class="btn btn-maroon" id="addPlanBtn">
                    <i class="fas fa-plus me-2"></i>Add Plan
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
                            <h5 class="mb-1 opacity-75 fw-semibold">Total Plans</h5>
                            <h2 class="mb-0 fw-bold text-white" id="statTotalPlans">-</h2>
                            <small class="opacity-75 d-block">
                                <i class="fas fa-list-alt me-1"></i>
                                Available plans
                            </small>
                        </div>
                        <div class="stats-icon">
                            <i class="fas fa-list-alt fa-2x opacity-75"></i>
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
                            <h5 class="mb-1 opacity-75 fw-semibold">Total Subscribers</h5>
                            <h2 class="mb-0 fw-bold text-white" id="statTotalSubscribers">-</h2>
                            <small class="opacity-75 d-block">
                                <i class="fas fa-users me-1"></i>
                                All subscribers
                            </small>
                        </div>
                        <div class="stats-icon">
                            <i class="fas fa-users fa-2x opacity-75"></i>
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
                            <h5 class="mb-1 opacity-75 fw-semibold">Active Subscribers</h5>
                            <h2 class="mb-0 fw-bold text-white" id="statActiveSubscribers">-</h2>
                            <small class="opacity-75 d-block">
                                <i class="fas fa-user-check me-1"></i>
                                Currently active
                            </small>
                        </div>
                        <div class="stats-icon">
                            <i class="fas fa-user-check fa-2x opacity-75"></i>
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
                            <h5 class="mb-1 opacity-75 fw-semibold">Total Revenue</h5>
                            <h2 class="mb-0 fw-bold text-white" id="statTotalRevenue">-</h2>
                            <small class="opacity-75 d-block">
                                <i class="fas fa-coins me-1"></i>
                                From subscriptions
                            </small>
                        </div>
                        <div class="stats-icon">
                            <i class="fas fa-coins fa-2x opacity-75"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Shared Filters Component -->
    <?= view('partials/filters/shared_filters') ?>

    <!-- Plans Table -->
    <div class="card shadow-sm">
        <div class="card-header">
            <div class="d-flex justify-content-between align-items-center flex-wrap gap-3">
                <div>
                    <h5 class="mb-0">Subscription Plans</h5>
                    <small class="text-muted" id="tableInfo">Loading plans...</small>
                </div>
                <div class="d-flex align-items-center gap-2">
                    <label class="mb-0">Per Page:</label>
                    <select class="form-select form-select-sm" style="min-width: 80px; width: auto;" id="perPageSelect">
                        <option value="10" <?= $globalPerPage == 10 ? 'selected' : '' ?>>10</option>
                        <option value="25" <?= $globalPerPage == 25 ? 'selected' : '' ?>>25</option>
                        <option value="50" <?= $globalPerPage == 50 ? 'selected' : '' ?>>50</option>
                        <option value="100" <?= $globalPerPage == 100 ? 'selected' : '' ?>>100</option>
                    </select>
                    <button class="btn btn-success btn-sm" id="exportSubscriptionsBtn">
                        <i class="fas fa-file-excel me-2"></i>Export to CSV
                    </button>
                </div>
            </div>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-hover" id="plansTable">
                    <thead class="table-light">
                        <tr>
                            <th class="sortable" data-column="plan_id">ID <i class="fas fa-sort text-muted ms-1"></i></th>
                            <th class="sortable" data-column="plan_name">Plan Name <i class="fas fa-sort text-muted ms-1"></i></th>
                            <th class="sortable" data-column="cost">Cost (₱) <i class="fas fa-sort text-muted ms-1"></i></th>
                            <th class="sortable" data-column="hours">Hours <i class="fas fa-sort text-muted ms-1"></i></th>
                            <th class="sortable" data-column="subscribers">Subscribers <i class="fas fa-sort text-muted ms-1"></i></th>
                            <th>Description</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody id="planTableBody">
                        <tr>
                            <td colspan="7" class="text-center py-5">
                                <div class="spinner-border text-primary" role="status">
                                    <span class="visually-hidden">Loading...</span>
                                </div>
                                <p class="mt-2 text-muted">Loading plans...</p>
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
<link rel="stylesheet" href="<?= base_url('assets/css/subscriptions.css') ?>">

<!-- Load JavaScript -->
<script src="<?= base_url('assets/js/subscriptions.js') ?>"></script>
