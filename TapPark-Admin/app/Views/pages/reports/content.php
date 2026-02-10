<!-- Reports Content Only (Used for AJAX updates - excludes filter) -->

<!-- Load Reports CSS -->
<link rel="stylesheet" href="<?= base_url('assets/css/reports.css') ?>">

<!-- Enhanced Stats Cards Row -->
<div class="row mb-4 g-3">
    <!-- Activity Rate -->
    <div class="col-xl-2 col-lg-3 col-md-6 col-sm-6">
        <div class="card stats-card-modern h-100 border-0 shadow-sm stats-card-maroon">
            <div class="card-body text-white position-relative overflow-hidden p-4">
                <div class="d-flex align-items-center">
                    <div class="flex-grow-1">
                        <h6 class="mb-1 opacity-75 fw-semibold small">Activity Rate</h6>
                        <h4 class="mb-0 fw-bold text-white"><?= number_format($userMetrics['activity_rate'] ?? 0, 1) ?>%</h4>
                        <small class="opacity-75 d-block">
                            <i class="fas fa-users me-1"></i>
                            <?= $userMetrics['active_parkers_in_range'] ?? ($userMetrics['active_parkers_this_month'] ?? 0) ?> / <?= $userMetrics['total_users'] ?? 0 ?> active
                        </small>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Avg Duration -->
    <div class="col-xl-2 col-lg-3 col-md-6 col-sm-6">
        <div class="card stats-card-modern h-100 border-0 shadow-sm stats-card-maroon">
            <div class="card-body text-white position-relative overflow-hidden p-4">
                <div class="d-flex align-items-center">
                    <div class="flex-grow-1">
                        <h6 class="mb-1 opacity-75 fw-semibold small">Avg Duration</h6>
                        <h4 class="mb-0 fw-bold text-white"><?= number_format($avgDuration ?? 0, 1) ?>h</h4>
                        <small class="opacity-75 d-block">
                            <i class="fas fa-clock me-1"></i>
                            Hours per session
                        </small>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Active Subscriptions -->
    <div class="col-xl-2 col-lg-3 col-md-6 col-sm-6">
        <div class="card stats-card-modern h-100 border-0 shadow-sm stats-card-maroon">
            <div class="card-body text-white position-relative overflow-hidden p-4">
                <div class="d-flex align-items-center">
                    <div class="flex-grow-1">
                        <h6 class="mb-1 opacity-75 fw-semibold small">Active Subscriptions</h6>
                        <h4 class="mb-0 fw-bold text-white"><?= number_format($subscriptionMetrics['total_subscriptions'] ?? 0) ?></h4>
                        <small class="opacity-75 d-block">
                            <i class="fas fa-calendar me-1"></i>
                            <?= $subscriptionMetrics['new_this_month'] ?? 0 ?> new this month
                        </small>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Avg Revenue per Booking -->
    <div class="col-xl-2 col-lg-3 col-md-6 col-sm-6">
        <div class="card stats-card-modern h-100 border-0 shadow-sm stats-card-maroon">
            <div class="card-body text-white position-relative overflow-hidden p-4">
                <div class="d-flex align-items-center">
                    <div class="flex-grow-1">
                        <h6 class="mb-1 opacity-75 fw-semibold small">Avg Revenue</h6>
                        <h4 class="mb-0 fw-bold text-white">₱<?= number_format(($totalBookings > 0) ? $totalRevenue / $totalBookings : 0, 2) ?></h4>
                        <small class="opacity-75 d-block">
                            <i class="fas fa-money-bill-wave me-1"></i>
                            Per booking
                        </small>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Cancellation Rate -->
    <div class="col-xl-2 col-lg-3 col-md-6 col-sm-6">
        <div class="card stats-card-modern h-100 border-0 shadow-sm stats-card-maroon">
            <div class="card-body text-white position-relative overflow-hidden p-4">
                <div class="d-flex align-items-center">
                    <div class="flex-grow-1">
                        <h6 class="mb-1 opacity-75 fw-semibold small">Cancellation</h6>
                        <h4 class="mb-0 fw-bold text-white"><?= number_format($bookingMetrics['cancellation_rate'] ?? 0, 1) ?>%</h4>
                        <small class="opacity-75 d-block">
                            <i class="fas fa-times-circle me-1"></i>
                            Of total bookings
                        </small>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Repeat Booking Rate -->
    <div class="col-xl-2 col-lg-3 col-md-6 col-sm-6">
        <div class="card stats-card-modern h-100 border-0 shadow-sm stats-card-maroon">
            <div class="card-body text-white position-relative overflow-hidden p-4">
                <div class="d-flex align-items-center">
                    <div class="flex-grow-1">
                        <h6 class="mb-1 opacity-75 fw-semibold small">Repeat Rate</h6>
                        <h4 class="mb-0 fw-bold text-white"><?= number_format($bookingMetrics['repeat_booking_rate'] ?? 0, 1) ?>%</h4>
                        <small class="opacity-75 d-block">
                            <i class="fas fa-redo me-1"></i>
                            Customer loyalty
                        </small>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Enhanced Revenue Analytics Section -->
<div class="row mb-4">
    <div class="col-12">
        <div class="card border-0 shadow-sm">
            <div class="card-header" style="background: var(--tappark-maroon); color: white;">
                <h5 class="mb-0 fw-bold">
                    <i class="fas fa-chart-line me-2"></i>Revenue Analytics
                </h5>
            </div>
            <div class="card-body">
                <!-- Revenue Charts Row 1 -->
                <div class="row mb-4">
                    <!-- Revenue by Plan -->
                    <div class="col-lg-4 mb-3">
                        <div class="card h-100 shadow-sm">
                            <div class="card-header bg-transparent border-0 pb-0">
                                <h6 class="mb-1 fw-bold">
                                    <i class="fas fa-chart-pie me-2" style="color: var(--tappark-maroon);"></i>Revenue by Plan
                                </h6>
                                <small class="text-muted">Distribution across subscription plans</small>
                            </div>
                            <div class="card-body">
                                <div style="height: 250px; position: relative;">
                                    <canvas id="revenuePlanChart"></canvas>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Revenue by Hour -->
                    <div class="col-lg-4 mb-3">
                        <div class="card h-100 shadow-sm">
                            <div class="card-header bg-transparent border-0 pb-0">
                                <h6 class="mb-1 fw-bold">
                                    <i class="fas fa-clock me-2" style="color: var(--tappark-maroon);"></i>Revenue by Hour
                                </h6>
                                <small class="text-muted">Hourly revenue patterns</small>
                            </div>
                            <div class="card-body">
                                <div style="height: 250px; position: relative;">
                                    <canvas id="revenueHourChart"></canvas>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Revenue by Day of Week -->
                    <div class="col-lg-4 mb-3">
                        <div class="card h-100 shadow-sm">
                            <div class="card-header bg-transparent border-0 pb-0">
                                <h6 class="mb-1 fw-bold">
                                    <i class="fas fa-calendar-week me-2" style="color: var(--tappark-maroon);"></i>Revenue by Day
                                </h6>
                                <small class="text-muted">Weekly revenue distribution</small>
                            </div>
                            <div class="card-body">
                                <div style="height: 250px; position: relative;">
                                    <canvas id="revenueDayChart"></canvas>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                
                <!-- Revenue Charts Row 2 -->
                <div class="row">
                    <!-- Revenue by Area -->
                    <div class="col-lg-8 mb-3">
                        <div class="card h-100 shadow-sm">
                            <div class="card-header bg-transparent border-0 pb-0">
                                <h6 class="mb-1 fw-bold">
                                    <i class="fas fa-map-marked-alt me-2" style="color: var(--tappark-maroon);"></i>Revenue by Area
                                </h6>
                                <small class="text-muted">Top performing parking areas</small>
                            </div>
                            <div class="card-body">
                                <div style="height: 300px; position: relative;">
                                    <canvas id="revenueAreaChart"></canvas>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Revenue Growth -->
                    <div class="col-lg-4 mb-3">
                        <div class="card h-100 shadow-sm">
                            <div class="card-header bg-transparent border-0 pb-0">
                                <h6 class="mb-1 fw-bold">
                                    <i class="fas fa-chart-line me-2" style="color: var(--tappark-maroon);"></i>Revenue Growth
                                </h6>
                                <small class="text-muted">Revenue trend analysis</small>
                            </div>
                            <div class="card-body">
                                <div style="height: 300px; position: relative;">
                                    <canvas id="revenueTrendChart"></canvas>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Enhanced Booking Analytics Section -->
<div class="row mb-4">
    <div class="col-12">
        <div class="card border-0 shadow-sm">
            <div class="card-header" style="background: var(--tappark-maroon); color: white;">
                <h5 class="mb-0 fw-bold">
                    <i class="fas fa-calendar-check me-2"></i>Booking Analytics
                </h5>
            </div>
            <div class="card-body">
                <!-- Booking Charts Row 1 -->
                <div class="row mb-4">
                    <!-- Bookings by Day of Week -->
                    <div class="col-lg-6 mb-3">
                        <div class="card h-100 shadow-sm">
                            <div class="card-header bg-transparent border-0 pb-0">
                                <h6 class="mb-1 fw-bold">
                                    <i class="fas fa-calendar-week me-2" style="color: var(--tappark-maroon);"></i>Bookings by Day
                                </h6>
                                <small class="text-muted">Weekly booking patterns</small>
                            </div>
                            <div class="card-body">
                                <div style="height: 280px; position: relative;">
                                    <canvas id="bookingsDayChart"></canvas>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Peak Hours -->
                    <div class="col-lg-6 mb-3">
                        <div class="card h-100 shadow-sm">
                            <div class="card-header bg-transparent border-0 pb-0">
                                <h6 class="mb-1 fw-bold">
                                    <i class="fas fa-business-time me-2" style="color: var(--tappark-maroon);"></i>Peak Hours
                                </h6>
                                <small class="text-muted">Hourly booking distribution</small>
                            </div>
                            <div class="card-body">
                                <div style="height: 280px; position: relative;">
                                    <canvas id="peakHoursChart"></canvas>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                
                <!-- Booking Metrics Row -->
                <div class="row mb-4">
                    <div class="col-md-3">
                        <div class="card text-center border-0 shadow-sm">
                            <div class="card-body">
                                <h3 class="fw-bold" style="color: var(--tappark-maroon);"><?= number_format($bookingMetrics['total_bookings'] ?? 0) ?></h3>
                                <p class="mb-0 text-muted">Total Bookings</p>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="card text-center border-0 shadow-sm">
                            <div class="card-body">
                                <h3 class="fw-bold" style="color: var(--tappark-maroon);"><?= number_format($bookingMetrics['cancelled_bookings'] ?? 0) ?></h3>
                                <p class="mb-0 text-muted">Cancelled</p>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="card text-center border-0 shadow-sm">
                            <div class="card-body">
                                <h3 class="fw-bold" style="color: var(--tappark-maroon);"><?= number_format($bookingMetrics['repeat_bookings'] ?? 0) ?></h3>
                                <p class="mb-0 text-muted">Repeat Bookings</p>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="card text-center border-0 shadow-sm">
                            <div class="card-body">
                                <h3 class="fw-bold" style="color: var(--tappark-maroon);"><?= number_format($bookingMetrics['repeat_users'] ?? 0) ?></h3>
                                <p class="mb-0 text-muted">Repeat Users</p>
                            </div>
                        </div>
                    </div>
                </div>
                
                <!-- Booking Charts Row 2 -->
                <div class="row">
                    <!-- Booking by Area Comparison -->
                    <div class="col-lg-8 mb-3">
                        <div class="card h-100 shadow-sm">
                            <div class="card-header bg-transparent border-0 pb-0">
                                <h6 class="mb-1 fw-bold">
                                    <i class="fas fa-map-marked-alt me-2" style="color: var(--tappark-maroon);"></i>Bookings by Area
                                </h6>
                                <small class="text-muted">Area-wise booking comparison</small>
                            </div>
                            <div class="card-body">
                                <div style="height: 300px; position: relative;">
                                    <canvas id="bookingsAreaChart"></canvas>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Vehicle Types -->
                    <div class="col-lg-4 mb-3">
                        <div class="card h-100 shadow-sm">
                            <div class="card-header bg-transparent border-0 pb-0">
                                <h6 class="mb-1 fw-bold">
                                    <i class="fas fa-car me-2" style="color: var(--tappark-maroon);"></i>Vehicle Types
                                </h6>
                                <small class="text-muted">Vehicle distribution</small>
                            </div>
                            <div class="card-body">
                                <div style="height: 300px; position: relative;">
                                    <canvas id="vehicleTypesChart"></canvas>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Enhanced Occupancy Analytics Section -->
<div class="row mb-4">
    <div class="col-12">
        <div class="card border-0 shadow-sm">
            <div class="card-header" style="background: var(--tappark-maroon); color: white;">
                <h5 class="mb-0 fw-bold">
                    <i class="fas fa-th me-2"></i>Occupancy Analytics
                </h5>
            </div>
            <div class="card-body">
                <!-- Occupancy Charts Row 1 -->
                <div class="row mb-4">
                    <!-- Hourly Occupancy Heatmap -->
                    <div class="col-lg-8 mb-3">
                        <div class="card h-100 shadow-sm">
                            <div class="card-header bg-transparent border-0 pb-0">
                                <h6 class="mb-1 fw-bold">
                                    <i class="fas fa-fire me-2" style="color: var(--tappark-maroon);"></i>Hourly Occupancy Heatmap
                                </h6>
                                <small class="text-muted">24-hour occupancy patterns</small>
                            </div>
                            <div class="card-body">
                                <div style="height: 350px; position: relative;">
                                    <canvas id="hourlyOccupancyChart"></canvas>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Occupancy Metrics -->
                    <div class="col-lg-4 mb-3">
                        <div class="card h-100 shadow-sm">
                            <div class="card-header bg-transparent border-0 pb-0">
                                <h6 class="mb-1 fw-bold">
                                    <i class="fas fa-chart-pie me-2" style="color: var(--tappark-maroon);"></i>Occupancy Metrics
                                </h6>
                                <small class="text-muted">Key performance indicators</small>
                            </div>
                            <div class="card-body">
                                <div class="text-center mb-3">
                                    <h2 class="fw-bold" style="color: var(--tappark-maroon);"><?= number_format($avgOccupancy ?? 0, 1) ?>%</h2>
                                    <p class="text-muted">Average Occupancy</p>
                                </div>
                                <hr>
                                <div class="row text-center">
                                    <div class="col-6">
                                        <h5 class="fw-bold" style="color: var(--tappark-maroon);"><?= count($areaPerformance ?? []) ?></h5>
                                        <small class="text-muted">Total Areas</small>
                                    </div>
                                    <div class="col-6">
                                        <h5 class="fw-bold" style="color: var(--tappark-maroon);">
                                            <?= count(array_filter($areaPerformance ?? [], fn($a) => $a['utilization_percent'] > 70)) ?>
                                        </h5>
                                        <small class="text-muted">High Utilization</small>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                
                <!-- Area Performance Table -->
                <div class="row">
                    <div class="col-12">
                        <div class="card shadow-sm">
                            <div class="card-header bg-transparent border-0 pb-0">
                                <h6 class="mb-1 fw-bold">
                                    <i class="fas fa-table me-2" style="color: var(--tappark-maroon);"></i>Detailed Area Performance
                                </h6>
                                <small class="text-muted">Comprehensive area analysis</small>
                            </div>
                            <div class="card-body">
                                <div class="table-responsive">
                                    <table class="table table-hover">
                                        <thead class="table-light">
                                            <tr>
                                                <th>#</th>
                                                <th>Area Name</th>
                                                <th>Total Spots</th>
                                                <th>Bookings</th>
                                                <th>Revenue</th>
                                                <th>Utilization %</th>
                                                <th>Revenue per Spot</th>
                                                <th>Turnover Rate</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php $areaCounter = 1; foreach ($areaPerformance ?? [] as $area): ?>
                                            <tr>
                                                <td>#<?= $areaCounter++ ?></td>
                                                <td class="fw-semibold"><?= $area['parking_area_name'] ?></td>
                                                <td><?= number_format($area['total_spots']) ?></td>
                                                <td><?= number_format($area['total_bookings']) ?></td>
                                                <td>₱<?= number_format($area['total_revenue'], 2) ?></td>
                                                <td>
                                                    <span class="badge" style="background: <?= $area['utilization_percent'] > 70 ? 'var(--tappark-maroon)' : ($area['utilization_percent'] > 40 ? '#ffc107' : '#dc3545') ?>; color: white;">
                                                        <?= number_format($area['utilization_percent'], 1) ?>%
                                                    </span>
                                                </td>
                                                <td>₱<?= number_format($area['revenue_per_spot'], 2) ?></td>
                                                <td><?= number_format($area['turnover_rate'], 1) ?></td>
                                            </tr>
                                            <?php endforeach; ?>
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Enhanced Subscriber Analytics Section -->
<div class="row mb-4">
    <div class="col-12">
        <div class="card border-0 shadow-sm">
            <div class="card-header" style="background: var(--tappark-maroon); color: white;">
                <h5 class="mb-0 fw-bold">
                    <i class="fas fa-users me-2"></i>Subscriber Analytics
                </h5>
            </div>
            <div class="card-body">
                <!-- User Charts Row 1 -->
                <div class="row mb-4">
                    <!-- User Growth -->
                    <div class="col-lg-6 mb-3">
                        <div class="card h-100 shadow-sm">
                            <div class="card-header bg-transparent border-0 pb-0">
                                <h6 class="mb-1 fw-bold">
                                    <i class="fas fa-chart-line me-2" style="color: var(--tappark-maroon);"></i>User Growth
                                </h6>
                                <small class="text-muted">Monthly user growth trends</small>
                            </div>
                            <div class="card-body">
                                <div style="height: 280px; position: relative;">
                                    <canvas id="userGrowthChart"></canvas>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Active vs Inactive Users -->
                    <div class="col-lg-6 mb-3">
                        <div class="card h-100 shadow-sm">
                            <div class="card-header bg-transparent border-0 pb-0">
                                <h6 class="mb-1 fw-bold">
                                    <i class="fas fa-user-check me-2" style="color: var(--tappark-maroon);"></i>User Activity
                                </h6>
                                <small class="text-muted">Active vs inactive users</small>
                            </div>
                            <div class="card-body">
                                <div style="height: 280px; position: relative;">
                                    <canvas id="userActivityChart"></canvas>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                
                <!-- User Metrics Row -->
                <div class="row mb-4">
                    <div class="col-md-3">
                        <div class="card text-center border-0 shadow-sm">
                            <div class="card-body">
                                <h3 class="fw-bold" style="color: var(--tappark-maroon);"><?= number_format($userAnalytics['total_users'] ?? 0) ?></h3>
                                <p class="mb-0 text-muted">Total Users</p>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="card text-center border-0 shadow-sm">
                            <div class="card-body">
                                <h3 class="fw-bold" style="color: var(--tappark-maroon);"><?= number_format($userAnalytics['active_users'] ?? 0) ?></h3>
                                <p class="mb-0 text-muted">Active Users</p>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="card text-center border-0 shadow-sm">
                            <div class="card-body">
                                <h3 class="fw-bold" style="color: var(--tappark-maroon);"><?= number_format($userAnalytics['retention_rate'] ?? 0, 1) ?>%</h3>
                                <p class="mb-0 text-muted">Retention Rate</p>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="card text-center border-0 shadow-sm">
                            <div class="card-body">
                                <h3 class="fw-bold" style="color: var(--tappark-maroon);">₱<?= number_format($userAnalytics['lifetime_value'] ?? 0, 2) ?></h3>
                                <p class="mb-0 text-muted">Lifetime Value</p>
                            </div>
                        </div>
                    </div>
                </div>
                
                            </div>
        </div>
    </div>
</div>

<!-- Enhanced Operational Reports Section -->
<div class="row mb-4">
    <div class="col-12">
        <div class="card border-0 shadow-sm">
            <div class="card-header" style="background: var(--tappark-maroon); color: white;">
                <h5 class="mb-0 fw-bold">
                    <i class="fas fa-cogs me-2"></i>Operational Reports
                </h5>
            </div>
            <div class="card-body">
                <div class="row">
                    <!-- Subscription Distribution -->
                    <div class="col-lg-6 mb-3">
                        <div class="card h-100 shadow-sm">
                            <div class="card-header bg-transparent border-0 pb-0">
                                <h6 class="mb-1 fw-bold">
                                    <i class="fas fa-chart-pie me-2" style="color: var(--tappark-maroon);"></i>Subscription Distribution
                                </h6>
                                <small class="text-muted">Plan usage breakdown</small>
                            </div>
                            <div class="card-body">
                                <div style="height: 300px; position: relative;">
                                    <canvas id="subscriptionChart"></canvas>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Booking Status Breakdown -->
                    <div class="col-lg-6 mb-3">
                        <div class="card h-100 shadow-sm">
                            <div class="card-header bg-transparent border-0 pb-0">
                                <h6 class="mb-1 fw-bold">
                                    <i class="fas fa-tasks me-2" style="color: var(--tappark-maroon);"></i>Booking Status
                                </h6>
                                <small class="text-muted">Current booking status breakdown</small>
                            </div>
                            <div class="card-body">
                                <div style="height: 300px; position: relative;">
                                    <canvas id="bookingStatusChart"></canvas>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Guest Bookings Analytics Section -->
<div class="row mb-4">
    <div class="col-12">
        <div class="card border-0 shadow-sm">
            <div class="card-header" style="background: var(--tappark-maroon); color: white;">
                <h5 class="mb-0 fw-bold">
                    <i class="fas fa-user-clock me-2"></i>Guest Bookings Analytics
                </h5>
            </div>
            <div class="card-body">
                <!-- Guest Bookings Charts Row -->
                <div class="row mb-4">
                    <!-- Guest Bookings Trend -->
                    <div class="col-lg-8 mb-3">
                        <div class="card h-100 shadow-sm">
                            <div class="card-header bg-transparent border-0 pb-0">
                                <h6 class="mb-1 fw-bold">
                                    <i class="fas fa-chart-line me-2" style="color: var(--tappark-maroon);"></i>Guest Bookings Trend
                                </h6>
                                <small class="text-muted">Guest booking trends over the selected time period</small>
                            </div>
                            <div class="card-body">
                                <div style="height: 300px; position: relative;">
                                    <canvas id="reportsGuestBookingsTrendChart"></canvas>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Guest Bookings by Vehicle Type -->
                    <div class="col-lg-4 mb-3">
                        <div class="card h-100 shadow-sm">
                            <div class="card-header bg-transparent border-0 pb-0">
                                <h6 class="mb-1 fw-bold">
                                    <i class="fas fa-car me-2" style="color: var(--tappark-maroon);"></i>By Vehicle Type
                                </h6>
                                <small class="text-muted">Distribution across vehicle types</small>
                            </div>
                            <div class="card-body">
                                <div style="height: 300px; position: relative;">
                                    <canvas id="reportsGuestBookingsVehicleChart"></canvas>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                
                <!-- Top Attendants -->
                <div class="row">
                    <div class="col-12">
                        <div class="card shadow-sm">
                            <div class="card-header bg-transparent border-0 pb-0">
                                <h6 class="mb-1 fw-bold">
                                    <i class="fas fa-user-tie me-2" style="color: var(--tappark-maroon);"></i>Top Performing Attendants
                                </h6>
                                <small class="text-muted">Attendants with the most guest bookings</small>
                            </div>
                            <div class="card-body">
                                <div style="height: 250px; position: relative;">
                                    <canvas id="reportsGuestBookingsAttendantChart"></canvas>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Period Comparison Section -->
<div class="row mb-4">
    <div class="col-12">
        <div class="card border-0 shadow-sm">
            <div class="card-header bg-dark text-white">
                <h5 class="mb-0 fw-bold">
                    <i class="fas fa-exchange-alt me-2"></i>Period Comparison
                </h5>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-4">
                        <div class="card border-0 shadow-sm">
                            <div class="card-body">
                                <h6 class="text-muted mb-3">Revenue Comparison</h6>
                                <div class="row">
                                    <div class="col-6">
                                        <h5 class="fw-bold" style="color: var(--tappark-maroon);">₱<?= number_format($periodComparison['current']['revenue'] ?? 0, 2) ?></h5>
                                        <small class="text-muted"><?= $periodComparison['period_labels']['current'] ?? 'Current' ?></small>
                                    </div>
                                    <div class="col-6">
                                        <h5 class="text-secondary fw-bold">₱<?= number_format($periodComparison['previous']['revenue'] ?? 0, 2) ?></h5>
                                        <small class="text-muted"><?= $periodComparison['period_labels']['previous'] ?? 'Previous' ?></small>
                                    </div>
                                </div>
                                <div class="mt-2">
                                    <?php 
                                    $revenueChange = $periodComparison['changes']['revenue_change'] ?? 0;
                                    $revenueBadgeClass = $revenueChange > 0 ? 'success' : ($revenueChange < 0 ? 'danger' : 'secondary');
                                    $revenueIcon = $revenueChange > 0 ? 'up' : ($revenueChange < 0 ? 'down' : 'right');
                                    ?>
                                    <span class="badge bg-<?= $revenueBadgeClass ?>">
                                        <i class="fas fa-arrow-<?= $revenueIcon ?>"></i>
                                        <?= number_format(abs($revenueChange), 1) ?>%
                                    </span>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="card border-0 shadow-sm">
                            <div class="card-body">
                                <h6 class="text-muted mb-3">Bookings Comparison</h6>
                                <div class="row">
                                    <div class="col-6">
                                        <h5 class="fw-bold" style="color: var(--tappark-maroon);"><?= number_format($periodComparison['current']['bookings'] ?? 0) ?></h5>
                                        <small class="text-muted"><?= $periodComparison['period_labels']['current'] ?? 'Current' ?></small>
                                    </div>
                                    <div class="col-6">
                                        <h5 class="text-secondary fw-bold"><?= number_format($periodComparison['previous']['bookings'] ?? 0) ?></h5>
                                        <small class="text-muted"><?= $periodComparison['period_labels']['previous'] ?? 'Previous' ?></small>
                                    </div>
                                </div>
                                <div class="mt-2">
                                    <?php 
                                    $bookingsChange = $periodComparison['changes']['bookings_change'] ?? 0;
                                    $bookingsBadgeClass = $bookingsChange > 0 ? 'success' : ($bookingsChange < 0 ? 'danger' : 'secondary');
                                    $bookingsIcon = $bookingsChange > 0 ? 'up' : ($bookingsChange < 0 ? 'down' : 'right');
                                    ?>
                                    <span class="badge bg-<?= $bookingsBadgeClass ?>">
                                        <i class="fas fa-arrow-<?= $bookingsIcon ?>"></i>
                                        <?= number_format(abs($bookingsChange), 1) ?>%
                                    </span>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="card border-0 shadow-sm">
                            <div class="card-body">
                                <h6 class="text-muted mb-3">Active Users Comparison</h6>
                                <div class="row">
                                    <div class="col-6">
                                        <h5 class="fw-bold" style="color: var(--tappark-maroon);"><?= number_format($periodComparison['current']['active_users'] ?? 0) ?></h5>
                                        <small class="text-muted"><?= $periodComparison['period_labels']['current'] ?? 'Current' ?></small>
                                    </div>
                                    <div class="col-6">
                                        <h5 class="text-secondary fw-bold"><?= number_format($periodComparison['previous']['active_users'] ?? 0) ?></h5>
                                        <small class="text-muted"><?= $periodComparison['period_labels']['previous'] ?? 'Previous' ?></small>
                                    </div>
                                </div>
                                <div class="mt-2">
                                    <?php 
                                    $usersChange = $periodComparison['changes']['users_change'] ?? 0;
                                    $usersBadgeClass = $usersChange > 0 ? 'success' : ($usersChange < 0 ? 'danger' : 'secondary');
                                    $usersIcon = $usersChange > 0 ? 'up' : ($usersChange < 0 ? 'down' : 'right');
                                    ?>
                                    <span class="badge bg-<?= $usersBadgeClass ?>">
                                        <i class="fas fa-arrow-<?= $usersIcon ?>"></i>
                                        <?= number_format(abs($usersChange), 1) ?>%
                                    </span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="row mb-4">
    <div class="col-12">
        <div class="card border-0 shadow-sm">
            <div class="card-header" style="background: var(--tappark-maroon); color: white; border-radius: 8px 8px 0 0;">
                <h5 class="mb-0 fw-bold d-flex align-items-center">
                    <i class="fas fa-star me-2"></i>
                    Feedback Analytics
                </h5>
            </div>
            <div class="card-body">
                <div class="row g-3 feedback-analytics-row">
                    <div class="col-lg-5">
                        <div class="feedback-chart-wrapper">
                            <canvas id="feedbackRatingChart"></canvas>
                        </div>
                    </div>
                    <div class="col-lg-7">
                        <?php
                            $feedbackRecords = $feedbackList ?? [];
                            $feedbackPageSize = 5;
                            $feedbackTotal = count($feedbackRecords);
                            $feedbackInitialRows = array_slice($feedbackRecords, 0, $feedbackPageSize);
                            $feedbackPageCount = max(1, (int)ceil($feedbackTotal / $feedbackPageSize));
                            $feedbackSummaryText = $feedbackTotal > 0
                                ? sprintf('Showing %d-%d of %d feedback entries', 1, min($feedbackPageSize, $feedbackTotal), $feedbackTotal)
                                : 'No feedback entries available';
                        ?>
                        <div class="feedback-card">
                            <div class="feedback-table-wrapper">
                                <table class="table table-hover align-middle mb-0">
                                    <thead>
                                        <tr>
                                            <th>#</th>
                                            <th>User</th>
                                            <th>Rating</th>
                                            <th>Feedback</th>
                                            <th>Date</th>
                                        </tr>
                                    </thead>
                                    <tbody id="feedbackTableBody">
                                        <?php if (empty($feedbackInitialRows)) : ?>
                                            <tr class="feedback-empty-row">
                                                <td colspan="5" class="text-center text-muted py-4">No feedback found for the selected period.</td>
                                            </tr>
                                        <?php else : ?>
                                            <?php $fbCounter = 1; foreach ($feedbackInitialRows as $fb) : ?>
                                                <tr>
                                                    <td>#<?= $fbCounter++ ?></td>
                                                    <td>
                                                        <div class="fw-semibold">
                                                            <?= esc(trim(($fb['first_name'] ?? '') . ' ' . ($fb['last_name'] ?? ''))) ?>
                                                        </div>
                                                        <div class="small text-muted"><?= esc($fb['email'] ?? '') ?></div>
                                                    </td>
                                                    <td>
                                                        <span class="badge bg-secondary"><?= esc($fb['rating'] ?? 0) ?>/5</span>
                                                    </td>
                                                    <td style="max-width: 420px;">
                                                        <div class="text-truncate" title="<?= esc($fb['content'] ?? '') ?>">
                                                            <?= esc($fb['content'] ?? '') ?>
                                                        </div>
                                                    </td>
                                                    <td class="small text-muted"><?= esc($fb['created_at'] ?? '') ?></td>
                                                </tr>
                                            <?php endforeach; ?>
                                        <?php endif; ?>
                                    </tbody>
                                </table>
                            </div>
                            <div class="feedback-pagination d-flex flex-wrap align-items-center justify-content-between mt-3 gap-2">
                                <div class="small text-muted" id="feedbackPaginationSummary"><?= esc($feedbackSummaryText) ?></div>
                                <div class="d-flex align-items-center gap-2">
                                    <button type="button" class="btn btn-outline-secondary btn-sm" id="feedbackPrevPage" disabled>
                                        <i class="fas fa-chevron-left me-1"></i>Prev
                                    </button>
                                    <span class="feedback-pagination-status small fw-semibold" id="feedbackPaginationStatus">1 / <?= $feedbackPageCount ?></span>
                                    <button type="button" class="btn btn-outline-secondary btn-sm" id="feedbackNextPage" <?= $feedbackTotal <= $feedbackPageSize ? 'disabled' : '' ?>>
                                        Next<i class="fas fa-chevron-right ms-1"></i>
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
<script>
    // Make enhanced reports data available to reports.js
    window.reportsData = {
        // Original data
        revenueByPlan: <?= json_encode($revenueByPlan ?? []) ?>,
        revenueByArea: <?= json_encode($revenueByArea ?? []) ?>,
        peakHours: <?= json_encode($peakHours ?? ['labels' => [], 'data' => []]) ?>,
        vehicleTypes: <?= json_encode($vehicleTypes ?? []) ?>,
        userGrowth: <?= json_encode($userGrowth ?? []) ?>,
        revenueTrend: <?= json_encode($revenueTrend ?? []) ?>,
        paymentMethods: <?= json_encode($paymentMethods ?? []) ?>,
        subscriptionDistribution: <?= json_encode($subscriptionDistribution ?? []) ?>,
        bookingStatusBreakdown: <?= json_encode($bookingStatusBreakdown ?? []) ?>,
        
        // Enhanced analytics data
        revenueByHour: <?= json_encode($revenueByHour ?? []) ?>,
        revenueByDayOfWeek: <?= json_encode($revenueByDayOfWeek ?? []) ?>,
        bookingsByDayOfWeek: <?= json_encode($bookingsByDayOfWeek ?? []) ?>,
        bookingMetrics: <?= json_encode($bookingMetrics ?? []) ?>,
        hourlyOccupancy: <?= json_encode($hourlyOccupancy ?? []) ?>,
        areaPerformance: <?= json_encode($areaPerformance ?? []) ?>,
        userAnalytics: <?= json_encode($userAnalytics ?? []) ?>,
        feedbackRatingDistribution: <?= json_encode($feedbackRatingDistribution ?? []) ?>,
        feedbackList: <?= json_encode($feedbackList ?? []) ?>,
        feedbackPagination: {
            pageSize: <?= (int)$feedbackPageSize ?>,
            total: <?= (int)$feedbackTotal ?>
        },
        periodComparison: <?= json_encode($periodComparison ?? [
            'current' => ['revenue' => 0, 'bookings' => 0, 'active_users' => 0],
            'previous' => ['revenue' => 0, 'bookings' => 0, 'active_users' => 0],
            'changes' => ['revenue_change' => 0, 'bookings_change' => 0, 'users_change' => 0]
        ]) ?>,
        
        // Guest bookings analytics
        guestBookingsStats: <?= json_encode($guestBookingsStats ?? [
            'total_guest_bookings' => 0,
            'guest_bookings_by_date' => [],
            'guest_bookings_by_vehicle' => [],
            'guest_bookings_by_attendant' => []
        ]) ?>,
        
        // Summary metrics
        totalRevenue: <?= $totalRevenue ?? 0 ?>,
        totalBookings: <?= $totalBookings ?? 0 ?>,
        avgOccupancy: <?= $avgOccupancy ?? 0 ?>
    };
    
    // Initialize enhanced reports charts after data is set
    setTimeout(function() {
        if (typeof window.initPageScripts === 'function') {
            window.initPageScripts();
        } else {
            console.warn('⚠️ initPageScripts not found. Make sure reports.js is loaded.');
        }
    }, 150);
</script>

<!-- Export and Print Functions -->
<script>
    // Export reports functionality
    function exportReports(format) {
        const baseUrl = (typeof BASE_URL !== 'undefined') ? BASE_URL : (typeof window.BASE_URL !== 'undefined' ? window.BASE_URL : '/');
        
        // Get current filter state from the page
        const currentFilter = document.querySelector('.filter-btn.active')?.getAttribute('data-filter') || '<?= $filter ?? 'today' ?>';
        const customStart = document.getElementById('reportsFilterStartDate')?.value || '<?= $customStart ?? '' ?>';
        const customEnd = document.getElementById('reportsFilterEndDate')?.value || '<?= $customEnd ?? '' ?>';
        
        // Build export URL with current filter parameters
        let exportUrl = `${baseUrl}reports/export?format=${format}&filter=${currentFilter}`;
        if (customStart && customEnd) {
            exportUrl += `&start_date=${customStart}&end_date=${customEnd}`;
        }
        
        // Show loading
        const loadingHtml = `
            <div class="d-flex justify-content-center align-items-center" style="min-height: 200px;">
                <div class="text-center">
                    <div class="spinner-border mb-3" style="color: var(--tappark-maroon);" role="status">
                        <span class="visually-hidden">Exporting...</span>
                    </div>
                    <h5>Exporting to ${format.toUpperCase()}...</h5>
                    <p class="text-muted">Please wait while we prepare your report</p>
                    <p class="small text-muted">Filter: ${currentFilter}${customStart && customEnd ? ' (' + customStart + ' to ' + customEnd + ')' : ''}</p>
                </div>
            </div>
        `;
        
        // Create modal overlay
        const modal = document.createElement('div');
        modal.className = 'modal fade show';
        modal.style.display = 'block';
        modal.style.backgroundColor = 'rgba(0,0,0,0.5)';
        modal.innerHTML = `
            <div class="modal-dialog modal-dialog-centered">
                <div class="modal-content">
                    <div class="modal-body">
                        ${loadingHtml}
                    </div>
                </div>
            </div>
        `;
        document.body.appendChild(modal);
        
        // Make export request
        fetch(exportUrl, {
            method: 'GET',
            headers: {
                'X-Requested-With': 'XMLHttpRequest'
            }
        })
        .then(response => {
            // Check if response is successful
            if (!response.ok) {
                throw new Error('Export failed');
            }
            
            // Check content type to determine if it's a file download
            const contentType = response.headers.get('content-type');
            if (contentType && (contentType.includes('application/octet-stream') || 
                contentType.includes('application/vnd.openxmlformats-officedocument.spreadsheetml.sheet') ||
                contentType.includes('text/csv') ||
                contentType.includes('application/pdf'))) {
                return response.blob();
            } else if (contentType && contentType.includes('text/html')) {
                // Handle HTML response (for PDF export)
                return response.blob();
            } else {
                // If it's not a file, it might be an error response
                return response.text().then(text => {
                    throw new Error(text || 'Invalid response format');
                });
            }
        })
        .then(blob => {
            const url = window.URL.createObjectURL(blob);
            const a = document.createElement('a');
            a.href = url;
            a.download = `parking-reports-${currentFilter}-${new Date().toISOString().split('T')[0]}.${format}`;
            document.body.appendChild(a);
            a.click();
            window.URL.revokeObjectURL(url);
            document.body.removeChild(a);
            document.body.removeChild(modal);
        })
        .catch(error => {
            console.error('Export error:', error);
            document.body.removeChild(modal);
            alert('Export failed: ' + error.message + '. Please try again.');
        });
    }
    
    // Print reports functionality
    function printReports() {
        window.print();
    }
    
    // Refresh reports functionality
    function refreshReports() {
        const currentFilter = '<?= $filter ?? 'today' ?>';
        const currentStart = '<?= $customStart ?? '' ?>';
        const currentEnd = '<?= $customEnd ?? '' ?>';
        
        if (typeof window.loadReportsWithFilter === 'function') {
            window.loadReportsWithFilter(currentFilter, currentStart, currentEnd);
        } else {
            location.reload();
        }
    }
</script>


