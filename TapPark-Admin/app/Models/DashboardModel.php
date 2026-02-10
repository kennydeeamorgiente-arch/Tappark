<?php

namespace App\Models;

use CodeIgniter\Model;

class DashboardModel extends Model
{
    private const MAX_RANGE_DAYS = 365;
    private const MAX_CHART_POINTS = 60;
    private const MAX_TOP_CATEGORIES = 5;
    protected $db;
    
    public function __construct()
    {
        $this->db = \Config\Database::connect();
    }
    
    /**
     * Get guest bookings statistics for dashboard
     */
    public function getGuestBookingsStats($filter = 'today', $customStart = null, $customEnd = null)
    {
        $dateRange = $this->getDateRange($filter, $customStart, $customEnd);
        $start = $dateRange['start'];
        $end = $dateRange['end'];
        
        $db = \Config\Database::connect();
        
        $totalGuestBookings = $db->table('guest_bookings')
            ->where('created_at >=', $start)
            ->where('created_at <=', $end)
            ->countAllResults();
        
        $rawSeries = $db->table('guest_bookings')
            ->select('DATE(created_at) as grouping_date')
            ->selectCount('*', 'count')
            ->where('created_at >=', $start)
            ->where('created_at <=', $end)
            ->groupBy('grouping_date')
            ->orderBy('grouping_date', 'ASC')
            ->get()
            ->getResultArray();
        $seriesMap = $this->buildDailyValueMap($rawSeries, 'grouping_date', 'count');
        $seriesBuckets = $this->shouldUseMonthlyBuckets($filter)
            ? $this->buildMonthlyBuckets(new \DateTime($start), new \DateTime($end))
            : null;
        $bucketedSeries = $this->buildBucketedSumSeries(
            $seriesMap,
            new \DateTime($start),
            new \DateTime($end),
            $seriesBuckets
        );
        $guestBookingsByDate = [];
        foreach ($bucketedSeries['labels'] as $idx => $label) {
            $guestBookingsByDate[] = [
                'label' => $label,
                'count' => $bucketedSeries['values'][$idx]
            ];
        }
        
        $guestBookingsByVehicle = $db->table('guest_bookings gb')
            ->select('v.vehicle_type, COUNT(*) as count')
            ->join('vehicles v', 'v.vehicle_id = gb.vehicle_id')
            ->where('gb.created_at >=', $start)
            ->where('gb.created_at <=', $end)
            ->groupBy('v.vehicle_type')
            ->orderBy('count', 'DESC')
            ->limit(self::MAX_TOP_CATEGORIES)
            ->get()
            ->getResultArray();
        
        $guestBookingsByAttendant = $db->table('guest_bookings gb')
            ->select('CONCAT(u.first_name, " ", u.last_name) as attendant_name, COUNT(*) as count')
            ->join('users u', 'u.user_id = gb.attendant_id')
            ->where('gb.created_at >=', $start)
            ->where('gb.created_at <=', $end)
            ->groupBy('gb.attendant_id, u.first_name, u.last_name')
            ->orderBy('count', 'DESC')
            ->limit(self::MAX_TOP_CATEGORIES)
            ->get()
            ->getResultArray();
        
        return [
            'total_guest_bookings' => $totalGuestBookings,
            'guest_bookings_by_date' => $guestBookingsByDate,
            'guest_bookings_by_vehicle' => $guestBookingsByVehicle,
            'guest_bookings_by_attendant' => $guestBookingsByAttendant
        ];
    }
    
    /**
     * Get date range based on filter type
     */
    private function getDateRange($filter = 'today', $customStart = null, $customEnd = null)
    {
        $baseDate = date('Y-m-d');
        $end = $baseDate . ' 23:59:59';
        
        switch ($filter) {
            case 'today':
                $start = $baseDate . ' 00:00:00';
                break;
            case 'last_7_days':
                $start = date('Y-m-d 00:00:00', strtotime($baseDate . ' -6 days'));
                break;
            case '30_days':
                $start = date('Y-m-d 00:00:00', strtotime($baseDate . ' -29 days'));
                break;
            case 'this_year':
                $start = date('Y-01-01 00:00:00');
                break;
            case 'last_year':
                $start = date('Y-01-01 00:00:00', strtotime($baseDate . ' -1 year'));
                $end = date('Y-12-31 23:59:59', strtotime($baseDate . ' -1 year'));
                break;
            case 'custom':
                $customStartDate = $customStart ? date('Y-m-d', strtotime($customStart)) : date('Y-m-d', strtotime($baseDate . ' -29 days'));
                $customEndDate = $customEnd ? date('Y-m-d', strtotime($customEnd)) : $baseDate;
                
                if (strtotime($customStartDate) > strtotime($customEndDate)) {
                    [$customStartDate, $customEndDate] = [$customEndDate, $customStartDate];
                }
                
                $start = $customStartDate . ' 00:00:00';
                $end = $customEndDate . ' 23:59:59';
                break;
            default:
                $start = $baseDate . ' 00:00:00';
        }
        
        [$start, $end] = $this->clampDateRange($start, $end);
        
        return ['start' => $start, 'end' => $end];
    }

    private function clampDateRange(string $start, string $end): array
    {
        $startDate = new \DateTime($start);
        $endDate = new \DateTime($end);

        if ($startDate > $endDate) {
            [$startDate, $endDate] = [$endDate, $startDate];
        }

        $diffDays = $startDate->diff($endDate)->days + 1;
        if ($diffDays > self::MAX_RANGE_DAYS) {
            $startDate = (clone $endDate)->modify('-' . (self::MAX_RANGE_DAYS - 1) . ' days');
        }

        return [$startDate->format('Y-m-d H:i:s'), $endDate->format('Y-m-d H:i:s')];
    }
    
    /**
     * Get total users count (regular users/subscribers only, excludes attendants and admins)
     */
    public function getTotalUsers()
    {
        return $this->db->table('users')
            ->where('status', 'active')
            ->whereNotIn('user_type_id', [UserModel::ROLE_ATTENDANT, UserModel::ROLE_ADMIN]) // Exclude attendants and admins, include users (1)
            ->countAllResults();
    }

    public function getTotalSubscribers()
    {
        return $this->db->table('users')
            ->whereNotIn('user_type_id', [UserModel::ROLE_ATTENDANT, UserModel::ROLE_ADMIN])
            ->countAllResults();
    }

    public function getActiveSubscribers()
    {
        return $this->db->table('users')
            ->whereNotIn('user_type_id', [UserModel::ROLE_ATTENDANT, UserModel::ROLE_ADMIN])
            ->where('status', 'active')
            ->countAllResults();
    }

    public function getInactiveSubscribers()
    {
        return $this->db->table('users')
            ->whereNotIn('user_type_id', [UserModel::ROLE_ATTENDANT, UserModel::ROLE_ADMIN])
            ->where('status', 'inactive')
            ->countAllResults();
    }
    
    /**
     * Get active bookings count
     */
    public function getActiveBookings()
    {
        // Placeholder - adjust table/column names to match your database
        return $this->db->table('reservations')
            ->where('start_time IS NOT NULL')
            ->where('end_time IS NULL')
            ->countAllResults();
    }
    
    /**
     * Get total parking spots
     */
    public function getTotalParkingSpots()
    {
        try {
            $spotCount = (int)($this->db->table('parking_spot spot')
                ->join('parking_section sec', 'sec.parking_section_id = spot.parking_section_id', 'inner')
                ->join('parking_area area', 'area.parking_area_id = sec.parking_area_id', 'inner')
                ->where('area.status', 'active')
                ->where('sec.status', 'active')
                ->countAllResults());

            $capRow = $this->db->table('parking_section sec')
                ->select('COALESCE(SUM(CASE WHEN sec.section_mode = "capacity_only" THEN sec.capacity ELSE 0 END), 0) as cap_only_capacity', false)
                ->join('parking_area area', 'area.parking_area_id = sec.parking_area_id', 'inner')
                ->where('area.status', 'active')
                ->where('sec.status', 'active')
                ->get()
                ->getRow();

            $capOnlyCapacity = (int)($capRow->cap_only_capacity ?? 0);

            return $spotCount + $capOnlyCapacity;
        } catch (\Exception $e) {
            log_message('error', 'DashboardModel::getTotalParkingSpots - ' . $e->getMessage());
            return 0;
        }
    }
    
    /**
     * Get revenue based on filter
     */
    public function getRevenue($filter = 'today', $customStart = null, $customEnd = null)
    {
        $dateRange = $this->getDateRange($filter, $customStart, $customEnd);
        
        // Placeholder - adjust table/column names to match your database
        $result = $this->db->table('payments')
            ->selectSum('amount')
            ->where('status', 'paid')
            ->where('payment_date >=', $dateRange['start'])
            ->where('payment_date <=', $dateRange['end'])
            ->get()
            ->getRow();
        
        return $result->amount ?? 0;
    }
    
    /**
     * Get occupancy rate
     */
    public function getOccupancyRate()
    {
        $totalSpots = $this->getTotalParkingSpots();
        
        if ($totalSpots == 0) {
            return 0;
        }
        
        $occupiedSpots = $this->getActiveBookings();
        $occupancyRate = ($occupiedSpots / $totalSpots) * 100;
        
        return round($occupancyRate, 1);
    }
    
    /**
     * Get online attendants count (includes attendants (2) and admins (3))
     */
    public function getOnlineAttendants()
    {
        $recentCutoff = date('Y-m-d H:i:s', strtotime('-10 minutes'));

        return $this->db->table('users')
            ->where('is_online', 1)
            ->where('last_activity_at >=', $recentCutoff)
            ->whereIn('user_type_id', [UserModel::ROLE_ATTENDANT, UserModel::ROLE_ADMIN]) // Only count attendants and admins
            ->countAllResults();
    }
    
    /**
     * Get revenue chart data
     * Returns array with 'labels' and 'data' keys for Chart.js
     */
    public function getRevenueChartData($filter = 'today', $customStart = null, $customEnd = null)
    {
        $dateRange = $this->getDateRange($filter, $customStart, $customEnd);
        
        $labels = [];
        $data = [];
        
        if ($filter === 'today') {
            // Hourly revenue for today
            $todayDate = date('Y-m-d', strtotime($dateRange['start']));
            
            try {
                $results = $this->db->table('payments')
                    ->select('HOUR(payment_date) as hour,
                             COALESCE(SUM(amount), 0) as revenue')
                    ->where('status', 'paid')
                    ->where('DATE(payment_date)', $todayDate)
                    ->groupby('HOUR(payment_date)')
                    ->orderby('hour')
                    ->get()
                    ->getResultArray();
                
                // Fill in all 24 hours (even if no data)
                $hourlyData = array_fill(0, 24, 0);
                foreach ($results as $row) {
                    $hourlyData[$row['hour']] = (float)$row['revenue'];
                }
                
                for ($i = 0; $i < 24; $i++) {
                    $labels[] = sprintf('%02d:00', $i);
                    $data[] = $hourlyData[$i];
                }
            } catch (\Exception $e) {
                // If table doesn't exist, return sample data
                for ($i = 0; $i < 24; $i++) {
                    $labels[] = sprintf('%02d:00', $i);
                    $data[] = 0;
                }
            }
            
        } else {
            $startDate = new \DateTime($dateRange['start']);
            $endDate = new \DateTime($dateRange['end']);
            $customBuckets = $this->shouldUseMonthlyBuckets($filter)
                ? $this->buildMonthlyBuckets($startDate, $endDate)
                : null;
            $rows = $this->db->table('payments')
                ->select('DATE(payment_date) as grouping_date')
                ->selectSum('amount', 'revenue')
                ->where('status', 'paid')
                ->where('payment_date >=', $dateRange['start'])
                ->where('payment_date <=', $dateRange['end'])
                ->groupBy('grouping_date')
                ->orderBy('grouping_date', 'ASC')
                ->get()
                ->getResultArray();
            $map = $this->buildDailyValueMap($rows, 'grouping_date', 'revenue');
            $series = $this->buildBucketedSumSeries($map, $startDate, $endDate, $customBuckets);
            $labels = $series['labels'];
            $data = $series['values'];
        }
        
        return [
            'labels' => $labels,
            'data' => $data
        ];
    }
    
    /**
     * Get occupancy chart data (for donut chart)
     */
    public function getOccupancyChartData()
    {
        $totalSpots = $this->getTotalParkingSpots();
        $occupiedSpots = $this->getActiveBookings();
        $availableSpots = $totalSpots - $occupiedSpots;
        
        return [
            'labels' => ['Occupied', 'Available'],
            'data' => [$occupiedSpots, $availableSpots]
        ];
    }
    
    /**
     * Get bookings chart data
     */
    public function getBookingsChartData($filter = 'today', $customStart = null, $customEnd = null)
    {
        $dateRange = $this->getDateRange($filter, $customStart, $customEnd);
        $labels = [];
        $data = [];
        
        if ($filter === 'today') {
            // Group by hour for today
            $todayDate = date('Y-m-d', strtotime($dateRange['start']));
            $query = "
                SELECT 
                    HOUR(created_at) as hour,
                    COUNT(*) as booking_count
                FROM reservations
                WHERE DATE(created_at) = ?
                GROUP BY HOUR(created_at)
                ORDER BY hour
            ";
            
            try {
                $results = $this->db->query($query, [$todayDate])->getResultArray();
                
                $hourlyData = array_fill(0, 24, 0);
                foreach ($results as $row) {
                    $hourlyData[$row['hour']] = (int)$row['booking_count'];
                }
                
                for ($i = 0; $i < 24; $i++) {
                    $labels[] = sprintf('%02d:00', $i);
                    $data[] = $hourlyData[$i];
                }
            } catch (\Exception $e) {
                for ($i = 0; $i < 24; $i++) {
                    $labels[] = sprintf('%02d:00', $i);
                    $data[] = 0;
                }
            }
            
        } else {
            $startDate = new \DateTime($dateRange['start']);
            $endDate = new \DateTime($dateRange['end']);
            $customBuckets = $this->shouldUseMonthlyBuckets($filter)
                ? $this->buildMonthlyBuckets($startDate, $endDate)
                : null;
            $rows = $this->db->table('reservations')
                ->select('DATE(created_at) as grouping_date')
                ->selectCount('reservation_id', 'booking_count')
                ->where('created_at >=', $dateRange['start'])
                ->where('created_at <=', $dateRange['end'])
                ->groupBy('grouping_date')
                ->orderBy('grouping_date', 'ASC')
                ->get()
                ->getResultArray();
            $map = $this->buildDailyValueMap($rows, 'grouping_date', 'booking_count');
            $series = $this->buildBucketedSumSeries($map, $startDate, $endDate, $customBuckets);
            $labels = $series['labels'];
            $data = $series['values'];
        }
        
        return [
            'labels' => $labels,
            'data' => $data
        ];
    }
    
    /**
     * Get user growth chart data
     */
    public function getUserGrowthChartData($filter = 'monthly', $customStart = null, $customEnd = null)
    {
        $dateRange = $this->getDateRange($filter, $customStart, $customEnd);
        $rows = $this->db->table('users')
            ->select('DATE(created_at) as grouping_date')
            ->selectCount('user_id', 'count')
            ->where('created_at >=', $dateRange['start'])
            ->where('created_at <=', $dateRange['end'])
            ->whereNotIn('user_type_id', [UserModel::ROLE_ATTENDANT, UserModel::ROLE_ADMIN])
            ->groupBy('grouping_date')
            ->orderBy('grouping_date', 'ASC')
            ->get()
            ->getResultArray();
        $map = $this->buildDailyValueMap($rows, 'grouping_date', 'count');
        $startDate = new \DateTime($dateRange['start']);
        $endDate = new \DateTime($dateRange['end']);
        $customBuckets = $this->shouldUseMonthlyBuckets($filter)
            ? $this->buildMonthlyBuckets($startDate, $endDate)
            : null;
        $series = $this->buildBucketedSumSeries($map, $startDate, $endDate, $customBuckets);
        $cumulative = [];
        $running = 0;
        foreach ($series['values'] as $value) {
            $running += $value;
            $cumulative[] = $running;
        }

        return [
            'labels' => $series['labels'],
            'data' => $cumulative
        ];
    }
    
    /**
     * Get Revenue vs Bookings comparison data
     */
    public function getRevenueVsBookingsData($filter = 'today', $customStart = null, $customEnd = null)
    {
        try {
            $dateRange = $this->getDateRange($filter, $customStart, $customEnd);
            
            $labels = [];
            $revenueData = [];
            $bookingsData = [];
            
            if ($filter === 'today') {
                // Hourly data
                $todayDate = date('Y-m-d', strtotime($dateRange['start']));
                
                $hourlyRevenue = array_fill(0, 24, 0);
                $hourlyBookings = array_fill(0, 24, 0);
                
                try {
                    // Revenue query
                    $revenueResults = $this->db->query("
                        SELECT HOUR(payment_date) as hour, COALESCE(SUM(amount), 0) as revenue
                        FROM payments WHERE status = 'paid' AND DATE(payment_date) = ?
                        GROUP BY HOUR(payment_date) ORDER BY hour
                    ", [$todayDate])->getResultArray();
                    
                    foreach ($revenueResults as $row) {
                        $hourlyRevenue[$row['hour']] = (float)$row['revenue'];
                    }
                    
                    // Bookings query
                    $bookingsResults = $this->db->query("
                        SELECT HOUR(created_at) as hour, COUNT(*) as booking_count
                        FROM reservations WHERE DATE(created_at) = ?
                        GROUP BY HOUR(created_at) ORDER BY hour
                    ", [$todayDate])->getResultArray();
                    
                    foreach ($bookingsResults as $row) {
                        $hourlyBookings[$row['hour']] = (int)$row['booking_count'];
                    }
                } catch (\Exception $e) {
                    // Tables don't exist, use empty data
                }
                
                for ($i = 0; $i < 24; $i++) {
                    $labels[] = sprintf('%02d:00', $i);
                    $revenueData[] = $hourlyRevenue[$i];
                    $bookingsData[] = $hourlyBookings[$i];
                }
                
            } else {
                $revRows = $this->db->table('payments')
                    ->select('DATE(payment_date) as grouping_date')
                    ->selectSum('amount', 'revenue')
                    ->where('status', 'paid')
                    ->where('payment_date >=', $dateRange['start'])
                    ->where('payment_date <=', $dateRange['end'])
                    ->groupBy('grouping_date')
                    ->orderBy('grouping_date', 'ASC')
                    ->get()
                    ->getResultArray();
                $revMap = $this->buildDailyValueMap($revRows, 'grouping_date', 'revenue');

                $bookRows = $this->db->table('reservations')
                    ->select('DATE(created_at) as grouping_date')
                    ->selectCount('reservation_id', 'booking_count')
                    ->where('created_at >=', $dateRange['start'])
                    ->where('created_at <=', $dateRange['end'])
                    ->groupBy('grouping_date')
                    ->orderBy('grouping_date', 'ASC')
                    ->get()
                    ->getResultArray();
                $bookMap = $this->buildDailyValueMap($bookRows, 'grouping_date', 'booking_count');

                $startDate = new \DateTime($dateRange['start']);
                $endDate = new \DateTime($dateRange['end']);
                $buckets = $this->shouldUseMonthlyBuckets($filter)
                    ? $this->buildMonthlyBuckets($startDate, $endDate)
                    : $this->buildBuckets($startDate, $endDate);
                foreach ($buckets as $bucket) {
                    [$bucketStart, $bucketEnd] = $bucket;
                    $labels[] = $this->formatBucketLabel($bucketStart, $bucketEnd);
                    $revenueData[] = $this->sumDailyValues($revMap, $bucketStart, $bucketEnd);
                    $bookingsData[] = $this->sumDailyValues($bookMap, $bucketStart, $bucketEnd);
                }
            }
            
            return [
                'labels' => $labels,
                'revenue' => $revenueData,
                'bookings' => $bookingsData
            ];
        } catch (\Exception $e) {
            return [
                'labels' => ['No Data'],
                'revenue' => [0],
                'bookings' => [0]
            ];
        }
    }
    
    /**
     * Get Hour Balance Trends
     */
    public function getHourBalanceTrends()
    {
        try {
            $result = $this->db->query("
                SELECT 
                    SUM(CASE WHEN transaction_type = 'hour_addition' THEN hours ELSE 0 END) as total_purchased,
                    SUM(CASE WHEN transaction_type = 'hour_deduction' THEN hours ELSE 0 END) as total_used
                FROM transactions
            ")->getRow();
            
            $purchased = (float)($result->total_purchased ?? 0);
            $used = (float)($result->total_used ?? 0);
            $remaining = $purchased - $used;
            
            return [
                'labels' => ['Purchased', 'Used', 'Remaining'],
                'data' => [$purchased, $used, $remaining]
            ];
        } catch (\Exception $e) {
            return [
                'labels' => ['Purchased', 'Used', 'Remaining'],
                'data' => [0, 0, 0]
            ];
        }
    }
    
    /**
     * Get recent activity from the system
     */
    public function getRecentActivity($limit = 5)
    {
        try {
            $query = "
                SELECT 
                    CONCAT(u.first_name, ' ', u.last_name) as user_name,
                    u.email,
                    r.start_time,
                    r.end_time,
                    r.created_at as booking_date,
                    pa.parking_area_name as parking_area,
                    sec.section_name as parking_section,
                    spot.spot_number as parking_spot,
                    r.booking_status as booking_status
                FROM reservations r
                LEFT JOIN users u ON r.user_id = u.user_id
                LEFT JOIN parking_spot spot ON r.parking_spots_id = spot.parking_spot_id
                LEFT JOIN parking_section sec ON spot.parking_section_id = sec.parking_section_id
                LEFT JOIN parking_area pa ON sec.parking_area_id = pa.parking_area_id
                ORDER BY r.created_at DESC
                LIMIT ?
            ";
            
            return $this->db->query($query, [$limit])->getResultArray();
        } catch (\Exception $e) {
            log_message('error', 'DashboardModel::getRecentActivity - ' . $e->getMessage());
            return [];
        }
    }
    
    /**
     * Get active vehicles breakdown by type
     */
    public function getActiveVehiclesBreakdown()
    {
        try {
            $query = "
                SELECT 
                    v.vehicle_type,
                    COUNT(DISTINCT r.user_id) as unique_users,
                    COUNT(r.reservation_id) as active_bookings,
                    AVG(TIMESTAMPDIFF(HOUR, r.start_time, COALESCE(r.end_time, NOW()))) as avg_duration_hours
                FROM reservations r
                LEFT JOIN vehicles v ON r.vehicle_id = v.vehicle_id
                WHERE r.start_time IS NOT NULL 
                AND (r.end_time IS NULL OR r.end_time > NOW() - INTERVAL 24 HOUR)
                GROUP BY v.vehicle_type
                ORDER BY active_bookings DESC
            ";
            
            return $this->db->query($query)->getResultArray();
        } catch (\Exception $e) {
            log_message('error', 'DashboardModel::getActiveVehiclesBreakdown - ' . $e->getMessage());
            return [];
        }
    }
    
    /**
     * Get top feedbacks/ratings
     */
    public function getTopFeedbacks($limit = 10)
    {
        try {
            $query = "
                SELECT 
                    CONCAT(u.first_name, ' ', u.last_name) as user_name,
                    u.email,
                    f.rating,
                    f.content,
                    f.created_at as feedback_date,
                    f.subscription_id
                FROM feedback f
                LEFT JOIN users u ON f.user_id = u.user_id
                ORDER BY f.created_at DESC
                LIMIT ?
            ";
            
            return $this->db->query($query, [$limit])->getResultArray();
        } catch (\Exception $e) {
            log_message('error', 'DashboardModel::getTopFeedbacks - ' . $e->getMessage());
            return [];
        }
    }
    
    /**
     * Get average rating across all feedbacks
     */
    public function getAverageRating()
    {
        try {
            $query = "
                SELECT AVG(rating) as avg_rating, COUNT(*) as total_ratings
                FROM feedback
                WHERE rating IS NOT NULL
                AND status = 'active'
                AND created_at > DATE_SUB(NOW(), INTERVAL 90 DAY)
            ";
            
            $result = $this->db->query($query)->getRow();
            
            if ($result && $result->avg_rating > 0) {
                return round($result->avg_rating, 1);
            }
            
            return 0;
            
        } catch (\Exception $e) {
            log_message('error', 'DashboardModel::getAverageRating - ' . $e->getMessage());
            return 0;
        }
    }

    public function getAverageRatingOverTime($filter = 'today', $customStart = null, $customEnd = null)
    {
        try {
            $dateRange = $this->getDateRange($filter, $customStart, $customEnd);

            $labels = [];
            $data = [];

            if ($filter === 'today') {
                $dateOnly = date('Y-m-d', strtotime($dateRange['start']));
                $query = "
                    SELECT 
                        HOUR(created_at) as hr,
                        ROUND(AVG(rating), 2) as avg_rating
                    FROM feedback
                    WHERE status = 'active'
                    AND DATE(created_at) = ?
                    GROUP BY HOUR(created_at)
                    ORDER BY hr ASC
                ";

                $rows = $this->db->query($query, [$dateOnly])->getResultArray();
                $map = [];
                foreach ($rows as $r) {
                    $map[(int)$r['hr']] = (float)$r['avg_rating'];
                }
                for ($h = 0; $h < 24; $h++) {
                    $labels[] = $h . ':00';
                    $data[] = isset($map[$h]) ? $map[$h] : 0;
                }
            } else {
                $query = "
                    SELECT 
                        DATE(created_at) as d,
                        ROUND(AVG(rating), 2) as avg_rating
                    FROM feedback
                    WHERE status = 'active'
                    AND created_at >= ?
                    AND created_at <= ?
                    GROUP BY DATE(created_at)
                    ORDER BY d ASC
                ";

                $rows = $this->db->query($query, [$dateRange['start'], $dateRange['end']])->getResultArray();
                $map = [];
                foreach ($rows as $r) {
                    $map[$r['d']] = (float)$r['avg_rating'];
                }
                $startDate = new \DateTime(date('Y-m-d', strtotime($dateRange['start'])));
                $endDate = new \DateTime(date('Y-m-d', strtotime($dateRange['end'])));
                $rows = $this->db->query($query, [$dateRange['start'], $dateRange['end']])->getResultArray();
                $sumMap = [];
                $countMap = [];
                foreach ($rows as $row) {
                    $key = date('Y-m-d', strtotime($row['d']));
                    $sumMap[$key] = (float)($row['avg_rating'] ?? 0) * (int)($row['rating_count'] ?? 0);
                    $countMap[$key] = (int)($row['rating_count'] ?? 0);
                }
                $series = $this->buildBucketedAverageSeries($sumMap, $countMap, $startDate, $endDate);
                $labels = $series['labels'];
                $data = $series['values'];
            }

            return ['labels' => $labels, 'data' => $data];
        } catch (\Exception $e) {
            log_message('error', 'DashboardModel::getAverageRatingOverTime - ' . $e->getMessage());
            return ['labels' => [], 'data' => []];
        }
    }

    private function buildDailyValueMap(array $rows, string $dateKey, string $valueKey): array
    {
        $map = [];
        foreach ($rows as $row) {
            if (!isset($row[$dateKey])) {
                continue;
            }
            $date = date('Y-m-d', strtotime($row[$dateKey]));
            $map[$date] = isset($row[$valueKey]) ? (float)$row[$valueKey] : 0.0;
        }
        return $map;
    }

    private function buildBuckets(\DateTime $start, \DateTime $end): array
    {
        $bucketSize = $this->getBucketSizeDays($start, $end);
        $buckets = [];
        $cursor = clone $start;
        while ($cursor <= $end) {
            $bucketStart = clone $cursor;
            $bucketEnd = (clone $cursor)->modify('+' . ($bucketSize - 1) . ' days');
            if ($bucketEnd > $end) {
                $bucketEnd = clone $end;
            }
            $buckets[] = [$bucketStart, $bucketEnd];
            $cursor = (clone $bucketEnd)->modify('+1 day');
        }
        return $buckets;
    }

    private function getBucketSizeDays(\DateTime $start, \DateTime $end): int
    {
        $days = max(1, $start->diff($end)->days + 1);
        return max(1, (int)ceil($days / self::MAX_CHART_POINTS));
    }

    private function sumDailyValues(array $map, \DateTime $start, \DateTime $end): float
    {
        $sum = 0.0;
        $cursor = clone $start;
        while ($cursor <= $end) {
            $key = $cursor->format('Y-m-d');
            if (isset($map[$key])) {
                $sum += $map[$key];
            }
            $cursor->modify('+1 day');
        }
        return $sum;
    }

    private function buildBucketedSumSeries(array $dailyMap, \DateTime $start, \DateTime $end, ?array $buckets = null): array
    {
        $buckets = $buckets ?? $this->buildBuckets($start, $end);
        $labels = [];
        $values = [];
        foreach ($buckets as $bucket) {
            [$bucketStart, $bucketEnd] = $bucket;
            $labels[] = $this->formatBucketLabel($bucketStart, $bucketEnd);
            $values[] = $this->sumDailyValues($dailyMap, $bucketStart, $bucketEnd);
        }
        return ['labels' => $labels, 'values' => $values, 'buckets' => $buckets];
    }

    private function buildBucketedAverageSeries(array $dailySumMap, array $dailyCountMap, \DateTime $start, \DateTime $end, ?array $buckets = null): array
    {
        $buckets = $buckets ?? $this->buildBuckets($start, $end);
        $labels = [];
        $values = [];
        foreach ($buckets as $bucket) {
            [$bucketStart, $bucketEnd] = $bucket;
            $sum = $this->sumDailyValues($dailySumMap, $bucketStart, $bucketEnd);
            $count = $this->sumDailyValues($dailyCountMap, $bucketStart, $bucketEnd);
            $labels[] = $this->formatBucketLabel($bucketStart, $bucketEnd);
            $values[] = $count > 0 ? round($sum / $count, 2) : 0;
        }
        return ['labels' => $labels, 'values' => $values, 'buckets' => $buckets];
    }

    private function formatBucketLabel(\DateTime $start, \DateTime $end): string
    {
        if ($start->format('Y-m-d') === $end->format('Y-m-d')) {
            return $start->format('M j');
        }

        if ($start->format('Y-m') === $end->format('Y-m')) {
            return $start->format('M j') . ' – ' . $end->format('j');
        }

        $startLabel = $start->format('M j');
        $endLabel = $end->format('M j');
        return $startLabel . ' – ' . $endLabel;
    }

    private function buildMonthlyBuckets(\DateTime $start, \DateTime $end): array
    {
        $buckets = [];
        $cursor = (clone $start)->modify('first day of this month midnight');
        while ($cursor <= $end) {
            $bucketStart = clone $cursor;
            $bucketEnd = (clone $cursor)->modify('last day of this month 23:59:59');
            if ($bucketStart < $start) {
                $bucketStart = clone $start;
            }
            if ($bucketEnd > $end) {
                $bucketEnd = clone $end;
            }
            $buckets[] = [$bucketStart, $bucketEnd];
            $cursor = (clone $cursor)->modify('first day of next month');
        }
        return $buckets;
    }

    private function shouldUseMonthlyBuckets(string $filter): bool
    {
        return in_array($filter, ['this_year', 'last_year'], true);
    }
}

