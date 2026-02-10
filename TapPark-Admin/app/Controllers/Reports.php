<?php

namespace App\Controllers;

use App\Models\ReportsModel;

class Reports extends BaseController
{
    protected $reportsModel;
    
    public function __construct()
    {
        $this->reportsModel = new ReportsModel();
    }
    
    public function index()
    {
        try {
            // Get filter and date range from request
            // Reports uses different filters: today, last_7_days, 30_days, this_year, last_year, custom
            $filter = $this->request->getGet('filter') ?? 'today';
            $customStart = $this->request->getGet('start_date');
            $customEnd = $this->request->getGet('end_date');
            
            // Set defaults based on filter if not provided
            if (!$customStart || !$customEnd) {
                $baseDate = date('Y-m-d');
                $customEnd = $baseDate;
                
                switch ($filter) {
                    case 'today':
                        $customStart = $baseDate;
                        break;
                    case 'last_7_days':
                        $customStart = date('Y-m-d', strtotime($baseDate . ' -6 days')); // Last 7 days inclusive
                        break;
                    case '30_days':
                        $customStart = date('Y-m-d', strtotime($baseDate . ' -29 days')); // Last 30 days inclusive
                        break;
                    case 'this_year':
                        $customStart = date('Y-01-01'); // Start of current year
                        break;
                    case 'last_year':
                        $customStart = date('Y-01-01', strtotime($baseDate . ' -1 year')); // Start of last year
                        $customEnd = date('Y-12-31', strtotime($baseDate . ' -1 year')); // End of last year
                        break;
                    default:
                        $customStart = date('Y-m-d', strtotime($baseDate . ' -29 days')); // Default to 30 days
                }
            }
            
            // Validate date range
            if (strtotime($customStart) > strtotime($customEnd)) {
                $temp = $customStart;
                $customStart = $customEnd;
                $customEnd = $temp;
            }
            
            // Get analytics data from model
            $data = [
                'filter' => $filter,
                'customStart' => $customStart,
                'customEnd' => $customEnd,
                
                // Revenue reports
                'revenueByArea' => $this->reportsModel->getRevenueByArea($customStart, $customEnd),
                'revenueByPlan' => $this->reportsModel->getRevenueByPlan($customStart, $customEnd),
                'paymentMethods' => $this->reportsModel->getPaymentMethodDistribution($customStart, $customEnd),
                'revenueByHour' => $this->reportsModel->getRevenueByHour($customStart, $customEnd),
                'revenueByDayOfWeek' => $this->reportsModel->getRevenueByDayOfWeek($customStart, $customEnd),
                
                // Usage reports
                'peakHours' => $this->reportsModel->getPeakHours($customStart, $customEnd),
                'avgDuration' => $this->reportsModel->getAverageParkingDuration($customStart, $customEnd),
                'popularAreas' => $this->reportsModel->getPopularAreas($customStart, $customEnd),
                'vehicleTypes' => $this->reportsModel->getVehicleTypeDistribution($customStart, $customEnd),
                
                // Booking analytics
                'bookingsByDayOfWeek' => $this->reportsModel->getBookingsByDayOfWeek($customStart, $customEnd),
                'bookingMetrics' => $this->reportsModel->getBookingMetrics($customStart, $customEnd),
                
                // Occupancy analytics
                'hourlyOccupancy' => $this->reportsModel->getHourlyOccupancy($customStart, $customEnd),
                'areaPerformance' => $this->reportsModel->getAreaPerformance($customStart, $customEnd),
                
                // User reports
                'userGrowth' => $this->reportsModel->getMonthlyUserGrowth($customStart, $customEnd),
                'userMetrics' => $this->reportsModel->getUserActivityMetricsByDateRange($customStart, $customEnd),
                'userAnalytics' => $this->reportsModel->getUserAnalytics($customStart, $customEnd),
                'subscriptionMetrics' => $this->reportsModel->getSubscriptionMetrics(),
                
                // Enhanced reports
                'revenueTrend' => $this->reportsModel->getRevenueTrend($customStart, $customEnd),
                'subscriptionDistribution' => $this->reportsModel->getSubscriptionDistribution($customStart, $customEnd),
                'bookingStatusBreakdown' => $this->reportsModel->getBookingStatusBreakdown($customStart, $customEnd),

                // Feedback analytics
                'feedbackRatingDistribution' => $this->reportsModel->getFeedbackRatingDistribution($customStart, $customEnd),
                'feedbackList' => $this->reportsModel->getFeedbackList($customStart, $customEnd, 50),
                
                // Guest bookings analytics
                'guestBookingsStats' => $this->reportsModel->getGuestBookingsStats($filter, $customStart, $customEnd),
                
                // Calculate summary metrics for header
                'totalRevenue' => $this->reportsModel->getTotalRevenue($customStart, $customEnd),
                'totalBookings' => $this->reportsModel->getTotalBookings($customStart, $customEnd),
                'avgOccupancy' => $this->calculateAverageOccupancy($customStart, $customEnd),
                
                // Period comparison data
                'periodComparison' => $this->calculatePeriodComparison($customStart, $customEnd, $filter)
            ];
            
            // Check if this is an AJAX request
            if ($this->request->isAJAX()) {
                // Check if this is a filter change
                $isFilterChange = $this->request->getGet('filter_change') === '1';
                
                if ($isFilterChange) {
                    // Return only the reports content (stats + charts, without filter)
                    return view('pages/reports/content', $data);
                } else {
                    // First AJAX load - return full reports page with filter
                    return view('pages/reports/index', $data);
                }
            }
            
            // Return full reports page (direct URL access, non-AJAX)
            return view('pages/reports/index', $data);
            
        } catch (\Exception $e) {
            log_message('error', 'Reports Controller Error: ' . $e->getMessage());
            
            // Return error response
            if ($this->request->isAJAX()) {
                return $this->response->setJSON([
                    'error' => true,
                    'message' => 'Error loading reports: ' . $e->getMessage()
                ])->setStatusCode(500);
            }
            
            throw $e;
        }
    }
    
    /**
     * Calculate average occupancy rate
     */
    private function calculateAverageOccupancy($startDate, $endDate)
    {
        try {
            $areaPerformance = $this->reportsModel->getAreaPerformance($startDate, $endDate);
            if (empty($areaPerformance)) {
                return 0;
            }
            
            $totalUtilization = array_sum(array_column($areaPerformance, 'utilization_percent'));
            return count($areaPerformance) > 0 ? $totalUtilization / count($areaPerformance) : 0;
        } catch (\Exception $e) {
            log_message('error', 'calculateAverageOccupancy Error: ' . $e->getMessage());
            return 0;
        }
    }
    
    /**
     * Calculate period comparison data
     */
    private function calculatePeriodComparison($currentStart, $currentEnd, $filter)
    {
        try {
            // Map filter to previous period logic
            switch ($filter) {
                case 'today':
                    // Compare today vs yesterday
                    $previousStart = date('Y-m-d', strtotime($currentStart . ' -1 day'));
                    $previousEnd = $previousStart;
                    break;
                case 'last_7_days':
                case '7days':
                    // Compare this week vs last week
                    $previousStart = date('Y-m-d', strtotime($currentStart . ' -7 days'));
                    $previousEnd = date('Y-m-d', strtotime($currentEnd . ' -7 days'));
                    break;
                case '30_days':
                // case '30days': (replaced with 30_days)
                    // Compare this month vs last month
                    $previousStart = date('Y-m-d', strtotime($currentStart . ' -30 days'));
                    $previousEnd = date('Y-m-d', strtotime($currentEnd . ' -30 days'));
                    break;
                // case '90days': (replaced with this_year/last_year)
                    // Compare last 90 days vs previous 90 days
                    $previousStart = date('Y-m-d', strtotime($currentStart . ' -90 days'));
                    $previousEnd = date('Y-m-d', strtotime($currentEnd . ' -90 days'));
                    break;
                case 'yearly':
                case 'annual':
                    // Compare this year vs last year
                    $previousStart = date('Y-m-d', strtotime($currentStart . ' -1 year'));
                    $previousEnd = date('Y-m-d', strtotime($currentEnd . ' -1 year'));
                    break;
                case 'custom':
                    // For custom, compare same duration previous period
                    $currentDuration = strtotime($currentEnd) - strtotime($currentStart);
                    $previousEnd = date('Y-m-d', strtotime($currentStart . ' -1 day'));
                    $previousStart = date('Y-m-d', strtotime($previousEnd . ' -' . $currentDuration . ' seconds'));
                    break;
                default:
                    // Default to 30 days comparison
                    $previousStart = date('Y-m-d', strtotime($currentStart . ' -30 days'));
                    $previousEnd = date('Y-m-d', strtotime($currentEnd . ' -30 days'));
                    break;
            }
            
            // Get data from model
            $comparison = $this->reportsModel->getPeriodComparison($currentStart, $currentEnd, $previousStart, $previousEnd);
            
            // Add period labels for display
            $comparison['period_labels'] = [
                'current' => $this->formatPeriodLabel($currentStart, $currentEnd, $filter),
                'previous' => $this->formatPeriodLabel($previousStart, $previousEnd, $filter, true)
            ];
            
            // Log for debugging
            log_message('info', 'Period Comparison: Current: ' . $currentStart . ' to ' . $currentEnd . ', Previous: ' . $previousStart . ' to ' . $previousEnd);
            log_message('info', 'Comparison Data: ' . json_encode($comparison));
            
            return $comparison;
        } catch (\Exception $e) {
            log_message('error', 'calculatePeriodComparison Error: ' . $e->getMessage());
            return [
                'current' => ['revenue' => 0, 'bookings' => 0, 'active_users' => 0],
                'previous' => ['revenue' => 0, 'bookings' => 0, 'active_users' => 0],
                'changes' => ['revenue_change' => 0, 'bookings_change' => 0, 'users_change' => 0],
                'period_labels' => ['current' => 'Current Period', 'previous' => 'Previous Period']
            ];
        }
    }
    
    /**
     * Format period label for display
     */
    private function formatPeriodLabel($startDate, $endDate, $filter, $isPrevious = false)
    {
        $start = new \DateTime($startDate);
        $end = new \DateTime($endDate);
        
        switch ($filter) {
            case 'today':
                return $isPrevious ? 'Yesterday' : 'Today';
            case 'last_7_days':
            case '7days':
                return $isPrevious ? 'Last Week' : 'This Week';
            case '30_days':
            // case '30days': (replaced with 30_days)
                return $isPrevious ? 'Last Month' : 'This Month';
            // case '90days': (replaced with this_year/last_year)
                return $isPrevious ? 'Previous 90 Days' : 'Last 90 Days';
            case 'yearly':
            case 'annual':
                return $isPrevious ? 'Last Year' : 'This Year';
            case 'custom':
                if ($start->format('Y-m-d') === $end->format('Y-m-d')) {
                    return $start->format('M j, Y');
                } else {
                    return $start->format('M j') . ' - ' . $end->format('M j, Y');
                }
            default:
                return $isPrevious ? 'Previous Period' : 'Current Period';
        }
    }
    
    /**
     * Export reports data in various formats
     */
    public function export()
    {
        try {
            // Get filter and date range from request
            $filter = $this->request->getGet('filter') ?? 'today';
            $customStart = $this->request->getGet('start_date');
            $customEnd = $this->request->getGet('end_date');
            $format = $this->request->getGet('format') ?? 'excel';
            
            // Set defaults based on filter if not provided
            if (!$customStart || !$customEnd) {
                $baseDate = date('Y-m-d');
                $customEnd = $baseDate;
                
                switch ($filter) {
                    case 'today':
                        $customStart = $baseDate;
                        break;
                    case 'last_7_days':
                        $customStart = date('Y-m-d', strtotime($baseDate . ' -6 days')); // Last 7 days inclusive
                        break;
                    case '30_days':
                        $customStart = date('Y-m-d', strtotime($baseDate . ' -29 days')); // Last 30 days inclusive
                        break;
                    case 'this_year':
                        $customStart = date('Y-01-01'); // Start of current year
                        break;
                    case 'last_year':
                        $customStart = date('Y-01-01', strtotime($baseDate . ' -1 year')); // Start of last year
                        $customEnd = date('Y-12-31', strtotime($baseDate . ' -1 year')); // End of last year
                        break;
                    default:
                        $customStart = date('Y-m-d', strtotime($baseDate . ' -29 days')); // Default to 30 days
                }
            }
            
            // Get ALL data for export
            $exportData = [
                'report_info' => [
                    'title' => 'TapPark Reports',
                    'filter' => $filter,
                    'date_range' => $customStart . ' to ' . $customEnd,
                    'generated_at' => date('Y-m-d H:i:s'),
                    'generated_by' => session()->get('user_name') ?? 'System'
                ],
                
                // Summary Metrics
                'summary_metrics' => [
                    'total_revenue' => array_sum(array_column($this->reportsModel->getRevenueByPlan($customStart, $customEnd), 'total_revenue')),
                    'total_bookings' => array_sum(array_column($this->reportsModel->getRevenueByArea($customStart, $customEnd), 'total_bookings')),
                    'avg_occupancy' => $this->calculateAverageOccupancy($customStart, $customEnd),
                    'total_users' => $this->reportsModel->getUserAnalytics($customStart, $customEnd)['total_users'] ?? 0,
                    'active_users' => $this->reportsModel->getUserAnalytics($customStart, $customEnd)['active_users'] ?? 0,
                    'retention_rate' => $this->reportsModel->getUserAnalytics($customStart, $customEnd)['retention_rate'] ?? 0,
                    'avg_duration' => $this->reportsModel->getAverageParkingDuration($customStart, $customEnd),
                    'lifetime_value' => $this->reportsModel->getUserAnalytics($customStart, $customEnd)['lifetime_value'] ?? 0
                ],
                
                // Revenue Analytics
                'revenue_by_area' => $this->reportsModel->getRevenueByArea($customStart, $customEnd),
                'revenue_by_plan' => $this->reportsModel->getRevenueByPlan($customStart, $customEnd),
                'revenue_by_hour' => $this->reportsModel->getRevenueByHour($customStart, $customEnd),
                'revenue_by_day_of_week' => $this->reportsModel->getRevenueByDayOfWeek($customStart, $customEnd),
                'revenue_trend' => $this->reportsModel->getRevenueTrend($customStart, $customEnd),
                'payment_methods' => $this->reportsModel->getPaymentMethodDistribution($customStart, $customEnd),
                
                // Booking Analytics
                'booking_metrics' => $this->reportsModel->getBookingMetrics($customStart, $customEnd),
                'bookings_by_day_of_week' => $this->reportsModel->getBookingsByDayOfWeek($customStart, $customEnd),
                'booking_status_breakdown' => $this->reportsModel->getBookingStatusBreakdown($customStart, $customEnd),
                'peak_hours' => $this->reportsModel->getPeakHours($customStart, $customEnd),
                
                // Occupancy Analytics
                'hourly_occupancy' => $this->reportsModel->getHourlyOccupancy($customStart, $customEnd),
                'area_performance' => $this->reportsModel->getAreaPerformance($customStart, $customEnd),
                
                // User Analytics
                'user_analytics' => $this->reportsModel->getUserAnalytics($customStart, $customEnd),
                'user_growth' => $this->reportsModel->getMonthlyUserGrowth($customStart, $customEnd),
                'user_activity_metrics' => $this->reportsModel->getUserActivityMetrics(),
                
                // Operational Reports
                'subscription_metrics' => $this->reportsModel->getSubscriptionMetrics(),
                'subscription_distribution' => $this->reportsModel->getSubscriptionDistribution($customStart, $customEnd),
                'vehicle_types' => $this->reportsModel->getVehicleTypeDistribution($customStart, $customEnd),
                'popular_areas' => $this->reportsModel->getPopularAreas($customStart, $customEnd),
                
                // Feedback Analytics
                'feedback_rating_distribution' => $this->reportsModel->getFeedbackRatingDistribution($customStart, $customEnd),
                'feedback_list' => $this->reportsModel->getFeedbackList($customStart, $customEnd, 50),
                
                // Period Comparison
                'period_comparison' => $this->calculatePeriodComparison($customStart, $customEnd, $filter)
            ];
            
            // Export based on format
            switch (strtolower($format)) {
                case 'csv':
                default:
                    return $this->exportToCSV($exportData);
            }
            
        } catch (\Exception $e) {
            log_message('error', 'Export Error: ' . $e->getMessage());
            return $this->response->setJSON([
                'error' => true,
                'message' => 'Export failed: ' . $e->getMessage()
            ])->setStatusCode(500);
        }
    }
    
    /**
     * Export to Excel format
     */
    private function exportToExcel($data)
    {
        // Create filename
        $filename = 'tappark-reports-' . date('Y-m-d-His') . '.xlsx';
        
        // Start output buffering
        ob_start();
        
        // Create CSV content (Excel can open CSV files)
        $output = fopen('php://output', 'w');
        
        // Add report info
        fputcsv($output, ['TapPark Reports Export']);
        fputcsv($output, ['Filter: ' . $data['report_info']['filter']]);
        fputcsv($output, ['Date Range: ' . $data['report_info']['date_range']]);
        fputcsv($output, ['Generated: ' . $data['report_info']['generated_at']]);
        fputcsv($output, ['Generated By: ' . $data['report_info']['generated_by']]);
        fputcsv($output, []);
        
        // Summary Metrics
        fputcsv($output, ['SUMMARY METRICS']);
        fputcsv($output, ['Metric', 'Value']);
        foreach ($data['summary_metrics'] as $metric => $value) {
            $displayValue = is_numeric($value) && $value > 1000 ? number_format($value, 2) : $value;
            fputcsv($output, [ucwords(str_replace('_', ' ', $metric)), $displayValue]);
        }
        fputcsv($output, []);
        
        // Revenue by Area
        fputcsv($output, ['REVENUE BY AREA']);
        fputcsv($output, ['Area Name', 'Total Bookings', 'Total Revenue', 'Revenue per Spot', 'Utilization %']);
        foreach ($data['revenue_by_area'] as $area) {
            fputcsv($output, [
                $area['parking_area_name'] ?? '',
                $area['total_bookings'] ?? 0,
                $area['total_revenue'] ?? 0,
                $area['revenue_per_spot'] ?? 0,
                ($area['utilization_percent'] ?? 0) . '%'
            ]);
        }
        fputcsv($output, []);
        
        // Revenue by Plan
        fputcsv($output, ['REVENUE BY SUBSCRIPTION PLAN']);
        fputcsv($output, ['Plan Name', 'Subscription Count', 'Revenue']);
        foreach ($data['revenue_by_plan'] as $plan) {
            fputcsv($output, [
                $plan['plan_name'] ?? '',
                $plan['subscription_count'] ?? 0,
                $plan['total_revenue'] ?? 0
            ]);
        }
        fputcsv($output, []);
        
        // Booking Metrics
        fputcsv($output, ['BOOKING METRICS']);
        fputcsv($output, ['Metric', 'Value']);
        foreach ($data['booking_metrics'] as $metric => $value) {
            fputcsv($output, [ucwords(str_replace('_', ' ', $metric)), $value]);
        }
        fputcsv($output, []);
        
        // Bookings by Day of Week
        fputcsv($output, ['BOOKINGS BY DAY OF WEEK']);
        fputcsv($output, ['Day', 'Bookings', 'Unique Users']);
        foreach ($data['bookings_by_day_of_week'] as $day) {
            fputcsv($output, [
                $day['day_name'] ?? '',
                $day['booking_count'] ?? 0,
                $day['unique_users'] ?? 0
            ]);
        }
        fputcsv($output, []);
        
        // User Analytics
        fputcsv($output, ['USER ANALYTICS']);
        fputcsv($output, ['Metric', 'Value']);
        foreach ($data['user_analytics'] as $metric => $value) {
            if ($metric === 'top_spending_users' && is_array($value)) {
                fputcsv($output, ['TOP SPENDING USERS']);
                fputcsv($output, ['User Name', 'Email', 'Bookings', 'Total Spent', 'Last Booking']);
                foreach ($value as $user) {
                    fputcsv($output, [
                        $user['user_name'] ?? '',
                        $user['email'] ?? '',
                        $user['booking_count'] ?? 0,
                        '₱' . number_format($user['total_spent'] ?? 0, 2),
                        $user['last_booking'] ?? ''
                    ]);
                }
            } else {
                fputcsv($output, [ucwords(str_replace('_', ' ', $metric)), $value]);
            }
        }
        fputcsv($output, []);
        
        // Area Performance
        fputcsv($output, ['AREA PERFORMANCE']);
        fputcsv($output, ['Area', 'Total Spots', 'Total Bookings', 'Total Revenue', 'Utilization %', 'Revenue per Spot', 'Turnover Rate']);
        foreach ($data['area_performance'] as $area) {
            fputcsv($output, [
                $area['parking_area_name'] ?? '',
                $area['total_spots'] ?? 0,
                $area['total_bookings'] ?? 0,
                $area['total_revenue'] ?? 0,
                ($area['utilization_percent'] ?? 0) . '%',
                $area['revenue_per_spot'] ?? 0,
                $area['turnover_rate'] ?? 0
            ]);
        }
        
        fclose($output);
        $content = ob_get_clean();
        
        // Set proper headers for Excel/CSV download
        return $this->response
            ->setHeader('Content-Type', 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet')
            ->setHeader('Content-Disposition', 'attachment; filename="' . $filename . '"')
            ->setHeader('Cache-Control', 'max-age=0')
            ->setHeader('Pragma', 'public')
            ->setHeader('Expires', '0')
            ->setBody($content);
    }
    
    /**
     * Export to CSV format - Complete Report Data
     */
    private function exportToCSV($data)
    {
        // Create filename
        $filename = 'tappark-reports-' . date('Y-m-d-His') . '.csv';
        
        // Start output buffering
        ob_start();
        
        // Create CSV content
        $output = fopen('php://output', 'w');
        
        // ===== REPORT HEADER =====
        fputcsv($output, ['TAPPARK COMPLETE REPORTS EXPORT']);
        fputcsv($output, ['Filter: ' . $data['report_info']['filter']]);
        fputcsv($output, ['Date Range: ' . $data['report_info']['date_range']]);
        fputcsv($output, ['Generated: ' . $data['report_info']['generated_at']]);
        fputcsv($output, ['Generated By: ' . $data['report_info']['generated_by']]);
        fputcsv($output, []);
        
        // ===== SUMMARY METRICS CARDS =====
        fputcsv($output, ['SUMMARY METRICS CARDS']);
        fputcsv($output, ['Metric', 'Value', 'Unit']);
        foreach ($data['summary_metrics'] as $metric => $value) {
            $displayValue = is_numeric($value) && $value > 1000 ? number_format($value, 2) : $value;
            $unit = '';
            if (strpos($metric, 'revenue') !== false) $unit = '₱';
            elseif (strpos($metric, 'rate') !== false || strpos($metric, 'occupancy') !== false) $unit = '%';
            elseif (strpos($metric, 'duration') !== false) $unit = 'minutes';
            fputcsv($output, [ucwords(str_replace('_', ' ', $metric)), $displayValue, $unit]);
        }
        fputcsv($output, []);
        
        // ===== REVENUE ANALYTICS =====
        fputcsv($output, ['REVENUE ANALYTICS']);
        
        // Revenue by Area Chart Data
        fputcsv($output, ['Revenue by Area']);
        fputcsv($output, ['Area Name', 'Total Bookings', 'Total Revenue (₱)', 'Revenue per Spot (₱)', 'Utilization (%)']);
        foreach ($data['revenue_by_area'] as $area) {
            fputcsv($output, [
                $area['parking_area_name'] ?? '',
                $area['total_bookings'] ?? 0,
                number_format($area['total_revenue'] ?? 0, 2),
                number_format($area['revenue_per_spot'] ?? 0, 2),
                number_format($area['utilization_percent'] ?? 0, 1)
            ]);
        }
        fputcsv($output, []);
        
        // Revenue by Plan Chart Data
        fputcsv($output, ['Revenue by Subscription Plan']);
        fputcsv($output, ['Plan Name', 'Subscription Count', 'Total Revenue (₱)', 'Average Revenue per Subscription (₱)']);
        foreach ($data['revenue_by_plan'] as $plan) {
            $avgRevenue = $plan['subscription_count'] > 0 ? ($plan['total_revenue'] / $plan['subscription_count']) : 0;
            fputcsv($output, [
                $plan['plan_name'] ?? '',
                $plan['subscription_count'] ?? 0,
                number_format($plan['total_revenue'] ?? 0, 2),
                number_format($avgRevenue, 2)
            ]);
        }
        fputcsv($output, []);
        
        // Revenue by Hour Chart Data
        fputcsv($output, ['Revenue by Hour of Day']);
        fputcsv($output, ['Hour', 'Revenue (₱)', 'Bookings Count', 'Average Revenue per Booking (₱)']);
        foreach ($data['revenue_by_hour'] as $hour) {
            $avgRevenue = $hour['booking_count'] > 0 ? ($hour['total_revenue'] / $hour['booking_count']) : 0;
            fputcsv($output, [
                ($hour['hour'] ?? 0) . ':00',
                number_format($hour['total_revenue'] ?? 0, 2),
                $hour['booking_count'] ?? 0,
                number_format($avgRevenue, 2)
            ]);
        }
        fputcsv($output, []);
        
        // Revenue by Day of Week Chart Data
        fputcsv($output, ['Revenue by Day of Week']);
        fputcsv($output, ['Day', 'Revenue (₱)', 'Bookings Count', 'Unique Users', 'Average Revenue per Booking (₱)']);
        foreach ($data['revenue_by_day_of_week'] as $day) {
            $avgRevenue = $day['booking_count'] > 0 ? ($day['total_revenue'] / $day['booking_count']) : 0;
            fputcsv($output, [
                $day['day_name'] ?? '',
                number_format($day['total_revenue'] ?? 0, 2),
                $day['booking_count'] ?? 0,
                $day['unique_users'] ?? 0,
                number_format($avgRevenue, 2)
            ]);
        }
        fputcsv($output, []);
        
        // Revenue Trend Chart Data
        fputcsv($output, ['Revenue Trend Over Time']);
        fputcsv($output, ['Date', 'Revenue (₱)', 'Bookings Count', 'Growth Rate (%)']);
        foreach ($data['revenue_trend'] as $trend) {
            fputcsv($output, [
                $trend['date'] ?? '',
                number_format($trend['revenue'] ?? 0, 2),
                $trend['bookings'] ?? 0,
                number_format($trend['growth_rate'] ?? 0, 2)
            ]);
        }
        fputcsv($output, []);
        
        // Payment Methods Distribution
        fputcsv($output, ['Payment Methods Distribution']);
        fputcsv($output, ['Payment Method', 'Count', 'Total Amount (₱)', 'Percentage (%)']);
        foreach ($data['payment_methods'] as $method) {
            fputcsv($output, [
                $method['payment_method'] ?? '',
                $method['count'] ?? 0,
                number_format($method['total_amount'] ?? 0, 2),
                number_format($method['percentage'] ?? 0, 1)
            ]);
        }
        fputcsv($output, []);
        
        // ===== BOOKING ANALYTICS =====
        fputcsv($output, ['BOOKING ANALYTICS']);
        
        // Booking Metrics
        fputcsv($output, ['Booking Metrics Summary']);
        fputcsv($output, ['Metric', 'Value', 'Unit']);
        foreach ($data['booking_metrics'] as $metric => $value) {
            $displayValue = is_numeric($value) && $value > 1000 ? number_format($value, 2) : $value;
            $unit = '';
            if (strpos($metric, 'rate') !== false) $unit = '%';
            elseif (strpos($metric, 'bookings') !== false) $unit = 'count';
            fputcsv($output, [ucwords(str_replace('_', ' ', $metric)), $displayValue, $unit]);
        }
        fputcsv($output, []);
        
        // Bookings by Day of Week
        fputcsv($output, ['Bookings by Day of Week']);
        fputcsv($output, ['Day', 'Booking Count', 'Unique Users', 'Average Bookings per User', 'Peak Hour']);
        foreach ($data['bookings_by_day_of_week'] as $day) {
            $avgBookings = $day['unique_users'] > 0 ? ($day['booking_count'] / $day['unique_users']) : 0;
            fputcsv($output, [
                $day['day_name'] ?? '',
                $day['booking_count'] ?? 0,
                $day['unique_users'] ?? 0,
                number_format($avgBookings, 2),
                ($day['peak_hour'] ?? 0) . ':00'
            ]);
        }
        fputcsv($output, []);
        
        // Booking Status Breakdown
        fputcsv($output, ['Booking Status Breakdown']);
        fputcsv($output, ['Status', 'Count', 'Percentage (%)', 'Revenue Impact (₱)']);
        foreach ($data['booking_status_breakdown'] as $status) {
            fputcsv($output, [
                $status['status'] ?? '',
                $status['count'] ?? 0,
                number_format($status['percentage'] ?? 0, 1),
                number_format($status['revenue_impact'] ?? 0, 2)
            ]);
        }
        fputcsv($output, []);
        
        // Peak Hours Analysis
        fputcsv($output, ['Peak Hours Analysis']);
        fputcsv($output, ['Hour', 'Booking Count', 'Revenue (₱)', 'Occupancy Rate (%)', 'Average Duration (minutes)']);
        foreach ($data['peak_hours'] as $hour) {
            fputcsv($output, [
                ($hour['hour'] ?? 0) . ':00',
                $hour['booking_count'] ?? 0,
                number_format($hour['revenue'] ?? 0, 2),
                number_format($hour['occupancy_rate'] ?? 0, 1),
                $hour['avg_duration'] ?? 0
            ]);
        }
        fputcsv($output, []);
        
        // ===== OCCUPANCY ANALYTICS =====
        fputcsv($output, ['OCCUPANCY ANALYTICS']);
        
        // Hourly Occupancy Heatmap Data
        fputcsv($output, ['Hourly Occupancy Heatmap']);
        fputcsv($output, ['Hour', 'Occupancy Rate (%)', 'Available Spots', 'Occupied Spots', 'Total Spots']);
        foreach ($data['hourly_occupancy'] as $hour) {
            fputcsv($output, [
                ($hour['hour'] ?? 0) . ':00',
                number_format($hour['occupancy_rate'] ?? 0, 1),
                $hour['available_spots'] ?? 0,
                $hour['occupied_spots'] ?? 0,
                $hour['total_spots'] ?? 0
            ]);
        }
        fputcsv($output, []);
        
        // Area Performance
        fputcsv($output, ['Area Performance Analysis']);
        fputcsv($output, ['Area Name', 'Total Spots', 'Total Bookings', 'Total Revenue (₱)', 'Utilization (%)', 'Revenue per Spot (₱)', 'Turnover Rate', 'Peak Hour']);
        foreach ($data['area_performance'] as $area) {
            fputcsv($output, [
                $area['parking_area_name'] ?? '',
                $area['total_spots'] ?? 0,
                $area['total_bookings'] ?? 0,
                number_format($area['total_revenue'] ?? 0, 2),
                number_format($area['utilization_percent'] ?? 0, 1),
                number_format($area['revenue_per_spot'] ?? 0, 2),
                number_format($area['turnover_rate'] ?? 0, 2),
                ($area['peak_hour'] ?? 0) . ':00'
            ]);
        }
        fputcsv($output, []);
        
        // ===== USER ANALYTICS =====
        fputcsv($output, ['USER ANALYTICS']);
        
        // User Analytics Summary
        fputcsv($output, ['User Analytics Summary']);
        fputcsv($output, ['Metric', 'Value', 'Unit']);
        foreach ($data['user_analytics'] as $metric => $value) {
            if ($metric !== 'top_spending_users') {
                $displayValue = is_numeric($value) && $value > 1000 ? number_format($value, 2) : $value;
                $unit = '';
                if (strpos($metric, 'rate') !== false) $unit = '%';
                elseif (strpos($metric, 'revenue') !== false) $unit = '₱';
                fputcsv($output, [ucwords(str_replace('_', ' ', $metric)), $displayValue, $unit]);
            }
        }
        fputcsv($output, []);
        
        // Top Spending Users
        fputcsv($output, ['Top Spending Users']);
        fputcsv($output, ['User Name', 'Email', 'Total Bookings', 'Total Spent (₱)', 'Average per Booking (₱)', 'Last Booking Date', 'Registration Date']);
        if (isset($data['user_analytics']['top_spending_users']) && is_array($data['user_analytics']['top_spending_users'])) {
            foreach ($data['user_analytics']['top_spending_users'] as $user) {
                $avgPerBooking = $user['booking_count'] > 0 ? ($user['total_spent'] / $user['booking_count']) : 0;
                fputcsv($output, [
                    $user['user_name'] ?? '',
                    $user['email'] ?? '',
                    $user['booking_count'] ?? 0,
                    number_format($user['total_spent'] ?? 0, 2),
                    number_format($avgPerBooking, 2),
                    $user['last_booking'] ?? '',
                    $user['registration_date'] ?? ''
                ]);
            }
        }
        fputcsv($output, []);
        
        // User Growth Trend
        fputcsv($output, ['User Growth Trend']);
        fputcsv($output, ['Month', 'New Users', 'Total Users', 'Growth Rate (%)', 'Active Users']);
        foreach ($data['user_growth'] as $growth) {
            fputcsv($output, [
                $growth['month'] ?? '',
                $growth['new_users'] ?? 0,
                $growth['total_users'] ?? 0,
                number_format($growth['growth_rate'] ?? 0, 2),
                $growth['active_users'] ?? 0
            ]);
        }
        fputcsv($output, []);
        
        // User Activity Metrics
        fputcsv($output, ['User Activity Metrics']);
        fputcsv($output, ['Activity Type', 'Count', 'Percentage (%)', 'Time Period']);
        foreach ($data['user_activity_metrics'] as $metric) {
            fputcsv($output, [
                $metric['activity_type'] ?? '',
                $metric['count'] ?? 0,
                number_format($metric['percentage'] ?? 0, 1),
                $metric['time_period'] ?? ''
            ]);
        }
        fputcsv($output, []);
        
        // ===== OPERATIONAL REPORTS =====
        fputcsv($output, ['OPERATIONAL REPORTS']);
        
        // Subscription Metrics
        fputcsv($output, ['Subscription Metrics']);
        fputcsv($output, ['Metric', 'Value', 'Unit']);
        foreach ($data['subscription_metrics'] as $metric => $value) {
            $displayValue = is_numeric($value) && $value > 1000 ? number_format($value, 2) : $value;
            $unit = '';
            if (strpos($metric, 'rate') !== false) $unit = '%';
            elseif (strpos($metric, 'revenue') !== false) $unit = '₱';
            fputcsv($output, [ucwords(str_replace('_', ' ', $metric)), $displayValue, $unit]);
        }
        fputcsv($output, []);
        
        // Subscription Distribution
        fputcsv($output, ['Subscription Distribution by Plan']);
        fputcsv($output, ['Plan Name', 'Active Subscriptions', 'Total Revenue (₱)', 'Average Duration (days)', 'Cancellation Rate (%)']);
        foreach ($data['subscription_distribution'] as $dist) {
            fputcsv($output, [
                $dist['plan_name'] ?? '',
                $dist['active_subscriptions'] ?? 0,
                number_format($dist['total_revenue'] ?? 0, 2),
                $dist['avg_duration_days'] ?? 0,
                number_format($dist['cancellation_rate'] ?? 0, 1)
            ]);
        }
        fputcsv($output, []);
        
        // Vehicle Types Distribution
        fputcsv($output, ['Vehicle Types Distribution']);
        fputcsv($output, ['Vehicle Type', 'Count', 'Percentage (%)', 'Average Duration (minutes)', 'Peak Hour']);
        foreach ($data['vehicle_types'] as $vehicle) {
            fputcsv($output, [
                $vehicle['vehicle_type'] ?? '',
                $vehicle['count'] ?? 0,
                number_format($vehicle['percentage'] ?? 0, 1),
                $vehicle['avg_duration'] ?? 0,
                ($vehicle['peak_hour'] ?? 0) . ':00'
            ]);
        }
        fputcsv($output, []);
        
        // Popular Areas
        fputcsv($output, ['Popular Areas Analysis']);
        fputcsv($output, ['Area Name', 'Total Bookings', 'Unique Users', 'Average Rating', 'Peak Time', 'Average Duration (minutes)']);
        foreach ($data['popular_areas'] as $area) {
            fputcsv($output, [
                $area['parking_area_name'] ?? '',
                $area['total_bookings'] ?? 0,
                $area['unique_users'] ?? 0,
                number_format($area['avg_rating'] ?? 0, 1),
                ($area['peak_time'] ?? 0) . ':00',
                $area['avg_duration'] ?? 0
            ]);
        }
        fputcsv($output, []);
        
        // ===== PERIOD COMPARISON =====
        fputcsv($output, ['PERIOD COMPARISON ANALYSIS']);
        fputcsv($output, ['Metric', 'Current Period', 'Previous Period', 'Change (%)', 'Trend']);
        
        $comparison = $data['period_comparison'];
        if (isset($comparison['current']) && isset($comparison['previous']) && isset($comparison['changes'])) {
            foreach ($comparison['current'] as $key => $currentValue) {
                $previousValue = $comparison['previous'][$key] ?? 0;
                $change = $comparison['changes'][$key . '_change'] ?? 0;
                $trend = $change > 0 ? '📈 Up' : ($change < 0 ? '📉 Down' : '➡️ Stable');
                
                fputcsv($output, [
                    ucwords(str_replace('_', ' ', $key)),
                    is_numeric($currentValue) && $currentValue > 1000 ? number_format($currentValue, 2) : $currentValue,
                    is_numeric($previousValue) && $previousValue > 1000 ? number_format($previousValue, 2) : $previousValue,
                    number_format($change, 2) . '%',
                    $trend
                ]);
            }
        }
        fputcsv($output, []);
        
        // ===== FEEDBACK ANALYTICS =====
        fputcsv($output, ['FEEDBACK ANALYTICS']);
        
        // Feedback Rating Distribution
        fputcsv($output, ['Feedback Rating Distribution']);
        fputcsv($output, ['Rating', 'Count', 'Percentage (%)', 'Average Rating', 'Total Feedback']);
        foreach ($data['feedback_rating_distribution'] as $rating) {
            fputcsv($output, [
                $rating['rating'] ?? 0 . ' Stars',
                $rating['count'] ?? 0,
                number_format($rating['percentage'] ?? 0, 1),
                number_format($rating['avg_rating'] ?? 0, 2),
                $rating['total_feedback'] ?? 0
            ]);
        }
        fputcsv($output, []);
        
        // Detailed Feedback List
        fputcsv($output, ['Detailed Feedback List']);
        fputcsv($output, ['Feedback ID', 'User Name', 'User Email', 'Rating', 'Feedback Text', 'Area Name', 'Booking ID', 'Created Date', 'Response Status', 'Response Date']);
        if (isset($data['feedback_list']) && is_array($data['feedback_list'])) {
            foreach ($data['feedback_list'] as $feedback) {
                fputcsv($output, [
                    $feedback['feedback_id'] ?? '',
                    $feedback['user_name'] ?? '',
                    $feedback['user_email'] ?? '',
                    ($feedback['rating'] ?? 0) . ' Stars',
                    $feedback['feedback_text'] ?? '',
                    $feedback['parking_area_name'] ?? '',
                    $feedback['booking_id'] ?? '',
                    $feedback['created_at'] ?? '',
                    $feedback['response_status'] ?? 'Pending',
                    $feedback['response_date'] ?? 'Not responded'
                ]);
            }
        }
        fputcsv($output, []);
        
        // ===== REPORT FOOTER =====
        fputcsv($output, ['REPORT FOOTER']);
        fputcsv($output, ['Total Sections Exported', '13']);
        fputcsv($output, ['Data Points Included', count($data['revenue_by_area']) + count($data['booking_metrics']) + count($data['user_analytics']) + count($data['feedback_list'] ?? [])]);
        fputcsv($output, ['Export Completed', date('Y-m-d H:i:s')]);
        fputcsv($output, ['System', 'TapPark Parking Management System']);
        
        fclose($output);
        $content = ob_get_clean();
        
        // Set proper headers for CSV download
        return $this->response
            ->setHeader('Content-Type', 'text/csv')
            ->setHeader('Content-Disposition', 'attachment; filename="' . $filename . '"')
            ->setHeader('Cache-Control', 'max-age=0')
            ->setHeader('Pragma', 'public')
            ->setHeader('Expires', '0')
            ->setBody($content);
    }
}
