<?php

namespace App\Models;

use CodeIgniter\Model;

class ReportsModel extends Model
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
     * Get revenue by parking area
     */
    public function getRevenueByArea($startDate = null, $endDate = null)
    {
        try {
            $builder = $this->db->table('parking_area pa')
                ->select('pa.parking_area_name,
                         COUNT(DISTINCT r.reservation_id) as total_bookings,
                         0 as total_revenue')
                ->join('parking_section ps', 'pa.parking_area_id = ps.parking_area_id', 'left')
                ->join('parking_spot spot', 'ps.parking_section_id = spot.parking_section_id', 'left')
                ->join('reservations r', 'spot.parking_spot_id = r.parking_spots_id', 'left')
                ->where('pa.status', 'active')
                ->groupby('pa.parking_area_id, pa.parking_area_name')
                ->orderby('total_bookings', 'DESC');
            
            // Apply date filters if provided
            if ($startDate) {
                $builder->where('DATE(r.created_at) >=', $startDate);
            }
            if ($endDate) {
                $builder->where('DATE(r.created_at) <=', $endDate);
            }
            
            return $builder->get()->getResultArray();
        } catch (\Exception $e) {
            log_message('error', 'ReportsModel::getRevenueByArea - ' . $e->getMessage());
            return [];
        }
    }

    public function getTotalRevenue($startDate = null, $endDate = null)
    {
        try {
            $builder = $this->db->table('payments')
                ->selectSum('amount')
                ->where('status', 'paid');

            if ($startDate) {
                $builder->where('DATE(payment_date) >=', $startDate);
            }
            if ($endDate) {
                $builder->where('DATE(payment_date) <=', $endDate);
            }

            $row = $builder->get()->getRow();
            return (float)($row->amount ?? 0);
        } catch (\Exception $e) {
            log_message('error', 'ReportsModel::getTotalRevenue - ' . $e->getMessage());
            return 0;
        }
    }

    public function getTotalBookings($startDate = null, $endDate = null)
    {
        try {
            $builder = $this->db->table('reservations');

            if ($startDate) {
                $builder->where('DATE(created_at) >=', $startDate);
            }
            if ($endDate) {
                $builder->where('DATE(created_at) <=', $endDate);
            }

            return (int)$builder->countAllResults();
        } catch (\Exception $e) {
            log_message('error', 'ReportsModel::getTotalBookings - ' . $e->getMessage());
            return 0;
        }
    }

    /**
     * Get bookings by area (alias for getRevenueByArea for clarity)
     */
    public function getBookingsByArea($startDate = null, $endDate = null)
    {
        return $this->getRevenueByArea($startDate, $endDate);
    }
    
    /**
     * Get revenue by subscription plan
     */
    public function getRevenueByPlan($startDate = null, $endDate = null)
    {
        try {
            $builder = $this->db->table('plans pl')
                ->select('pl.plan_name,
                         pl.cost,
                         COUNT(DISTINCT s.subscription_id) as subscription_count,
                         COALESCE(SUM(p.amount), 0) as total_revenue')
                ->join('subscriptions s', 'pl.plan_id = s.plan_id', 'left')
                ->join('payments p', 's.subscription_id = p.subscription_id AND p.status = "paid"', 'left')
                ->groupby('pl.plan_id, pl.plan_name, pl.cost')
                ->orderby('total_revenue', 'DESC');
            
            // Apply date filters if provided
            if ($startDate) {
                $builder->where('DATE(p.payment_date) >=', $startDate);
            }
            if ($endDate) {
                $builder->where('DATE(p.payment_date) <=', $endDate);
            }
            
            return $builder->get()->getResultArray();
        } catch (\Exception $e) {
            log_message('error', 'ReportsModel::getRevenueByPlan - ' . $e->getMessage());
            return [];
        }
    }
    
    /**
     * Get payment method distribution
     */
    public function getPaymentMethodDistribution($startDate = null, $endDate = null)
    {
        try {
            $builder = $this->db->table('payments')
                ->select("'Online Payment' as payment_method,
                         COUNT(*) as count,
                         COALESCE(SUM(amount), 0) as total_amount")
                ->where('status', 'paid')
                ->groupby('payment_method')
                ->orderby('total_amount', 'DESC');
            
            // Apply date filters if provided
            if ($startDate) {
                $builder->where('DATE(payment_date) >=', $startDate);
            }
            if ($endDate) {
                $builder->where('DATE(payment_date) <=', $endDate);
            }
            
            return $builder->get()->getResultArray();
        } catch (\Exception $e) {
            log_message('error', 'ReportsModel::getPaymentMethodDistribution - ' . $e->getMessage());
            return [];
        }
    }
    
    /**
     * Get peak hours
     */
    public function getPeakHours($startDate = null, $endDate = null)
    {
        try {
            [$rangeStart, $rangeEnd, , , $totalDays] = $this->resolveRange($startDate, $endDate);

            $rows = $this->db->table('reservations')
                ->select('HOUR(created_at) as hour, COUNT(*) as booking_count')
                ->where('created_at >=', $rangeStart)
                ->where('created_at <=', $rangeEnd)
                ->groupBy('HOUR(created_at)')
                ->orderBy('hour')
                ->get()
                ->getResultArray();

            $hourly = array_fill(0, 24, 0.0);
            foreach ($rows as $row) {
                $hour = (int)($row['hour'] ?? 0);
                $count = (float)($row['booking_count'] ?? 0);
                $hourly[$hour] = $totalDays > 0 ? round($count / $totalDays, 2) : 0;
            }

            $labels = [];
            $data = [];
            for ($i = 0; $i < 24; $i++) {
                $labels[] = sprintf('%02d:00', $i);
                $data[] = $hourly[$i];
            }

            return ['labels' => $labels, 'data' => $data];
        } catch (\Exception $e) {
            log_message('error', 'ReportsModel::getPeakHours - ' . $e->getMessage());
            return ['labels' => [], 'data' => []];
        }
    }
    
    /**
     * Get average parking duration
     */
    public function getAverageParkingDuration($startDate = null, $endDate = null)
    {
        try {
            $builder = $this->db->table('reservations')
                ->select('AVG(TIMESTAMPDIFF(HOUR, start_time, end_time)) as avg_duration')
                ->where('start_time IS NOT NULL')
                ->where('end_time IS NOT NULL');
            
            // Apply date filters if provided
            if ($startDate) {
                $builder->where('DATE(created_at) >=', $startDate);
            }
            if ($endDate) {
                $builder->where('DATE(created_at) <=', $endDate);
            }
            
            $result = $builder->get()->getRow();
            return round($result->avg_duration ?? 0, 1);
        } catch (\Exception $e) {
            log_message('error', 'ReportsModel::getAverageParkingDuration - ' . $e->getMessage());
            return 0;
        }
    }
    
    /**
     * Get popular areas
     */
    public function getPopularAreas($startDate = null, $endDate = null)
    {
        try {
            $builder = $this->db->table('parking_area pa')
                ->select('pa.parking_area_name,
                         COUNT(DISTINCT r.reservation_id) as booking_count')
                ->join('parking_section ps', 'pa.parking_area_id = ps.parking_area_id', 'left')
                ->join('parking_spot spot', 'ps.parking_section_id = spot.parking_section_id', 'left')
                ->join('reservations r', 'spot.parking_spot_id = r.parking_spots_id', 'left')
                ->where('pa.status', 'active')
                ->groupby('pa.parking_area_id, pa.parking_area_name')
                ->orderby('booking_count', 'DESC')
                ->limit(10);
            
            // Apply date filters if provided
            if ($startDate) {
                $builder->where('DATE(r.created_at) >=', $startDate);
            }
            if ($endDate) {
                $builder->where('DATE(r.created_at) <=', $endDate);
            }
            
            return $builder->get()->getResultArray();
        } catch (\Exception $e) {
            log_message('error', 'ReportsModel::getPopularAreas - ' . $e->getMessage());
            return [];
        }
    }
    
    /**
     * Get vehicle type distribution
     */
    public function getVehicleTypeDistribution($startDate = null, $endDate = null)
    {
        try {
            $builder = $this->db->table('reservations r')
                ->select('v.vehicle_type,
                         COUNT(*) as count')
                ->join('vehicles v', 'r.vehicle_id = v.vehicle_id')
                ->groupby('v.vehicle_type')
                ->orderby('count', 'DESC');
            
            // Apply date filters if provided
            if ($startDate) {
                $builder->where('DATE(r.created_at) >=', $startDate);
            }
            if ($endDate) {
                $builder->where('DATE(r.created_at) <=', $endDate);
            }
            
            return $builder->get()->getResultArray();
        } catch (\Exception $e) {
            log_message('error', 'ReportsModel::getVehicleTypeDistribution - ' . $e->getMessage());
            return [];
        }
    }
    
    /**
     * Get monthly user growth
     */
    public function getMonthlyUserGrowth($startDate = null, $endDate = null)
    {
        try {
            $query = "
                SELECT 
                    DATE_FORMAT(created_at, '%Y-%m') as month,
                    COUNT(*) as new_users
                FROM users
                WHERE DATE(created_at) >= ?
                AND DATE(created_at) <= ?
                AND user_type_id NOT IN (" . \App\Models\UserModel::ROLE_ATTENDANT . ", " . \App\Models\UserModel::ROLE_ADMIN . ")
                GROUP BY DATE_FORMAT(created_at, '%Y-%m')
                ORDER BY month
            ";
            
            return $this->db->query($query, [$startDate, $endDate])->getResultArray();
        } catch (\Exception $e) {
            log_message('error', 'ReportsModel::getMonthlyUserGrowth - ' . $e->getMessage());
            return [];
        }
    }
    
    /**
     * Get user activity metrics
     */
    public function getUserActivityMetrics()
    {
        try {
            $totalUsers = $this->db->table('users')
                ->where('status', 'active')
                ->whereNotIn('user_type_id', [UserModel::ROLE_ATTENDANT, UserModel::ROLE_ADMIN]) // Exclude attendants and admins, include users (1)
                ->countAllResults();
            
            $activeThisMonth = $this->db->table('users')
                ->join('reservations', 'reservations.user_id = users.user_id')
                ->where('users.status', 'active')
                ->whereNotIn('users.user_type_id', [UserModel::ROLE_ATTENDANT, UserModel::ROLE_ADMIN]) // Exclude attendants and admins, include users (1)
                ->where('MONTH(reservations.created_at)', date('m'))
                ->where('YEAR(reservations.created_at)', date('Y'))
                ->countAllResults(false);
            
            $activityRate = $totalUsers > 0 ? round(($activeThisMonth / $totalUsers) * 100, 1) : 0;
            
            return [
                'total_users' => $totalUsers,
                'active_parkers_this_month' => $activeThisMonth,
                'activity_rate' => $activityRate
            ];
        } catch (\Exception $e) {
            log_message('error', 'ReportsModel::getUserActivityMetrics - ' . $e->getMessage());
            return [
                'total_users' => 0,
                'active_parkers_this_month' => 0,
                'activity_rate' => 0
            ];
        }
    }

    public function getUserActivityMetricsByDateRange($startDate = null, $endDate = null)
    {
        try {
            $totalUsers = $this->db->table('users')
                ->where('status', 'active')
                ->whereNotIn('user_type_id', [UserModel::ROLE_ATTENDANT, UserModel::ROLE_ADMIN])
                ->countAllResults();

            $builder = $this->db->table('users')
                ->select('users.user_id')
                ->join('reservations', 'reservations.user_id = users.user_id', 'inner')
                ->where('users.status', 'active')
                ->whereNotIn('users.user_type_id', [UserModel::ROLE_ATTENDANT, UserModel::ROLE_ADMIN]);

            if ($startDate) {
                $builder->where('DATE(reservations.created_at) >=', $startDate);
            }
            if ($endDate) {
                $builder->where('DATE(reservations.created_at) <=', $endDate);
            }

            $activeInRange = (int)$builder->distinct()->countAllResults();

            $activityRate = $totalUsers > 0 ? round(($activeInRange / $totalUsers) * 100, 1) : 0;

            return [
                'total_users' => $totalUsers,
                'active_parkers_in_range' => $activeInRange,
                'activity_rate' => $activityRate
            ];
        } catch (\Exception $e) {
            log_message('error', 'ReportsModel::getUserActivityMetricsByDateRange - ' . $e->getMessage());
            return [
                'total_users' => 0,
                'active_parkers_in_range' => 0,
                'activity_rate' => 0
            ];
        }
    }
    
    /**
     * Get subscription metrics
     */
    public function getSubscriptionMetrics()
    {
        try {
            // Count all active subscriptions
            $totalSubscriptions = $this->db->table('subscriptions')
                ->where('status', 'active')
                ->countAllResults();
            
            // Count active subscriptions purchased this month
            // Use proper date range comparison instead of MONTH()/YEAR() functions
            $startOfMonth = date('Y-m-01 00:00:00');
            $endOfMonth = date('Y-m-t 23:59:59');
            
            $activeThisMonth = $this->db->table('subscriptions')
                ->where('status', 'active')
                ->where('purchase_date >=', $startOfMonth)
                ->where('purchase_date <=', $endOfMonth)
                ->countAllResults();
            
            return [
                'total_subscriptions' => $totalSubscriptions,
                'new_this_month' => $activeThisMonth
            ];
        } catch (\Exception $e) {
            log_message('error', 'ReportsModel::getSubscriptionMetrics - ' . $e->getMessage());
            return [
                'total_subscriptions' => 0,
                'new_this_month' => 0
            ];
        }
    }
    
    /**
     * Get revenue trend
     */
    public function getRevenueTrend($startDate = null, $endDate = null)
    {
        try {
            $query = "
                SELECT 
                    DATE(payment_date) as date,
                    COALESCE(SUM(amount), 0) as daily_revenue
                FROM payments
                WHERE status = 'paid'
                AND DATE(payment_date) >= ?
                AND DATE(payment_date) <= ?
                GROUP BY DATE(payment_date)
                ORDER BY date
            ";
            
            return $this->db->query($query, [$startDate, $endDate])->getResultArray();
        } catch (\Exception $e) {
            log_message('error', 'ReportsModel::getRevenueTrend - ' . $e->getMessage());
            return [];
        }
    }
    
    /**
     * Get subscription distribution
     */
    public function getSubscriptionDistribution($startDate = null, $endDate = null)
    {
        try {
            $query = "
                SELECT 
                    pl.plan_name,
                    COUNT(DISTINCT s.subscription_id) as subscription_count
                FROM plans pl
                LEFT JOIN subscriptions s ON pl.plan_id = s.plan_id
                    AND s.status = 'active'
                    AND (DATE(s.purchase_date) >= ? OR ? IS NULL)
                    AND (DATE(s.purchase_date) <= ? OR ? IS NULL)
                GROUP BY pl.plan_id, pl.plan_name
                ORDER BY subscription_count DESC
            ";
            
            return $this->db->query($query, [$startDate, $startDate, $endDate, $endDate])->getResultArray();
        } catch (\Exception $e) {
            log_message('error', 'ReportsModel::getSubscriptionDistribution - ' . $e->getMessage());
            return [];
        }
    }
    
    /**
     * Get booking status breakdown
     */
    public function getBookingStatusBreakdown($startDate = null, $endDate = null)
    {
        try {
            $query = "
                SELECT 
                    r.booking_status as status,
                    COUNT(*) as count
                FROM reservations r
                WHERE (DATE(r.created_at) >= ? OR ? IS NULL)
                AND (DATE(r.created_at) <= ? OR ? IS NULL)
                GROUP BY r.booking_status
                ORDER BY count DESC
            ";
            
            return $this->db->query($query, [$startDate, $startDate, $endDate, $endDate])->getResultArray();
        } catch (\Exception $e) {
            log_message('error', 'ReportsModel::getBookingStatusBreakdown - ' . $e->getMessage());
            return [];
        }
    }
    
    // ===== ENHANCED ANALYTICS METHODS =====
    
    /**
     * Get revenue by hour of day
     */
    public function getRevenueByHour($startDate = null, $endDate = null)
    {
        try {
            $query = "
                SELECT 
                    HOUR(p.payment_date) as hour,
                    COALESCE(SUM(p.amount), 0) as revenue,
                    COUNT(DISTINCT p.payment_id) as transaction_count
                FROM payments p
                WHERE p.status = 'paid'
                AND (DATE(p.payment_date) >= ? OR ? IS NULL)
                AND (DATE(p.payment_date) <= ? OR ? IS NULL)
                GROUP BY HOUR(p.payment_date)
                ORDER BY hour
            ";
            
            return $this->db->query($query, [$startDate, $startDate, $endDate, $endDate])->getResultArray();
        } catch (\Exception $e) {
            log_message('error', 'ReportsModel::getRevenueByHour - ' . $e->getMessage());
            return [];
        }
    }
    
    /**
     * Get revenue by day of week
     */
    public function getRevenueByDayOfWeek($startDate = null, $endDate = null)
    {
        try {
            $query = "
                SELECT 
                    DAYNAME(p.payment_date) as day_name,
                    DAYOFWEEK(p.payment_date) as day_of_week,
                    COALESCE(SUM(p.amount), 0) as revenue,
                    COUNT(DISTINCT p.payment_id) as transaction_count
                FROM payments p
                WHERE p.status = 'paid'
                AND (DATE(p.payment_date) >= ? OR ? IS NULL)
                AND (DATE(p.payment_date) <= ? OR ? IS NULL)
                GROUP BY DAYNAME(p.payment_date), DAYOFWEEK(p.payment_date)
                ORDER BY day_of_week
            ";
            
            return $this->db->query($query, [$startDate, $startDate, $endDate, $endDate])->getResultArray();
        } catch (\Exception $e) {
            log_message('error', 'ReportsModel::getRevenueByDayOfWeek - ' . $e->getMessage());
            return [];
        }
    }
    
    /**
     * Get bookings by day of week
     */
    public function getBookingsByDayOfWeek($startDate = null, $endDate = null)
    {
        try {
            $query = "
                SELECT 
                    DAYNAME(r.created_at) as day_name,
                    DAYOFWEEK(r.created_at) as day_of_week,
                    COUNT(*) as booking_count,
                    COUNT(DISTINCT r.user_id) as unique_users
                FROM reservations r
                WHERE (DATE(r.created_at) >= ? OR ? IS NULL)
                AND (DATE(r.created_at) <= ? OR ? IS NULL)
                GROUP BY DAYNAME(r.created_at), DAYOFWEEK(r.created_at)
                ORDER BY day_of_week
            ";
            
            return $this->db->query($query, [$startDate, $startDate, $endDate, $endDate])->getResultArray();
        } catch (\Exception $e) {
            log_message('error', 'ReportsModel::getBookingsByDayOfWeek - ' . $e->getMessage());
            return [];
        }
    }
    
    /**
     * Get cancellation and repeat booking metrics
     */
    public function getBookingMetrics($startDate = null, $endDate = null)
    {
        try {
            // Total bookings
            $totalBookingsQuery = "
                SELECT COUNT(*) as total_bookings
                FROM reservations
                WHERE (DATE(created_at) >= ? OR ? IS NULL)
                AND (DATE(created_at) <= ? OR ? IS NULL)
            ";
            $totalBookings = $this->db->query($totalBookingsQuery, [$startDate, $startDate, $endDate, $endDate])->getRow()->total_bookings;
            
            // Cancelled bookings
            $cancelledQuery = "
                SELECT COUNT(*) as cancelled_bookings
                FROM reservations
                WHERE booking_status = 'cancelled'
                AND (DATE(created_at) >= ? OR ? IS NULL)
                AND (DATE(created_at) <= ? OR ? IS NULL)
            ";
            $cancelledBookings = $this->db->query($cancelledQuery, [$startDate, $startDate, $endDate, $endDate])->getRow()->cancelled_bookings;
            
            // Repeat bookings (users with more than 1 booking)
            $repeatQuery = "
                SELECT 
                    COUNT(*) as repeat_bookings,
                    COUNT(DISTINCT user_id) as repeat_users
                FROM reservations
                WHERE user_id IN (
                    SELECT user_id 
                    FROM reservations 
                    WHERE (DATE(created_at) >= ? OR ? IS NULL)
                    AND (DATE(created_at) <= ? OR ? IS NULL)
                    GROUP BY user_id 
                    HAVING COUNT(*) > 1
                )
                AND (DATE(created_at) >= ? OR ? IS NULL)
                AND (DATE(created_at) <= ? OR ? IS NULL)
            ";
            $repeatData = $this->db->query($repeatQuery, [$startDate, $startDate, $endDate, $endDate, $startDate, $startDate, $endDate, $endDate])->getRow();
            
            return [
                'total_bookings' => $totalBookings,
                'cancelled_bookings' => $cancelledBookings,
                'cancellation_rate' => $totalBookings > 0 ? ($cancelledBookings / $totalBookings) * 100 : 0,
                'repeat_bookings' => $repeatData->repeat_bookings ?? 0,
                'repeat_users' => $repeatData->repeat_users ?? 0,
                'repeat_booking_rate' => $totalBookings > 0 ? (($repeatData->repeat_bookings ?? 0) / $totalBookings) * 100 : 0
            ];
        } catch (\Exception $e) {
            log_message('error', 'ReportsModel::getBookingMetrics - ' . $e->getMessage());
            return [
                'total_bookings' => 0,
                'cancelled_bookings' => 0,
                'cancellation_rate' => 0,
                'repeat_bookings' => 0,
                'repeat_users' => 0,
                'repeat_booking_rate' => 0
            ];
        }
    }
    
    /**
     * Get hourly occupancy heatmap data
     */
    public function getHourlyOccupancy($startDate = null, $endDate = null)
    {
        try {
            $query = "
                SELECT 
                    HOUR(r.created_at) as hour,
                    COUNT(*) as bookings,
                    COUNT(DISTINCT ps.parking_section_id) as sections_used
                FROM reservations r
                LEFT JOIN parking_spot ps ON r.parking_spots_id = ps.parking_spot_id
                WHERE (DATE(r.created_at) >= ? OR ? IS NULL)
                AND (DATE(r.created_at) <= ? OR ? IS NULL)
                GROUP BY HOUR(r.created_at)
                ORDER BY hour
            ";
            
            return $this->db->query($query, [$startDate, $startDate, $endDate, $endDate])->getResultArray();
        } catch (\Exception $e) {
            log_message('error', 'ReportsModel::getHourlyOccupancy - ' . $e->getMessage());
            return [];
        }
    }
    
    /**
     * Get area performance metrics
     */
    public function getAreaPerformance($startDate = null, $endDate = null)
    {
        try {
            $query = "
                SELECT 
                    pa.parking_area_id,
                    pa.parking_area_name,
                    COUNT(DISTINCT ps.parking_spot_id) as total_spots,
                    COUNT(DISTINCT r.reservation_id) as total_bookings,
                    0 as total_revenue,
                    COUNT(DISTINCT r.user_id) as unique_users
                FROM parking_area pa
                LEFT JOIN parking_section psec ON pa.parking_area_id = psec.parking_area_id
                LEFT JOIN parking_spot ps ON psec.parking_section_id = ps.parking_section_id
                LEFT JOIN reservations r ON ps.parking_spot_id = r.parking_spots_id
                    AND (DATE(r.created_at) >= ? OR ? IS NULL)
                    AND (DATE(r.created_at) <= ? OR ? IS NULL)
                WHERE pa.status = 'active'
                GROUP BY pa.parking_area_id, pa.parking_area_name
                ORDER BY total_bookings DESC
            ";
            
            $results = $this->db->query($query, [$startDate, $startDate, $endDate, $endDate])->getResultArray();
            
            // Calculate utilization and revenue per spot
            foreach ($results as &$area) {
                $area['utilization_percent'] = $area['total_spots'] > 0 ? ($area['total_bookings'] / $area['total_spots']) * 100 : 0;
                $area['revenue_per_spot'] = $area['total_spots'] > 0 ? $area['total_revenue'] / $area['total_spots'] : 0;
                $area['turnover_rate'] = $area['total_spots'] > 0 ? $area['total_bookings'] / $area['total_spots'] : 0;
            }
            
            return $results;
        } catch (\Exception $e) {
            log_message('error', 'ReportsModel::getAreaPerformance - ' . $e->getMessage());
            return [];
        }
    }
    
    /**
     * Get user analytics including retention and lifetime value
     */
    public function getUserAnalytics($startDate = null, $endDate = null)
    {
        try {
            // Total users and active users - FILTER FOR SUBSCRIBERS ONLY (user_type_id = 1)
            $userQuery = "
                SELECT 
                    COUNT(DISTINCT u.user_id) as total_users,
                    COUNT(DISTINCT CASE WHEN r.reservation_id IS NOT NULL THEN u.user_id END) as active_users,
                    COUNT(DISTINCT CASE WHEN u.last_activity_at >= DATE_SUB(CURDATE(), INTERVAL 30 DAY) THEN u.user_id END) as recently_active,
                    COALESCE(SUM(p.amount), 0) as total_revenue,
                    COUNT(DISTINCT r.reservation_id) as total_bookings
                FROM users u
                LEFT JOIN reservations r ON u.user_id = r.user_id
                    AND (DATE(r.created_at) >= ? OR ? IS NULL)
                    AND (DATE(r.created_at) <= ? OR ? IS NULL)
                LEFT JOIN payments p ON u.user_id = p.user_id 
                    AND p.status = 'paid'
                    AND (DATE(p.payment_date) >= ? OR ? IS NULL)
                    AND (DATE(p.payment_date) <= ? OR ? IS NULL)
                WHERE u.user_type_id = 1  -- Only subscribers
            ";
            
            $userData = $this->db->query($userQuery, [$startDate, $startDate, $endDate, $endDate, $startDate, $startDate, $endDate, $endDate])->getRow();
            
            // Top spending users - FILTER FOR SUBSCRIBERS ONLY
            $topUsersQuery = "
                SELECT 
                    u.user_id,
                    CONCAT(u.first_name, ' ', u.last_name) as user_name,
                    u.email,
                    COUNT(DISTINCT r.reservation_id) as booking_count,
                    COALESCE(SUM(p.amount), 0) as total_spent,
                    MAX(r.created_at) as last_booking
                FROM users u
                LEFT JOIN reservations r ON u.user_id = r.user_id
                    AND (DATE(r.created_at) >= ? OR ? IS NULL)
                    AND (DATE(r.created_at) <= ? OR ? IS NULL)
                LEFT JOIN payments p ON u.user_id = p.user_id 
                    AND p.status = 'paid'
                    AND (DATE(p.payment_date) >= ? OR ? IS NULL)
                    AND (DATE(p.payment_date) <= ? OR ? IS NULL)
                WHERE u.user_type_id = 1  -- Only subscribers
                GROUP BY u.user_id, u.first_name, u.last_name, u.email
                HAVING total_spent > 0 OR booking_count > 0
                ORDER BY total_spent DESC, booking_count DESC
                LIMIT 10
            ";
            
            $topUsers = $this->db->query($topUsersQuery, [$startDate, $startDate, $endDate, $endDate, $startDate, $startDate, $endDate, $endDate])->getResultArray();
            
            // Calculate lifetime value and retention
            $lifetimeValue = $userData->total_users > 0 ? $userData->total_revenue / $userData->total_users : 0;
            $retentionRate = $userData->total_users > 0 ? (($userData->recently_active ?? 0) / $userData->total_users) * 100 : 0;
            
            return [
                'total_users' => $userData->total_users ?? 0,
                'active_users' => $userData->active_users ?? 0,
                'recently_active' => $userData->recently_active ?? 0,
                'retention_rate' => $retentionRate,
                'lifetime_value' => $lifetimeValue,
                'total_revenue' => $userData->total_revenue ?? 0,
                'total_bookings' => $userData->total_bookings ?? 0,
                'top_spending_users' => $topUsers
            ];
        } catch (\Exception $e) {
            log_message('error', 'ReportsModel::getUserAnalytics - ' . $e->getMessage());
            return [
                'total_users' => 0,
                'active_users' => 0,
                'recently_active' => 0,
                'retention_rate' => 0,
                'lifetime_value' => 0,
                'total_revenue' => 0,
                'total_bookings' => 0,
                'top_spending_users' => []
            ];
        }
    }
    
    /**
     * Get period comparison data
     */
    public function getPeriodComparison($currentStart, $currentEnd, $previousStart, $previousEnd)
    {
        try {
            // Current period data - using subscription payments
            $currentQuery = "
                SELECT 
                    COALESCE(SUM(p.amount), 0) as revenue,
                    COUNT(DISTINCT r.reservation_id) as bookings,
                    COUNT(DISTINCT r.user_id) as active_users
                FROM reservations r
                LEFT JOIN payments p ON r.user_id = p.user_id 
                    AND p.status = 'paid'
                    AND DATE(p.payment_date) >= ? AND DATE(p.payment_date) <= ?
                WHERE DATE(r.created_at) >= ? AND DATE(r.created_at) <= ?
            ";
            $current = $this->db->query($currentQuery, [$currentStart, $currentEnd, $currentStart, $currentEnd])->getRow();
            
            // Previous period data - using subscription payments
            $previousQuery = "
                SELECT 
                    COALESCE(SUM(p.amount), 0) as revenue,
                    COUNT(DISTINCT r.reservation_id) as bookings,
                    COUNT(DISTINCT r.user_id) as active_users
                FROM reservations r
                LEFT JOIN payments p ON r.user_id = p.user_id 
                    AND p.status = 'paid'
                    AND DATE(p.payment_date) >= ? AND DATE(p.payment_date) <= ?
                WHERE DATE(r.created_at) >= ? AND DATE(r.created_at) <= ?
            ";
            $previous = $this->db->query($previousQuery, [$previousStart, $previousEnd, $previousStart, $previousEnd])->getRow();
            
            // Calculate changes with better handling for zero previous values
            $revenueChange = 0;
            $bookingsChange = 0;
            $usersChange = 0;
            
            // Revenue change calculation
            if ($previous->revenue > 0) {
                $revenueChange = (($current->revenue - $previous->revenue) / $previous->revenue) * 100;
            } elseif ($current->revenue > 0) {
                // If previous was 0 but current has data, show as growth
                $revenueChange = 100; // Show as 100% growth from zero
            }
            
            // Bookings change calculation
            if ($previous->bookings > 0) {
                $bookingsChange = (($current->bookings - $previous->bookings) / $previous->bookings) * 100;
            } elseif ($current->bookings > 0) {
                // If previous was 0 but current has data, show as growth
                $bookingsChange = 100; // Show as 100% growth from zero
            }
            
            // Users change calculation
            if ($previous->active_users > 0) {
                $usersChange = (($current->active_users - $previous->active_users) / $previous->active_users) * 100;
            } elseif ($current->active_users > 0) {
                // If previous was 0 but current has data, show as growth
                $usersChange = 100; // Show as 100% growth from zero
            }
            
            return [
                'current' => [
                    'revenue' => $current->revenue ?? 0,
                    'bookings' => $current->bookings ?? 0,
                    'active_users' => $current->active_users ?? 0
                ],
                'previous' => [
                    'revenue' => $previous->revenue ?? 0,
                    'bookings' => $previous->bookings ?? 0,
                    'active_users' => $previous->active_users ?? 0
                ],
                'changes' => [
                    'revenue_change' => $revenueChange,
                    'bookings_change' => $bookingsChange,
                    'users_change' => $usersChange
                ]
            ];
        } catch (\Exception $e) {
            log_message('error', 'ReportsModel::getPeriodComparison - ' . $e->getMessage());
            return [
                'current' => ['revenue' => 0, 'bookings' => 0, 'active_users' => 0],
                'previous' => ['revenue' => 0, 'bookings' => 0, 'active_users' => 0],
                'changes' => ['revenue_change' => 0, 'bookings_change' => 0, 'users_change' => 0]
            ];
        }
    }

    public function getFeedbackRatingDistribution($startDate = null, $endDate = null)
    {
        try {
            $builder = $this->db->table('feedback')
                ->select('rating, COUNT(*) as count')
                ->where('status', 'active')
                ->groupby('rating')
                ->orderby('rating', 'ASC');

            if ($startDate) {
                $builder->where('DATE(created_at) >=', $startDate);
            }
            if ($endDate) {
                $builder->where('DATE(created_at) <=', $endDate);
            }

            return $builder->get()->getResultArray();
        } catch (\Exception $e) {
            log_message('error', 'ReportsModel::getFeedbackRatingDistribution - ' . $e->getMessage());
            return [];
        }
    }

    public function getFeedbackList($startDate = null, $endDate = null, $limit = 50)
    {
        try {
            $builder = $this->db->table('feedback f')
                ->select("f.feedback_id, f.user_id, f.subscription_id, f.rating, f.content, f.status, f.created_at, u.first_name, u.last_name, u.email")
                ->join('users u', 'u.user_id = f.user_id', 'left')
                ->where('f.status', 'active')
                ->orderby('f.created_at', 'DESC')
                ->limit((int)$limit);

            if ($startDate) {
                $builder->where('DATE(f.created_at) >=', $startDate);
            }
            if ($endDate) {
                $builder->where('DATE(f.created_at) <=', $endDate);
            }

            return $builder->get()->getResultArray();
        } catch (\Exception $e) {
            log_message('error', 'ReportsModel::getFeedbackList - ' . $e->getMessage());
            return [];
        }
    }

    /**
     * Get guest bookings statistics for reports
     */
    public function getGuestBookingsStats($filter = 'today', $customStart = null, $customEnd = null)
    {
        try {
            $dateRange = $this->getDateRange($filter, $customStart, $customEnd);
            $start = $dateRange['start'];
            $end = $dateRange['end'];
            
            // Total guest bookings in date range
            $totalGuestBookings = $this->db->table('guest_bookings')
                ->where('created_at >=', $start)
                ->where('created_at <=', $end)
                ->countAllResults();
            
            // Guest bookings by date for chart
            $guestBookingsByDate = $this->db->table('guest_bookings')
                ->select('DATE(created_at) as date, COUNT(*) as count')
                ->where('created_at >=', $start)
                ->where('created_at <=', $end)
                ->groupBy('DATE(created_at)')
                ->orderBy('date', 'ASC')
                ->get()
                ->getResultArray();
            
            // Guest bookings by vehicle type
            $guestBookingsByVehicle = $this->db->table('guest_bookings gb')
                ->select('v.vehicle_type, COUNT(*) as count')
                ->join('vehicles v', 'v.vehicle_id = gb.vehicle_id')
                ->where('gb.created_at >=', $start)
                ->where('gb.created_at <=', $end)
                ->groupBy('v.vehicle_type')
                ->get()
                ->getResultArray();
            
            // Guest bookings by attendant
            $guestBookingsByAttendant = $this->db->table('guest_bookings gb')
                ->select('CONCAT(u.first_name, " ", u.last_name) as attendant_name, COUNT(*) as count')
                ->join('users u', 'u.user_id = gb.attendant_id')
                ->where('gb.created_at >=', $start)
                ->where('gb.created_at <=', $end)
                ->groupBy('gb.attendant_id, u.first_name, u.last_name')
                ->orderBy('count', 'DESC')
                ->limit(5)
                ->get()
                ->getResultArray();
            
            return [
                'total_guest_bookings' => $totalGuestBookings,
                'guest_bookings_by_date' => $guestBookingsByDate,
                'guest_bookings_by_vehicle' => $guestBookingsByVehicle,
                'guest_bookings_by_attendant' => $guestBookingsByAttendant
            ];
        } catch (\Exception $e) {
            log_message('error', 'ReportsModel::getGuestBookingsStats - ' . $e->getMessage());
            return [
                'total_guest_bookings' => 0,
                'guest_bookings_by_date' => [],
                'guest_bookings_by_vehicle' => [],
                'guest_bookings_by_attendant' => []
            ];
        }
    }

    private function resolveRange(?string $startDate, ?string $endDate): array
    {
        if ($startDate && $endDate) {
            $rangeStart = $startDate . ' 00:00:00';
            $rangeEnd = $endDate . ' 23:59:59';
            [$rangeStart, $rangeEnd] = $this->clampDateRange($rangeStart, $rangeEnd);
        } else {
            $range = $this->getDateRange('30_days');
            $rangeStart = $range['start'];
            $rangeEnd = $range['end'];
        }

        $start = new \DateTime($rangeStart);
        $end = new \DateTime($rangeEnd);
        $days = max(1, $start->diff($end)->days + 1);

        return [$rangeStart, $rangeEnd, $start, $end, $days];
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

    private function formatBucketLabel(\DateTime $start, \DateTime $end): string
    {
        if ($start->format('Y-m-d') === $end->format('Y-m-d')) {
            return $start->format('M j');
        }

        if ($start->format('Y-m') === $end->format('Y-m')) {
            return $start->format('M j') . ' – ' . $end->format('j');
        }

        return $start->format('M j') . ' – ' . $end->format('M j');
    }

    private function shouldUseMonthlyBuckets(\DateTime $start, \DateTime $end): bool
    {
        $days = $start->diff($end)->days + 1;
        return $days > 31;
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
            default:
                if ($customStart && $customEnd) {
                    $customStartDate = date('Y-m-d', strtotime($customStart));
                    $customEndDate = date('Y-m-d', strtotime($customEnd));
                    if (strtotime($customStartDate) > strtotime($customEndDate)) {
                        [$customStartDate, $customEndDate] = [$customEndDate, $customStartDate];
                    }
                    $start = $customStartDate . ' 00:00:00';
                    $end = $customEndDate . ' 23:59:59';
                } else {
                    $start = date('Y-m-d 00:00:00', strtotime($baseDate . ' -29 days'));
                }
        }

        [$start, $end] = $this->clampDateRange($start ?? ($baseDate . ' 00:00:00'), $end);
        return ['start' => $start, 'end' => $end];
    }
}


