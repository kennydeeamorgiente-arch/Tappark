<!-- Dashboard Content Only (Used for AJAX updates - excludes filter) -->
<!-- Stats Cards Row -->
<div class="row mb-4 g-3">
    <!-- Total Subscribers -->
    <div class="col-xl-2 col-lg-3 col-md-4 col-sm-6">
        <div class="card stats-card-modern h-100 border-0 shadow-sm stats-card-maroon">
            <div class="card-body text-white position-relative overflow-hidden p-4">
                <div class="d-flex align-items-center justify-content-between">
                    <div class="flex-grow-1">
                        <h6 class="mb-1 opacity-75 fw-semibold small">Total Subscribers</h6>
                        <h4 class="mb-0 fw-bold text-white"><?= number_format($totalSubscribers ?? 0) ?></h4>
                        <small class="opacity-75 d-block">
                            <i class="fas fa-users me-1"></i>
                            Active: <?= number_format($activeSubscribers ?? 0) ?> | Inactive: <?= number_format($inactiveSubscribers ?? 0) ?>
                        </small>
                    </div>
                    <div class="stats-icon">
                        <i class="fas fa-users fa-2x opacity-75"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Active Bookings -->
    <div class="col-xl-2 col-lg-3 col-md-4 col-sm-6">
        <div class="card stats-card-modern h-100 border-0 shadow-sm stats-card-maroon">
            <div class="card-body text-white position-relative overflow-hidden p-4">
                <div class="d-flex align-items-center justify-content-between">
                    <div class="flex-grow-1">
                        <h6 class="mb-1 opacity-75 fw-semibold small">Active Bookings</h6>
                        <h4 class="mb-0 fw-bold text-white"><?= number_format($activeBookings ?? 0) ?></h4>
                        <small class="opacity-75 d-block">
                            <i class="fas fa-calendar-check me-1"></i>
                            Active
                        </small>
                    </div>
                    <div class="stats-icon">
                        <i class="fas fa-calendar-check fa-2x opacity-75"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Parking Spaces -->
    <div class="col-xl-2 col-lg-3 col-md-4 col-sm-6">
        <div class="card stats-card-modern h-100 border-0 shadow-sm stats-card-maroon">
            <div class="card-body text-white position-relative overflow-hidden p-4">
                <div class="d-flex align-items-center justify-content-between">
                    <div class="flex-grow-1">
                        <h6 class="mb-1 opacity-75 fw-semibold small">Parking Spaces</h6>
                        <h4 class="mb-0 fw-bold text-white"><?= number_format($totalSpots ?? 0) ?></h4>
                        <small class="opacity-75 d-block">
                            <i class="fas fa-parking me-1"></i>
                            Available
                        </small>
                    </div>
                    <div class="stats-icon">
                        <i class="fas fa-parking fa-2x opacity-75"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Revenue -->
    <div class="col-xl-2 col-lg-3 col-md-4 col-sm-6">
        <div class="card stats-card-modern h-100 border-0 shadow-sm stats-card-maroon">
            <div class="card-body text-white position-relative overflow-hidden p-4">
                <div class="d-flex align-items-center justify-content-between">
                    <div class="flex-grow-1">
                        <h6 class="mb-1 opacity-75 fw-semibold small">Revenue</h6>
                        <h4 class="mb-0 fw-bold text-white">₱<?= number_format($revenue ?? 0, 2) ?></h4>
                        <small class="opacity-75 d-block">
                            <i class="fas fa-coins me-1"></i>
                            Earnings
                        </small>
                    </div>
                    <div class="stats-icon">
                        <i class="fas fa-money-bill-wave fa-2x opacity-75"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Occupancy Rate -->
    <div class="col-xl-2 col-lg-3 col-md-4 col-sm-6">
        <div class="card stats-card-modern h-100 border-0 shadow-sm stats-card-maroon">
            <div class="card-body text-white position-relative overflow-hidden p-4">
                <div class="d-flex align-items-center justify-content-between">
                    <div class="flex-grow-1">
                        <h6 class="mb-1 opacity-75 fw-semibold small">Occupancy</h6>
                        <h4 class="mb-0 fw-bold text-white"><?= number_format($occupancyRate ?? 0, 1) ?>%</h4>
                        <small class="opacity-75 d-block">
                            <i class="fas fa-chart-pie me-1"></i>
                            Rate
                        </small>
                    </div>
                    <div class="stats-icon">
                        <i class="fas fa-chart-pie fa-2x opacity-75"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Online Staff -->
    <div class="col-xl-2 col-lg-3 col-md-4 col-sm-6">
        <div class="card stats-card-modern h-100 border-0 shadow-sm stats-card-maroon">
            <div class="card-body text-white position-relative overflow-hidden p-4">
                <div class="d-flex align-items-center justify-content-between">
                    <div class="flex-grow-1">
                        <h6 class="mb-1 opacity-75 fw-semibold small">Online Staff</h6>
                        <h4 class="mb-0 fw-bold text-white"><?= number_format($onlineAttendants ?? 0) ?></h4>
                        <small class="opacity-75 d-block">
                            <i class="fas fa-user-tie me-1"></i>
                            Online
                        </small>
                    </div>
                    <div class="stats-icon">
                        <i class="fas fa-user-tie fa-2x opacity-75"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Charts Row 1 -->
<div class="row mb-4">
    <!-- Revenue Chart -->
    <div class="col-lg-8 mb-3">
        <div class="card h-100 shadow-sm">
            <div class="card-header bg-transparent border-0 pb-0">
                <h5 class="mb-1 fw-bold">
                    <i class="fas fa-chart-line text-primary me-2"></i>Revenue Trend
                </h5>
                <small class="text-muted">Revenue trends over the selected time period</small>
            </div>
            <div class="card-body">
                <div style="height: 350px; position: relative;">
                    <canvas id="revenueChart"></canvas>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Occupancy Chart -->
    <div class="col-lg-4 mb-3">
        <div class="card h-100 shadow-sm">
            <div class="card-header bg-transparent border-0 pb-0">
                <h6 class="mb-1 fw-bold">
                    <i class="fas fa-chart-pie text-primary me-2"></i>Parking Occupancy
                </h6>
            </div>
            <div class="card-body d-flex align-items-center justify-content-center">
                <div style="max-width: 250px; max-height: 250px;">
                    <canvas id="occupancyChart"></canvas>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Charts Row 2 -->
<div class="row mb-4">
    <!-- Bookings Chart -->
    <div class="col-lg-6 mb-3">
        <div class="card h-100 shadow-sm">
            <div class="card-header bg-transparent border-0 pb-0">
                <h5 class="mb-1 fw-bold">
                    <i class="fas fa-calendar-check text-primary me-2"></i>Bookings Overview
                </h5>
                <small class="text-muted">Booking trends over the selected time period</small>
            </div>
            <div class="card-body">
                <div style="height: 300px; position: relative;">
                    <canvas id="bookingsChart"></canvas>
                </div>
            </div>
        </div>
    </div>
    
    <!-- User Growth Chart -->
    <div class="col-lg-6 mb-3">
        <div class="card h-100 shadow-sm">
            <div class="card-header bg-transparent border-0 pb-0">
                <h5 class="mb-1 fw-bold">
                    <i class="fas fa-users text-primary me-2"></i>Subscriber Growth
                </h5>
                <small class="text-muted">Cumulative subscriber growth over time</small>
            </div>
            <div class="card-body">
                <div style="height: 300px; position: relative;">
                    <canvas id="userGrowthChart"></canvas>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Charts Row 3 (Optional Enhanced Charts) -->
<div class="row mb-4">
    <!-- Hour Balance Chart -->
    <div class="col-lg-6 mb-3">
        <div class="card h-100 shadow-sm">
            <div class="card-header bg-transparent border-0 pb-0">
                <h5 class="mb-1 fw-bold">
                    <i class="fas fa-clock text-primary me-2"></i>Hour Balance Trends
                </h5>
                <small class="text-muted">Purchased, used, and remaining hours</small>
            </div>
            <div class="card-body">
                <div style="height: 300px; position: relative;">
                    <canvas id="hourBalanceChart"></canvas>
                </div>
            </div>
        </div>
    </div>

    <!-- Average Rating Over Time -->
    <div class="col-lg-6 mb-3">
        <div class="card h-100 shadow-sm">
            <div class="card-header bg-transparent border-0 pb-0">
                <h5 class="mb-1 fw-bold">
                    <i class="fas fa-star text-primary me-2"></i>Average Rating Over Time
                </h5>
                <small class="text-muted">Average feedback rating trend for the selected time period</small>
            </div>
            <div class="card-body">
                <div style="height: 300px; position: relative;">
                    <canvas id="avgRatingChart"></canvas>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Charts Row 4 - Guest Bookings Overview -->
<div class="row mb-4">
    <!-- Guest Bookings Summary -->
    <div class="col-12">
        <div class="card shadow-sm h-100 guest-summary-card">
            <div class="card-header bg-white border-bottom d-flex justify-content-between align-items-center flex-wrap gap-2">
                <h5 class="mb-0 fw-semibold">
                    <i class="fas fa-user-clock me-2 text-primary"></i>Guest Bookings Summary
                </h5>
                <span class="badge bg-primary rounded-pill">
                    <?= number_format($guestBookingsStats['total_guest_bookings'] ?? 0) ?> Total
                </span>
            </div>
            <div class="card-body guest-summary-body">
                <div class="row g-4 align-items-stretch">
                    <div class="col-lg-8">
                        <div class="guest-summary-chart" style="height: 380px;">
                            <canvas id="guestBookingsTrendChart" aria-label="Guest bookings trend chart"></canvas>
                        </div>
                    </div>
                    <div class="col-lg-4">
                        <div class="guest-summary-panel">
                            <div class="guest-summary-section">
                                <div class="guest-panel-heading">
                                    <span class="guest-panel-icon">
                                        <i class="fas fa-car-side"></i>
                                    </span>
                                    <div>
                                        <h6>Bookings by Vehicle</h6>
                                        <p class="guest-panel-meta">Volume per vehicle category</p>
                                    </div>
                                </div>
                                <div class="guest-panel-list" role="list">
                                    <?php if (!empty($guestBookingsStats['guest_bookings_by_vehicle'])): ?>
                                        <?php foreach ($guestBookingsStats['guest_bookings_by_vehicle'] as $vehicle): ?>
                                            <div class="guest-stat-card" role="listitem">
                                                <div class="guest-stat-card__main">
                                                    <span class="guest-stat-icon">
                                                        <i class="fas fa-car"></i>
                                                    </span>
                                                    <div class="guest-stat-copy">
                                                        <span class="guest-stat-label" title="<?= esc(ucfirst($vehicle['vehicle_type'])) ?>">
                                                            <?= esc(ucfirst($vehicle['vehicle_type'])) ?>
                                                        </span>
                                                        <small class="guest-stat-meta">Vehicle type</small>
                                                    </div>
                                                </div>
                                                <div class="guest-stat-card__value">
                                                    <span class="guest-stat-value"><?= number_format($vehicle['count'] ?? 0) ?></span>
                                                    <small class="guest-stat-value-label">bookings</small>
                                                </div>
                                            </div>
                                        <?php endforeach; ?>
                                    <?php else: ?>
                                        <p class="text-muted small mb-0">No vehicle data available</p>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Pass PHP data to JavaScript via global variable -->
<script>
    // Make dashboard data available to dashboard.js
    window.dashboardData = {
        revenueChart: <?= json_encode($revenueChart ?? ['labels' => [], 'data' => []]) ?>,
        occupancyChart: <?= json_encode($occupancyChart ?? ['labels' => ['Occupied', 'Available'], 'data' => [0, 0]]) ?>,
        bookingsChart: <?= json_encode($bookingsChart ?? ['labels' => [], 'data' => []]) ?>,
        userGrowthChart: <?= json_encode($userGrowthChart ?? ['labels' => [], 'data' => []]) ?>,
        hourBalanceTrends: <?= json_encode($hourBalanceTrends ?? ['labels' => ['Purchased', 'Used', 'Remaining'], 'data' => [0, 0, 0]]) ?>,
        avgRatingTrend: <?= json_encode($avgRatingTrend ?? ['labels' => [], 'data' => []]) ?>,
        guestBookingsStats: <?= json_encode($guestBookingsStats ?? [
            'total_guest_bookings' => 0,
            'guest_bookings_by_date' => [],
            'guest_bookings_by_vehicle' => [],
            'guest_bookings_by_attendant' => []
        ]) ?>
    };
    
    // Initialize dashboard charts after data is set
    // Use setTimeout to ensure DOM is ready and Chart.js is loaded
    // Note: When loaded via AJAX, loadPage() will also call initPageScripts()
    // This ensures charts initialize even if called directly
    setTimeout(function() {
        if (typeof window.initPageScripts === 'function') {
            window.initPageScripts();
        } else {
            console.warn('⚠️ initPageScripts not found. Make sure dashboard.js is loaded.');
        }
    }, 150);
</script>

