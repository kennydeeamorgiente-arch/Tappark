/**
 * Staff Management JavaScript (includes Admins and Attendants)
 * Handles all attendant CRUD operations, filtering, pagination
 */

// Extend initPageScripts for attendants page
if (typeof window.initPageScripts === 'function') {
    const originalInitPageScripts = window.initPageScripts;

    window.initPageScripts = function () {
        // Check if we're on the attendants page FIRST
        if ($('#attendantsTable').length > 0) {
            console.log('Attendants page initialized');

            // Get base URL from global config
            const baseUrl = window.APP_BASE_URL || window.BASE_URL || '';

            // Global variables
            let currentPage = 1;
            let perPage = window.APP_RECORDS_PER_PAGE || 25;
            let currentFilters = {};
            let attendantTypes = [];

            // Listen for global records per page updates
            document.addEventListener('app-records-per-page-updated', function (e) {
                const newPerPage = e.detail.perPage;
                console.log('Attendants page: Records per page updated to', newPerPage);
                perPage = newPerPage;
                $('#perPageSelect').val(newPerPage);
                currentPage = 1;
                loadAttendants();
            });

            // Initialize shared filters
            if (typeof window.initSharedFilters === 'function') {
                window.initSharedFilters('attendants');
            }

            // Show export button for attendants
            $('#sharedExportBtn').css('display', 'block');

            // Initialize
            loadAttendantTypes();
            loadParkingAreas();
            initAttendantPasswordStrength();

            // Initialize filters for Users List tab (default)
            updateFiltersForTab('#users-list');

            // Load attendants with a small delay to ensure DOM is ready
            setTimeout(function () {
                loadAttendants();
            }, 100);

            // ====================================
            // LOAD ATTENDANT TYPES (for dropdown)
            // ====================================
            function loadAttendantTypes() {
                $.ajax({
                    url: `${baseUrl}attendants/getUserTypes`,
                    method: 'GET',
                    success: function (response) {
                        if (response.success) {
                            attendantTypes = response.data;
                            populateAttendantTypeDropdowns();
                        }
                    }
                });
            }

            // Populate attendant type dropdowns
            function populateAttendantTypeDropdowns() {
                let options = '<option value="">Select Role</option>';
                let filterOptions = '<option value="">All Roles</option>';

                attendantTypes.forEach(type => {
                    options += `<option value="${type.user_type_id}">${type.user_type_name}</option>`;
                    filterOptions += `<option value="${type.user_type_id}">${type.user_type_name}</option>`;
                });

                // Update modal dropdown
                $('#attendantUserTypeId').html(options);

                // Update shared filter dropdown
                $('#sharedFilterUserType').html(filterOptions);
            }

            // ====================================
            // LOAD PARKING AREAS
            // ====================================
            let parkingAreas = [];

            function loadParkingAreas() {
                $.ajax({
                    url: `${baseUrl}attendants/getParkingAreas`,
                    method: 'GET',
                    success: function (response) {
                        if (response.success) {
                            parkingAreas = response.data;
                            populateParkingAreaDropdowns();
                        }
                    },
                    error: function (xhr) {
                        console.error('Error loading parking areas:', xhr);
                    }
                });
            }

            // Populate parking area dropdowns
            function populateParkingAreaDropdowns() {
                let options = '<option value="">Not Assigned</option>';

                parkingAreas.forEach(area => {
                    options += `<option value="${area.parking_area_id}">${escapeHtml(area.parking_area_name)}</option>`;
                });

                // Update modal dropdown
                $('#attendantAssignedArea').html(options);
            }

            // ====================================
            // FILTER VISIBILITY MANAGEMENT
            // ====================================

            function updateFilterVisibility() {
                const hasActiveFilters = checkActiveFilters();
                const filterActionsContainer = $('#filterActionsContainer');

                if (hasActiveFilters) {
                    filterActionsContainer.removeClass('filter-actions-hidden').addClass('filter-actions-visible');
                } else {
                    filterActionsContainer.removeClass('filter-actions-visible').addClass('filter-actions-hidden');
                }
            }

            function checkActiveFilters() {
                const searchInput = $('#sharedSearchInput');
                const userTypeSelect = $('#sharedFilterUserType');
                const statusSelect = $('#sharedFilterStatus');

                const search = searchInput.length ? searchInput.val().trim() : '';
                const userType = userTypeSelect.length ? userTypeSelect.val() : '';
                const status = statusSelect.length ? statusSelect.val() : '';
                const online = $('#sharedFilterOnline').val();

                // Return true if ANY filter has a value (active filters)
                return !!(search || userType || status || online);
            }

            // Update filter visibility on any filter change
            $('#sharedSearchInput, #sharedFilterUserType, #sharedFilterStatus, #sharedFilterOnline').off('input.filter-visibility').on('input.filter-visibility', function () {
                updateFilterVisibility();
            });

            // Update filter visibility on document ready
            $(document).ready(function () {
                setTimeout(updateFilterVisibility, 200);
            });

            // Export to CSV
            $('#exportAttendantsBtn').off('click').on('click', function () {
                const params = buildExportParams();
                const exportUrl = baseUrl + 'attendants/export' + (params ? '?' + params : '');
                window.location.href = exportUrl;
            });

            // Build export parameters from current filters
            function buildExportParams() {
                const params = [];
                const search = $('#sharedSearchInput').val().trim();
                const userType = $('#sharedFilterUserType').val();
                const status = $('#sharedFilterStatus').val();
                const online = $('#sharedFilterOnline').val();

                if (search) params.push('search=' + encodeURIComponent(search));
                if (userType) params.push('user_type_id=' + encodeURIComponent(userType));
                if (status) params.push('status=' + encodeURIComponent(status));
                if (online) params.push('is_online=' + encodeURIComponent(online));

                return params.join('&');
            }

            // ====================================
            // LOAD ATTENDANTS TABLE
            // ====================================
            function loadAttendants() {
                const params = new URLSearchParams({
                    page: currentPage,
                    per_page: perPage,
                    ...currentFilters
                });

                $.ajax({
                    url: `${baseUrl}attendants/list?${params}`,
                    method: 'GET',
                    beforeSend: function () {
                        $('#attendantTableBody').html(`
                        <tr>
                            <td colspan="8" class="text-center py-5">
                                <div class="spinner-border text-primary" role="status"></div>
                                <p class="mt-2 text-muted">Loading attendants...</p>
                            </td>
                        </tr>
                    `);
                    },
                    success: function (response) {
                        if (response.success) {
                            // Store all data for dynamic filtering
                            allAttendantsData = response.data;
                            renderAttendantsTable(response.data);
                            renderPagination(response.pagination);
                            if (response.stats) {
                                updateStats(response.stats);
                            }
                            // Clear filters
                            currentFilters = {};
                            $('#sharedSearchInput').val('');
                            $('#sharedFilterUserType').val('');
                            $('#sharedFilterStatus').val('');
                            $('#sharedFilterOnline').val('');
                        }
                    },
                    error: function () {
                        $('#attendantTableBody').html(`
                        <tr>
                            <td colspan="8" class="text-center py-5 text-danger">
                                <i class="fas fa-exclamation-triangle fa-3x mb-3"></i>
                                <p>Error loading attendants. Please try again.</p>
                            </td>
                        </tr>
                    `);
                    }
                });
            }

            // ====================================
            // RENDER ATTENDANTS TABLE
            // ====================================
            function renderAttendantsTable(attendants) {
                let html = '';

                if (!attendants || attendants.length === 0) {
                    html = `
                    <tr>
                        <td colspan="8" class="text-center py-5">
                            <i class="fas fa-user-tie fa-3x text-muted mb-3"></i>
                            <p class="text-muted">No attendants found</p>
                        </td>
                    </tr>
                `;
                } else {
                    attendants.forEach((attendant, index) => {
                        const fullName = `${attendant.first_name || ''} ${attendant.last_name || ''}`.trim();
                        const statusClass = attendant.status === 'active' ? 'bg-success' :
                            attendant.status === 'inactive' ? 'bg-secondary' : 'bg-warning';
                        // Check online status properly (handle both string "1" and boolean/int)
                        const isOnline = (attendant.is_online == 1 || attendant.is_online === true || attendant.is_online === '1');
                        const onlineClass = isOnline ? 'bg-success' : 'bg-secondary';
                        const onlineText = isOnline ? 'Online' : 'Offline';

                        const startIdx = (currentPage - 1) * perPage + 1;

                        html += `
                        <tr data-attendant-id="${attendant.user_id}">
                            <td>#${startIdx + index}</td>
                            <td><strong>${escapeHtml(fullName || '-')}</strong></td>
                            <td>${escapeHtml(attendant.email || '-')}</td>
                            <td><span class="badge bg-info">${escapeHtml(attendant.user_type_name || '-')}</span></td>
                            <td>${escapeHtml(attendant.parking_area_name || 'Not Assigned')}</td>
                            <td><span class="badge ${statusClass}">${escapeHtml(attendant.status ? attendant.status.charAt(0).toUpperCase() + attendant.status.slice(1) : '-')}</span></td>
                            <td><span class="badge ${onlineClass}">${onlineText}</span></td>
                            <td>
                                <div class="btn-group" role="group">
                                    <button class="btn btn-sm btn-outline-primary view-attendant-btn" 
                                            data-id="${attendant.user_id}" 
                                            title="View"
                                            style="border-color: #800000; color: #800000;">
                                        <i class="fas fa-eye"></i>
                                    </button>
                                    <button class="btn btn-sm btn-outline-secondary edit-attendant-btn" 
                                            data-id="${attendant.user_id}"
                                            data-attendant='${JSON.stringify(attendant).replace(/"/g, '&quot;')}'
                                            title="Edit"
                                            style="border-color: var(--text-color); color: var(--text-color);">
                                        <i class="fas fa-pen"></i>
                                    </button>
                                    <button class="btn btn-sm btn-outline-danger delete-attendant-btn" 
                                            data-id="${attendant.user_id}" 
                                            data-name="${escapeHtml(fullName)}" 
                                            title="Delete"
                                            style="border-color: #dc3545; color: #dc3545;">
                                        <i class="fas fa-trash"></i>
                                    </button>
                                </div>
                            </td>
                        </tr>
                    `;
                    });
                }

                $('#attendantTableBody').html(html);
            }

            // ====================================
            // RENDER PAGINATION
            // ====================================
            function renderPagination(pagination) {
                const { current_page, per_page, total, total_pages, showing_from, showing_to } = pagination;

                currentPage = parseInt(current_page, 10);
                perPage = parseInt(per_page, 10);
                $('#perPageSelect').val(perPage);

                $('#paginationInfo').html(`Showing ${showing_from || 0} to ${showing_to || 0} of ${total} attendants`);
                $('#tableInfo').html(`${total} total attendants`);

                let paginationHtml = '';

                // Previous button
                paginationHtml += current_page === 1
                    ? '<li class="page-item disabled"><span class="page-link">Previous</span></li>'
                    : `<li class="page-item"><a class="page-link" href="#" data-page="${current_page - 1}">Previous</a></li>`;

                // Page numbers
                for (let i = 1; i <= total_pages; i++) {
                    if (i === 1 || i === total_pages || (i >= current_page - 2 && i <= current_page + 2)) {
                        paginationHtml += `
                        <li class="page-item ${i === current_page ? 'active' : ''}">
                            <a class="page-link" href="#" data-page="${i}">${i}</a>
                        </li>
                    `;
                    } else if (i === current_page - 3 || i === current_page + 3) {
                        paginationHtml += '<li class="page-item disabled"><span class="page-link">...</span></li>';
                    }
                }

                // Next button
                paginationHtml += (current_page === total_pages || total_pages === 0)
                    ? '<li class="page-item disabled"><span class="page-link">Next</span></li>'
                    : `<li class="page-item"><a class="page-link" href="#" data-page="${current_page + 1}">Next</a></li>`;

                $('#paginationControls').html(paginationHtml);
            }

            // ====================================
            // UPDATE STATS
            // ====================================
            function updateStats(stats) {
                if (!stats) {
                    stats = { total: 0, active: 0, online: 0, inactive: 0 };
                }

                const total = parseInt(stats.total) || 0;
                const active = parseInt(stats.active) || 0;
                const online = parseInt(stats.online) || 0;
                const inactive = parseInt(stats.inactive) || 0;

                const totalEl = document.getElementById('statTotalStaff');
                const activeEl = document.getElementById('statActiveStaff');
                const onlineEl = document.getElementById('statOnlineStaff');
                const inactiveEl = document.getElementById('statInactiveStaff');

                if (totalEl) {
                    totalEl.textContent = total;
                    totalEl.classList.add('text-white');
                }
                if (activeEl) {
                    activeEl.textContent = active;
                    activeEl.classList.add('text-white');
                }
                if (onlineEl) {
                    onlineEl.textContent = online;
                    onlineEl.classList.add('text-white');
                }
                if (inactiveEl) {
                    inactiveEl.textContent = inactive;
                    inactiveEl.classList.add('text-white');
                }
            }

            // ====================================
            // ESCAPE HTML HELPER
            // ====================================
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

            // ====================================
            // EVENT HANDLERS - PAGINATION
            // ====================================

            // Pagination click
            $(document).off('click', '#paginationControls a.page-link').on('click', '#paginationControls a.page-link', function (e) {
                e.preventDefault();
                if ($(this).parent().hasClass('disabled')) return false;

                const page = parseInt($(this).data('page'), 10);
                if (page && page > 0 && page !== currentPage) {
                    currentPage = page;
                    loadAttendants();
                }
                return false;
            });

            // Per page change
            $('#perPageSelect').off('change').on('change', function () {
                perPage = parseInt($(this).val());
                currentPage = 1;
                loadAttendants();
            });

            // ====================================
            // DYNAMIC FILTERING (No Reload)
            // ====================================

            // Store all attendants data for filtering
            let allAttendantsData = [];

            // Dynamic filter function
            function applyDynamicFilters() {
                const search = $('#sharedSearchInput').val().trim().toLowerCase();
                const userType = $('#sharedFilterUserType').val();
                const status = $('#sharedFilterStatus').val();
                const online = $('#sharedFilterOnline').val();

                const filteredAttendants = allAttendantsData.filter(attendant => {
                    // Search filter
                    if (search) {
                        // Check for exact ID match first (prioritize exact user_id match)
                        if (search.match(/^\d+$/) && attendant.user_id.toString() === search) {
                            return true; // Exact numeric ID match - include immediately
                        }

                        // Check for exact external_user_id match
                        if (attendant.external_user_id && attendant.external_user_id.toLowerCase() === search) {
                            return true; // Exact external ID match - include immediately
                        }

                        // If not exact ID match, then search in other fields (names, email, etc.)
                        const searchText = `${attendant.first_name} ${attendant.last_name} ${attendant.email} ${attendant.user_type_name} ${attendant.parking_area_name}`.toLowerCase();

                        // For numeric searches, don't match partial IDs in other fields
                        if (search.match(/^\d+$/)) {
                            // Only allow partial numeric matches in external_user_id
                            if (!attendant.external_user_id || !attendant.external_user_id.toLowerCase().includes(search)) {
                                return false; // No partial numeric ID matching in other fields
                            }
                        }

                        // For text searches, allow normal contains search in other fields
                        if (!searchText.includes(search) && (!attendant.external_user_id || !attendant.external_user_id.toLowerCase().includes(search))) {
                            return false;
                        }
                    }

                    // User type filter
                    if (userType && attendant.user_type_id != userType) return false;

                    // Status filter
                    if (status && attendant.status !== status) return false;

                    // Online status filter
                    if (online !== '' && ((online === '1' && attendant.is_online != 1) || (online === '0' && attendant.is_online == 1))) return false;

                    return true;
                });

                // Re-render table with filtered data
                renderAttendantsTable(filteredAttendants);

                // Update pagination info
                const total = filteredAttendants.length;
                $('#paginationInfo').html(`Showing ${Math.min(1, total)} to ${Math.min(total, total)} of ${total} attendants`);
                $('#tableInfo').html(`${total} total attendants`);

                // Hide pagination controls for filtered results
                $('#paginationControls').html('');
            }

            // ====================================
            // EVENT HANDLERS - FILTERS (Dynamic)
            // ====================================

            // Search input
            $('#sharedSearchInput').off('input').on('input', function () {
                currentFilters.search = $(this).val();
                currentPage = 1;
                applyDynamicFilters();
                updateFilterVisibility();
            });

            // Filter changes
            $('#sharedFilterUserType, #sharedFilterStatus, #sharedFilterOnline').off('change').on('change', function () {
                currentFilters.user_type_id = $('#sharedFilterUserType').val();
                currentFilters.status = $('#sharedFilterStatus').val();
                currentFilters.is_online = $('#sharedFilterOnline').val();
                currentPage = 1;
                applyDynamicFilters();
                updateFilterVisibility();
            });

            // Clear filters
            $('#sharedClearFiltersBtn').off('click').on('click', function () {
                $('#sharedSearchInput').val('');
                $('#sharedFilterUserType, #sharedFilterStatus, #sharedFilterOnline').val('');
                currentFilters = {};
                currentPage = 1;

                // Use dynamic filtering instead of reload
                if (allAttendantsData.length > 0) {
                    renderAttendantsTable(allAttendantsData);
                    renderPagination({ current_page: 1, per_page: perPage, total: allAttendantsData.length, total_pages: 1, showing_from: 1, showing_to: allAttendantsData.length });
                    $('#paginationInfo').html(`Showing 1 to ${allAttendantsData.length} of ${allAttendantsData.length} attendants`);
                    $('#tableInfo').html(`${allAttendantsData.length} total attendants`);
                } else {
                    loadAttendants();
                }

                updateFilterVisibility();
            });

            // Refresh
            $('#sharedRefreshBtn').off('click').on('click', function () {
                loadAttendants();
            });

            // Build export parameters from current filters
            function buildExportParams() {
                const params = [];
                const search = $('#sharedSearchInput').val().trim();
                const userType = $('#sharedFilterUserType').val();
                const status = $('#sharedFilterStatus').val();
                const online = $('#sharedFilterOnline').val();

                if (search) params.push('search=' + encodeURIComponent(search));
                if (userType) params.push('user_type_id=' + encodeURIComponent(userType));
                if (status) params.push('status=' + encodeURIComponent(status));
                if (online !== '') params.push('is_online=' + encodeURIComponent(online));

                return params.join('&');
            }

            // ====================================
            // ADD ATTENDANT
            // ====================================
            $('#addAttendantBtn').off('click').on('click', function () {
                // Blur any active element
                if (document.activeElement && document.activeElement.blur) {
                    document.activeElement.blur();
                }

                clearValidationErrors();
                resetAttendantPasswordStrength();

                // Reset form
                $('#crudForm')[0].reset();
                $('#crudEntityId').val('');
                $('#crudAction').val('add');
                $('#crudEntityType').val('attendants');

                // Ensure parking areas dropdown is populated
                populateParkingAreaDropdowns();

                // Reset footer to normal
                $('#crudConfirmFooter').hide();
                $('#crudNormalFooter').show();

                // Set modal mode
                $('#crudFormModal').removeClass('mode-edit').addClass('mode-add');

                // Update title
                $('#crudModalIcon').removeClass().addClass('fas fa-user-plus me-2');
                $('#crudModalTitleText').text('Add New Staff');
                $('#crudSubmitText').text('Add');

                // Hide all entity fields, show only attendants
                $('.entity-fields').hide();
                $('.fields-attendants').show();

                // Show modal
                const bsModal = bootstrap.Modal.getOrCreateInstance($('#crudFormModal')[0], {
                    backdrop: true,
                    keyboard: true,
                    focus: false
                });
                bsModal.show();

                // Focus on first field
                setTimeout(() => {
                    $('#attendantFirstName').focus();
                }, 500);
            });

            // ====================================
            // VIEW ATTENDANT
            // ====================================
            $(document).off('click', '.view-attendant-btn').on('click', '.view-attendant-btn', function () {
                const attendantId = $(this).data('id');

                if (!attendantId) {
                    console.error('Attendant ID is missing');
                    return;
                }

                // Fetch attendant data and show in view modal
                $.ajax({
                    url: `${baseUrl}attendants/get/${attendantId}`,
                    method: 'GET',
                    success: function (response) {
                        if (response.success) {
                            openViewAttendantModal(attendantId, response.data, 'attendants');
                        }
                    },
                    error: function (xhr) {
                        console.error('Error fetching attendant:', xhr);
                        showSuccessModal('Error', 'Error loading attendant details. Please try again.');
                    }
                });
            });

            // ====================================
            // OPEN VIEW ATTENDANT MODAL
            // ====================================
            window.openViewAttendantModal = function (attendantId, attendantData, entityType) {
                // Only handle if it's attendants
                if (entityType && entityType !== 'attendants') {
                    return; // Let other handlers take care of it
                }

                entityType = entityType || 'attendants';
                const modal = $('#viewDetailsModal');

                // Blur any active element
                if (document.activeElement && document.activeElement.blur) {
                    document.activeElement.blur();
                }

                // Store data for edit button
                modal.data('attendant-id', attendantId);
                modal.data('attendant-data', attendantData);

                // Update title
                $('#viewModalTitleText').text('Attendant Details');

                // Hide all view content, show loading
                $('.view-content').hide();
                $('#viewDetailsLoading').show();

                // Show modal
                const bsModal = bootstrap.Modal.getOrCreateInstance(modal[0], {
                    backdrop: true,
                    keyboard: true,
                    focus: false
                });
                bsModal.show();

                // Display attendant data
                setTimeout(function () {
                    displayAttendantViewData(attendantData);
                }, 300);

                // Handle Edit button click in view modal
                $('#viewEditBtn').off('click.attendants').on('click.attendants', function (e) {
                    e.stopImmediatePropagation();
                    e.preventDefault();

                    // Blur the button
                    if (document.activeElement && document.activeElement.blur) {
                        document.activeElement.blur();
                    }

                    // Close view modal
                    bsModal.hide();

                    // Small delay to ensure modal is closed
                    setTimeout(function () {
                        // Open edit modal
                        openEditAttendantModal(attendantId, attendantData);
                    }, 300);

                    return false;
                });
            };

            // ====================================
            // DISPLAY ATTENDANT VIEW DATA
            // ====================================
            function displayAttendantViewData(attendant) {
                $('#viewDetailsLoading').hide();
                $('.view-content').hide();
                $('.view-attendants').show();

                // Format dates
                const createdDate = attendant.created_at ? new Date(attendant.created_at).toLocaleDateString('en-US', {
                    year: 'numeric',
                    month: 'long',
                    day: 'numeric'
                }) : '-';

                const lastActivity = attendant.last_activity_at ? new Date(attendant.last_activity_at).toLocaleDateString('en-US', {
                    year: 'numeric',
                    month: 'long',
                    day: 'numeric',
                    hour: '2-digit',
                    minute: '2-digit'
                }) : 'Never';

                // Set avatar
                const firstName = attendant.first_name || 'A';
                const firstLetter = firstName.charAt(0).toUpperCase();
                // Use inline SVG instead of checking file (to avoid 404 errors)
                const avatarSrc = 'data:image/svg+xml;base64,' + btoa(`<svg width="120" height="120" xmlns="http://www.w3.org/2000/svg"><rect width="120" height="120" fill="#800000"/><text x="50%" y="50%" font-family="Arial, sans-serif" font-size="48" font-weight="bold" fill="#ffffff" text-anchor="middle" dominant-baseline="central">${firstLetter}</text></svg>`);

                // Status badge
                const statusClass = attendant.status === 'active' ? 'bg-success' :
                    attendant.status === 'inactive' ? 'bg-secondary' : 'bg-warning';
                const statusText = attendant.status ? attendant.status.charAt(0).toUpperCase() + attendant.status.slice(1) : '-';

                // Update fields
                $('#viewAttendantAvatar').attr('src', avatarSrc);
                $('#viewAttendantFullName').text(`${attendant.first_name || ''} ${attendant.last_name || ''}`.trim() || '-');
                $('#viewAttendantStatusBadge').removeClass().addClass('badge ' + statusClass).text(statusText);
                $('#viewAttendantId').text(attendant.user_id || '-');
                $('#viewAttendantEmail').text(attendant.email || '-');
                $('#viewAttendantRole').text(attendant.user_type_name || '-');
                $('#viewAttendantArea').text(attendant.parking_area_name || 'Not Assigned');
                // Check online status properly
                const isOnline = (attendant.is_online == 1 || attendant.is_online === true || attendant.is_online === '1');
                $('#viewAttendantOnline').html(isOnline
                    ? '<span class="badge bg-success">Online</span>'
                    : '<span class="badge bg-secondary">Offline</span>');
                $('#viewAttendantCreatedAt').text(createdDate);
                $('#viewAttendantLastActivity').text(lastActivity);
            }

            // ====================================
            // EDIT STAFF
            // ====================================
            $(document).off('click', '.edit-attendant-btn').on('click', '.edit-attendant-btn', function () {
                // Blur any active element
                if (document.activeElement && document.activeElement.blur) {
                    document.activeElement.blur();
                }

                clearValidationErrors();

                const attendantId = $(this).data('id');

                if (!attendantId) {
                    console.error('Attendant ID is missing');
                    return;
                }

                let attendantData = $(this).data('attendant');

                // Try to parse attendantData if it's a string
                if (typeof attendantData === 'string') {
                    try {
                        attendantData = JSON.parse(attendantData.replace(/&quot;/g, '"'));
                    } catch (e) {
                        attendantData = null;
                    }
                }

                // If we have the data already, use it
                if (attendantData && attendantData.user_id) {
                    openEditAttendantModal(attendantId, attendantData);
                } else {
                    // Fetch from server
                    $.ajax({
                        url: `${baseUrl}attendants/get/${attendantId}`,
                        method: 'GET',
                        success: function (response) {
                            if (response.success) {
                                openEditAttendantModal(attendantId, response.data);
                            } else {
                                showSuccessModal('Error', 'Error loading attendant data. Please try again.');
                            }
                        },
                        error: function (xhr) {
                            console.error('Error fetching attendant:', xhr);
                            showSuccessModal('Error', 'Error loading attendant data. Please try again.');
                        }
                    });
                }
            });

            function openEditAttendantModal(attendantId, attendantData) {
                // Reset form
                $('#crudForm')[0].reset();
                $('#crudEntityId').val(attendantId);
                $('#crudAction').val('edit');
                $('#crudEntityType').val('attendants');
                resetAttendantPasswordStrength();

                // Ensure parking areas dropdown is populated
                populateParkingAreaDropdowns();

                // Reset footer to normal
                $('#crudConfirmFooter').hide();
                $('#crudNormalFooter').show();

                // Set modal mode
                $('#crudFormModal').removeClass('mode-add').addClass('mode-edit');

                // Update title
                $('#crudModalIcon').removeClass().addClass('fas fa-user-edit me-2');
                $('#crudModalTitleText').text('Edit Staff');
                $('#crudSubmitText').text('Update');

                // Hide all entity fields, show only attendants
                $('.entity-fields').hide();
                $('.fields-attendants').show();

                // Fill form
                $('#attendantFirstName').val(attendantData.first_name || '');
                $('#attendantLastName').val(attendantData.last_name || '');
                $('#attendantEmail').val(attendantData.email || '');
                $('#attendantUserTypeId').val(attendantData.user_type_id || '');
                $('#attendantStatus').val(attendantData.status || 'active');

                // Set assigned area after dropdown is populated
                setTimeout(function () {
                    $('#attendantAssignedArea').val(attendantData.assigned_area_id || '');
                }, 100);

                // Show modal
                const bsModal = bootstrap.Modal.getOrCreateInstance($('#crudFormModal')[0], {
                    backdrop: true,
                    keyboard: true,
                    focus: false
                });
                bsModal.show();
            }

            // ====================================
            // DELETE ATTENDANT
            // ====================================
            $(document).off('click', '.delete-attendant-btn').on('click', '.delete-attendant-btn', function () {
                // Blur any active element
                if (document.activeElement && document.activeElement.blur) {
                    document.activeElement.blur();
                }

                const attendantId = $(this).data('id');
                const attendantName = $(this).data('name');

                if (!attendantId) {
                    console.error('Attendant ID is missing');
                    return;
                }

                // Use the global delete modal function
                if (typeof window.openDeleteModal === 'function') {
                    window.openDeleteModal(attendantId, attendantName, 'attendants');
                } else {
                    // Fallback if function doesn't exist
                    if (confirm(`Are you sure you want to delete attendant "${attendantName}"?`)) {
                        ajaxWithCSRF(`${baseUrl}attendants/delete/${attendantId}`, {
                            method: 'POST',
                            data: {},
                            success: function (response) {
                                if (response.success) {
                                    showSuccessModal('Staff Deleted Successfully', 'Staff member has been removed from the system.');
                                    // Remove from table dynamically instead of reloading
                                    removeAttendantFromTable(attendantId);
                                } else {
                                    showSuccessModal('Delete Failed', response.message || 'Failed to delete staff member');
                                }
                            },
                            error: function (xhr) {
                                const errorMsg = xhr.responseJSON?.message || 'Error deleting staff member. Please try again.';
                                showSuccessModal('Delete Error', errorMsg);
                            }
                        });
                    }
                }
            });

            // Extend the global confirmDelete to handle attendants
            const originalConfirmDelete = window.confirmDelete;
            window.confirmDelete = function () {
                const entity = $('#deleteEntityType').val();

                // Handle attendants
                if (entity === 'attendants') {
                    const attendantId = $('#deleteEntityId').val();
                    const deleteBtn = $('#confirmDeleteBtn');
                    const originalText = deleteBtn.html();

                    if (!attendantId) {
                        showSuccessModal('Error', 'Staff ID is missing');
                        return;
                    }

                    deleteBtn.prop('disabled', true).html('<span class="spinner-border spinner-border-sm me-2"></span>Deleting...');

                    ajaxWithCSRF(`${baseUrl}attendants/delete/${attendantId}`, {
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
                                showSuccessModal('Staff Deleted Successfully', 'Staff member has been removed from the system.');
                                // Remove from table dynamically instead of reloading
                                removeAttendantFromTable(attendantId);
                                if (response.stats && typeof updateStats === 'function') {
                                    updateStats(response.stats);
                                }

                            } else {
                                showSuccessModal('Delete Failed', response.message || 'Failed to delete staff member');
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

                            const errorMsg = xhr.responseJSON?.message || 'Error deleting staff member. Please try again.';
                            showSuccessModal('Delete Error', errorMsg);
                        },
                        complete: function () {
                            deleteBtn.prop('disabled', false).html(originalText);
                        }
                    });
                } else {
                    // Call original handler for other entity types
                    if (originalConfirmDelete && typeof originalConfirmDelete === 'function') {
                        originalConfirmDelete();
                    }
                }
            };

            // ====================================
            // TABLE SORTING FUNCTIONALITY
            // ====================================

            // Initialize sorting for attendants table
            function initializeAttendantTableSorting() {
                let sortOrder = {}; // Store sort order for each column

                $('#attendantsTable th.sortable').off('click').on('click', function () {
                    const $th = $(this);
                    const column = $th.data('column');
                    const $table = $('#attendantsTable');
                    const $tbody = $table.find('tbody');
                    const rows = $tbody.find('tr').toArray();

                    // Toggle sort order
                    sortOrder[column] = sortOrder[column] === 'asc' ? 'desc' : 'asc';

                    // Remove sort classes from all headers
                    $table.find('th').removeClass('asc desc');
                    $th.addClass(sortOrder[column]);

                    // Sort rows
                    rows.sort(function (a, b) {
                        const aValue = $(a).find('td').eq($th.index()).text().trim();
                        const bValue = $(b).find('td').eq($th.index()).text().trim();

                        // Handle numeric sorting
                        if (column === 'user_id') {
                            return sortOrder[column] === 'asc'
                                ? parseInt(aValue) - parseInt(bValue)
                                : parseInt(bValue) - parseInt(aValue);
                        }

                        // Handle text sorting
                        if (sortOrder[column] === 'asc') {
                            return aValue.localeCompare(bValue);
                        } else {
                            return bValue.localeCompare(aValue);
                        }
                    });

                    // Re-append sorted rows
                    $tbody.empty().append(rows);
                });
            }

            // Initialize sorting on page load
            initializeAttendantTableSorting();

            // ====================================
            // DYNAMIC TABLE FUNCTIONS
            // ====================================

            // Add attendant to table dynamically
            function addAttendantToTable(attendantData) {
                const fullName = `${attendantData.first_name || ''} ${attendantData.last_name || ''}`.trim();
                const statusClass = attendantData.status === 'active' ? 'bg-success' :
                    attendantData.status === 'inactive' ? 'bg-secondary' : 'bg-warning';
                // Check online status properly (handle both string "1" and boolean/int)
                const isOnline = (attendantData.is_online == 1 || attendantData.is_online === true || attendantData.is_online === '1');
                const onlineClass = isOnline ? 'bg-success' : 'bg-secondary';
                const onlineText = isOnline ? 'Online' : 'Offline';

                const attendantRow = `
                <tr data-attendant-id="${attendantData.user_id}">
                    <td>${attendantData.user_id}</td>
                    <td><strong>${escapeHtml(fullName || '-')}</strong></td>
                    <td>${escapeHtml(attendantData.email || '-')}</td>
                    <td><span class="badge bg-info">${escapeHtml(attendantData.user_type_name || '-')}</span></td>
                    <td>${escapeHtml(attendantData.parking_area_name || 'Not Assigned')}</td>
                    <td><span class="badge ${statusClass}">${escapeHtml(attendantData.status ? attendantData.status.charAt(0).toUpperCase() + attendantData.status.slice(1) : '-')}</span></td>
                    <td><span class="badge ${onlineClass}">${onlineText}</span></td>
                    <td>
                        <div class="btn-group" role="group">
                            <button class="btn btn-sm btn-outline-primary view-attendant-btn" 
                                    data-id="${attendantData.user_id}" 
                                    title="View"
                                    style="border-color: #800000; color: #800000;">
                                <i class="fas fa-eye"></i>
                            </button>
                            <button class="btn btn-sm btn-outline-secondary edit-attendant-btn" 
                                    data-id="${attendantData.user_id}"
                                    data-attendant='${JSON.stringify(attendantData).replace(/"/g, '&quot;')}'
                                    title="Edit"
                                    style="border-color: var(--text-color); color: var(--text-color);">
                                <i class="fas fa-pen"></i>
                            </button>
                            <button class="btn btn-sm btn-outline-danger delete-attendant-btn" 
                                    data-id="${attendantData.user_id}" 
                                    data-name="${escapeHtml(fullName)}" 
                                    title="Delete"
                                    style="border-color: #dc3545; color: #dc3545;">
                                <i class="fas fa-trash"></i>
                            </button>
                        </div>
                    </td>
                </tr>
            `;

                // Add at the bottom for proper order
                $('#attendantsTableBody').append(attendantRow);
            }

            // Update attendant in table dynamically
            function updateAttendantInTable(attendantData) {
                const row = $(`#attendantsTableBody tr[data-attendant-id="${attendantData.user_id}"]`);
                if (row.length) {
                    const fullName = `${attendantData.first_name || ''} ${attendantData.last_name || ''}`.trim();
                    const statusClass = attendantData.status === 'active' ? 'bg-success' :
                        attendantData.status === 'inactive' ? 'bg-secondary' : 'bg-warning';
                    // Check online status properly (handle both string "1" and boolean/int)
                    const isOnline = (attendantData.is_online == 1 || attendantData.is_online === true || attendantData.is_online === '1');
                    const onlineClass = isOnline ? 'bg-success' : 'bg-secondary';
                    const onlineText = isOnline ? 'Online' : 'Offline';

                    row.find('td:eq(0)').text(attendantData.user_id);
                    row.find('td:eq(1)').html(`<strong>${escapeHtml(fullName || '-')}</strong>`);
                    row.find('td:eq(2)').text(escapeHtml(attendantData.email || '-'));
                    row.find('td:eq(3)').html(`<span class="badge bg-info">${escapeHtml(attendantData.user_type_name || '-')}</span>`);
                    row.find('td:eq(4)').text(escapeHtml(attendantData.parking_area_name || 'Not Assigned'));
                    row.find('td:eq(5)').html(`<span class="badge ${statusClass}">${escapeHtml(attendantData.status ? attendantData.status.charAt(0).toUpperCase() + attendantData.status.slice(1) : '-')}</span>`);
                    row.find('td:eq(6)').html(`<span class="badge ${onlineClass}">${onlineText}</span>`);

                    // Update button data attributes
                    row.find('.edit-attendant-btn').data('id', attendantData.user_id).data('attendant', JSON.stringify(attendantData).replace(/"/g, '&quot;'));
                    row.find('.delete-attendant-btn').data('id', attendantData.user_id).data('name', fullName);
                }
            }

            // Remove attendant from table dynamically
            function removeAttendantFromTable(attendantId) {
                // Remove from cached data so filters don't show deleted attendants
                allAttendantsData = allAttendantsData.filter(a => String(a.user_id) !== String(attendantId));

                $(`#attendantsTableBody tr[data-attendant-id="${attendantId}"]`).fadeOut(300, function () {
                    $(this).remove();
                });
            }

            // ====================================
            // SUCCESS MODAL FUNCTION
            // ====================================
            // showSuccessModal moved to scripts.php

            // ====================================
            // FORM SUBMIT HANDLER
            // ====================================
            $('#crudSubmitBtn').off('click.attendants').on('click.attendants', function (e) {
                const entity = $('#crudEntityType').val();

                if (entity !== 'attendants') {
                    return;
                }

                // Stop propagation
                e.stopImmediatePropagation();

                clearValidationErrors();

                const action = $('#crudAction').val();

                // Get form data for validation
                const formData = {
                    first_name: $('#attendantFirstName').val().trim(),
                    last_name: $('#attendantLastName').val().trim(),
                    email: $('#attendantEmail').val().trim(),
                    password: $('#attendantPassword').val(),
                    user_type_id: $('#attendantUserTypeId').val(),
                    assigned_area_id: $('#attendantAssignedArea').val() || null,
                    status: $('#attendantStatus').val()
                };

                // Client-side validation
                let hasErrors = false;
                const errors = {};

                if (!formData.first_name) {
                    errors.first_name = 'First name is required';
                    hasErrors = true;
                }

                if (!formData.last_name) {
                    errors.last_name = 'Last name is required';
                    hasErrors = true;
                }

                if (!formData.email) {
                    errors.email = 'Email is required';
                    hasErrors = true;
                } else if (!window.isValidEmailStrict || !window.isValidEmailStrict(formData.email)) {
                    errors.email = 'Please enter a valid email address';
                    hasErrors = true;
                }

                if (action === 'add' && !formData.password) {
                    errors.password = 'Password is required';
                    hasErrors = true;
                } else if (formData.password && formData.password.length < 6) {
                    errors.password = 'Password must be at least 6 characters';
                    hasErrors = true;
                }

                if (!formData.user_type_id) {
                    errors.user_type_id = 'Role is required';
                    hasErrors = true;
                }

                // Check for duplicate names (client-side validation)
                if (action === 'add' && formData.first_name && formData.last_name) {
                    const fullName = `${formData.first_name} ${formData.last_name}`.toLowerCase();
                    const existingAttendants = $('#attendantsTableBody tr').map(function () {
                        const nameText = $(this).find('td:nth-child(2) strong').text().toLowerCase();
                        return nameText;
                    }).get();

                    if (existingAttendants.includes(fullName)) {
                        errors.first_name = 'A staff member with this name already exists';
                        errors.last_name = 'A staff member with this name already exists';
                        hasErrors = true;
                    }
                }

                // Show validation errors if any
                if (hasErrors) {
                    showValidationErrors(errors);
                    const firstErrorField = Object.keys(errors)[0];
                    $(`#crudFormModal [name="${firstErrorField}"]`).focus();
                    return;
                }

                // Store form data for confirmation
                window.pendingCrudFormData = formData;
                window.pendingCrudAction = action;

                // Build confirmation summary
                const summaryHtml = `
                <div class="row">
                    <div class="col-md-6"><strong>Name:</strong></div>
                    <div class="col-md-6">${escapeHtml(formData.first_name + ' ' + formData.last_name)}</div>
                </div>
                <div class="row">
                    <div class="col-md-6"><strong>Email:</strong></div>
                    <div class="col-md-6">${escapeHtml(formData.email)}</div>
                </div>
                <div class="row">
                    <div class="col-md-6"><strong>Role:</strong></div>
                    <div class="col-md-6">${$('#attendantUserTypeId option:selected').text() || 'N/A'}</div>
                </div>
                <div class="row">
                    <div class="col-md-6"><strong>Assigned Area:</strong></div>
                    <div class="col-md-6">${$('#attendantAssignedArea option:selected').text() || 'Not Assigned'}</div>
                </div>
                <div class="row">
                    <div class="col-md-6"><strong>Status:</strong></div>
                    <div class="col-md-6">${formData.status || 'active'}</div>
                </div>
                ${action === 'add' && formData.password ? '<div class="row"><div class="col-md-6"><strong>Password:</strong></div><div class="col-md-6">••••••••</div></div>' : ''}
            `;

                // Change to confirmation view
                const message = action === 'add'
                    ? 'Are you sure you want to add this staff member?'
                    : 'Are you sure you want to update this staff member?';
                const description = action === 'add'
                    ? `You are about to add "${formData.first_name} ${formData.last_name}" to the system.`
                    : `You are about to update staff member "${formData.first_name} ${formData.last_name}".`;

                $('#crudConfirmTitle').text('Confirm ' + (action === 'add' ? 'Add Staff' : 'Update Staff'));
                $('#crudConfirmMessage').text(message);
                $('#crudConfirmDescription').text(description);
                $('#crudConfirmSummary').html(summaryHtml);
                $('#crudConfirmYesText').text(action === 'add' ? 'Yes, Add Staff' : 'Yes, Update Staff');

                // Hide form section, show confirmation section
                $('#crudFormSection').hide();
                $('#crudConfirmSection').show();

                // Hide normal footer, show confirmation footer
                $('#crudNormalFooter').hide();
                $('#crudConfirmFooter').show();
            });

            // Cancel confirmation (No button)
            $('#crudConfirmCancelBtn').off('click.attendants').on('click.attendants', function () {
                // Check if we are on the users management page - if so, don't let attendants.js handle it
                if (window.location.pathname.includes('/users')) {
                    return;
                }

                // Show form section, hide confirmation section
                $('#crudFormSection').show();
                $('#crudConfirmSection').hide();

                // Hide confirmation footer, show normal footer
                $('#crudConfirmFooter').hide();
                $('#crudNormalFooter').show();

                // Clear stored data
                delete window.pendingCrudFormData;
                delete window.pendingCrudAction;
            });

            // Confirm button (Yes button) - handle attendants
            $('#crudConfirmYesBtn').off('click.attendants').on('click.attendants', function (e) {
                // Check if we are on the users management page - if so, don't let attendants.js handle it
                if (window.location.pathname.includes('/users')) {
                    return;
                }

                // Check entity type FIRST
                const entity = $('#crudEntityType').val();

                if (entity !== 'attendants') {
                    return; // Let other handlers handle it
                }

                // Stop propagation IMMEDIATELY
                e.stopImmediatePropagation();
                e.preventDefault();

                // Get stored form data
                const formData = window.pendingCrudFormData;
                const action = window.pendingCrudAction;
                const id = $('#crudEntityId').val();

                if (!formData) {
                    console.error('No form data found in window.pendingCrudFormData');
                    showSuccessModal('Error', 'Form data is missing. Please try again.');
                    return;
                }

                // Remove password if empty for edit
                if (action === 'edit' && !formData.password) {
                    delete formData.password;
                }

                const url = action === 'add'
                    ? `${baseUrl}attendants/create`
                    : `${baseUrl}attendants/update/${id}`;

                const method = 'POST';

                // Show loading state
                const confirmBtn = $('#crudConfirmYesBtn');
                const originalText = confirmBtn.html();
                confirmBtn.prop('disabled', true).html('<span class="spinner-border spinner-border-sm me-2"></span>Processing...');

                console.log('Attendants: Submitting to', url, 'with data:', formData);

                ajaxWithCSRF(url, {
                    method: method,
                    data: formData,
                    success: function (response) {
                        console.log('Attendants: Success response', response);
                        if (response.success) {
                            // Close form modal
                            const bsModal = bootstrap.Modal.getInstance($('#crudFormModal')[0]);
                            bsModal.hide();

                            // Reset footer
                            $('#crudConfirmFooter').hide();
                            $('#crudNormalFooter').show();

                            // Show success modal
                            showSuccessModal(action === 'add' ? 'Staff Added Successfully' : 'Staff Updated Successfully',
                                action === 'add'
                                    ? `Staff member "${formData.first_name} ${formData.last_name}" has been added to the system.`
                                    : `Staff member "${formData.first_name} ${formData.last_name}" has been updated successfully.`);

                            // Update table dynamically instead of reloading
                            if (action === 'add' && response.data) {
                                addAttendantToTable(response.data);
                            } else if (action === 'edit' && response.data) {
                                updateAttendantInTable(response.data);
                            }
                        } else {
                            // Show validation errors
                            if (response.errors) {
                                showValidationErrors(response.errors);
                                // Reset footer to normal
                                $('#crudConfirmFooter').hide();
                                $('#crudNormalFooter').show();
                            }
                        }
                    },
                    error: function (xhr) {
                        console.error('Attendants: Error response', xhr);
                        const response = xhr.responseJSON;
                        if (response && response.errors) {
                            showValidationErrors(response.errors);
                            // Reset footer to normal
                            $('#crudConfirmFooter').hide();
                            $('#crudNormalFooter').show();
                        } else {
                            showSuccessModal('Error', 'Error: ' + (response?.message || xhr.statusText || 'Unknown error'));
                        }
                    },
                    complete: function () {
                        confirmBtn.prop('disabled', false).html(originalText);
                        // Clear stored data
                        delete window.pendingCrudFormData;
                        delete window.pendingCrudAction;
                    }
                });

                return false;
            });

            // ====================================
            // HELPER FUNCTIONS
            // ====================================
            function clearValidationErrors() {
                $('#crudFormModal .is-invalid').removeClass('is-invalid');
                $('#crudFormModal .invalid-feedback').text('').hide();
            }

            function showValidationErrors(errors) {
                clearValidationErrors();

                Object.keys(errors).forEach(field => {
                    const input = $(`#crudFormModal [name="${field}"]`);
                    const errorDiv = $(`#error-${field}`);

                    if (input.length) {
                        input.addClass('is-invalid');
                    }
                    if (errorDiv.length) {
                        errorDiv.text(errors[field]).show();
                    }
                });
            }

            // Password strength helpers
            function initAttendantPasswordStrength() {
                if (!window.PasswordStrength) {
                    console.warn('PasswordStrength helper not found');
                    return;
                }

                $(document).off('input.attendantPassword').on('input.attendantPassword', '#attendantPassword', function () {
                    window.PasswordStrength.update(this, '#attendantPasswordStrengthBar', '#attendantPasswordStrengthText');
                });

                $('#crudFormModal').off('hidden.bs.modal.attendantPassword').on('hidden.bs.modal.attendantPassword', function () {
                    resetAttendantPasswordStrength();
                });
            }

            function resetAttendantPasswordStrength() {
                if (window.PasswordStrength) {
                    window.PasswordStrength.reset('#attendantPasswordStrengthBar', '#attendantPasswordStrengthText');
                }
            }

            // Reset footer when modal is closed
            $('#crudFormModal').off('hidden.bs.modal').on('hidden.bs.modal', function () {
                // Show form section, hide confirmation section
                $('#crudFormSection').show();
                $('#crudConfirmSection').hide();

                $('#crudConfirmFooter').hide();
                $('#crudNormalFooter').show();
                clearValidationErrors();
            });

            // ====================================
            // GUEST BOOKINGS FUNCTIONALITY
            // ====================================

            // Guest booking variables
            let guestCurrentPage = 1;
            let guestPerPage = 25;
            let guestCurrentFilters = {};
            let guestSortColumn = 'guest_booking_id';
            let guestSortDirection = 'desc';

            // Tab switching functionality
            $('#attendantsTabs button[data-bs-toggle="tab"]').on('shown.bs.tab', function (e) {
                const target = $(e.target).attr('data-bs-target');
                console.log('Tab switched to:', target);

                // Update filters based on active tab
                updateFiltersForTab(target);

                if (target === '#guest-bookings') {
                    // Initialize guest bookings when tab is shown
                    console.log('Guest bookings tab activated');
                    initializeGuestBookings();
                }
            });

            function updateFiltersForTab(targetTab) {
                // Hide all filters first
                $('.filter-field').hide();

                if (targetTab === '#users-list') {
                    // Show Users List filters
                    $('.filter-search').show();
                    $('.filter-user-type').show();
                    $('.filter-status').show();
                    $('.filter-online-status').show();

                    // Update search placeholder
                    $('#sharedSearchInput').attr('placeholder', 'Search users...');

                } else if (targetTab === '#guest-bookings') {
                    // Show Guest Bookings filters (without guest name)
                    $('.filter-search').show();
                    $('.filter-attendant').show();
                    $('.filter-vehicle-type').show();
                    $('.filter-date-range').show();

                    // Update search placeholder
                    $('#sharedSearchInput').attr('placeholder', 'Search guest bookings...');
                }

                // Clear any active filters when switching tabs
                clearAllFilters();
            }

            function clearAllFilters() {
                $('#sharedSearchInput').val('');
                $('#sharedFilterAttendant').val('');
                $('#sharedFilterVehicleType').val('');
                $('#sharedFilterDateRange').val('');
                $('#sharedFilterUserType').val('');
                $('#sharedFilterStatus').val('');
                $('#sharedFilterOnline').val('');

                // Hide filter actions
                $('#filterActionsContainer').addClass('filter-actions-hidden').removeClass('filter-actions-visible');
            }

            function initializeGuestBookings() {
                console.log('Initializing guest bookings...');
                loadGuestBookings();
                loadGuestBookingFilters();
                setupGuestBookingEventListeners();
            }

            function loadGuestBookings() {
                const params = {
                    page: guestCurrentPage,
                    per_page: guestPerPage,
                    sort_column: guestSortColumn,
                    sort_direction: guestSortDirection,
                    ...guestCurrentFilters
                };

                $.ajax({
                    url: `${baseUrl}attendants/getGuestBookings`,
                    method: 'GET',
                    data: params,
                    beforeSend: function () {
                        $('#guestBookingsTableBody').html(`
                        <tr>
                            <td colspan="7" class="text-center py-5">
                                <div class="spinner-border text-primary" role="status">
                                    <span class="visually-hidden">Loading...</span>
                                </div>
                                <p class="mt-2 text-muted">Loading guest bookings...</p>
                            </td>
                        </tr>
                    `);
                    },
                    success: function (response) {
                        console.log('AJAX Success:', response);
                        if (response.success) {
                            console.log('Data received:', response.data);
                            renderGuestBookingsTable(response.data.data);
                            updateGuestPagination(response.data);
                            updateGuestTableInfo(response.data);
                        } else {
                            console.log('Server returned error:', response.message);
                            $('#guestBookingsTableBody').html(`
                            <tr>
                                <td colspan="7" class="text-center py-5">
                                    <div class="alert alert-danger">
                                        <i class="fas fa-exclamation-triangle me-2"></i>
                                        Error loading guest bookings: ${response.message}
                                    </div>
                                </td>
                            </tr>
                        `);
                        }
                    },
                    error: function (xhr, status, error) {
                        console.error('AJAX Error:', xhr, status, error);
                        console.log('Response Text:', xhr.responseText);
                        $('#guestBookingsTableBody').html(`
                        <tr>
                            <td colspan="7" class="text-center py-5">
                                <div class="alert alert-danger">
                                    <i class="fas fa-exclamation-triangle me-2"></i>
                                    Error loading guest bookings. Status: ${status}, Error: ${error}
                                    <br><small>Response: ${xhr.responseText}</small>
                                </div>
                            </td>
                        </tr>
                    `);
                    }
                });
            }

            function renderGuestBookingsTable(bookings) {
                console.log('Rendering guest bookings table with data:', bookings);
                console.log('Number of bookings:', bookings.length);
                let html = '';

                if (bookings.length === 0) {
                    console.log('No bookings found, showing empty state');
                    html = `
                    <tr>
                        <td colspan="7" class="text-center py-5">
                            <div class="text-muted">
                                <i class="fas fa-inbox fa-3x mb-3"></i>
                                <p class="mb-0">No guest bookings found</p>
                            </div>
                        </td>
                    </tr>
                `;
                } else {
                    console.log('Rendering bookings rows...');
                    bookings.forEach((booking, index) => {
                        console.log(`Rendering booking ${index}:`, booking);
                        // Using a simple index + 1 here as this table doesn't seem to have complex pagination logic in JS
                        html += `
                        <tr>
                            <td>#${index + 1}</td>
                            <td>
                                <div class="d-flex align-items-center">
                                    <div class="avatar-sm bg-primary text-white rounded-circle d-flex align-items-center justify-content-center me-2" style="width: 32px; height: 32px; font-size: 12px;">
                                        ${booking.guest_name ? booking.guest_name.charAt(0).toUpperCase() : 'G'}
                                    </div>
                                    <div>
                                        <div class="fw-semibold">${booking.guest_name || 'Unknown Guest'}</div>
                                        <small class="text-muted">${booking.guest_email || 'No email'}</small>
                                    </div>
                                </div>
                            </td>
                            <td>
                                <div class="d-flex align-items-center">
                                    <i class="fas ${getVehicleIcon(booking.vehicle_type)} me-2 text-muted"></i>
                                    <div>
                                        <div class="fw-semibold">${booking.vehicle_brand || 'Unknown'}</div>
                                        <small class="text-muted">${booking.vehicle_color || 'N/A'} • ${booking.plate_number || 'N/A'}</small>
                                    </div>
                                </div>
                            </td>
                            <td>
                                <div class="d-flex align-items-center">
                                    <div class="avatar-sm bg-success text-white rounded-circle d-flex align-items-center justify-content-center me-2" style="width: 32px; height: 32px; font-size: 12px;">
                                        ${booking.attendant_name ? booking.attendant_name.charAt(0).toUpperCase() : 'A'}
                                    </div>
                                    <div>
                                        <div class="fw-semibold">${booking.attendant_name || 'Unknown'}</div>
                                        <small class="text-muted">${booking.attendant_role || 'Staff'}</small>
                                    </div>
                                </div>
                            </td>
                            <td>
                                <div>
                                    <div class="fw-semibold">#${booking.reservation_id}</div>
                                    <small class="text-muted">${booking.reservation_status || 'Unknown'}</small>
                                </div>
                            </td>
                            <td>
                                <div>
                                    <div class="fw-semibold">${formatDate(booking.created_at)}</div>
                                    <small class="text-muted">${formatTime(booking.created_at)}</small>
                                </div>
                            </td>
                            <td>
                                <div class="btn-group btn-group-sm" role="group">
                                    <button class="btn btn-outline-primary btn-sm" onclick="viewGuestBookingDetails(${booking.guest_booking_id})" title="View Details">
                                        <i class="fas fa-eye"></i>
                                    </button>
                                </div>
                            </td>
                        </tr>
                    `;
                    });
                }

                console.log('Final HTML length:', html.length);
                console.log('Updating table body...');
                console.log('Target element exists:', $('#guestBookingsTableBody').length > 0);
                console.log('Target element before update:', $('#guestBookingsTableBody').html());

                $('#guestBookingsTableBody').html(html);

                console.log('Table body updated');
                console.log('Target element after update:', $('#guestBookingsTableBody').html());
                console.log('Table body visible:', $('#guestBookingsTableBody').is(':visible'));
                console.log('Tab content visible:', $('#guest-bookings').is(':visible'));

                // Force the tab to be visible if it's hidden
                if (!$('#guest-bookings').is(':visible')) {
                    console.log('Forcing Guest Bookings tab to be visible...');

                    // Use Bootstrap's tab API properly
                    const guestTab = document.getElementById('guest-bookings-tab');
                    const guestTabPane = document.getElementById('guest-bookings');
                    const usersTab = document.getElementById('users-list-tab');
                    const usersTabPane = document.getElementById('users-list');

                    if (guestTab && guestTabPane) {
                        // Show the guest tab
                        const tab = new bootstrap.Tab(guestTab);
                        tab.show();
                        console.log('Bootstrap tab shown');
                    }

                    // Also try direct DOM manipulation as fallback
                    setTimeout(() => {
                        if (!$('#guest-bookings').is(':visible')) {
                            console.log('Fallback: Direct DOM manipulation');
                            $('#guest-bookings').css('display', 'block').addClass('show active');
                            $('#guest-bookings-tab').addClass('active');
                            $('#users-list').css('display', 'none').removeClass('show active');
                            $('#users-list-tab').removeClass('active');
                            console.log('Fallback applied');
                        }
                    }, 100);
                }
            }

            function loadGuestBookingFilters() {
                console.log('Loading guest booking filters...');

                // Load attendants for filter dropdown
                $.ajax({
                    url: `${baseUrl}attendants/getAttendantsList`,
                    method: 'GET',
                    success: function (response) {
                        console.log('Attendants loaded:', response);
                        if (response.success) {
                            let options = '<option value="">All Attendants</option>';
                            response.data.forEach(attendant => {
                                options += `<option value="${attendant.user_id}">${attendant.name}</option>`;
                            });
                            $('#sharedFilterAttendant').html(options);
                        } else {
                            console.error('Error loading attendants:', response.message);
                        }
                    },
                    error: function (xhr, status, error) {
                        console.error('AJAX Error loading attendants:', xhr, status, error);
                        console.log('Response:', xhr.responseText);
                    }
                });

                // Load vehicle types - use a simple approach since the endpoint might not exist
                const vehicleTypes = ['car', 'motorcycle', 'bicycle'];
                let options = '<option value="">All Vehicles</option>';
                vehicleTypes.forEach(type => {
                    options += `<option value="${type}">${type.charAt(0).toUpperCase() + type.slice(1)}</option>`;
                });
                $('#sharedFilterVehicleType').html(options);
            }

            function setupGuestBookingEventListeners() {
                // Per page change
                $('#guestPerPageSelect').off('change').on('change', function () {
                    guestPerPage = parseInt($(this).val());
                    guestCurrentPage = 1;
                    loadGuestBookings();
                });

                // Sorting
                $('#guestBookingsTable .sortable').off('click').on('click', function () {
                    const column = $(this).data('column');
                    if (guestSortColumn === column) {
                        guestSortDirection = guestSortDirection === 'asc' ? 'desc' : 'asc';
                    } else {
                        guestSortColumn = column;
                        guestSortDirection = 'asc';
                    }
                    loadGuestBookings();
                });

                // Filters
                $('#sharedSearchInput, #sharedFilterAttendant, #sharedFilterVehicleType, #sharedFilterDateRange').off('input change').on('input change', function () {
                    applyGuestBookingFilters();
                });

                // Export
                $('#exportGuestBookingsBtn').off('click').on('click', function () {
                    exportGuestBookings();
                });
            }

            function applyGuestBookingFilters() {
                guestCurrentFilters = {
                    search: $('#sharedSearchInput').val(),
                    attendant_id: $('#sharedFilterAttendant').val(),
                    vehicle_type: $('#sharedFilterVehicleType').val(),
                    date_range: $('#sharedFilterDateRange').val()
                };

                // Remove empty filters
                Object.keys(guestCurrentFilters).forEach(key => {
                    if (!guestCurrentFilters[key]) {
                        delete guestCurrentFilters[key];
                    }
                });

                guestCurrentPage = 1;
                loadGuestBookings();
            }

            function updateGuestPagination(data) {
                const pagination = $('#guestPaginationControls');
                const paginationInfo = $('#guestPaginationInfo');

                if (data.total <= data.per_page) {
                    pagination.empty();
                    paginationInfo.html(`Showing ${data.data.length} of ${data.total} guest bookings`);
                    return;
                }

                let html = '';
                const totalPages = Math.ceil(data.total / data.per_page);
                const currentPage = data.current_page;

                // Previous
                html += `
                <li class="page-item ${currentPage === 1 ? 'disabled' : ''}">
                    <a class="page-link" href="#" data-page="${currentPage - 1}">
                        <i class="fas fa-chevron-left"></i>
                    </a>
                </li>
            `;

                // Page numbers
                for (let i = 1; i <= totalPages; i++) {
                    if (i === 1 || i === totalPages || (i >= currentPage - 2 && i <= currentPage + 2)) {
                        html += `
                        <li class="page-item ${i === currentPage ? 'active' : ''}">
                            <a class="page-link" href="#" data-page="${i}">${i}</a>
                        </li>
                    `;
                    } else if (i === currentPage - 3 || i === currentPage + 3) {
                        html += `<li class="page-item disabled"><a class="page-link" href="#">...</a></li>`;
                    }
                }

                // Next
                html += `
                <li class="page-item ${currentPage === totalPages ? 'disabled' : ''}">
                    <a class="page-link" href="#" data-page="${currentPage + 1}">
                        <i class="fas fa-chevron-right"></i>
                    </a>
                </li>
            `;

                pagination.html(html);
                paginationInfo.html(`Showing ${data.from}-${data.to} of ${data.total} guest bookings`);

                // Pagination click handlers
                pagination.find('.page-link').off('click').on('click', function (e) {
                    e.preventDefault();
                    const page = parseInt($(this).data('page'));
                    if (page && page !== guestCurrentPage) {
                        guestCurrentPage = page;
                        loadGuestBookings();
                    }
                });
            }

            function updateGuestTableInfo(data) {
                $('#guestTableInfo').text(`Showing ${data.from}-${data.to} of ${data.total} guest bookings`);
            }

            function exportGuestBookings() {
                const params = {
                    export: 'csv',
                    ...guestCurrentFilters
                };

                const url = `${baseUrl}attendants/exportGuestBookings?${$.param(params)}`;
                window.open(url, '_blank');
            }

            // Helper functions
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

            function formatDate(dateString) {
                const date = new Date(dateString);
                return date.toLocaleDateString('en-US', {
                    year: 'numeric',
                    month: 'short',
                    day: 'numeric'
                });
            }

            function formatTime(dateString) {
                const date = new Date(dateString);
                return date.toLocaleTimeString('en-US', {
                    hour: '2-digit',
                    minute: '2-digit'
                });
            }

            // Placeholder functions for actions
            window.viewGuestBookingDetails = function (bookingId) {
                console.log('View guest booking details:', bookingId);

                // Load booking details and show modal
                $.ajax({
                    url: `${baseUrl}attendants/getGuestBookingDetails/${bookingId}`,
                    method: 'GET',
                    success: function (response) {
                        if (response.success) {
                            showGuestBookingModal(response.data);
                        } else {
                            alert('Error loading booking details: ' + response.message);
                        }
                    },
                    error: function () {
                        alert('Error loading booking details. Please try again.');
                    }
                });
            };

            window.viewReservation = function (reservationId) {
                console.log('View reservation:', reservationId);
                // Navigate to reservation details or open modal
                window.open(`${baseUrl}reservations/view/${reservationId}`, '_blank');
            };

            function showGuestBookingModal(booking) {
                const modalHtml = `
                <div class="modal fade" id="guestBookingModal" tabindex="-1">
                    <div class="modal-dialog modal-lg">
                        <div class="modal-content">
                            <div class="modal-header">
                                <h5 class="modal-title">Guest Booking Details</h5>
                                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                            </div>
                            <div class="modal-body">
                                <div class="row">
                                    <div class="col-md-6">
                                        <h6>Guest Information</h6>
                                        <p><strong>Name:</strong> ${booking.guest_name}</p>
                                        <p><strong>Email:</strong> ${booking.guest_email}</p>
                                        <p><strong>Booking ID:</strong> #${booking.guest_booking_id}</p>
                                    </div>
                                    <div class="col-md-6">
                                        <h6>Vehicle Information</h6>
                                        <p><strong>Type:</strong> ${booking.vehicle_type}</p>
                                        <p><strong>Brand:</strong> ${booking.vehicle_brand}</p>
                                        <p><strong>Color:</strong> ${booking.vehicle_color}</p>
                                        <p><strong>Plate:</strong> ${booking.plate_number}</p>
                                    </div>
                                </div>
                                <div class="row mt-3">
                                    <div class="col-md-6">
                                        <h6>Attendant Information</h6>
                                        <p><strong>Name:</strong> ${booking.attendant_name}</p>
                                        <p><strong>Role:</strong> ${booking.attendant_role}</p>
                                    </div>
                                    <div class="col-md-6">
                                        <h6>Reservation Information</h6>
                                        <p><strong>Reservation ID:</strong> #${booking.reservation_id}</p>
                                        <p><strong>Status:</strong> ${booking.reservation_status}</p>
                                        <p><strong>Created:</strong> ${formatDate(booking.created_at)} ${formatTime(booking.created_at)}</p>
                                    </div>
                                </div>
                            </div>
                            <div class="modal-footer">
                                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                            </div>
                        </div>
                    </div>
                </div>
            `;

                // Remove existing modal if any
                $('#guestBookingModal').remove();

                // Add modal to body and show it
                $('body').append(modalHtml);
                const modal = bootstrap.Modal.getOrCreateInstance(document.getElementById('guestBookingModal'));
                modal.show();
            }

            // If we reach here, we're on the attendants page and have initialized it
            // Return early to prevent other page scripts from running
            return;
        }

        // Call original initPageScripts for other pages
        if (originalInitPageScripts) {
            originalInitPageScripts();
        }
    };
}

