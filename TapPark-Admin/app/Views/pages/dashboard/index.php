<div class="container-fluid">
    <!-- Dashboard Header -->
    <div class="card mb-4 border-0 shadow-sm">
        <div class="card-body py-4">
            <div class="d-flex justify-content-between align-items-center flex-wrap gap-3">
                <div>
                    <h2 class="mb-2 fw-bold">
                        <i class="fas fa-tachometer-alt me-3 text-primary"></i>Dashboard
                    </h2>
                    <p class="mb-0 text-muted">Overview of key metrics and recent activity</p>
                </div>
                <div class="text-center">
                    <div class="h6 mb-0 fw-bold"><?= number_format($occupancyRate ?? 0, 1) ?>%</div>
                    <small class="text-muted">Occupancy Rate</small>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Date Filter Component (Static - Won't be replaced) -->
    <?= view('partials/components/date_filter', [
        'filter' => $filter ?? 'today',
        'customStart' => $customStart ?? null,
        'customEnd' => $customEnd ?? null,
        'filterCallback' => 'loadDashboardWithFilter',
        'componentId' => 'dashboardFilter'
    ]) ?>
    
    <!-- Dashboard Content (Dynamic - Gets replaced on filter change) -->
    <div id="dashboardContent">
        <?= view('pages/dashboard/content', [
            'totalUsers' => $totalUsers ?? 0,
            'activeBookings' => $activeBookings ?? 0,
            'totalSpots' => $totalSpots ?? 0,
            'revenue' => $revenue ?? 0,
            'occupancyRate' => $occupancyRate ?? 0,
            'onlineAttendants' => $onlineAttendants ?? 0,
            'revenueChart' => $revenueChart ?? ['labels' => [], 'data' => []],
            'occupancyChart' => $occupancyChart ?? ['labels' => ['Occupied', 'Available'], 'data' => [0, 0]],
            'bookingsChart' => $bookingsChart ?? ['labels' => [], 'data' => []],
            'userGrowthChart' => $userGrowthChart ?? ['labels' => [], 'data' => []],
            'revenueVsBookings' => $revenueVsBookings ?? ['labels' => [], 'revenue' => [], 'bookings' => []],
            'hourBalanceTrends' => $hourBalanceTrends ?? ['labels' => ['Purchased', 'Used', 'Remaining'], 'data' => [0, 0, 0]]
        ]) ?>
    </div>
</div>

    <!-- Dashboard.js is already loaded globally in scripts.php -->
    
    <!-- Helper function to update filter display after AJAX loads -->
    <script>
        // Function to update filter component display after AJAX load
        window.updateFilterDisplay = function(filter, customStart, customEnd) {
            const componentId = 'dashboardFilter';
            
            if (filter === 'custom' && customStart && customEnd) {
                console.log('🔄 updateFilterDisplay: Updating custom filter display');
            } else {
                console.log('🔄 updateFilterDisplay: Updating preset filter display:', filter);
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
                    // Handle date string format (YYYY-MM-DD)
                    const date = new Date(dateString + 'T00:00:00'); // Add time to avoid timezone issues
                    const months = ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun', 'Jul', 'Aug', 'Sep', 'Oct', 'Nov', 'Dec'];
                    return months[date.getMonth()] + ' ' + date.getDate() + ', ' + date.getFullYear();
                };
                
                const $infoDiv = $(`#${componentId}Info`);
                const $startDisplay = $(`#${componentId}StartDateDisplay`);
                const $endDisplay = $(`#${componentId}EndDateDisplay`);
                
                console.log('Info div found:', $infoDiv.length);
                console.log('Start display found:', $startDisplay.length);
                console.log('End display found:', $endDisplay.length);
                
                if ($startDisplay.length && $endDisplay.length && $infoDiv.length) {
                    $startDisplay.text(formatDateForDisplay(customStart));
                    $endDisplay.text(formatDateForDisplay(customEnd));
                    $infoDiv.show().css('display', 'block'); // Force show
                    console.log('✅ Filter display updated with custom dates');
                } else {
                    console.error('❌ Filter display elements not found!', {
                        infoDiv: $infoDiv.length,
                        startDisplay: $startDisplay.length,
                        endDisplay: $endDisplay.length
                    });
                }
            } else {
                $(`.filter-btn[data-component-id="${componentId}"][data-filter="${filter}"]`).addClass('active');
                $(`#${componentId}Info`).slideUp(200);
            }
        };
    </script>
    
    <!-- Initialize charts on page load -->
    <script>
        // Wait for DOM and scripts to be ready
        $(document).ready(function() {
            // Small delay to ensure dashboardData is set and Chart.js is loaded
            setTimeout(function() {
                if (typeof window.initPageScripts === 'function') {
                    window.initPageScripts();
                }
            }, 200);
        });
    </script>
