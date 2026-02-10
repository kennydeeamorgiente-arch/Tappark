<!-- Change Password Modal -->
<div class="modal fade" id="changePasswordModal" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header text-white" style="background: linear-gradient(135deg, #800000 0%, #990000 100%); padding: 1rem 1.5rem;">
                <button type="button" class="btn btn-link text-white p-0 me-2" onclick="backToProfileModal()" style="text-decoration: none; border: none; background: none;" title="Back to Profile">
                    <i class="fas fa-arrow-left fa-lg"></i>
                </button>
                <h5 class="modal-title fw-bold text-white mb-0 flex-grow-1">
                    <i class="fas fa-shield-alt me-2"></i>Change Password
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <?= form_open('profile/change-password', ['id' => 'changePasswordForm']) ?>
                <div class="modal-body" style="padding: 1.5rem;">
                    <!-- Info Banner -->
                    <div class="alert alert-info d-flex align-items-center mb-3 py-2" style="border-left: 4px solid #0dcaf0;">
                        <i class="fas fa-info-circle me-2"></i>
                        <div>
                            <strong class="small">Password Requirements:</strong><br>
                            <small>• Minimum 8 characters<br>• Must contain letters and numbers</small>
                        </div>
                    </div>
                    
                    <!-- Current Password Section -->
                    <div class="mb-3">
                        <label for="currentPassword" class="form-label fw-semibold small">
                            <i class="fas fa-lock me-2" style="color: #800000;"></i>Current Password <span class="text-danger">*</span>
                        </label>
                        <div class="input-group">
                            <span class="input-group-text bg-light">
                                <i class="fas fa-key text-muted"></i>
                            </span>
                            <input type="password" 
                                   class="form-control" 
                                   id="currentPassword" 
                                   name="current_password"
                                   placeholder="Enter your current password"
                                   required>
                            <button class="btn btn-outline-secondary" 
                                    type="button" 
                                    onclick="togglePasswordVisibility('currentPassword')"
                                    title="Show/Hide Password">
                                <i class="fas fa-eye"></i>
                            </button>
                        </div>
                        <div class="invalid-feedback">Current password is required</div>
                    </div>
                    
                    <hr class="my-3">
                    
                    <!-- New Password Section -->
                    <div class="mb-3">
                        <label for="newPassword" class="form-label fw-semibold small">
                            <i class="fas fa-key me-2" style="color: #800000;"></i>New Password <span class="text-danger">*</span>
                        </label>
                        <div class="input-group">
                            <span class="input-group-text bg-light">
                                <i class="fas fa-lock text-muted"></i>
                            </span>
                            <input type="password" 
                                   class="form-control" 
                                   id="newPassword" 
                                   name="new_password"
                                   placeholder="Enter your new password"
                                   required 
                                   minlength="8">
                            <button class="btn btn-outline-secondary" 
                                    type="button" 
                                    onclick="togglePasswordVisibility('newPassword')"
                                    title="Show/Hide Password">
                                <i class="fas fa-eye"></i>
                            </button>
                        </div>
                        <div class="invalid-feedback">Password must be at least 8 characters</div>
                        <div class="mt-1">
                            <small id="passwordStrength" class="fw-semibold small">Password strength: <span class="text-muted">Not set</span></small>
                        </div>
                    </div>
                    
                    <!-- Confirm Password Section -->
                    <div class="mb-3">
                        <label for="confirmPassword" class="form-label fw-semibold small">
                            <i class="fas fa-check-circle me-2" style="color: #800000;"></i>Confirm New Password <span class="text-danger">*</span>
                        </label>
                        <div class="input-group">
                            <span class="input-group-text bg-light">
                                <i class="fas fa-lock text-muted"></i>
                            </span>
                            <input type="password" 
                                   class="form-control" 
                                   id="confirmPassword" 
                                   name="confirm_password"
                                   placeholder="Re-enter your new password"
                                   required>
                            <button class="btn btn-outline-secondary" 
                                    type="button" 
                                    onclick="togglePasswordVisibility('confirmPassword')"
                                    title="Show/Hide Password">
                                <i class="fas fa-eye"></i>
                            </button>
                        </div>
                        <div class="invalid-feedback">Passwords do not match</div>
                    </div>
                    
                    <!-- Alert Messages -->
                    <div id="changePasswordError" class="alert alert-danger d-none py-2">
                        <i class="fas fa-exclamation-triangle me-2"></i>
                        <span></span>
                    </div>
                    <div id="changePasswordSuccess" class="alert alert-success d-none py-2">
                        <i class="fas fa-check-circle me-2"></i>
                        <span></span>
                    </div>
                </div>
                <div class="modal-footer" style="padding: 1rem 1.5rem;">
                    <button type="button" class="btn btn-secondary btn-sm" data-bs-dismiss="modal">
                        <i class="fas fa-times me-1"></i>Cancel
                    </button>
                    <button type="submit" class="btn btn-maroon btn-sm" id="changePasswordBtn">
                        <i class="fas fa-shield-alt me-1"></i>Change Password
                    </button>
                </div>
            <?= form_close() ?>
        </div>
    </div>
</div>

