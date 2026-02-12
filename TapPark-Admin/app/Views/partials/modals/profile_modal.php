<!-- Profile & Settings Modal -->
<div class="modal fade" id="profileModal" tabindex="-1">
    <div class="modal-dialog modal-lg modal-dialog-scrollable">
        <div class="modal-content">
            <div class="modal-header text-white" style="background: linear-gradient(135deg, #800000 0%, #990000 100%); padding: 1rem 1.5rem;">
                <h5 class="modal-title fw-bold text-white mb-0">
                    <i class="fas fa-user-cog me-2"></i>Profile & Settings
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body" style="padding: 1.5rem; max-height: 70vh; overflow-y: auto;">
                <!-- Tab Navigation -->
                <ul class="nav nav-tabs mb-3" role="tablist">
                    <li class="nav-item" role="presentation">
                        <button class="nav-link active" id="profile-tab" data-bs-toggle="tab" data-bs-target="#profile-content" type="button">
                            <i class="fas fa-user me-2"></i>Profile
                        </button>
                    </li>
                    <li class="nav-item" role="presentation">
                        <button class="nav-link" id="settings-tab" data-bs-toggle="tab" data-bs-target="#settings-content" type="button">
                            <i class="fas fa-cog me-2"></i>Settings
                        </button>
                    </li>
                    <li class="nav-item" role="presentation">
                        <button class="nav-link" id="system-tab" data-bs-toggle="tab" data-bs-target="#system-content" type="button">
                            <i class="fas fa-server me-2"></i>System
                        </button>
                    </li>
                </ul>

                <!-- Tab Content -->
                <div class="tab-content">
                    <!-- Profile Tab -->
                    <div class="tab-pane fade show active" id="profile-content">
                        <!-- Alert container with fixed height to prevent layout shift -->
                        <div style="min-height: 48px; margin-bottom: 1rem;">
                            <div id="profileError" class="alert alert-danger d-none py-2 mb-0">
                                <i class="fas fa-exclamation-circle me-2"></i>
                                <span></span>
                            </div>
                            <div id="profileSuccess" class="alert alert-success d-none py-2 mb-0">
                                <i class="fas fa-check-circle me-2"></i>
                                <span></span>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-4 text-center mb-3">
                                <div class="profile-avatar-large mb-2 position-relative d-inline-block">
                                    <?php 
                                    // Fetch user data from database, not just session
                                    $userModel = new \App\Models\UserModel();
                                    $userId = session()->get('user_id');
                                    $userData = null;
                                    $profilePic = null;
                                    $firstName = 'A';
                                    $lastName = '';
                                    $email = '';
                                    
                                    if ($userId) {
                                        $userData = $userModel->find($userId);
                                        if ($userData) {
                                            // Get data from database first
                                            $firstName = $userData['first_name'] ?? session()->get('first_name') ?? 'A';
                                            $lastName = $userData['last_name'] ?? session()->get('last_name') ?? '';
                                            $email = $userData['email'] ?? session()->get('email') ?? '';
                                            
                                            if (!empty($userData['profile_picture'])) {
                                                $profilePic = $userData['profile_picture'];
                                            }
                                        }
                                    }
                                    
                                    // Fallback to session if database fetch failed
                                    if (empty($firstName) || $firstName === 'A') {
                                        $firstName = session()->get('first_name') ?? 'A';
                                    }
                                    if (empty($lastName)) {
                                        $lastName = session()->get('last_name') ?? '';
                                    }
                                    if (empty($email)) {
                                        $email = session()->get('email') ?? '';
                                    }
                                    if (empty($profilePic)) {
                                        $profilePic = session()->get('profile_picture');
                                    }
                                    
                                    $firstLetter = strtoupper(substr($firstName, 0, 1));
                                    $avatarSrc = !empty($profilePic) && file_exists(ROOTPATH . 'public/uploads/profiles/' . $profilePic)
                                        ? base_url('uploads/profiles/' . $profilePic)
                                        : 'data:image/svg+xml;base64,' . base64_encode('<svg width="100" height="100" xmlns="http://www.w3.org/2000/svg"><rect width="100" height="100" fill="#800000"/><text x="50%" y="50%" font-family="Arial, sans-serif" font-size="40" font-weight="bold" fill="#ffffff" text-anchor="middle" dominant-baseline="central">' . htmlspecialchars($firstLetter) . '</text></svg>');
                                    ?>
                                    <img id="profileAvatarPreview" 
                                         src="<?= esc($avatarSrc) ?>" 
                                         alt="Avatar" 
                                         class="rounded-circle shadow-sm" 
                                         style="width: 100px; height: 100px; border: 3px solid #800000; object-fit: cover;">
                                    <label id="profilePictureCameraBtn" for="profilePictureInput" class="btn btn-sm btn-maroon position-absolute bottom-0 end-0 rounded-circle" style="width: 32px; height: 32px; padding: 0; line-height: 32px; cursor: pointer;" title="Change Profile Picture">
                                        <i class="fas fa-camera fa-xs"></i>
                                    </label>
                                    <input type="file" id="profilePictureInput" name="profile_picture" accept="image/jpeg,image/jpg,image/png,image/gif" class="d-none" disabled>
                                </div>
                                <h6 class="mb-1 fw-bold" id="profileDisplayName"><?= esc($firstName . ' ' . $lastName) ?></h6>
                                <p class="text-muted small mb-1" id="profileDisplayEmail"><?= esc($email ?: 'admin@tappark.com') ?></p>
                                <span class="badge bg-maroon">Administrator</span>
                            </div>
                            <div class="col-md-8">
                                <div class="d-flex justify-content-between align-items-center mb-2">
                                    <h6 class="mb-0 fw-semibold">Personal Information</h6>
                                    <button type="button" class="btn btn-maroon btn-sm" id="profileEditBtn">
                                        <i class="fas fa-pen me-1"></i> Edit
                                    </button>
                                </div>
                                <?= form_open('profile/update', ['id' => 'profileForm', 'enctype' => 'multipart/form-data']) ?>
                                    <div id="profileFormSection">
                                    <div class="row mb-2">
                                        <div class="col-md-6 mb-2">
                                            <label class="form-label small">First Name <span class="text-danger">*</span></label>
                                            <input type="text" class="form-control form-control-sm bg-light" id="firstName" name="first_name" value="<?= esc($firstName) ?>" required readonly>
                                        </div>
                                        <div class="col-md-6 mb-2">
                                            <label class="form-label small">Last Name <span class="text-danger">*</span></label>
                                            <input type="text" class="form-control form-control-sm bg-light" id="lastName" name="last_name" value="<?= esc($lastName) ?>" required readonly>
                                        </div>
                                    </div>
                                    <div class="mb-2">
                                        <label class="form-label small">Email Address <span class="text-danger">*</span></label>
                                        <input type="email" class="form-control form-control-sm bg-light" id="email" name="email" value="<?= esc($email) ?>" required readonly>
                                    </div>
                                    <div class="mb-2">
                                        <label class="form-label small">Profile Picture</label>
                                        <input type="file" class="form-control form-control-sm bg-light" id="profilePictureFile" name="profile_picture" accept="image/jpeg,image/jpg,image/png,image/gif" disabled>
                                        <small class="text-muted">Max size: 2MB. Allowed formats: JPEG, PNG, GIF</small>
                                    </div>
                                    <div class="mb-2">
                                        <label class="form-label small">User Type</label>
                                        <input type="text" class="form-control form-control-sm bg-light" value="Administrator" disabled>
                                    </div>
                                    
                                    <!-- Normal Action Buttons (hidden by default) -->
                                    <div class="d-flex gap-2 d-none" id="profileNormalActions">
                                        <button type="button" class="btn btn-maroon btn-sm" id="profileSaveBtn">
                                            <i class="fas fa-save me-1"></i> Save Changes
                                        </button>
                                        <button type="button" class="btn btn-outline-secondary btn-sm" id="profileCancelBtn">
                                            <i class="fas fa-times me-1"></i> Cancel
                                        </button>
                                    </div>
                                    </div>
                                    
                                    <!-- Confirmation Section (hidden by default) -->
                                    <div id="profileConfirmSection" class="d-none">
                                        <div class="alert alert-info py-2 mb-2">
                                            <i class="fas fa-info-circle me-2"></i>
                                            <small class="mb-0 fw-semibold" id="profileConfirmMessage">Are you sure you want to save these changes?</small>
                                            <small class="d-block text-muted mt-1" id="profileConfirmDescription">Please review your information before confirming.</small>
                                        </div>
                                        <div class="d-flex justify-content-center gap-2">
                                            <button type="button" class="btn btn-secondary btn-sm px-4" id="profileConfirmCancelBtn">
                                                <i class="fas fa-times me-1"></i> No
                                            </button>
                                            <button type="button" class="btn btn-maroon btn-sm px-4" id="profileConfirmYesBtn">
                                                <i class="fas fa-check me-1"></i> Yes, Save Changes
                                            </button>
                                        </div>
                                    </div>
                            <?= form_close() ?>
                            </div>
                        </div>
                    </div>

                    <!-- Settings Tab -->
                    <div class="tab-pane fade" id="settings-content">
                        <!-- Theme Settings Card -->
                        <div class="card border-0 shadow-sm mb-3 settings-card">
                            <div class="card-body p-4">
                                <div class="d-flex align-items-center mb-3">
                                    <div class="settings-icon-wrapper me-3">
                                        <i class="fas fa-palette settings-icon"></i>
                                    </div>
                                    <div>
                                        <h6 class="mb-0 fw-semibold">Theme Preferences</h6>
                                        <small class="text-muted">Customize your visual experience</small>
                                    </div>
                                </div>
                                <hr class="my-3">
                                <div class="form-check form-switch settings-switch">
                                    <input class="form-check-input" type="checkbox" id="darkModeToggle">
                                    <label class="form-check-label" for="darkModeToggle">
                                        <span class="fw-semibold">Dark Mode</span>
                                        <small class="d-block text-muted">Switch to dark theme for better viewing in low light</small>
                                    </label>
                                </div>
                            </div>
                        </div>

                        <!-- Security Settings Card -->
                        <div class="card border-0 shadow-sm mb-3 settings-card">
                            <div class="card-body p-4">
                                <div class="d-flex align-items-center mb-3">
                                    <div class="settings-icon-wrapper me-3">
                                        <i class="fas fa-shield-alt settings-icon"></i>
                                    </div>
                                    <div>
                                        <h6 class="mb-0 fw-semibold">Security Settings</h6>
                                        <small class="text-muted">Manage your account security</small>
                                    </div>
                                </div>
                                <hr class="my-3">
                                <div class="d-flex align-items-start justify-content-between mb-3">
                                    <div class="flex-grow-1 me-3">
                                        <h6 class="mb-1 fw-semibold">Password</h6>
                                        <p class="text-muted small mb-2">Keep your account secure with a strong password</p>
                                        <small class="text-muted d-flex align-items-center">
                                            <i class="fas fa-info-circle me-1"></i>
                                            Last password change: Never
                                        </small>
                                    </div>
                                    <button class="btn btn-maroon btn-sm" onclick="openChangePasswordModal()">
                                        <i class="fas fa-key me-1"></i>Change Password
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- System Tab -->
                    <div class="tab-pane fade" id="system-content">
                        <!-- Database Settings -->
                        <div class="mb-4">
                            <h6 class="mb-3 fw-semibold d-flex align-items-center">
                                <i class="fas fa-database me-2" style="color: #800000;"></i>Database Configuration
                            </h6>
                            <?= form_open('settings/database', ['id' => 'databaseSettingsForm']) ?>
                                <div id="databaseSettingsError" class="alert alert-danger d-none py-2 mb-2">
                                    <i class="fas fa-exclamation-circle me-2"></i>
                                    <span></span>
                                </div>
                                <div id="databaseSettingsSuccess" class="alert alert-success d-none py-2 mb-2">
                                    <i class="fas fa-check-circle me-2"></i>
                                    <span></span>
                                </div>
                                <div class="alert alert-info py-2 mb-3">
                                    <i class="fas fa-info-circle me-2"></i>
                                    <small class="mb-0"><strong>Read-Only:</strong> Database configuration is displayed for reference only. For security reasons, changes must be made manually in the <code>.env</code> file by system administrators.</small>
                                </div>
                                <div class="row mb-2">
                                    <div class="col-md-6 mb-2">
                                        <label class="form-label small">Database Host</label>
                                        <input type="text" class="form-control form-control-sm bg-light" id="dbHost" value="localhost" placeholder="localhost" readonly disabled>
                                    </div>
                                    <div class="col-md-6 mb-2">
                                        <label class="form-label small">Database Port</label>
                                        <input type="text" class="form-control form-control-sm bg-light" id="dbPort" value="3306" placeholder="3306" readonly disabled>
                                    </div>
                                </div>
                                <div class="mb-2">
                                    <label class="form-label small">Database Name</label>
                                    <input type="text" class="form-control form-control-sm bg-light" id="dbName" value="admintappark" placeholder="database_name" readonly disabled>
                                </div>
                                <div class="row mb-2">
                                    <div class="col-md-6 mb-2">
                                        <label class="form-label small">Database Username</label>
                                        <input type="text" class="form-control form-control-sm bg-light" id="dbUsername" value="root" placeholder="username" readonly disabled>
                                    </div>
                                    <div class="col-md-6 mb-2">
                                        <label class="form-label small">Database Password</label>
                                        <input type="password" class="form-control form-control-sm bg-light" id="dbPassword" value="••••••••" placeholder="••••••••" readonly disabled>
                                    </div>
                                </div>
                                <div class="alert alert-warning py-2 mb-0">
                                    <i class="fas fa-shield-alt me-2"></i>
                                    <small class="mb-0"><strong>Security Notice:</strong> Database credentials are sensitive information. Configuration changes must be made directly in the <code>.env</code> file and require a system restart to take effect.</small>
                                </div>
                            <?= form_close() ?>
                        </div>

                        <!-- Application Settings -->
                        <div class="mb-4">
                            <h6 class="mb-3 fw-semibold d-flex align-items-center">
                                <i class="fas fa-sliders-h me-2" style="color: #800000;"></i>Application Settings
                            </h6>
                            <div id="appSettingsError" class="alert alert-danger d-none py-2 mb-2">
                                <i class="fas fa-exclamation-circle me-2"></i>
                                <span></span>
                            </div>
                            <div id="appSettingsSuccess" class="alert alert-success d-none py-2 mb-2">
                                <i class="fas fa-check-circle me-2"></i>
                                <span></span>
                            </div>
                            <?php 
                                $appSettings = session()->get('app_settings') ?: [
                                    'app_name' => 'TapPark Admin',
                                    'timezone' => 'Asia/Manila',
                                    'session_timeout' => 60,
                                    'records_per_page' => 25
                                ];
                                $currentTimezone = $appSettings['timezone'] ?? 'Asia/Manila';
                                $currentTimeout = $appSettings['session_timeout'] ?? 60;
                                $currentPerPage = $appSettings['records_per_page'] ?? 25;
                            ?>
                            <?= form_open('settings/application', ['id' => 'applicationSettingsForm']) ?>
                                <div class="row mb-2">
                                    <div class="col-md-6 mb-2">
                                        <label class="form-label small">Application Name</label>
                                        <input type="text" class="form-control form-control-sm bg-light" id="appName" value="TapPark Admin" placeholder="App Name" readonly disabled>
                                    </div>
                                    <div class="col-md-6 mb-2">
                                        <label class="form-label small">Time Zone</label>
                                        <select class="form-select form-select-sm" id="appTimezone">
                                            <option value="Asia/Manila" <?= $currentTimezone === 'Asia/Manila' ? 'selected' : '' ?>>Asia/Manila (UTC+8)</option>
                                            <option value="UTC" <?= $currentTimezone === 'UTC' ? 'selected' : '' ?>>UTC (UTC+0)</option>
                                            <option value="America/New_York" <?= $currentTimezone === 'America/New_York' ? 'selected' : '' ?>>America/New York (UTC-5)</option>
                                            <option value="Europe/London" <?= $currentTimezone === 'Europe/London' ? 'selected' : '' ?>>Europe/London (UTC+0)</option>
                                            <option value="Asia/Tokyo" <?= $currentTimezone === 'Asia/Tokyo' ? 'selected' : '' ?>>Asia/Tokyo (UTC+9)</option>
                                            <option value="Australia/Sydney" <?= $currentTimezone === 'Australia/Sydney' ? 'selected' : '' ?>>Australia/Sydney (UTC+11)</option>
                                        </select>
                                    </div>
                                </div>
                                <div class="row mb-2">
                                    <div class="col-md-6 mb-2">
                                        <label class="form-label small">Session Timeout</label>
                                        <select class="form-select form-select-sm" id="sessionTimeout">
                                            <option value="15" <?= (int)$currentTimeout === 15 ? 'selected' : '' ?>>15 Minutes</option>
                                            <option value="30" <?= (int)$currentTimeout === 30 ? 'selected' : '' ?>>30 Minutes</option>
                                            <option value="45" <?= (int)$currentTimeout === 45 ? 'selected' : '' ?>>45 Minutes</option>
                                            <option value="60" <?= (int)$currentTimeout === 60 ? 'selected' : '' ?>>1 Hour</option>
                                            <option value="360" <?= (int)$currentTimeout === 360 ? 'selected' : '' ?>>6 Hours</option>
                                            <option value="720" <?= (int)$currentTimeout === 720 ? 'selected' : '' ?>>12 Hours</option>
                                            <option value="1440" <?= (int)$currentTimeout === 1440 ? 'selected' : '' ?>>24 Hours</option>
                                        </select>
                                    </div>
                                    <div class="col-md-6 mb-2">
                                        <label class="form-label small">Records Per Page</label>
                                        <select class="form-select form-select-sm" id="recordsPerPage">
                                            <option value="10" <?= (int)$currentPerPage === 10 ? 'selected' : '' ?>>10 Rows</option>
                                            <option value="25" <?= (int)$currentPerPage === 25 ? 'selected' : '' ?>>25 Rows</option>
                                            <option value="50" <?= (int)$currentPerPage === 50 ? 'selected' : '' ?>>50 Rows</option>
                                            <option value="100" <?= (int)$currentPerPage === 100 ? 'selected' : '' ?>>100 Rows</option>
                                        </select>
                                    </div>
                                </div>
                                <button type="button" class="btn btn-maroon btn-sm" id="saveAppSettingsBtn">
                                    <i class="fas fa-save me-1"></i> Save Application Settings
                                </button>
                            <?= form_close() ?>
                        </div>

                        <!-- System Maintenance -->
                        <div class="mb-0">
                            <h6 class="mb-3 fw-semibold d-flex align-items-center">
                                <i class="fas fa-tools me-2" style="color: #800000;"></i>System Maintenance
                            </h6>
                            <div class="d-flex flex-wrap gap-2 mb-2">
                                <button type="button" class="btn btn-outline-info btn-sm" disabled>
                                    <i class="fas fa-database me-1"></i> Backup Database
                                </button>
                                <button type="button" class="btn btn-outline-warning btn-sm" disabled>
                                    <i class="fas fa-broom me-1"></i> Clear Cache
                                </button>
                                <button type="button" class="btn btn-outline-secondary btn-sm" disabled>
                                    <i class="fas fa-file-alt me-1"></i> View Logs
                                </button>
                                <button type="button" class="btn btn-outline-danger btn-sm" disabled>
                                    <i class="fas fa-sync-alt me-1"></i> System Reset
                                </button>
                            </div>
                            <div class="alert alert-info py-2 mb-0">
                                <i class="fas fa-info-circle me-2"></i>
                                <small class="mb-0">System maintenance features are currently under development.</small>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="modal-footer" style="padding: 1rem 1.5rem;">
                <button type="button" class="btn btn-secondary btn-sm" data-bs-dismiss="modal">Close</button>
            </div>
        </div>
    </div>
</div>

<!-- Profile Picture Crop Modal -->
<div class="modal fade" id="profileImageCropModal" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header text-white" style="background: linear-gradient(135deg, #800000 0%, #990000 100%); padding: 1rem 1.5rem;">
                <h5 class="modal-title fw-bold text-white mb-0">
                    <i class="fas fa-crop-alt me-2"></i>Crop Profile Picture
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body" style="padding: 1.25rem 1.5rem;">
                <div class="d-flex justify-content-center">
                    <canvas id="profileCropCanvas" width="360" height="360" style="width: 360px; height: 360px; background: #f1f3f5; border-radius: 12px;"></canvas>
                </div>
                <div class="mt-3">
                    <label for="profileCropZoom" class="form-label small mb-1">Zoom</label>
                    <input type="range" class="form-range" id="profileCropZoom" min="1" max="3" step="0.01" value="1">
                </div>
            </div>
            <div class="modal-footer" style="padding: 1rem 1.5rem;">
                <button type="button" class="btn btn-secondary btn-sm" id="profileCropCancelBtn" data-bs-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-maroon btn-sm" id="profileCropApplyBtn">
                    <i class="fas fa-check me-1"></i> Apply
                </button>
            </div>
        </div>
    </div>
</div>

<style>
/* Profile Modal Styles */
#profileModal .settings-card {
    border-radius: 12px;
    transition: all 0.3s ease;
}

#profileModal .settings-card:hover {
    transform: translateY(-2px);
    box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
}

#profileModal .settings-icon-wrapper {
    width: 48px;
    height: 48px;
    border-radius: 12px;
    background: linear-gradient(135deg, #800000 0%, #990000 100%);
    display: flex;
    align-items: center;
    justify-content: center;
    flex-shrink: 0;
}

#profileModal .settings-icon {
    color: white;
    font-size: 1.25rem;
}

#profileModal .settings-switch {
    padding: 0.75rem;
    border-radius: 8px;
    transition: background-color 0.2s;
    display: flex;
    align-items: flex-start;
    gap: 0.75rem;
}

#profileModal .settings-switch:hover {
    background-color: rgba(128, 0, 0, 0.05);
}

#profileModal .settings-switch .form-check-input {
    cursor: pointer;
    width: 2.75rem;
    height: 1.5rem;
    margin-top: 0.125rem;
    flex-shrink: 0;
}

[data-bs-theme="dark"] #profileModal .settings-card {
    background: var(--card-bg);
    border-color: var(--border-color);
}

[data-bs-theme="dark"] #profileModal .settings-icon-wrapper {
    background: linear-gradient(135deg, #661f1f 0%, #7a2f2f 100%);
}

[data-bs-theme="dark"] #profileModal .settings-switch:hover {
    background-color: rgba(128, 0, 0, 0.15);
}

[data-bs-theme="dark"] #profileModal .settings-switch .form-check-input {
    background-color: var(--input-bg);
    border-color: var(--border-color);
}

[data-bs-theme="dark"] #profileModal .settings-switch .form-check-input:checked {
    background-color: #800000;
    border-color: #800000;
}

/* Profile Avatar Container - Prevent layout shift */
#profileModal .profile-avatar-large {
    min-width: 100px;
    min-height: 100px;
    display: inline-block;
    position: relative;
}

#profileModal #profileAvatarPreview {
    width: 100px !important;
    height: 100px !important;
    border: 3px solid #800000 !important;
    object-fit: cover !important;
    display: block !important;
    border-radius: 50% !important;
}

/* Save Changes Button Enhanced Hover */
#profileModal #profileSaveBtn {
    transition: all 0.3s ease;
    background: linear-gradient(135deg, #800000 0%, #990000 100%);
    border: none;
    box-shadow: 0 2px 8px rgba(128, 0, 0, 0.2);
    color: white !important;
}

#profileModal #profileSaveBtn:hover {
    background: linear-gradient(135deg, #990000 0%, #b30000 100%);
    transform: translateY(-2px);
    box-shadow: 0 4px 12px rgba(128, 0, 0, 0.4);
    color: white !important;
}

#profileModal #profileSaveBtn:active {
    transform: translateY(0);
    box-shadow: 0 2px 6px rgba(128, 0, 0, 0.3);
    color: white !important;
}

#profileModal #profileSaveBtn:disabled {
    opacity: 0.6;
    cursor: not-allowed;
    transform: none;
    color: white !important;
}

/* All btn-maroon buttons in profile modal should have white text */
#profileModal .btn-maroon {
    color: white !important;
}

#profileModal .btn-maroon:hover {
    color: white !important;
}

/* Dark mode button hover */
[data-bs-theme="dark"] #profileModal #profileSaveBtn {
    background: linear-gradient(135deg, #661f1f 0%, #7a2f2f 100%);
    box-shadow: 0 2px 8px rgba(128, 0, 0, 0.3);
    color: #ffb3b3 !important;
}

[data-bs-theme="dark"] #profileModal #profileSaveBtn:hover {
    background: linear-gradient(135deg, #7a2f2f 0%, #8a3f3f 100%);
    box-shadow: 0 4px 12px rgba(128, 0, 0, 0.5);
    color: white !important;
}

[data-bs-theme="dark"] #profileModal .btn-maroon {
    color: #ffb3b3 !important;
}

[data-bs-theme="dark"] #profileModal .btn-maroon:hover {
    color: white !important;
}

/* Ensure alerts don't affect avatar layout */
#profileModal #profileError,
#profileModal #profileSuccess {
    transition: opacity 0.3s ease, transform 0.3s ease;
    margin-bottom: 0;
}

#profileModal #profileError.d-none,
#profileModal #profileSuccess.d-none {
    display: none !important;
}

/* Prevent layout shift when alerts show/hide */
#profileModal #profile-content > div:first-child {
    min-height: 48px;
    display: flex;
    flex-direction: column;
    justify-content: flex-start;
}

/* Readonly fields styling */
#profileModal #firstName[readonly],
#profileModal #lastName[readonly],
#profileModal #email[readonly] {
    background-color: #f8f9fa;
    cursor: not-allowed;
}

#profileModal #profilePictureFile[disabled] {
    background-color: #f8f9fa;
    cursor: not-allowed;
}

/* Global disabled control treatment inside modal */
#profileModal .form-control[disabled],
#profileModal .form-select[disabled],
#profileModal input[readonly][disabled],
#profileModal input[disabled],
#profileModal select[disabled] {
    cursor: not-allowed;
    background-color: #f1f3f5;
    opacity: 1;
}

/* Confirmation section styling */
#profileModal #profileConfirmSection {
    border-top: 1px solid #dee2e6;
    padding-top: 1rem;
    margin-top: 1rem;
}

#profileModal #profileConfirmSection .alert {
    margin-bottom: 0.75rem;
}

/* Responsive adjustments for profile modal */
@media (max-width: 768px) {
    #profileModal .settings-card .card-body {
        padding: 1rem !important;
    }

    #profileModal .settings-icon-wrapper {
        width: 40px;
        height: 40px;
    }

    #profileModal .settings-switch .form-check-input {
        width: 2.5rem;
        height: 1.25rem;
    }
    
    #profileModal .profile-avatar-large {
        min-width: 80px;
        min-height: 80px;
    }
    
    #profileModal #profileAvatarPreview {
        width: 80px !important;
        height: 80px !important;
    }
}
</style>

