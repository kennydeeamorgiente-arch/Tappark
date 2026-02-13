<!-- ============================================
   UNIFIED CRUD FORM MODAL
   One modal for Add/Edit across all entities
   Uses data-entity to show/hide relevant fields
   ============================================ -->
<div class="modal fade" id="crudFormModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <!-- Modal Header -->
            <div class="modal-header" style="background: linear-gradient(135deg, #800000 0%, #990000 100%);">
                <h5 class="modal-title text-white" id="crudModalTitle">
                    <i class="fas fa-plus-circle me-2" id="crudModalIcon"></i>
                    <span id="crudModalTitleText">Add New</span>
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            
            <!-- Modal Body -->
            <div class="modal-body">
                <!-- Form Section (shown by default) -->
                <div id="crudFormSection">
                    <!-- Static Role Warning Banner -->
                    <div id="staticRoleWarning" class="alert alert-info py-2 px-3 mb-3 small d-flex align-items-center" style="display: none !important;">
                        <i class="fas fa-info-circle me-2"></i>
                        <span>This user's role is fixed and cannot be changed from this form.</span>
                    </div>

                    <?= form_open('', ['id' => 'crudForm']) ?>
                        <!-- Hidden Fields -->
                        <input type="hidden" id="crudEntityType" name="entity_type" value="">
                        <input type="hidden" id="crudEntityId" name="entity_id" value="">
                        <input type="hidden" id="crudAction" name="action" value="add">
                        
                        <!-- ============================
                             SUBSCRIBER FIELDS
                             ============================ -->
                        <div class="entity-fields fields-users" style="display: none;">
                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label class="form-label">Subscriber First Name <span class="text-danger">*</span></label>
                                    <input type="text" class="form-control" name="first_name" id="userFirstName">
                                    <div class="invalid-feedback" id="error-first_name"></div>
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label class="form-label">Subscriber Last Name <span class="text-danger">*</span></label>
                                    <input type="text" class="form-control" name="last_name" id="userLastName">
                                    <div class="invalid-feedback" id="error-last_name"></div>
                                </div>
                            </div>
                            <div class="mb-3">
                                <label class="form-label">Subscriber Email <span class="text-danger">*</span></label>
                                <input type="email" class="form-control" name="email" id="userEmail">
                                <div class="invalid-feedback" id="error-email"></div>
                            </div>
                            <div class="mb-3 password-field">
                                <label class="form-label">Password <span class="text-danger add-only">*</span></label>
                                <input type="password" class="form-control" name="password" id="userPassword" minlength="8">
                                <small class="text-muted edit-only">Leave blank to keep current password</small>
                                <small class="text-muted add-only">Minimum 8 characters</small>
                                <div class="invalid-feedback" id="error-password"></div>
                                <div class="password-strength-meter" aria-hidden="true">
                                    <div class="password-strength-bar" id="userPasswordStrengthBar"></div>
                                </div>
                                <small class="password-strength-text" id="userPasswordStrengthText">Enter a password to check strength.</small>
                            </div>
                            <input type="hidden" name="user_type_id" id="userTypeId" value="1">

                            <div class="mb-3">
                                <label class="form-label">Hour Balance</label>
                                <input type="number" class="form-control" name="hour_balance" id="userHourBalance" value="0" min="0">
                                <div class="invalid-feedback" id="error-hour_balance"></div>
                            </div>
                            <!-- Status Field moved to Checkbox for Edit only -->
                            <div class="mb-3 edit-only">
                                <div class="form-check form-switch">
                                    <input class="form-check-input" type="checkbox" id="userSuspendAccount">
                                    <label class="form-check-label text-danger fw-bold" for="userSuspendAccount">Suspend Account</label>
                                </div>
                                <small class="text-muted">Suspended users cannot log in.</small>
                            </div>
                        </div>
                        
                        <!-- ============================
                             ATTENDANT FIELDS
                             ============================ -->
                        <div class="entity-fields fields-attendants" style="display: none;">
                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label class="form-label">First Name <span class="text-danger">*</span></label>
                                    <input type="text" class="form-control" name="first_name" id="attendantFirstName">
                                    <div class="invalid-feedback" id="error-first_name"></div>
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label class="form-label">Last Name <span class="text-danger">*</span></label>
                                    <input type="text" class="form-control" name="last_name" id="attendantLastName">
                                    <div class="invalid-feedback" id="error-last_name"></div>
                                </div>
                            </div>
                            <div class="mb-3">
                                <label class="form-label">Email <span class="text-danger">*</span></label>
                                <input type="email" class="form-control" name="email" id="attendantEmail">
                                <div class="invalid-feedback" id="error-email"></div>
                            </div>
                            <div class="mb-3 password-field">
                                <label class="form-label">Password <span class="text-danger add-only">*</span></label>
                                <input type="password" class="form-control" name="password" id="attendantPassword" minlength="8">
                                <small class="text-muted edit-only">Leave blank to keep current password</small>
                                <small class="text-muted add-only">Minimum 8 characters</small>
                                <div class="invalid-feedback" id="error-password"></div>
                                <div class="password-strength-meter" aria-hidden="true">
                                    <div class="password-strength-bar" id="attendantPasswordStrengthBar"></div>
                                </div>
                                <small class="password-strength-text" id="attendantPasswordStrengthText">Enter a password to check strength.</small>
                            </div>
                            <div class="mb-3">
                                <label class="form-label">Role <span class="text-danger">*</span></label>
                                <select class="form-select" name="user_type_id" id="attendantUserTypeId">
                                    <option value="">Select Role</option>
                                    <!-- Will be populated dynamically -->
                                </select>
                                <div class="invalid-feedback" id="error-user_type_id"></div>
                            </div>
                            <div class="mb-3">
                                <label class="form-label">Assigned Area</label>
                                <select class="form-select" name="assigned_area_id" id="attendantAssignedArea">
                                    <option value="">Select Area (Optional)</option>
                                    <!-- Will be populated dynamically -->
                                </select>
                                <div class="invalid-feedback" id="error-assigned_area_id"></div>
                            </div>
                            <!-- Status Field moved to Checkbox for Edit only -->
                            <div class="mb-3 edit-only">
                                <div class="form-check form-switch">
                                    <input class="form-check-input" type="checkbox" id="attendantSuspendAccount">
                                    <label class="form-check-label text-danger fw-bold" for="attendantSuspendAccount">Suspend Account</label>
                                </div>
                                <small class="text-muted">Suspended staff cannot log in.</small>
                            </div>
                        </div>
                        
                        <!-- ============================
                             SUBSCRIPTION/PLAN FIELDS
                             ============================ -->
                        <div class="entity-fields fields-subscriptions" style="display: none;">
                            <div class="mb-3">
                                <label class="form-label">Plan Name <span class="text-danger">*</span></label>
                                <input type="text" class="form-control" name="plan_name" id="planName" required>
                                <div class="invalid-feedback" id="error-plan_name"></div>
                            </div>
                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label class="form-label">Cost (₱) <span class="text-danger">*</span></label>
                                    <input type="number" class="form-control" name="cost" id="planCost" step="0.01" min="0" required>
                                    <div class="invalid-feedback" id="error-cost"></div>
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label class="form-label">Hours Included <span class="text-danger">*</span></label>
                                    <input type="number" class="form-control" name="number_of_hours" id="planHours" min="1" required>
                                    <div class="invalid-feedback" id="error-number_of_hours"></div>
                                </div>
                            </div>
                            <div class="mb-3">
                                <label class="form-label">Description</label>
                                <textarea class="form-control" name="description" id="planDescription" rows="3"></textarea>
                                <div class="invalid-feedback" id="error-description"></div>
                            </div>
                        </div>
                    <?= form_close() ?>
                </div>
                
                <!-- Confirmation Section (hidden by default) -->
                <div id="crudConfirmSection" style="display: none;">
                    <div class="text-center py-4">
                        <div class="mb-3">
                            <i class="fas fa-exclamation-triangle fa-3x text-warning mb-3"></i>
                        </div>
                        <h5 class="mb-3" id="crudConfirmTitle">Confirm Your Action</h5>
                        <p class="mb-2 fw-semibold" id="crudConfirmMessage">Are you sure you want to proceed?</p>
                        <small class="text-muted" id="crudConfirmDescription">Please review your information before confirming.</small>
                        
                        <!-- Summary of data being submitted -->
                        <div id="crudConfirmSummary" class="mt-4 p-3 bg-light rounded text-start">
                            <!-- Will be populated dynamically -->
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- Modal Footer -->
            <div class="modal-footer" id="crudModalFooter">
                <!-- Normal Footer -->
                <div id="crudNormalFooter">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="button" class="btn btn-maroon" id="crudSubmitBtn">
                        <span id="crudSubmitText">Add</span>
                    </button>
                </div>
                
                <!-- Confirmation Footer (hidden by default) -->
                <div id="crudConfirmFooter" style="display: none; width: 100%;">
                    <div class="text-center mb-2">
                        <p class="mb-0 fw-semibold" id="crudConfirmMessage">Are you sure you want to proceed?</p>
                        <small class="text-muted" id="crudConfirmDescription">Please review your information before confirming.</small>
                    </div>
                    <div class="d-flex justify-content-center gap-2">
                        <button type="button" class="btn btn-secondary px-4" id="crudConfirmCancelBtn">
                            <i class="fas fa-times me-1"></i> No
                        </button>
                        <button type="button" class="btn btn-maroon px-4" id="crudConfirmYesBtn">
                            <i class="fas fa-check me-1"></i> <span id="crudConfirmYesText">Yes</span>
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
#crudFormModal .modal-dialog {
    max-width: 550px;
}

#crudFormModal .modal-content {
    border-radius: 12px;
    border: none;
    box-shadow: 0 10px 40px rgba(0, 0, 0, 0.2);
}

#crudFormModal .modal-header {
    border-radius: 12px 12px 0 0;
    padding: 1rem 1.5rem;
}

#crudFormModal .modal-title {
    font-weight: 600;
    font-size: 1.1rem;
}

#crudFormModal .modal-body {
    padding: 1.5rem;
    max-height: 70vh;
    overflow-y: auto;
}

#crudFormModal .form-label {
    font-weight: 500;
    color: #495057;
    margin-bottom: 0.4rem;
    font-size: 0.9rem;
}

#crudFormModal .form-control,
#crudFormModal .form-select {
    padding: 0.6rem 0.85rem;
    font-size: 0.95rem;
    border-radius: 8px;
    border: 1.5px solid #dee2e6;
    transition: all 0.2s ease;
}

#crudFormModal .form-control:focus,
#crudFormModal .form-select:focus {
    border-color: #800000;
    box-shadow: 0 0 0 3px rgba(128, 0, 0, 0.1);
}

#crudFormModal .mb-3 {
    margin-bottom: 1.25rem !important;
}

#crudFormModal .row {
    margin-left: -0.5rem;
    margin-right: -0.5rem;
}

#crudFormModal .row > [class*="col-"] {
    padding-left: 0.5rem;
    padding-right: 0.5rem;
}

#crudFormModal small.text-muted {
    font-size: 0.8rem;
    margin-top: 0.3rem;
    display: block;
}

#crudFormModal .modal-footer {
    padding: 1rem 1.5rem;
    border-top: 1px solid #eee;
    gap: 0.5rem;
    flex-direction: column;
}

#crudFormModal .modal-footer .btn {
    padding: 0.5rem 1.25rem;
    font-weight: 500;
    border-radius: 8px;
}

#crudFormModal #crudNormalFooter {
    display: flex;
    justify-content: flex-end;
    gap: 0.5rem;
    width: 100%;
}

#crudFormModal #crudConfirmFooter {
    width: 100%;
    text-align: center;
}

#crudFormModal #crudConfirmMessage {
    font-size: 1rem;
    color: #333;
    margin-bottom: 0.25rem;
}

#crudFormModal #crudConfirmDescription {
    font-size: 0.85rem;
    color: #6c757d;
}

#crudFormModal #crudConfirmFooter .d-flex {
    margin-top: 1rem;
}

/* Dark Mode */
[data-bs-theme="dark"] #crudFormModal #crudConfirmMessage {
    color: var(--text-color, #e8d4d4);
}

[data-bs-theme="dark"] #crudFormModal .modal-footer {
    border-top-color: var(--border-color, rgba(128, 0, 0, 0.3));
}

#crudFormModal.mode-edit .add-only {
    display: none !important;
}

#crudFormModal.mode-add .edit-only {
    display: none !important;
}

#crudFormModal.mode-edit .password-field input {
    required: false;
}

#crudFormModal .invalid-feedback {
    display: none;
    width: 100%;
    margin-top: 0.35rem;
    font-size: 0.8rem;
    color: #dc3545;
    padding-left: 2px;
}

/* Show error when input has is-invalid class */
#crudFormModal .is-invalid ~ .invalid-feedback,
#crudFormModal .is-invalid + .invalid-feedback,
#crudFormModal .is-invalid + small + .invalid-feedback,
#crudFormModal .is-invalid + small + small + .invalid-feedback {
    display: block !important;
}

/* Red border for invalid inputs */
#crudFormModal .form-control.is-invalid,
#crudFormModal .form-select.is-invalid {
    border-color: #dc3545 !important;
    padding-right: calc(1.5em + 0.75rem);
    background-image: url("data:image/svg+xml,%3csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 12 12' width='12' height='12' fill='none' stroke='%23dc3545'%3e%3ccircle cx='6' cy='6' r='4.5'/%3e%3cpath d='m5.8 3.6 .4.4.4-.4m0 4.8-.4-.4-.4.4'/%3e%3c/svg%3e");
    background-repeat: no-repeat;
    background-position: right calc(0.375em + 0.1875rem) center;
    background-size: calc(0.75em + 0.375rem) calc(0.75em + 0.375rem);
    box-shadow: 0 0 0 3px rgba(220, 53, 69, 0.15) !important;
}

#crudFormModal .form-control.is-invalid:focus,
#crudFormModal .form-select.is-invalid:focus {
    border-color: #dc3545 !important;
    box-shadow: 0 0 0 3px rgba(220, 53, 69, 0.25) !important;
}

/* Confirmation Section Styles */
#crudConfirmSection {
    background: linear-gradient(135deg, #f8f9fa 0%, #e9ecef 100%);
    border-radius: 8px;
    margin: -1rem;
    padding: 2rem;
}

#crudConfirmSection .fa-exclamation-triangle {
    color: #ffc107;
    animation: pulse 2s infinite;
}

@keyframes pulse {
    0% { transform: scale(1); }
    50% { transform: scale(1.1); }
    100% { transform: scale(1); }
}

#crudConfirmSummary {
    background: white;
    border: 1px solid #dee2e6;
    border-radius: 6px;
    font-size: 0.9rem;
}

#crudConfirmSummary .row {
    border-bottom: 1px solid #f1f3f4;
    padding: 0.5rem 0;
}

#crudConfirmSummary .row:last-child {
    border-bottom: none;
    padding-bottom: 0;
}

#crudConfirmSummary .row:first-child {
    padding-top: 0;
}

#crudConfirmSummary .col-md-6:first-child {
    font-weight: 600;
    color: #495057;
}

#crudConfirmSummary .col-md-6:last-child {
    color: #212529;
}

/* Dark mode for confirmation section */
[data-bs-theme="dark"] #crudConfirmSection {
    background: linear-gradient(135deg, #2d3436 0%, #343a40 100%);
    color: #e8d4d4;
}

[data-bs-theme="dark"] #crudConfirmSummary {
    background: #343a40;
    border-color: #495057;
    color: #e8d4d4;
}

[data-bs-theme="dark"] #crudConfirmSummary .col-md-6:first-child {
    color: #adb5bd;
}

[data-bs-theme="dark"] #crudConfirmSummary .col-md-6:last-child {
    color: #e8d4d4;
}

[data-bs-theme="dark"] #crudConfirmSummary .row {
    border-bottom-color: #495057;
}
</style>

