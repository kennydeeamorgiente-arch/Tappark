<!-- Activity Logs Page -->
<div class="container-fluid">
    <!-- Page Header -->
    <div class="card mb-4 border-0 shadow-sm">
        <div class="card-body py-4">
            <div class="d-flex justify-content-between align-items-center flex-wrap gap-3">
                <div>
                    <h2 class="mb-2 fw-bold">
                        <i class="fas fa-history me-3 text-primary"></i>Activity Logs
                    </h2>
                    <p class="mb-0 text-muted">Monitor and review all user and system activity</p>
                </div>
            </div>
        </div>
    </div>

    <!-- Stats Cards Row -->
    <div class="row mb-4 g-3">
        <div class="col-xl-3 col-lg-6 col-md-6">
            <div class="card stats-card-modern h-100 border-0 shadow-sm stats-card-maroon">
                <div class="card-body text-white position-relative overflow-hidden p-4">
                    <div class="d-flex align-items-center justify-content-between">
                        <div class="flex-grow-1">
                            <h6 class="mb-2 opacity-75 fw-semibold small">Total Activities (7 days)</h6>
                            <h4 class="mb-0 fw-bold text-white" id="statTotalActivities"><?= number_format($summary['total_activities'] ?? 0) ?></h4>
                            <small class="opacity-75 d-block mt-1">
                                <i class="fas fa-chart-line me-1"></i>Recent
                            </small>
                        </div>
                        <div class="stats-icon">
                            <i class="fas fa-chart-line fa-2x opacity-75"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-xl-3 col-lg-6 col-md-6">
            <div class="card stats-card-modern h-100 border-0 shadow-sm stats-card-maroon">
                <div class="card-body text-white position-relative overflow-hidden p-4">
                    <div class="d-flex align-items-center justify-content-between">
                        <div class="flex-grow-1">
                            <h6 class="mb-2 opacity-75 fw-semibold small">Active Users</h6>
                            <h4 class="mb-0 fw-bold text-white" id="statActiveUsers"><?= number_format($summary['active_users'] ?? 0) ?></h4>
                            <small class="opacity-75 d-block mt-1">
                                <i class="fas fa-users me-1"></i>Active
                            </small>
                        </div>
                        <div class="stats-icon">
                            <i class="fas fa-users fa-2x opacity-75"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-xl-3 col-lg-6 col-md-6">
            <div class="card stats-card-modern h-100 border-0 shadow-sm stats-card-maroon">
                <div class="card-body text-white position-relative overflow-hidden p-4">
                    <div class="d-flex align-items-center justify-content-between">
                        <div class="flex-grow-1">
                            <h6 class="mb-2 opacity-75 fw-semibold small">Failed Logins</h6>
                            <h4 class="mb-0 fw-bold text-white" id="statFailedLogins"><?= number_format($summary['failed_logins'] ?? 0) ?></h4>
                            <small class="opacity-75 d-block mt-1">
                                <i class="fas fa-exclamation-triangle me-1"></i>Failed
                            </small>
                        </div>
                        <div class="stats-icon">
                            <i class="fas fa-exclamation-triangle fa-2x opacity-75"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-xl-3 col-lg-6 col-md-6">
            <div class="card stats-card-modern h-100 border-0 shadow-sm stats-card-maroon">
                <div class="card-body text-white position-relative overflow-hidden p-4">
                    <div class="d-flex align-items-center justify-content-between">
                        <div class="flex-grow-1">
                            <h6 class="mb-2 opacity-75 fw-semibold small">Top Action</h6>
                            <h4 class="mb-0 fw-bold text-white" style="font-size: 1.5rem;" id="statTopAction">
                                <?php 
                                if (!empty($summary['activities_by_type']) && !empty($summary['activities_by_type'][0])) {
                                    echo esc($summary['activities_by_type'][0]['action_type'] ?? 'N/A');
                                } else {
                                    echo 'N/A';
                                } 
                                ?>
                            </h4>
                            <small class="opacity-75 d-block mt-1">
                                <i class="fas fa-bolt me-1"></i>Most Common
                            </small>
                        </div>
                        <div class="stats-icon">
                            <i class="fas fa-bolt fa-2x opacity-75"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Activity Timeline Chart and Most Active Users (Side by Side) -->
    <div class="row mb-4 g-3">
        <!-- Activity Timeline Chart (70% width) -->
        <div class="col-lg-8">
            <div class="card analytics-card-modern h-100">
                <div class="card-header bg-transparent border-0 pb-0">
                    <h5 class="mb-1 fw-bold"><i class="fas fa-chart-area text-maroon me-2"></i>Activity Timeline (Last 30 Days)</h5>
                    <small class="text-muted">Activity trends over the past month</small>
                </div>
                <div class="card-body">
                    <div class="chart-container-analytics chart-container-analytics-350">
                        <canvas id="activityTimelineChart" height="80"></canvas>
                    </div>
                </div>
            </div>
        </div>

        <!-- Most Active Users (30% width) -->
        <div class="col-lg-4">
            <div class="card analytics-card-modern h-100">
                <div class="card-header bg-transparent border-0 pb-0">
                    <h5 class="mb-1 fw-bold"><i class="fas fa-user-clock text-maroon me-2"></i>Most Active Users</h5>
                    <small class="text-muted">Top users by activity count</small>
                </div>
                <div class="card-body p-0" style="max-height: 400px; overflow-y: auto;" id="activeUsersList">
                    <?php if (!empty($activeUsers)): ?>
                        <div class="list-group list-group-flush">
                            <?php foreach ($activeUsers as $user): ?>
                                <div class="list-group-item px-3 py-2 d-flex justify-content-between align-items-center border-bottom">
                                    <div class="flex-grow-1">
                                        <div class="fw-bold small"><?= esc(($user['first_name'] ?? '') . ' ' . ($user['last_name'] ?? '')) ?></div>
                                        <small class="text-muted d-block" style="font-size: 0.75rem;"><?= esc($user['email'] ?? '') ?></small>
                                    </div>
                                    <span class="badge bg-maroon rounded-pill ms-2"><?= number_format($user['activity_count'] ?? 0) ?></span>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    <?php else: ?>
                        <div class="p-3">
                            <p class="text-muted text-center my-3 small">No activity data available</p>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>

    <!-- Logs Filters -->
    <?= view('partials/components/logs_filter', [
        'filters' => $filters ?? [],
        'actionTypes' => $actionTypes ?? []
    ]) ?>

    <!-- Store logs-specific data for JavaScript -->
    <script>
        window.logsActionTypes = <?= json_encode($actionTypes ?? []) ?>;
        window.logsFilters = <?= json_encode($filters ?? []) ?>;
        window.LOGS_TIMELINE_DATA = <?= json_encode($timeline ?? []) ?>;
    </script>

    <!-- Activity Logs Table -->
    <div id="logsContent">
        <!-- Content will be loaded here via AJAX -->
        <?= view('pages/logs/content', [
            'logs' => $logs ?? [],
            'pagination' => $pagination ?? []
        ]) ?>
    </div>
</div>

<script>
// Initialize logs page scripts after content loads
setTimeout(function() {
    if (typeof window.initPageScripts === 'function') {
        window.initPageScripts();
    }
}, 150);
</script>
