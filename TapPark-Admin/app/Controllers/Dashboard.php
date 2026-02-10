<?php

namespace App\Controllers;

use App\Models\DashboardModel;

class Dashboard extends BaseController
{
    protected $dashboardModel;
    
    public function __construct()
    {
        $this->dashboardModel = new DashboardModel();
    }
    
    public function index()
    {
        // Get filter from request (default: today)
        $filter = $this->request->getGet('filter') ?? 'today';
        $customStart = $this->request->getGet('start_date');
        $customEnd = $this->request->getGet('end_date');
        
        // Calculate date range based on filter
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
        
        // Get dashboard data from model
        $data = [
            'filter' => $filter,
            'customStart' => $customStart,
            'customEnd' => $customEnd,
            
            // Stats cards
            'totalUsers' => $this->dashboardModel->getTotalUsers(),
            'totalSubscribers' => $this->dashboardModel->getTotalSubscribers(),
            'activeSubscribers' => $this->dashboardModel->getActiveSubscribers(),
            'inactiveSubscribers' => $this->dashboardModel->getInactiveSubscribers(),
            'activeBookings' => $this->dashboardModel->getActiveBookings(),
            'totalSpots' => $this->dashboardModel->getTotalParkingSpots(),
            'revenue' => $this->dashboardModel->getRevenue($filter, $customStart, $customEnd),
            'occupancyRate' => $this->dashboardModel->getOccupancyRate(),
            'onlineAttendants' => $this->dashboardModel->getOnlineAttendants(),
            
            // Chart data (these will be passed to JavaScript)
            'revenueChart' => $this->dashboardModel->getRevenueChartData($filter, $customStart, $customEnd),
            'occupancyChart' => $this->dashboardModel->getOccupancyChartData(),
            'bookingsChart' => $this->dashboardModel->getBookingsChartData($filter, $customStart, $customEnd),
            'userGrowthChart' => $this->dashboardModel->getUserGrowthChartData($filter, $customStart, $customEnd),
            
            // Optional enhanced charts
            'hourBalanceTrends' => $this->dashboardModel->getHourBalanceTrends(),
            'avgRatingTrend' => $this->dashboardModel->getAverageRatingOverTime($filter, $customStart, $customEnd),
            'guestBookingsStats' => $this->dashboardModel->getGuestBookingsStats($filter, $customStart, $customEnd),
            
            // Additional data (if needed)
            'recentActivity' => $this->dashboardModel->getRecentActivity(5),
            'activeVehicles' => $this->dashboardModel->getActiveVehiclesBreakdown(),
            'topFeedbacks' => $this->dashboardModel->getTopFeedbacks(10),
            'averageRating' => $this->dashboardModel->getAverageRating()
        ];
        
        // Check if this is an AJAX request
        if ($this->request->isAJAX()) {
            // Check if this is the first load (contentArea has loading spinner or is empty)
            // vs a filter change (dashboardContent already exists)
            // We'll use a query parameter to determine this
            $isFilterChange = $this->request->getGet('filter_change') === '1';
            
            if ($isFilterChange) {
                // Return only the dashboard content (stats + charts, without filter)
                // This allows the filter component to stay in place
                return view('pages/dashboard/content', $data);
            } else {
                // First AJAX load - return full dashboard page with filter
                return view('pages/dashboard/index', $data);
            }
        }
        
        // Return full dashboard page (direct URL access, non-AJAX)
        // This includes the filter component and initializes everything
        return view('pages/dashboard/index', $data);
    }
}

