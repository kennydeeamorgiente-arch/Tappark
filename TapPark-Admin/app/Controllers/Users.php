<?php

namespace App\Controllers;

use App\Models\UserModel;

class Users extends BaseController
{
    protected $userModel;

    public function __construct()
    {
        $this->userModel = new UserModel();
        helper('activity'); // Load activity helper for logging
    }

    /**
     * Main index - returns the view
     */
    public function index()
    {
        // Check if this is an AJAX request
        if ($this->request->isAJAX()) {
            // Return only the content (for AJAX loading)
            return view('pages/users/index');
        }
        
        // Return full page (for direct URL access, non-AJAX)
        return view('main_layout', [
            'content' => view('pages/users/index')
        ]);
    }

    /**
     * Get users list (AJAX endpoint)
     */
    public function list()
    {
        // Get filters from request
        $filters = [
            'search' => $this->request->getGet('search'),
            'user_type_id' => $this->request->getGet('user_type_id'),
            'status' => $this->request->getGet('status'),
            'is_online' => $this->request->getGet('is_online')
        ];

        // Get pagination params
        $page = $this->request->getGet('page') ?? 1;
        $perPage = $this->request->getGet('per_page') ?? 25;
        $offset = ($page - 1) * $perPage;

        // Get users
        $users = $this->userModel->getUsersWithFilters($filters, $perPage, $offset);
        $totalUsers = $this->userModel->getUsersCount($filters);
        $stats = $this->userModel->getUserStats();

        // Calculate pagination data
        $totalPages = ceil($totalUsers / $perPage);
        $showingFrom = $totalUsers > 0 ? (($page - 1) * $perPage) + 1 : 0;
        $showingTo = min($page * $perPage, $totalUsers);

        return $this->response->setJSON([
            'success' => true,
            'data' => $users,
            'pagination' => [
                'current_page' => (int)$page,
                'per_page' => (int)$perPage,
                'total' => $totalUsers,
                'total_pages' => $totalPages,
                'showing_from' => $showingFrom,
                'showing_to' => $showingTo
            ],
            'stats' => $stats
        ]);
    }

    /**
     * Get single user by ID (AJAX endpoint)
     */
    public function get($userId)
    {
        $user = $this->userModel->getUserById($userId);

        if (!$user) {
            return $this->response->setJSON([
                'success' => false,
                'message' => 'User not found'
            ])->setStatusCode(404);
        }

        return $this->response->setJSON([
            'success' => true,
            'data' => $user
        ]);
    }

    /**
     * Get user types (AJAX endpoint)
     */
    public function getUserTypes()
    {
        $userTypes = $this->userModel->getUserTypes();

        return $this->response->setJSON([
            'success' => true,
            'data' => $userTypes
        ]);
    }

    /**
     * Create new user (AJAX endpoint)
     */
    public function create()
    {
        $data = $this->request->getPost();

        // Default status to active if not provided (since we removed the field from form)
        if (empty($data['status'])) {
            $data['status'] = 'active';
        }
        
        // Validate input
        if (!$this->validate([
            'first_name' => 'required|min_length[2]|max_length[100]',
            'last_name' => 'required|min_length[2]|max_length[100]',
            'email' => 'required|valid_email|is_unique[users.email]',
            'password' => 'required|min_length[8]'
        ])) {
            return $this->response->setJSON([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $this->validator->getErrors()
            ])->setStatusCode(400);
        }

        $userId = $this->userModel->createUser($data);

        if ($userId) {
            // Log activity
            $logResult = log_create('User', $userId, "{$data['first_name']} {$data['last_name']}");
            if ($logResult === false) {
                log_message('error', 'Users::create - Activity log failed for user_id ' . $userId);
            }

            // Get the full user data for dynamic table update
            $newUser = $this->userModel->getUserById($userId);
            
            return $this->response->setJSON([
                'success' => true,
                'message' => 'User created successfully',
                'data' => $newUser  // Return full user data
            ]);
        }

        return $this->response->setJSON([
            'success' => false,
            'message' => 'Failed to create user'
        ])->setStatusCode(500);
    }

    /**
     * Update user (AJAX endpoint)
     */
    public function update($userId)
    {
        $user = $this->userModel->find($userId);

        if (!$user) {
            return $this->response->setJSON([
                'success' => false,
                'message' => 'User not found'
            ])->setStatusCode(404);
        }

        $data = [
            'first_name' => $this->request->getPost('first_name'),
            'last_name' => $this->request->getPost('last_name'),
            'email' => $this->request->getPost('email'),
            'user_type_id' => $this->request->getPost('user_type_id'),
            'status' => $this->request->getPost('status'),
            'hour_balance' => $this->request->getPost('hour_balance')
        ];

        // Only update password if provided
        $password = $this->request->getPost('password');
        if (!empty($password)) {
            $data['password'] = $password;
        }

        // Validate
        $rules = [
            'first_name' => 'required|min_length[2]|max_length[100]',
            'last_name' => 'required|min_length[2]|max_length[100]',
            'email' => "required|valid_email|is_unique[users.email,user_id,{$userId}]"
        ];

        if (!empty($password)) {
            $rules['password'] = 'min_length[8]';
        }

        if (!$this->validate($rules)) {
            return $this->response->setJSON([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $this->validator->getErrors()
            ])->setStatusCode(400);
        }

        if ($this->userModel->updateUser($userId, $data)) {
            // Log activity
            $logResult = log_update('User', $userId, "{$data['first_name']} {$data['last_name']}");
            if ($logResult === false) {
                log_message('error', 'Users::update - Activity log failed for user_id ' . $userId);
            }

            // Get the updated user data for dynamic table update
            $updatedUser = $this->userModel->getUserById($userId);

            return $this->response->setJSON([
                'success' => true,
                'message' => 'User updated successfully',
                'data' => $updatedUser  // Return full user data
            ]);
        }

        return $this->response->setJSON([
            'success' => false,
            'message' => 'Failed to update user'
        ])->setStatusCode(500);
    }

    /**
     * Delete user (AJAX endpoint)
     */
    public function delete($userId)
    {
        $user = $this->userModel->find($userId);

        if (!$user) {
            return $this->response->setJSON([
                'success' => false,
                'message' => 'User not found'
            ])->setStatusCode(404);
        }

        // Safeguard: Do not allow deletion if user has active balance
        if ($user['user_type_id'] == UserModel::ROLE_SUBSCRIBER && floatval($user['hour_balance'] ?? 0) > 0) {
            return $this->response->setJSON([
                'success' => false,
                'message' => 'Cannot delete subscriber with active hour balance. Please exhaust or refund the balance first.'
            ])->setStatusCode(400);
        }

        if ($this->userModel->deleteUser($userId)) {
            // Log activity
            $logResult = log_delete('User', $userId, "{$user['first_name']} {$user['last_name']}");
            if ($logResult === false) {
                log_message('error', 'Users::delete - Activity log failed for user_id ' . $userId);
            }

            return $this->response->setJSON([
                'success' => true,
                'message' => 'User deleted successfully'
            ]);
        }

        return $this->response->setJSON([
            'success' => false,
            'message' => 'Failed to delete user'
        ])->setStatusCode(500);
    }

    /**
     * Export users to CSV based on current filters
     */
    public function export()
    {
        // Get filters from request (same as list)
        $filters = [
            'search' => $this->request->getGet('search'),
            'user_type_id' => $this->request->getGet('user_type_id'),
            'status' => $this->request->getGet('status'),
            'is_online' => $this->request->getGet('is_online')
        ];

        // Get ALL users with filters (no pagination for export)
        $users = $this->userModel->getUsersWithFilters($filters, 999999, 0);
        
        // Generate filename with current date
        $filename = 'Users_Export_' . date('Y-m-d_His') . '.csv';
        
        // Set headers for CSV download
        header('Content-Type: text/csv');
        header('Content-Disposition: attachment; filename="' . $filename . '"');
        header('Pragma: no-cache');
        header('Expires: 0');
        
        // Open output stream
        $output = fopen('php://output', 'w');
        
        // Add BOM for UTF-8 (helps Excel recognize UTF-8)
        fprintf($output, chr(0xEF).chr(0xBB).chr(0xBF));
        
        // Add CSV headers
        fputcsv($output, [
            'ID',
            'First Name',
            'Last Name',
            'Email',
            'User Type',
            'Hour Balance',
            'Status',
            'Online Status',
            'Created At'
        ]);
        
        // Add data rows
        foreach ($users as $user) {
            // Format created_at safely
            $createdAt = '';
            if (!empty($user['created_at'])) {
                $timestamp = strtotime($user['created_at']);
                if ($timestamp !== false) {
                    $createdAt = date('Y-m-d H:i:s', $timestamp);
                }
            }
            
            fputcsv($output, [
                $user['user_id'] ?? $user['id'] ?? '',
                $user['first_name'] ?? '',
                $user['last_name'] ?? '',
                $user['email'] ?? '',
                $user['user_type_name'] ?? $user['type_name'] ?? 'N/A',
                $user['hour_balance'] ?? 0,
                ucfirst($user['status'] ?? 'N/A'),
                (!empty($user['is_online']) && $user['is_online']) ? 'Online' : 'Offline',
                $createdAt
            ]);
        }
        
        fclose($output);
        exit;
    }

    /**
     * ============================================================================
     * WALK-IN GUESTS MANAGEMENT METHODS
     * ============================================================================
     */

    /**
     * Get walk-in guests (guest bookings) list (AJAX endpoint)
     */
    public function getWalkInGuests()
    {
        try {
            // Get filters from request
            $filters = [
                'search' => $this->request->getGet('search'),
                'attendant_id' => $this->request->getGet('attendant_id'),
                'vehicle_type' => $this->request->getGet('vehicle_type'),
                'date_range' => $this->request->getGet('date_range')
            ];
            
            // Get pagination params
            $page = $this->request->getGet('page') ?? 1;
            $perPage = $this->request->getGet('per_page') ?? 25;
            $sortColumn = $this->request->getGet('sort_column') ?? 'guest_booking_id';
            $sortDirection = $this->request->getGet('sort_direction') ?? 'desc';
            
            // Build the query with JOINs to get real names and details
            $db = \Config\Database::connect();
        
            $builder = $db->table('guest_bookings gb')
                ->select('
                    gb.guest_booking_id,
                    gb.guest_user_id,
                    gb.vehicle_id,
                    gb.reservation_id,
                    gb.attendant_id,
                    gb.created_at,
                    gu.first_name as guest_first_name,
                    gu.last_name as guest_last_name,
                    gu.email as guest_email,
                    v.vehicle_type,
                    v.brand as vehicle_brand,
                    v.color as vehicle_color,
                    v.plate_number,
                    u.first_name as attendant_first_name,
                    u.last_name as attendant_last_name,
                    t.account_type_name as attendant_role,
                    r.booking_status as reservation_status
                ')
                ->join('users gu', 'gu.user_id = gb.guest_user_id', 'left')
                ->join('vehicles v', 'v.vehicle_id = gb.vehicle_id', 'left')
                ->join('users u', 'u.user_id = gb.attendant_id', 'left')
                ->join('types t', 't.type_id = u.user_type_id', 'left')
                ->join('reservations r', 'r.reservation_id = gb.reservation_id', 'left');
        
            // Apply filters
            if (!empty($filters['search'])) {
                $builder->groupStart()
                    ->like('gu.first_name', $filters['search'])
                    ->orLike('gu.last_name', $filters['search'])
                    ->orLike('CONCAT(gu.first_name, " ", gu.last_name)', $filters['search'])
                    ->orLike('v.plate_number', $filters['search'])
                    ->groupEnd();
            }
            
            if (!empty($filters['attendant_id'])) {
                $builder->where('gb.attendant_id', $filters['attendant_id']);
            }
            
            if (!empty($filters['vehicle_type'])) {
                $builder->where('v.vehicle_type', $filters['vehicle_type']);
            }
            
            if (!empty($filters['date_range'])) {
                $builder->where($this->getDateRangeCondition($filters['date_range'], 'gb.created_at'));
            }
            
            // Apply sorting
            $allowedSortColumns = ['guest_booking_id', 'guest_name', 'vehicle_type', 'attendant_name', 'created_at'];
            if (in_array($sortColumn, $allowedSortColumns)) {
                if ($sortColumn === 'guest_name') {
                    $builder->orderBy('gu.first_name', $sortDirection)
                             ->orderBy('gu.last_name', $sortDirection);
                } elseif ($sortColumn === 'attendant_name') {
                    $builder->orderBy('u.first_name', $sortDirection)
                             ->orderBy('u.last_name', $sortDirection);
                } else {
                    $builder->orderBy($sortColumn, $sortDirection);
                }
            } else {
                $builder->orderBy('gb.guest_booking_id', 'desc');
            }
            
            // Get total count for pagination
            $total = $builder->countAllResults(false);
            
            // Apply pagination
            $offset = ($page - 1) * $perPage;
            $builder->limit($perPage, $offset);
            
            // Get results
            $bookings = $builder->get()->getResultArray();
            
            // Format the data
            $formattedBookings = [];
            foreach ($bookings as $booking) {
                $formattedBookings[] = [
                    'guest_booking_id' => $booking['guest_booking_id'],
                    'guest_name' => trim($booking['guest_first_name'] . ' ' . $booking['guest_last_name']),
                    'guest_email' => $booking['guest_email'],
                    'vehicle_type' => $booking['vehicle_type'],
                    'vehicle_brand' => $booking['vehicle_brand'],
                    'vehicle_color' => $booking['vehicle_color'],
                    'plate_number' => $booking['plate_number'],
                    'attendant_name' => trim($booking['attendant_first_name'] . ' ' . $booking['attendant_last_name']),
                    'attendant_role' => $booking['attendant_role'],
                    'reservation_id' => $booking['reservation_id'],
                    'reservation_status' => $booking['reservation_status'],
                    'created_at' => $booking['created_at']
                ];
            }
            
            // Get stats for walk-in guests
            $today = date('Y-m-d');
            $stats = [
                'total' => $db->table('guest_bookings')->countAllResults(),
                'today' => $db->table('guest_bookings')
                    ->where('DATE(created_at)', $today)
                    ->countAllResults(),
                'parked' => $db->table('guest_bookings gb')
                    ->join('reservations r', 'r.reservation_id = gb.reservation_id', 'left')
                    ->whereIn('r.booking_status', ['confirmed', 'active'])
                    ->countAllResults(),
                'completed' => $db->table('guest_bookings gb')
                    ->join('reservations r', 'r.reservation_id = gb.reservation_id', 'left')
                    ->whereIn('r.booking_status', ['completed', 'cancelled'])
                    ->countAllResults()
            ];

            // Return paginated response
            return $this->response->setJSON([
                'success' => true,
                'data' => [
                    'data' => $formattedBookings,
                    'current_page' => (int)$page,
                    'per_page' => (int)$perPage,
                    'total' => (int)$total,
                    'from' => $total > 0 ? $offset + 1 : 0,
                    'to' => min($offset + $perPage, $total)
                ],
                'stats' => $stats
            ]);
            
        } catch (\Exception $e) {
            log_message('error', 'Error in getWalkInGuests: ' . $e->getMessage());
            return $this->response->setJSON([
                'success' => false,
                'message' => 'An error occurred while loading walk-in guests: ' . $e->getMessage()
            ])->setStatusCode(500);
        }
    }
    
    /**
     * Get attendants list for filter dropdown
     */
    public function getAttendantsList()
    {
        try {
            $db = \Config\Database::connect();
            
            $attendants = $db->table('users u')
                ->select('u.user_id, u.first_name, u.last_name, t.account_type_name')
                ->join('types t', 't.type_id = u.user_type_id', 'left')
                ->whereIn('u.user_type_id', [UserModel::ROLE_ATTENDANT, UserModel::ROLE_ADMIN]) // Attendant and Admin
                ->orderBy('u.first_name', 'asc')
                ->get()
                ->getResultArray();
            
            $formattedAttendants = [];
            foreach ($attendants as $attendant) {
                $formattedAttendants[] = [
                    'user_id' => $attendant['user_id'],
                    'name' => trim($attendant['first_name'] . ' ' . $attendant['last_name']),
                    'role' => $attendant['account_type_name']
                ];
            }
            
            return $this->response->setJSON([
                'success' => true,
                'data' => $formattedAttendants
            ]);
            
        } catch (\Exception $e) {
            log_message('error', 'Error in getAttendantsList: ' . $e->getMessage());
            return $this->response->setJSON([
                'success' => false,
                'message' => 'An error occurred: ' . $e->getMessage()
            ])->setStatusCode(500);
        }
    }
    
    /**
     * Get individual walk-in guest booking details
     */
    public function getWalkInGuestDetails($bookingId)
    {
        try {
            $db = \Config\Database::connect();
            
            $booking = $db->table('guest_bookings gb')
                ->select('
                    gb.guest_booking_id,
                    gb.guest_user_id,
                    gb.vehicle_id,
                    gb.reservation_id,
                    gb.attendant_id,
                    gb.created_at,
                    gu.first_name as guest_first_name,
                    gu.last_name as guest_last_name,
                    gu.email as guest_email,
                    v.vehicle_type,
                    v.brand as vehicle_brand,
                    v.color as vehicle_color,
                    v.plate_number,
                    u.first_name as attendant_first_name,
                    u.last_name as attendant_last_name,
                    t.account_type_name as attendant_role,
                    r.booking_status as reservation_status,
                    r.start_time,
                    r.end_time,
                    r.QR as qr_code
                ')
                ->join('users gu', 'gu.user_id = gb.guest_user_id', 'left')
                ->join('vehicles v', 'v.vehicle_id = gb.vehicle_id', 'left')
                ->join('users u', 'u.user_id = gb.attendant_id', 'left')
                ->join('types t', 't.type_id = u.user_type_id', 'left')
                ->join('reservations r', 'r.reservation_id = gb.reservation_id', 'left')
                ->where('gb.guest_booking_id', $bookingId)
                ->get()
                ->getRowArray();
            
            if (!$booking) {
                return $this->response->setJSON([
                    'success' => false,
                    'message' => 'Walk-in guest booking not found'
                ])->setStatusCode(404);
            }
            
            // Format the data
            $formattedBooking = [
                'guest_booking_id' => $booking['guest_booking_id'],
                'guest_name' => trim($booking['guest_first_name'] . ' ' . $booking['guest_last_name']),
                'guest_email' => $booking['guest_email'],
                'vehicle_type' => $booking['vehicle_type'],
                'vehicle_brand' => $booking['vehicle_brand'],
                'vehicle_color' => $booking['vehicle_color'],
                'plate_number' => $booking['plate_number'],
                'attendant_name' => trim($booking['attendant_first_name'] . ' ' . $booking['attendant_last_name']),
                'attendant_role' => $booking['attendant_role'],
                'reservation_id' => $booking['reservation_id'],
                'reservation_status' => $booking['reservation_status'],
                'start_time' => $booking['start_time'] ?? null,
                'end_time' => $booking['end_time'] ?? null,
                'qr_code' => $booking['qr_code'] ?? null,
                'created_at' => $booking['created_at']
            ];
            
            return $this->response->setJSON([
                'success' => true,
                'data' => $formattedBooking
            ]);
            
        } catch (\Exception $e) {
            log_message('error', 'Error in getWalkInGuestDetails: ' . $e->getMessage());
            return $this->response->setJSON([
                'success' => false,
                'message' => 'An error occurred: ' . $e->getMessage()
            ])->setStatusCode(500);
        }
    }
    
    /**
     * Get vehicle types for filter dropdown
     */
    public function getVehicleTypes()
    {
        try {
            $db = \Config\Database::connect();
            
            // Get distinct vehicle types from vehicles table
            $types = $db->table('vehicles')
                ->select('vehicle_type')
                ->distinct()
                ->where('vehicle_type IS NOT NULL')
                ->where('vehicle_type !=', '')
                ->orderBy('vehicle_type', 'ASC')
                ->get()
                ->getResultArray();
            
            // Format for dropdown
            $formattedTypes = [];
            foreach ($types as $type) {
                if (!empty($type['vehicle_type'])) {
                    $formattedTypes[] = [
                        'value' => $type['vehicle_type'],
                        'label' => ucfirst($type['vehicle_type'])
                    ];
                }
            }
            
            return $this->response->setJSON([
                'success' => true,
                'data' => $formattedTypes
            ]);
            
        } catch (\Exception $e) {
            log_message('error', 'Error in getVehicleTypes: ' . $e->getMessage());
            return $this->response->setJSON([
                'success' => false,
                'message' => 'An error occurred: ' . $e->getMessage()
            ])->setStatusCode(500);
        }
    }
    
    /**
     * Export walk-in guests to CSV
     */
    public function exportWalkInGuests()
    {
        // Get filters from request
        $filters = [
            'search' => $this->request->getGet('search'),
            'attendant_id' => $this->request->getGet('attendant_id'),
            'vehicle_type' => $this->request->getGet('vehicle_type'),
            'date_range' => $this->request->getGet('date_range')
        ];
        
        $db = \Config\Database::connect();
        
        $builder = $db->table('guest_bookings gb')
            ->select('
                gb.guest_booking_id,
                gu.first_name as guest_first_name,
                gu.last_name as guest_last_name,
                gu.email as guest_email,
                v.vehicle_type,
                v.brand as vehicle_brand,
                v.color as vehicle_color,
                v.plate_number,
                u.first_name as attendant_first_name,
                u.last_name as attendant_last_name,
                t.account_type_name as attendant_role,
                r.reservation_id,
                r.booking_status as reservation_status,
                gb.created_at
            ')
            ->join('users gu', 'gu.user_id = gb.guest_user_id', 'left')
            ->join('vehicles v', 'v.vehicle_id = gb.vehicle_id', 'left')
            ->join('users u', 'u.user_id = gb.attendant_id', 'left')
            ->join('types t', 't.type_id = u.user_type_id', 'left')
            ->join('reservations r', 'r.reservation_id = gb.reservation_id', 'left');
        
        // Apply same filters as getWalkInGuests
        if (!empty($filters['search'])) {
            $builder->groupStart()
                ->like('gu.first_name', $filters['search'])
                ->orLike('gu.last_name', $filters['search'])
                ->orLike('CONCAT(gu.first_name, " ", gu.last_name)', $filters['search'])
                ->orLike('v.plate_number', $filters['search'])
                ->groupEnd();
        }
        
        if (!empty($filters['attendant_id'])) {
            $builder->where('gb.attendant_id', $filters['attendant_id']);
        }
        
        if (!empty($filters['vehicle_type'])) {
            $builder->where('v.vehicle_type', $filters['vehicle_type']);
        }
        
        if (!empty($filters['date_range'])) {
            $builder->where($this->getDateRangeCondition($filters['date_range'], 'gb.created_at'));
        }
        
        $builder->orderBy('gb.guest_booking_id', 'desc');
        $bookings = $builder->get()->getResultArray();
        
        // Set headers for CSV download
        header('Content-Type: text/csv');
        header('Content-Disposition: attachment; filename="walk_in_guests_' . date('Y-m-d') . '.csv"');
        
        $output = fopen('php://output', 'w');
        
        // Add BOM for UTF-8
        fprintf($output, chr(0xEF).chr(0xBB).chr(0xBF));
        
        // CSV headers
        fputcsv($output, [
            'Booking ID',
            'Guest Name',
            'Guest Email',
            'Vehicle Type',
            'Vehicle Brand',
            'Vehicle Color',
            'Plate Number',
            'Attendant Name',
            'Attendant Role',
            'Reservation ID',
            'Reservation Status',
            'Created At'
        ]);
        
        // CSV data
        foreach ($bookings as $booking) {
            fputcsv($output, [
                $booking['guest_booking_id'] ?? '',
                trim($booking['guest_first_name'] . ' ' . $booking['guest_last_name']),
                $booking['guest_email'] ?? '',
                $booking['vehicle_type'] ?? '',
                $booking['vehicle_brand'] ?? '',
                $booking['vehicle_color'] ?? '',
                $booking['plate_number'] ?? '',
                trim($booking['attendant_first_name'] . ' ' . $booking['attendant_last_name']),
                $booking['attendant_role'] ?? '',
                $booking['reservation_id'] ?? '',
                $booking['reservation_status'] ?? '',
                $booking['created_at'] ?? ''
            ]);
        }
        
        fclose($output);
        exit;
    }
    
    /**
     * Helper function to get date range condition
     */
    private function getDateRangeCondition($range, $field)
    {
        switch ($range) {
            case 'today':
                return "DATE($field) = CURDATE()";
            case 'yesterday':
                return "DATE($field) = DATE_SUB(CURDATE(), INTERVAL 1 DAY)";
            case 'week':
                return "YEAR($field) = YEAR(CURDATE()) AND WEEK($field) = WEEK(CURDATE())";
            case 'month':
                return "YEAR($field) = YEAR(CURDATE()) AND MONTH($field) = MONTH(CURDATE())";
            case 'last30':
                return "$field >= DATE_SUB(CURDATE(), INTERVAL 30 DAY)";
            case 'last90':
                return "$field >= DATE_SUB(CURDATE(), INTERVAL 90 DAY)";
            default:
                return "1=1";
        }
    }
    
    // ====================================
    // STAFF MANAGEMENT METHODS
    // ====================================
    
    /**
     * Get staff list (admins and attendants) - AJAX endpoint
     */
    public function getStaffList()
    {
        // Get filters from request
        $filters = [
            'search' => $this->request->getGet('search'),
            'user_type_id' => $this->request->getGet('user_type_id'),
            'status' => $this->request->getGet('status'),
            'is_online' => $this->request->getGet('is_online'),
            'assigned_area_id' => $this->request->getGet('assigned_area_id'),
            'attendant_only' => true // Special flag to only show admin/attendant
        ];

        // Get pagination params
        $page = $this->request->getGet('page') ?? 1;
        $perPage = $this->request->getGet('per_page') ?? 25;
        $offset = ($page - 1) * $perPage;

        // Get sort parameters
        $sortColumn = $this->request->getGet('sort_column') ?? 'user_id';
        $sortDirection = $this->request->getGet('sort_direction') ?? 'DESC';

        // Get staff members
        $staff = $this->userModel->getAttendantsWithFilters($filters, $perPage, $offset, $sortColumn, $sortDirection);
        $totalStaff = $this->userModel->getAttendantsCount($filters);

        // Calculate pagination info
        $totalPages = ceil($totalStaff / $perPage);
        $showingFrom = $totalStaff > 0 ? (($page - 1) * $perPage) + 1 : 0;
        $showingTo = min($page * $perPage, $totalStaff);

        // Get staff stats
        $stats = $this->userModel->getAttendantStats();

        return $this->response->setJSON([
            'success' => true,
            'data' => [
                'data' => $staff,
                'current_page' => (int)$page,
                'per_page' => (int)$perPage,
                'total' => $totalStaff,
                'from' => $showingFrom,
                'to' => $showingTo
            ],
            'stats' => $stats
        ]);
    }

    /**
     * Get staff user types (roles) for filter dropdown
     */
    public function getStaffUserTypes()
    {
        try {
            $types = $this->userModel->getAttendantUserTypes();
            
            return $this->response->setJSON([
                'success' => true,
                'data' => $types
            ]);
            
        } catch (\Exception $e) {
            log_message('error', 'Error in getStaffUserTypes: ' . $e->getMessage());
            return $this->response->setJSON([
                'success' => false,
                'message' => 'An error occurred: ' . $e->getMessage()
            ])->setStatusCode(500);
        }
    }

    /**
     * Get parking areas for filter dropdown
     */
    public function getParkingAreas()
    {
        try {
            $areas = $this->userModel->getParkingAreas();
            
            return $this->response->setJSON([
                'success' => true,
                'data' => $areas
            ]);
            
        } catch (\Exception $e) {
            log_message('error', 'Error in getParkingAreas: ' . $e->getMessage());
            return $this->response->setJSON([
                'success' => false,
                'message' => 'An error occurred: ' . $e->getMessage()
            ])->setStatusCode(500);
        }
    }

    /**
     * Get single staff member details - AJAX endpoint
     */
    public function getStaffDetails($userId)
    {
        try {
            $user = $this->userModel->getUserById($userId);

            if (!$user) {
                return $this->response->setJSON([
                    'success' => false,
                    'message' => 'Staff member not found'
                ])->setStatusCode(404);
            }

            // Verify user is staff (admin = 3, attendants = 2)
            if (!in_array($user['user_type_id'], [2, 3])) {
                return $this->response->setJSON([
                    'success' => false,
                    'message' => 'User is not a staff member'
                ])->setStatusCode(403);
            }

            return $this->response->setJSON([
                'success' => true,
                'data' => $user
            ]);
        } catch (\Exception $e) {
            log_message('error', 'Error fetching staff details: ' . $e->getMessage());
            return $this->response->setJSON([
                'success' => false,
                'message' => 'Error loading staff details'
            ])->setStatusCode(500);
        }
    }

    /**
     * Export staff to CSV
     */
    public function exportStaff()
    {
        // Get filters from request
        $filters = [
            'search' => $this->request->getGet('search'),
            'user_type_id' => $this->request->getGet('user_type_id'),
            'status' => $this->request->getGet('status'),
            'is_online' => $this->request->getGet('is_online'),
            'assigned_area_id' => $this->request->getGet('assigned_area_id'),
            'attendant_only' => true
        ];

        $staff = $this->userModel->getAllAttendants($filters);

        // Generate CSV
        $filename = 'staff_export_' . date('Y-m-d_H-i-s') . '.csv';
        
        header('Content-Type: text/csv');
        header('Content-Disposition: attachment; filename="' . $filename . '"');
        header('Pragma: no-cache');
        header('Expires: 0');
        
        $output = fopen('php://output', 'w');
        
        // Add BOM for UTF-8 (helps Excel recognize UTF-8)
        fprintf($output, chr(0xEF).chr(0xBB).chr(0xBF));
        
        // CSV Headers
        fputcsv($output, [
            'User ID',
            'External ID',
            'First Name',
            'Last Name',
            'Email',
            'Role',
            'Assigned Area',
            'Status',
            'Online Status',
            'Created At'
        ]);
        
        // CSV Data
        foreach ($staff as $member) {
            // Format created_at safely
            $createdAt = '';
            if (!empty($member['created_at'])) {
                $timestamp = strtotime($member['created_at']);
                if ($timestamp !== false) {
                    $createdAt = date('Y-m-d H:i:s', $timestamp);
                } else {
                    $createdAt = $member['created_at'];
                }
            }
            
            fputcsv($output, [
                $member['user_id'] ?? '',
                $member['external_user_id'] ?? '',
                $member['first_name'] ?? '',
                $member['last_name'] ?? '',
                $member['email'] ?? '',
                $member['account_type_name'] ?? $member['user_type_name'] ?? 'N/A',
                $member['parking_area_name'] ?? 'Not Assigned',
                ucfirst($member['status'] ?? 'N/A'),
                (!empty($member['is_online']) && $member['is_online']) ? 'Online' : 'Offline',
                $createdAt
            ]);
        }
        
        fclose($output);
        exit;
    }
}



