<?php

namespace App\Models;

use CodeIgniter\Model;

class UserLogModel extends Model
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

    // Dates
    protected $useTimestamps = false; // Using custom timestamp field
    protected $dateFormat = 'datetime';

    // Validation
    protected $validationRules = [
        'user_id' => 'required|integer',
        'target_id' => 'permit_empty|integer',
        'action_type' => 'permit_empty|max_length[50]',
        'change_field' => 'permit_empty|max_length[50]',
        'description' => 'required|max_length[500]'
    ];

    protected $validationMessages = [
        'user_id' => [
            'required' => 'User ID is required',
            'integer' => 'User ID must be a valid integer'
        ],
        'description' => [
            'required' => 'Description is required',
            'max_length' => 'Description cannot exceed 500 characters'
        ]
    ];

    protected $skipValidation = false;
    protected $cleanValidationRules = true;

    /**
     * Log user activity
     */
    public function logActivity($userId, $actionType, $description = '', $targetId = null, $changeField = null)
    {
        $data = [
            'user_id' => $userId,
            'target_id' => $targetId,
            'action_type' => $actionType,
            'change_field' => $changeField,
            'description' => $description,
            'timestamp' => date('Y-m-d H:i:s')
        ];

        return $this->insert($data);
    }

    /**
     * Get user activity logs
     */
    public function getUserLogs($userId, $limit = 50)
    {
        return $this->where('user_id', $userId)
                   ->orderBy('timestamp', 'DESC')
                   ->limit($limit)
                   ->findAll();
    }

    /**
     * Get recent activity logs
     */
    public function getRecentLogs($limit = 100)
    {
        return $this->select('user_logs.*, users.first_name, users.last_name, users.email')
                   ->join('users', 'users.user_id = user_logs.user_id', 'left')
                   ->orderBy('user_logs.timestamp', 'DESC')
                   ->limit($limit)
                   ->findAll();
    }

    /**
     * Get login attempts (for security monitoring)
     */
    public function getLoginAttempts($hours = 24)
    {
        $since = date('Y-m-d H:i:s', strtotime("-{$hours} hours"));
        
        return $this->select('user_logs.*, users.first_name, users.last_name, users.email')
                   ->join('users', 'users.user_id = user_logs.user_id', 'left') // LEFT JOIN for failed attempts with user_id = 0
                   ->where('user_logs.action_type', 'LOGIN')
                   ->where('user_logs.timestamp >=', $since)
                   ->orderBy('user_logs.timestamp', 'DESC')
                   ->findAll();
    }

    /**
     * Get failed login attempts (for security monitoring)
     */
    public function getFailedLoginAttempts($hours = 24)
    {
        $since = date('Y-m-d H:i:s', strtotime("-{$hours} hours"));
        
        return $this->where('user_logs.action_type IS NULL')
                   ->where('user_logs.description LIKE', '%Failed login attempt%')
                   ->where('user_logs.timestamp >=', $since)
                   ->orderBy('user_logs.timestamp', 'DESC')
                   ->findAll();
    }

    /**
     * Clean up old logs (keep only last 90 days)
     */
    public function cleanupOldLogs()
    {
        $cutoffDate = date('Y-m-d H:i:s', strtotime('-90 days'));
        
        return $this->where('timestamp <', $cutoffDate)->delete();
    }
}

