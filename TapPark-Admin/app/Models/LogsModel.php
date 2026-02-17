<?php

namespace App\Models;

use CodeIgniter\Model;

class LogsModel extends Model
{
    protected $table = 'user_logs';
    protected $primaryKey = 'logs_id';
    protected $useAutoIncrement = true;
    protected $returnType = 'array';
    protected $useSoftDeletes = false;
    protected $protectFields = true;
    protected $allowedFields = [
        'user_id', 'target_id', 'action_type', 'change_field', 'description', 'timestamp'
    ];

    /**
     * Get all logs with user information and filtering (with pagination)
     */
    public function getLogsWithFilters($filters = [])
    {
        $builder = $this->db->table('user_logs')
            ->select('user_logs.*, 
                      users.first_name, 
                      users.last_name, 
                      users.email')
            ->join('users', 'users.user_id = user_logs.user_id', 'left')
            ->orderBy('user_logs.timestamp', 'DESC');

        // Apply filters
        $this->applyFilters($builder, $filters);

        // Pagination
        $perPage = $filters['per_page'] ?? 25;
        $page = $filters['page'] ?? 1;
        $offset = ($page - 1) * $perPage;

        $builder->limit($perPage, $offset);

        return $builder->get()->getResultArray();
    }

    /**
     * Get total count of logs with filters (for pagination)
     */
    public function getLogsCount($filters = [])
    {
        $builder = $this->db->table('user_logs')
            ->join('users', 'users.user_id = user_logs.user_id', 'left');

        // Apply same filters
        $this->applyFilters($builder, $filters);

        return $builder->countAllResults();
    }

    /**
     * Apply filters to query builder
     */
    private function applyFilters($builder, $filters)
    {
        // Filter by action type
        if (!empty($filters['action_type']) && $filters['action_type'] !== 'all') {
            if ($filters['action_type'] === 'NULL') {
                $builder->where('user_logs.action_type IS NULL');
            } else {
                $builder->where('user_logs.action_type', $filters['action_type']);
            }
        }

        // Filter by date range
        if (!empty($filters['start_date'])) {
            $builder->where('DATE(user_logs.timestamp) >=', $filters['start_date']);
        }
        if (!empty($filters['end_date'])) {
            $builder->where('DATE(user_logs.timestamp) <=', $filters['end_date']);
        }

        // Filter by user
        if (!empty($filters['user_id'])) {
            $builder->where('user_logs.user_id', $filters['user_id']);
        }

        // Search in description
        if (!empty($filters['search'])) {
            $builder->like('user_logs.description', $filters['search']);
        }
    }

    /**
     * Get activity summary statistics
     */
    public function getActivitySummary($days = 7)
    {
        $since = date('Y-m-d', strtotime("-{$days} days"));
        
        // Total activities
        $totalActivities = $this->db->table('user_logs')
            ->where('DATE(timestamp) >=', $since)
            ->countAllResults();
        
        // Unique active users
        $activeUsers = $this->db->table('user_logs')
            ->select('COUNT(DISTINCT user_id) as count')
            ->where('DATE(timestamp) >=', $since)
            ->where('user_id >', 0)
            ->get()
            ->getRow();
        
        $activeUsersCount = $activeUsers ? (int)$activeUsers->count : 0;
        
        // Activities by type
        $activitiesByType = $this->db->table('user_logs')
            ->select('action_type, COUNT(*) as count')
            ->where('DATE(timestamp) >=', $since)
            ->groupBy('action_type')
            ->orderBy('count', 'DESC')
            ->get()
            ->getResultArray();
        
        // Recent failed logins
        $failedLogins = $this->db->table('user_logs')
            ->where('DATE(timestamp) >=', $since)
            ->like('description', 'Failed login')
            ->countAllResults();
        
        return [
            'total_activities' => $totalActivities,
            'active_users' => $activeUsersCount,
            'activities_by_type' => $activitiesByType,
            'failed_logins' => $failedLogins,
            'period_days' => $days
        ];
    }

    /**
     * Get unique action types for filter dropdown
     */
    public function getActionTypes()
    {
        return $this->db->table('user_logs')
            ->distinct()
            ->select('action_type')
            ->where('action_type IS NOT NULL')
            ->orderBy('action_type', 'ASC')
            ->get()
            ->getResultArray();
    }

    /**
     * Get activity timeline (daily counts for charts)
     */
    public function getActivityTimeline($days = 30)
    {
        $since = date('Y-m-d', strtotime("-{$days} days"));
        
        $query = "
            SELECT 
                DATE(timestamp) as date,
                COUNT(*) as count
            FROM user_logs
            WHERE DATE(timestamp) >= ?
            GROUP BY DATE(timestamp)
            ORDER BY date ASC
        ";
        
        return $this->db->query($query, [$since])->getResultArray();
    }

    /**
     * Get most active users
     * 
     * @param int $limit Maximum number of users to return
     * @param int $days Number of days to look back (used if startDate/endDate not provided)
     * @param string|null $startDate Start date (Y-m-d format) - optional
     * @param string|null $endDate End date (Y-m-d format) - optional
     */
    public function getMostActiveUsers($limit = 10, $days = 30, $startDate = null, $endDate = null)
    {
        $query = $this->db->table('user_logs')
            ->select('user_logs.user_id, 
                      users.first_name, 
                      users.last_name, 
                      users.email,
                      COUNT(*) as activity_count')
            ->join('users', 'users.user_id = user_logs.user_id', 'left')
            ->where('user_logs.user_id >', 0);

        // Exclude seeded/demo users from "Most Active Users" widget
        $query->groupStart()
            ->where('users.email IS NULL')
            ->orWhere('users.email NOT LIKE', 'seed.%@example.com')
            ->groupEnd();
        $query->groupStart()
            ->where('users.external_user_id IS NULL')
            ->orWhere('users.external_user_id NOT LIKE', 'SEED-%')
            ->groupEnd();
        
        // Use date range if provided and valid, otherwise use days
        if (!empty($startDate) && !empty($endDate) && $startDate !== null && $endDate !== null) {
            $query->where('DATE(user_logs.timestamp) >=', $startDate)
                  ->where('DATE(user_logs.timestamp) <=', $endDate);
        } else {
            // Always use the last N days period (not filtered by any date range)
            $since = date('Y-m-d', strtotime("-{$days} days"));
            $query->where('DATE(user_logs.timestamp) >=', $since);
        }
        
        // Get the SQL query for debugging
        $sqlQuery = $query->groupBy('user_logs.user_id, users.first_name, users.last_name, users.email')
            ->orderBy('activity_count', 'DESC')
            ->limit($limit);
        
        $result = $sqlQuery->get()->getResultArray();
        
        // Ensure we always return an array
        if (!is_array($result)) {
            $result = [];
        }
        
        // Log for debugging (can be removed later)
        $since = !empty($startDate) && !empty($endDate) ? "{$startDate} to {$endDate}" : date('Y-m-d', strtotime("-{$days} days")) . " (last {$days} days)";
        $logMessage = "getMostActiveUsers - Period: {$since} | Results: " . count($result);
        if (!empty($result)) {
            $logMessage .= ' | First user: ' . ($result[0]['first_name'] ?? 'N/A') . ' with count: ' . ($result[0]['activity_count'] ?? 'N/A');
        }
        log_message('debug', $logMessage);
        
        return $result;
    }
}

