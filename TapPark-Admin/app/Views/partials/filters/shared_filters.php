<!-- ============================================
   SHARED FILTER COMPONENT
   One filter card for all entity types
   Uses data-entity to show/hide relevant filters
   ============================================ -->
<div class="card shadow-sm mb-4" id="sharedFiltersCard">
    <div class="card-body" style="padding: 1.5rem;">
        <!-- Hidden field to store current entity type -->
        <input type="hidden" id="filterEntityType" value="">
        
        <div class="filter-container-full">
            <!-- ============================
                 SEARCH (All entities)
                 ============================ -->
            <div class="filter-field filter-search">
                <label class="form-label">
                    <i class="fas fa-search me-1"></i>Search
                </label>
                <input type="text" class="form-control" id="sharedSearchInput" placeholder="Search subscription plans...">
            </div>
            
            <!-- ============================
                 VEHICLE TYPE (Guest Bookings)
                 ============================ -->
            <div class="filter-field filter-vehicle-type" style="display: none;">
                <label class="form-label">
                    <i class="fas fa-car me-1"></i>Vehicle Type
                </label>
                <select class="form-select" id="sharedFilterVehicleType">
                    <option value="">All Vehicles</option>
                    <!-- Will be populated dynamically -->
                </select>
            </div>
            
            <!-- ============================
                 DATE RANGE (Guest Bookings)
                 ============================ -->
            <div class="filter-field filter-date-range" style="display: none;">
                <label class="form-label">
                    <i class="fas fa-calendar me-1"></i>Date Range
                </label>
                <select class="form-select" id="sharedFilterDateRange">
                    <option value="">All Dates</option>
                    <option value="today">Today</option>
                    <option value="yesterday">Yesterday</option>
                    <option value="week">This Week</option>
                    <option value="month">This Month</option>
                    <option value="last30">Last 30 Days</option>
                    <option value="last90">Last 90 Days</option>
                </select>
            </div>
            
            <!-- ============================
                 USER TYPE (Users & Attendants)
                 ============================ -->
            <div class="filter-field filter-user-type" style="display: none;">
                <label class="form-label">
                    <i class="fas fa-user-tag me-1"></i>User Type
                </label>
                <select class="form-select" id="sharedFilterUserType">
                    <option value="">All Types</option>
                    <!-- Will be populated dynamically -->
                </select>
            </div>
            
            <!-- ============================
                 STATUS (Users & Attendants)
                 ============================ -->
            <div class="filter-field filter-status" style="display: none;">
                <label class="form-label">
                    <i class="fas fa-toggle-on me-1"></i>Status
                </label>
                <select class="form-select" id="sharedFilterStatus">
                    <option value="">All Status</option>
                    <option value="active">Active</option>
                    <option value="inactive">Inactive</option>
                    <option value="suspended">Suspended</option>
                </select>
            </div>
            
            <!-- ============================
                 ONLINE STATUS (Users & Attendants)
                 ============================ -->
            <div class="filter-field filter-online-status" style="display: none;">
                <label class="form-label">
                    <i class="fas fa-circle me-1"></i>Login Status
                </label>
                <select class="form-select" id="sharedFilterOnline">
                    <option value="">All</option>
                    <option value="1">Online</option>
                    <option value="0">Offline</option>
                </select>
            </div>
            
            <!-- ============================
                 ROLE (Staff)
                 ============================ -->
            <div class="filter-field filter-role" style="display: none;">
                <label class="form-label">
                    <i class="fas fa-id-badge me-1"></i>Role
                </label>
                <select class="form-select" id="sharedFilterRole">
                    <option value="">All Roles</option>
                    <!-- Will be populated dynamically -->
                </select>
            </div>
            
            <!-- ============================
                 ASSIGNED AREA (Staff)
                 ============================ -->
            <div class="filter-field filter-area" style="display: none;">
                <label class="form-label">
                    <i class="fas fa-map-marker-alt me-1"></i>Assigned Area
                </label>
                <select class="form-select" id="sharedFilterArea">
                    <option value="">All Areas</option>
                    <!-- Will be populated dynamically -->
                </select>
            </div>
            
            <!-- ============================
                 PRICE RANGE (Subscriptions)
                 ============================ -->
            <div class="filter-field filter-price-range" style="display: none;">
                <label class="form-label">
                    <i class="fas fa-dollar-sign me-1"></i>Price Range
                </label>
                <select class="form-select" id="sharedFilterPriceRange">
                    <option value="">All Prices</option>
                    <option value="0-100">₱0 - ₱100</option>
                    <option value="100-500">₱100 - ₱500</option>
                    <option value="500-1000">₱500 - ₱1,000</option>
                    <option value="1000-5000">₱1,000 - ₱5,000</option>
                    <option value="5000+">₱5,000+</option>
                </select>
            </div>
            
            <!-- ============================
                 HOURS RANGE (Subscriptions)
                 ============================ -->
            <div class="filter-field filter-hours-range" style="display: none;">
                <label class="form-label">
                    <i class="fas fa-clock me-1"></i>Hours Range
                </label>
                <select class="form-select" id="sharedFilterHoursRange">
                    <option value="">All Hours</option>
                    <option value="1-10">1 - 10 hours</option>
                    <option value="10-50">10 - 50 hours</option>
                    <option value="50-100">50 - 100 hours</option>
                    <option value="100-500">100 - 500 hours</option>
                    <option value="500+">500+ hours</option>
                </select>
            </div>
            
            <!-- ============================
                 ATTENDANT (Guest Bookings)
                 ============================ -->
            <div class="filter-field filter-attendant" style="display: none;">
                <label class="form-label">
                    <i class="fas fa-user-tie me-1"></i>Attendant
                </label>
                <select class="form-select" id="sharedFilterAttendant">
                    <option value="">All Attendants</option>
                    <!-- Will be populated dynamically -->
                </select>
            </div>
            
            <!-- Action Buttons - Hidden by default, shown when filters are active -->
            <div class="filter-actions filter-actions-hidden" id="filterActionsContainer">
                <!-- Apply Filter Button -->
                <button class="btn btn-primary btn-sm" id="sharedApplyFiltersBtn">
                    <i class="fas fa-filter me-1"></i>Apply Filter
                </button>
                
                <!-- Clear Filters button -->
                <button class="btn btn-secondary btn-sm" id="sharedClearFiltersBtn">
                    <i class="fas fa-times me-1"></i>Clear Filters
                </button>
            </div>
        </div>
    </div>
</div>

<style>
/* Filter field spacing */
#sharedFiltersCard .filter-field {
    transition: all 0.3s ease;
    flex: 1;
    min-width: 200px;
}

/* Filter Actions Visibility */
.filter-actions-hidden {
    opacity: 0;
    visibility: hidden;
    transition: opacity 0.3s ease, visibility 0.3s ease;
    height: 0;
    overflow: hidden;
}

.filter-actions-visible {
    opacity: 1;
    visibility: visible;
    transition: opacity 0.3s ease, visibility 0.3s ease;
    height: auto;
    overflow: visible;
}

/* Smooth show/hide animation */
#sharedFiltersCard .filter-field[style*="display: none"] {
    opacity: 0;
    height: 0;
    overflow: hidden;
    margin: 0;
    padding: 0;
}

#sharedFiltersCard .filter-field:not([style*="display: none"]) {
    opacity: 1;
    height: auto;
}

/* Action Buttons - Better spacing */
#sharedFiltersCard .filter-actions {
    flex: 0 0 auto;
    display: flex;
    gap: 0.5rem;
    align-items: center;
    min-width: fit-content;
}

/* Override page-specific .btn-sm styles for shared filter buttons */
#sharedFiltersCard .btn-sm,
#sharedFiltersCard button.btn-sm {
    padding: 0.5rem 1rem !important;
    min-width: 120px !important;
    font-weight: 500 !important;
    border-radius: 8px !important;
    transition: all 0.2s ease !important;
}

#sharedFiltersCard .btn-sm:hover {
    transform: translateY(-1px) !important;
    box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1) !important;
}

/* Responsive button spacing */
@media (max-width: 768px) {
    #sharedFiltersCard .filter-container-full {
        flex-direction: column;
        align-items: stretch;
    }
    
    #sharedFiltersCard .filter-field {
        min-width: auto;
    }
    
    #sharedFiltersCard .filter-actions {
        justify-content: center;
        margin-top: 0.5rem;
    }
}
</style>

