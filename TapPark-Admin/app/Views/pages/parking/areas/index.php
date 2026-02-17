<div class="container-fluid">
    <!-- Enhanced Page Header -->
    <div class="card mb-4 border-0 shadow-sm">
        <div class="card-body py-4">
            <div class="d-flex justify-content-between align-items-center flex-wrap gap-3">
                <div>
                    <h2 class="mb-2 fw-bold">
                        <i class="fas fa-parking me-3 text-primary"></i>Parking Area & Section Management
                    </h2>
                    <p class="mb-0 text-muted">Manage parking areas and their sections</p>
                </div>
                <div class="d-flex gap-2">
                    <button class="btn btn-secondary" id="refreshAreasBtn" title="Refresh">
                        <i class="fas fa-sync-alt"></i>
                    </button>
                    <button class="btn btn-maroon" id="addAreaBtn">
                        <i class="fas fa-plus me-1"></i> Add New Area
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- Search & Filters -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="card shadow-sm" id="areasFiltersCard">
                <div class="card-body">
                    <div class="compact-filter-row">
                        <div class="compact-filter-field compact-filter-search">
                            <label class="form-label"><i class="fas fa-search me-2"></i>Search Areas</label>
                            <div class="input-group">
                                <span class="input-group-text"><i class="fas fa-search"></i></span>
                                <input type="text" class="form-control" id="searchInput" placeholder="Search areas...">
                            </div>
                        </div>
                        <div class="compact-filter-field compact-filter-medium" style="flex: 1 1 210px;">
                            <label class="form-label"><i class="fas fa-filter me-2"></i>Status</label>
                            <select class="form-select" id="filterStatus">
                                <option value="">All Status</option>
                                <option value="active">Active</option>
                                <option value="inactive">Inactive</option>
                            </select>
                        </div>
                        <div class="compact-filter-actions filter-actions filter-actions-visible" id="areasFilterActions">
                                <button class="btn btn-primary" id="applyAreasFilterBtn">
                                    <i class="fas fa-filter me-1"></i>Apply
                                </button>
                                <button class="btn btn-secondary" id="clearFiltersBtn">
                                    <i class="fas fa-times me-1"></i>Clear
                                </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Enhanced Stats Cards -->
    <div class="row mb-4 g-3">
        <div class="col-xl-3 col-lg-3 col-md-6 col-sm-6">
            <div class="card stats-card-modern h-100 border-0 shadow-sm stats-card-maroon">
                <div class="card-body text-white position-relative overflow-hidden p-4">
                    <div class="d-flex align-items-center justify-content-between">
                        <div class="flex-grow-1">
                            <h5 class="mb-1 opacity-75 fw-semibold">Total Areas</h5>
                            <h2 class="mb-0 fw-bold text-white" id="statTotalAreas">0</h2>
                            <small class="opacity-75 d-block">
                                <i class="fas fa-map-marked-alt me-1"></i>
                                Parking areas
                            </small>
                        </div>
                        <div class="stats-icon">
                            <i class="fas fa-map-marked-alt fa-2x opacity-75"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-xl-3 col-lg-3 col-md-6 col-sm-6">
            <div class="card stats-card-modern h-100 border-0 shadow-sm stats-card-gray">
                <div class="card-body text-white position-relative overflow-hidden p-4">
                    <div class="d-flex align-items-center justify-content-between">
                        <div class="flex-grow-1">
                            <h5 class="mb-1 opacity-75 fw-semibold">Total Sections</h5>
                            <h2 class="mb-0 fw-bold text-white" id="statTotalSections">0</h2>
                            <small class="opacity-75 d-block">
                                <i class="fas fa-th me-1"></i>
                                All sections
                            </small>
                        </div>
                        <div class="stats-icon">
                            <i class="fas fa-th fa-2x opacity-75"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-xl-3 col-lg-3 col-md-6 col-sm-6">
            <div class="card stats-card-modern h-100 border-0 shadow-sm stats-card-gray">
                <div class="card-body text-white position-relative overflow-hidden p-4">
                    <div class="d-flex align-items-center justify-content-between">
                        <div class="flex-grow-1">
                            <h5 class="mb-1 opacity-75 fw-semibold">Total Spots</h5>
                            <h2 class="mb-0 fw-bold text-white" id="statTotalSpots">0</h2>
                            <small class="opacity-75 d-block">
                                <i class="fas fa-car me-1"></i>
                                Parking spots
                            </small>
                        </div>
                        <div class="stats-icon">
                            <i class="fas fa-car fa-2x opacity-75"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-xl-3 col-lg-3 col-md-6 col-sm-6">
            <div class="card stats-card-modern h-100 border-0 shadow-sm stats-card-gray">
                <div class="card-body text-white position-relative overflow-hidden p-4">
                    <div class="d-flex align-items-center justify-content-between">
                        <div class="flex-grow-1">
                            <h5 class="mb-1 opacity-75 fw-semibold">Active Areas</h5>
                            <h2 class="mb-0 fw-bold text-white" id="statActiveAreas">0</h2>
                            <small class="opacity-75 d-block">
                                <i class="fas fa-check-circle me-1"></i>
                                Currently active
                            </small>
                        </div>
                        <div class="stats-icon">
                            <i class="fas fa-check-circle fa-2x opacity-75"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Parking Areas Grid -->
    <div class="row" id="areasGrid">
        <!-- Loading State -->
        <div class="col-12 text-center py-5">
            <div class="spinner-border text-primary" role="status">
                <span class="visually-hidden">Loading...</span>
            </div>
            <p class="mt-2 text-muted">Loading parking areas...</p>
        </div>
    </div>
</div>

<!-- Area Sections Modal -->
<div class="modal fade" id="areaSectionsModal" tabindex="-1" data-bs-backdrop="static" data-bs-keyboard="false">
    <div class="modal-dialog modal-xl modal-dialog-scrollable">
        <div class="modal-content">
            <div class="modal-header text-white" style="background: linear-gradient(135deg, #800000 0%, #990000 100%);">
                <div class="d-flex align-items-center flex-grow-1 me-3">
                    <i class="fas fa-th me-3"></i>
                    <h5 class="modal-title fw-bold text-white mb-0" id="areaSectionsModalTitle">View Sections</h5>
                </div>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <input type="hidden" id="areaSectionsAreaId" value="">

                <div class="d-flex justify-content-between align-items-center flex-wrap gap-2 mb-3">
                    <div>
                        <div class="fw-semibold" id="areaSectionsAreaName">-</div>
                        <small class="text-muted" id="areaSectionsMeta">Loading sections...</small>
                    </div>
                    <button type="button" class="btn btn-maroon" id="areaSectionsAddBtn">
                        <i class="fas fa-plus me-1"></i> Add Section
                    </button>
                </div>

                <div class="row g-2 mb-3">
                    <div class="col-md-4">
                        <label class="form-label mb-1">Floor</label>
                        <select class="form-select" id="areaSectionsFloorSelect" disabled>
                            <option value="all">All Floors</option>
                        </select>
                    </div>
                    <div class="col-md-8">
                        <label class="form-label mb-1">Search</label>
                        <div class="input-group">
                            <span class="input-group-text"><i class="fas fa-search"></i></span>
                            <input type="text" class="form-control" id="areaSectionsSearchInput" placeholder="Search section name..." disabled>
                        </div>
                    </div>
                </div>

                <div class="card">
                    <div class="card-body">
                        <div id="areaSectionsList">
                            <div class="text-center py-5">
                                <div class="spinner-border text-primary" role="status">
                                    <span class="visually-hidden">Loading...</span>
                                </div>
                                <p class="mt-2 text-muted">Loading sections...</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
            </div>
        </div>
    </div>
</div>

 <style>
 #areaSectionsModal .modal-content {
     border-radius: 14px;
     border: 0;
     box-shadow: 0 18px 55px rgba(0, 0, 0, 0.25);
 }
 #areaSectionsModal .modal-header {
     border-bottom: 0;
     padding: 1rem 1.25rem;
 }
 #areaSectionsModal .modal-body {
     background: #f6f7f9;
 }
 #areaSectionsModal .modal-footer {
     background: #ffffff;
     border-top: 1px solid rgba(0, 0, 0, 0.06);
 }
 #areaSectionsModal .card {
     border: 1px solid rgba(0, 0, 0, 0.06);
     border-radius: 12px;
     box-shadow: 0 8px 20px rgba(0, 0, 0, 0.06);
 }
 #areaSectionsModal #areaSectionsList .area-section-row {
     background: #ffffff;
     border: 1px solid rgba(0, 0, 0, 0.06);
     border-radius: 10px;
     transition: transform 0.12s ease, box-shadow 0.12s ease, border-color 0.12s ease;
 }
 #areaSectionsModal #areaSectionsList .area-section-row:hover {
     transform: translateY(-1px);
     box-shadow: 0 10px 24px rgba(0, 0, 0, 0.08);
     border-color: rgba(128, 0, 0, 0.25);
 }
 #areaSectionsModal .area-section-actions .btn {
     border-radius: 8px;
 }
 #areaSectionsModal .area-section-actions .btn.btn-maroon {
     background: #800000;
     border-color: #800000;
 }
 #areaSectionsModal .area-section-actions .btn.btn-maroon:hover {
     background: #6d0000;
     border-color: #6d0000;
 }
 #areaSectionsModal .badge {
     border-radius: 999px;
 }

 /* Nested modal overlay (industry-style) */
 .modal-backdrop.modal-stack {
     opacity: 0.65;
 }
 </style>

<!-- Wizard Modal for Creating Area + Sections -->
<div class="modal fade" id="wizardModal" tabindex="-1" data-bs-backdrop="static" data-bs-keyboard="false">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header text-white" style="background: linear-gradient(135deg, #800000 0%, #990000 100%);">
                <h5 class="modal-title fw-bold text-white"><i class="fas fa-magic me-2"></i>Create New Parking Area</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <!-- Stepper -->
                <div class="wizard-stepper mb-4">
                    <div class="wizard-step active" data-step="1">
                        <div class="wizard-step-circle">1</div>
                        <div class="wizard-step-label">Area Details</div>
                    </div>
                    <div class="wizard-step-line"></div>
                    <div class="wizard-step" data-step="2">
                        <div class="wizard-step-circle">2</div>
                        <div class="wizard-step-label">Add Sections</div>
                    </div>
                    <div class="wizard-step-line"></div>
                    <div class="wizard-step" data-step="3">
                        <div class="wizard-step-circle">3</div>
                        <div class="wizard-step-label">Review</div>
                    </div>
                </div>

                <!-- Step 1: Area Details -->
                <div class="wizard-content" data-step="1">
                    <h5 class="mb-3"><i class="fas fa-map-marked-alt me-2 text-primary"></i>Parking Area Information</h5>
                    <form id="wizardStep1Form">
                        <div class="mb-3">
                            <label for="wizardAreaName" class="form-label">Area Name <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" id="wizardAreaName" required placeholder="e.g., FPA CAMPUS, FU-MAIN CAMPUS">
                            <div class="invalid-feedback">Area name is required</div>
                        </div>
                        <div class="mb-3">
                            <label for="wizardAreaLocation" class="form-label">Location <span class="text-danger">*</span></label>
                            <div class="location-autocomplete-wrapper">
                                <input type="text" class="form-control" id="wizardAreaLocation" required placeholder="e.g., Foundation for Professional Advancement Campus" autocomplete="off">
                                <div class="location-autocomplete-list" id="wizardLocationSuggestions" role="listbox"></div>
                            </div>
                            <div class="invalid-feedback">Location is required</div>
                            <small class="text-muted">Powered by Geoapify – start typing to search and confirm the location.</small>
                        </div>
                        <div class="wizard-location-map d-none" id="wizardLocationMap" aria-label="Parking area map preview"></div>
                        <input type="hidden" id="wizardAreaLat">
                        <input type="hidden" id="wizardAreaLon">
                        <div class="mb-3">
                            <label for="wizardNumFloors" class="form-label">Number of Floors <span class="text-danger">*</span></label>
                            <input type="number" class="form-control" id="wizardNumFloors" min="1" value="1" required>
                            <small class="text-muted">Sections can be assigned to different floors</small>
                            <div class="invalid-feedback">Number of floors is required</div>
                        </div>

                    </form>
                </div>

                <!-- Step 2: Add Sections -->
                <div class="wizard-content d-none" data-step="2">
                    <h5 class="mb-3"><i class="fas fa-th me-2 text-primary"></i>Parking Sections</h5>
                    <p class="text-muted">Add sections for this parking area. You can add multiple sections.</p>
                    
                    <!-- Section Form -->
                    <div class="card mb-3 border-primary">
                        <div class="card-body">
                            <form id="wizardSectionForm">
                                <div class="row">
                                    <div class="col-md-6 mb-3">
                                        <label for="wizardSectionName" class="form-label">Section Name <span class="text-danger">*</span></label>
                                        <input type="text" class="form-control" id="wizardSectionName" required maxlength="3" placeholder="e.g., A, AB, A1">
                                        <small class="text-muted">1-3 characters only</small>
                                        <div class="invalid-feedback">Section name is required</div>
                                    </div>
                                    <div class="col-md-6 mb-3">
                                        <label for="wizardSectionFloor" class="form-label">Floor <span class="text-danger">*</span></label>
                                        <input type="number" class="form-control" id="wizardSectionFloor" min="1" value="1" required>
                                        <div class="invalid-feedback">Floor is required and must be at least 1</div>
                                    </div>
                                </div>
                                <div class="mb-3">
                                    <label for="wizardSectionVehicleType" class="form-label">Vehicle Type <span class="text-danger">*</span></label>
                                    <select class="form-select" id="wizardSectionVehicleType" required>
                                        <option value="">Select Vehicle Type</option>
                                        <!-- Will be populated dynamically -->
                                    </select>
                                    <div class="invalid-feedback">Vehicle type is required</div>
                                </div>
                                
                                <!-- Special options for Motorcycle/Bicycle -->
                                <div id="specialVehicleOptions" class="d-none">
                                    <div class="alert alert-info mb-3">
                                        <i class="fas fa-info-circle me-2"></i>
                                        <strong>Special Section Options</strong> - This vehicle type supports two section modes:
                                    </div>
                                    <div class="mb-3">
                                        <label class="form-label">Section Mode <span class="text-danger">*</span></label>
                                        <div class="btn-group w-100" role="group">
                                            <input type="radio" class="btn-check" name="sectionMode" id="slotBasedMode" value="slot_based" checked>
                                            <label class="btn btn-outline-primary" for="slotBasedMode">
                                                <i class="fas fa-th me-2"></i>Slot-based
                                                <small class="d-block text-muted">Individual parking slots (Rows × Columns)</small>
                                            </label>
                                            
                                            <input type="radio" class="btn-check" name="sectionMode" id="capacityOnlyMode" value="capacity_only">
                                            <label class="btn btn-outline-primary" for="capacityOnlyMode">
                                                <i class="fas fa-rectangle-wide me-2"></i>Capacity-only
                                                <small class="d-block text-muted">Single block with capacity</small>
                                            </label>
                                        </div>
                                    </div>
                                </div>
                                
                                <!-- Slot-based fields (default) -->
                                <div id="slotBasedFields">
                                    <div class="row">
                                        <div class="col-md-4 mb-3">
                                            <label for="wizardSectionRows" class="form-label">Rows <span class="text-danger">*</span></label>
                                            <input type="number" class="form-control" id="wizardSectionRows" min="1" required placeholder="e.g., 5">
                                            <div class="invalid-feedback">Rows is required and must be at least 1</div>
                                        </div>
                                        <div class="col-md-4 mb-3">
                                            <label for="wizardSectionColumns" class="form-label">Columns <span class="text-danger">*</span></label>
                                            <input type="number" class="form-control" id="wizardSectionColumns" min="1" required placeholder="e.g., 10">
                                            <div class="invalid-feedback">Columns is required and must be at least 1</div>
                                        </div>
                                        <div class="col-md-4 mb-3">
                                            <label class="form-label">Total Spots</label>
                                            <input type="text" class="form-control" id="wizardTotalSpotsPreview" readonly value="0 spots">
                                        </div>
                                    </div>
                                </div>
                                
                                <!-- Capacity-only fields (hidden by default) -->
                                <div id="capacityOnlyFields" class="d-none">
                                    <div class="row">
                                        <div class="col-md-6 mb-3">
                                            <label for="wizardSectionCapacity" class="form-label">Capacity <span class="text-danger">*</span></label>
                                            <input type="number" class="form-control" id="wizardSectionCapacity" min="1" placeholder="e.g., 20">
                                            <div class="form-text">Total number of vehicles this section can hold</div>
                                            <div class="invalid-feedback">Capacity is required and must be at least 1</div>
                                        </div>
                                        <div class="col-md-6 mb-3">
                                            <label for="wizardSectionGridWidth" class="form-label">Grid Width <span class="text-danger">*</span></label>
                                            <input type="number" class="form-control" id="wizardSectionGridWidth" min="1" max="20" placeholder="e.g., 10">
                                            <div class="form-text">How many grid columns this section occupies (1-20)</div>
                                            <div class="invalid-feedback">Grid width is required and must be between 1-20</div>
                                        </div>
                                    </div>
                                </div>
                                <button type="button" class="btn btn-maroon" id="addSectionToListBtn">
                                    <i class="fas fa-plus me-1"></i> Add Section to List
                                </button>
                            </form>
                        </div>
                    </div>

                    <!-- Sections List -->
                    <div class="card">
                        <div class="card-header bg-light">
                            <h6 class="mb-0"><i class="fas fa-list me-2"></i>Sections to Create (<span id="sectionsCount">0</span>)</h6>
                        </div>
                        <div class="card-body" id="sectionsList" style="max-height: 300px; overflow-y: auto;">
                            <p class="text-muted text-center py-3">No sections added yet. Add at least one section to continue.</p>
                        </div>
                    </div>
                </div>

                <!-- Step 3: Review -->
                <div class="wizard-content d-none" data-step="3">
                    <h5 class="mb-3"><i class="fas fa-check-circle me-2 text-primary"></i>Review & Confirm</h5>
                    <p class="text-muted">Please review the information before creating</p>
                    
                    <!-- Area Summary -->
                    <div class="card mb-3">
                        <div class="card-header bg-primary text-white">
                            <h6 class="mb-0"><i class="fas fa-map-marked-alt me-2"></i>Parking Area</h6>
                        </div>
                        <div class="card-body">
                            <table class="table table-sm table-borderless mb-0">
                                <tbody>
                                    <tr>
                                        <th width="150">Area Name:</th>
                                        <td id="reviewAreaName">-</td>
                                    </tr>
                                    <tr>
                                        <th>Location:</th>
                                        <td id="reviewAreaLocation">-</td>
                                    </tr>
                                    <tr>
                                        <th>Number of Floors:</th>
                                        <td id="reviewNumFloors">-</td>
                                    </tr>

                                </tbody>
                            </table>
                        </div>
                    </div>

                    <!-- Sections Summary -->
                    <div class="card">
                        <div class="card-header bg-success text-white">
                            <h6 class="mb-0"><i class="fas fa-th me-2"></i>Sections (<span id="reviewSectionsCount">0</span>)</h6>
                        </div>
                        <div class="card-body">
                            <div class="table-responsive">
                                <table class="table table-sm table-hover mb-0">
                                    <thead>
                                        <tr>
                                            <th>#</th>
                                            <th>Section Name</th>
                                            <th>Floor</th>
                                            <th>Grid Size</th>
                                            <th>Spots</th>
                                            <th>Vehicle Type</th>
                                        </tr>
                                    </thead>
                                    <tbody id="reviewSectionsTable">
                                        <!-- Will be populated dynamically -->
                                    </tbody>
                                    <tfoot>
                                        <tr class="fw-bold">
                                            <td colspan="3" class="text-end">Total Parking Spots:</td>
                                            <td colspan="2"><span class="badge bg-primary" id="reviewTotalSpots">0</span></td>
                                        </tr>
                                    </tfoot>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <div id="wizardNormalFooter" style="display: flex; justify-content: flex-end; gap: 0.5rem; width: 100%;">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="button" class="btn btn-outline-secondary" id="wizardPrevBtn" style="display:none;">
                        <i class="fas fa-arrow-left me-1"></i> Previous
                    </button>
                    <button type="button" class="btn btn-maroon" id="wizardNextBtn">
                        Next <i class="fas fa-arrow-right ms-1"></i>
                    </button>
                    <button type="button" class="btn btn-maroon" id="wizardSubmitBtn" style="display:none;">
                        <i class="fas fa-check me-1"></i> Create Area & Sections
                    </button>
                </div>
                <div id="wizardConfirmFooter" style="display: none; width: 100%; text-align: center;">
                    <p class="mb-1" id="wizardConfirmMessage">Are you sure you want to create this parking area with sections?</p>
                    <p class="text-muted small mb-2" id="wizardConfirmDescription"></p>
                    <div class="d-flex justify-content-center gap-2 mt-3">
                        <button type="button" class="btn btn-secondary" id="wizardConfirmCancelBtn">No</button>
                        <button type="button" class="btn btn-maroon" id="wizardConfirmYesBtn">
                            <i class="fas fa-check me-1"></i> Yes, Create Area & Sections
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Edit Area Modal -->
<div class="modal fade" id="editAreaModal" tabindex="-1">
    <div class="modal-dialog modal-dialog-scrollable">
        <div class="modal-content">
            <div class="modal-header text-white" style="background: linear-gradient(135deg, #800000 0%, #990000 100%);">
                <h5 class="modal-title fw-bold text-white"><i class="fas fa-pen me-2"></i>Edit Parking Area</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form id="editAreaForm">
                <input type="hidden" id="editAreaId" name="parking_area_id">
                <div class="modal-body">
                    <!-- Form Section (shown by default) -->
                    <div id="editAreaFormSection">
                        <div class="mb-3">
                            <label for="editAreaName" class="form-label">Area Name <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" id="editAreaName" name="parking_area_name" required>
                            <div class="invalid-feedback" id="error-parking_area_name"></div>
                        </div>
                        <div class="mb-3">
                            <label for="editAreaLocation" class="form-label">Location <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" id="editAreaLocation" name="location" required>
                            <div class="invalid-feedback" id="error-location"></div>
                        </div>
                        <div class="mb-3">
                            <label for="editAreaFloors" class="form-label">Number of Floors</label>
                            <input type="number" class="form-control" id="editAreaFloors" name="num_of_floors" min="1" value="1">
                            <small class="text-muted">Total number of floors in this parking area</small>
                        </div>
                        <div class="mb-3">
                            <label for="editAreaStatus" class="form-label">Status</label>
                            <select class="form-select" id="editAreaStatus" name="status">
                                <option value="active">Active</option>
                                <option value="inactive">Inactive</option>
                            </select>
                        </div>
                    </div>
                    
                    <!-- Confirmation Section (hidden by default) -->
                    <div id="editAreaConfirmSection" style="display: none;">
                        <div class="text-center py-4">
                            <div class="mb-3">
                                <i class="fas fa-exclamation-triangle fa-3x text-warning mb-3"></i>
                            </div>
                            <h5 class="mb-3" id="editAreaConfirmTitle">Confirm Update</h5>
                            <p class="mb-2 fw-semibold" id="editAreaConfirmMessage">Are you sure you want to update this parking area?</p>
                            <small class="text-muted" id="editAreaConfirmDescription"></small>
                            
                            <!-- Summary of data being submitted -->
                            <div id="editAreaConfirmSummary" class="mt-4 p-3 bg-light rounded text-start">
                                <!-- Will be populated dynamically -->
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <div id="editAreaNormalFooter" style="display: flex; justify-content: flex-end; gap: 0.5rem; width: 100%;">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                        <button type="button" class="btn btn-maroon" id="editAreaSubmitBtn">
                            <i class="fas fa-save me-1"></i> Update Area
                        </button>
                    </div>
                    <div id="editAreaConfirmFooter" style="display: none; width: 100%; text-align: center;">
                        <div class="d-flex justify-content-center gap-2 mt-3">
                            <button type="button" class="btn btn-secondary" id="editAreaConfirmCancelBtn">No</button>
                            <button type="button" class="btn btn-maroon" id="editAreaConfirmYesBtn">
                                <i class="fas fa-check me-1"></i> Yes, Update Area
                            </button>
                        </div>
                    </div>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Delete Area Modal -->
<div class="modal fade" id="deleteAreaModal" tabindex="-1">
    <div class="modal-dialog modal-dialog-scrollable">
        <div class="modal-content">
            <div class="modal-header text-white" style="background: linear-gradient(135deg, #800000 0%, #990000 100%);">
                <h5 class="modal-title fw-bold text-white"><i class="fas fa-exclamation-triangle me-2"></i>Confirm Delete</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <input type="hidden" id="deleteAreaId">
                <div class="text-center py-3">
                        <i class="fas fa-exclamation-circle fa-3x text-danger mb-3"></i>
                    <h5>Are you sure you want to delete this area?</h5>
                    <p class="text-muted mb-0"><strong id="deleteAreaName"></strong></p>
                    <p class="text-danger mt-2">This action cannot be undone. All sections and spots in this area will also be deleted.</p>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-maroon" id="confirmDeleteAreaBtn">
                    <i class="fas fa-trash me-1"></i> Delete Area
                </button>
            </div>
        </div>
    </div>
</div>

<!-- Add Section Modal -->
<div class="modal fade" id="addSectionModal" tabindex="-1">
    <div class="modal-dialog modal-dialog-scrollable">
        <div class="modal-content">
            <div class="modal-header text-white" style="background: linear-gradient(135deg, #800000 0%, #990000 100%);">
                <h5 class="modal-title fw-bold text-white"><i class="fas fa-plus me-2"></i>Add New Section</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form id="addSectionForm">
                <input type="hidden" id="sectionAreaId" name="parking_area_id">
                <div class="modal-body">
                    <!-- Form Section (shown by default) -->
                    <div id="addSectionFormSection">
                        <div class="alert alert-info">
                            <i class="fas fa-info-circle me-1"></i> Adding section to: <strong id="sectionAreaName"></strong>
                        </div>
                        <div class="mb-3">
                            <label for="sectionName" class="form-label">Section Name <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" id="sectionName" name="section_name" required maxlength="3" placeholder="e.g., A, AB, A1">
                            <small class="text-muted">1-3 characters only</small>
                            <div class="invalid-feedback" id="error-section_name"></div>
                        </div>
                        <div class="mb-3">
                            <label for="sectionFloor" class="form-label">Floor Number</label>
                            <input type="number" class="form-control" id="sectionFloor" name="floor_number" min="1" value="1">
                        </div>
                        <div class="mb-3">
                            <label for="sectionVehicleType" class="form-label">Vehicle Type <span class="text-danger">*</span></label>
                            <select class="form-select" id="sectionVehicleType" name="vehicle_type_id" required>
                                <option value="">Select Vehicle Type</option>
                            </select>
                            <div class="invalid-feedback" id="error-vehicle_type_id"></div>
                        </div>
                        
                        <!-- Special options for Motorcycle/Bicycle -->
                        <div id="addSpecialVehicleOptions" class="d-none">
                            <div class="alert alert-info mb-3">
                                <i class="fas fa-info-circle me-2"></i>
                                <strong>Special Section Options</strong> - This vehicle type supports two section modes:
                            </div>
                            <div class="mb-3">
                                <label class="form-label">Section Mode <span class="text-danger">*</span></label>
                                <div class="btn-group w-100" role="group">
                                    <input type="radio" class="btn-check" name="addSectionMode" id="addSlotBasedMode" value="slot_based" checked>
                                    <label class="btn btn-outline-primary" for="addSlotBasedMode">
                                        <i class="fas fa-th me-1"></i> Slot-based Grid
                                    </label>
                                    <input type="radio" class="btn-check" name="addSectionMode" id="addCapacityOnlyMode" value="capacity_only">
                                    <label class="btn btn-outline-primary" for="addCapacityOnlyMode">
                                        <i class="fas fa-list-ol me-1"></i> Capacity Only
                                    </label>
                                </div>
                            </div>
                        </div>

                        <!-- Slot-based fields (default) -->
                        <div id="addSlotBasedFields">
                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label for="sectionRows" class="form-label">Rows <span class="text-danger">*</span></label>
                                    <input type="number" class="form-control" id="sectionRows" name="rows" min="1" required>
                                    <div class="invalid-feedback" id="error-rows"></div>
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label for="sectionColumns" class="form-label">Columns <span class="text-danger">*</span></label>
                                    <input type="number" class="form-control" id="sectionColumns" name="columns" min="1" required>
                                    <div class="invalid-feedback" id="error-columns"></div>
                                </div>
                            </div>
                            <div class="mb-3">
                                <label class="form-label">Total Spots: <span class="badge bg-primary" id="totalSpotsPreview">0</span></label>
                            </div>
                        </div>
                        
                        <!-- Capacity-only fields (hidden by default) -->
                        <div id="addCapacityOnlyFields" class="d-none">
                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label for="addSectionCapacity" class="form-label">Capacity <span class="text-danger">*</span></label>
                                    <input type="number" class="form-control" id="addSectionCapacity" name="capacity" min="1" placeholder="e.g., 20">
                                    <div class="form-text">Total number of vehicles this section can hold</div>
                                    <div class="invalid-feedback" id="error-capacity"></div>
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label for="addSectionGridWidth" class="form-label">Grid Width <span class="text-danger">*</span></label>
                                    <input type="number" class="form-control" id="addSectionGridWidth" name="grid_width" min="1" max="20" placeholder="e.g., 10">
                                    <div class="form-text">How many grid columns this section occupies (1-20)</div>
                                    <div class="invalid-feedback" id="error-grid_width"></div>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Confirmation Section (hidden by default) -->
                    <div id="addSectionConfirmSection" style="display: none;">
                        <div class="text-center py-4">
                            <div class="mb-3">
                                <i class="fas fa-exclamation-triangle fa-3x text-warning mb-3"></i>
                            </div>
                            <h5 class="mb-3" id="addSectionConfirmTitle">Confirm Create Section</h5>
                            <p class="mb-2 fw-semibold" id="addSectionConfirmMessage">Are you sure you want to create this section?</p>
                            <small class="text-muted" id="addSectionConfirmDescription"></small>
                            
                            <!-- Summary of data being submitted -->
                            <div id="addSectionConfirmSummary" class="mt-4 p-3 bg-light rounded text-start">
                                <!-- Will be populated dynamically -->
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <div id="addSectionNormalFooter" style="display: flex; justify-content: flex-end; gap: 0.5rem; width: 100%;">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                        <button type="button" class="btn btn-maroon" id="addSectionSubmitBtn">
                            <i class="fas fa-save me-1"></i> Create Section
                        </button>
                    </div>
                    <div id="addSectionConfirmFooter" style="display: none; width: 100%; text-align: center;">
                        <div class="d-flex justify-content-center gap-2 mt-3">
                            <button type="button" class="btn btn-secondary" id="addSectionConfirmCancelBtn">No</button>
                            <button type="button" class="btn btn-maroon" id="addSectionConfirmYesBtn">
                                <i class="fas fa-check me-1"></i> Yes, Create Section
                            </button>
                        </div>
                    </div>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Edit Section Modal -->
<div class="modal fade" id="editSectionModal" tabindex="-1">
    <div class="modal-dialog modal-dialog-scrollable">
        <div class="modal-content">
            <div class="modal-header text-white" style="background: linear-gradient(135deg, #800000 0%, #990000 100%);">
                <h5 class="modal-title fw-bold text-white"><i class="fas fa-pen me-2"></i>Edit Section</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form id="editSectionForm">
                <input type="hidden" id="editSectionId" name="parking_section_id">
                <div class="modal-body">
                    <!-- Form Section (shown by default) -->
                        <div id="editSectionFormSection">
                            <!-- Data Integrity Warning -->
                            <div class="alert alert-warning mb-3">
                                <div class="d-flex">
                                    <div class="me-3">
                                        <i class="fas fa-exclamation-triangle fa-2x"></i>
                                    </div>
                                    <div>
                                        <h6 class="alert-heading fw-bold mb-1">Dimensions & Vehicle Type Locked</h6>
                                        <p class="small mb-0">Grid dimensions and vehicle types are locked to maintain layout integrity. To change these, please <strong>delete and recreate</strong> the section in the Layout Designer.</p>
                                    </div>
                                </div>
                            </div>

                            <div class="mb-3">
                            <label for="editSectionName" class="form-label">Section Name <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" id="editSectionName" name="section_name" required maxlength="3">
                            <small class="text-muted">1-3 characters only</small>
                            <div class="invalid-feedback" id="error-section_name"></div>
                        </div>
                        <div class="mb-3">
                            <label for="editSectionFloor" class="form-label">Floor Number</label>
                            <input type="number" class="form-control" id="editSectionFloor" name="floor_number" min="1">
                        </div>
                        <div class="mb-3">
                            <label for="editSectionVehicleType" class="form-label">Vehicle Type <span class="text-danger">*</span></label>
                            <select class="form-select" id="editSectionVehicleType" name="vehicle_type_id" required>
                                <option value="">Select Vehicle Type</option>
                            </select>
                            <div class="invalid-feedback" id="error-vehicle_type_id"></div>
                        </div>
                        
                        <!-- Special options for Motorcycle/Bicycle -->
                        <div id="editSpecialVehicleOptions" class="d-none">
                            <div class="alert alert-info mb-3">
                                <i class="fas fa-info-circle me-2"></i>
                                <strong>Special Section Options</strong> - This vehicle type supports two section modes:
                            </div>
                            <div class="mb-3">
                                <label class="form-label">Section Mode <span class="text-danger">*</span></label>
                                <div class="btn-group w-100" role="group">
                                    <input type="radio" class="btn-check" name="editSectionMode" id="editSlotBasedMode" value="slot_based" checked>
                                    <label class="btn btn-outline-primary" for="editSlotBasedMode">
                                        <i class="fas fa-th me-2"></i>Slot-based
                                        <small class="d-block text-muted">Individual parking slots (Rows × Columns)</small>
                                    </label>
                                    
                                    <input type="radio" class="btn-check" name="editSectionMode" id="editCapacityOnlyMode" value="capacity_only">
                                    <label class="btn btn-outline-primary" for="editCapacityOnlyMode">
                                        <i class="fas fa-rectangle-wide me-2"></i>Capacity-only
                                        <small class="d-block text-muted">Single block with capacity</small>
                                    </label>
                                </div>
                            </div>
                        </div>
                        
                        <!-- Slot-based fields (default) -->
                        <div id="editSlotBasedFields">
                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label for="editSectionRows" class="form-label">Rows <span class="text-danger">*</span></label>
                                    <input type="number" class="form-control" id="editSectionRows" name="rows" min="1" required>
                                    <div class="invalid-feedback" id="error-rows"></div>
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label for="editSectionColumns" class="form-label">Columns <span class="text-danger">*</span></label>
                                    <input type="number" class="form-control" id="editSectionColumns" name="columns" min="1" required>
                                    <div class="invalid-feedback" id="error-columns"></div>
                                </div>
                            </div>
                            <div class="mb-3">
                                <label class="form-label">Total Spots: <span class="badge bg-primary" id="editTotalSpotsPreview">0</span></label>
                            </div>
                        </div>
                        
                        <!-- Capacity-only fields (hidden by default) -->
                        <div id="editCapacityOnlyFields" class="d-none">
                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label for="editSectionCapacity" class="form-label">Capacity <span class="text-danger">*</span></label>
                                    <input type="number" class="form-control" id="editSectionCapacity" name="capacity" min="1" placeholder="e.g., 20">
                                    <div class="form-text">Total number of vehicles this section can hold</div>
                                    <div class="invalid-feedback" id="error-capacity"></div>
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label for="editSectionGridWidth" class="form-label">Grid Width <span class="text-danger">*</span></label>
                                    <input type="number" class="form-control" id="editSectionGridWidth" name="grid_width" min="1" max="20" placeholder="e.g., 10">
                                    <div class="form-text">How many grid columns this section occupies (1-20)</div>
                                    <div class="invalid-feedback" id="error-grid_width"></div>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Confirmation Section (hidden by default) -->
                    <div id="editSectionConfirmSection" style="display: none;">
                        <div class="text-center py-4">
                            <div class="mb-3">
                                <i class="fas fa-exclamation-triangle fa-3x text-warning mb-3"></i>
                            </div>
                            <h5 class="mb-3" id="editSectionConfirmTitle">Confirm Update Section</h5>
                            <p class="mb-2 fw-semibold" id="editSectionConfirmMessage">Are you sure you want to update this section?</p>
                            <small class="text-muted" id="editSectionConfirmDescription"></small>
                            
                            <!-- Summary of data being submitted -->
                            <div id="editSectionConfirmSummary" class="mt-4 p-3 bg-light rounded text-start">
                                <!-- Will be populated dynamically -->
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <div id="editSectionNormalFooter" style="display: flex; justify-content: flex-end; gap: 0.5rem; width: 100%;">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                        <button type="button" class="btn btn-maroon" id="editSectionSubmitBtn">
                            <i class="fas fa-save me-1"></i> Update Section
                        </button>
                    </div>
                    <div id="editSectionConfirmFooter" style="display: none; width: 100%; text-align: center;">
                        <div class="d-flex justify-content-center gap-2 mt-3">
                            <button type="button" class="btn btn-secondary" id="editSectionConfirmCancelBtn">No</button>
                            <button type="button" class="btn btn-maroon" id="editSectionConfirmYesBtn">
                                <i class="fas fa-check me-1"></i> Yes, Update Section
                            </button>
                        </div>
                    </div>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Set base URL for JavaScript -->
<script>
window.APP_BASE_URL = '<?= base_url() ?>';
</script>

<!-- Load CSS -->
<link rel="stylesheet" href="<?= base_url('assets/css/parking_areas.css') ?>">

<!-- Load JavaScript -->
<script src="<?= base_url('assets/js/parking_areas.js') ?>"></script>
