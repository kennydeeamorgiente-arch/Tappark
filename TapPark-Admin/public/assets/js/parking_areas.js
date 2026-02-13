/**
 * Parking Areas & Sections Management JavaScript
 * Handles all area and section CRUD operations
 */

// Extend initPageScripts for parking areas page
if (typeof window.initPageScripts === 'function') {
    const originalInitPageScripts = window.initPageScripts;

    window.initPageScripts = function () {
        // Check if we're on the parking areas page
        if ($('#areasGrid').length > 0) {
            console.log('Parking Areas page initialized!');

            // Get base URL from global config
            const baseUrl = window.APP_BASE_URL || '';
            const geoapifyKey = window.GEOAPIFY_API_KEY || null;

            // Global variables
            let currentAreaId = null;
            let currentSectionId = null;
            let vehicleTypes = [];
            let allAreas = [];
            let wizardLocationBindingsInitialized = false;
            let wizardLocationAbortController = null;

            // Initialize
            loadVehicleTypes();
            loadAreas();

            // ====================================
            // UNIFIED DELETE HANDLER
            // ====================================
            // Store original if it exists to preserve the chain
            const originalConfirmDelete = window.confirmDelete;

            window.confirmDelete = function () {
                const entity = $('#deleteEntityType').val();

                if (entity === 'parking-section') {
                    const sectionId = $('#deleteEntityId').val();
                    const deleteBtn = $('#confirmDeleteBtn');
                    const originalText = deleteBtn.html();

                    deleteBtn.prop('disabled', true).html('<i class="fas fa-spinner fa-spin me-1"></i> Deleting...');

                    ajaxWithCSRF(`${baseUrl}parking/areas/sections/delete/${sectionId}`, {
                        method: 'POST',
                        data: {},
                        success: function (response) {
                            // Blur active element before hiding modal
                            if (document.activeElement) {
                                document.activeElement.blur();
                            }

                            const deleteConfirmModal = document.getElementById('deleteConfirmModal');
                            let bsModal = bootstrap.Modal.getInstance(deleteConfirmModal);
                            if (bsModal) bsModal.hide();

                            if (response.success) {
                                // Show success modal
                                if (typeof showSuccessModal === 'function') {
                                    showSuccessModal('Section Deleted Successfully', `Section "${$('#deleteEntityLabel').text()}" has been removed from the system.`);
                                } else {
                                    showToast('Section deleted successfully!', 'success');
                                }

                                // Update UI via hook (no reload)
                                if (typeof window.onParkingSectionDeleted === 'function') {
                                    window.onParkingSectionDeleted(sectionId, response.stats);
                                }
                            } else {
                                if (typeof showSuccessModal === 'function') {
                                    showSuccessModal('Delete Failed', response.message || 'Failed to delete section');
                                } else {
                                    showToast(response.message || 'Failed to delete section', 'error');
                                }
                            }
                        },
                        error: function (xhr) {
                            // Blur active element before hiding modal
                            if (document.activeElement) {
                                document.activeElement.blur();
                            }

                            const deleteConfirmModal = document.getElementById('deleteConfirmModal');
                            let bsModal = bootstrap.Modal.getInstance(deleteConfirmModal);
                            if (bsModal) bsModal.hide();

                            const errorMsg = xhr.responseJSON?.message || 'Error deleting section. Please try again.';
                            if (typeof showSuccessModal === 'function') {
                                showSuccessModal('Delete Error', errorMsg);
                            } else {
                                showToast(errorMsg, 'error');
                            }
                        },
                        complete: function () {
                            deleteBtn.prop('disabled', false).html(originalText);
                        }
                    });
                } else if (originalConfirmDelete && typeof originalConfirmDelete === 'function') {
                    originalConfirmDelete();
                }
            };

            // Attach confirmDelete to the button click
            $('#confirmDeleteBtn').off('click').on('click', function () {
                confirmDelete();
            });


            // Load vehicle types for dropdowns
            function loadVehicleTypes() {
                $.ajax({
                    url: `${baseUrl}parking/areas/getVehicleTypes`,
                    method: 'GET',
                    success: function (response) {
                        if (response.success && response.data) {
                            vehicleTypes = response.data;
                            populateVehicleTypeDropdowns();
                        }
                    },
                    error: function () {
                        console.error('Failed to load vehicle types');
                    }
                });
            }

            // Expose for global delete modal handler (users.js confirmDelete)
            window.loadAreas = loadAreas;

            // Populate vehicle type dropdowns
            function populateVehicleTypeDropdowns() {
                let options = '<option value="">Select Vehicle Type</option>';
                vehicleTypes.forEach(type => {
                    options += `<option value="${type.vehicle_type_id}">${type.vehicle_type_name}</option>`;
                });

                $('#sectionVehicleType').html(options);
                $('#editSectionVehicleType').html(options);
                // Also populate wizard dropdown
                if ($('#wizardSectionVehicleType').length) {
                    $('#wizardSectionVehicleType').html(options);
                }
            }

            // Load all parking areas
            function loadAreas() {
                $.ajax({
                    url: `${baseUrl}parking/areas/list`,
                    method: 'GET',
                    beforeSend: function () {
                        $('#areasGrid').html(`
                            <div class="col-12 text-center py-5">
                                <div class="spinner-border text-primary" role="status">
                                    <span class="visually-hidden">Loading...</span>
                                </div>
                                <p class="mt-2 text-muted">Loading parking areas...</p>
                            </div>
                        `);
                    },
                    success: function (response) {
                        if (response.success) {
                            allAreas = response.data;
                            renderAreas(response.data);
                            updateStats(response.stats);
                        }
                    },
                    error: function () {
                        $('#areasGrid').html(`
                            <div class="col-12">
                                <div class="alert alert-danger">
                                    <i class="fas fa-exclamation-triangle me-2"></i>
                                    Failed to load parking areas. Please try again.
                                </div>
                            </div>
                        `);
                    }
                });
            }

            // Render areas as cards
            function renderAreas(areas) {
                if (!areas || areas.length === 0) {
                    $('#areasGrid').html(`
                        <div class="col-12">
                            <div class="empty-state">
                                <i class="fas fa-parking"></i>
                                <h4>No Parking Areas Found</h4>
                                <p class="text-muted">Start by adding a new parking area</p>
                                <button class="btn btn-maroon" onclick="$('#addAreaBtn').click()">
                                    <i class="fas fa-plus me-1"></i> Add First Area
                                </button>
                            </div>
                        </div>
                    `);
                    return;
                }

                let html = '';
                areas.forEach(area => {
                    const statusBadge = area.status === 'active'
                        ? '<span class="badge bg-success">Active</span>'
                        : '<span class="badge bg-secondary">Inactive</span>';

                    const occupancyPercent = area.total_spots > 0
                        ? Math.round((area.occupied_spots / area.total_spots) * 100)
                        : 0;

                    html += `
                        <div class="col-lg-6 col-xl-4">
                            <div class="card area-card status-${area.status} shadow-sm mb-4">
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

                                    <!-- Stats Grid -->
                                    <div class="area-stats-grid">
                                        <div class="stat-box">
                                            <div class="value text-primary">${area.total_sections || 0}</div>
                                            <div class="label">Sections</div>
                                        </div>
                                        <div class="stat-box">
                                            <div class="value text-info">${area.total_spots || 0}</div>
                                            <div class="label">Parking Spaces</div>
                                        </div>
                                        <div class="stat-box">
                                            <div class="value text-success">${area.available_spots || 0}</div>
                                            <div class="label">Available</div>
                                        </div>
                                        <div class="stat-box">
                                            <div class="value text-warning">${area.occupied_spots || 0}</div>
                                            <div class="label">Occupied</div>
                                        </div>
                                    </div>

                                    <!-- Occupancy Bar -->
                                    <div class="mb-3">
                                        <small class="text-muted">Occupancy Rate</small>
                                        <div class="progress" style="height: 8px;">
                                            <div class="progress-bar ${occupancyPercent > 80 ? 'bg-danger' : occupancyPercent > 50 ? 'bg-warning' : 'bg-success'}" 
                                                 role="progressbar" 
                                                 style="width: ${occupancyPercent}%">
                                            </div>
                                        </div>
                                        <small class="text-muted">${occupancyPercent}% occupied</small>
                                    </div>

                                    <!-- Action Buttons -->
                                    <div class="d-flex gap-2 mb-3">
                                        <button class="btn btn-sm btn-outline-primary flex-fill" 
                                                onclick="openAreaSectionsModal(${area.parking_area_id})">
                                            <i class="fas fa-eye me-1"></i> View Sections (${area.total_sections || 0})
                                        </button>
                                        <button class="btn btn-sm btn-maroon" 
                                                onclick="openEditAreaModal(${area.parking_area_id})" 
                                                title="Edit Area">
                                            <i class="fas fa-pen"></i>
                                        </button>
                                        <button class="btn btn-sm btn-danger" 
                                                onclick="openDeleteAreaModal(${area.parking_area_id}, '${escapeHtml(area.parking_area_name)}')" 
                                                title="Delete Area">
                                            <i class="fas fa-trash"></i>
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </div>
                    `;
                });

                $('#areasGrid').html(html);
            }

            // ==========================
            // AREA SECTIONS MODAL
            // ==========================

            const areaSectionsModalEl = document.getElementById('areaSectionsModal');
            const areaSectionsModal = areaSectionsModalEl
                ? bootstrap.Modal.getOrCreateInstance(areaSectionsModalEl, { backdrop: 'static', keyboard: false, focus: false })
                : null;

            const areaSectionsState = {
                areaId: null,
                areaName: '',
                sections: [],
                floor: 'all',
                search: ''
            };

            // Hook for unified delete modal (scripts.php) to update this modal without reload
            window.onParkingSectionDeleted = function (sectionId, stats) {
                if (!areaSectionsState.areaId) return;

                const existing = (areaSectionsState.sections || []).find(s => String(s.parking_section_id) === String(sectionId));
                if (!existing) return;

                const spots = getSectionSpotsCount(existing);
                removeSectionFromModalState(sectionId);
                updateAreaAggregatesForSectionDelta(areaSectionsState.areaId, -1, -spots);
                renderAreaSectionsModal();

                // Update global stats if provided
                if (stats && typeof updateStats === 'function') {
                    updateStats(stats);
                }
            };


            function getSectionSpotsCount(section) {
                if (!section) return 0;
                const mode = section.section_mode || 'slot_based';
                if (mode === 'capacity_only') {
                    return parseInt(section.capacity || 0, 10) || 0;
                }
                const rows = parseInt(section.rows || 0, 10) || 0;
                const cols = parseInt(section.columns || 0, 10) || 0;
                return rows * cols;
            }

            function updateAreaAggregatesForSectionDelta(areaId, deltaSections, deltaSpots) {
                const area = (allAreas || []).find(a => String(a.parking_area_id) === String(areaId));
                if (!area) return;

                area.total_sections = (parseInt(area.total_sections || 0, 10) || 0) + (deltaSections || 0);
                area.total_spots = (parseInt(area.total_spots || 0, 10) || 0) + (deltaSpots || 0);

                // Recompute available spots using existing occupied count
                const occupied = parseInt(area.occupied_spots || 0, 10) || 0;
                area.available_spots = Math.max(0, (parseInt(area.total_spots || 0, 10) || 0) - occupied);

                // Update card UI without re-rendering whole page
                const $card = $(`.area-card`).filter(function () {
                    return $(this).find('[onclick^="openAreaSectionsModal("]').attr('onclick')?.includes(`(${areaId})`);
                });

                // Fallback: if we can't locate card reliably, do nothing (data still updated)
                if ($card.length) {
                    $card.find('.stat-box .label').each(function () {
                        const label = $(this).text().trim().toLowerCase();
                        if (label === 'sections') {
                            $(this).closest('.stat-box').find('.value').text(area.total_sections || 0);
                        }
                        if (label === 'spots') {
                            $(this).closest('.stat-box').find('.value').text(area.total_spots || 0);
                        }
                        if (label === 'available') {
                            $(this).closest('.stat-box').find('.value').text(area.available_spots || 0);
                        }
                    });

                    // Update "View Sections (N)" button text
                    $card.find('button[onclick^="openAreaSectionsModal("]').each(function () {
                        $(this).html(`<i class="fas fa-eye me-1"></i> View Sections (${area.total_sections || 0})`);
                    });
                }
            }

            function upsertSectionInModalState(section) {
                if (!section || !section.parking_section_id) return;
                const idx = areaSectionsState.sections.findIndex(s => String(s.parking_section_id) === String(section.parking_section_id));
                if (idx >= 0) {
                    areaSectionsState.sections[idx] = { ...areaSectionsState.sections[idx], ...section };
                } else {
                    areaSectionsState.sections.unshift(section);
                }
            }

            function removeSectionFromModalState(sectionId) {
                areaSectionsState.sections = (areaSectionsState.sections || []).filter(s => String(s.parking_section_id) !== String(sectionId));
            }

            function maybeUpdateViewSectionsStateForArea(areaId, sectionObj, mode) {
                if (!areaId || !sectionObj) return;
                if (!areaSectionsState.areaId) return;
                if (String(areaSectionsState.areaId) !== String(areaId)) return;
                if (!$('#areaSectionsModal').hasClass('show')) return;

                if (mode === 'add' || mode === 'edit') {
                    upsertSectionInModalState(sectionObj);
                    renderAreaSectionsModal();
                }
                if (mode === 'delete') {
                    removeSectionFromModalState(sectionObj.parking_section_id);
                    renderAreaSectionsModal();
                }
            }

            function setAreaSectionsLoading() {
                $('#areaSectionsMeta').text('Loading sections...');
                $('#areaSectionsFloorSelect').prop('disabled', true);
                $('#areaSectionsSearchInput').prop('disabled', true);
                $('#areaSectionsList').html(`
                    <div class="text-center py-5">
                        <div class="spinner-border text-primary" role="status">
                            <span class="visually-hidden">Loading...</span>
                        </div>
                        <p class="mt-2 text-muted">Loading sections...</p>
                    </div>
                `);
            }

            function renderAreaSectionsModal() {
                const sections = Array.isArray(areaSectionsState.sections) ? areaSectionsState.sections : [];

                // Normalize floor value from DB (can be floor or floor_number)
                const floors = [...new Set(sections.map(s => s.floor ?? s.floor_number ?? 1))]
                    .map(f => parseInt(f, 10))
                    .filter(f => !Number.isNaN(f))
                    .sort((a, b) => a - b);

                const floorSelect = $('#areaSectionsFloorSelect');
                floorSelect.empty();
                floorSelect.append('<option value="all">All Floors</option>');
                floors.forEach(f => {
                    floorSelect.append(`<option value="${f}">Floor ${f}</option>`);
                });
                floorSelect.val(String(areaSectionsState.floor));

                // Apply filters
                const searchLower = (areaSectionsState.search || '').trim().toLowerCase();
                const filtered = sections.filter(s => {
                    const floor = String(s.floor ?? s.floor_number ?? 1);
                    const matchesFloor = areaSectionsState.floor === 'all' ? true : floor === String(areaSectionsState.floor);
                    const name = String(s.section_name ?? '').toLowerCase();
                    const matchesSearch = !searchLower || name.includes(searchLower);
                    return matchesFloor && matchesSearch;
                });

                $('#areaSectionsMeta').text(`${filtered.length} section(s) shown`);

                if (filtered.length === 0) {
                    $('#areaSectionsList').html(`
                        <div class="text-center py-4">
                            <div class="text-muted">No sections found.</div>
                        </div>
                    `);
                    return;
                }

                let html = '';
                filtered.forEach(section => {
                    const sectionId = section.parking_section_id;
                    const sectionName = escapeHtml(section.section_name);
                    const floor = section.floor ?? section.floor_number ?? 1;
                    const mode = section.section_mode || 'slot_based';
                    const vehicleType = escapeHtml(section.vehicle_type || section.vehicle_type_name || 'N/A');

                    let spotsInfo = '';
                    let modeBadge = '';
                    if (mode === 'capacity_only') {
                        spotsInfo = `${section.capacity || 0} capacity (width: ${section.grid_width || 0})`;
                        modeBadge = '<span class="badge bg-warning ms-1">Capacity-only</span>';
                    } else {
                        const rows = parseInt(section.rows || 0, 10);
                        const cols = parseInt(section.columns || 0, 10);
                        const total = rows * cols;
                        spotsInfo = `${rows}×${cols} = ${total} parking spaces`;
                    }

                    html += `
                        <div class="area-section-row d-flex justify-content-between align-items-center p-3 mb-2">
                            <div class="me-3">
                                <div class="d-flex align-items-center flex-wrap gap-2">
                                    <span class="fw-semibold" style="letter-spacing: 0.01em;">${sectionName}</span>
                                    <span class="badge bg-light text-dark">Floor ${floor}</span>
                                    <span class="badge bg-info">${spotsInfo}</span>
                                    <span class="badge bg-secondary">${vehicleType}</span>
                                    ${modeBadge}
                                </div>
                            </div>
                            <div class="area-section-actions d-flex gap-2">
                                <button class="btn btn-sm btn-maroon" onclick="openEditSectionModal(${sectionId})" title="Edit" style="width: 38px; height: 34px; padding: 0; display:flex; align-items:center; justify-content:center;">
                                    <i class="fas fa-pen"></i>
                                </button>
                                <button class="btn btn-sm btn-danger" onclick="deleteSection(${sectionId}, '${sectionName.replace(/'/g, "\\'")}')" title="Delete" style="width: 38px; height: 34px; padding: 0; display:flex; align-items:center; justify-content:center;">
                                    <i class="fas fa-trash"></i>
                                </button>
                            </div>
                        </div>
                    `;
                });

                $('#areaSectionsList').html(html);
            }

            function loadAreaSectionsForModal(areaId) {
                setAreaSectionsLoading();

                $.ajax({
                    url: `${baseUrl}parking/areas/${areaId}/sections`,
                    method: 'GET',
                    success: function (response) {
                        if (response && response.success) {
                            areaSectionsState.sections = response.data || [];
                        } else {
                            areaSectionsState.sections = [];
                        }

                        $('#areaSectionsFloorSelect').prop('disabled', false);
                        $('#areaSectionsSearchInput').prop('disabled', false);
                        renderAreaSectionsModal();
                    },
                    error: function () {
                        areaSectionsState.sections = [];
                        $('#areaSectionsMeta').text('Failed to load sections');
                        $('#areaSectionsFloorSelect').prop('disabled', true);
                        $('#areaSectionsSearchInput').prop('disabled', true);
                        $('#areaSectionsList').html(`
                            <div class="alert alert-warning mb-0">
                                <i class="fas fa-exclamation-circle me-1"></i>
                                Failed to load sections.
                            </div>
                        `);
                    }
                });
            }

            window.openAreaSectionsModal = function (areaId) {
                // Blur any active element
                if (document.activeElement && document.activeElement.blur) {
                    document.activeElement.blur();
                }

                const area = (allAreas || []).find(a => String(a.parking_area_id) === String(areaId));
                const areaName = area ? (area.parking_area_name || '') : '';

                areaSectionsState.areaId = areaId;
                areaSectionsState.areaName = areaName;
                areaSectionsState.floor = 'all';
                areaSectionsState.search = '';

                $('#areaSectionsAreaId').val(areaId);
                $('#areaSectionsAreaName').text(areaName || '');
                $('#areaSectionsSearchInput').val('');
                $('#areaSectionsFloorSelect').val('all');

                if (areaSectionsModal) {
                    areaSectionsModal.show();
                }

                loadAreaSectionsForModal(areaId);
            };

            // Industry-standard nested modal stacking (Jira/Asana style)
            // Keep View Sections open; stack Add/Edit/Delete modals on top with their own darker backdrop.
            $(document)
                .off('show.bs.modal.modalStack')
                .on('show.bs.modal.modalStack', '.modal', function () {
                    const $openModals = $('.modal.show');
                    const modalZ = 1055 + ($openModals.length * 20);
                    $(this).css('z-index', modalZ);
                });

            $(document)
                .off('shown.bs.modal.modalStack')
                .on('shown.bs.modal.modalStack', '.modal', function () {
                    const $backdrops = $('.modal-backdrop').not('.modal-stack');
                    if ($backdrops.length) {
                        const $openModals = $('.modal.show');
                        const backdropZ = 1050 + (($openModals.length - 1) * 20);
                        $backdrops.last().css('z-index', backdropZ).addClass('modal-stack');
                    }
                });

            // Modal controls
            $('#areaSectionsFloorSelect').off('change').on('change', function () {
                areaSectionsState.floor = $(this).val();
                renderAreaSectionsModal();
            });

            $('#areaSectionsSearchInput').off('input').on('input', function () {
                areaSectionsState.search = $(this).val();
                renderAreaSectionsModal();
            });

            // Add Section inside modal
            $('#areaSectionsAddBtn').off('click').on('click', function () {
                const areaId = areaSectionsState.areaId;
                const areaName = areaSectionsState.areaName;
                if (!areaId) return;
                openAddSectionModal(areaId, escapeHtml(areaName));
            });

            // When section add/edit modals close, refresh current modal list if open
            $('#addSectionModal, #editSectionModal').off('hidden.bs.modal.areaSections').on('hidden.bs.modal.areaSections', function () {
                // No re-fetch here. We update UI immediately on success.
            });

            // When deleting a section via the unified delete modal, refresh the modal list after it closes
            $('#deleteConfirmModal').off('hidden.bs.modal.areaSections').on('hidden.bs.modal.areaSections', function () {
                // No re-fetch here. Deletion is handled by window.onParkingSectionDeleted hook.
            });

            // Escape HTML helper
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

            // Toggle sections display
            window.toggleSections = function (areaId) {
                const sectionsDiv = $(`#sections-${areaId}`);
                const btn = $(`#toggle-btn-${areaId}`);
                const icon = btn.find('i');

                if (sectionsDiv.hasClass('show')) {
                    // Hide sections with animation
                    sectionsDiv.slideUp(300, function () {
                        sectionsDiv.removeClass('show');
                    });
                    btn.removeClass('active');
                    icon.removeClass('fa-chevron-up').addClass('fa-chevron-down');
                } else {
                    // Show sections with animation
                    sectionsDiv.addClass('show');
                    sectionsDiv.slideDown(300);
                    btn.addClass('active');
                    icon.removeClass('fa-chevron-down').addClass('fa-chevron-up');

                    // Load sections if not already loaded
                    const contentDiv = $(`#sections-content-${areaId}`);
                    if (contentDiv.find('.spinner-border').length > 0 || contentDiv.children().length === 0) {
                        loadSectionsForArea(areaId);
                    }
                }
            };

            // Load sections for a specific area
            function loadSectionsForArea(areaId) {
                $.ajax({
                    url: `${baseUrl}parking/areas/${areaId}/sections`,
                    method: 'GET',
                    success: function (response) {
                        if (response.success) {
                            renderSections(areaId, response.data);
                        }
                    },
                    error: function () {
                        $(`#sections-content-${areaId}`).html(`
                            <div class="alert alert-warning small">
                                <i class="fas fa-exclamation-circle me-1"></i>
                                Failed to load sections
                            </div>
                        `);
                    }
                });
            }

            // Store sections data for each area (for filtering/pagination)
            const areaSectionsData = {};

            // Render sections for an area
            function renderSections(areaId, sections) {
                // Store sections data
                areaSectionsData[areaId] = {
                    allSections: sections,
                    currentFloor: 'all',
                    currentPage: 1,
                    perPage: 5
                };

                if (!sections || sections.length === 0) {
                    $(`#sections-content-${areaId}`).html(`
                        <p class="text-muted small text-center">
                            <i class="fas fa-info-circle me-1"></i>
                            No sections in this area yet
                        </p>
                    `);
                    return;
                }

                // Initial render
                updateSectionsDisplay(areaId);
            }

            // Filter sections by floor
            window.filterByFloor = function (areaId, floor) {
                const data = areaSectionsData[areaId];
                if (!data) return;

                data.currentFloor = floor;
                data.currentPage = 1;

                updateSectionsDisplay(areaId);
            };

            // Change page
            window.changeSectionPage = function (areaId, direction) {
                const data = areaSectionsData[areaId];
                if (!data) return;

                data.currentPage += direction;
                updateSectionsDisplay(areaId);
            };

            // Update sections display with current filters and pagination
            function updateSectionsDisplay(areaId) {
                const data = areaSectionsData[areaId];
                if (!data) return;

                // Regenerate filter HTML every time (if multiple floors exist)
                let filterHtml = '';
                const floors = [...new Set(data.allSections.map(s => s.floor_number || 1))].sort((a, b) => a - b);
                if (floors.length > 1) {
                    filterHtml = '<div class="floor-filter">';
                    filterHtml += '<small class="text-muted me-2">Floor:</small>';
                    filterHtml += `<button class="floor-filter-btn ${data.currentFloor === 'all' ? 'active' : ''}" onclick="filterByFloor(${areaId}, 'all')">All</button>`;
                    floors.forEach(floor => {
                        const isActive = data.currentFloor == floor ? 'active' : '';
                        filterHtml += `<button class="floor-filter-btn ${isActive}" onclick="filterByFloor(${areaId}, ${floor})">Floor ${floor}</button>`;
                    });
                    filterHtml += '</div>';
                }

                // Filter sections by floor
                let filteredSections = data.currentFloor === 'all'
                    ? data.allSections
                    : data.allSections.filter(s => (s.floor_number || 1) == data.currentFloor);

                // Calculate pagination
                const totalSections = filteredSections.length;
                const totalPages = Math.ceil(totalSections / data.perPage);
                const startIdx = (data.currentPage - 1) * data.perPage;
                const endIdx = startIdx + data.perPage;
                const paginatedSections = filteredSections.slice(startIdx, endIdx);

                // Render sections
                let html = '';
                paginatedSections.forEach(section => {
                    let spotsInfo, modeBadge = '';

                    if (section.section_mode === 'capacity_only') {
                        spotsInfo = `${section.capacity} capacity (width: ${section.grid_width})`;
                        modeBadge = '<span class="section-badge badge bg-warning ms-1">Capacity-only</span>';
                    } else {
                        const totalSpots = (section.rows || 0) * (section.columns || 0);
                        spotsInfo = `${section.rows}×${section.columns} = ${totalSpots} spots`;
                        modeBadge = '';
                    }

                    html += `
                        <div class="section-item d-flex justify-content-between align-items-center">
                            <div>
                                <strong>${escapeHtml(section.section_name)}</strong>
                                <span class="section-badge badge bg-light text-dark ms-2">
                                    Floor ${section.floor_number || 1}
                                </span>
                                <span class="section-badge badge bg-info ms-1">
                                    ${spotsInfo}
                                </span>
                                <span class="section-badge badge bg-secondary ms-1">
                                    ${escapeHtml(section.vehicle_type || 'N/A')}
                                </span>
                                ${modeBadge}
                            </div>
                            <div class="btn-group btn-group-sm">
                                <button class="btn btn-sm btn-maroon" 
                                        onclick="openEditSectionModal(${section.parking_section_id})" 
                                        title="Edit">
                                    <i class="fas fa-pen"></i>
                                </button>
                                <button class="btn btn-sm btn-danger" 
                                        onclick="deleteSection(${section.parking_section_id}, '${escapeHtml(section.section_name)}')" 
                                        title="Delete">
                                    <i class="fas fa-trash"></i>
                                </button>
                            </div>
                        </div>
                    `;
                });

                // Render pagination if needed
                let paginationHtml = '';
                if (totalPages > 1) {
                    paginationHtml = `
                        <div class="section-pagination">
                            <div class="pagination-info">
                                Showing ${startIdx + 1}-${Math.min(endIdx, totalSections)} of ${totalSections} sections
                            </div>
                            <div class="pagination-controls">
                                <button class="btn btn-sm btn-outline-secondary me-1" 
                                        ${data.currentPage === 1 ? 'disabled' : ''} 
                                        onclick="changeSectionPage(${areaId}, -1)">
                                    <i class="fas fa-chevron-left"></i> Prev
                                </button>
                                <span class="mx-2 text-muted">Page ${data.currentPage} of ${totalPages}</span>
                                <button class="btn btn-sm btn-outline-secondary ms-1" 
                                        ${data.currentPage === totalPages ? 'disabled' : ''} 
                                        onclick="changeSectionPage(${areaId}, 1)">
                                    Next <i class="fas fa-chevron-right"></i>
                                </button>
                            </div>
                        </div>
                    `;
                }

                // Update DOM
                const container = $(`#sections-content-${areaId}`);
                container.prev('.floor-filter').remove();
                if (filterHtml) {
                    container.before(filterHtml);
                }
                container.html(html + paginationHtml);
            }

            // Update stats cards
            window.updateStats = function (stats) {
                if (!stats) {
                    stats = { total_areas: 0, total_sections: 0, total_spots: 0, active_areas: 0 };
                }

                const totalAreas = parseInt(stats.total_areas) || 0;
                const totalSections = parseInt(stats.total_sections) || 0;
                const totalSpots = parseInt(stats.total_spots) || 0;
                const activeAreas = parseInt(stats.active_areas) || 0;

                const totalAreasEl = document.getElementById('statTotalAreas');
                const totalSectionsEl = document.getElementById('statTotalSections');
                const totalSpotsEl = document.getElementById('statTotalSpots');
                const activeAreasEl = document.getElementById('statActiveAreas');

                if (totalAreasEl) {
                    totalAreasEl.textContent = totalAreas;
                    totalAreasEl.classList.add('text-white');
                }
                if (totalSectionsEl) {
                    totalSectionsEl.textContent = totalSections;
                    totalSectionsEl.classList.add('text-white');
                }
                if (totalSpotsEl) {
                    totalSpotsEl.textContent = totalSpots;
                    totalSpotsEl.classList.add('text-white');
                }
                if (activeAreasEl) {
                    activeAreasEl.textContent = activeAreas;
                    activeAreasEl.classList.add('text-white');
                }
            }

            // Filter visibility management
            function updateAreasFilterVisibility() {
                // Always show filter actions to allow resetting to default
                $('#areasFilterActions').removeClass('filter-actions-hidden').addClass('filter-actions-visible');
            }

            // Show filter buttons when user types/changes filters (but don't apply yet)
            $('#searchInput').on('input', function () {
                updateAreasFilterVisibility();
            });

            $('#filterStatus').on('change', function () {
                updateAreasFilterVisibility();
            });

            // Apply Filter button - only trigger when clicked
            $('#applyAreasFilterBtn').on('click', function () {
                filterAreas();
            });

            // Clear Filters button
            $('#clearFiltersBtn').on('click', function () {
                $('#searchInput').val('');
                $('#filterStatus').val('');
                updateAreasFilterVisibility();
                filterAreas();
            });

            function filterAreas() {
                const search = $('#searchInput').val().toLowerCase();
                const status = $('#filterStatus').val();

                const filtered = allAreas.filter(area => {
                    const matchSearch = !search ||
                        area.parking_area_name.toLowerCase().includes(search) ||
                        (area.location && area.location.toLowerCase().includes(search));

                    const matchStatus = !status || area.status === status;

                    return matchSearch && matchStatus;
                });

                renderAreas(filtered);
            }

            // Refresh button
            $('#refreshAreasBtn').on('click', function () {
                loadAreas();
            });

            // ==========================
            // HELPER FUNCTIONS
            // ==========================

            // Clear validation errors helper
            function clearValidationErrors() {
                $('.is-invalid').removeClass('is-invalid');
                $('.invalid-feedback').text('');
            }

            // Show validation errors helper
            function showValidationErrors(errors) {
                Object.keys(errors).forEach(field => {
                    const input = $(`[name="${field}"]`);
                    input.addClass('is-invalid');
                    $(`#error-${field}`).text(errors[field]);
                });
            }

            // ==========================
            // FORM FIELD STATE MANAGEMENT
            // ==========================

            // ==========================
            // WIZARD FUNCTIONALITY
            // ==========================

            let wizardCurrentStep = 1;
            let wizardSections = [];
            const wizardModal = bootstrap.Modal.getOrCreateInstance($('#wizardModal')[0], {
                backdrop: 'static',
                keyboard: false,
                focus: false
            });
            // Open wizard instead of old modal
            $('#addAreaBtn').off('click').on('click', function () {
                // Blur any active element
                if (document.activeElement && document.activeElement.blur) {
                    document.activeElement.blur();
                }
                openWizard();
            });

            function openWizard() {
                wizardCurrentStep = 1;
                wizardSections = [];
                resetWizardForms();
                updateWizardStep();
                populateWizardVehicleTypes();
                initializeWizardLocationFeatures();

                // Reset footer state
                $('#wizardConfirmFooter').hide();
                $('#wizardNormalFooter').show();

                wizardModal.show();
            }

            function resetWizardForms() {
                $('#wizardStep1Form')[0].reset();
                $('#wizardSectionForm')[0].reset();
                $('#wizardNumFloors').val(1);
                $('#wizardSectionFloor').val(1);
                $('#wizardAreaStatus').val('active');
                $('#wizardLocationSuggestions').empty().removeClass('show');
                $('#wizardAreaLat').val('');
                $('#wizardAreaLon').val('');
                hideWizardLocationPreview();

                // Reset section mode visibility
                $('#specialVehicleOptions').addClass('d-none');
                $('#slotBasedFields').removeClass('d-none');
                $('#capacityOnlyFields').addClass('d-none');
                $('#wizardSectionRows, #wizardSectionColumns').prop('required', true);

                updateTotalSpotsPreview();
                renderWizardSectionsList();
            }

            function populateWizardVehicleTypes() {
                let options = '<option value="">Select Vehicle Type</option>';
                vehicleTypes.forEach(type => {
                    options += `<option value="${type.vehicle_type_id}">${type.vehicle_type_name || type.type_name}</option>`;
                });
                $('#wizardSectionVehicleType').html(options);
            }

            // Handle vehicle type change for Wizard modal
            $('#wizardSectionVehicleType').on('change', function () {
                const selectedVehicleName = $(this).find('option:selected').text().toLowerCase();

                // Check if this is Motorcycle or Bicycle
                const isSpecialVehicle = selectedVehicleName.includes('motorcycle') || selectedVehicleName.includes('bicycle');

                if (isSpecialVehicle) {
                    $('#specialVehicleOptions').removeClass('d-none');
                    // Reset to slot-based mode when vehicle type changes
                    $('input[name="sectionMode"][value="slot_based"]').prop('checked', true).trigger('change');
                } else {
                    $('#specialVehicleOptions').addClass('d-none');
                    // For regular vehicles, always show slot-based fields
                    $('#slotBasedFields').removeClass('d-none');
                    $('#capacityOnlyFields').addClass('d-none');
                    // Make rows/columns required again
                    $('#wizardSectionRows, #wizardSectionColumns').prop('required', true);
                }
            });

            // Wizard Navigation
            $('#wizardNextBtn').on('click', function () {
                if (validateWizardStep(wizardCurrentStep)) {
                    wizardCurrentStep++;
                    updateWizardStep();
                }
            });

            $('#wizardPrevBtn').on('click', function () {
                wizardCurrentStep--;
                updateWizardStep();
            });

            function updateWizardStep() {
                // Update stepper UI
                $('.wizard-step').removeClass('active completed');
                $(`.wizard-step[data-step="${wizardCurrentStep}"]`).addClass('active');
                for (let i = 1; i < wizardCurrentStep; i++) {
                    $(`.wizard-step[data-step="${i}"]`).addClass('completed');
                }

                // Show/hide content
                $('.wizard-content').addClass('d-none');
                $(`.wizard-content[data-step="${wizardCurrentStep}"]`).removeClass('d-none');

                // Update buttons
                if (wizardCurrentStep === 1) {
                    $('#wizardPrevBtn').hide();
                    $('#wizardNextBtn').show();
                    $('#wizardSubmitBtn').hide();
                } else if (wizardCurrentStep === 2) {
                    $('#wizardPrevBtn').show();
                    $('#wizardNextBtn').show();
                    $('#wizardSubmitBtn').hide();
                } else if (wizardCurrentStep === 3) {
                    $('#wizardPrevBtn').show();
                    $('#wizardNextBtn').hide();
                    $('#wizardSubmitBtn').show();
                    populateReviewStep();
                }
            }

            function validateWizardStep(step) {
                if (step === 1) {
                    const form = $('#wizardStep1Form')[0];
                    if (!form.checkValidity()) {
                        form.classList.add('was-validated');
                        // Focus on the first invalid field
                        const firstInvalid = form.querySelector(':invalid');
                        if (firstInvalid) {
                            firstInvalid.focus();
                            firstInvalid.scrollIntoView({ behavior: 'smooth', block: 'center' });
                        }
                        return false;
                    }
                    return true;
                } else if (step === 2) {
                    if (wizardSections.length === 0) {
                        showToast('Please add at least one section before continuing.', 'error');
                        return false;
                    }
                    return true;
                }
                return true;
            }

            // Handle vehicle type change for Add Section modal
            $('#sectionVehicleType').on('change', function () {
                const selectedVehicleName = $(this).find('option:selected').text().toLowerCase();

                // Check if this is Motorcycle or Bicycle
                const isSpecialVehicle = selectedVehicleName.includes('motorcycle') || selectedVehicleName.includes('bicycle');

                if (isSpecialVehicle) {
                    $('#addSpecialVehicleOptions').removeClass('d-none');
                    // Reset to slot-based mode when vehicle type changes
                    $('#addSlotBasedMode').prop('checked', true).trigger('change');
                } else {
                    $('#addSpecialVehicleOptions').addClass('d-none');
                    $('#addSlotBasedFields').removeClass('d-none');
                    $('#addCapacityOnlyFields').addClass('d-none');
                    // Make rows/columns required again
                    $('#sectionRows, #sectionColumns').prop('required', true);
                }
            });

            // Handle section mode change for Add Section modal
            $('input[name="addSectionMode"]').on('change', function () {
                const mode = $(this).val();

                // Update button active states
                $('input[name="addSectionMode"]').each(function () {
                    const label = $(`label[for="${$(this).attr('id')}"]`);
                    if ($(this).is(':checked')) {
                        label.addClass('active').removeClass('btn-outline-primary').addClass('btn-primary');
                    } else {
                        label.removeClass('active').removeClass('btn-primary').addClass('btn-outline-primary');
                    }
                });

                if (mode === 'capacity_only') {
                    $('#addSlotBasedFields').addClass('d-none');
                    $('#addCapacityOnlyFields').removeClass('d-none');
                    // Remove required from rows/columns, add to capacity fields
                    $('#sectionRows, #sectionColumns').prop('required', false);
                    $('#addSectionCapacity, #addSectionGridWidth').prop('required', true);
                } else {
                    $('#addSlotBasedFields').removeClass('d-none');
                    $('#addCapacityOnlyFields').addClass('d-none');
                    // Add required to rows/columns, remove from capacity fields
                    $('#sectionRows, #sectionColumns').prop('required', true);
                    $('#addSectionCapacity, #addSectionGridWidth').prop('required', false);
                }
            });

            // Handle vehicle type change for Edit Section modal
            $('#editSectionVehicleType').on('change', function () {
                const selectedVehicleName = $(this).find('option:selected').text().toLowerCase();

                // Check if this is Motorcycle or Bicycle
                const isSpecialVehicle = selectedVehicleName.includes('motorcycle') || selectedVehicleName.includes('bicycle');

                if (isSpecialVehicle) {
                    $('#editSpecialVehicleOptions').removeClass('d-none');
                    // Reset to slot-based mode when vehicle type changes
                    $('#editSlotBasedMode').prop('checked', true).trigger('change');
                } else {
                    $('#editSpecialVehicleOptions').addClass('d-none');
                    $('#editSlotBasedFields').removeClass('d-none');
                    $('#editCapacityOnlyFields').addClass('d-none');
                    // Make rows/columns required again
                    $('#editSectionRows, #editSectionColumns').prop('required', true);
                }
            });

            // Handle section mode change for Edit Section modal
            $('input[name="editSectionMode"]').on('change', function () {
                const mode = $(this).val();

                // Update button active states
                $('input[name="editSectionMode"]').each(function () {
                    const label = $(`label[for="${$(this).attr('id')}"]`);
                    if ($(this).is(':checked')) {
                        label.addClass('active').removeClass('btn-outline-primary').addClass('btn-primary');
                    } else {
                        label.removeClass('active').removeClass('btn-primary').addClass('btn-outline-primary');
                    }
                });

                if (mode === 'capacity_only') {
                    $('#editSlotBasedFields').addClass('d-none');
                    $('#editCapacityOnlyFields').removeClass('d-none');
                    // Remove required from rows/columns, add to capacity fields
                    $('#editSectionRows, #editSectionColumns').prop('required', false);
                    $('#editSectionCapacity, #editSectionGridWidth').prop('required', true);
                } else {
                    $('#editSlotBasedFields').removeClass('d-none');
                    $('#editCapacityOnlyFields').addClass('d-none');
                    // Add required to rows/columns, remove from capacity fields
                    $('#editSectionRows, #editSectionColumns').prop('required', true);
                    $('#editSectionCapacity, #editSectionGridWidth').prop('required', false);
                }
            });

            // Auto-calculate total spots preview for Add Section
            $('#sectionRows, #sectionColumns').on('input', function () {
                const rows = parseInt($('#sectionRows').val()) || 0;
                const cols = parseInt($('#sectionColumns').val()) || 0;
                const total = rows * cols;
                $('#totalSpotsPreview').text(`${total} spots`);
            });

            // Auto-calculate total spots preview for Edit Section
            $('#editSectionRows, #editSectionColumns').on('input', function () {
                const rows = parseInt($('#editSectionRows').val()) || 0;
                const cols = parseInt($('#editSectionColumns').val()) || 0;
                const total = rows * cols;
                $('#editTotalSpotsPreview').text(`${total} spots`);
            });

            function updateTotalSpotsPreview() {
                const mode = $('input[name="sectionMode"]:checked').val();
                let total = 0;

                if (mode === 'capacity_only') {
                    const capacity = parseInt($('#wizardSectionCapacity').val()) || 0;
                    total = capacity;
                } else {
                    const rows = parseInt($('#wizardSectionRows').val()) || 0;
                    const cols = parseInt($('#wizardSectionColumns').val()) || 0;
                    total = rows * cols;
                }

                $('#wizardTotalSpotsPreview').val(`${total} spots`);
            }

            // Auto-update total spots when rows/columns change
            $('#wizardSectionRows, #wizardSectionColumns').on('input', function () {
                updateTotalSpotsPreview();
            });

            // Auto-update total spots when capacity changes
            $('#wizardSectionCapacity').on('input', function () {
                updateTotalSpotsPreview();
            });

            // Add section to list
            $('#addSectionToListBtn').on('click', function () {
                const form = $('#wizardSectionForm')[0];
                if (!form.checkValidity()) {
                    form.classList.add('was-validated');
                    // Focus on the first invalid field
                    const firstInvalid = form.querySelector(':invalid');
                    if (firstInvalid) {
                        firstInvalid.focus();
                        firstInvalid.scrollIntoView({ behavior: 'smooth', block: 'center' });
                    }
                    return;
                }

                const vehicleTypeName = $('#wizardSectionVehicleType option:selected').text().toLowerCase();
                const isSpecialVehicle = vehicleTypeName.includes('motorcycle') || vehicleTypeName.includes('bicycle');
                const sectionMode = isSpecialVehicle ? $('input[name="sectionMode"]:checked').val() : 'slot_based';

                let section = {
                    id: Date.now(),
                    name: $('#wizardSectionName').val(),
                    floor: parseInt($('#wizardSectionFloor').val()),
                    vehicle_type_id: parseInt($('#wizardSectionVehicleType').val()),
                    vehicle_type_name: $('#wizardSectionVehicleType option:selected').text(),
                    section_mode: sectionMode
                };

                if (sectionMode === 'capacity_only') {
                    section.capacity = parseInt($('#wizardSectionCapacity').val());
                    section.grid_width = parseInt($('#wizardSectionGridWidth').val());
                    section.rows = 1; // For layout compatibility
                    section.columns = section.grid_width;
                } else {
                    section.rows = parseInt($('#wizardSectionRows').val());
                    section.columns = parseInt($('#wizardSectionColumns').val());
                    section.capacity = section.rows * section.columns;
                    section.grid_width = section.columns;
                }

                wizardSections.push(section);
                renderWizardSectionsList();

                // Reset form
                $('#wizardSectionForm')[0].reset();
                form.classList.remove('was-validated');
                $('#wizardSectionFloor').val(1);
                updateTotalSpotsPreview();

                // Reset special options visibility
                if (isSpecialVehicle) {
                    $('#slotBasedMode').prop('checked', true).trigger('change');
                }

                showToast('Section added to list!', 'success');
            });

            // Handle section mode change for Wizard modal
            $('input[name="sectionMode"]').on('change', function () {
                const mode = $(this).val();

                // Update button active states
                $('input[name="sectionMode"]').each(function () {
                    const label = $(`label[for="${$(this).attr('id')}"]`);
                    if ($(this).is(':checked')) {
                        label.addClass('active').removeClass('btn-outline-primary').addClass('btn-primary');
                    } else {
                        label.removeClass('active').removeClass('btn-primary').addClass('btn-outline-primary');
                    }
                });

                if (mode === 'capacity_only') {
                    $('#slotBasedFields').addClass('d-none');
                    $('#capacityOnlyFields').removeClass('d-none');
                    // Remove required from rows/columns, add to capacity fields
                    $('#wizardSectionRows, #wizardSectionColumns').prop('required', false);
                    $('#wizardSectionCapacity, #wizardSectionGridWidth').prop('required', true);
                } else {
                    $('#slotBasedFields').removeClass('d-none');
                    $('#capacityOnlyFields').addClass('d-none');
                    // Add required to rows/columns, remove from capacity fields
                    $('#wizardSectionRows, #wizardSectionColumns').prop('required', true);
                    $('#wizardSectionCapacity, #wizardSectionGridWidth').prop('required', false);
                }

                updateTotalSpotsPreview();
            });

            function renderWizardSectionsList() {
                const container = $('#sectionsList');
                const count = wizardSections.length;
                $('#sectionsCount').text(count);

                if (count === 0) {
                    container.html('<p class="text-muted text-center py-3">No sections added yet. Add at least one section to continue.</p>');
                    return;
                }

                let html = '';
                wizardSections.forEach((section, index) => {
                    let spotsInfo;
                    if (section.section_mode === 'capacity_only') {
                        spotsInfo = `${section.capacity} capacity (width: ${section.grid_width})`;
                    } else {
                        const totalSpots = section.rows * section.columns;
                        spotsInfo = `${section.rows}×${section.columns} = ${totalSpots} spots`;
                    }

                    const modeBadge = section.section_mode === 'capacity_only'
                        ? '<span class="badge bg-warning">Capacity-only</span>'
                        : '<span class="badge bg-info">Slot-based</span>';

                    html += `
                        <div class="section-list-item d-flex justify-content-between align-items-center mb-2 p-2 border rounded">
                            <div>
                                <strong>${section.name}</strong>
                                <span class="section-badge badge bg-light text-dark ms-2">Floor ${section.floor}</span>
                                <span class="section-badge badge bg-info ms-1">${spotsInfo}</span>
                                <span class="section-badge badge bg-secondary ms-1">${section.vehicle_type_name}</span>
                                ${section.section_mode === 'capacity_only' ? modeBadge : ''}
                            </div>
                            <button class="btn btn-sm btn-outline-danger" onclick="removeWizardSection(${section.id})">
                                <i class="fas fa-times"></i>
                            </button>
                        </div>
                    `;
                });

                container.html(html);
            }

            // Remove section from list
            window.removeWizardSection = function (id) {
                wizardSections = wizardSections.filter(s => s.id !== id);
                renderWizardSectionsList();
                showToast('Section removed from list', 'success');
            };

            function populateReviewStep() {
                $('#reviewAreaName').text($('#wizardAreaName').val());
                $('#reviewAreaLocation').text($('#wizardAreaLocation').val());
                $('#reviewNumFloors').text($('#wizardNumFloors').val());

                $('#reviewSectionsCount').text(wizardSections.length);
                let html = '';
                let totalSpots = 0;

                wizardSections.forEach((section, index) => {
                    let spots, layoutInfo;
                    if (section.section_mode === 'capacity_only') {
                        spots = section.capacity;
                        layoutInfo = `Capacity-only (width: ${section.grid_width})`;
                    } else {
                        spots = section.rows * section.columns;
                        layoutInfo = `${section.rows} × ${section.columns}`;
                    }
                    totalSpots += spots;

                    html += `
                        <tr>
                            <td>${index + 1}</td>
                            <td>${section.name}</td>
                            <td>Floor ${section.floor}</td>
                            <td>${layoutInfo}</td>
                            <td>${spots}</td>
                            <td><span class="badge bg-secondary">${section.vehicle_type_name}</span></td>
                        </tr>
                    `;
                });

                $('#reviewSectionsTable').html(html);
                $('#reviewTotalSpots').text(totalSpots);
            }

            // Submit wizard - Show confirmation first
            $('#wizardSubmitBtn').off('click').on('click', function () {
                const areaName = $('#wizardAreaName').val();
                const totalSections = wizardSections.length;
                const totalSpots = wizardSections.reduce((sum, s) => sum + (s.capacity || (s.rows * s.columns)), 0);

                // Show confirmation footer
                $('#wizardConfirmMessage').text('Are you sure you want to create this parking area with sections?');
                $('#wizardConfirmDescription').text(`You are about to create "${areaName}" with ${totalSections} section${totalSections !== 1 ? 's' : ''} and ${totalSpots} total parking spots.`);

                $('#wizardNormalFooter').hide();
                $('#wizardConfirmFooter').show();
            });

            // Wizard Confirm Cancel
            $('#wizardConfirmCancelBtn').on('click', function () {
                $('#wizardConfirmFooter').hide();
                $('#wizardNormalFooter').show();
            });

            // Wizard Confirm Yes - Actually submit
            $('#wizardConfirmYesBtn').on('click', function () {
                const btn = $(this);
                const originalText = btn.html();
                btn.prop('disabled', true).html('<span class="spinner-border spinner-border-sm me-1"></span> Creating...');

                const data = {
                    area: {
                        parking_area_name: $('#wizardAreaName').val(),
                        location: $('#wizardAreaLocation').val(),
                        latitude: $('#wizardAreaLat').val() || null,
                        longitude: $('#wizardAreaLon').val() || null,
                        num_of_floors: $('#wizardNumFloors').val(),
                        status: 'active'
                    },
                    sections: wizardSections.map(s => ({
                        section_name: s.name,
                        floor_number: s.floor,
                        rows: s.rows,
                        columns: s.columns,
                        vehicle_type_id: s.vehicle_type_id,
                        section_mode: s.section_mode,
                        capacity: s.capacity,
                        grid_width: s.grid_width
                    }))
                };

                $.ajax({
                    url: `${baseUrl}parking/areas/createWithSections`,
                    method: 'POST',
                    data: JSON.stringify(data),
                    contentType: 'application/json',
                    success: function (response) {
                        if (response.success) {
                            const totalSpots = wizardSections.reduce((sum, s) => sum + (s.capacity || (s.rows * s.columns)), 0);
                            wizardModal.hide();
                            showToast(`Success! Created parking area "${data.area.parking_area_name}" with ${wizardSections.length} sections and ${totalSpots} total spots!`, 'success');
                            if (response.stats) {
                                updateStats(response.stats);
                            }
                            loadAreas();
                        } else {
                            showToast(response.message || 'Failed to create parking area', 'error');
                            $('#wizardConfirmFooter').hide();
                            $('#wizardNormalFooter').show();
                        }
                    },
                    error: function (xhr) {
                        const message = xhr.responseJSON?.message || 'Failed to create parking area. Please try again.';
                        showToast(message, 'error');
                        $('#wizardConfirmFooter').hide();
                        $('#wizardNormalFooter').show();
                    },
                    complete: function () {
                        btn.prop('disabled', false).html(originalText);
                    }
                });
            });

            // ====================================
            // GEOAPIFY AUTOCOMPLETE + MAP HELPERS
            // ====================================

            function initializeWizardLocationFeatures() {
                if (wizardLocationBindingsInitialized) return;

                const input = $('#wizardAreaLocation');
                const suggestionsEl = $('#wizardLocationSuggestions');

                if (!input.length || !suggestionsEl.length) return;

                const debouncedSearch = debounce(function (query) {
                    fetchWizardLocationSuggestions(query);
                }, 300);

                input.on('input', function () {
                    const value = $(this).val().trim();
                    $('#wizardAreaLat, #wizardAreaLon').val('');
                    if (value.length < 3) {
                        suggestionsEl.removeClass('show').empty();
                        cancelWizardLocationRequest();
                        return;
                    }
                    debouncedSearch(value);
                });

                suggestionsEl.on('click', '.location-autocomplete-item', function () {
                    const data = $(this).data();
                    applyWizardLocationSelection(data);
                    suggestionsEl.removeClass('show').empty();
                });

                $(document).on('click', function (e) {
                    const wrapper = $('.location-autocomplete-wrapper');
                    if (!wrapper.is(e.target) && wrapper.has(e.target).length === 0) {
                        suggestionsEl.removeClass('show');
                    }
                });

                $('#wizardModal').on('hidden.bs.modal.location', function () {
                    suggestionsEl.removeClass('show').empty();
                });

                wizardLocationBindingsInitialized = true;
            }

            function fetchWizardLocationSuggestions(query) {
                const suggestionsEl = $('#wizardLocationSuggestions');
                if (!geoapifyKey) {
                    suggestionsEl.removeClass('show').empty();
                    return;
                }

                cancelWizardLocationRequest();
                wizardLocationAbortController = new AbortController();

                const controller = wizardLocationAbortController;
                const params = new URLSearchParams({
                    text: query,
                    limit: '5',
                    filter: 'countrycode:ph'
                });

                fetch(`${baseUrl}api/geoapify/autocomplete?${params.toString()}`, {
                    signal: controller.signal
                })
                    .then(res => res.ok ? res.json() : Promise.reject(res))
                    .then(data => {
                        renderWizardLocationSuggestions(data?.results || data?.features || []);
                    })
                    .catch(err => {
                        if (err.name === 'AbortError') return;
                        console.error('Geoapify autocomplete error', err);
                        suggestionsEl.removeClass('show').empty();
                    });
            }

            function renderWizardLocationSuggestions(results) {
                const suggestionsEl = $('#wizardLocationSuggestions');
                if (!results.length) {
                    suggestionsEl.removeClass('show').empty();
                    return;
                }

                const itemsHtml = results.map(result => {
                    const primaryTextRaw = result.address_line1 || result.formatted || 'Unknown location';
                    const secondaryTextRaw = result.address_line2 || result.city || result.country || '';
                    const fullTextRaw = result.formatted || result.address_line1 || primaryTextRaw;

                    const primaryText = escapeHtml(primaryTextRaw);
                    const secondaryText = escapeHtml(secondaryTextRaw);
                    const fullText = escapeHtml(fullTextRaw);
                    const lat = typeof result.lat === 'number' ? result.lat : '';
                    const lon = typeof result.lon === 'number' ? result.lon : '';

                    return `
                        <div class="location-autocomplete-item" data-primary="${primaryText}" data-secondary="${secondaryText}" data-full="${fullText}" data-lat="${lat}" data-lon="${lon}" role="option" tabindex="0">
                            <span class="location-primary">${primaryText}</span>
                            ${secondaryText ? `<span class="location-secondary">${secondaryText}</span>` : ''}
                        </div>
                    `;
                }).join('');

                suggestionsEl.html(itemsHtml).addClass('show');
            }

            function applyWizardLocationSelection(data) {
                const full = data.full || data.primary || '';
                const lat = data.lat || '';
                const lon = data.lon || '';

                $('#wizardAreaLocation').val(full || '');
                $('#wizardAreaLat').val(lat || '');
                $('#wizardAreaLon').val(lon || '');
                $('#wizardLocationSuggestions').removeClass('show').empty();
            }

            function updateWizardLocationPreview(details) {
                const preview = $('#wizardLocationMap');
                if (!preview.length) return;

                const primary = escapeHtml(details?.primary || details?.full || '');
                const secondary = escapeHtml(details?.secondary || '');
                const lat = details?.lat;
                const lon = details?.lon;

                if (!primary) {
                    preview.addClass('d-none').empty();
                    return;
                }

                preview.removeClass('d-none').html(`
                    <div class="location-preview-title">
                        <i class="fas fa-location-dot me-2 text-primary"></i>${primary}
                    </div>
                    ${secondary ? `<div class="location-preview-sub text-muted">${secondary}</div>` : ''}
                    <div class="location-preview-coords text-muted">
                        ${lat && lon ? `Lat: ${lat}, Lon: ${lon}` : 'Coordinates unavailable'}
                    </div>
                `);
            }

            function hideWizardLocationPreview() {
                $('#wizardLocationMap').addClass('d-none').empty();
            }

            function cancelWizardLocationRequest() {
                if (wizardLocationAbortController) {
                    wizardLocationAbortController.abort();
                    wizardLocationAbortController = null;
                }
            }

            function debounce(fn, delay) {
                let timer = null;
                return function (...args) {
                    clearTimeout(timer);
                    timer = setTimeout(() => fn.apply(this, args), delay);
                };
            }

            // Edit Area Modal
            window.openEditAreaModal = function (areaId) {
                // Blur any active element
                if (document.activeElement && document.activeElement.blur) {
                    document.activeElement.blur();
                }

                $.ajax({
                    url: `${baseUrl}parking/areas/get/${areaId}`,
                    method: 'GET',
                    success: function (response) {
                        if (response.success) {
                            const area = response.data;
                            $('#editAreaId').val(area.parking_area_id);
                            $('#editAreaName').val(area.parking_area_name);
                            $('#editAreaLocation').val(area.location);
                            $('#editAreaFloors').val(area.num_of_floors || 1);
                            $('#editAreaStatus').val(area.status);

                            const modal = bootstrap.Modal.getOrCreateInstance($('#editAreaModal')[0], {
                                backdrop: 'static',
                                keyboard: false,
                                focus: false
                            });
                            modal.show();
                        }
                    }
                });
            };

            // Edit Area - Submit button handler
            $('#editAreaSubmitBtn').off('click').on('click', function (e) {
                e.preventDefault();
                clearValidationErrors();

                const areaName = $('#editAreaName').val().trim();
                const location = $('#editAreaLocation').val().trim();

                // Client-side validation
                let hasErrors = false;
                const errors = {};

                if (!areaName) {
                    errors.parking_area_name = 'Area name is required';
                    hasErrors = true;
                }

                if (!location) {
                    errors.location = 'Location is required';
                    hasErrors = true;
                }

                if (hasErrors) {
                    showValidationErrors(errors);
                    return;
                }

                // Store form data for confirmation
                window.pendingEditAreaData = {
                    areaId: $('#editAreaId').val(),
                    areaName: areaName
                };

                // Build confirmation summary
                const summaryHtml = `
                    <div class="row">
                        <div class="col-md-6"><strong>Area Name:</strong></div>
                        <div class="col-md-6">${escapeHtml(areaName)}</div>
                    </div>
                    <div class="row">
                        <div class="col-md-6"><strong>Location:</strong></div>
                        <div class="col-md-6">${escapeHtml($('#editAreaLocation').val())}</div>
                    </div>
                    <div class="row">
                        <div class="col-md-6"><strong>Number of Floors:</strong></div>
                        <div class="col-md-6">${$('#editAreaFloors').val() || 1}</div>
                    </div>
                    <div class="row">
                        <div class="col-md-6"><strong>Status:</strong></div>
                        <div class="col-md-6">${$('#editAreaStatus').val() || 'active'}</div>
                    </div>
                `;

                // Show confirmation footer
                $('#editAreaConfirmTitle').text('Confirm Update Area');
                $('#editAreaConfirmMessage').text('Are you sure you want to update this parking area?');
                $('#editAreaConfirmDescription').text(`You are about to update "${areaName}"`);
                $('#editAreaConfirmSummary').html(summaryHtml);

                // Hide form section, show confirmation section
                $('#editAreaFormSection').hide();
                $('#editAreaConfirmSection').show();

                $('#editAreaNormalFooter').hide();
                $('#editAreaConfirmFooter').show();
            });

            // Edit Area - Confirm Cancel
            $('#editAreaConfirmCancelBtn').on('click', function () {
                // Show form section, hide confirmation section
                $('#editAreaFormSection').show();
                $('#editAreaConfirmSection').hide();

                $('#editAreaConfirmFooter').hide();
                $('#editAreaNormalFooter').show();
                delete window.pendingEditAreaData;
            });

            // Edit Area - Confirm Yes
            $('#editAreaConfirmYesBtn').on('click', function () {
                if (!window.pendingEditAreaData) return;

                const areaId = window.pendingEditAreaData.areaId;
                const formData = $('#editAreaForm').serialize();

                $.ajax({
                    url: `${baseUrl}parking/areas/update/${areaId}`,
                    method: 'POST',
                    data: formData,
                    success: function (response) {
                        if (response.success) {
                            bootstrap.Modal.getInstance($('#editAreaModal')[0]).hide();
                            showToast('Parking area updated successfully!', 'success');
                            if (response.stats) {
                                updateStats(response.stats);
                            }
                            loadAreas();
                        } else {
                            showToast(response.message || 'Failed to update area', 'error');
                            // Show form section, hide confirmation section
                            $('#editAreaFormSection').show();
                            $('#editAreaConfirmSection').hide();

                            $('#editAreaConfirmFooter').hide();
                            $('#editAreaNormalFooter').show();
                        }
                    },
                    error: function (xhr) {
                        const response = xhr.responseJSON;
                        if (response && response.errors) {
                            showValidationErrors(response.errors);
                            // Show form section, hide confirmation section
                            $('#editAreaFormSection').show();
                            $('#editAreaConfirmSection').hide();

                            $('#editAreaConfirmFooter').hide();
                            $('#editAreaNormalFooter').show();
                        } else {
                            showToast('Error updating parking area', 'error');
                            // Show form section, hide confirmation section
                            $('#editAreaFormSection').show();
                            $('#editAreaConfirmSection').hide();

                            $('#editAreaConfirmFooter').hide();
                            $('#editAreaNormalFooter').show();
                        }
                    },
                    complete: function () {
                        delete window.pendingEditAreaData;
                    }
                });
            });

            // Delete Area Modal
            window.openDeleteAreaModal = function (areaId, areaName) {
                // Blur any active element
                if (document.activeElement && document.activeElement.blur) {
                    document.activeElement.blur();
                }

                $('#deleteAreaId').val(areaId);
                $('#deleteAreaName').text(areaName);
                const modal = bootstrap.Modal.getOrCreateInstance($('#deleteAreaModal')[0], {
                    backdrop: 'static',
                    keyboard: false,
                    focus: false
                });
                modal.show();
            };

            $('#confirmDeleteAreaBtn').on('click', function () {
                const areaId = $('#deleteAreaId').val();

                ajaxWithCSRF(`${baseUrl}parking/areas/delete/${areaId}`, {
                    method: 'POST',
                    data: {},
                    success: function (response) {
                        if (response.success) {
                            bootstrap.Modal.getInstance($('#deleteAreaModal')[0]).hide();
                            showToast('Parking area deleted successfully!', 'success');
                            if (response.stats) {
                                updateStats(response.stats);
                            }
                            loadAreas();
                        } else {
                            showToast(response.message || 'Failed to delete area', 'error');
                        }
                    },
                    error: function () {
                        showToast('Error deleting parking area', 'error');
                    }
                });
            });

            // Add Section Modal
            window.openAddSectionModal = function (areaId, areaName) {
                // Blur any active element
                if (document.activeElement && document.activeElement.blur) {
                    document.activeElement.blur();
                }

                // Reset footer state
                $('#addSectionConfirmFooter').hide();
                $('#addSectionNormalFooter').show();
                clearValidationErrors();

                $('#sectionAreaId').val(areaId);
                $('#sectionAreaName').text(areaName);
                $('#addSectionForm')[0].reset();
                $('#sectionAreaId').val(areaId);
                $('#sectionFloor').val(1);
                $('#sectionRows').val(3);
                $('#sectionColumns').val(5);

                // Hide special options initially
                $('#addSpecialVehicleOptions').addClass('d-none');
                $('#addSlotBasedFields').removeClass('d-none');
                $('#addCapacityOnlyFields').addClass('d-none');

                updateSpotsPreview();

                // Trigger vehicle type change to show/hide special options
                $('#sectionVehicleType').trigger('change');

                const modal = bootstrap.Modal.getOrCreateInstance($('#addSectionModal')[0], {
                    backdrop: 'static',
                    keyboard: false,
                    focus: false
                });
                modal.show();
            };

            // Update spots preview when rows/columns change
            $('#sectionRows, #sectionColumns').on('input', updateSpotsPreview);
            $('#editSectionRows, #editSectionColumns').on('input', updateEditSpotsPreview);

            function updateSpotsPreview() {
                const rows = parseInt($('#sectionRows').val()) || 0;
                const cols = parseInt($('#sectionColumns').val()) || 0;
                $('#totalSpotsPreview').text(rows * cols);
            }

            function updateEditSpotsPreview() {
                const rows = parseInt($('#editSectionRows').val()) || 0;
                const cols = parseInt($('#editSectionColumns').val()) || 0;
                $('#editTotalSpotsPreview').text(rows * cols);
            }

            // Add Section - Submit button handler
            $('#addSectionSubmitBtn').off('click').on('click', function (e) {
                e.preventDefault();
                clearValidationErrors();

                const sectionName = $('#sectionName').val().trim();
                const areaId = $('#sectionAreaId').val();
                const floor = $('#sectionFloor').val();
                let rows = $('#sectionRows').val();
                let columns = $('#sectionColumns').val();
                const vehicleTypeId = $('#sectionVehicleType').val();
                const vehicleTypeName = $('#sectionVehicleType option:selected').text();

                // Check if this is a special vehicle type
                const isSpecialVehicle = vehicleTypeName.toLowerCase().includes('motorcycle') || vehicleTypeName.toLowerCase().includes('bicycle');
                const sectionMode = isSpecialVehicle ? $('input[name="addSectionMode"]:checked').val() : 'slot_based';

                let capacity, gridWidth;
                if (sectionMode === 'capacity_only') {
                    capacity = $('#addSectionCapacity').val();
                    gridWidth = $('#addSectionGridWidth').val();
                    rows = 1; // For layout compatibility
                    columns = gridWidth;
                } else {
                    capacity = rows * columns;
                    gridWidth = columns;
                }

                // Client-side validation
                let hasErrors = false;
                const errors = {};

                if (!sectionName) {
                    errors.section_name = 'Section name is required';
                    hasErrors = true;
                }

                if (!rows || rows < 1) {
                    errors.rows = 'Rows must be at least 1';
                    hasErrors = true;
                }

                if (!columns || columns < 1) {
                    errors.columns = 'Columns must be at least 1';
                    hasErrors = true;
                }

                if (!vehicleTypeId) {
                    errors.vehicle_type_id = 'Vehicle type is required';
                    hasErrors = true;
                }

                if (sectionMode === 'capacity_only') {
                    if (!capacity || capacity < 1) {
                        errors.capacity = 'Capacity must be at least 1';
                        hasErrors = true;
                    }
                    if (!gridWidth || gridWidth < 1 || gridWidth > 20) {
                        errors.grid_width = 'Grid width must be between 1 and 20';
                        hasErrors = true;
                    }
                }

                if (hasErrors) {
                    showValidationErrors(errors);
                    return;
                }

                // Store form data for confirmation
                window.pendingAddSectionData = {
                    sectionName: sectionName,
                    areaName: $('#sectionAreaName').text(),
                    sectionMode: sectionMode,
                    capacity: capacity,
                    gridWidth: gridWidth
                };

                // Build confirmation summary
                const summaryHtml = `
                    <div class="row">
                        <div class="col-md-6"><strong>Section Name:</strong></div>
                        <div class="col-md-6">${escapeHtml(sectionName)}</div>
                    </div>
                    <div class="row">
                        <div class="col-md-6"><strong>Area:</strong></div>
                        <div class="col-md-6">${escapeHtml($('#sectionAreaName').text())}</div>
                    </div>
                    <div class="row">
                        <div class="col-md-6"><strong>Floor:</strong></div>
                        <div class="col-md-6">${$('#sectionFloor').val() || 1}</div>
                    </div>
                    <div class="row">
                        <div class="col-md-6"><strong>Vehicle Type:</strong></div>
                        <div class="col-md-6">${$('#sectionVehicleType option:selected').text() || 'N/A'}</div>
                    </div>
                    <div class="row">
                        <div class="col-md-6"><strong>Section Mode:</strong></div>
                        <div class="col-md-6">${sectionMode === 'capacity_only' ? 'Capacity Only' : 'Slot-based Grid'}</div>
                    </div>
                    ${sectionMode === 'capacity_only' ? `
                        <div class="row">
                            <div class="col-md-6"><strong>Capacity:</strong></div>
                            <div class="col-md-6">${capacity || 0} spots</div>
                        </div>
                        <div class="row">
                            <div class="col-md-6"><strong>Grid Width:</strong></div>
                            <div class="col-md-6">${gridWidth || 1} columns</div>
                        </div>
                    ` : ''}
                `;

                // Show confirmation footer
                $('#addSectionConfirmTitle').text('Confirm Create Section');
                $('#addSectionConfirmMessage').text('Are you sure you want to create this section?');
                $('#addSectionConfirmDescription').text(`You are about to add section "${sectionName}" to ${$('#sectionAreaName').text()}`);
                $('#addSectionConfirmSummary').html(summaryHtml);

                // Hide form section, show confirmation section
                $('#addSectionFormSection').hide();
                $('#addSectionConfirmSection').show();

                $('#addSectionNormalFooter').hide();
                $('#addSectionConfirmFooter').show();
            });

            // Add Section - Confirm Cancel
            $('#addSectionConfirmCancelBtn').on('click', function () {
                // Show form section, hide confirmation section
                $('#addSectionFormSection').show();
                $('#addSectionConfirmSection').hide();

                $('#addSectionConfirmFooter').hide();
                $('#addSectionNormalFooter').show();
                delete window.pendingAddSectionData;
            });

            // Add Section - Confirm Yes
            $('#addSectionConfirmYesBtn').on('click', function () {
                if (!window.pendingAddSectionData) return;

                // Client-side validation for section name
                const sectionName = $('#sectionName').val().trim();
                if (!sectionName) {
                    showToast('Section name is required', 'error');
                    // Show form section, hide confirmation section
                    $('#addSectionFormSection').show();
                    $('#addSectionConfirmSection').hide();

                    $('#addSectionConfirmFooter').hide();
                    $('#addSectionNormalFooter').show();
                    return;
                }

                if (sectionName.length > 3) {
                    showToast('Section name must be 1-3 characters only', 'error');
                    // Show form section, hide confirmation section
                    $('#addSectionFormSection').show();
                    $('#addSectionConfirmSection').hide();

                    $('#addSectionConfirmFooter').hide();
                    $('#addSectionNormalFooter').show();
                    return;
                }

                $('#sectionName').val(sectionName);

                // Check for duplicate section name on the same floor (client-side)
                const areaId = $('#sectionAreaId').val();
                const floor = $('#sectionFloor').val() || 1;

                // Get current sections in this area
                $.ajax({
                    url: `${baseUrl}parking/areas/${areaId}/sections`,
                    method: 'GET',
                    success: function (response) {
                        if (response.success && response.data) {
                            const duplicateFound = response.data.some(section =>
                                section.section_name === sectionName &&
                                section.floor == floor
                            );

                            if (duplicateFound) {
                                showToast(`Section '${sectionName}' already exists on floor ${floor}. Please choose a different name.`, 'error');
                                // Show form section, hide confirmation section
                                $('#addSectionFormSection').show();
                                $('#addSectionConfirmSection').hide();

                                $('#addSectionConfirmFooter').hide();
                                $('#addSectionNormalFooter').show();
                                return;
                            }
                        }

                        // No duplicate found, proceed with form submission
                        proceedWithAddSection();
                    },
                    error: function () {
                        // If we can't check duplicates, proceed anyway (server will validate)
                        proceedWithAddSection();
                    }
                });

                return; // Stop here, continue in AJAX callback
            });

            // Function to proceed with add section after duplicate check
            function proceedWithAddSection() {
                // Create custom form data with corrected values for capacity_only mode
                const formData = new FormData();
                formData.append('parking_area_id', $('#sectionAreaId').val());
                formData.append('section_name', $('#sectionName').val());
                formData.append('floor_number', $('#sectionFloor').val());
                formData.append('vehicle_type_id', $('#sectionVehicleType').val());

                const vehicleTypeName = $('#sectionVehicleType option:selected').text();
                const isSpecialVehicle = vehicleTypeName.toLowerCase().includes('motorcycle') || vehicleTypeName.toLowerCase().includes('bicycle');
                const sectionMode = isSpecialVehicle ? $('input[name="addSectionMode"]:checked').val() : 'slot_based';

                formData.append('section_mode', sectionMode);

                if (sectionMode === 'capacity_only') {
                    formData.append('rows', 1);
                    formData.append('columns', $('#addSectionGridWidth').val());
                    formData.append('capacity', $('#addSectionCapacity').val());
                    formData.append('grid_width', $('#addSectionGridWidth').val());
                } else {
                    formData.append('rows', $('#sectionRows').val());
                    formData.append('columns', $('#sectionColumns').val());
                    formData.append('capacity', $('#sectionRows').val() * $('#sectionColumns').val());
                    formData.append('grid_width', $('#sectionColumns').val());
                }

                $.ajax({
                    url: `${baseUrl}parking/areas/sections/create`,
                    method: 'POST',
                    data: formData,
                    processData: false,
                    contentType: false,
                    success: function (response) {
                        if (response.success) {
                            const createdId = response.data?.parking_section_id;

                            // Build a section object from the form so UI updates instantly (no refetch)
                            const sectionMode = formData.get('section_mode') || 'slot_based';
                            const sectionObj = {
                                parking_section_id: createdId,
                                parking_area_id: parseInt(formData.get('parking_area_id'), 10),
                                section_name: String(formData.get('section_name') || ''),
                                floor: parseInt(formData.get('floor_number') || 1, 10),
                                floor_number: parseInt(formData.get('floor_number') || 1, 10),
                                vehicle_type_id: parseInt(formData.get('vehicle_type_id'), 10),
                                vehicle_type: String($('#sectionVehicleType option:selected').text() || '').toLowerCase(),
                                section_mode: sectionMode,
                                rows: parseInt(formData.get('rows') || 0, 10),
                                columns: parseInt(formData.get('columns') || 0, 10),
                                capacity: parseInt(formData.get('capacity') || 0, 10),
                                grid_width: parseInt(formData.get('grid_width') || 0, 10)
                            };

                            const spotsAdded = getSectionSpotsCount(sectionObj);
                            updateAreaAggregatesForSectionDelta(sectionObj.parking_area_id, 1, spotsAdded);
                            maybeUpdateViewSectionsStateForArea(sectionObj.parking_area_id, sectionObj, 'add');

                            bootstrap.Modal.getInstance($('#addSectionModal')[0]).hide();
                            showToast('Section created successfully!', 'success');
                            if (response.stats) {
                                updateStats(response.stats);
                            }
                        } else {
                            showToast(response.message || 'Failed to create section', 'error');
                            // Show form section, hide confirmation section
                            $('#addSectionFormSection').show();
                            $('#addSectionConfirmSection').hide();

                            $('#addSectionConfirmFooter').hide();
                            $('#addSectionNormalFooter').show();
                        }
                    },
                    error: function (xhr) {
                        const response = xhr.responseJSON;
                        if (response && response.errors) {
                            showValidationErrors(response.errors);
                            // Show form section, hide confirmation section
                            $('#addSectionFormSection').show();
                            $('#addSectionConfirmSection').hide();

                            $('#addSectionConfirmFooter').hide();
                            $('#addSectionNormalFooter').show();
                        } else {
                            showToast('Error creating section', 'error');
                            // Show form section, hide confirmation section
                            $('#addSectionFormSection').show();
                            $('#addSectionConfirmSection').hide();

                            $('#addSectionConfirmFooter').hide();
                            $('#addSectionNormalFooter').show();
                        }
                    },
                    complete: function () {
                        delete window.pendingAddSectionData;
                    }
                });
            };

            // Edit Section Modal
            window.openEditSectionModal = function (sectionId) {
                // Blur any active element
                if (document.activeElement && document.activeElement.blur) {
                    document.activeElement.blur();
                }

                $.ajax({
                    url: `${baseUrl}parking/areas/sections/get/${sectionId}`,
                    method: 'GET',
                    success: function (response) {
                        if (response.success) {
                            const section = response.data;
                            $('#editSectionId').val(section.parking_section_id);
                            $('#editSectionName').val(section.section_name);
                            $('#editSectionFloor').val(section.floor || section.floor_number || 1);
                            $('#editSectionVehicleType').val(section.vehicle_type_id);

                            // Check if this is a special vehicle type and set up special options
                            const vehicleTypeName = $('#editSectionVehicleType option:selected').text();
                            const isSpecialVehicle = vehicleTypeName.toLowerCase().includes('motorcycle') || vehicleTypeName.toLowerCase().includes('bicycle');

                            if (isSpecialVehicle) {
                                $('#editSpecialVehicleOptions').removeClass('d-none');

                                // Set section mode
                                const sectionMode = section.section_mode || 'slot_based';
                                $(`input[name="editSectionMode"][value="${sectionMode}"]`).prop('checked', true);

                                // Set capacity and grid width values
                                $('#editSectionCapacity').val(section.capacity || (section.rows * section.columns));
                                $('#editSectionGridWidth').val(section.grid_width || section.columns);

                                // For capacity_only, set rows/columns to the stored values (should be 1 and grid_width)
                                if (sectionMode === 'capacity_only') {
                                    $('#editSectionRows').val(1);
                                    $('#editSectionColumns').val(section.grid_width || section.columns);
                                } else {
                                    $('#editSectionRows').val(section.rows);
                                    $('#editSectionColumns').val(section.columns);
                                }

                                // Trigger change to show correct fields
                                $(`input[name="editSectionMode"][value="${sectionMode}"]`).trigger('change');
                            } else {
                                $('#editSpecialVehicleOptions').addClass('d-none');
                                $('#editSlotBasedFields').removeClass('d-none');
                                $('#editCapacityOnlyFields').addClass('d-none');
                                // For non-special vehicles, always show original rows/columns
                                $('#editSectionRows').val(section.rows);
                                $('#editSectionColumns').val(section.columns);
                            }

                            // Lock fields to maintain layout integrity
                            const lockedFields = [
                                '#editSectionFloor',
                                '#editSectionVehicleType',
                                '#editSectionRows',
                                '#editSectionColumns',
                                '#editSectionGridWidth'
                            ];

                            // Capacity is locked in slot-based mode (as it's a calculated field)
                            // but remains editable in capacity_only mode per user requirement
                            const currentMode = section.section_mode || 'slot_based';
                            if (currentMode === 'slot_based') {
                                lockedFields.push('#editSectionCapacity');
                            } else {
                                $('#editSectionCapacity').prop('disabled', false).removeClass('form-control-integrity-locked');
                            }

                            $(lockedFields.join(', ')).prop('disabled', true).addClass('form-control-integrity-locked');
                            $('input[name="editSectionMode"]').prop('disabled', true);

                            const modal = bootstrap.Modal.getOrCreateInstance($('#editSectionModal')[0], {
                                backdrop: 'static',
                                keyboard: false,
                                focus: false
                            });
                            modal.show();
                        }
                    }
                });
            };

            // Ensure fields are enabled in the ADD modal (safety reset)
            $('#addSectionModal').on('show.bs.modal', function () {
                const fields = [
                    '#sectionVehicleType',
                    '#sectionRows',
                    '#sectionColumns',
                    '#addSectionCapacity',
                    '#addSectionGridWidth'
                ];
                $(fields.join(', ')).prop('disabled', false).removeClass('form-control-integrity-locked');
                $('input[name="addSectionMode"]').prop('disabled', false);
            });

            // Edit Section - Submit button handler
            $('#editSectionSubmitBtn').off('click').on('click', function (e) {
                e.preventDefault();
                clearValidationErrors();

                const sectionName = $('#editSectionName').val().trim();
                let rows = parseInt($('#editSectionRows').val());
                let columns = parseInt($('#editSectionColumns').val());
                const vehicleTypeId = $('#editSectionVehicleType').val();
                const vehicleTypeName = $('#editSectionVehicleType option:selected').text();

                // Check if this is a special vehicle type
                const isSpecialVehicle = vehicleTypeName.toLowerCase().includes('motorcycle') || vehicleTypeName.toLowerCase().includes('bicycle');
                const sectionMode = isSpecialVehicle ? $('input[name="editSectionMode"]:checked').val() : 'slot_based';

                let capacity, gridWidth;
                if (sectionMode === 'capacity_only') {
                    capacity = $('#editSectionCapacity').val();
                    gridWidth = $('#editSectionGridWidth').val();
                    rows = 1; // For layout compatibility
                    columns = gridWidth;
                } else {
                    capacity = rows * columns;
                    gridWidth = columns;
                }

                // Client-side validation
                let hasErrors = false;
                const errors = {};

                if (!sectionName) {
                    errors.section_name = 'Section name is required';
                    hasErrors = true;
                }

                if (!rows || rows < 1) {
                    errors.rows = 'Rows must be at least 1';
                    hasErrors = true;
                }

                if (!columns || columns < 1) {
                    errors.columns = 'Columns must be at least 1';
                    hasErrors = true;
                }

                if (!vehicleTypeId) {
                    errors.vehicle_type_id = 'Vehicle type is required';
                    hasErrors = true;
                }

                if (sectionMode === 'capacity_only') {
                    if (!capacity || capacity < 1) {
                        errors.capacity = 'Capacity must be at least 1';
                        hasErrors = true;
                    }
                    if (!gridWidth || gridWidth < 1 || gridWidth > 20) {
                        errors.grid_width = 'Grid width must be between 1 and 20';
                        hasErrors = true;
                    }
                }

                if (hasErrors) {
                    showValidationErrors(errors);
                    return;
                }

                // Store form data for confirmation
                window.pendingEditSectionData = {
                    sectionId: $('#editSectionId').val(),
                    sectionName: sectionName,
                    sectionMode: sectionMode,
                    capacity: capacity,
                    gridWidth: gridWidth
                };

                // Build confirmation summary
                const summaryHtml = `
                    <div class="row">
                        <div class="col-md-6"><strong>Section Name:</strong></div>
                        <div class="col-md-6">${escapeHtml(sectionName)}</div>
                    </div>
                    <div class="row">
                        <div class="col-md-6"><strong>Floor:</strong></div>
                        <div class="col-md-6">${$('#editSectionFloor').val() || 1}</div>
                    </div>
                    <div class="row">
                        <div class="col-md-6"><strong>Vehicle Type:</strong></div>
                        <div class="col-md-6">${$('#editSectionVehicleType option:selected').text() || 'N/A'}</div>
                    </div>
                    <div class="row">
                        <div class="col-md-6"><strong>Section Mode:</strong></div>
                        <div class="col-md-6">${sectionMode === 'capacity_only' ? 'Capacity Only' : 'Slot-based Grid'}</div>
                    </div>
                    ${sectionMode === 'capacity_only' ? `
                        <div class="row">
                            <div class="col-md-6"><strong>Capacity:</strong></div>
                            <div class="col-md-6">${capacity || 0} spots</div>
                        </div>
                        <div class="row">
                            <div class="col-md-6"><strong>Grid Width:</strong></div>
                            <div class="col-md-6">${gridWidth || 1} columns</div>
                        </div>
                    ` : `
                        <div class="row">
                            <div class="col-md-6"><strong>Grid Size:</strong></div>
                            <div class="col-md-6">${$('#editSectionRows').val() || 0} × ${$('#editSectionColumns').val() || 0}</div>
                        </div>
                    `}
                `;

                // Show confirmation footer
                $('#editSectionConfirmTitle').text('Confirm Update Section');
                $('#editSectionConfirmMessage').text('Are you sure you want to update this section?');
                $('#editSectionConfirmDescription').text(`You are about to update section "${sectionName}"`);
                $('#editSectionConfirmSummary').html(summaryHtml);

                // Hide form section, show confirmation section
                $('#editSectionFormSection').hide();
                $('#editSectionConfirmSection').show();

                $('#editSectionNormalFooter').hide();
                $('#editSectionConfirmFooter').show();
            });

            // Edit Section - Confirm Cancel
            $('#editSectionConfirmCancelBtn').on('click', function () {
                // Show form section, hide confirmation section
                $('#editSectionFormSection').show();
                $('#editSectionConfirmSection').hide();

                $('#editSectionConfirmFooter').hide();
                $('#editSectionNormalFooter').show();
                delete window.pendingEditSectionData;
            });

            // Edit Section - Confirm Yes
            $('#editSectionConfirmYesBtn').on('click', function () {
                if (!window.pendingEditSectionData) return;

                const sectionId = window.pendingEditSectionData.sectionId;

                // Client-side validation for section name
                const sectionName = $('#editSectionName').val().trim();
                if (!sectionName) {
                    showToast('Section name is required', 'error');
                    // Show form section, hide confirmation section
                    $('#editSectionFormSection').show();
                    $('#editSectionConfirmSection').hide();

                    $('#editSectionConfirmFooter').hide();
                    $('#editSectionNormalFooter').show();
                    return;
                }

                if (sectionName.length > 3) {
                    showToast('Section name must be 1-3 characters only', 'error');
                    // Show form section, hide confirmation section
                    $('#editSectionFormSection').show();
                    $('#editSectionConfirmSection').hide();

                    $('#editSectionConfirmFooter').hide();
                    $('#editSectionNormalFooter').show();
                    return;
                }

                $('#editSectionName').val(sectionName);

                // Create custom form data with corrected values for capacity_only mode
                const formData = new FormData();
                formData.append('section_name', sectionName);
                formData.append('floor_number', $('#editSectionFloor').val());
                formData.append('vehicle_type_id', $('#editSectionVehicleType').val());

                const vehicleTypeName = $('#editSectionVehicleType option:selected').text();
                const isSpecialVehicle = vehicleTypeName.toLowerCase().includes('motorcycle') || vehicleTypeName.toLowerCase().includes('bicycle');
                const sectionMode = isSpecialVehicle ? $('input[name="editSectionMode"]:checked').val() : 'slot_based';

                formData.append('section_mode', sectionMode);

                if (sectionMode === 'capacity_only') {
                    formData.append('rows', 1);
                    formData.append('columns', $('#editSectionGridWidth').val());
                    formData.append('capacity', $('#editSectionCapacity').val());
                    formData.append('grid_width', $('#editSectionGridWidth').val());
                } else {
                    formData.append('rows', $('#editSectionRows').val());
                    formData.append('columns', $('#editSectionColumns').val());
                    formData.append('capacity', $('#editSectionRows').val() * $('#editSectionColumns').val());
                    formData.append('grid_width', $('#editSectionColumns').val());
                }

                $.ajax({
                    url: `${baseUrl}parking/areas/sections/update/${sectionId}`,
                    method: 'POST',
                    data: formData,
                    processData: false,
                    contentType: false,
                    success: function (response) {
                        if (response.success) {
                            const sectionId = parseInt($('#editSectionId').val(), 10);

                            // compute delta spots (old vs new) if section exists in modal state
                            const existing = (areaSectionsState.sections || []).find(s => String(s.parking_section_id) === String(sectionId));
                            const oldSpots = getSectionSpotsCount(existing);

                            const sectionMode = formData.get('section_mode') || 'slot_based';
                            const areaId = existing?.parking_area_id || areaSectionsState.areaId;
                            const updatedSection = {
                                parking_section_id: sectionId,
                                parking_area_id: areaId,
                                section_name: String(formData.get('section_name') || ''),
                                floor: parseInt(formData.get('floor_number') || 1, 10),
                                floor_number: parseInt(formData.get('floor_number') || 1, 10),
                                vehicle_type_id: parseInt(formData.get('vehicle_type_id'), 10),
                                vehicle_type: String($('#editSectionVehicleType option:selected').text() || '').toLowerCase(),
                                section_mode: sectionMode,
                                rows: parseInt(formData.get('rows') || 0, 10),
                                columns: parseInt(formData.get('columns') || 0, 10),
                                capacity: parseInt(formData.get('capacity') || 0, 10),
                                grid_width: parseInt(formData.get('grid_width') || 0, 10)
                            };

                            const newSpots = getSectionSpotsCount(updatedSection);
                            const deltaSpots = newSpots - oldSpots;
                            updateAreaAggregatesForSectionDelta(areaId, 0, deltaSpots);
                            maybeUpdateViewSectionsStateForArea(areaId, updatedSection, 'edit');

                            bootstrap.Modal.getInstance($('#editSectionModal')[0]).hide();
                            showToast('Section updated successfully!', 'success');
                            if (response.stats) {
                                updateStats(response.stats);
                            }
                        } else {
                            showToast(response.message || 'Failed to update section', 'error');
                            // Show form section, hide confirmation section
                            $('#editSectionFormSection').show();
                            $('#editSectionConfirmSection').hide();

                            $('#editSectionConfirmFooter').hide();
                            $('#editSectionNormalFooter').show();
                        }
                    },
                    error: function (xhr) {
                        const response = xhr.responseJSON;
                        if (response && response.errors) {
                            showValidationErrors(response.errors);
                            // Show form section, hide confirmation section
                            $('#editSectionFormSection').show();
                            $('#editSectionConfirmSection').hide();

                            $('#editSectionConfirmFooter').hide();
                            $('#editSectionNormalFooter').show();
                        } else {
                            showToast('Error updating section', 'error');
                            // Show form section, hide confirmation section
                            $('#editSectionFormSection').show();
                            $('#editSectionConfirmSection').hide();

                            $('#editSectionConfirmFooter').hide();
                            $('#editSectionNormalFooter').show();
                        }
                    },
                    complete: function () {
                        delete window.pendingEditSectionData;
                    }
                });
            });

            // Delete Section (using delete modal)
            window.deleteSection = function (sectionId, sectionName) {
                // Use the global delete modal if available
                if (typeof window.openDeleteModal === 'function') {
                    window.openDeleteModal(sectionId, sectionName, 'parking-section');
                } else {
                    // Fallback to confirm if modal not available
                    if (confirm(`Are you sure you want to delete section "${sectionName}"?\n\nThis will also delete all parking spots in this section.`)) {
                        $.ajax({
                            url: `${baseUrl}parking/areas/sections/delete/${sectionId}`,
                            method: 'POST',
                            data: {},
                            success: function (response) {
                                if (response.success) {
                                    if (typeof window.onParkingSectionDeleted === 'function') {
                                        window.onParkingSectionDeleted(sectionId);
                                    }
                                    showToast('Section deleted successfully!', 'success');
                                    if (response.stats) {
                                        updateStats(response.stats);
                                    }
                                } else {
                                    showToast(response.message || 'Failed to delete section', 'error');
                                }
                            },
                            error: function () {
                                showToast('Error deleting section', 'error');
                            }
                        });
                    }
                }
            };

            // Modal close handlers - reset to form view
            $('#editSectionModal').on('hidden.bs.modal', function () {
                $('#editSectionFormSection').show();
                $('#editSectionConfirmSection').hide();
                $('#editSectionNormalFooter').show();
                $('#editSectionConfirmFooter').hide();

                // Reset integrity locked fields
                const fields = [
                    '#editSectionFloor',
                    '#editSectionVehicleType',
                    '#editSectionRows',
                    '#editSectionColumns',
                    '#editSectionCapacity',
                    '#editSectionGridWidth'
                ];
                $(fields.join(', ')).prop('disabled', false).removeClass('form-control-integrity-locked');
                $('input[name="editSectionMode"]').prop('disabled', false);
            });

            // Fix for "Blocked aria-hidden" warning
            $('.modal').on('hide.bs.modal', function () {
                if (document.activeElement) {
                    document.activeElement.blur();
                }
            });

            // Modal close handlers - reset to form view
            $('#wizardModal').on('hidden.bs.modal', function () {
                // Clear wizard data
                wizardCurrentStep = 1;
                wizardSections = [];
            });

            $('#editAreaModal').on('hidden.bs.modal', function () {
                // Show form section, hide confirmation section
                $('#editAreaFormSection').show();
                $('#editAreaConfirmSection').hide();

                // Clear edit area data
                delete window.pendingEditAreaData;
            });

            $('#addSectionModal').on('hidden.bs.modal', function () {
                // Show form section, hide confirmation section
                $('#addSectionFormSection').show();
                $('#addSectionConfirmSection').hide();

                // Clear add section data
                delete window.pendingAddSectionData;
            });

            $('#editSectionModal').on('hidden.bs.modal', function () {
                // Show form section, hide confirmation section
                $('#editSectionFormSection').show();
                $('#editSectionConfirmSection').hide();

                // Clear edit section data
                delete window.pendingEditSectionData;
            });

            // If we reach here, we're on the parking areas page and have initialized it
            // Return early to prevent other page scripts from running
            return;
        }

        // Call original initPageScripts for other pages
        if (originalInitPageScripts) {
            originalInitPageScripts();
        }
    };
}

