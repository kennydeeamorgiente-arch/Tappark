<div class="container-fluid">
    <!-- Parking Overview CSS -->
    <link rel="stylesheet" href="<?= base_url('assets/css/parking-overview.css') ?>">
    
    <!-- Enhanced Page Header -->
    <div class="card mb-4 border-0 shadow-sm">
        <div class="card-body py-4">
            <div class="d-flex justify-content-between align-items-center flex-wrap gap-3">
                <div>
                    <h2 class="mb-2 fw-bold">
                        <i class="fas fa-map text-primary me-3"></i>Parking Overview & Layout
                    </h2>
                    <p class="mb-0 text-muted">Visual overview of all parking areas and their real-time status</p>
                </div>
                <div class="d-flex gap-2">
                    <button class="btn btn-light layout-designer-btn" onclick="openLayoutDesigner()" title="Open Layout Designer">
                        <i class="fas fa-drafting-compass me-1"></i> Layout Designer
                    </button>
                    <button class="btn btn-maroon" id="toggleViewBtn" title="Toggle View">
                        <i class="fas fa-th-large me-1"></i> <span id="viewBtnText">Grid View</span>
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- Enhanced Quick Stats -->
    <div class="row mb-4 g-2">
        <div class="col-xl-3 col-lg-3 col-md-6 col-sm-6">
            <div class="card stats-card-modern h-100 border-0 shadow-sm stats-card-maroon">
                <div class="card-body text-white position-relative overflow-hidden p-4">
                    <div class="d-flex align-items-center justify-content-between">
                        <div class="flex-grow-1">
                            <h5 class="mb-2 opacity-75 fw-semibold">Total Areas</h5>
                            <h2 class="mb-0 fw-bold text-white" id="statTotalAreas">0</h2>
                            <small class="opacity-75 d-block mt-1">
                                <i class="fas fa-map-marked-alt me-1"></i>Registered
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
                            <h5 class="mb-2 opacity-75 fw-semibold">Available Spots</h5>
                            <h2 class="mb-0 fw-bold text-white" id="statAvailableSpots">0</h2>
                            <small class="opacity-75 d-block mt-1">
                                <i class="fas fa-check-circle me-1"></i>Available
                            </small>
                        </div>
                        <div class="stats-icon">
                            <i class="fas fa-check-circle fa-2x opacity-75"></i>
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
                            <h5 class="mb-2 opacity-75 fw-semibold">Occupied Spots</h5>
                            <h2 class="mb-0 fw-bold text-white" id="statOccupiedSpots">0</h2>
                            <small class="opacity-75 d-block mt-1">
                                <i class="fas fa-car me-1"></i>Occupied
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
                            <h5 class="mb-2 opacity-75 fw-semibold">Occupancy Rate</h5>
                            <h2 class="mb-0 fw-bold text-white" id="statOccupancyRate">0%</h2>
                            <small class="opacity-75 d-block mt-1">
                                <i class="fas fa-percentage me-1"></i>Rate
                            </small>
                        </div>
                        <div class="stats-icon">
                            <i class="fas fa-percentage fa-2x opacity-75"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Area Selection & Filters -->
    <div class="card shadow-sm mb-4" id="parkingOverviewFiltersCard">
        <div class="card-body">
            <div class="compact-filter-row">
                <div class="compact-filter-field compact-filter-medium" style="flex: 1 1 220px;">
                    <label class="form-label"><i class="fas fa-filter me-2"></i>Select Area</label>
                    <select class="form-select" id="areaFilterSelect">
                        <option value="all">All Areas</option>
                        <!-- Areas will be populated dynamically -->
                    </select>
                </div>
                <div class="compact-filter-field compact-filter-search">
                    <label class="form-label"><i class="fas fa-search me-2"></i>Search</label>
                    <div class="input-group">
                        <span class="input-group-text"><i class="fas fa-search"></i></span>
                        <input type="text" class="form-control" id="areaSearchInput" placeholder="Search areas...">
                    </div>
                </div>
                <div class="compact-filter-actions filter-actions filter-actions-visible" id="parkingFilterActions">
                        <button class="btn btn-primary" id="applyParkingFilterBtn">
                            <i class="fas fa-filter me-1"></i>Apply
                        </button>
                        <button class="btn btn-secondary" id="clearParkingFiltersBtn">
                            <i class="fas fa-times me-1"></i>Clear
                        </button>
                </div>
            </div>
        </div>
    </div>

    <!-- Parking Areas Overview -->
    <div class="row" id="parkingAreasContainer">
        <!-- Loading State -->
        <div class="col-12 text-center py-5">
            <div class="spinner-border text-primary" role="status">
                <span class="visually-hidden">Loading...</span>
            </div>
            <p class="mt-2 text-muted">Loading parking overview...</p>
        </div>
    </div>
</div>

<!-- Section Grid Modal -->
<div class="modal fade" id="sectionGridModal" tabindex="-1" data-bs-backdrop="static" data-bs-keyboard="false">
    <div class="modal-dialog modal-xl modal-dialog-scrollable">
        <div class="modal-content">
            <div class="modal-header text-white" style="background: linear-gradient(135deg, #800000 0%, #990000 100%); padding: 1.25rem 1.75rem;">
                <div class="d-flex align-items-center flex-grow-1 me-4">
                    <button type="button" class="btn btn-link text-white p-0 me-3" onclick="backToAreaView()" title="Back to Area Details" style="text-decoration: none; font-size: 1.25rem;">
                        <i class="fas fa-arrow-left"></i>
                    </button>
                    <i class="fas fa-th me-3" style="font-size: 1.35rem;"></i>
                    <h5 class="modal-title fw-bold text-white mb-0" id="gridSectionName" style="font-size: 1.35rem; line-height: 1.6; letter-spacing: 0.01em;">Section Grid View</h5>
                </div>
                <button type="button" class="btn-close btn-close-white" onclick="closeSectionGridModal()" aria-label="Close" style="opacity: 1; font-size: 1.1rem;"></button>
            </div>
            <div class="modal-body">
                <!-- Section Info Bar -->
                <div class="section-info-bar d-flex justify-content-between align-items-center mb-3 p-3 bg-light rounded">
                    <div class="section-stats d-flex gap-4">
                        <div class="stat-item">
                            <span class="stat-label text-muted small">Total:</span>
                            <span class="stat-value fw-bold text-dark" id="gridTotalSpots">0</span>
                        </div>
                        <div class="stat-item">
                            <span class="stat-label text-muted small">Available:</span>
                            <span class="stat-value fw-bold text-success" id="gridAvailableSpots">0</span>
                        </div>
                        <div class="stat-item">
                            <span class="stat-label text-muted small">Occupied:</span>
                            <span class="stat-value fw-bold text-danger" id="gridOccupiedSpots">0</span>
                        </div>
                        <div class="stat-item">
                            <span class="stat-label text-muted small">Occupancy:</span>
                            <span class="stat-value fw-bold text-primary" id="gridOccupancyRate">0%</span>
                        </div>
                        <!-- Capacity Toggle Button (hidden by default, shown for capacity-only sections) -->
                        <div class="stat-item toggle-item" id="capacityToggleContainer" style="display: none;">
                            <button type="button" id="capacityToggleBtn" class="btn btn-warning btn-sm" onclick="toggleCapacityView()" title="Toggle between single block and individual slots">
                                <i class="fas fa-th me-1"></i> Show Slots
                            </button>
                        </div>
                    </div>
                </div>

                <!-- Legend -->
                <div class="row mb-3">
                    <div class="col-12">
                        <div class="grid-legend">
                            <span class="legend-item">
                                <span class="legend-color legend-color-available"></span>
                                Available
                            </span>
                            <span class="legend-item">
                                <span class="legend-color legend-color-occupied"></span>
                                Occupied
                            </span>
                            <span class="legend-item">
                                <span class="legend-color legend-color-reserved"></span>
                                Reserved
                            </span>
                        </div>
                    </div>
                </div>

                <!-- Search & Filter -->
                <div class="row mb-3">
                    <div class="col-md-6">
                        <div class="input-group">
                            <span class="input-group-text"><i class="fas fa-search"></i></span>
                            <input type="text" class="form-control" id="gridSearchInput" placeholder="Search spot number...">
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="dropdown">
                            <button class="btn btn-outline-secondary dropdown-toggle w-100" type="button" id="filterDropdown" data-bs-toggle="dropdown">
                                <i class="fas fa-filter me-2"></i>Filter: <span id="currentFilter">All</span>
                            </button>
                            <ul class="dropdown-menu w-100">
                                <li><a class="dropdown-item active" href="#" data-filter="all" onclick="setGridFilter('all')">
                                    <i class="fas fa-th me-2"></i>All Spots
                                </a></li>
                                <li><a class="dropdown-item" href="#" data-filter="available" onclick="setGridFilter('available')">
                                    <i class="fas fa-check-circle text-success me-2"></i>Available
                                </a></li>
                                <li><a class="dropdown-item" href="#" data-filter="occupied" onclick="setGridFilter('occupied')">
                                    <i class="fas fa-car text-danger me-2"></i>Occupied
                                </a></li>
                                <li><a class="dropdown-item" href="#" data-filter="reserved" onclick="setGridFilter('reserved')">
                                    <i class="fas fa-clock text-warning me-2"></i>Reserved
                                </a></li>
                            </ul>
                        </div>
                    </div>
                </div>

                <!-- Grid Container -->
                <div class="card">
                    <div class="card-body">
                        <div id="parkingGridContainer" class="parking-grid-container">
                            <!-- Grid will be rendered here -->
                            <div class="text-center py-5">
                                <div class="spinner-border text-primary"></div>
                                <p class="mt-2 text-muted">Loading grid...</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-outline-secondary btn-sm" onclick="backToAreaView()">
                    <i class="fas fa-arrow-left me-1"></i> Back to Area
                </button>
                <button type="button" class="btn btn-maroon btn-sm" onclick="refreshSectionGrid()">
                    <i class="fas fa-sync-alt me-1"></i> Refresh
                </button>
                <button type="button" class="btn btn-secondary btn-sm" data-bs-dismiss="modal">Close</button>
            </div>
        </div>
    </div>
</div>

<!-- Area Detail Modal -->
<div class="modal fade" id="areaDetailModal" tabindex="-1" data-bs-backdrop="static" data-bs-keyboard="false">
    <div class="modal-dialog modal-xl modal-dialog-scrollable">
        <div class="modal-content">
            <div class="modal-header text-white" style="background: linear-gradient(135deg, #800000 0%, #990000 100%); padding: 1.25rem 1.75rem;">
                <div class="d-flex align-items-center flex-grow-1 me-4">
                    <i class="fas fa-map-marked-alt me-3" style="font-size: 1.35rem;"></i>
                    <h5 class="modal-title fw-bold text-white mb-0" id="modalAreaName" style="font-size: 1.35rem; line-height: 1.6; letter-spacing: 0.01em;">Area Details</h5>
                </div>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close" style="opacity: 1; font-size: 1.1rem;"></button>
            </div>
            <div class="modal-body">
                <!-- Area Info -->
                <div class="row mb-3 g-3">
                    <div class="col-md-6">
                        <div class="info-group">
                            <label class="info-label"><i class="fas fa-map-marker-alt me-1"></i>Location</label>
                            <div class="info-value fw-semibold" id="modalAreaLocation">-</div>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="info-group">
                            <label class="info-label"><i class="fas fa-building me-1"></i>Floors</label>
                            <div class="info-value fw-semibold" id="modalAreaFloors">-</div>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="info-group">
                            <label class="info-label"><i class="fas fa-th me-1"></i>Sections</label>
                            <div class="info-value fw-semibold" id="modalAreaSections">-</div>
                        </div>
                    </div>
                </div>

                <!-- Occupancy Progress -->
                <div class="row mb-3">
                    <div class="col-12">
                        <div class="card border-0 shadow-sm">
                            <div class="card-body py-3">
                                <h6 class="mb-2 fw-semibold">Overall Occupancy</h6>
                                <div class="d-flex justify-content-between mb-2 small">
                                    <span><strong id="modalOccupiedCount">0</strong> Occupied</span>
                                    <span><strong id="modalAvailableCount">0</strong> Available</span>
                                </div>
                                <div class="progress progress-occupancy">
                                    <div class="progress-bar bg-danger" role="progressbar" style="width: 0%" id="modalOccupancyBar">
                                        <span class="small fw-bold" id="modalOccupancyPercent">0%</span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Floor-by-Floor View -->
                <div class="row">
                    <div class="col-12">
                        <h6 class="mb-2 fw-semibold"><i class="fas fa-layer-group me-2"></i>Floor Layout</h6>
                        <div id="floorLayoutContainer">
                            <!-- Floor tabs and content will be dynamically loaded -->
                        </div>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-maroon" id="viewLayoutBtn" onclick="openLayoutVisualization()">
                    <i class="fas fa-eye me-2"></i> View Layout
                </button>
                <button type="button" class="btn btn-outline-secondary btn-sm" data-bs-dismiss="modal">Close</button>
            </div>
        </div>
    </div>
</div>

<!-- Layout Visualization Modal -->
<div class="modal fade" id="layoutVisualizationModal" tabindex="-1" data-bs-backdrop="static" data-bs-keyboard="false">
    <div class="modal-dialog modal-xl modal-dialog-scrollable">
        <div class="modal-content">
            <div class="modal-header text-white" style="background: linear-gradient(135deg, #800000 0%, #990000 100%); padding: 1.25rem 1.75rem;">
                <div class="d-flex align-items-center flex-grow-1 me-4">
                    <button type="button" class="btn btn-link text-white p-0 me-3" onclick="backToAreaDetailsFromLayout()" title="Back to Area Details" style="text-decoration: none; font-size: 1.25rem;">
                        <i class="fas fa-arrow-left"></i>
                    </button>
                    <i class="fas fa-map me-3" style="font-size: 1.35rem;"></i>
                    <h5 class="modal-title fw-bold text-white mb-0" style="font-size: 1.35rem;">
                        <span id="layoutModalTitle">Parking Layout Visualization</span>
                    </h5>
                </div>
                <button type="button" class="btn-close btn-close-white" onclick="closeLayoutVisualization()" aria-label="Close" style="opacity: 1; font-size: 1.1rem;"></button>
            </div>
            <div class="modal-body p-0" style="background: #f8f9fa;">
                <div id="layoutVisualizationContent" class="layout-visualization-content">
                    <!-- Layout will be rendered here -->
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Set base URL for JavaScript -->
<script>
window.APP_BASE_URL = '<?= base_url() ?>';
</script>

<!-- Load CSS -->
<link rel="stylesheet" href="<?= base_url('assets/css/parking-overview.css') ?>?v=<?= @filemtime(FCPATH . 'assets/css/parking-overview.css') ?>">
<link rel="stylesheet" href="<?= base_url('assets/css/layout-designer.css') ?>?v=<?= @filemtime(FCPATH . 'assets/css/layout-designer.css') ?>">

<!-- Load JavaScript -->
<script src="<?= base_url('assets/js/parking-overview.js') ?>?v=<?= @filemtime(FCPATH . 'assets/js/parking-overview.js') ?>"></script>
<script src="<?= base_url('assets/js/layout-designer.js') ?>?v=<?= @filemtime(FCPATH . 'assets/js/layout-designer.js') ?>"></script>

<!-- Include Layout Designer Modal -->
<?php include(APPPATH . 'Views/pages/parking/overview/layout_designer_modal.php'); ?>
