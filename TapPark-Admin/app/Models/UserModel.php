<?php

namespace App\Models;

use CodeIgniter\Model;

class UserModel extends Model
{
    // User Role Constants
    public const ROLE_SUBSCRIBER = 1;
    public const ROLE_ATTENDANT = 2; // Attendant / Staff
    public const ROLE_ADMIN = 3;      // Administrator

    protected $table = 'users';
    protected $primaryKey = 'user_id';
    protected $allowedFields = [
        'external_user_id',
        'profile_picture',
        'email',
        'last_name',
        'first_name',
        'password',
        'hour_balance',
        'user_type_id',
        'assigned_area_id',
        'is_online',
        'last_activity_at',
        'status'
    ];
    protected $useTimestamps = true;
    protected $createdField = 'created_at';
    protected $updatedField = 'updated_at';

    /**
     * Authenticate user with email and password
     * 
     * @param string $email User's email address
     * @param string $password User's plain text password
     * @return array|false Returns user array if authenticated, false otherwise
     */
    public function authenticate($email, $password)
    {
        // Find user by email with active status
        $user = $this->where('email', $email)
                     ->where('status', 'active')
                     ->first();

        // If user not found, return false
        if (!$user) {
            return false;
        }

        // Verify password using PHP's password_verify
        // This checks the plain text password against the hashed password in database
        if (password_verify($password, $user['password'])) {
            return $user;
        }

        // Password doesn't match
        return false;
    }

    /**
     * Check if user is admin
     * 
     * Admin users have user_type_id = 3
     * 
     * @param int $userId User ID to check
     * @return bool True if admin, false otherwise
     */
    public function isAdmin($userId)
    {
        $user = $this->find($userId);
        
        // If user not found, return false
        if (!$user) {
            return false;
        }

        // Admin type_id = 3
        return $user['user_type_id'] == 3;
    }

    /**
     * Update user's online status
     * 
     * @param int $userId User ID
     * @param bool $isOnline Online status (true/false)
     * @return bool Success status
     */
    public function updateOnlineStatus($userId, $isOnline)
    {
        return $this->update($userId, [
            'is_online' => $isOnline ? 1 : 0,
            'last_activity_at' => date('Y-m-d H:i:s')
        ]);
    }

    /**
     * Get users with filters and pagination
     */
    public function getUsersWithFilters($filters = [], $limit = 25, $offset = 0)
    {
        $builder = $this->builder();
        $builder->select('users.*, types.account_type_name as user_type_name, parking_area.parking_area_name');
        $builder->join('types', 'types.type_id = users.user_type_id', 'left');
        $builder->join('parking_area', 'users.assigned_area_id = parking_area.parking_area_id', 'left');

        // By default, exclude admin and attendant (if not explicitly requested)
        if (empty($filters['user_type_id'])) {
            $builder->whereNotIn('users.user_type_id', [self::ROLE_ATTENDANT, self::ROLE_ADMIN]);
        } else {
            $builder->where('users.user_type_id', $filters['user_type_id']);
        }
        $builder->orderBy('users.created_at', 'DESC');

        // Apply filters
        $this->applyFilters($builder, $filters);

        return $builder->limit($limit, $offset)->get()->getResultArray();
    }

    /**
     * Get total users count with filters
     */
    public function getUsersCount($filters = [])
    {
        $builder = $this->builder();
        $builder->whereNotIn('users.user_type_id', [self::ROLE_ATTENDANT, self::ROLE_ADMIN]); // Exclude admin and attendants

        // Apply filters
        $this->applyFilters($builder, $filters);

        return $builder->countAllResults();
    }

    /**
     * Apply filters to query builder
     */
    private function applyFilters($builder, $filters)
    {
        // Search filter
        if (!empty($filters['search'])) {
            $search = $filters['search'];
            $builder->groupStart()
                ->like('users.user_id', $search)
                ->orLike('users.first_name', $search)
                ->orLike('users.last_name', $search)
                ->orLike('users.email', $search)
                ->orLike('users.external_user_id', $search)
                ->groupEnd();
        }

        // User type filter
        if (!empty($filters['user_type_id'])) {
            $builder->where('users.user_type_id', $filters['user_type_id']);
        }

        // Status filter
        if (!empty($filters['status'])) {
            $builder->where('users.status', $filters['status']);
        }

        // Online status filter
        if (isset($filters['is_online']) && $filters['is_online'] !== '') {
            $builder->where('users.is_online', $filters['is_online']);
        }
    }

    /**
     * Get user by ID with relationships
     */
    public function getUserById($userId)
    {
        return $this->db->table('users u')
            ->select('u.*, t.account_type_name as user_type_name, pa.parking_area_name')
            ->join('types t', 'u.user_type_id = t.type_id', 'left')
            ->join('parking_area pa', 'u.assigned_area_id = pa.parking_area_id', 'left')
            ->where('u.user_id', $userId)
            ->get()
            ->getRowArray();
    }

    /**
     * Get attendant by ID with relationships
     */
    public function getAttendantById($userId)
    {
        return $this->db->table('users u')
            ->select('u.*, t.account_type_name as user_type_name, pa.parking_area_name')
            ->join('types t', 'u.user_type_id = t.type_id', 'left')
            ->join('parking_area pa', 'u.assigned_area_id = pa.parking_area_id', 'left')
            ->where('u.user_id', $userId)
            ->get()
            ->getRowArray();
    }

    /**
     * Get user types
     */
    public function getUserTypes()
    {
        // Use types table with account_type_name column
        $types = $this->db->table('types')
            ->select('type_id as user_type_id, account_type_name as user_type_name')
            ->whereNotIn('type_id', [self::ROLE_ATTENDANT, self::ROLE_ADMIN]) // Exclude admin and attendant types
            ->get()
            ->getResultArray();
        
        return $types;
    }

    /**
     * Get user statistics
     */
    public function getUserStats()
    {
        return [
            'total' => $this->where('user_type_id !=', self::ROLE_ADMIN)->countAllResults(false),
            'active' => $this->where('user_type_id !=', self::ROLE_ADMIN)->where('status', 'active')->countAllResults(false),
            'inactive' => $this->where('user_type_id !=', self::ROLE_ADMIN)->where('status', 'inactive')->countAllResults(false),
            'online' => $this->where('user_type_id !=', self::ROLE_ADMIN)->where('is_online', 1)->countAllResults(false)
        ];
    }

    /**
     * Get statistics for a specific user type
     */
    public function getStatsByUserType($typeId)
    {
        $stats = [
            'total' => $this->where('user_type_id', $typeId)->countAllResults(false),
            'active' => $this->where('user_type_id', $typeId)->where('status', 'active')->countAllResults(false),
            'inactive' => $this->where('user_type_id', $typeId)->where('status', 'inactive')->countAllResults(false),
            'online' => $this->where('user_type_id', $typeId)->where('is_online', 1)->countAllResults(false)
        ];

        // Add additional stats for Attendants
        if ($typeId == self::ROLE_ATTENDANT) {
            $stats['assigned'] = $this->where('user_type_id', $typeId)
                                      ->where('assigned_area_id IS NOT NULL', null, false)
                                      ->where('assigned_area_id !=', 0)
                                      ->countAllResults(false);
        }

        return $stats;
    }

    /**
     * Create new user
     */
    public function createUser($data)
    {
        // Hash password if provided
        if (!empty($data['password'])) {
            $data['password'] = password_hash($data['password'], PASSWORD_DEFAULT);
        }

        // Generate external user ID if not provided
        if (empty($data['external_user_id'])) {
            $data['external_user_id'] = 'USR-' . strtoupper(substr(uniqid(), -8));
        }

        // Skip model validation since we validate in the controller (like updateUser method)
        $this->skipValidation(true);
        return $this->insert($data);
    }

    /**
     * Update user
     */
    public function updateUser($userId, $data)
    {
        // Hash password if provided and not empty
        if (!empty($data['password'])) {
            $data['password'] = password_hash($data['password'], PASSWORD_DEFAULT);
        } else {
            unset($data['password']);
        }

        // Skip model validation since we validate in the controller
        $this->skipValidation(true);
        return $this->update($userId, $data);
    }

    /**
     * Delete user
     */
    public function deleteUser($userId)
    {
        return $this->delete($userId);
    }

    /**
     * Get staff with filters and pagination (Admin (3) and Attendant (2) only)
     */
    public function getAttendantsWithFilters($filters = [], $limit = 10, $offset = 0)
    {
        $builder = $this->db->table('users u')
            ->select('u.*, t.account_type_name as user_type_name, pa.parking_area_name')
            ->join('types t', 'u.user_type_id = t.type_id', 'left')
            ->join('parking_area pa', 'u.assigned_area_id = pa.parking_area_id', 'left')
            ->whereIn('u.user_type_id', [self::ROLE_ATTENDANT, self::ROLE_ADMIN]) // Include attendants and admins
            ->orderBy('u.created_at', 'DESC');

        // Apply filters
        $this->applyAttendantFilters($builder, $filters);

        return $builder->limit($limit, $offset)->get()->getResultArray();
    }

    /**
     * Get total staff count with filters (Admin and Attendant)
     */
    public function getAttendantsCount($filters = [])
    {
        $builder = $this->db->table('users u');
        $builder->whereIn('u.user_type_id', [self::ROLE_ATTENDANT, self::ROLE_ADMIN]); // Include attendants and admins

        // Apply filters
        $this->applyAttendantFilters($builder, $filters);

        return $builder->countAllResults();
    }

    /**
     * Apply filters for attendants
     */
    private function applyAttendantFilters($builder, $filters)
    {
        // Search filter
        if (!empty($filters['search'])) {
            $search = $filters['search'];
            $builder->groupStart()
                ->like('u.user_id', $search)
                ->orLike('u.first_name', $search)
                ->orLike('u.last_name', $search)
                ->orLike('u.email', $search)
                ->orLike('u.external_user_id', $search)
                ->groupEnd();
        }

        // User type filter
        if (!empty($filters['user_type_id'])) {
            $builder->where('u.user_type_id', $filters['user_type_id']);
        }

        // Status filter
        if (!empty($filters['status'])) {
            $builder->where('u.status', $filters['status']);
        }

        // Online status filter
        if (isset($filters['is_online']) && $filters['is_online'] !== '') {
            $builder->where('u.is_online', $filters['is_online']);
        }

        // Assigned area filter
        if (!empty($filters['assigned_area_id'])) {
            $builder->where('u.assigned_area_id', $filters['assigned_area_id']);
        }
    }

    /**
     * Get attendant statistics (includes attendants (2) and admins (3))
     */
    public function getAttendantStats()
    {
        $total = $this->whereIn('user_type_id', [self::ROLE_ATTENDANT, self::ROLE_ADMIN])->countAllResults();
        $active = $this->whereIn('user_type_id', [self::ROLE_ATTENDANT, self::ROLE_ADMIN])->where('status', 'active')->countAllResults();
        $online = $this->whereIn('user_type_id', [self::ROLE_ATTENDANT, self::ROLE_ADMIN])->where('is_online', 1)->countAllResults();
        $inactive = $this->whereIn('user_type_id', [self::ROLE_ATTENDANT, self::ROLE_ADMIN])->where('status', 'inactive')->countAllResults();
        $admins = $this->where('user_type_id', self::ROLE_ADMIN)->countAllResults();

        return [
            'total' => $total,
            'active' => $active,
            'online' => $online,
            'inactive' => $inactive,
            'admins' => $admins
        ];
    }

    /**
     * Get staff user types (Admin and Attendant)
     */
    public function getAttendantUserTypes()
    {
        return $this->db->table('types')
            ->select('type_id as user_type_id, account_type_name as user_type_name')
            ->whereIn('type_id', [self::ROLE_ATTENDANT, self::ROLE_ADMIN]) // Include attendant type and admin type
            ->get()
            ->getResultArray();
    }

    /**
     * Get all attendants (no pagination, for export)
     */
    public function getAllAttendants($filters = [])
    {
        $builder = $this->db->table('users u')
            ->select('u.*, t.account_type_name as user_type_name, pa.parking_area_name')
            ->join('types t', 'u.user_type_id = t.type_id', 'left')
            ->join('parking_area pa', 'u.assigned_area_id = pa.parking_area_id', 'left')
            ->whereIn('u.user_type_id', [self::ROLE_ATTENDANT, self::ROLE_ADMIN])
            ->orderBy('u.created_at', 'DESC');

        // Apply filters
        $this->applyAttendantFilters($builder, $filters);

        return $builder->get()->getResultArray();
    }

    /**
     * Get all parking areas
     */
    public function getParkingAreas()
    {
        return $this->db->table('parking_area')
            ->orderBy('parking_area_name', 'ASC')
            ->get()
            ->getResultArray();
    }
}
