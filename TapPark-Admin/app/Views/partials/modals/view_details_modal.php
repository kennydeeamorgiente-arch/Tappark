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
                     USER DETAILS
                     ============================ -->
                <div class="view-content view-users" style="display: none;">
                    <div class="row">
                        <div class="col-md-4 text-center mb-4">
                            <img src="" alt="Profile" id="viewUserAvatar" class="rounded-circle mb-3" 
                                 style="width: 120px; height: 120px; object-fit: cover; border: 3px solid #800000;">
                            <h5 id="viewUserFullName" class="mb-1">-</h5>
                            <span class="badge" id="viewUserStatusBadge">-</span>
                        </div>
                        <div class="col-md-8">
                            <table class="table table-borderless">
                                <tr>
                                    <td class="text-muted" width="40%"><i class="fas fa-id-card me-2"></i>User ID</td>
                                    <td><strong id="viewUserId">-</strong></td>
                                </tr>
                                <tr>
                                    <td class="text-muted"><i class="fas fa-envelope me-2"></i>Email</td>
                                    <td id="viewUserEmail">-</td>
                                </tr>
                                <tr>
                                    <td class="text-muted"><i class="fas fa-user-tag me-2"></i>User Type</td>
                                    <td id="viewUserType">-</td>
                                </tr>
                                <tr>
                                    <td class="text-muted"><i class="fas fa-clock me-2"></i>Hour Balance</td>
                                    <td id="viewUserHourBalance">-</td>
                                </tr>
                                <tr>
                                    <td class="text-muted"><i class="fas fa-circle me-2"></i>Online Status</td>
                                    <td id="viewUserOnline">-</td>
                                </tr>
                                <tr>
                                    <td class="text-muted"><i class="fas fa-calendar me-2"></i>Registered</td>
                                    <td id="viewUserCreatedAt">-</td>
                                </tr>
                                <tr>
                                    <td class="text-muted"><i class="fas fa-history me-2"></i>Last Activity</td>
                                    <td id="viewUserLastActivity">-</td>
                                </tr>
                            </table>
                        </div>
                    </div>
                </div>
                
                <!-- ============================
                     ATTENDANT DETAILS
                     ============================ -->
                <div class="view-content view-attendants" style="display: none;">
                    <div class="row">
                        <div class="col-md-4 text-center mb-4">
                            <img src="" alt="Profile" id="viewAttendantAvatar" class="rounded-circle mb-3" 
                                 style="width: 120px; height: 120px; object-fit: cover; border: 3px solid #800000;">
                            <h5 id="viewAttendantFullName" class="mb-1">-</h5>
                            <span class="badge" id="viewAttendantStatusBadge">-</span>
                        </div>
                        <div class="col-md-8">
                            <table class="table table-borderless">
                                <tr>
                                    <td class="text-muted" width="40%"><i class="fas fa-id-card me-2"></i>Staff ID</td>
                                    <td><strong id="viewAttendantId">-</strong></td>
                                </tr>
                                <tr>
                                    <td class="text-muted"><i class="fas fa-envelope me-2"></i>Email</td>
                                    <td id="viewAttendantEmail">-</td>
                                </tr>
                                <tr>
                                    <td class="text-muted"><i class="fas fa-user-tag me-2"></i>Role</td>
                                    <td id="viewAttendantRole">-</td>
                                </tr>
                                <tr>
                                    <td class="text-muted"><i class="fas fa-map-marker-alt me-2"></i>Assigned Area</td>
                                    <td id="viewAttendantArea">-</td>
                                </tr>
                                <tr>
                                    <td class="text-muted"><i class="fas fa-circle me-2"></i>Online Status</td>
                                    <td id="viewAttendantOnline">-</td>
                                </tr>
                                <tr>
                                    <td class="text-muted"><i class="fas fa-calendar me-2"></i>Hired Date</td>
                                    <td id="viewAttendantCreatedAt">-</td>
                                </tr>
                                <tr>
                                    <td class="text-muted"><i class="fas fa-history me-2"></i>Last Activity</td>
                                    <td id="viewAttendantLastActivity">-</td>
                                </tr>
                            </table>
                        </div>
                    </div>
                </div>
                
                <!-- ============================
                     SUBSCRIPTION/PLAN DETAILS
                     ============================ -->
                <div class="view-content view-subscriptions" style="display: none;">
                    <div class="row">
                        <div class="col-md-4 text-center mb-4">
                            <div class="plan-icon-wrapper mb-3">
                                <i class="fas fa-crown"></i>
                            </div>
                            <h5 id="viewPlanName" class="mb-1">-</h5>
                            <span class="badge bg-maroon" id="viewPlanIdBadge">Plan ID: -</span>
                        </div>
                        <div class="col-md-8">
                            <table class="table table-borderless">
                                <tr>
                                    <td class="text-muted" width="40%"><i class="fas fa-coins me-2"></i>Cost</td>
                                    <td><strong id="viewPlanPrice">-</strong></td>
                                </tr>
                                <tr>
                                    <td class="text-muted"><i class="fas fa-clock me-2"></i>Hours Included</td>
                                    <td id="viewPlanHours">-</td>
                                </tr>
                                <tr>
                                    <td class="text-muted"><i class="fas fa-calculator me-2"></i>Cost per Hour</td>
                                    <td id="viewPlanCostPerHour">-</td>
                                </tr>
                                <tr>
                                    <td class="text-muted"><i class="fas fa-users me-2"></i>Total Subscribers</td>
                                    <td id="viewPlanSubscribers">-</td>
                                </tr>
                                <tr>
                                    <td class="text-muted"><i class="fas fa-user-check me-2"></i>Active Subscribers</td>
                                    <td id="viewPlanActive">-</td>
                                </tr>
                                <tr>
                                    <td class="text-muted align-top"><i class="fas fa-align-left me-2"></i>Description</td>
                                    <td id="viewPlanDescription" class="text-muted">-</td>
                                </tr>
                            </table>
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

