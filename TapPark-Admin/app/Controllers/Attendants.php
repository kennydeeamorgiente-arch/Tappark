<?php

namespace App\Controllers;

use App\Models\UserModel;

class Attendants extends BaseController
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
            return view('pages/attendants/index');
        }
        
        // Return full page (for direct URL access, non-AJAX)
        $data = [
            'content' => view('pages/attendants/index')
        ];
        return view('main_layout', $data);
    }

    /**
     * Get staff list (AJAX endpoint)
     * Shows both Attendants (type_id = 2) and Admins (type_id = 3)
     */
    public function list()
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

        // Get attendants
        $attendants = $this->userModel->getAttendantsWithFilters($filters, $perPage, $offset);
        $totalAttendants = $this->userModel->getAttendantsCount($filters);
        $stats = $this->userModel->getAttendantStats();

        // Calculate pagination info
        $totalPages = ceil($totalAttendants / $perPage);
        $showingFrom = $totalAttendants > 0 ? (($page - 1) * $perPage) + 1 : 0;
        $showingTo = min($page * $perPage, $totalAttendants);

        return $this->response->setJSON([
            'success' => true,
            'data' => $attendants,
            'pagination' => [
                'current_page' => (int)$page,
                'per_page' => (int)$perPage,
                'total' => $totalAttendants,
                'total_pages' => $totalPages,
                'showing_from' => $showingFrom,
                'showing_to' => $showingTo
            ],
            'stats' => $stats
        ]);
    }

    /**
     * Get single attendant details (AJAX endpoint)
     */
    public function get($userId)
    {
        $user = $this->userModel->getUserById($userId);

        if (!$user) {
            return $this->response->setJSON([
                'success' => false,
                'message' => 'Attendant not found'
            ])->setStatusCode(404);
        }

        // Verify user is attendant (admin = 3, attendants = 2)
        if (!in_array($user['user_type_id'], [UserModel::ROLE_ATTENDANT, UserModel::ROLE_ADMIN])) {
            return $this->response->setJSON([
                'success' => false,
                'message' => 'User is not an admin or attendant'
            ])->setStatusCode(403);
        }

        return $this->response->setJSON([
            'success' => true,
            'data' => $user
        ]);
    }

    /**
     * Get user types (admin and attendant only)
     */
    public function getUserTypes()
    {
        $types = $this->userModel->getAttendantUserTypes();

        return $this->response->setJSON([
            'success' => true,
            'data' => $types
        ]);
    }

    /**
     * Get parking areas (for dropdown)
     */
    public function getParkingAreas()
    {
        $areas = $this->userModel->db->table('parking_area')
            ->select('parking_area_id, parking_area_name')
            ->orderBy('parking_area_name', 'ASC')
            ->get()
            ->getResultArray();

        return $this->response->setJSON([
            'success' => true,
            'data' => $areas
        ]);
    }

    /**
     * Create new attendant (AJAX endpoint)
     */
    public function create()
    {
        $data = [
            'first_name' => $this->request->getPost('first_name'),
            'last_name' => $this->request->getPost('last_name'),
            'email' => $this->request->getPost('email'),
            'password' => $this->request->getPost('password'),
            'user_type_id' => $this->request->getPost('user_type_id'),
            'assigned_area_id' => $this->request->getPost('assigned_area_id') ?: null,
            'status' => $this->request->getPost('status'),
            'hour_balance' => 0
        ];

        // Validate
        if (!$this->validate([
            'first_name' => 'required|min_length[2]|max_length[100]',
            'last_name' => 'required|min_length[2]|max_length[100]',
            'email' => 'required|valid_email|is_unique[users.email]',
            'password' => 'required|min_length[6]',
            'user_type_id' => 'required|in_list[' . UserModel::ROLE_ATTENDANT . ',' . UserModel::ROLE_ADMIN . ']', // Only attendant and admin types
            'status' => 'required|in_list[active,inactive]'
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
            $typeName = 'Attendant';
            log_create($typeName, $userId, "{$data['first_name']} {$data['last_name']}");

            // Get the full attendant data for dynamic table update
            $newAttendant = $this->userModel->getAttendantById($userId);

            return $this->response->setJSON([
                'success' => true,
                'message' => 'Attendant created successfully',
                'data' => $newAttendant  // Return full attendant data
            ]);
        }

        return $this->response->setJSON([
            'success' => false,
            'message' => 'Failed to create attendant'
        ])->setStatusCode(500);
    }

    /**
     * Update attendant (AJAX endpoint)
     */
    public function update($userId)
    {
        $user = $this->userModel->find($userId);

        if (!$user) {
            return $this->response->setJSON([
                'success' => false,
                'message' => 'Attendant not found'
            ])->setStatusCode(404);
        }

        // Verify user is attendant (admin = 3, attendants = 2)
        if (!in_array($user['user_type_id'], [UserModel::ROLE_ATTENDANT, UserModel::ROLE_ADMIN])) {
            return $this->response->setJSON([
                'success' => false,
                'message' => 'User is not an admin or attendant'
            ])->setStatusCode(403);
        }

        $data = [
            'first_name' => $this->request->getPost('first_name'),
            'last_name' => $this->request->getPost('last_name'),
            'email' => $this->request->getPost('email'),
            'user_type_id' => $this->request->getPost('user_type_id'),
            'assigned_area_id' => $this->request->getPost('assigned_area_id') ?: null,
            'status' => $this->request->getPost('status')
        ];

        $password = $this->request->getPost('password');
        if (!empty($password)) {
            $data['password'] = $password;
        }

        // Validate
        $rules = [
            'first_name' => 'required|min_length[2]|max_length[100]',
            'last_name' => 'required|min_length[2]|max_length[100]',
            'email' => "required|valid_email|is_unique[users.email,user_id,{$userId}]",
            'user_type_id' => 'required|in_list[' . UserModel::ROLE_ATTENDANT . ',' . UserModel::ROLE_ADMIN . ']',
            'status' => 'required|in_list[active,inactive,suspended]'
        ];

        if (!empty($password)) {
            $rules['password'] = 'min_length[6]';
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
            $typeName = 'Attendant';
            log_update($typeName, $userId, "{$data['first_name']} {$data['last_name']}");

            // Get the updated attendant data for dynamic table update
            $updatedAttendant = $this->userModel->getAttendantById($userId);

            return $this->response->setJSON([
                'success' => true,
                'message' => 'Attendant updated successfully',
                'data' => $updatedAttendant  // Return full attendant data
            ]);
        }

        return $this->response->setJSON([
            'success' => false,
            'message' => 'Failed to update attendant',
            'errors' => $this->userModel->errors()
        ])->setStatusCode(500);
    }

    /**
     * Delete attendant (AJAX endpoint)
     */
    public function delete($userId)
    {
        $user = $this->userModel->find($userId);

        if (!$user) {
            return $this->response->setJSON([
                'success' => false,
                'message' => 'Attendant not found'
            ])->setStatusCode(404);
        }

        // Verify user is attendant (admin = 3, attendants = 2)
        if (!in_array($user['user_type_id'], [UserModel::ROLE_ATTENDANT, UserModel::ROLE_ADMIN])) {
            return $this->response->setJSON([
                'success' => false,
                'message' => 'User is not an admin or attendant'
            ])->setStatusCode(403);
        }

        if ($this->userModel->deleteUser($userId)) {
            // Log activity
            $typeName = 'Attendant';
            log_delete($typeName, $userId, "{$user['first_name']} {$user['last_name']}");

            return $this->response->setJSON([
                'success' => true,
                'message' => 'Attendant deleted successfully'
            ]);
        }

        return $this->response->setJSON([
            'success' => false,
            'message' => 'Failed to delete attendant'
        ])->setStatusCode(500);
    }

    /**
     * Export attendants to CSV
     */
    public function export()
    {
        // Get filters from request
        $filters = [
            'search' => $this->request->getGet('search'),
            'user_type_id' => $this->request->getGet('user_type_id'),
            'status' => $this->request->getGet('status'),
            'is_online' => $this->request->getGet('is_online'),
            'attendant_only' => true
        ];

        $attendants = $this->userModel->getAllAttendants($filters);

        // Generate CSV
        $filename = 'attendants_export_' . date('Y-m-d_H-i-s') . '.csv';
        
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
            'User Type',
            'Assigned Area',
            'Status',
            'Online Status',
            'Created At'
        ]);
        
        // CSV Data
        foreach ($attendants as $attendant) {
            // Format created_at safely
            $createdAt = '';
            if (!empty($attendant['created_at'])) {
                $timestamp = strtotime($attendant['created_at']);
                if ($timestamp !== false) {
                    $createdAt = date('Y-m-d H:i:s', $timestamp);
                } else {
                    $createdAt = $attendant['created_at']; // Use original if can't parse
                }
            }
            
            fputcsv($output, [
                $attendant['user_id'] ?? '',
                $attendant['external_user_id'] ?? '',
                $attendant['first_name'] ?? '',
                $attendant['last_name'] ?? '',
                $attendant['email'] ?? '',
                $attendant['account_type_name'] ?? $attendant['user_type_name'] ?? 'N/A',
                $attendant['parking_area_name'] ?? 'Not Assigned',
                ucfirst($attendant['status'] ?? 'N/A'),
                (!empty($attendant['is_online']) && $attendant['is_online']) ? 'Online' : 'Offline',
                $createdAt
            ]);
        }
        
        fclose($output);
        exit;
    }
    
    /**
     * Get guest bookings list (AJAX endpoint)
     */
    public function getGuestBookings()
    {
        try {
            // Get filters from request
            $filters = [
                'guest_name' => $this->request->getGet('guest_name'),
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
            ]
        ]);
        
        } catch (\Exception $e) {
            log_message('error', 'Error in getGuestBookings: ' . $e->getMessage());
            return $this->response->setJSON([
                'success' => false,
                'message' => 'An error occurred while loading guest bookings: ' . $e->getMessage()
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
                ->select('u.user_id, u.first_name, u.last_name')
                ->join('types t', 't.type_id = u.user_type_id')
                ->whereIn('t.type_id', [UserModel::ROLE_ATTENDANT, UserModel::ROLE_ADMIN]) // Attendants and Admins
                ->where('u.status', 'active')
                ->orderBy('u.first_name', 'asc')
                ->orderBy('u.last_name', 'asc')
                ->get()
                ->getResultArray();
            
            $formattedAttendants = [];
            foreach ($attendants as $attendant) {
                $formattedAttendants[] = [
                    'user_id' => $attendant['user_id'],
                    'name' => trim($attendant['first_name'] . ' ' . $attendant['last_name'])
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
                'message' => 'An error occurred while loading attendants: ' . $e->getMessage()
            ])->setStatusCode(500);
        }
    }
    
    /**
     * Export guest bookings to CSV
     */
    public function exportGuestBookings()
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
        
        // Apply same filters as getGuestBookings
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
        header('Content-Disposition: attachment; filename="guest_bookings_' . date('Y-m-d') . '.csv"');
        
        $output = fopen('php://output', 'w');
        
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
     * Get individual guest booking details for modal
     */
    public function getGuestBookingDetails($bookingId)
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
                    r.booking_status as reservation_status
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
                    'message' => 'Guest booking not found'
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
                'created_at' => $booking['created_at']
            ];
            
            return $this->response->setJSON([
                'success' => true,
                'data' => $formattedBooking
            ]);
            
        } catch (\Exception $e) {
            log_message('error', 'Error in getGuestBookingDetails: ' . $e->getMessage());
            return $this->response->setJSON([
                'success' => false,
                'message' => 'An error occurred: ' . $e->getMessage()
            ])->setStatusCode(500);
        }
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
}

