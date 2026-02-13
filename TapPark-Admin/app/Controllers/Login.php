<?php

namespace App\Controllers;

use App\Models\UserModel;

class Login extends BaseController
{
    protected $userModel;
    protected $session;

    public function __construct()
    {
        // Initialize UserModel and Session
        $this->userModel = new UserModel();
        $this->session = \Config\Services::session();
        helper('form');
    }

    /**
     * Display login page
     * 
     * This method returns the login view.
     * If user is already logged in, redirects to dashboard.
     * 
     * @return mixed - HTML view or redirect
     */
    public function index()
    {
        // Check if user is already logged in
        if ($this->session->get('user_id')) {
            return redirect()->to('/dashboard');
        }

        // Return the login view
        return view('login');
    }

    /**
     * Process login form submission
     * 
     * Handles:
     * 1. Form validation (email format, required fields)
     * 2. User authentication (verify email and password)
     * 3. Check if user is admin
     * 4. Create session with user data
     * 5. Return JSON response for AJAX
     * 
     * @return mixed - JSON response
     */
    public function process()
    {
        // Only allow POST requests
        if (!$this->request->isAJAX()) {
            return $this->response->setJSON([
                'success' => false,
                'message' => 'Invalid request method'
            ])->setStatusCode(400);
        }

        // Rate limiting check
        $email = $this->request->getPost('email');
        $rateLimitKey = 'login_attempt_' . md5($email);
        $lockoutKey = 'login_lockout_' . md5($email);
        $attemptCount = cache()->get($rateLimitKey) ?? 0;
        $lockoutUntil = cache()->get($lockoutKey);

        // Check if currently locked out
        if ($lockoutUntil && time() < $lockoutUntil) {
            $remaining = (int) ceil(($lockoutUntil - time()) / 60);
            return $this->response->setJSON([
                'success' => false,
                'message' => "Too many login attempts. Please try again in {$remaining} minute(s)."
            ])->setStatusCode(429);
        }

        // If lockout has expired, clear both keys and reset count
        if ($lockoutUntil && time() >= $lockoutUntil) {
            cache()->delete($rateLimitKey);
            cache()->delete($lockoutKey);
            $attemptCount = 0;
        }

        // Get validation service
        $validation = \Config\Services::validation();
        
        // Set validation rules
        $validation->setRules([
            'email' => 'required|valid_email',
            'password' => 'required|min_length[6]'
        ], [
            'email' => [
                'required' => 'Email is required',
                'valid_email' => 'Please enter a valid email address'
            ],
            'password' => [
                'required' => 'Password is required',
                'min_length' => 'Password must be at least 6 characters long'
            ]
        ]);

        // Run validation
        if (!$validation->withRequest($this->request)->run()) {
            return $this->response->setJSON([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $validation->getErrors()
            ])->setStatusCode(422);
        }

        // Get validated data
        $email = $this->request->getPost('email');
        $password = $this->request->getPost('password');

        // First, check if user exists (regardless of status)
        $user = $this->userModel->where('email', $email)->first();

        if (!$user) {
            // User doesn't exist - invalid email
            // Increment rate limit counter
            $newCount = $attemptCount + 1;
            cache()->save($rateLimitKey, $newCount, 1800);
            if ($newCount >= 5) {
                cache()->save($lockoutKey, time() + 900, 900);
            }
            
            try {
                log_failed_login($email);
            } catch (\Exception $e) {
                log_message('error', 'Failed to log failed login attempt: ' . $e->getMessage());
            }
            
            return $this->response
                ->setContentType('application/json')
                ->setJSON([
                    'success' => false,
                    'message' => 'Invalid email or password'
                ])
                ->setStatusCode(401);
        }

        // User exists - check if status is active
        if ($user['status'] !== 'active') {
            // Increment rate limit counter
            $newCount = $attemptCount + 1;
            cache()->save($rateLimitKey, $newCount, 1800);
            if ($newCount >= 5) {
                cache()->save($lockoutKey, time() + 900, 900);
            }
            
            try {
                log_failed_login($email);
            } catch (\Exception $e) {
                log_message('error', 'Failed to log failed login attempt: ' . $e->getMessage());
            }
            
            return $this->response
                ->setContentType('application/json')
                ->setJSON([
                    'success' => false,
                    'message' => 'Invalid email or password'
                ])
                ->setStatusCode(401);
        }

        // User exists and is active - verify password
        // Support both bcrypt-hashed and plain-text passwords
        $passwordValid = false;
        if (str_starts_with($user['password'], '$2y$') || str_starts_with($user['password'], '$2a$')) {
            // Password is bcrypt-hashed — use password_verify
            $passwordValid = password_verify($password, $user['password']);
        } else {
            // Plain-text fallback — auto-hash on successful match
            $passwordValid = ($password === $user['password']);
            if ($passwordValid) {
                // Upgrade to bcrypt hash for future logins
                $this->userModel->skipValidation(true);
                $this->userModel->update($user['user_id'], [
                    'password' => password_hash($password, PASSWORD_DEFAULT)
                ]);
                log_message('info', 'Auto-hashed plain-text password for user ID: ' . $user['user_id']);
            }
        }

        if (!$passwordValid) {
            // Password is wrong - increment rate limit counter
            $newCount = $attemptCount + 1;
            cache()->save($rateLimitKey, $newCount, 1800);
            if ($newCount >= 5) {
                cache()->save($lockoutKey, time() + 900, 900);
            }
            
            try {
                log_failed_login($email);
            } catch (\Exception $e) {
                log_message('error', 'Failed to log failed login attempt: ' . $e->getMessage());
            }
            
            return $this->response
                ->setContentType('application/json')
                ->setJSON([
                    'success' => false,
                    'message' => 'Invalid email or password'
                ])
                ->setStatusCode(401);
        }

        // User exists, is active, and password is correct
        // Now check if user is admin (user_type_id = 3)
        // Only users with user_type_id = 3 are allowed to access the admin system
        if ($user['user_type_id'] != UserModel::ROLE_ADMIN) {
            return $this->response->setJSON([
                'success' => false,
                'message' => 'Access denied. Admin privileges required.'
            ])->setStatusCode(403);
        }

        // Clear rate limit counter on successful login
        cache()->delete($rateLimitKey);

        // Create session data
        $sessionData = [
            'user_id' => $user['user_id'],
            'email' => $user['email'],
            'first_name' => $user['first_name'] ?? '',
            'last_name' => $user['last_name'] ?? '',
            'user_type_id' => $user['user_type_id'],
            'is_admin' => true,
            'login_time' => time(),
            'last_activity' => time()
        ];
        
        // Add profile picture if it exists
        if (!empty($user['profile_picture'])) {
            $sessionData['profile_picture'] = $user['profile_picture'];
        }

        // Set session data
        $this->session->set($sessionData);

        // Update user online status (if method exists)
        if (method_exists($this->userModel, 'updateOnlineStatus')) {
            $this->userModel->updateOnlineStatus($user['user_id'], true);
        }

        // Log successful login
        $userName = ($user['first_name'] ?? '') . ' ' . ($user['last_name'] ?? '');
        log_login($user['user_id'], trim($userName));

        // Return success response
        // Redirect to main layout (home page) which will show the dashboard
        $firstName = $user['first_name'] ?? 'Admin';
        $this->session->setFlashdata('welcome_message', "Welcome back, {$firstName}!");

        return $this->response->setJSON([
            'success' => true,
            'message' => 'Login successful',
            'redirect' => base_url() // Redirects to '/' which loads main_layout.php
        ]);
    }

    /**
     * Logout user
     * 
     * Destroys session and redirects to login page
     * 
     * @return mixed - Redirect to login page
     */
    public function logout()
    {
        $userId = $this->session->get('user_id');
        $userName = ($this->session->get('first_name') ?? '') . ' ' . ($this->session->get('last_name') ?? '');

        // Update user online status before logout (if method exists)
        if ($userId && method_exists($this->userModel, 'updateOnlineStatus')) {
            $this->userModel->updateOnlineStatus($userId, false);
        }

        // Log logout before destroying session
        if ($userId) {
            log_logout($userId, trim($userName));
        }

        // Destroy session
        $this->session->destroy();

        // Redirect to login page
        return redirect()->to('/login');
    }
}

