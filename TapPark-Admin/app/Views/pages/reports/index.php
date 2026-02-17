<div class="container-fluid">
    <!-- Load Reports CSS -->
    <link rel="stylesheet" href="<?= base_url('assets/css/reports.css') ?>?v=<?= @filemtime(FCPATH . 'assets/css/reports.css') ?: time() ?>">
    
    <!-- Enhanced Reports Header -->
    <div class="card mb-3 border-0 shadow-sm">
        <div class="card-body py-3">
            <div class="d-flex justify-content-between align-items-center flex-wrap gap-3">
                <div>
                    <h2 class="mb-1 fw-bold">
                        <i class="fas fa-chart-line me-3" style="color: var(--tappark-maroon);"></i>Reports & Insights
                    </h2>
                    <p class="mb-0 text-muted small">Comprehensive analysis of parking system performance</p>
                </div>
                
                <!-- Action Buttons -->
                <div class="d-flex align-items-center gap-2">
                    <!-- Export Button -->
                    <button type="button" class="btn btn-outline-maroon" onclick="exportReports('csv')">
                        <i class="fas fa-download me-2"></i>Export CSV
                    </button>
                    
                    <!-- Print Button -->
                    <button type="button" class="btn btn-outline-secondary" onclick="printReports()">
                        <i class="fas fa-print me-2"></i>Print
                    </button>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Enhanced Date Filter Component -->
    <?= view('partials/components/date_filter', [
        'filter' => $filter ?? 'today',
        'customStart' => $customStart ?? null,
        'customEnd' => $customEnd ?? null,
        'filterCallback' => 'loadReportsWithFilter',
        'componentId' => 'reportsFilter',
        'filterType' => 'dashboard' // Use dashboard filter type (today, weekly, monthly)
    ]) ?>
    
    <!-- Reports Content (Dynamic - Gets replaced on filter change) -->
    <div id="reportsContent">
        <?= view('pages/reports/content', [
            'userMetrics' => $userMetrics ?? ['total_users' => 0, 'active_parkers_this_month' => 0, 'activity_rate' => 0],
            'avgDuration' => $avgDuration ?? 0,
            'subscriptionMetrics' => $subscriptionMetrics ?? ['total_subscriptions' => 0, 'new_this_month' => 0],
            'revenueByArea' => $revenueByArea ?? [],
            'revenueByPlan' => $revenueByPlan ?? [],
            'paymentMethods' => $paymentMethods ?? [],
            'peakHours' => $peakHours ?? ['labels' => [], 'data' => []],
            'popularAreas' => $popularAreas ?? [],
            'vehicleTypes' => $vehicleTypes ?? [],
            'userGrowth' => $userGrowth ?? [],
            'revenueTrend' => $revenueTrend ?? [],
            'subscriptionDistribution' => $subscriptionDistribution ?? [],
            'bookingStatusBreakdown' => $bookingStatusBreakdown ?? []
        ]) ?>
    </div>
</div>

<!-- Reports.js is already loaded globally in scripts.php -->

<!-- Helper function to update filter display after AJAX loads -->
<script>
    // Function to update filter component display after AJAX load
    window.updateReportsFilterDisplay = function(filter, customStart, customEnd) {
        const componentId = 'reportsFilter';
        
        if (filter === 'custom' && customStart && customEnd) {
            console.log('🔄 updateReportsFilterDisplay: Updating custom filter display');
        } else {
            console.log('🔄 updateReportsFilterDisplay: Updating preset filter display:', filter);
        }
        
        // Update active button states
        $(`.filter-btn[data-component-id="${componentId}"]`).removeClass('active');
        $(`#${componentId}CustomBtn`).removeClass('active');
        
        if (filter === 'custom' && customStart && customEnd) {
            $(`#${componentId}CustomBtn`).addClass('active');
            
            // Update date inputs
            $(`#${componentId}StartDate`).val(customStart);
            $(`#${componentId}EndDate`).val(customEnd);
            
            // Format and display dates
            const formatDateForDisplay = function(dateString) {
                const date = new Date(dateString + 'T00:00:00');
                const months = ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun', 'Jul', 'Aug', 'Sep', 'Oct', 'Nov', 'Dec'];
                return months[date.getMonth()] + ' ' + date.getDate() + ', ' + date.getFullYear();
            };
            
            const $infoDiv = $(`#${componentId}Info`);
            const $startDisplay = $(`#${componentId}StartDateDisplay`);
            const $endDisplay = $(`#${componentId}EndDateDisplay`);
            
            if ($startDisplay.length && $endDisplay.length && $infoDiv.length) {
                $startDisplay.text(formatDateForDisplay(customStart));
                $endDisplay.text(formatDateForDisplay(customEnd));
                $infoDiv.show().css('display', 'block');
                console.log('✅ Reports filter display updated with custom dates');
            }
        } else {
            $(`.filter-btn[data-component-id="${componentId}"][data-filter="${filter}"]`).addClass('active');
            $(`#${componentId}Info`).slideUp(200);
        }
    };
</script>

<!-- Initialize reports on page load -->
<script>
    // Wait for DOM and scripts to be ready
    $(document).ready(function() {
        // Small delay to ensure reportsData is set and Chart.js is loaded
        setTimeout(function() {
            if (typeof window.initPageScripts === 'function') {
                window.initPageScripts();
            }
        }, 200);
    });
</script>
