/**
 * Parking Overview JavaScript
 * Handles parking overview page functionality
 */

// Extend initPageScripts for parking overview page
if (typeof window.initPageScripts === 'function') {
    const originalInitPageScripts = window.initPageScripts;

    window.initPageScripts = function () {
        // Check if we're on the parking overview page
        if ($('#parkingAreasContainer').length > 0) {
            console.log('Parking Overview page initialized!');

            const baseUrl = window.APP_BASE_URL || '';
            let allAreasData = [];
            let currentFilter = 'all';
            let isGridView = true;

            // Initialize
            loadParkingOverview();

            // Filter button visibility logic
            function updateFilterButtonVisibility() {
                // Always show filter actions to allow resetting to default
                $('#parkingFilterActions').removeClass('filter-actions-hidden').addClass('filter-actions-visible');
            }

            // Search input handler - remove automatic filtering
            $('#areaSearchInput').off('input').on('input', function () {
                updateFilterButtonVisibility();
            });

            // Area filter dropdown handler - remove automatic filtering
            $('#areaFilterSelect').off('change').on('change', function () {
                updateFilterButtonVisibility();
            });

            // Apply filter button handler
            $('#applyParkingFilterBtn').off('click').on('click', function () {
                const searchTerm = $('#areaSearchInput').val().trim();
                const areaFilter = $('#areaFilterSelect').val();

                currentFilter = areaFilter;

                if (searchTerm) {
                    performSearch(searchTerm);
                } else {
                    if (currentFilter === 'all') {
                        renderAreas(allAreasData);
                    } else {
                        const filtered = allAreasData.filter(a => a.parking_area_id == currentFilter);
                        renderAreas(filtered);
                    }
                }
            });

            // Clear filters button handler
            $('#clearParkingFiltersBtn').off('click').on('click', function () {
                $('#areaSearchInput').val('');
                $('#areaFilterSelect').val('all');
                currentFilter = 'all';
                updateFilterButtonVisibility();
                renderAreas(allAreasData);
            });

            // Search functionality - removed automatic filtering
            let searchTimeout;
            function performSearch(searchTerm) {
                if (!searchTerm) {
                    filterAndRenderAreas();
                    return;
                }

                const searchLower = searchTerm.toLowerCase();
                const filteredAreas = allAreasData.filter(area => {
                    const name = (area.parking_area_name || '').toLowerCase();
                    const location = (area.location || '').toLowerCase();
                    return name.includes(searchLower) || location.includes(searchLower);
                });

                let areasToRender = filteredAreas;
                if (currentFilter !== 'all') {
                    areasToRender = filteredAreas.filter(a => a.parking_area_id == currentFilter);
                }

                renderAreas(areasToRender);

                if (areasToRender.length === 0 && searchTerm) {
                    const safeTerm = escapeHtml(searchTerm);
                    $('#parkingAreasContainer').html(`
                        <div class="col-12">
                            <div class="alert alert-info text-center">
                                <i class="fas fa-search me-2"></i>
                                No parking areas found matching "<strong>${safeTerm}</strong>"
                            </div>
                        </div>
                    `);
                }
            }

            // Toggle View button
            $('#toggleViewBtn').on('click', function () {
                isGridView = !isGridView;
                const $icon = $(this).find('i');
                const $text = $('#viewBtnText');

                if (isGridView) {
                    $icon.removeClass('fa-list').addClass('fa-th-large');
                    $text.text('Grid View');
                } else {
                    $icon.removeClass('fa-th-large').addClass('fa-list');
                    $text.text('List View');
                }

                filterAndRenderAreas();
            });

            // Load parking overview data
            function loadParkingOverview() {
                $.ajax({
                    url: `${baseUrl}api/parking/overview`,
                    method: 'GET',
                    success: function (response) {
                        if (response.success) {
                            allAreasData = response.data;
                            updateStats(response.data);
                            updateAreaFilter(response.data);
                            renderAreas(response.data);
                        } else {
                            showError('Failed to load parking overview');
                        }
                    },
                    error: function () {
                        showError('Error loading parking data');
                    }
                });
            }

            // Update statistics
            function updateStats(areas) {
                let totalAreas = areas.length;
                let totalAvailable = 0;
                let totalOccupied = 0;
                let totalSpots = 0;

                areas.forEach(area => {
                    totalAvailable += parseInt(area.available_spots || 0);
                    totalOccupied += parseInt(area.occupied_spots || 0);
                    totalSpots += parseInt(area.total_spots || 0);
                });

                let occupancyRate = totalSpots > 0 ? ((totalOccupied / totalSpots) * 100).toFixed(1) : 0;

                $('#statTotalAreas').text(totalAreas).addClass('text-white');
                $('#statAvailableSpots').text(totalAvailable).addClass('text-white');
                $('#statOccupiedSpots').text(totalOccupied).addClass('text-white');
                $('#statOccupancyRate').text(occupancyRate + '%').addClass('text-white');
            }

            // Update area filter dropdown - removed automatic filtering
            function updateAreaFilter(areas) {
                const select = $('#areaFilterSelect');
                select.find('option:not([value="all"])').remove();

                areas.forEach(area => {
                    const safeAreaName = escapeHtml(area.parking_area_name || '');
                    select.append(`<option value="${area.parking_area_id}">${area.parking_area_name}</option>`);
                    select.find('option[value="' + area.parking_area_id + '"]').text(safeAreaName || '');
                });
            }

            // Filter and render areas
            function filterAndRenderAreas() {
                const searchTerm = $('#areaSearchInput').val().trim();

                if (searchTerm) {
                    performSearch(searchTerm);
                } else {
                    if (currentFilter === 'all') {
                        renderAreas(allAreasData);
                    } else {
                        const filtered = allAreasData.filter(a => a.parking_area_id == currentFilter);
                        renderAreas(filtered);
                    }
                }
            }

            // Render parking areas
            function renderAreas(areas) {
                const container = $('#parkingAreasContainer');

                if (areas.length === 0) {
                    container.html(`
                        <div class="col-12">
                            <div class="empty-state">
                                <div class="empty-state-icon"><i class="fas fa-parking"></i></div>
                                <div class="empty-state-text">No parking areas found</div>
                            </div>
                        </div>
                    `);
                    return;
                }

                container.empty();

                if (!isGridView) {
                    renderListView(areas, container);
                } else {
                    renderGridView(areas, container);
                }
            }

            // Render grid view
            function renderGridView(areas, container) {
                areas.forEach(area => {
                    const totalSpots = parseInt(area.total_spots || 0);
                    const occupiedSpots = parseInt(area.occupied_spots || 0);
                    const availableSpots = parseInt(area.available_spots || 0);
                    const occupancyRate = totalSpots > 0 ? ((occupiedSpots / totalSpots) * 100).toFixed(1) : 0;

                    let barColor = 'success';
                    if (occupancyRate > 75) barColor = 'danger';
                    else if (occupancyRate > 50) barColor = 'warning';

                    const statusBadge = area.status === 'active'
                        ? '<span class="badge bg-success">Active</span>'
                        : '<span class="badge bg-secondary">Inactive</span>';

                    const card = $(`
                        <div class="col-lg-6 col-xl-4 mb-4">
                            <div class="card area-card status-${area.status || 'active'} shadow-sm h-100 clickable-card" data-area-id="${area.parking_area_id}">
                                <div class="card-body">
                                    <div class="area-header">
                                        <div class="d-flex justify-content-between align-items-start">
                                            <div>
                                                <h5 class="mb-1">
                                                    <i class="fas fa-map-marker-alt text-primary me-2"></i>
                                                    ${escapeHtml(area.parking_area_name)}
                                                </h5>
                                                <p class="text-muted small mb-0">
                                                    <i class="fas fa-location-dot me-1"></i> ${escapeHtml(area.location || 'No location')}
                                                </p>
                                            </div>
                                            ${statusBadge}
                                        </div>
                                    </div>

                                    <div class="area-stats-grid">
                                        <div class="stat-box">
                                            <div class="value text-primary">${area.total_sections || 0}</div>
                                            <div class="label">Sections</div>
                                        </div>
                                        <div class="stat-box">
                                            <div class="value text-info">${totalSpots}</div>
                                            <div class="label">Parking Spaces</div>
                                        </div>
                                        <div class="stat-box">
                                            <div class="value text-success">${availableSpots}</div>
                                            <div class="label">Available</div>
                                        </div>
                                        <div class="stat-box">
                                            <div class="value text-warning">${occupiedSpots}</div>
                                            <div class="label">Occupied</div>
                                        </div>
                                    </div>

                                    <div class="mb-3">
                                        <small class="text-muted">Occupancy Rate</small>
                                        <div class="progress progress-occupancy-bar">
                                            <div class="progress-bar ${barColor === 'danger' ? 'bg-danger' : barColor === 'warning' ? 'bg-warning' : 'bg-success'}" 
                                                 role="progressbar" 
                                                 style="width: ${occupancyRate}%">
                                            </div>
                                        </div>
                                        <small class="text-muted">${occupancyRate}% occupied</small>
                                    </div>

                                    <div class="d-grid gap-2">
                                        <button class="btn btn-outline-primary btn-view-details" data-area-id="${area.parking_area_id}">
                                            <i class="fas fa-eye me-1"></i> View Layout
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </div>
                    `);

                    container.append(card);
                });

                // Add click event listener to entire parking area cards
                $('.clickable-card').off('click').on('click', function (e) {
                    // Don't trigger if clicking on the button
                    if (!$(e.target).closest('.btn-view-details').length) {
                        const areaId = $(this).data('area-id');
                        openAreaDetails(areaId);
                    }
                });

                $('.btn-view-details').off('click').on('click', function (e) {
                    e.stopPropagation();
                    const areaId = $(this).data('area-id');
                    openAreaLayout(areaId);
                });
            }

            // Render list view
            function renderListView(areas, container) {
                const listCard = $(`
                    <div class="col-12">
                        <div class="card shadow-sm">
                            <div class="card-body p-0">
                                <div class="table-responsive">
                                    <table class="table table-hover mb-0">
                                        <thead class="table-light">
                                            <tr>
                                                <th class="ps-3">#</th>
                                                <th>Area Name</th>
                                                <th>Location</th>
                                                <th class="text-center">Floors</th>
                                                <th class="text-center">Sections</th>
                                                <th class="text-center">Parking Spaces</th>
                                                <th class="text-center">Available</th>
                                                <th class="text-center">Occupied</th>
                                                <th class="text-center">Occupancy</th>
                                                <th class="text-center">Actions</th>
                                            </tr>
                                        </thead>
                                        <tbody id="listViewBody">
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
                    </div>
                `);

                container.append(listCard);
                const tbody = $('#listViewBody');

                areas.forEach((area, index) => {
                    const totalSpots = parseInt(area.total_spots || 0);
                    const occupiedSpots = parseInt(area.occupied_spots || 0);
                    const availableSpots = parseInt(area.available_spots || 0);
                    const occupancyRate = totalSpots > 0 ? ((occupiedSpots / totalSpots) * 100).toFixed(1) : 0;

                    let statusColor = 'success';
                    if (occupancyRate > 75) statusColor = 'danger';
                    else if (occupancyRate > 50) statusColor = 'warning';

                    const row = $(`
                        <tr class="list-view-row" data-area-id="${area.parking_area_id}">
                            <td class="ps-3 text-muted">#${index + 1}</td>
                            <td><strong>${escapeHtml(area.parking_area_name)}</strong></td>
                            <td><small class="text-muted"><i class="fas fa-map-marker-alt me-1"></i>${escapeHtml(area.location || 'N/A')}</small></td>
                            <td class="text-center"><span class="badge bg-secondary">${area.num_of_floors || 1}</span></td>
                            <td class="text-center"><span class="badge bg-info">${area.total_sections || 0}</span></td>
                            <td class="text-center"><strong>${totalSpots}</strong></td>
                            <td class="text-center"><span class="text-success fw-bold">${availableSpots}</span></td>
                            <td class="text-center"><span class="text-danger fw-bold">${occupiedSpots}</span></td>
                            <td class="text-center"><span class="badge bg-${statusColor}">${occupancyRate}%</span></td>
                            <td class="text-center">
                                <button class="btn btn-sm btn-outline-primary btn-view-details" data-area-id="${area.parking_area_id}">
                                    <i class="fas fa-eye"></i>
                                </button>
                            </td>
                        </tr>
                    `);

                    tbody.append(row);
                });

                $('.btn-view-details').off('click').on('click', function (e) {
                    e.stopPropagation();
                    const areaId = $(this).data('area-id');
                    openAreaLayout(areaId);
                });

                $('#listViewBody tr').off('click').on('click', function () {
                    const areaId = $(this).data('area-id');
                    openAreaDetails(areaId);
                });
            }

            function openAreaLayout(areaId) {
                window.currentAreaId = areaId;
                const area = allAreasData.find(a => a.parking_area_id == areaId);
                const areaName = area ? (area.parking_area_name || '') : '';

                // Ensure any open area-detail modal is closed (if user opened it previously)
                const detailEl = document.getElementById('areaDetailModal');
                if (detailEl) {
                    const detailModal = bootstrap.Modal.getInstance(detailEl);
                    if (detailModal) {
                        detailModal.hide();
                    }
                }

                if (typeof window.openLayoutVisualization === 'function') {
                    // openLayoutVisualization uses window.currentAreaId and loads the layout
                    window.openLayoutVisualization();
                } else {
                    console.error('openLayoutVisualization is not available');
                    showToast(`Unable to open layout for ${areaName || 'selected area'}`, 'error');
                }
            }

            // Open area details modal (separate from layout view)
            function openAreaDetails(areaId) {
                window.currentAreaId = areaId;
                const area = allAreasData.find(a => a.parking_area_id == areaId);
                if (!area) return;

                // Use the existing showAreaDetails function to populate the modal
                showAreaDetails(areaId);

                // Open the area detail modal
                const modal = bootstrap.Modal.getOrCreateInstance(document.getElementById('areaDetailModal'), {
                    backdrop: true,
                    keyboard: true,
                    focus: false
                });
                modal.show();
            }

            // Show area details
            function showAreaDetails(areaId) {
                window.currentAreaId = areaId;
                const area = allAreasData.find(a => a.parking_area_id == areaId);
                if (!area) return;

                $('#modalAreaName').html(`<span class="text-white">${escapeHtml(area.parking_area_name)}</span>`);
                $('#modalAreaLocation').text(area.location || 'No location');
                $('#modalAreaFloors').text(area.num_of_floors || 1);
                $('#modalAreaSections').text(area.total_sections || 0);

                const totalSpots = parseInt(area.total_spots || 0);
                const occupiedSpots = parseInt(area.occupied_spots || 0);
                const availableSpots = parseInt(area.available_spots || 0);
                const occupancyRate = totalSpots > 0 ? ((occupiedSpots / totalSpots) * 100).toFixed(1) : 0;

                $('#modalOccupiedCount').text(occupiedSpots);
                $('#modalAvailableCount').text(availableSpots);
                $('#modalOccupancyBar').css('width', occupancyRate + '%').text(occupancyRate + '%');

                loadAreaSections(areaId);

                const modal = bootstrap.Modal.getOrCreateInstance(document.getElementById('areaDetailModal'), {
                    backdrop: true,
                    keyboard: true,
                    focus: false
                });
                modal.show();
            }

            window.showAreaDetails = showAreaDetails;

            // Load sections for area
            function loadAreaSections(areaId) {
                $('#floorLayoutContainer').html('<div class="text-center py-4"><div class="spinner-border"></div></div>');

                $.ajax({
                    url: `${baseUrl}api/parking/sections/${areaId}`,
                    method: 'GET',
                    success: function (response) {
                        if (response.success) {
                            renderFloorLayout(response.data);
                        }
                    },
                    error: function () {
                        $('#floorLayoutContainer').html('<p class="text-muted text-center py-3">Failed to load sections</p>');
                    }
                });
            }

            // Render floor layout
            function renderFloorLayout(sections) {
                if (!sections || sections.length === 0) {
                    $('#floorLayoutContainer').html('<p class="text-muted text-center py-3">No sections found</p>');
                    return;
                }

                const floors = {};
                sections.forEach(section => {
                    const floor = section.floor || section.floor_number || 1;
                    if (!floors[floor]) floors[floor] = [];
                    floors[floor].push(section);
                });

                let html = '<div class="floor-tabs">';
                Object.keys(floors).sort((a, b) => a - b).forEach((floor, index) => {
                    html += `<div class="floor-tab ${index === 0 ? 'active' : ''}" data-floor="${floor}">
                        <i class="fas fa-layer-group me-1"></i> Floor ${floor}
                    </div>`;
                });
                html += '</div>';

                Object.keys(floors).sort((a, b) => a - b).forEach((floor, index) => {
                    html += `<div class="floor-content ${index === 0 ? 'active' : ''}" data-floor="${floor}">`;
                    html += '<div class="section-grid">';

                    floors[floor].forEach(section => {
                        const isCapacityOnly = section.section_mode === 'capacity_only';
                        const spots = isCapacityOnly ? (section.capacity || 0) : (section.rows || 0) * (section.columns || 0);
                        const areaName = $('#modalAreaName').text() || '';
                        const sectionName = escapeHtml(section.section_name);
                        const vehicleType = escapeHtml(section.vehicle_type_name || section.vehicle_type || 'Car');

                        // Display format based on section mode
                        const spotDisplay = isCapacityOnly
                            ? `${spots} capacity`
                            : `${section.rows} × ${section.columns} = ${spots} parking spaces`;

                        html += `
                            <div class="section-item section-item-clickable" onclick="viewSectionGrid(${section.parking_section_id}, '${sectionName}', '${escapeHtml(areaName)}', ${JSON.stringify(section).replace(/"/g, '&quot;')})">
                                <div class="section-header">
                                    <div class="section-name">${sectionName}</div>
                                </div>
                                <div class="text-muted small mb-2">
                                    <i class="fas fa-car me-1"></i>${vehicleType}
                                </div>
                                <div class="text-muted small">
                                    <i class="fas fa-th me-1"></i>${spotDisplay}
                                </div>
                                <div class="section-stats">
                                    <div class="section-stat">
                                        <span class="section-stat-value text-success">${spots}</span>
                                        <span class="section-stat-label">Capacity</span>
                                    </div>
                                </div>
                                <div class="mt-2">
                                    <button class="btn btn-sm btn-outline-primary w-100" onclick="event.stopPropagation(); viewSectionGrid(${section.parking_section_id}, '${sectionName}', '${escapeHtml(areaName)}', ${JSON.stringify(section).replace(/"/g, '&quot;')})">
                                        <i class="fas fa-th me-1"></i> View Grid
                                    </button>
                                </div>
                            </div>
                        `;
                    });

                    html += '</div></div>';
                });

                $('#floorLayoutContainer').html(html);

                $('.floor-tab').off('click').on('click', function () {
                    const floor = $(this).data('floor');
                    $('.floor-tab').removeClass('active');
                    $(this).addClass('active');
                    $('.floor-content').removeClass('active');
                    $(`.floor-content[data-floor="${floor}"]`).addClass('active');
                });
            }

            // Helper: Escape HTML
            function escapeHtml(text) {
                if (!text) return '';
                const map = {
                    '&': '&amp;',
                    '<': '&lt;',
                    '>': '&gt;',
                    '"': '&quot;',
                    "'": '&#039;'
                };
                return String(text).replace(/[&<>"']/g, m => map[m]);
            }

            // Helper: Show error
            function showError(message) {
                $('#parkingAreasContainer').html(`
                    <div class="col-12">
                        <div class="alert alert-danger">
                            <i class="fas fa-exclamation-circle me-2"></i>${message}
                        </div>
                    </div>
                `);
            }

            // Modal blur handlers
            $('#areaDetailModal, #sectionGridModal').on('hide.bs.modal', function () {
                if (document.activeElement && document.activeElement.blur) {
                    document.activeElement.blur();
                }
            });

            // Modal backdrop cleanup handler
            $('#areaDetailModal, #sectionGridModal').on('hidden.bs.modal', function () {
                // Remove any remaining modal backdrops
                $('.modal-backdrop').remove();
                // Remove modal-open class from body
                $('body').removeClass('modal-open');
                // Reset body padding
                $('body').css('padding-right', '');
            });

            // Return early to prevent other page scripts from running
            return;
        }

        // Call original initPageScripts for other pages
        if (originalInitPageScripts) {
            originalInitPageScripts();
        }
    };
}

// Global variables
window.currentSectionId = window.currentSectionId || null;
window.currentAreaId = window.currentAreaId || null;

// Helper: Escape HTML (make it globally available)
window.escapeHtml = window.escapeHtml || function (text) {
    if (!text) return '';
    const map = {
        '&': '&amp;',
        '<': '&lt;',
        '>': '&gt;',
        '"': '&quot;',
        "'": '&#039;'
    };
    return String(text).replace(/[&<>"']/g, m => map[m]);
};

// View section grid
window.viewSectionGrid = function (sectionId, sectionName, areaName, completeSectionData) {
    window.currentSectionId = sectionId;

    // Store complete section data globally for use in grid rendering
    if (completeSectionData) {
        window.currentSectionData = completeSectionData;
        console.log('Stored complete section data:', completeSectionData);
    }

    // Reset capacity view state
    window.showCapacitySlots = false;

    const escapedAreaName = window.escapeHtml(areaName || '');
    const escapedSectionName = window.escapeHtml(sectionName || 'Section Grid View');

    if (areaName) {
        $('#gridSectionName').html(`<span class="text-white-50" style="opacity: 0.8;">${escapedAreaName}</span> <span class="text-white">/</span> <span class="text-white">${escapedSectionName}</span>`);
    } else {
        $('#gridSectionName').html(`<span class="text-white">${escapedSectionName}</span>`);
    }

    $('#gridSearchInput').val('');
    $('input[name="gridFilter"][value="all"]').prop('checked', true);

    // Close area detail modal
    const areaModalEl = document.getElementById('areaDetailModal');
    if (areaModalEl) {
        const focusedElement = areaModalEl.querySelector(':focus');
        if (focusedElement) {
            focusedElement.blur();
        }
        const areaModal = bootstrap.Modal.getInstance(areaModalEl);
        if (areaModal) {
            areaModal.hide();
        }
    }

    setTimeout(() => {
        $('.modal-backdrop').remove();
        $('body').removeClass('modal-open').css('padding-right', '');

        const gridModalEl = document.getElementById('sectionGridModal');
        const gridModal = bootstrap.Modal.getOrCreateInstance(gridModalEl, {
            backdrop: 'static',
            keyboard: false,
            focus: false
        });
        gridModal.show();

        loadSectionGrid(sectionId);
    }, 300);
};

// Refresh section grid
window.refreshSectionGrid = function () {
    if (window.currentSectionId) {
        loadSectionGrid(window.currentSectionId);
    }
};

// Load section grid data
function loadSectionGrid(sectionId) {
    const baseUrl = window.APP_BASE_URL || '';

    $('#parkingGridContainer').html(`
        <div class="text-center py-5">
            <div class="spinner-border text-primary"></div>
            <p class="mt-2 text-muted">Loading grid...</p>
        </div>
    `);

    $.ajax({
        url: `${baseUrl}api/parking/section-grid/${sectionId}`,
        method: 'GET',
        success: function (response) {
            if (response.success) {
                renderParkingGrid(response.data);
            } else {
                $('#parkingGridContainer').html(`
                    <div class="alert alert-danger">
                        <i class="fas fa-exclamation-circle me-2"></i>${response.message || 'Failed to load grid'}
                    </div>
                `);
            }
        },
        error: function () {
            $('#parkingGridContainer').html(`
                <div class="alert alert-danger">
                    <i class="fas fa-exclamation-circle me-2"></i>Error loading grid data
                </div>
            `);
        }
    });
}

// Render parking grid
function renderParkingGrid(data) {
    const { section, spots } = data;

    // Use complete section data from view details if available, otherwise use API data
    const completeSectionData = window.currentSectionData || section;

    // Debug: Log the section data to see what's returned
    console.log('=== SECTION GRID DEBUG ===');
    console.log('API section data:', section);
    console.log('Complete section data from view details:', completeSectionData);
    console.log('Section mode:', completeSectionData.section_mode);
    console.log('Vehicle type:', completeSectionData.vehicle_type);
    console.log('Capacity:', completeSectionData.capacity);
    console.log('Rows:', completeSectionData.rows, 'Columns:', completeSectionData.columns);

    // Check if this is a capacity-only section using complete data
    const isCapacityOnly = completeSectionData.section_mode === 'capacity_only';

    // Fallback 1: Check if capacity is significantly larger than grid dimensions
    const gridCapacity = (parseInt(completeSectionData.rows || 0) * parseInt(completeSectionData.columns || 0));
    const actualCapacity = parseInt(completeSectionData.capacity || gridCapacity || 0);
    const isCapacityOnlyBySize = actualCapacity > 0 && actualCapacity > gridCapacity;

    // Fallback 2: Check vehicle types that are typically capacity_only (motorcycle, bicycle)
    const vehicleType = (completeSectionData.vehicle_type || '').toLowerCase();
    const isCapacityOnlyByVehicle = ['motorcycle', 'motor', 'bicycle', 'bike'].includes(vehicleType);

    // Fallback 3: Check if section name suggests capacity-only (common patterns)
    const sectionNameLower = (completeSectionData.section_name || '').toLowerCase();
    const isCapacityOnlyByName = ['motor', 'bike', 'bicycle', 'mc'].some(pattern => sectionNameLower.includes(pattern));

    console.log('Grid capacity (rows×cols):', gridCapacity);
    console.log('Actual capacity from DB:', actualCapacity);
    console.log('Is capacity-only by mode:', isCapacityOnly);
    console.log('Is capacity-only by size:', isCapacityOnlyBySize);
    console.log('Is capacity-only by vehicle:', isCapacityOnlyByVehicle);
    console.log('Is capacity-only by name:', isCapacityOnlyByName);

    if (isCapacityOnly || isCapacityOnlyBySize || isCapacityOnlyByVehicle || isCapacityOnlyByName) {
        // For capacity-only sections, render as a single block
        console.log('Rendering capacity-only grid...');

        // Show the capacity toggle button for capacity-only sections
        $('#capacityToggleContainer').show();

        renderCapacityOnlyGrid(completeSectionData, spots);
        return;
    }

    // Hide the capacity toggle button for regular sections
    $('#capacityToggleContainer').hide();

    console.log('Rendering regular slot-based grid...');
    // Ensure we parse rows and columns correctly (handle both string and number)
    const rows = parseInt(completeSectionData.rows || 0);
    const cols = parseInt(completeSectionData.columns || 0);

    if (rows === 0 || cols === 0) {
        $('#parkingGridContainer').html('<p class="text-muted text-center">Invalid grid dimensions</p>');
        return;
    }

    // Calculate total spots based on grid dimensions (this should match the visual grid)
    let totalSpots = rows * cols;
    let occupiedCount = 0;
    let availableCount = 0;
    let reservedCount = 0;

    // Build spots map for quick lookup
    const spotsMap = {};
    spots.forEach(spot => {
        const key = `${spot.grid_row}-${spot.grid_col}`;
        spotsMap[key] = spot;
    });

    // Count statistics based on all grid positions (matching what will be rendered)
    for (let row = 0; row < rows; row++) {
        for (let col = 0; col < cols; col++) {
            const key = `${row}-${col}`;
            const spot = spotsMap[key];

            if (spot) {
                // Spot exists in database
                if (spot.is_occupied || spot.is_occupied === '1' || spot.is_occupied === 1) {
                    occupiedCount++;
                } else if (spot.status === 'reserved' || spot.status === 'Reserved') {
                    reservedCount++;
                    // Reserved spots are not available, but also not occupied - they're a separate category
                    // For now, we'll count them separately, but they shouldn't be in available count
                } else {
                    availableCount++;
                }
            } else {
                // Spot doesn't exist in database, treat as available (same as rendering logic)
                availableCount++;
            }
        }
    }

    // Verify counts match (should always be true if logic is correct)
    // If there's a discrepancy, log it for debugging but use loop counts
    const calculatedTotal = availableCount + occupiedCount + reservedCount;
    if (calculatedTotal !== totalSpots) {
        console.warn('Spot count mismatch:', { totalSpots, calculatedTotal, availableCount, occupiedCount, reservedCount });
        // Ensure available count accounts for any discrepancies
        availableCount = Math.max(0, totalSpots - occupiedCount - reservedCount);
    }

    // Update stats
    $('#gridTotalSpots').text(totalSpots);
    $('#gridAvailableSpots').text(availableCount);
    $('#gridOccupiedSpots').text(occupiedCount);
    const occupancyRate = totalSpots > 0 ? ((occupiedCount / totalSpots) * 100).toFixed(1) : 0;
    $('#gridOccupancyRate').text(occupancyRate + '%');

    // Build grid HTML
    let gridHTML = `<div class="parking-grid" style="grid-template-columns: repeat(${cols}, 70px);">`;

    // Get section name for ID generation (same format as layout designer)
    const sectionName = section.section_name || section.type || 'section';

    for (let row = 0; row < rows; row++) {
        for (let col = 0; col < cols; col++) {
            const key = `${row}-${col}`;
            const spot = spotsMap[key];

            // Calculate slot number (same as layout designer: row * cols + col + 1)
            const slotNumber = (row * cols) + col + 1;

            // Generate ID in same format as layout designer: "SectionName-SlotNumber" (e.g., "C-1", "C-2")
            const slotId = `${sectionName}-${slotNumber}`;

            if (spot) {
                let statusClass = 'available';
                let icon = 'fa-parking';
                let tooltipText = `${spot.spot_number} - Available`;

                if (spot.is_occupied || spot.is_occupied === '1' || spot.is_occupied === 1) {
                    statusClass = 'occupied';
                    icon = 'fa-car';
                    tooltipText = `${spot.spot_number} - Occupied`;
                    if (spot.occupied_by) {
                        tooltipText += ` by ${spot.occupied_by}`;
                    }
                } else if (spot.status === 'reserved') {
                    statusClass = 'reserved';
                    icon = 'fa-clock';
                    tooltipText = `${spot.spot_number} - Reserved`;
                }

                gridHTML += `
                    <div class="parking-spot ${statusClass}" 
                         id="${slotId}"
                         data-spot-id="${spot.parking_spot_id || ''}"
                         data-slot-number="${slotNumber}">
                        <div class="spot-icon"><i class="fas ${icon}"></i></div>
                        <div class="spot-number">${spot.spot_number || slotId}</div>
                        <div class="spot-status">${statusClass}</div>
                    </div>
                `;
            } else {
                // Generate spot number in same format as layout designer
                const spotNumber = `${sectionName}-${slotNumber}`;
                gridHTML += `
                    <div class="parking-spot available" 
                         id="${slotId}"
                         data-slot-number="${slotNumber}">
                        <div class="spot-icon"><i class="fas fa-parking"></i></div>
                        <div class="spot-number">${spotNumber}</div>
                        <div class="spot-status">available</div>
                    </div>
                `;
            }
        }
    }

    gridHTML += '</div>';
    $('#parkingGridContainer').html(gridHTML);

    // Setup search and filter
    setupGridSearchAndFilter();
}

// Render capacity-only grid as a single block or individual slots
function renderCapacityOnlyGrid(section, spots) {
    const sectionName = section.section_name || 'Section';
    const capacity = parseInt(section.capacity || 0);

    console.log('CAPACITY-ONLY RENDERING:');
    console.log('- Section:', sectionName);
    console.log('- Capacity:', capacity);
    console.log('- Section mode:', section.section_mode);
    console.log('- Show slots toggle:', window.showCapacitySlots);
    console.log('- Spots data:', spots);

    // Check if admin has toggled to show individual slots
    if (window.showCapacitySlots) {
        console.log('Using individual slots display (admin toggle)');
        renderCapacityIndividualSlots(section, spots);
    } else {
        console.log('Using single block display (default)');
        renderCapacitySingleBlock(section, spots);
    }
}

// Toggle capacity view between single block and individual slots
window.toggleCapacityView = function () {
    window.showCapacitySlots = !window.showCapacitySlots;

    const toggleBtn = $('#capacityToggleBtn');
    if (window.showCapacitySlots) {
        toggleBtn.html('<i class="fas fa-compress me-1"></i> Show Block');
        toggleBtn.removeClass('btn-warning').addClass('btn-info');
    } else {
        toggleBtn.html('<i class="fas fa-th me-1"></i> Show Slots');
        toggleBtn.removeClass('btn-info').addClass('btn-warning');
    }

    // Re-render the grid with the new view mode
    refreshSectionGrid();
};

// Show slot status menu for individual slot management
window.showSlotStatusMenu = function (slotId, slotNumber, currentStatus, spotId) {
    const statusOptions = [
        { value: 'available', label: 'Available', icon: 'fa-parking', color: 'success' },
        { value: 'occupied', label: 'Occupied', icon: 'fa-car', color: 'danger' },
        { value: 'reserved', label: 'Reserved', icon: 'fa-clock', color: 'warning' }
    ];

    let menuHTML = `
        <div class="slot-status-menu">
            <div class="slot-status-header">
                <h6>Change Status: ${slotId}</h6>
                <button type="button" class="btn-close" onclick="closeSlotStatusMenu()"></button>
            </div>
            <div class="slot-status-options">
    `;

    statusOptions.forEach(option => {
        const isActive = option.value === currentStatus;
        menuHTML += `
            <div class="slot-status-option ${isActive ? 'active' : ''}" 
                 onclick="updateSlotStatus('${slotId}', ${slotNumber}, '${option.value}', ${spotId})">
                <div class="status-icon">
                    <i class="fas ${option.icon} text-${option.color}"></i>
                </div>
                <div class="status-label">${option.label}</div>
                ${isActive ? '<div class="status-check"><i class="fas fa-check"></i></div>' : ''}
            </div>
        `;
    });

    menuHTML += `
            </div>
        </div>
    `;

    // Create modal overlay
    const overlay = $(`
        <div class="slot-status-overlay" onclick="closeSlotStatusMenu()">
            ${menuHTML}
        </div>
    `);

    $('body').append(overlay);

    // Position the menu near the clicked slot
    const slotElement = $(`#${slotId}`);
    const slotOffset = slotElement.offset();
    const slotWidth = slotElement.outerWidth();
    const slotHeight = slotElement.outerHeight();

    const menuElement = overlay.find('.slot-status-menu');
    const menuWidth = 200; // Approximate menu width

    // Calculate position
    let left = slotOffset.left + slotWidth / 2 - menuWidth / 2;
    let top = slotOffset.top + slotHeight + 10;

    // Adjust if menu goes off screen
    if (left < 10) left = 10;
    if (left + menuWidth > $(window).width() - 10) {
        left = $(window).width() - menuWidth - 10;
    }
    if (top + 200 > $(window).height() - 10) {
        top = slotOffset.top - 210; // Show above instead
    }

    menuElement.css({
        position: 'absolute',
        left: left + 'px',
        top: top + 'px',
        zIndex: 9999
    });

    // Fade in
    overlay.fadeIn(200);
}

// Close slot status menu
window.closeSlotStatusMenu = function () {
    $('.slot-status-overlay').fadeOut(200, function () {
        $(this).remove();
    });
}

// Update slot status
window.updateSlotStatus = function (slotId, slotNumber, newStatus, spotId) {
    console.log(`Updating ${slotId} to status: ${newStatus}`);

    // Here you would make an API call to update the spot status
    // For now, we'll just refresh the grid to simulate the change
    closeSlotStatusMenu();

    // Show loading indicator
    $('#parkingGridContainer').html(`
        <div class="text-center py-5">
            <div class="spinner-border text-primary"></div>
            <p class="mt-2 text-muted">Updating slot status...</p>
        </div>
    `);

    // Simulate API call
    setTimeout(() => {
        refreshSectionGrid();
    }, 500);
};

// Set grid filter from dropdown
window.setGridFilter = function (filterValue) {
    // Update the radio button for compatibility
    $(`input[name="gridFilter"][value="${filterValue}"]`).prop('checked', true);

    // Update dropdown button text
    const filterText = {
        'all': 'All',
        'available': 'Available',
        'occupied': 'Occupied',
        'reserved': 'Reserved'
    };

    $('#currentFilter').text(filterText[filterValue] || 'All');

    // Update active state in dropdown
    $('.dropdown-item').removeClass('active');
    $(`.dropdown-item[data-filter="${filterValue}"]`).addClass('active');

    // Apply filter
    filterGrid();
};

// Render capacity-only section as individual slots
function renderCapacityIndividualSlots(section, spots) {
    const sectionName = section.section_name || 'Section';
    const capacity = parseInt(section.capacity || 0);

    // Count spots by status
    let occupiedCount = 0;
    let reservedCount = 0;

    spots.forEach(spot => {
        if (spot.is_occupied || spot.is_occupied === '1' || spot.is_occupied === 1) {
            occupiedCount++;
        } else if (spot.status === 'reserved') {
            reservedCount++;
        }
    });

    const availableCount = capacity - occupiedCount - reservedCount;
    const occupancyRate = capacity > 0 ? ((occupiedCount / capacity) * 100).toFixed(1) : 0;

    // Update stats
    $('#gridTotalSpots').text(capacity);
    $('#gridAvailableSpots').text(availableCount);
    $('#gridOccupiedSpots').text(occupiedCount);
    $('#gridOccupancyRate').text(occupancyRate + '%');

    // Create individual slots grid
    let gridHTML = `<div class="parking-grid capacity-individual-slots" style="grid-template-columns: repeat(auto-fit, minmax(60px, 1fr));">`;

    for (let i = 1; i <= capacity; i++) {
        const slotId = `${sectionName}-${i.toString().padStart(2, '0')}`;
        const spot = spots[i - 1]; // Assume spots are in order

        let statusClass = 'available';
        let icon = 'fa-parking';
        let tooltipText = `${slotId} - Available (Click to change status)`;

        if (spot) {
            if (spot.is_occupied || spot.is_occupied === '1' || spot.is_occupied === 1) {
                statusClass = 'occupied';
                icon = 'fa-car';
                tooltipText = `${slotId} - Occupied${spot.occupied_by ? ' by ' + spot.occupied_by : ''} (Click to change status)`;
            } else if (spot.status === 'reserved') {
                statusClass = 'reserved';
                icon = 'fa-clock';
                tooltipText = `${slotId} - Reserved (Click to change status)`;
            }
        }

        gridHTML += `
            <div class="parking-spot ${statusClass} clickable-slot" 
                 id="${slotId}"
                 data-spot-id="${spot?.parking_spot_id || ''}"
                 data-slot-number="${i}"
                 data-current-status="${statusClass}"
                 onclick="showSlotStatusMenu('${slotId}', ${i}, '${statusClass}', ${spot?.parking_spot_id || 'null'})">
                <div class="spot-icon"><i class="fas ${icon}"></i></div>
                <div class="spot-number">${slotId}</div>
                <div class="spot-status">${statusClass}</div>
            </div>
        `;
    }

    gridHTML += '</div>';
    $('#parkingGridContainer').html(gridHTML);
}

// Render capacity-only section as single block
function renderCapacitySingleBlock(section, spots) {
    const sectionName = section.section_name || 'Section';
    const capacity = parseInt(section.capacity || 0);

    // Count occupied spots from the spots data
    let occupiedCount = 0;
    spots.forEach(spot => {
        if (spot.is_occupied || spot.is_occupied === '1' || spot.is_occupied === 1) {
            occupiedCount++;
        }
    });

    const availableCount = capacity - occupiedCount;
    const occupancyRate = capacity > 0 ? ((occupiedCount / capacity) * 100).toFixed(1) : 0;

    // Update stats
    $('#gridTotalSpots').text(capacity);
    $('#gridAvailableSpots').text(availableCount);
    $('#gridOccupiedSpots').text(occupiedCount);
    $('#gridOccupancyRate').text(occupancyRate + '%');

    // Create single block representation
    const gridHTML = `
        <div class="parking-grid capacity-only-grid">
            <div class="capacity-only-block ${occupiedCount > 0 ? 'has-occupied' : ''}" 
                 data-section-name="${sectionName}"
                 data-capacity="${capacity}"
                 data-occupied="${occupiedCount}">
                <div class="block-icon">
                    <i class="fas ${getVehicleIcon(section.vehicle_type || 'car')}"></i>
                </div>
                <div class="block-info">
                    <div class="section-name">${sectionName}</div>
                    <div class="occupancy-stats">${occupiedCount}/${capacity} occupied</div>
                    <div class="availability-info">${availableCount} available</div>
                </div>
            </div>
        </div>
    `;

    $('#parkingGridContainer').html(gridHTML);
}

// Get vehicle icon based on vehicle type
function getVehicleIcon(vehicleType) {
    const type = (vehicleType || '').toLowerCase();
    switch (type) {
        case 'motorcycle':
        case 'motor':
            return 'fa-motorcycle';
        case 'bicycle':
        case 'bike':
            return 'fa-bicycle';
        case 'car':
        case 'tahp':
        default:
            return 'fa-car';
    }
}

// Setup smart tooltip positioning - shows tooltip below if not enough space above
function setupSmartTooltips() {
    $('.parking-spot').off('mouseenter mouseleave').on('mouseenter', function () {
        const $spot = $(this);
        const tooltipText = $spot.attr('title');
        if (!tooltipText) return;

        // Remove existing tooltip if any
        $spot.find('.spot-tooltip').remove();

        // Get spot position
        const spotOffset = $spot.offset();
        const spotTop = spotOffset.top;
        const spotHeight = $spot.outerHeight();
        const viewportTop = $(window).scrollTop();
        const modalBody = $spot.closest('.modal-body');
        const modalBodyTop = modalBody.length ? modalBody.offset().top : viewportTop;

        // Calculate available space above
        const spaceAbove = spotTop - modalBodyTop;
        const tooltipHeight = 40; // Approximate tooltip height

        // Create tooltip element
        const $tooltip = $('<div class="spot-tooltip">' + tooltipText + '</div>');
        $spot.append($tooltip);

        // Position tooltip above or below based on available space
        if (spaceAbove > tooltipHeight + 20) {
            // Enough space above - show tooltip above
            $tooltip.css({
                'bottom': '100%',
                'top': 'auto',
                'margin-bottom': '8px',
                'margin-top': '0'
            }).attr('data-position', 'above');
        } else {
            // Not enough space above - show tooltip below
            $tooltip.css({
                'top': '100%',
                'bottom': 'auto',
                'margin-top': '8px',
                'margin-bottom': '0'
            }).attr('data-position', 'below');
        }

        // Center horizontally
        $tooltip.css({
            'left': '50%',
            'transform': 'translateX(-50%)'
        });

    }).on('mouseleave', function () {
        $(this).find('.spot-tooltip').remove();
    });
}

// Setup grid search and filter
function setupGridSearchAndFilter() {
    $('#gridSearchInput').off('input').on('input', function () {
        filterGrid();
    });

    $('input[name="gridFilter"]').off('change').on('change', function () {
        filterGrid();
    });
}

// Filter grid
function filterGrid() {
    const searchTerm = $('#gridSearchInput').val().toLowerCase();
    const filterStatus = $('input[name="gridFilter"]:checked').val();

    let visibleCount = 0;

    $('.parking-spot').each(function () {
        const $spot = $(this);
        const spotNumber = $spot.find('.spot-number').text().toLowerCase();
        const isAvailable = $spot.hasClass('available');
        const isOccupied = $spot.hasClass('occupied');

        const matchesSearch = spotNumber.includes(searchTerm);
        let matchesFilter = true;

        if (filterStatus === 'available' && !isAvailable) {
            matchesFilter = false;
        } else if (filterStatus === 'occupied' && !isOccupied) {
            matchesFilter = false;
        }

        if (matchesSearch && matchesFilter) {
            $spot.show();
            visibleCount++;
        } else {
            $spot.hide();
        }
    });

    if (visibleCount === 0 && (searchTerm || filterStatus !== 'all')) {
        if ($('#noResultsMessage').length === 0) {
            $('#parkingGridContainer').prepend(`
                <div id="noResultsMessage" class="alert alert-info">
                    <i class="fas fa-info-circle me-2"></i>No spots found matching your criteria.
                </div>
            `);
        }
    } else {
        $('#noResultsMessage').remove();
    }
}

// Close section grid modal entirely
window.closeSectionGridModal = function () {
    const gridModalEl = document.getElementById('sectionGridModal');
    if (gridModalEl) {
        const focusedElement = gridModalEl.querySelector(':focus');
        if (focusedElement) {
            focusedElement.blur();
        }
        const gridModal = bootstrap.Modal.getInstance(gridModalEl);
        if (gridModal) {
            gridModal.hide();
        }
    }

    setTimeout(() => {
        $('.modal-backdrop').remove();
        $('body').removeClass('modal-open').css('padding-right', '');
    }, 300);
};

// Back to area view
window.backToAreaView = function () {
    const gridModalEl = document.getElementById('sectionGridModal');
    if (gridModalEl) {
        const focusedElement = gridModalEl.querySelector(':focus');
        if (focusedElement) {
            focusedElement.blur();
        }
        const gridModal = bootstrap.Modal.getInstance(gridModalEl);
        if (gridModal) {
            gridModal.hide();
        }
    }

    setTimeout(() => {
        $('.modal-backdrop').remove();
        $('body').removeClass('modal-open').css('padding-right', '');

        if (window.currentAreaId && typeof window.showAreaDetails === 'function') {
            window.showAreaDetails(window.currentAreaId);
        }
    }, 300);
};

// Layout Visualization Functions
window.openLayoutVisualization = async function () {
    if (!window.currentAreaId) {
        showToast('Please select an area first', 'error');
        return;
    }

    const areaName = $('#modalAreaName').text();

    // Close area detail modal
    const areaModalEl = document.getElementById('areaDetailModal');
    if (areaModalEl) {
        const focusedElement = areaModalEl.querySelector(':focus');
        if (focusedElement) {
            focusedElement.blur();
        }
        const areaModal = bootstrap.Modal.getInstance(areaModalEl);
        if (areaModal) {
            areaModal.hide();
        }
    }

    // Show visualization modal with loading
    setTimeout(() => {
        $('#layoutModalTitle').text(`Parking Layout - ${areaName}`);
        const modal = bootstrap.Modal.getOrCreateInstance(document.getElementById('layoutVisualizationModal'), {
            backdrop: true,
            keyboard: true,
            focus: false
        });
        modal.show();

        $('#layoutVisualizationContent').html(`
            <div class="text-center py-5">
                <div class="spinner-border text-primary mb-3" role="status">
                    <span class="visually-hidden">Loading...</span>
                </div>
                <h3>Loading Layout...</h3>
                <p class="text-muted">Fetching layout data from database</p>
            </div>
        `);

        // Load layout data
        loadLayoutVisualization(window.currentAreaId, areaName);
    }, 300);
};

async function loadLayoutVisualization(areaId, areaName) {
    try {
        const baseUrl = window.APP_BASE_URL || '';

        // First, get available floors for this area
        const floorsResponse = await fetch(`${baseUrl}api/parking/sections/${areaId}`);
        const floorsResult = await floorsResponse.json();

        if (floorsResult.success && floorsResult.data && floorsResult.data.length > 0) {
            // Get unique floors
            const floors = [...new Set(floorsResult.data.map(s => s.floor))].sort();

            // Use the first floor
            const firstFloor = floors[0];

            // Fetch layout data
            const response = await fetch(`${baseUrl}api/parking/layout/${areaId}/${firstFloor}`);

            // Handle 404 - no layout found (this is expected, not an error)
            if (response.status === 404) {
                showNoLayoutMessage(areaName, firstFloor, floors, areaId);
                return;
            }

            // Check if response is ok (but ignore 404s as they're already handled)
            if (!response.ok && response.status !== 404) {
                throw new Error(`HTTP ${response.status}: ${response.statusText}`);
            }

            const result = await response.json();

            if (result.success && result.data) {
                renderLayoutVisualization(result.data, floors, areaId, areaName);
            } else {
                showNoLayoutMessage(areaName, firstFloor, floors, areaId);
            }
        } else {
            showNoLayoutMessage(areaName, null, [], areaId);
        }
    } catch (error) {
        console.error('Error loading layout:', error);
        $('#layoutVisualizationContent').html(`
            <div class="alert alert-danger m-4">
                <i class="fas fa-exclamation-triangle me-2"></i>
                Error loading layout: ${error.message}
            </div>
        `);
    }
}

function renderLayoutVisualization(data, floors, areaId, areaName) {
    // Calculate accurate stats from sections
    const sections = data.sections || [];
    const sectionsCount = sections.length;

    // Calculate total spots from actual sections data
    let totalSpots = 0;
    sections.forEach(section => {
        const rows = parseInt(section.rows || 0);
        const cols = parseInt(section.columns || 0);
        totalSpots += rows * cols;
    });

    // Use API-provided total_spots if available and valid, otherwise use calculated
    const finalTotalSpots = (data.total_spots && data.total_spots > 0) ? data.total_spots : totalSpots;

    // Get vehicle types
    const vehicleTypes = data.vehicle_types || 'Mixed';

    const content = `
        <div style="padding: 1.5rem;">
            <!-- Layout Info Bar -->
            <div class="section-info-bar d-flex justify-content-between align-items-center mb-3 p-3 bg-light rounded">
                <div class="section-stats d-flex gap-4">
                    <div class="stat-item">
                        <span class="stat-label text-muted small">Floor:</span>
                        <select id="floorSelector" class="form-select form-select-sm" style="max-width: 100px; font-size: 14px; padding: 2px 8px;" onchange="changeVisualizationFloor(${areaId})">
                            ${floors.map(f => `<option value="${f}" ${f == data.floor ? 'selected' : ''}>${f}</option>`).join('')}
                        </select>
                    </div>
                    <div class="stat-item">
                        <span class="stat-label text-muted small">Sections:</span>
                        <span class="stat-value fw-bold text-dark" id="layoutSectionsCount">${sectionsCount}</span>
                    </div>
                    <div class="stat-item">
                        <span class="stat-label text-muted small">Total Spots:</span>
                        <span class="stat-value fw-bold text-success" id="layoutTotalSpots">${finalTotalSpots}</span>
                    </div>
                    <div class="stat-item">
                        <span class="stat-label text-muted small">Vehicle Types:</span>
                        <span class="stat-value fw-bold text-primary" id="layoutVehicleTypes">${vehicleTypes}</span>
                    </div>
                </div>
            </div>
            
            <!-- Layout Grid -->
            <div class="mb-0">
                <div id="visualizationGrid" class="border rounded p-4 bg-white shadow-sm" style="overflow: auto; max-height: 600px; border-color: #dee2e6 !important; border-width: 1.5px !important;">
                    <!-- Grid will be rendered here -->
                </div>
            </div>
        </div>
    `;

    $('#layoutVisualizationContent').html(content);
    renderLayoutGrid(data);
}

function renderLayoutGrid(data) {
    const gridContainer = document.getElementById('visualizationGrid');
    if (!gridContainer) return;

    // Parse layout_data if it's a string
    let layoutData = data.layout_data;
    if (typeof layoutData === 'string') {
        try {
            layoutData = JSON.parse(layoutData);
        } catch (e) {
            console.error('Error parsing layout_data:', e);
            layoutData = null;
        }
    }

    // Check if we have SVG data (either directly or inside layout_data)
    const svgData = data.svg_data || (layoutData && layoutData.svg_data);

    if (svgData) {
        // Generate new clean SVG using same functions as design modal
        // The layoutData should already contain elements and sections
        const visualizationData = {
            elements: layoutData.elements || {},
            sections: layoutData.sections || []
        };
        generateCleanVisualizationSVG(visualizationData, gridContainer);
        return;
    }

    // Fallback: Render sections as cards if no SVG
    if (data.sections && data.sections.length > 0) {
        let html = '<div class="row g-3">';
        data.sections.forEach(section => {
            const totalSpots = (section.rows || 0) * (section.columns || 0);
            html += `
                <div class="col-md-6 col-lg-4">
                    <div class="card h-100 border-0 shadow-sm">
                        <div class="card-body p-3">
                            <h6 class="card-title fw-semibold mb-3 d-flex align-items-center" style="color: #800000;">
                                <i class="fas fa-parking me-2"></i>${section.section_name || 'Section'}
                            </h6>
                            <div class="mb-3">
                                <i class="fas fa-car me-2" style="color: #6c757d;"></i>
                                <small class="text-muted">${section.vehicle_type_name || 'All Vehicles'}</small>
                            </div>
                            <div class="d-flex justify-content-between mb-3 p-2 bg-light rounded">
                                <div>
                                    <div class="text-muted small mb-1">Grid Size</div>
                                    <div class="fw-bold" style="color: #495057;">${section.rows || 0} × ${section.columns || 0}</div>
                                </div>
                                <div>
                                    <div class="text-muted small mb-1">Total Spots</div>
                                    <div class="fw-bold" style="color: #800000;">${totalSpots}</div>
                                </div>
                            </div>
                            <button class="btn btn-maroon btn-sm w-100" onclick="viewSectionGrid(${section.parking_section_id}, '${section.section_name || ''}', '${data.area_name || ''}')">
                                <i class="fas fa-th me-1"></i>View Grid
                            </button>
                        </div>
                    </div>
                </div>
            `;
        });
        html += '</div>';
        gridContainer.innerHTML = html;
    } else {
        gridContainer.innerHTML = `
            <div class="alert alert-light border text-center py-5" style="background: #f8f9fa !important; border-color: #dee2e6 !important;">
                <i class="fas fa-info-circle me-2" style="color: #800000;"></i>
                <strong style="color: #495057;">No sections configured for this floor yet.</strong>
            </div>
        `;
    }
}

function showNoLayoutMessage(areaName, floor, floors, areaId) {
    const floorSelector = floors && floors.length > 0 ? `
        <div class="mb-4">
            <label class="form-label fw-semibold mb-2" style="color: #800000;">
                <i class="fas fa-layer-group me-2"></i>Select Floor:
            </label>
            <select id="floorSelector" class="form-select form-select-sm" style="max-width: 220px; border: 1.5px solid #ced4da; border-radius: 0.375rem;" onchange="changeVisualizationFloor(${areaId})">
                ${floors.map(f => `<option value="${f}" ${f == floor ? 'selected' : ''}>Floor ${f}</option>`).join('')}
            </select>
        </div>
    ` : '';

    $('#layoutVisualizationContent').html(`
        <div style="padding: 1.5rem;">
            ${floorSelector}
            <div class="text-center py-5">
                <div class="mb-4">
                    <i class="fas fa-map-marked-alt" style="font-size: 5rem; color: #dee2e6; margin-bottom: 20px;"></i>
                </div>
                <h4 class="fw-semibold mb-3" style="color: #495057;">No Layout Available</h4>
                <p class="text-muted mb-4" style="max-width: 500px; margin: 0 auto;">
                    ${floor ? `Floor ${floor} doesn't have a layout configured yet.` : 'This parking area doesn\'t have any layouts configured yet.'}
                    <br>Sections are available but no visual layout has been created.
                </p>
                <button class="btn btn-maroon btn-sm mt-3" onclick="openLayoutDesignerFromView()">
                    <i class="fas fa-pen me-2"></i>Create Layout
                </button>
            </div>
        </div>
    `);
}

window.changeVisualizationFloor = async function (areaId) {
    const baseUrl = window.APP_BASE_URL || '';
    const floorSelector = document.getElementById('floorSelector');
    const floor = floorSelector?.value;
    const areaName = $('#layoutModalTitle').text().replace('Parking Layout - ', '');

    if (!floor) {
        console.error('Floor selector not found or no floor selected');
        return;
    }

    $('#visualizationGrid').html(`
        <div class="text-center py-5">
            <div class="spinner-border" style="color: #800000; width: 3rem; height: 3rem; border-width: 0.3rem;"></div>
            <p class="mt-3 fw-semibold" style="color: #495057;">Loading floor ${floor}...</p>
        </div>
    `);

    try {
        const response = await fetch(`${baseUrl}api/parking/layout/${areaId}/${floor}`);

        // Handle 404 - no layout found for this floor (expected, not an error)
        // Note: Browser will still log 404, but it's expected behavior when no layout exists
        if (response.status === 404) {
            $('#visualizationGrid').html(`
                <div class="alert alert-light border text-center py-5" style="background: #f8f9fa !important; border-color: #dee2e6 !important;">
                    <i class="fas fa-info-circle me-2" style="color: #800000;"></i>
                    <strong style="color: #495057;">No layout found for Floor ${floor}.</strong>
                    <p class="text-muted mt-2 mb-3">This floor doesn't have a layout designed yet.</p>
                    <button class="btn btn-maroon btn-sm" onclick="openLayoutDesignerFromView()">
                        <i class="fas fa-pen me-2"></i>Create Layout
                    </button>
                </div>
            `);

            // Reset stats to default values safely
            const statsCards = document.querySelectorAll('.stats-card-modern');
            if (statsCards && statsCards.length >= 3) {
                const sectionsH4 = statsCards[0]?.querySelector('h4');
                if (sectionsH4) sectionsH4.textContent = '0';
                const spotsH4 = statsCards[1]?.querySelector('h4');
                if (spotsH4) spotsH4.textContent = '0';
                const typesH4 = statsCards[2]?.querySelector('h4');
                if (typesH4) typesH4.textContent = 'Mixed';
            }
            return;
        }

        // Check if response is ok (200-299)
        if (!response.ok) {
            throw new Error(`HTTP ${response.status}: ${response.statusText}`);
        }

        const result = await response.json();

        if (result.success && result.data) {
            renderLayoutGrid(result.data);

            // Update stats - calculate from sections data
            const stats = result.data;
            const sections = stats.sections || [];
            const sectionsCount = sections.length;

            // Calculate total spots from actual sections
            let totalSpots = 0;
            sections.forEach(section => {
                const rows = parseInt(section.rows || 0);
                const cols = parseInt(section.columns || 0);
                totalSpots += rows * cols;
            });

            // Use API-provided total_spots if available and valid, otherwise use calculated
            const finalTotalSpots = (stats.total_spots && stats.total_spots > 0) ? stats.total_spots : totalSpots;
            const vehicleTypes = stats.vehicle_types || 'Mixed';

            // Update stats cards - safely check if elements exist
            const statsCards = document.querySelectorAll('.stats-card-modern');
            if (statsCards && statsCards.length >= 3) {
                // Sections count
                const sectionsH4 = statsCards[0]?.querySelector('h4') || document.getElementById('layoutSectionsCount');
                if (sectionsH4) {
                    sectionsH4.textContent = sectionsCount;
                }

                // Total spots
                const spotsH4 = statsCards[1]?.querySelector('h4') || document.getElementById('layoutTotalSpots');
                if (spotsH4) {
                    spotsH4.textContent = finalTotalSpots;
                }

                // Vehicle types
                const typesH4 = statsCards[2]?.querySelector('h4') || document.getElementById('layoutVehicleTypes');
                if (typesH4) {
                    typesH4.textContent = vehicleTypes;
                }
            }
        } else {
            // API returned success: false
            $('#visualizationGrid').html(`
                <div class="alert alert-light border text-center py-5" style="background: #f8f9fa !important; border-color: #dee2e6 !important;">
                    <i class="fas fa-info-circle me-2" style="color: #800000;"></i>
                    <strong style="color: #495057;">No layout found for Floor ${floor}.</strong>
                    <p class="text-muted mt-2 mb-3">${result.message || 'This floor doesn\'t have a layout designed yet.'}</p>
                    <button class="btn btn-maroon btn-sm" onclick="openLayoutDesignerFromView()">
                        <i class="fas fa-pen me-2"></i>Create Layout
                    </button>
                </div>
            `);

            // Reset stats
            const statsCards = document.querySelectorAll('.stats-card-modern');
            if (statsCards && statsCards.length >= 3) {
                const sectionsH4 = statsCards[0]?.querySelector('h4');
                if (sectionsH4) sectionsH4.textContent = '0';
                const spotsH4 = statsCards[1]?.querySelector('h4');
                if (spotsH4) spotsH4.textContent = '0';
                const typesH4 = statsCards[2]?.querySelector('h4');
                if (typesH4) typesH4.textContent = 'Mixed';
            }
        }
    } catch (error) {
        console.error('Error changing floor:', error);
        $('#visualizationGrid').html(`
            <div class="alert alert-danger m-4">
                <i class="fas fa-exclamation-triangle me-2"></i>
                Error loading layout: ${error.message}
            </div>
        `);

        // Reset stats on error
        const statsCards = document.querySelectorAll('.stats-card-modern');
        if (statsCards && statsCards.length >= 3) {
            const sectionsH4 = statsCards[0]?.querySelector('h4');
            if (sectionsH4) sectionsH4.textContent = '0';
            const spotsH4 = statsCards[1]?.querySelector('h4');
            if (spotsH4) spotsH4.textContent = '0';
            const typesH4 = statsCards[2]?.querySelector('h4');
            if (typesH4) typesH4.textContent = 'Mixed';
        }
    }
};

// Open layout designer from view modal (closes view modal first)
window.openLayoutDesignerFromView = function () {
    // Close the view layout modal first using Bootstrap's modal instance
    const viewModalEl = document.getElementById('layoutVisualizationModal');
    if (viewModalEl) {
        const viewModal = bootstrap.Modal.getInstance(viewModalEl);
        if (viewModal) {
            viewModal.hide();
        } else {
            // If no instance exists, create one and hide it
            const modal = bootstrap.Modal.getOrCreateInstance(viewModalEl);
            modal.hide();
        }
    }

    // Small delay to ensure modal is fully closed before opening designer
    setTimeout(() => {
        if (typeof window.openLayoutDesigner === 'function') {
            window.openLayoutDesigner();
        } else {
            console.error('openLayoutDesigner function not found');
        }
    }, 300);
};

// Back to area details from layout visualization
window.backToAreaDetailsFromLayout = function () {
    const layoutModalEl = document.getElementById('layoutVisualizationModal');
    if (layoutModalEl) {
        const focusedElement = layoutModalEl.querySelector(':focus');
        if (focusedElement) {
            focusedElement.blur();
        }
        const layoutModal = bootstrap.Modal.getInstance(layoutModalEl);
        if (layoutModal) {
            layoutModal.hide();
        }
    }

    setTimeout(() => {
        $('.modal-backdrop').remove();
        $('body').removeClass('modal-open').css('padding-right', '');

        if (window.currentAreaId && typeof window.showAreaDetails === 'function') {
            window.showAreaDetails(window.currentAreaId);
        }
    }, 300);
};

window.closeLayoutVisualization = function () {
    const modalEl = document.getElementById('layoutVisualizationModal');
    if (modalEl) {
        const focusedElement = modalEl.querySelector(':focus');
        if (focusedElement) {
            focusedElement.blur();
        }
        const modal = bootstrap.Modal.getInstance(modalEl);
        if (modal) {
            modal.hide();
        }
    }
}

// Generate clean visualization SVG using same functions as design modal
// Import getElementSVG from layout-designer.js if not available
if (typeof getElementSVG === 'undefined') {
    // Copy the same functions from layout-designer.js
    function getElementSVG(elementType, direction = 'right', sectionType = null, slotNumber = null, sectionName = null) {
        switch (elementType) {
            case 'road':
                // Static road design - no generator
                if (direction === 'horizontal') {
                    return `<svg xmlns="http://www.w3.org/2000/svg" width="50" height="50" viewBox="0 0 50 50">
                        <rect x="0" y="0" width="51" height="51" fill="#4a4a4a"/>
                        <line x1="0" y1="25" x2="50" y2="25" stroke="#ffd54f" stroke-width="3"/>
                    </svg>`;
                } else if (direction === 'vertical') {
                    return `<svg xmlns="http://www.w3.org/2000/svg" width="50" height="50" viewBox="0 0 50 50">
                        <rect x="0" y="0" width="51" height="51" fill="#4a4a4a"/>
                        <line x1="25" y1="0" x2="25" y2="50" stroke="#ffd54f" stroke-width="3"/>
                    </svg>`;
                }
                return `<svg xmlns="http://www.w3.org/2000/svg" width="50" height="50" viewBox="0 0 50 50" shape-rendering="crispEdges">
                        <rect x="0" y="0" width="51" height="51" fill="#4a4a4a"/>
                        <line x1="0" y1="25" x2="50" y2="25" stroke="#ffd54f" stroke-width="3"/>
                    </svg>`;

            case 'l-road':
                // Static L-road design - no generator
                if (direction === 'right-down') {
                    return `<svg xmlns="http://www.w3.org/2000/svg" width="50" height="50" viewBox="0 0 50 50" shape-rendering="crispEdges">
                        <rect x="0" y="0" width="51" height="51" fill="#4a4a4a"/>
                        <path d="M 0 25 L 25 25 L 25 50" stroke="#ffd54f" stroke-width="3" fill="none"/>
                    </svg>`;
                } else if (direction === 'right-up') {
                    return `<svg xmlns="http://www.w3.org/2000/svg" width="50" height="50" viewBox="0 0 50 50" shape-rendering="crispEdges">
                        <rect x="0" y="0" width="51" height="51" fill="#4a4a4a"/>
                        <path d="M 0 25 L 25 25 L 25 0" stroke="#ffd54f" stroke-width="3" fill="none"/>
                    </svg>`;
                } else if (direction === 'left-down') {
                    return `<svg xmlns="http://www.w3.org/2000/svg" width="50" height="50" viewBox="0 0 50 50" shape-rendering="crispEdges">
                        <rect x="0" y="0" width="51" height="51" fill="#4a4a4a"/>
                        <path d="M 50 25 L 25 25 L 25 50" stroke="#ffd54f" stroke-width="3" fill="none"/>
                    </svg>`;
                } else if (direction === 'left-up') {
                    return `<svg xmlns="http://www.w3.org/2000/svg" width="50" height="50" viewBox="0 0 50 50" shape-rendering="crispEdges">
                        <rect x="0" y="0" width="51" height="51" fill="#4a4a4a"/>
                        <path d="M 50 25 L 25 25 L 25 0" stroke="#ffd54f" stroke-width="3" fill="none"/>
                    </svg>`;
                }
                return `<svg xmlns="http://www.w3.org/2000/svg" width="50" height="50" viewBox="0 0 50 50">
                    <rect x="0" y="0" width="50" height="50" fill="#4a4a4a"/>
                    <path d="M 0 25 L 25 25 L 25 50" stroke="#ffd54f" stroke-width="3" fill="none"/>
                </svg>`;

            case 't-road':
                if (direction === 'up' || direction === 'top') {
                    return `<svg xmlns="http://www.w3.org/2000/svg" width="50" height="50" viewBox="0 0 50 50" shape-rendering="crispEdges">
                        <rect x="0" y="0" width="51" height="51" fill="#4a4a4a"/>
                        <line x1="0" y1="25" x2="50" y2="25" stroke="#ffd54f" stroke-width="3"/>
                        <line x1="25" y1="25" x2="25" y2="50" stroke="#ffd54f" stroke-width="3"/>
                    </svg>`;
                } else if (direction === 'down' || direction === 'bottom') {
                    return `<svg xmlns="http://www.w3.org/2000/svg" width="50" height="50" viewBox="0 0 50 50" shape-rendering="crispEdges">
                        <rect x="0" y="0" width="51" height="51" fill="#4a4a4a"/>
                        <line x1="0" y1="25" x2="50" y2="25" stroke="#ffd54f" stroke-width="3"/>
                        <line x1="25" y1="0" x2="25" y2="25" stroke="#ffd54f" stroke-width="3"/>
                    </svg>`;
                } else if (direction === 'left') {
                    return `<svg xmlns="http://www.w3.org/2000/svg" width="50" height="50" viewBox="0 0 50 50" shape-rendering="crispEdges">
                        <rect x="0" y="0" width="51" height="51" fill="#4a4a4a"/>
                        <line x1="25" y1="0" x2="25" y2="50" stroke="#ffd54f" stroke-width="3"/>
                        <line x1="25" y1="25" x2="50" y2="25" stroke="#ffd54f" stroke-width="3"/>
                    </svg>`;
                } else if (direction === 'right') {
                    return `<svg xmlns="http://www.w3.org/2000/svg" width="50" height="50" viewBox="0 0 50 50" shape-rendering="crispEdges">
                        <rect x="0" y="0" width="51" height="51" fill="#4a4a4a"/>
                        <line x1="25" y1="0" x2="25" y2="50" stroke="#ffd54f" stroke-width="3"/>
                        <line x1="0" y1="25" x2="25" y2="25" stroke="#ffd54f" stroke-width="3"/>
                    </svg>`;
                }
                return `<svg xmlns="http://www.w3.org/2000/svg" width="50" height="50" viewBox="0 0 50 50">
                    <rect x="0" y="0" width="50" height="50" fill="#4a4a4a"/>
                    <line x1="0" y1="25" x2="50" y2="25" stroke="#ffd54f" stroke-width="3"/>
                    <line x1="25" y1="0" x2="25" y2="25" stroke="#ffd54f" stroke-width="3"/>
                </svg>`;

            case 'intersection':
                return `<svg xmlns="http://www.w3.org/2000/svg" width="50" height="50" viewBox="0 0 50 50" shape-rendering="crispEdges">
                    <rect x="0" y="0" width="51" height="51" fill="#4a4a4a"/>
                    <line x1="0" y1="25" x2="50" y2="25" stroke="#ffd54f" stroke-width="3"/>
                    <line x1="25" y1="0" x2="25" y2="50" stroke="#ffd54f" stroke-width="3"/>
                </svg>`;

            case 'entrance':
                // Modernized entrance design with bold arrow and IN text
                if (direction === 'left') {
                    return `<svg xmlns="http://www.w3.org/2000/svg" width="50" height="50" viewBox="0 0 50 50">
                        <rect x="0" y="0" width="51" height="51" fill="#4a4a4a"/>
                        <rect x="6" y="6" width="38" height="38" rx="8" fill="#4CAF50"/>
                        <text x="25" y="22" font-family="Segoe UI, Arial" font-size="13" font-weight="900" fill="white" text-anchor="middle">IN</text>
                        <path d="M 33 35 L 17 35 M 22 30 L 17 35 L 22 40" stroke="white" stroke-width="3" fill="none" stroke-linecap="round" stroke-linejoin="round"/>
                    </svg>`;
                } else if (direction === 'up') {
                    return `<svg xmlns="http://www.w3.org/2000/svg" width="50" height="50" viewBox="0 0 50 50">
                        <rect x="0" y="0" width="51" height="51" fill="#4a4a4a"/>
                        <rect x="6" y="6" width="38" height="38" rx="8" fill="#4CAF50"/>
                        <text x="25" y="22" font-family="Segoe UI, Arial" font-size="13" font-weight="900" fill="white" text-anchor="middle">IN</text>
                        <path d="M 25 40 L 25 30 M 20 35 L 25 30 L 30 35" stroke="white" stroke-width="3" fill="none" stroke-linecap="round" stroke-linejoin="round"/>
                    </svg>`;
                } else if (direction === 'down') {
                    return `<svg xmlns="http://www.w3.org/2000/svg" width="50" height="50" viewBox="0 0 50 50">
                        <rect x="0" y="0" width="51" height="51" fill="#4a4a4a"/>
                        <rect x="6" y="6" width="38" height="38" rx="8" fill="#4CAF50"/>
                        <text x="25" y="22" font-family="Segoe UI, Arial" font-size="13" font-weight="900" fill="white" text-anchor="middle">IN</text>
                        <path d="M 25 30 L 25 40 M 20 35 L 25 40 L 30 35" stroke="white" stroke-width="3" fill="none" stroke-linecap="round" stroke-linejoin="round"/>
                    </svg>`;
                }
                // Default: right
                return `<svg xmlns="http://www.w3.org/2000/svg" width="50" height="50" viewBox="0 0 50 50">
                    <rect x="0" y="0" width="51" height="51" fill="#4a4a4a"/>
                    <rect x="6" y="6" width="38" height="38" rx="8" fill="#4CAF50"/>
                    <text x="25" y="22" font-family="Segoe UI, Arial" font-size="13" font-weight="900" fill="white" text-anchor="middle">IN</text>
                    <path d="M 17 35 L 33 35 M 28 30 L 33 35 L 28 40" stroke="white" stroke-width="3" fill="none" stroke-linecap="round" stroke-linejoin="round"/>
                </svg>`;
            case 'exit':
                // Modernized exit design with bold arrow and OUT text
                if (direction === 'left') {
                    return `<svg xmlns="http://www.w3.org/2000/svg" width="50" height="50" viewBox="0 0 50 50">
                        <rect x="0" y="0" width="51" height="51" fill="#4a4a4a"/>
                        <rect x="6" y="6" width="38" height="38" rx="8" fill="#f44336"/>
                        <text x="25" y="22" font-family="Segoe UI, Arial" font-size="11" font-weight="900" fill="white" text-anchor="middle">OUT</text>
                        <path d="M 33 35 L 17 35 M 22 30 L 17 35 L 22 40" stroke="white" stroke-width="3" fill="none" stroke-linecap="round" stroke-linejoin="round"/>
                    </svg>`;
                } else if (direction === 'up') {
                    return `<svg xmlns="http://www.w3.org/2000/svg" width="50" height="50" viewBox="0 0 50 50">
                        <rect x="0" y="0" width="51" height="51" fill="#4a4a4a"/>
                        <rect x="6" y="6" width="38" height="38" rx="8" fill="#f44336"/>
                        <text x="25" y="22" font-family="Segoe UI, Arial" font-size="11" font-weight="900" fill="white" text-anchor="middle">OUT</text>
                        <path d="M 25 40 L 25 30 M 20 35 L 25 30 L 30 35" stroke="white" stroke-width="3" fill="none" stroke-linecap="round" stroke-linejoin="round"/>
                    </svg>`;
                } else if (direction === 'down') {
                    return `<svg xmlns="http://www.w3.org/2000/svg" width="50" height="50" viewBox="0 0 50 50">
                        <rect x="0" y="0" width="51" height="51" fill="#4a4a4a"/>
                        <rect x="6" y="6" width="38" height="38" rx="8" fill="#f44336"/>
                        <text x="25" y="22" font-family="Segoe UI, Arial" font-size="11" font-weight="900" fill="white" text-anchor="middle">OUT</text>
                        <path d="M 25 30 L 25 40 M 20 35 L 25 40 L 30 35" stroke="white" stroke-width="3" fill="none" stroke-linecap="round" stroke-linejoin="round"/>
                    </svg>`;
                }
                // Default: right
                return `<svg xmlns="http://www.w3.org/2000/svg" width="50" height="50" viewBox="0 0 50 50">
                    <rect x="0" y="0" width="51" height="51" fill="#4a4a4a"/>
                    <rect x="6" y="6" width="38" height="38" rx="8" fill="#f44336"/>
                    <text x="25" y="22" font-family="Segoe UI, Arial" font-size="11" font-weight="900" fill="white" text-anchor="middle">OUT</text>
                    <path d="M 17 35 L 33 35 M 28 30 L 33 35 L 28 40" stroke="white" stroke-width="3" fill="none" stroke-linecap="round" stroke-linejoin="round"/>
                </svg>`;

            case 'oneway':
                if (direction === 'right') {
                    return `<svg xmlns="http://www.w3.org/2000/svg" width="50" height="50" viewBox="0 0 50 50">
                        <rect x="0" y="0" width="51" height="51" fill="#4a4a4a"/>
                        <line x1="4" y1="25" x2="46" y2="25" stroke="#ffd54f" stroke-width="2"/>
                        <line x1="8" y1="19" x2="35" y2="19" stroke="#ffd54f" stroke-width="3" stroke-linecap="round"/>
                        <polygon points="35,14 45,19 35,24" fill="#ffffff"/>
                        <line x1="8" y1="31" x2="35" y2="31" stroke="#ffd54f" stroke-width="3" stroke-linecap="round"/>
                        <polygon points="35,26 45,31 35,36" fill="#ffffff"/>
                    </svg>`;
                } else if (direction === 'left') {
                    return `<svg xmlns="http://www.w3.org/2000/svg" width="50" height="50" viewBox="0 0 50 50">
                        <rect x="0" y="0" width="51" height="51" fill="#4a4a4a"/>
                        <line x1="4" y1="25" x2="46" y2="25" stroke="#ffd54f" stroke-width="2"/>
                        <line x1="42" y1="19" x2="15" y2="19" stroke="#ffd54f" stroke-width="3" stroke-linecap="round"/>
                        <polygon points="15,14 5,19 15,24" fill="#ffffff"/>
                        <line x1="42" y1="31" x2="15" y2="31" stroke="#ffd54f" stroke-width="3" stroke-linecap="round"/>
                        <polygon points="15,26 5,31 15,36" fill="#ffffff"/>
                    </svg>`;
                } else if (direction === 'up') {
                    return `<svg xmlns="http://www.w3.org/2000/svg" width="50" height="50" viewBox="0 0 50 50">
                        <rect x="0" y="0" width="51" height="51" fill="#4a4a4a"/>
                        <line x1="25" y1="4" x2="25" y2="46" stroke="#ffd54f" stroke-width="2"/>
                        <line x1="19" y1="42" x2="19" y2="15" stroke="#ffd54f" stroke-width="3" stroke-linecap="round"/>
                        <polygon points="14,15 19,5 24,15" fill="#ffffff"/>
                        <line x1="31" y1="42" x2="31" y2="15" stroke="#ffd54f" stroke-width="3" stroke-linecap="round"/>
                        <polygon points="26,15 31,5 36,15" fill="#ffffff"/>
                    </svg>`;
                }
                return `<svg xmlns="http://www.w3.org/2000/svg" width="50" height="50" viewBox="0 0 50 50">
                    <rect x="0" y="0" width="51" height="51" fill="#4a4a4a"/>
                    <line x1="25" y1="4" x2="25" y2="46" stroke="#ffd54f" stroke-width="2"/>
                    <line x1="19" y1="8" x2="19" y2="35" stroke="#ffd54f" stroke-width="3" stroke-linecap="round"/>
                    <polygon points="14,35 19,45 24,35" fill="#ffffff"/>
                    <line x1="31" y1="8" x2="31" y2="35" stroke="#ffd54f" stroke-width="3" stroke-linecap="round"/>
                    <polygon points="26,35 31,45 36,35" fill="#ffffff"/>
                </svg>`;

            case 'two-way':
                if (direction === 'vertical') {
                    return `<svg xmlns="http://www.w3.org/2000/svg" width="50" height="50" viewBox="0 0 50 50">
                        <g transform="rotate(90 25 25)">
                            <rect x="0" y="0" width="51" height="51" fill="#4a4a4a"/>
                            <rect x="6" y="6" width="38" height="38" rx="8" fill="#4a4a4a"/>
                            <line x1="8" y1="25" x2="42" y2="25" stroke="#ffd54f" stroke-width="3"/>
                            <line x1="10" y1="17" x2="30" y2="17" stroke="#ffffff" stroke-width="3.4" stroke-linecap="round"/>
                            <polygon points="30,11 40,17 30,23" fill="#ffffff"/>
                            <line x1="40" y1="33" x2="20" y2="33" stroke="#ffffff" stroke-width="3.4" stroke-linecap="round"/>
                            <polygon points="20,27 10,33 20,39" fill="#ffffff"/>
                        </g>
                    </svg>`;
                }
                return `<svg xmlns="http://www.w3.org/2000/svg" width="50" height="50" viewBox="0 0 50 50">
                    <rect x="0" y="0" width="51" height="51" fill="#4a4a4a"/>
                    <rect x="6" y="6" width="38" height="38" rx="8" fill="#4a4a4a"/>
                    <line x1="8" y1="25" x2="42" y2="25" stroke="#ffd54f" stroke-width="3"/>
                    <line x1="10" y1="17" x2="30" y2="17" stroke="#ffffff" stroke-width="3.4" stroke-linecap="round"/>
                    <polygon points="30,11 40,17 30,23" fill="#ffffff"/>
                    <line x1="40" y1="33" x2="20" y2="33" stroke="#ffffff" stroke-width="3.4" stroke-linecap="round"/>
                    <polygon points="20,27 10,33 20,39" fill="#ffffff"/>
                </svg>`;

            case 'entry-exit':
                let entryExitArrowLine = '<line x1="11" y1="35" x2="33" y2="35" stroke="white" stroke-width="3.1" stroke-linecap="round"/>';
                let entryExitArrowHead = '<polygon points="33,29 42,35 33,41" fill="white"/>';

                if (direction === 'left') {
                    entryExitArrowLine = '<line x1="39" y1="35" x2="17" y2="35" stroke="white" stroke-width="3.1" stroke-linecap="round"/>';
                    entryExitArrowHead = '<polygon points="17,29 8,35 17,41" fill="white"/>';
                } else if (direction === 'up') {
                    entryExitArrowLine = '<line x1="25" y1="43" x2="25" y2="21" stroke="white" stroke-width="3.1" stroke-linecap="round"/>';
                    entryExitArrowHead = '<polygon points="19,21 25,12 31,21" fill="white"/>';
                } else if (direction === 'down') {
                    entryExitArrowLine = '<line x1="25" y1="17" x2="25" y2="39" stroke="white" stroke-width="3.1" stroke-linecap="round"/>';
                    entryExitArrowHead = '<polygon points="19,39 25,48 31,39" fill="white"/>';
                }

                return `<svg xmlns="http://www.w3.org/2000/svg" width="50" height="50" viewBox="0 0 50 50">
                    <rect x="0" y="0" width="51" height="51" fill="#4a4a4a"/>
                    <rect x="6" y="6" width="38" height="38" rx="8" fill="#4a4a4a"/>
                    <rect x="7" y="8" width="17" height="13" rx="3.5" fill="#f44336"/>
                    <text x="15.5" y="16.5" text-anchor="middle" font-family="Segoe UI, Arial" font-size="6.8" font-weight="900" fill="white">OUT</text>
                    <rect x="26" y="8" width="17" height="13" rx="3.5" fill="#4CAF50"/>
                    <text x="34.5" y="16.5" text-anchor="middle" font-family="Segoe UI, Arial" font-size="6.8" font-weight="900" fill="white">IN</text>
                    ${entryExitArrowLine}
                    ${entryExitArrowHead}
                </svg>`;

            case 'wall':
                return `<svg xmlns="http://www.w3.org/2000/svg" width="50" height="50" viewBox="0 0 50 50" shape-rendering="crispEdges">
                    <rect x="0" y="0" width="51" height="51" fill="#757575"/>
                    <rect x="5" y="5" width="40" height="40" fill="#616161"/>
                </svg>`;

            case 'pillar':
                return `<svg xmlns="http://www.w3.org/2000/svg" width="50" height="50" viewBox="0 0 50 50" shape-rendering="crispEdges">
                    <rect x="0" y="0" width="51" height="51" fill="#9E9E9E"/>
                    <circle cx="25" cy="25" r="18" fill="#757575"/>
                    <circle cx="25" cy="25" r="12" fill="#616161"/>
                </svg>`;

            case 'tree':
                return `<svg xmlns="http://www.w3.org/2000/svg" width="50" height="50" viewBox="0 0 50 50" shape-rendering="crispEdges">
                    <rect x="0" y="0" width="51" height="51" fill="#E8F5E8"/>
                    <circle cx="25" cy="25" r="18" fill="#4CAF50"/>
                    <circle cx="25" cy="25" r="3" fill="#795548"/>
                </svg>`;

            case 'section':
                return `<svg xmlns="http://www.w3.org/2000/svg" width="50" height="50" viewBox="0 0 50 50">
                    <rect x="0" y="0" width="50" height="50" fill="#ffffff" stroke="#dee2e6" stroke-width="1"/>
                    <text x="25" y="20" text-anchor="middle" font-family="Arial" font-size="10" font-weight="bold" fill="#333">${sectionName || 'SLOT'}</text>
                    <text x="25" y="35" text-anchor="middle" font-family="Arial" font-size="8" fill="#666">${slotNumber || '#'}</text>
                </svg>`;

            case 'vehicle':
                if (sectionType === 'car' || sectionType === 'tahp') {
                    return `<svg xmlns="http://www.w3.org/2000/svg" width="50" height="50" viewBox="0 0 50 50">
                        <rect x="5" y="15" width="40" height="20" fill="#2196F3" rx="3"/>
                        <rect x="10" y="10" width="25" height="15" fill="#1976D2" rx="2"/>
                        <circle cx="12" cy="38" r="4" fill="#333"/>
                        <circle cx="38" cy="38" r="4" fill="#333"/>
                    </svg>`;
                } else if (sectionType === 'motor' || sectionType === 'motorcycle') {
                    return `<svg xmlns="http://www.w3.org/2000/svg" width="50" height="50" viewBox="0 0 50 50">
                        <rect x="15" y="10" width="20" height="30" fill="#FF9800" rx="2"/>
                        <rect x="18" y="8" width="14" height="20" fill="#F57C00" rx="1"/>
                        <circle cx="20" cy="40" r="3" fill="#333"/>
                        <circle cx="30" cy="40" r="3" fill="#333"/>
                    </svg>`;
                } else if (sectionType === 'bike' || sectionType === 'bicycle') {
                    return `<svg xmlns="http://www.w3.org/2000/svg" width="50" height="50" viewBox="0 0 50 50">
                        <circle cx="25" cy="25" r="8" fill="#4CAF50"/>
                        <circle cx="25" cy="25" r="2" fill="#2E7D32"/>
                        <circle cx="15" cy="40" r="2" fill="#333"/>
                        <circle cx="35" cy="40" r="2" fill="#333"/>
                    </svg>`;
                }
                return `<svg xmlns="http://www.w3.org/2000/svg" width="50" height="50" viewBox="0 0 50 50">
                    <rect x="5" y="15" width="40" height="20" fill="#2196F3" rx="3"/>
                    <circle cx="12" cy="38" r="4" fill="#333"/>
                    <circle cx="38" cy="38" r="4" fill="#333"/>
                </svg>`;

            default:
                return `<svg xmlns="http://www.w3.org/2000/svg" width="50" height="50" viewBox="0 0 50 50">
                    <rect x="0" y="0" width="50" height="50" fill="#f8f9fa"/>
                    <text x="25" y="25" text-anchor="middle" font-family="Arial" font-size="8" fill="#333">${elementType}</text>
                </svg>`;
        }
    }
}

function generateCleanVisualizationSVG(data, container) {
    const TILE_SIZE = 50;
    const elements = data.elements || {};
    const sections = data.sections || [];

    // Calculate bounds
    let minRow = Infinity, maxRow = -Infinity;
    let minCol = Infinity, maxCol = -Infinity;

    // Check elements
    Object.keys(elements).forEach(position => {
        const [row, col] = position.split(',').map(Number);
        minRow = Math.min(minRow, row);
        maxRow = Math.max(maxRow, row);
        minCol = Math.min(minCol, col);
        maxCol = Math.max(maxCol, col);
    });

    // Check sections
    sections.forEach(section => {
        if (section.position) {
            const [row, col] = section.position.split(',').map(Number);
            const sectionData = section.section_data;
            if (sectionData) {
                const isCapacityOnly = sectionData.section_mode === 'capacity_only';

                if (isCapacityOnly) {
                    // For capacity-only, check orientation
                    const gridWidth = parseInt(sectionData.grid_width) || parseInt(sectionData.cols) || 1;
                    const orientation = sectionData.orientation || 'horizontal';

                    // For vertical capacity-only, grid_width was set to 1, so we need to use rows as the actual width
                    const actualGridWidth = orientation === 'vertical' ? sectionData.rows : gridWidth;

                    if (orientation === 'vertical') {
                        // Vertical: actualGridWidth becomes height, 1 column width
                        minRow = Math.min(minRow, row);
                        maxRow = Math.max(maxRow, row + actualGridWidth - 1); // Use actualGridWidth for height
                        minCol = Math.min(minCol, col);
                        maxCol = Math.max(maxCol, col); // Only 1 column
                    } else {
                        // Horizontal: actualGridWidth becomes width, 1 row height
                        minRow = Math.min(minRow, row);
                        maxRow = Math.max(maxRow, row); // Only 1 row
                        minCol = Math.min(minCol, col);
                        maxCol = Math.max(maxCol, col + actualGridWidth - 1); // Use actualGridWidth for width
                    }
                } else {
                    // Regular section
                    const rows = parseInt(sectionData.rows) || 1;
                    const cols = parseInt(sectionData.cols) || 1;

                    minRow = Math.min(minRow, row);
                    maxRow = Math.max(maxRow, row + rows - 1);
                    minCol = Math.min(minCol, col);
                    maxCol = Math.max(maxCol, col + cols - 1);
                }
            }
        }
    });

    // Ensure minimum bounds
    if (minRow === Infinity) minRow = 0;
    if (maxRow === -Infinity) maxRow = 7;
    if (minCol === Infinity) minCol = 0;
    if (maxCol === -Infinity) maxCol = 7;

    const COLS = maxCol - minCol + 1;
    const ROWS = maxRow - minRow + 1;
    const W = COLS * TILE_SIZE;
    const H = ROWS * TILE_SIZE;

    let svgContent = `<svg xmlns="http://www.w3.org/2000/svg" width="${W}" height="${H}" viewBox="0 0 ${W} ${H}">
        <defs>
            <pattern id="asphalt" x="0" y="0" width="20" height="20" patternUnits="userSpaceOnUse">
                <rect width="20" height="20" fill="#4a4a4a"/>
            </pattern>
            <pattern id="concrete" x="0" y="0" width="10" height="10" patternUnits="userSpaceOnUse">
                <rect width="10" height="10" fill="#f8f9fa"/>
                <circle cx="1" cy="1" r="0.5" fill="#4a4a4a" opacity="0.5"/>
                <circle cx="5" cy="5" r="0.5" fill="#4a4a4a" opacity="0.5"/>
                <circle cx="9" cy="9" r="0.5" fill="#4a4a4a" opacity="0.5"/>
            </pattern>
            <style>
                .parking-text { font: bold 12px 'Segoe UI', Arial, sans-serif; fill: #333333; text-anchor: middle; dominant-baseline: middle; }
                .section-label { font: bold 14px 'Segoe UI', Arial, sans-serif; fill: #2c3e50; text-anchor: middle; font-weight: 600; }
            </style>
        </defs>
        
        <!-- Background -->
        <rect x="0" y="0" width="${W}" height="${H}" fill="url(#concrete)"/>`;

    // Add elements using same clean generation as design modal
    Object.entries(elements).forEach(([position, element]) => {
        const [row, col] = position.split(',').map(Number);
        const x = (col - minCol) * TILE_SIZE;
        const y = (row - minRow) * TILE_SIZE;

        // Use same element generation as design modal with proper positioning
        const elementSvg = getElementSVG(element.type, element.direction || 'right');
        // Extract inner content from SVG (remove outer SVG tags)
        const innerContent = elementSvg.replace(/<svg[^>]*>/, '').replace(/<\/svg>$/, '');
        svgContent += `
            <g transform="translate(${x}, ${y})">
                ${innerContent}
            </g>`;
    });

    // Add sections with proper spacing (no overlap)
    sections.forEach(section => {
        if (section.position) {
            const [row, col] = section.position.split(',').map(Number);
            const sectionData = section.section_data;
            if (sectionData) {
                const startX = (col - minCol) * TILE_SIZE;
                const startY = (row - minRow) * TILE_SIZE;
                const sectionWidth = (sectionData.cols || 1) * TILE_SIZE;
                const sectionHeight = (sectionData.rows || 1) * TILE_SIZE;

                let sectionName = sectionData.type || sectionData.section_name || 'section';
                if (sectionName.includes('_')) {
                    sectionName = sectionName.split('_')[0];
                }

                // Check if this is a capacity-only section
                const isCapacityOnly = sectionData.section_mode === 'capacity_only';

                if (isCapacityOnly) {
                    // Capacity-only: Create single merged block
                    const gridWidth = parseInt(sectionData.grid_width) || parseInt(sectionData.cols) || 1;
                    const orientation = sectionData.orientation || 'horizontal';

                    // For vertical capacity-only, grid_width was set to 1, so we need to use rows as the actual width
                    const actualGridWidth = orientation === 'vertical' ? sectionData.rows : gridWidth;

                    if (orientation === 'vertical') {
                        // Vertical orientation: actualGridWidth becomes height, 1 column width
                        const sectionWidth = TILE_SIZE; // Single column
                        const sectionHeight = actualGridWidth * TILE_SIZE; // Use actualGridWidth for height

                        svgContent += `
                            <g transform="translate(${startX}, ${startY})">
                                <!-- Capacity-only section background - match designer #e9ecef -->
                                <rect x="0" y="0" width="${sectionWidth}" height="${sectionHeight}" 
                                      fill="#e9ecef" stroke="#ced4da" stroke-width="2" rx="4"/>
                                
                                <!-- Section label only - no capacity display -->
                                <g transform="translate(${sectionWidth / 2}, ${sectionHeight / 2})">
                                    <rect x="-30" y="-12" width="60" height="24" fill="white" stroke="#ced4da" stroke-width="1" rx="3"/>
                                    <text x="0" y="4" text-anchor="middle" font-family="Arial" font-size="12" font-weight="bold" fill="#333">${sectionName}</text>
                                </g>
                            </g>`;
                    } else {
                        // Horizontal orientation: actualGridWidth becomes width, 1 row height
                        const sectionWidth = actualGridWidth * TILE_SIZE; // Use actualGridWidth for width
                        const sectionHeight = TILE_SIZE; // Single row height

                        svgContent += `
                            <g transform="translate(${startX}, ${startY})">
                                <!-- Capacity-only section background - match designer #e9ecef -->
                                <rect x="0" y="0" width="${sectionWidth}" height="${sectionHeight}" 
                                      fill="#e9ecef" stroke="#ced4da" stroke-width="2" rx="4"/>
                                
                                <!-- Section label only - no capacity display -->
                                <g transform="translate(${sectionWidth / 2}, ${sectionHeight / 2})">
                                    <rect x="-30" y="-12" width="60" height="24" fill="white" stroke="#ced4da" stroke-width="1" rx="3"/>
                                    <text x="0" y="4" text-anchor="middle" font-family="Arial" font-size="12" font-weight="bold" fill="#333">${sectionName}</text>
                                </g>
                            </g>`;
                    }
                } else {
                    // Regular slot-based section
                    svgContent += `
                        <g transform="translate(${startX}, ${startY})">
                            <rect x="0" y="0" width="${sectionWidth}" height="${sectionHeight}" 
                                  fill="#4a4a4a" stroke="#ced4da" stroke-width="1"/>
                            
                            <!-- Section label -->
                            <rect x="5" y="5" width="60" height="25" fill="white" stroke="#495057" stroke-width="1" rx="3"/>
                            <text x="35" y="17" class="section-label">${sectionName}</text>`;

                    // Add parking slots with proper spacing (no overlap)
                    for (let r = 0; r < (sectionData.rows || 1); r++) {
                        for (let c = 0; c < (sectionData.cols || 1); c++) {
                            const slotX = c * TILE_SIZE;
                            const slotY = r * TILE_SIZE;
                            const slotNumber = (r * (sectionData.cols || 1)) + c + 1;

                            // Use same parking slot SVG as design modal
                            const slotSvg = getElementSVG('section', null, null, slotNumber, sectionName);
                            const innerContent = slotSvg.replace(/<svg[^>]*>/, '').replace(/<\/svg>$/, '');
                            svgContent += `
                                <g transform="translate(${slotX}, ${slotY})">
                                ${innerContent}
                            </g>`;
                        }
                    }

                    svgContent += '</g>';
                }
            }
        }
    });

    svgContent += '</svg>';

    // Set container styling
    container.innerHTML = svgContent;
    container.style.textAlign = 'center';
    container.style.padding = '20px';

    // Style SVG
    const svg = container.querySelector('svg');
    if (svg) {
        svg.style.maxWidth = '100%';
        svg.style.height = 'auto';
        svg.style.border = '1.5px solid #dee2e6';
        svg.style.borderRadius = '0.5rem';
        svg.style.background = 'white';
        svg.style.boxShadow = '0 2px 8px rgba(0,0,0,0.08)';
    }
}

// Import getElementSVG from layout-designer.js if not available
if (typeof getElementSVG === 'undefined') {
    // Copy the same functions from layout-designer.js
    function getElementSVG(elementType, direction = 'right', sectionType = null, slotNumber = null, sectionName = null) {
        switch (elementType) {
            case 'road':
                if (direction === 'horizontal') {
                    return `< svg xmlns = "http://www.w3.org/2000/svg" width = "50" height = "50" viewBox = "0 0 50 50" >
                        <rect x="0" y="0" width="51" height="51" fill="#4a4a4a"/>
                        <line x1="0" y1="25" x2="50" y2="25" stroke="#ffd54f" stroke-width="3"/>
                    </svg > `;
                } else if (direction === 'vertical') {
                    return `< svg xmlns = "http://www.w3.org/2000/svg" width = "50" height = "50" viewBox = "0 0 50 50" >
                        <rect x="0" y="0" width="51" height="51" fill="#4a4a4a"/>
                        <line x1="25" y1="0" x2="25" y2="50" stroke="#ffd54f" stroke-width="3"/>
                    </svg > `;
                }
                return `< svg xmlns = "http://www.w3.org/2000/svg" width = "50" height = "50" viewBox = "0 0 50 50" shape - rendering="crispEdges" >
                        <rect x="0" y="0" width="51" height="51" fill="#4a4a4a"/>
                        <line x1="0" y1="25" x2="50" y2="25" stroke="#ffd54f" stroke-width="3"/>
                    </svg > `;

            case 'l-road':
                if (direction === 'right-down') {
                    return `< svg xmlns = "http://www.w3.org/2000/svg" width = "50" height = "50" viewBox = "0 0 50 50" shape - rendering="crispEdges" >
                        <rect x="0" y="0" width="51" height="51" fill="#4a4a4a"/>
                        <path d="M 0 25 L 25 25 L 25 50" stroke="#ffd54f" stroke-width="3" fill="none"/>
                    </svg > `;
                } else if (direction === 'right-up') {
                    return `< svg xmlns = "http://www.w3.org/2000/svg" width = "50" height = "50" viewBox = "0 0 50 50" shape - rendering="crispEdges" >
                        <rect x="0" y="0" width="51" height="51" fill="#4a4a4a"/>
                        <path d="M 0 25 L 25 25 L 25 0" stroke="#ffd54f" stroke-width="3" fill="none"/>
                    </svg > `;
                } else if (direction === 'left-down') {
                    return `< svg xmlns = "http://www.w3.org/2000/svg" width = "50" height = "50" viewBox = "0 0 50 50" shape - rendering="crispEdges" >
                        <rect x="0" y="0" width="51" height="51" fill="#4a4a4a"/>
                        <path d="M 50 25 L 25 25 L 25 50" stroke="#ffd54f" stroke-width="3" fill="none"/>
                    </svg > `;
                } else if (direction === 'left-up') {
                    return `< svg xmlns = "http://www.w3.org/2000/svg" width = "50" height = "50" viewBox = "0 0 50 50" shape - rendering="crispEdges" >
                        <rect x="0" y="0" width="51" height="51" fill="#4a4a4a"/>
                        <path d="M 50 25 L 25 25 L 25 0" stroke="#ffd54f" stroke-width="3" fill="none"/>
                    </svg > `;
                }
                return `< svg xmlns = "http://www.w3.org/2000/svg" width = "50" height = "50" viewBox = "0 0 50 50" >
                    <rect x="0" y="0" width="50" height="50" fill="#4a4a4a"/>
                    <path d="M 0 25 L 25 25 L 25 50" stroke="#ffd54f" stroke-width="3" fill="none"/>
                </svg > `;

            case 't-road':
                if (direction === 'up' || direction === 'top') {
                    return `< svg xmlns = "http://www.w3.org/2000/svg" width = "50" height = "50" viewBox = "0 0 50 50" shape - rendering="crispEdges" >
                        <rect x="0" y="0" width="51" height="51" fill="#4a4a4a"/>
                        <line x1="0" y1="25" x2="50" y2="25" stroke="#ffd54f" stroke-width="3"/>
                        <line x1="25" y1="25" x2="25" y2="50" stroke="#ffd54f" stroke-width="3"/>
                    </svg > `;
                } else if (direction === 'down' || direction === 'bottom') {
                    return `< svg xmlns = "http://www.w3.org/2000/svg" width = "50" height = "50" viewBox = "0 0 50 50" shape - rendering="crispEdges" >
                        <rect x="0" y="0" width="51" height="51" fill="#4a4a4a"/>
                        <line x1="0" y1="25" x2="50" y2="25" stroke="#ffd54f" stroke-width="3"/>
                        <line x1="25" y1="0" x2="25" y2="25" stroke="#ffd54f" stroke-width="3"/>
                    </svg > `;
                } else if (direction === 'left') {
                    return `< svg xmlns = "http://www.w3.org/2000/svg" width = "50" height = "50" viewBox = "0 0 50 50" shape - rendering="crispEdges" >
                        <rect x="0" y="0" width="51" height="51" fill="#4a4a4a"/>
                        <line x1="25" y1="0" x2="25" y2="50" stroke="#ffd54f" stroke-width="3"/>
                        <line x1="25" y1="25" x2="50" y2="25" stroke="#ffd54f" stroke-width="3"/>
                    </svg > `;
                } else {
                    return `< svg xmlns = "http://www.w3.org/2000/svg" width = "50" height = "50" viewBox = "0 0 50 50" shape - rendering="crispEdges" >
                        <rect x="0" y="0" width="51" height="51" fill="#4a4a4a"/>
                        <line x1="25" y1="0" x2="25" y2="50" stroke="#ffd54f" stroke-width="3"/>
                        <line x1="0" y1="25" x2="25" y2="25" stroke="#ffd54f" stroke-width="3"/>
                    </svg > `;
                }

            case 'intersection':
                return `< svg xmlns = "http://www.w3.org/2000/svg" width = "50" height = "50" viewBox = "0 0 50 50" shape - rendering="crispEdges" >
                    <rect x="0" y="0" width="51" height="51" fill="#4a4a4a"/>
                    <line x1="0" y1="25" x2="50" y2="25" stroke="#ffd54f" stroke-width="3"/>
                    <line x1="25" y1="0" x2="25" y2="50" stroke="#ffd54f" stroke-width="3"/>
                </svg > `;

            case 'entrance':
                // Modernized entrance design fallback
                if (direction === 'left') {
                    return `<svg xmlns="http://www.w3.org/2000/svg" width="50" height="50" viewBox="0 0 50 50">
                        <rect x="0" y="0" width="51" height="51" fill="#4a4a4a"/>
                        <rect x="6" y="6" width="38" height="38" rx="8" fill="#4CAF50"/>
                        <text x="25" y="22" font-family="Segoe UI, Arial" font-size="13" font-weight="900" fill="white" text-anchor="middle">IN</text>
                        <path d="M 33 35 L 17 35 M 22 30 L 17 35 L 22 40" stroke="white" stroke-width="3" fill="none" stroke-linecap="round" stroke-linejoin="round"/>
                    </svg>`;
                } else if (direction === 'up') {
                    return `<svg xmlns="http://www.w3.org/2000/svg" width="50" height="50" viewBox="0 0 50 50">
                        <rect x="0" y="0" width="51" height="51" fill="#4a4a4a"/>
                        <rect x="6" y="6" width="38" height="38" rx="8" fill="#4CAF50"/>
                        <text x="25" y="22" font-family="Segoe UI, Arial" font-size="13" font-weight="900" fill="white" text-anchor="middle">IN</text>
                        <path d="M 25 40 L 25 30 M 20 35 L 25 30 L 30 35" stroke="white" stroke-width="3" fill="none" stroke-linecap="round" stroke-linejoin="round"/>
                    </svg>`;
                } else if (direction === 'down') {
                    return `<svg xmlns="http://www.w3.org/2000/svg" width="50" height="50" viewBox="0 0 50 50">
                        <rect x="0" y="0" width="51" height="51" fill="#4a4a4a"/>
                        <rect x="6" y="6" width="38" height="38" rx="8" fill="#4CAF50"/>
                        <text x="25" y="22" font-family="Segoe UI, Arial" font-size="13" font-weight="900" fill="white" text-anchor="middle">IN</text>
                        <path d="M 25 30 L 25 40 M 20 35 L 25 40 L 30 35" stroke="white" stroke-width="3" fill="none" stroke-linecap="round" stroke-linejoin="round"/>
                    </svg>`;
                }
                return `<svg xmlns="http://www.w3.org/2000/svg" width="50" height="50" viewBox="0 0 50 50">
                    <rect x="0" y="0" width="51" height="51" fill="#4a4a4a"/>
                    <rect x="6" y="6" width="38" height="38" rx="8" fill="#4CAF50"/>
                    <text x="25" y="22" font-family="Segoe UI, Arial" font-size="13" font-weight="900" fill="white" text-anchor="middle">IN</text>
                    <path d="M 17 35 L 33 35 M 28 30 L 33 35 L 28 40" stroke="white" stroke-width="3" fill="none" stroke-linecap="round" stroke-linejoin="round"/>
                </svg>`;

            case 'exit':
                // Modernized exit design fallback
                if (direction === 'left') {
                    return `<svg xmlns="http://www.w3.org/2000/svg" width="50" height="50" viewBox="0 0 50 50">
                        <rect x="0" y="0" width="51" height="51" fill="#4a4a4a"/>
                        <rect x="6" y="6" width="38" height="38" rx="8" fill="#f44336"/>
                        <text x="25" y="22" font-family="Segoe UI, Arial" font-size="11" font-weight="900" fill="white" text-anchor="middle">OUT</text>
                        <path d="M 33 35 L 17 35 M 22 30 L 17 35 L 22 40" stroke="white" stroke-width="3" fill="none" stroke-linecap="round" stroke-linejoin="round"/>
                    </svg>`;
                } else if (direction === 'up') {
                    return `<svg xmlns="http://www.w3.org/2000/svg" width="50" height="50" viewBox="0 0 50 50">
                        <rect x="0" y="0" width="51" height="51" fill="#4a4a4a"/>
                        <rect x="6" y="6" width="38" height="38" rx="8" fill="#f44336"/>
                        <text x="25" y="22" font-family="Segoe UI, Arial" font-size="11" font-weight="900" fill="white" text-anchor="middle">OUT</text>
                        <path d="M 25 40 L 25 30 M 20 35 L 25 30 L 30 35" stroke="white" stroke-width="3" fill="none" stroke-linecap="round" stroke-linejoin="round"/>
                    </svg>`;
                } else if (direction === 'down') {
                    return `<svg xmlns="http://www.w3.org/2000/svg" width="50" height="50" viewBox="0 0 50 50">
                        <rect x="0" y="0" width="51" height="51" fill="#4a4a4a"/>
                        <rect x="6" y="6" width="38" height="38" rx="8" fill="#f44336"/>
                        <text x="25" y="22" font-family="Segoe UI, Arial" font-size="11" font-weight="900" fill="white" text-anchor="middle">OUT</text>
                        <path d="M 25 30 L 25 40 M 20 35 L 25 40 L 30 35" stroke="white" stroke-width="3" fill="none" stroke-linecap="round" stroke-linejoin="round"/>
                    </svg>`;
                }
                return `<svg xmlns="http://www.w3.org/2000/svg" width="50" height="50" viewBox="0 0 50 50">
                    <rect x="0" y="0" width="51" height="51" fill="#4a4a4a"/>
                    <rect x="6" y="6" width="38" height="38" rx="8" fill="#f44336"/>
                    <text x="25" y="22" font-family="Segoe UI, Arial" font-size="11" font-weight="900" fill="white" text-anchor="middle">OUT</text>
                    <path d="M 17 35 L 33 35 M 28 30 L 33 35 L 28 40" stroke="white" stroke-width="3" fill="none" stroke-linecap="round" stroke-linejoin="round"/>
                </svg>`;

            case 'section':
                return `< svg xmlns = "http://www.w3.org/2000/svg" width = "50" height = "50" viewBox = "0 0 50 50" >
                    <rect x="0" y="0" width="50" height="50" fill="#ffffff" stroke="#dee2e6" stroke-width="1"/>
                    <text x="25" y="20" text-anchor="middle" font-family="Arial" font-size="10" font-weight="bold" fill="#333">${sectionName || 'SLOT'}</text>
                    <text x="25" y="35" text-anchor="middle" font-family="Arial" font-size="8" fill="#666">${slotNumber || '#'}</text>
                </svg > `;

            case 'wall':
                return `< svg xmlns = "http://www.w3.org/2000/svg" width = "50" height = "50" viewBox = "0 0 50 50" >
                                <rect x="0" y="0" width="50" height="50" fill="#757575" />
                </svg > `;

            case 'pillar':
                return `< svg xmlns = "http://www.w3.org/2000/svg" width = "50" height = "50" viewBox = "0 0 50 50" >
                                <circle cx="25" cy="25" r="18" fill="#757575" />
                </svg > `;

            case 'tree':
                return `< svg xmlns = "http://www.w3.org/2000/svg" width = "50" height = "50" viewBox = "0 0 50 50" >
                                <circle cx="25" cy="25" r="18" fill="#4CAF50" />
                </svg > `;

            case 'vehicle':
                return `< svg xmlns = "http://www.w3.org/2000/svg" width = "50" height = "50" viewBox = "0 0 50 50" >
                                <rect x="5" y="15" width="40" height="20" fill="#2196F3" rx="3" />
                </svg > `;

            default:
                return `< svg xmlns = "http://www.w3.org/2000/svg" width = "50" height = "50" viewBox = "0 0 50 50" >
                                <text x="25" y="25" text-anchor="middle" font-family="Arial" font-size="8" fill="#333">${elementType}</text>
                </svg > `;
        }
    }
}

