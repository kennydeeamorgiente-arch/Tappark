<?php

namespace App\Controllers;

use App\Controllers\BaseController;
use App\Models\UserModel;

class Profile extends BaseController
{
    protected $userModel;

    public function __construct()
    {
        $this->userModel = new UserModel();
        helper('form');
    }

    /**
     * Update user profile
     */
    public function update()
    {
        try {
            // Check if user is logged in - use get() instead of has() for more reliable check
            $userId = session()->get('user_id');
            
            if (!$userId) {
                return $this->response->setJSON([
                    'success' => false,
                    'message' => 'Unauthorized access. Please log in again.'
                ])->setStatusCode(401);
            }
            
            // Verify user exists in database and is admin
            $user = $this->userModel->find($userId);
            if (!$user) {
                return $this->response->setJSON([
                    'success' => false,
                    'message' => 'User not found'
                ])->setStatusCode(404);
            }
            
            // Check if user is admin (user_type_id = 3)
            if (empty($user['user_type_id']) || $user['user_type_id'] != UserModel::ROLE_ADMIN) {
                return $this->response->setJSON([
                    'success' => false,
                    'message' => 'Access denied. Admin privileges required.'
                ])->setStatusCode(403);
            }

            // Get posted data
            $data = [
                'first_name' => $this->request->getPost('first_name'),
                'last_name' => $this->request->getPost('last_name'),
                'email' => $this->request->getPost('email')
            ];

            // Validation
            $validation = \Config\Services::validation();
            $validation->setRules([
                'first_name' => 'required|min_length[2]|max_length[50]',
                'last_name' => 'required|min_length[2]|max_length[50]',
                'email' => "required|valid_email|is_unique[users.email,user_id,{$userId}]"
            ]);

            if (!$validation->run($data)) {
                return $this->response->setJSON([
                    'success' => false,
                    'message' => 'Validation failed',
                    'errors' => $validation->getErrors()
                ])->setStatusCode(400);
            }

            // Handle profile picture upload
            $profilePicture = $this->request->getFile('profile_picture');
            
            // Only process file if it exists and is valid
            if ($profilePicture && $profilePicture->isValid() && !$profilePicture->hasMoved()) {
                // Get user data to check for old profile picture
                $user = $this->userModel->find($userId);
                
                // Define upload path
                $uploadPath = ROOTPATH . 'public/uploads/profiles/';
                
                // Create directory if it doesn't exist
                if (!is_dir($uploadPath)) {
                    mkdir($uploadPath, 0755, true);
                }
                
                // Validate file
                $maxSize = 2 * 1024 * 1024; // 2MB
                $allowedTypes = ['image/jpeg', 'image/jpg', 'image/png', 'image/gif'];
                $allowedExtensions = ['jpg', 'jpeg', 'png', 'gif'];
                $maxDimensions = [2048, 2048]; // Max width/height
                
                if ($profilePicture->getSize() > $maxSize) {
                    return $this->response->setJSON([
                        'success' => false,
                        'message' => 'File size exceeds 2MB limit'
                    ])->setStatusCode(400);
                }
                
                if (!in_array($profilePicture->getMimeType(), $allowedTypes)) {
                    return $this->response->setJSON([
                        'success' => false,
                        'message' => 'Invalid file type. Only JPEG, PNG, and GIF images are allowed'
                    ])->setStatusCode(400);
                }
                
                // Validate file extension
                $extension = strtolower($profilePicture->getClientExtension());
                if (!in_array($extension, $allowedExtensions)) {
                    return $this->response->setJSON([
                        'success' => false,
                        'message' => 'Invalid file extension. Only JPEG, PNG, and GIF images are allowed'
                    ])->setStatusCode(400);
                }
                
                // Validate actual image content (prevent fake images)
                $imageInfo = @getimagesize($profilePicture->getTempName());
                if (!$imageInfo) {
                    return $this->response->setJSON([
                        'success' => false,
                        'message' => 'Invalid image file or corrupted image'
                    ])->setStatusCode(400);
                }
                
                // Check image dimensions
                list($width, $height) = $imageInfo;
                if ($width > $maxDimensions[0] || $height > $maxDimensions[1]) {
                    return $this->response->setJSON([
                        'success' => false,
                        'message' => "Image dimensions too large. Maximum allowed: {$maxDimensions[0]}x{$maxDimensions[1]} pixels"
                    ])->setStatusCode(400);
                }
                
                // Get file extension
                $extension = $profilePicture->getClientExtension();
                
                // Generate secure random filename
                $newName = 'profile_' . $userId . '_' . bin2hex(random_bytes(8)) . '.' . $extension;
                
                // Move uploaded file
                $moved = $profilePicture->move($uploadPath, $newName);
                
                if ($moved) {
                    // Delete old profile picture if exists
                    if (!empty($user['profile_picture']) && file_exists($uploadPath . $user['profile_picture'])) {
                        @unlink($uploadPath . $user['profile_picture']);
                    }
                    
                    // Add profile picture filename to data array
                    $data['profile_picture'] = $newName;
                } else {
                    $errorMsg = $profilePicture->getErrorString() ?: 'Unknown upload error';
                    log_message('error', 'Profile::update File Move Failed: ' . $errorMsg);
                    return $this->response->setJSON([
                        'success' => false,
                        'message' => 'Failed to upload profile picture: ' . $errorMsg
                    ])->setStatusCode(500);
                }
            }

            // Update user in database
            $this->userModel->skipValidation(true);
            $updateResult = $this->userModel->update($userId, $data);
            
            if ($updateResult) {
                // Get updated user data from database
                $updatedUser = $this->userModel->find($userId);
                
                // Update session with new data
                $sessionData = [
                    'first_name' => $data['first_name'],
                    'last_name' => $data['last_name'],
                    'email' => $data['email']
                ];
                
                // Update profile picture in session if changed
                if (!empty($data['profile_picture'])) {
                    $sessionData['profile_picture'] = $data['profile_picture'];
                } elseif (!empty($updatedUser['profile_picture'])) {
                    $sessionData['profile_picture'] = $updatedUser['profile_picture'];
                }
                
                session()->set($sessionData);

                // Log the profile update activity
                log_update('User Profile', $userId, $data['first_name'] . ' ' . $data['last_name']);

                $response = [
                    'success' => true,
                    'message' => 'Profile updated successfully'
                ];
                
                // Include profile picture filename in response
                if (!empty($data['profile_picture'])) {
                    $response['profile_picture'] = $data['profile_picture'];
                } elseif (!empty($updatedUser['profile_picture'])) {
                    $response['profile_picture'] = $updatedUser['profile_picture'];
                }

                return $this->response->setJSON($response);
            }
            
            // If update failed
            $errors = $this->userModel->errors();
            log_message('error', 'Profile::update - Update failed. Model errors: ' . json_encode($errors));
            
            return $this->response->setJSON([
                'success' => false,
                'message' => 'Failed to update profile',
                'errors' => $errors
            ])->setStatusCode(500);

        } catch (\Exception $e) {
            log_message('error', 'Profile::update Error: ' . $e->getMessage());
            return $this->response->setJSON([
                'success' => false,
                'message' => 'An error occurred while updating profile: ' . $e->getMessage()
            ])->setStatusCode(500);
        }
    }

    /**
     * Change user password
     */
    public function changePassword()
    {
        try {
            // Check if user is logged in - use get() instead of has() for more reliable check
            $userId = session()->get('user_id');
            
            if (!$userId) {
                return $this->response->setJSON([
                    'success' => false,
                    'message' => 'Unauthorized access. Please log in again.'
                ])->setStatusCode(401);
            }
            $currentPassword = $this->request->getPost('current_password');
            $newPassword = $this->request->getPost('new_password');
            $confirmPassword = $this->request->getPost('confirm_password');

            // Validation
            $validation = \Config\Services::validation();
            $validation->setRules([
                'current_password' => 'required',
                'new_password' => 'required|min_length[8]',
                'confirm_password' => 'required|matches[new_password]'
            ]);

            if (!$validation->run($this->request->getPost())) {
                return $this->response->setJSON([
                    'success' => false,
                    'message' => 'Validation failed',
                    'errors' => $validation->getErrors()
                ])->setStatusCode(400);
            }

            // Get user
            $user = $this->userModel->find($userId);
            
            if (!$user) {
                return $this->response->setJSON([
                    'success' => false,
                    'message' => 'User not found'
                ])->setStatusCode(404);
            }

            // Verify current password
            if (!password_verify($currentPassword, $user['password'])) {
                return $this->response->setJSON([
                    'success' => false,
                    'message' => 'Current password is incorrect'
                ])->setStatusCode(400);
            }

            // Check if new password is same as current
            if (password_verify($newPassword, $user['password'])) {
                return $this->response->setJSON([
                    'success' => false,
                    'message' => 'New password must be different from current password'
                ])->setStatusCode(400);
            }

            // Hash new password
            $hashedPassword = password_hash($newPassword, PASSWORD_DEFAULT);

            // Update password in database
            $updateResult = $this->userModel->update($userId, [
                'password' => $hashedPassword
            ]);

            if ($updateResult) {
                // Log the password change activity
                $userName = $user['first_name'] . ' ' . ($user['last_name'] ?? '');
                log_activity($userId, 'UPDATE', "Changed password for user: {$userName}", $userId, 'password');
                
                return $this->response->setJSON([
                    'success' => true,
                    'message' => 'Password changed successfully'
                ]);
            }
            
            return $this->response->setJSON([
                'success' => false,
                'message' => 'Failed to update password'
            ])->setStatusCode(500);

        } catch (\Exception $e) {
            log_message('error', 'Profile::changePassword Error: ' . $e->getMessage());
            return $this->response->setJSON([
                'success' => false,
                'message' => 'An error occurred while changing password'
            ])->setStatusCode(500);
        }
    }

    /**
     * Save application settings
     */
    public function saveAppSettings()
    {
        try {
            // Check if user is logged in - use get() instead of has() for more reliable check
            $userId = session()->get('user_id');
            
            if (!$userId) {
                return $this->response->setJSON([
                    'success' => false,
                    'message' => 'Unauthorized access. Please log in again.'
                ])->setStatusCode(401);
            }

            // Get posted data
            $data = [
                'app_name' => $this->request->getPost('app_name'),
                'timezone' => $this->request->getPost('timezone'),
                'session_timeout' => $this->request->getPost('session_timeout'),
                'records_per_page' => $this->request->getPost('records_per_page')
            ];

            // Validation
            $validation = \Config\Services::validation();
            $validation->setRules([
                'app_name' => 'required|min_length[2]|max_length[100]',
                'timezone' => 'required',
                'session_timeout' => 'required|integer|in_list[15,30,45,60,360,720,1440]',
                'records_per_page' => 'required|integer|in_list[10,25,50,100]'
            ]);

            if (!$validation->run($data)) {
                return $this->response->setJSON([
                    'success' => false,
                    'message' => 'Validation failed',
                    'errors' => $validation->getErrors()
                ])->setStatusCode(400);
            }

            // Store in session (you can also save to database if you have a settings table)
            session()->set([
                'app_settings' => $data
            ]);

            // Log the settings update
            log_activity($userId, 'UPDATE', 'Application settings updated', null, 'settings');

            return $this->response->setJSON([
                'success' => true,
                'message' => 'Application settings saved successfully'
            ]);

        } catch (\Exception $e) {
            log_message('error', 'Profile::saveAppSettings Error: ' . $e->getMessage());
            return $this->response->setJSON([
                'success' => false,
                'message' => 'An error occurred while saving settings'
            ])->setStatusCode(500);
        }
    }

    /**
     * Save database configuration (read-only for security - just logs the attempt)
     */
    public function saveDatabaseConfig()
    {
        try {
            // Check if user is logged in - use get() instead of has() for more reliable check
            $userId = session()->get('user_id');
            
            if (!$userId) {
                return $this->response->setJSON([
                    'success' => false,
                    'message' => 'Unauthorized access. Please log in again.'
                ])->setStatusCode(401);
            }

            // Get posted data
            $data = [
                'db_host' => $this->request->getPost('db_host'),
                'db_port' => $this->request->getPost('db_port'),
                'db_name' => $this->request->getPost('db_name'),
                'db_username' => $this->request->getPost('db_username'),
                'db_password' => $this->request->getPost('db_password') ? '***' : '' // Don't log actual password
            ];

            // Log the attempt (but don't actually change database config for security)
            log_activity($userId, 'VIEW', 'Database configuration viewed (read-only)', null, 'database');

            // Return message explaining it's read-only for security
            return $this->response->setJSON([
                'success' => true,
                'message' => 'Database configuration is read-only for security. Changes must be made manually in the .env file by system administrators.'
            ]);

        } catch (\Exception $e) {
            log_message('error', 'Profile::saveDatabaseConfig Error: ' . $e->getMessage());
            return $this->response->setJSON([
                'success' => false,
                'message' => 'An error occurred while processing the request'
            ])->setStatusCode(500);
        }
    }
}

