<!-- ============================================
   UNIFIED VIEW DETAILS MODAL
   One modal for viewing details across all entities
   ============================================ -->
<div class="modal fade" id="viewDetailsModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered">
        <div class="modal-content">
            <!-- Modal Header -->
            <div class="modal-header" style="background: linear-gradient(135deg, #800000 0%, #990000 100%);">
                <h5 class="modal-title text-white" id="viewModalTitle">
                    <i class="fas fa-eye me-2"></i>
                    <span id="viewModalTitleText">View Details</span>
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            
            <!-- Modal Body -->
            <div class="modal-body" id="viewDetailsContent">
                <!-- Loading State -->
                <div class="text-center py-4" id="viewDetailsLoading">
                    <div class="spinner-border text-primary" role="status">
                        <span class="visually-hidden">Loading...</span>
                    </div>
                    <p class="mt-2 text-muted">Loading details...</p>
                </div>
                
                <!-- ============================
                     USER DETAILS (Subscriber)
                     ============================ -->
                <div class="view-content view-users" style="display: none;">
                    <div class="row">
                        <div class="col-md-4 text-center mb-4 border-end">
                            <img src="" alt="Profile" id="viewUserAvatar" class="rounded-circle mb-3 shadow-sm" 
                                 style="width: 120px; height: 120px; object-fit: cover; border: 3px solid #800000;">
                            <h5 id="viewUserFullName" class="mb-1 text-dark fw-bold">-</h5>
                            <div id="viewUserStatusBadge" class="mb-2">-</div>
                            <span class="badge bg-maroon mb-3" id="viewUserId">-</span>
                            
                            <div class="p-2 bg-light rounded-3 mt-2">
                                <small class="text-muted d-block">Account Type</small>
                                <span id="viewUserType" class="fw-bold">Subscriber</span>
                            </div>
                        </div>
                        <div class="col-md-8 px-4">
                            <div class="row g-3">
                                <!-- Contact Info -->
                                <div class="col-12">
                                    <div class="detail-card">
                                        <h6 class="text-maroon fw-bold mb-3"><i class="fas fa-id-card-alt me-2"></i>Contact Information</h6>
                                        <div class="row">
                                            <div class="col-sm-6 mb-2">
                                                <small class="text-muted d-block">Email Address</small>
                                                <span id="viewUserEmail" class="fw-semibold">-</span>
                                            </div>
                                            <div class="col-sm-6 mb-2">
                                                <small class="text-muted d-block">Member Since</small>
                                                <span id="viewUserCreatedAt" class="fw-semibold">-</span>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                
                                <!-- Balance & Activity -->
                                <div class="col-md-6">
                                    <div class="detail-card h-100 bg-light-maroon border-0 shadow-none">
                                        <h6 class="text-maroon fw-bold mb-3"><i class="fas fa-clock me-2"></i>Parking Balance</h6>
                                        <div class="display-6 fw-bold text-dark" id="viewUserHourBalance">-</div>
                                        <small class="text-muted">Total hours available</small>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="detail-card h-100 border bg-white">
                                        <h6 class="text-primary fw-bold mb-3"><i class="fas fa-signal me-2"></i>Presence</h6>
                                        <div id="viewUserOnline" class="mb-2">-</div>
                                        <div class="mt-2">
                                            <small class="text-muted d-block">Last Activity</small>
                                            <span id="viewUserLastActivity" class="fw-semibold small">-</span>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                
                <!-- ============================
                     ATTENDANT DETAILS (Staff)
                     ============================ -->
                <div class="view-content view-attendants" style="display: none;">
                    <div class="row">
                        <div class="col-md-4 text-center mb-4 border-end">
                            <img src="" alt="Profile" id="viewAttendantAvatar" class="rounded-circle mb-3 shadow-sm" 
                                 style="width: 120px; height: 120px; object-fit: cover; border: 3px solid #800000;">
                            <h5 id="viewAttendantFullName" class="mb-1 text-dark fw-bold">-</h5>
                            <div id="viewAttendantStatusBadge" class="mb-2">-</div>
                            <span class="badge bg-secondary mb-3" id="viewAttendantId">Staff ID: -</span>
                        </div>
                        <div class="col-md-8 px-4">
                            <div class="row g-3">
                                <!-- Role & Area -->
                                <div class="col-12">
                                    <div class="detail-card bg-light-blue border-0 shadow-none">
                                        <div class="row align-items-center">
                                            <div class="col-sm-6">
                                                <h6 class="text-info fw-bold mb-2"><i class="fas fa-user-shield me-2"></i>Position</h6>
                                                <div id="viewAttendantRole" class="h6 fw-bold">-</div>
                                            </div>
                                            <div class="col-sm-6 border-start ps-4">
                                                <h6 class="text-primary fw-bold mb-2"><i class="fas fa-map-marked-alt me-2"></i>Assigned Area</h6>
                                                <div id="viewAttendantArea" class="h6 fw-bold">-</div>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <!-- Contact & Stats -->
                                <div class="col-md-6">
                                    <div class="detail-card bg-white border h-100">
                                        <h6 class="text-maroon fw-bold mb-3"><i class="fas fa-envelope me-2"></i>Contact</h6>
                                        <small class="text-muted d-block">Work Email</small>
                                        <div id="viewAttendantEmail" class="fw-semibold text-truncate">-</div>
                                        <hr class="my-2">
                                        <small class="text-muted d-block">Hired Date</small>
                                        <div id="viewAttendantCreatedAt" class="fw-semibold">-</div>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="detail-card bg-white border h-100">
                                        <h6 class="text-success fw-bold mb-3"><i class="fas fa-user-clock me-2"></i>Availability</h6>
                                        <div id="viewAttendantOnline" class="mb-3">-</div>
                                        <small class="text-muted d-block">Last Active</small>
                                        <div id="viewAttendantLastActivity" class="fw-semibold small">-</div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                
                <!-- ============================
                     SUBSCRIPTION/PLAN DETAILS
                     ============================ -->
                <div class="view-content view-subscriptions" style="display: none;">
                    <div class="row">
                        <div class="col-md-4 text-center mb-4 border-end">
                            <div class="plan-icon-wrapper mb-3 shadow">
                                <i class="fas fa-crown"></i>
                            </div>
                            <h4 id="viewPlanName" class="mb-1 text-dark fw-bold">-</h4>
                            <span class="badge bg-maroon px-3 py-2 rounded-pill mt-2" id="viewPlanIdBadge">Plan ID: -</span>
                        </div>
                        <div class="col-md-8 px-4">
                            <div class="row g-3">
                                <!-- Pricing Card -->
                                <div class="col-12">
                                    <div class="detail-card bg-light-maroon border-0 shadow-none text-center py-4">
                                        <div class="row align-items-center">
                                            <div class="col-4">
                                                <small class="text-muted d-block">Flat Cost</small>
                                                <div class="h3 fw-bold text-maroon mb-0" id="viewPlanPrice">-</div>
                                            </div>
                                            <div class="col-4 border-start border-end">
                                                <small class="text-muted d-block">Included Hours</small>
                                                <div class="h3 fw-bold text-dark mb-0" id="viewPlanHours">-</div>
                                            </div>
                                            <div class="col-4">
                                                <small class="text-muted d-block">Cost/Hour</small>
                                                <div class="h3 fw-bold text-muted mb-0" id="viewPlanCostPerHour">-</div>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <!-- Statistics Row -->
                                <div class="col-md-6">
                                    <div class="detail-card bg-white border h-100">
                                        <h6 class="text-primary fw-bold mb-3"><i class="fas fa-users me-2"></i>Adoption</h6>
                                        <div class="d-flex justify-content-between mb-2">
                                            <span>Total Users:</span>
                                            <strong id="viewPlanSubscribers" class="text-dark">-</strong>
                                        </div>
                                        <div class="d-flex justify-content-between">
                                            <span>Active Now:</span>
                                            <strong id="viewPlanActive" class="text-success">-</strong>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="detail-card bg-white border h-100">
                                        <h6 class="text-secondary fw-bold mb-2"><i class="fas fa-quote-left me-2"></i>Description</h6>
                                        <p id="viewPlanDescription" class="small text-muted mb-0">-</p>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- ============================
                     WALK-IN GUEST DETAILS
                     ============================ -->
                <div class="view-content view-guests" style="display: none;">
                    <div class="row">
                        <div class="col-md-4 text-center mb-4">
                            <img src="" alt="Profile" id="viewGuestAvatar" class="rounded-circle mb-3" 
                                 style="width: 120px; height: 120px; object-fit: cover; border: 3px solid #800000;">
                            <h5 id="viewGuestFullName" class="mb-1">-</h5>
                            <span class="badge bg-maroon" id="viewGuestIdDisplay">-</span>
                        </div>
                        <div class="col-md-8">
                            <div class="row g-3">
                                <!-- Basic Info Card -->
                                <div class="col-12">
                                    <div class="card border-0 bg-light-maroon p-3 rounded-3 shadow-none">
                                        <h6 class="text-maroon mb-3 fw-bold"><i class="fas fa-info-circle me-2"></i>Guest Information</h6>
                                        <div class="row">
                                            <div class="col-6 mb-2">
                                                <small class="text-muted d-block">Email</small>
                                                <span id="viewGuestEmail" class="fw-semibold">-</span>
                                            </div>
                                            <div class="col-6 mb-2">
                                                <small class="text-muted d-block">Registered</small>
                                                <span id="viewGuestCreatedAt" class="fw-semibold">-</span>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <!-- Vehicle & Reservation Row -->
                                <div class="col-md-6">
                                    <div class="card border-0 bg-light p-3 h-100 rounded-3 shadow-none">
                                        <h6 class="text-primary mb-3 fw-bold"><i class="fas fa-car me-2"></i>Vehicle Details</h6>
                                        <div class="mb-2">
                                            <small class="text-muted d-block">Plate Number</small>
                                            <span id="viewGuestPlate" class="h5 fw-bold text-dark">-</span>
                                        </div>
                                        <div class="mb-2">
                                            <small class="text-muted d-block">Type</small>
                                            <span id="viewGuestVehicleType" class="badge bg-secondary">-</span>
                                        </div>
                                        <div>
                                            <small class="text-muted d-block">Brand & Color</small>
                                            <span id="viewGuestVehicleInfo" class="fw-semibold">-</span>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="card border-0 bg-light p-3 h-100 rounded-3 shadow-none">
                                        <h6 class="text-info mb-3 fw-bold"><i class="fas fa-calendar-check me-2"></i>Reservation</h6>
                                        <div class="mb-2">
                                            <small class="text-muted d-block">Status</small>
                                            <div id="viewGuestResStatus">-</div>
                                        </div>
                                        <div class="mb-2">
                                            <small class="text-muted d-block">Start Time</small>
                                            <span id="viewGuestStartTime" class="fw-semibold small">-</span>
                                        </div>
                                        <div>
                                            <small class="text-muted d-block">End Time</small>
                                            <span id="viewGuestEndTime" class="fw-semibold small">-</span>
                                        </div>
                                    </div>
                                </div>

                                <!-- Attendant Info -->
                                <div class="col-12 mt-3 text-center">
                                    <div class="p-2 border rounded bg-white">
                                        <small class="text-muted me-2">Processed by:</small>
                                        <span id="viewGuestAttendant" class="fw-bold">-</span>
                                    </div>
                                </div>

                                <!-- QR Code Section -->
                                <div class="col-12 mt-4 text-center" id="viewGuestQRSection" style="display: none;">
                                    <p class="text-muted mb-2"><i class="fas fa-qrcode me-2"></i>Reservation QR Code</p>
                                    <div class="qr-code-container d-inline-block p-2 bg-white rounded shadow-sm border">
                                        <img src="" alt="QR Code" id="viewGuestQRCode" class="img-fluid" style="max-width: 150px;">
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
/* View Details Modal Styles */
#viewDetailsModal .modal-content {
    border-radius: 12px;
    border: none;
    box-shadow: 0 10px 40px rgba(0, 0, 0, 0.2);
    overflow: hidden;
}

#viewDetailsModal .modal-header {
    border: none;
    padding: 1rem 1.5rem;
    border-radius: 12px 12px 0 0;
}

#viewDetailsModal .modal-title {
    font-weight: 600;
}

#viewDetailsModal .modal-body {
    padding: 1.5rem;
    max-height: 70vh;
    overflow-y: auto;
}

#viewDetailsModal .table {
    margin-bottom: 0;
}

#viewDetailsModal .table td {
    padding: 0.6rem 0.5rem;
    border: none;
    vertical-align: middle;
}

#viewDetailsModal .table td:first-child {
    width: 40%;
}

#viewDetailsModal .modal-footer {
    padding: 1rem 1.5rem;
    border-top: 1px solid #eee;
    gap: 0.5rem;
}

#viewDetailsModal .modal-footer .btn {
    border-radius: 8px;
    font-weight: 500;
    padding: 0.5rem 1.25rem;
}

/* Guest Layout Specific Styles */
.bg-light-maroon {
    background-color: rgba(128, 0, 0, 0.05);
}
.text-maroon {
    color: #800000;
}
.bg-maroon {
    background-color: #800000;
}
.view-guests .card {
    transition: transform 0.2s ease;
}
.view-guests .card:hover {
    transform: translateY(-2px);
}
.view-guests .qr-code-container img {
    border: 1px solid #eee;
}

/* Detail Card Styles */
.detail-card {
    background: #fff;
    border: 1px solid #edf2f9;
    padding: 1.25rem;
    border-radius: 12px;
    box-shadow: 0 2px 4px rgba(0,0,0,0.02);
}

.bg-light-maroon {
    background-color: rgba(128, 0, 0, 0.05) !important;
}

.bg-light-blue {
    background-color: #f0f7ff !important;
}

.text-maroon {
    color: #800000 !important;
}

.border-maroon {
    border-color: #800000 !important;
}

/* Plan Styles */
.view-subscriptions .detail-card {
    transition: all 0.3s ease;
}

/* Subscription Plan Icon Wrapper */
#viewDetailsModal .plan-icon-wrapper {
    width: 100px;
    height: 100px;
    border-radius: 50%;
    background: linear-gradient(135deg, #800000 0%, #990000 100%);
    display: flex;
    align-items: center;
    justify-content: center;
    margin: 0 auto;
    border: 3px solid rgba(128, 0, 0, 0.2);
}

#viewDetailsModal .plan-icon-wrapper i {
    font-size: 3rem;
    color: white;
}

[data-bs-theme="dark"] #viewDetailsModal .plan-icon-wrapper {
    background: linear-gradient(135deg, #4a1a1a 0%, #5a2525 100%);
    border-color: rgba(128, 0, 0, 0.3);
}
</style>

