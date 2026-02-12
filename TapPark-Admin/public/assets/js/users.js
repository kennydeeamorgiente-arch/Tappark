/**
 * User Management JavaScript
 * Handles all user CRUD operations, filtering, pagination
 */

// Extend initPageScripts for users page
if (typeof window.initPageScripts === 'function') {
    const originalInitPageScripts = window.initPageScripts;

    window.initPageScripts = function () {
        // Check if we're on the users page FIRST - if so, don't call other page scripts
        if ($('#usersTable').length > 0) {
            console.log('Users page initialized');

            // Get base URL from global config
            const baseUrl = window.APP_BASE_URL || window.BASE_URL || '';

            // Global variables
            let currentPage = 1;
            let perPage = window.APP_RECORDS_PER_PAGE || 25;
            let currentFilters = {};
            let userTypes = [];
            let adminsFilters = {};
            let attendantsFilters = {};
            let adminsCurrentPage = 1;
            let attendantsCurrentPage = 1;
            let adminsPerPage = perPage;
            let attendantsPerPage = perPage;

            // Listen for global records per page updates
            document.addEventListener('app-records-per-page-updated', function (e) {
                const newPerPage = e.detail.perPage;
                console.log('Users page: Records per page updated to', newPerPage);

                // Update local variables
                perPage = newPerPage;
                if (typeof guestPerPage !== 'undefined') guestPerPage = newPerPage;
                if (typeof adminsPerPage !== 'undefined') adminsPerPage = newPerPage;
                if (typeof attendantsPerPage !== 'undefined') attendantsPerPage = newPerPage;

                // Sync dropdowns
                $('#perPageSelect').val(newPerPage);
                $('#guestPerPageSelect').val(newPerPage);
                $('#adminsPerPageSelect').val(newPerPage);
                $('#attendantsPerPageSelect').val(newPerPage);

                // Reload active tab data
                const activeTab = $('.nav-link.active').attr('data-bs-target');
                if (activeTab === '#subscribers') {
                    currentPage = 1;
                    loadUsers();
                } else if (activeTab === '#admins') {
                    adminsCurrentPage = 1;
                    loadAdmins();
                } else if (activeTab === '#attendants') {
                    attendantsCurrentPage = 1;
                    loadAttendants();
                } else if (activeTab === '#walk-in-guests') {
                    guestCurrentPage = 1;
                    loadWalkInGuests();
                }
            });

            // Initialize shared filters for users
            if (typeof window.initSharedFilters === 'function') {
                window.initSharedFilters('users');
            } else {
                // Initialize filters manually if function doesn't exist
                initFilters();
            }

            // Show export button for users
            $('#sharedExportBtn').css('display', 'block');

            // Initialize
            loadUserTypes();

            // Load staff dropdowns if staff tab is active
            if ($('.nav-link.active').attr('data-bs-target') === '#attendants') {
                populateStaffDropdowns();
            }

            // Load data based on active tab with a small delay
            setTimeout(function () {
                const activeTab = $('.nav-link.active').attr('data-bs-target');
                if (activeTab === '#subscribers') {
                    loadUsers();
                } else if (activeTab === '#admins') {
                    loadAdmins();
                } else if (activeTab === '#attendants') {
                    loadAttendants();
                } else if (activeTab === '#walk-in-guests') {
                    if (typeof loadGuests === 'function') {
                        loadGuests();
                    } else if (typeof loadWalkInGuests === 'function') {
                        loadWalkInGuests();
                    }
                } else {
                    // Default fallback
                    loadUsers();
                }
            }, 100);

            // Initialize password strength listeners
            initUserPasswordStrength();

            // Tab change event listener
            $('button[data-bs-toggle="tab"]').off('shown.bs.tab').on('shown.bs.tab', function (e) {
                const target = $(e.target).attr('data-bs-target');
                updateFiltersForTab(target);

                // Load data for the selected tab
                if (target === '#subscribers') {
                    loadUsers();
                } else if (target === '#admins') {
                    loadAdmins();
                } else if (target === '#attendants') {
                    loadAttendants();
                    // Auto-populate staff dropdowns when switching to attendants tab
                    setTimeout(function () {
                        populateStaffDropdowns();
                    }, 100);
                } else if (target === '#walk-in-guests') {
                    // Check if loadGuests exists (it might be defined later or in different scope)
                    // Based on previous code, guest logic might be separate or named differently.
                    // Checking existing initPageScripts, it called loadWalkInGuests() in one place.
                    if (typeof loadGuests === 'function') {
                        loadGuests();
                    } else if (typeof loadWalkInGuests === 'function') {
                        loadWalkInGuests();
                    }
                }
            });

            // ====================================
            // INITIALIZE FILTERS
            // ====================================
            function initFilters() {
                const filterCard = $('#sharedFiltersCard');
                if (!filterCard.length) {
                    return;
                }

                // Show export button for users
                $('#sharedExportBtn').css('display', 'block');

                // Set search placeholder
                $('#sharedSearchInput').attr('placeholder', 'Search by ID, name, email...');

                // Initialize filters for the active tab (Subscribers by default)
                updateFiltersForTab('#subscribers');
            }

            // Update filters based on active tab
            function updateFiltersForTab(tabId) {
                // Hide all filter fields first
                $('.filter-field').not('.filter-search').hide();

                // Update stats labels based on tab can be done here
                updateStatsLabels(tabId);

                if (tabId === '#subscribers') {
                    $('.filter-status, .filter-online-status').show();
                } else if (tabId === '#admins') {
                    // Admins can be filtered by status and online status
                    $('.filter-status, .filter-online-status').show();
                } else if (tabId === '#attendants') {
                    // Attendants can be filtered by area, status, and online status
                    $('.filter-area, .filter-status, .filter-online-status').show();
                } else if (tabId === '#walk-in-guests') {
                    $('.filter-attendant, .filter-vehicle-type, .filter-date-range').show();
                }

                // Reset filter values when switching
                // We should probably clear values to avoid confusion, but sometimes persistent filters are nice.
                // For now, let's keep them if they make sense, or reset if they don't.
                // The current implementation resets them via the 'change' trigger on tab switch in some versions,
                // but let's be explicit if needed.
            }



            // Update stats labels for the active tab
            function updateStatsLabels(tabId) {
                if (tabId === '#subscribers') {
                    $('#labelTotalUsers').text('Total Subscribers');
                    $('#descTotalUsers').html('<i class="fas fa-users me-2"></i>Registered');
                    $('#labelOnlineUsers').text('Online');
                    $('#descOnlineUsers').html('<i class="fas fa-circle me-2"></i>Login Status');
                    $('#labelActiveUsers').text('Active');
                    $('#descActiveUsers').html('<i class="fas fa-check-circle me-2"></i>Active');
                    $('#labelInactiveUsers').text('Inactive');
                    $('#descInactiveUsers').html('<i class="fas fa-pause-circle me-2"></i>Inactive');
                } else if (tabId === '#admins') {
                    $('#labelTotalUsers').text('Total Admins');
                    $('#descTotalUsers').html('<i class="fas fa-user-shield me-2"></i>System Administrators');
                    $('#labelOnlineUsers').text('Online');
                    $('#descOnlineUsers').html('<i class="fas fa-circle me-2"></i>Login Status');
                    $('#labelActiveUsers').text('Active');
                    $('#descActiveUsers').html('<i class="fas fa-check-circle me-2"></i>Account Active');
                    $('#labelInactiveUsers').text('Inactive');
                    $('#descInactiveUsers').html('<i class="fas fa-pause-circle me-2"></i>Account Inactive');
                } else if (tabId === '#attendants') {
                    $('#labelTotalUsers').text('Total Attendants');
                    $('#descTotalUsers').html('<i class="fas fa-user-tie me-2"></i>Staff Members');
                    $('#labelOnlineUsers').text('Online');
                    $('#descOnlineUsers').html('<i class="fas fa-circle me-2"></i>Login Status');
                    $('#labelActiveUsers').text('Active');
                    $('#descActiveUsers').html('<i class="fas fa-check-circle me-2"></i>Account Active');
                    $('#labelInactiveUsers').text('Assigned');
                    $('#descInactiveUsers').html('<i class="fas fa-map-marker-alt me-2"></i>Assigned to Area');
                } else if (tabId === '#walk-in-guests') {
                    $('#labelTotalUsers').text('Total Guests');
                    $('#descTotalUsers').html('<i class="fas fa-user-clock me-2"></i>All Bookings');
                    $('#labelOnlineUsers').text('Pending');
                    $('#descOnlineUsers').html('<i class="fas fa-clock me-2"></i>Reservation Pending');
                    $('#labelActiveUsers').text('Active');
                    $('#descActiveUsers').html('<i class="fas fa-car me-2"></i>Currently Parked');
                    $('#labelInactiveUsers').text('Completed');
                    $('#descInactiveUsers').html('<i class="fas fa-check-double me-2"></i>Completed/Cancelled');
                }
            }

            // ====================================
            // LOAD STAFF DROPDOWNS
            // ====================================
            function populateStaffDropdowns() {
                console.log('Populating staff dropdowns...');

                // First, populate with hardcoded data as fallback
                const hardcodedRoles = [
                    { user_type_id: 2, user_type_name: 'Attendant' },
                    { user_type_id: 3, user_type_name: 'Admin' }
                ];

                const hardcodedAreas = [
                    { parking_area_id: 1, parking_area_name: 'Main Campus' },
                    { parking_area_id: 2, parking_area_name: 'FPA' },
                    { parking_area_id: 4, parking_area_name: 'Hyper Mart' }
                ];

                // Populate roles dropdown
                let roleOptions = '<option value="">All Roles</option>';
                hardcodedRoles.forEach(type => {
                    roleOptions += `<option value="${type.user_type_id}">${type.user_type_name}</option>`;
                });
                $('#sharedFilterRole').html(roleOptions);
                console.log('Staff roles populated with hardcoded data');

                // Populate areas dropdown
                let areaOptions = '<option value="">All Areas</option>';
                hardcodedAreas.forEach(area => {
                    areaOptions += `<option value="${area.parking_area_id}">${area.parking_area_name}</option>`;
                });
                $('#sharedFilterArea').html(areaOptions);
                console.log('Parking areas populated with hardcoded data');

                // Try to load from server (will update if successful)
                tryLoadFromServer();
            }

            function tryLoadFromServer() {
                console.log('Base URL:', baseUrl);

                // Load user types (Roles)
                const roleUrl = `${baseUrl}users/getStaffUserTypes`;
                console.log('Calling role URL:', roleUrl);

                $.ajax({
                    url: roleUrl,
                    method: 'GET',
                    timeout: 5000, // 5 second timeout
                    success: function (response) {
                        console.log('Staff roles response:', response);
                        if (response.success && response.data && response.data.length > 0) {
                            let options = '<option value="">All Roles</option>';
                            response.data.forEach(type => {
                                options += `<option value="${type.user_type_id}">${type.user_type_name}</option>`;
                            });
                            $('#sharedFilterRole').html(options);
                            console.log('Staff roles updated from server');
                        }
                    },
                    error: function (xhr, status, error) {
                        console.error('Server call failed, using hardcoded data');
                    }
                });

                // Load parking areas
                const areaUrl = `${baseUrl}users/getParkingAreas`;
                console.log('Calling area URL:', areaUrl);

                $.ajax({
                    url: areaUrl,
                    method: 'GET',
                    timeout: 5000, // 5 second timeout
                    success: function (response) {
                        console.log('Parking areas response:', response);
                        if (response.success && response.data && response.data.length > 0) {
                            let options = '<option value="">All Areas</option>';
                            response.data.forEach(area => {
                                options += `<option value="${area.parking_area_id}">${area.parking_area_name}</option>`;
                            });
                            $('#sharedFilterArea').html(options);
                            console.log('Parking areas updated from server');
                        }
                    },
                    error: function (xhr, status, error) {
                        console.error('Server call failed, using hardcoded data');
                    }
                });
            }

            // Add simple manual trigger (call from console: window.populateStaffDropdowns())
            window.populateStaffDropdowns = function () {
                // Populate with hardcoded data immediately
                const hardcodedRoles = [
                    { user_type_id: 2, user_type_name: 'Attendant' },
                    { user_type_id: 3, user_type_name: 'Admin' }
                ];

                const hardcodedAreas = [
                    { parking_area_id: 1, parking_area_name: 'Main Campus' },
                    { parking_area_id: 2, parking_area_name: 'FPA' },
                    { parking_area_id: 4, parking_area_name: 'Hyper Mart' }
                ];

                // Populate roles dropdown
                let roleOptions = '<option value="">All Roles</option>';
                hardcodedRoles.forEach(type => {
                    roleOptions += `<option value="${type.user_type_id}">${type.user_type_name}</option>`;
                });
                $('#sharedFilterRole').html(roleOptions);

                // Populate areas dropdown
                let areaOptions = '<option value="">All Areas</option>';
                hardcodedAreas.forEach(area => {
                    areaOptions += `<option value="${area.parking_area_id}">${area.parking_area_name}</option>`;
                });
                $('#sharedFilterArea').html(areaOptions);

                return 'Dropdowns populated successfully!';
            };

            // ====================================
            // LOAD USER TYPES (for dropdown)
            // ====================================
            function loadUserTypes() {
                $.ajax({
                    url: `${baseUrl}users/getUserTypes`,
                    method: 'GET',
                    success: function (response) {
                        if (response.success) {
                            userTypes = response.data;
                            populateUserTypeDropdowns();
                        }
                    }
                });
            }

            // Populate user type dropdowns
            function populateUserTypeDropdowns() {
                let options = '<option value="">Select Type</option>';
                let filterOptions = '<option value="">All Types</option>';

                userTypes.forEach(type => {
                    options += `<option value="${type.user_type_id}">${type.user_type_name}</option>`;
                    filterOptions += `<option value="${type.user_type_id}">${type.user_type_name}</option>`;
                });

                // Update modal dropdown
                $('#userTypeId').html(options);

                // Subscribers page requirement: remove User Type filter
                // Keep the modal user type dropdown intact, but hide/disable the filter UI.
                $('.filter-user-type').hide();
                $('#sharedFilterUserType').html(filterOptions).val('');
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
                const statusSelect = $('#sharedFilterStatus');
                const onlineSelect = $('#sharedFilterOnline');

                const search = searchInput.length ? searchInput.val().trim() : '';
                const status = statusSelect.length ? statusSelect.val() : '';
                const online = onlineSelect.length ? onlineSelect.val() : '';

                // Return true if ANY filter has a value (active filters)
                return !!(search || status || online);
            }

            // Search input handler - remove automatic filtering
            $('#sharedSearchInput').off('input').on('input', function () {
                updateFilterVisibility();
            });

            // Filter changes - remove automatic filtering
            $('#sharedFilterStatus, #sharedFilterOnline').off('change').on('change', function () {
                updateFilterVisibility();
            });

            // Apply Filter button handler
            $('#sharedApplyFiltersBtn').off('click').on('click', function () {
                const search = $('#sharedSearchInput').val().trim();
                const status = $('#sharedFilterStatus').val();
                const online = $('#sharedFilterOnline').val();

                currentFilters.search = search;
                currentFilters.status = status;
                currentFilters.is_online = online;

                // Load data based on active tab
                const activeTab = $('.nav-link.active').attr('data-bs-target');
                if (activeTab === '#subscribers') {
                    loadUsers();
                } else if (activeTab === '#admins') {
                    loadAdmins();
                } else if (activeTab === '#attendants') {
                    loadAttendants();
                } else if (activeTab === '#walk-in-guests') {
                    if (typeof loadGuests === 'function') loadGuests();
                }

                updateFilterVisibility();
            });

            // Update filter visibility on document ready
            $(document).ready(function () {
                setTimeout(updateFilterVisibility, 200);
            });

            // Export to CSV
            $('#exportUsersBtn').off('click').on('click', function () {
                const params = buildExportParams();
                const exportUrl = baseUrl + 'users/export' + (params ? '?' + params : '');
                window.location.href = exportUrl;
            });

            // Build export parameters from current filters
            function buildExportParams() {
                const params = [];
                const search = $('#sharedSearchInput').val().trim();
                const status = $('#sharedFilterStatus').val();
                const online = $('#sharedFilterOnline').val();

                if (search) params.push('search=' + encodeURIComponent(search));
                if (status) params.push('status=' + encodeURIComponent(status));
                if (online) params.push('is_online=' + encodeURIComponent(online));

                return params.join('&');
            }

            // ====================================
            // LOAD USERS TABLE
            // ====================================
            function loadUsers() {
                const params = new URLSearchParams({
                    page: currentPage,
                    per_page: perPage,
                    ...currentFilters
                });

                $.ajax({
                    url: `${baseUrl}users/list?${params}`,
                    method: 'GET',
                    beforeSend: function () {
                        $('#userTableBody').html(`
                        <tr>
                            <td colspan="7" class="text-center py-5">
                                <div class="spinner-border text-primary" role="status"></div>
                                <p class="mt-2 text-muted">Loading users...</p>
                            </td>
                        </tr>
                    `);
                    },
                    success: function (response) {
                        if (response.success) {
                            // Store all data for dynamic filtering
                            allUsersData = response.data;
                            renderUsersTable(response.data);
                            renderPagination(response.pagination);
                            if (response.stats) {
                                updateStats(response.stats, 'subscribers');
                            }
                        }
                    },
                    error: function () {
                        $('#userTableBody').html(`
                        <tr>
                            <td colspan="7" class="text-center py-5 text-danger">
                                <i class="fas fa-exclamation-triangle fa-3x mb-3"></i>
                                <p>Error loading users. Please try again.</p>
                            </td>
                        </tr>
                    `);
                    }
                });
            }

            // Make loadUsers available globally for refresh
            window.refreshCurrentPage = loadUsers;

            // ====================================
            // RENDER USERS TABLE
            // ====================================
            function renderUsersTable(users) {
                let html = '';

                if (!users || users.length === 0) {
                    html = `
                    <tr>
                        <td colspan="7" class="text-center py-5">
                            <i class="fas fa-users fa-3x text-muted mb-3"></i>
                            <p class="text-muted">No users found</p>
                        </td>
                    </tr>
                `;
                } else {
                    users.forEach((user, index) => {
                        const statusBadge = getStatusBadge(user.status);
                        const onlineBadge = (user.is_online == 1 || user.is_online === true) ?
                            '<span class="badge bg-success"><i class="fas fa-circle"></i> Online</span>' :
                            '<span class="badge bg-secondary"><i class="fas fa-circle"></i> Offline</span>';

                        // Store user data as JSON for easy access
                        const userData = JSON.stringify(user).replace(/"/g, '&quot;');
                        const startIdx = (currentPage - 1) * perPage + 1;

                        html += `
                        <tr data-user-id="${user.user_id}">
                            <td>#${startIdx + index}</td>
                            <td>
                                <strong>${escapeHtml(user.first_name)} ${escapeHtml(user.last_name)}</strong>
                                ${user.external_user_id ? `<br><small class="text-muted">${escapeHtml(user.external_user_id)}</small>` : ''}
                            </td>
                            <td>${escapeHtml(user.email)}</td>
                            <td>
                                ${parseFloat(user.hour_balance || 0) > 0
                                ? `<span class="badge bg-info">${user.hour_balance} hrs</span>`
                                : `<span class="badge bg-secondary opacity-75">Unused</span>`}
                            </td>
                            <td>${statusBadge}</td>
                            <td>${onlineBadge}</td>
                            <td>
                                <button class="btn btn-sm btn-outline-primary view-user-btn" 
                                        data-id="${user.user_id}" 
                                        title="View"
                                        style="border-color: #800000; color: #800000;">
                                    <i class="fas fa-eye"></i>
                                </button>
                                <button class="btn btn-sm btn-outline-secondary edit-user-btn" 
                                        data-id="${user.user_id}"
                                        data-user='${userData}'
                                        title="Edit"
                                        style="border-color: #6c757d; color: #6c757d;">
                                    <i class="fas fa-pen"></i>
                                </button>
                                <button class="btn btn-sm btn-outline-danger delete-user-btn" 
                                        data-id="${user.user_id}" 
                                        data-name="${escapeHtml(user.first_name + ' ' + user.last_name)}" 
                                        data-balance="${user.hour_balance || 0}"
                                        title="Delete"
                                        style="border-color: #dc3545; color: #dc3545;">
                                    <i class="fas fa-trash"></i>
                                </button>
                            </td>
                        </tr>
                    `;
                    });
                }

                $('#userTableBody').html(html);
            }

            // Get status badge
            function getStatusBadge(status) {
                const badges = {
                    'active': '<span class="badge bg-success">Active</span>',
                    'inactive': '<span class="badge bg-secondary">Inactive</span>',
                    'suspended': '<span class="badge bg-danger">Suspended</span>'
                };
                return badges[status] || '<span class="badge bg-secondary">Unknown</span>';
            }

            // Escape HTML
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
            // RENDER PAGINATION
            // ====================================
            function renderPagination(pagination) {
                const { current_page, per_page, total, total_pages, showing_from, showing_to } = pagination;

                currentPage = parseInt(current_page, 10);
                perPage = parseInt(per_page, 10);
                $('#perPageSelect').val(perPage);

                $('#paginationInfo').html(`Showing ${showing_from || 0} to ${showing_to || 0} of ${total} users`);
                $('#tableInfo').html(`${total} total users`);

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

            // Update stats dynamically based on type
            function updateStats(stats, type = 'subscribers') {
                // Ensure stats object exists
                if (!stats) {
                    stats = { total: 0, active: 0, online: 0, inactive: 0 };
                }

                let config;

                // Config for Subscribers
                if (type === 'subscribers') {
                    config = {
                        total: { label: 'Total Subscribers', icon: 'fa-users', value: stats.total || 0, desc: 'Registered' },
                        card2: { label: 'Online', icon: 'fa-circle', value: stats.online || 0, desc: 'Login Status' },
                        card3: { label: 'Active', icon: 'fa-check-circle', value: stats.active || 0, desc: 'Active' },
                        card4: { label: 'Inactive', icon: 'fa-pause-circle', value: stats.inactive || 0, desc: 'Inactive' }
                    };
                }
                // Config for Admins
                else if (type === 'admins') {
                    config = {
                        total: { label: 'Total Admins', icon: 'fa-user-shield', value: stats.total || 0, desc: 'Administrators' },
                        card2: { label: 'Online', icon: 'fa-circle', value: stats.online || 0, desc: 'Login Status' },
                        card3: { label: 'Active', icon: 'fa-check-circle', value: stats.active || 0, desc: 'Active Accounts' },
                        card4: { label: 'Inactive', icon: 'fa-pause-circle', value: stats.inactive || 0, desc: 'Inactive Accounts' }
                    };
                }
                // Config for Attendants
                else if (type === 'attendants') {
                    config = {
                        total: { label: 'Total Attendants', icon: 'fa-user-tie', value: stats.total || 0, desc: 'Staff Members' },
                        card2: { label: 'Online', icon: 'fa-circle', value: stats.online || 0, desc: 'Login Status' },
                        card3: { label: 'Active', icon: 'fa-check-circle', value: stats.active || 0, desc: 'Active Accounts' },
                        card4: { label: 'Assigned', icon: 'fa-map-marker-alt', value: stats.assigned || 0, desc: 'Assigned to Area' }
                    };
                }
                // Config for Walk-in Guests
                else if (type === 'guests') {
                    config = {
                        total: { label: 'Total Guests', icon: 'fa-user-clock', value: stats.total || 0, desc: 'All Time' },
                        card2: { label: 'Today\'s Walk-ins', icon: 'fa-calendar-day', value: stats.today || 0, desc: 'New Today' },
                        card3: { label: 'Occupied Spots', icon: 'fa-car', value: stats.parked || 0, desc: 'Currently Parked' },
                        card4: { label: 'Departed', icon: 'fa-history', value: stats.completed || 0, desc: 'Has Left' }
                    };
                } else {
                    return; // No config matches
                }

                // Helper to update a card
                const updateCard = (idPrefix, data) => {
                    $(`#label${idPrefix}`).text(data.label);
                    $(`#stat${idPrefix}`).text(data.value).addClass('text-white');
                    $(`#desc${idPrefix}`).html(`<i class="fas ${data.icon} me-2"></i>${data.desc}`);

                    // Update icon container if possible (requires finding the .stats-icon div)
                    const cardBody = $(`#stat${idPrefix}`).closest('.card-body');
                    cardBody.find('.stats-icon i').attr('class', `fas ${data.icon}`);
                };

                updateCard('TotalUsers', config.total);
                updateCard('OnlineUsers', config.card2);
                updateCard('ActiveUsers', config.card3);
                updateCard('InactiveUsers', config.card4);
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
                    loadUsers();
                }
                return false;
            });

            // Per page change
            $('#perPageSelect').off('change').on('change', function () {
                perPage = parseInt($(this).val());
                currentPage = 1;
                loadUsers();
            });

            // ====================================
            // DYNAMIC FILTERING (No Reload)
            // ====================================

            // Store all users data for filtering
            let allUsersData = [];

            // Dynamic filter function
            function applyDynamicFilters() {
                const search = $('#sharedSearchInput').val().trim().toLowerCase();
                const status = $('#sharedFilterStatus').val();
                const online = $('#sharedFilterOnline').val();

                const filteredUsers = allUsersData.filter(user => {
                    // Search filter (case-insensitive)
                    if (search) {
                        const searchLower = search.toLowerCase(); // Convert search to lowercase

                        // Check for exact ID match first (prioritize exact user_id match)
                        if (search.match(/^\d+$/) && user.user_id.toString() === search) {
                            return true; // Exact numeric ID match - include immediately
                        }

                        // Check for exact external_user_id match (case-insensitive)
                        if (user.external_user_id && user.external_user_id.toLowerCase() === searchLower) {
                            return true; // Exact external ID match - include immediately
                        }

                        // If not exact ID match, then search in other fields (names, email, etc.)
                        const firstName = (user.first_name || '').toLowerCase();
                        const lastName = (user.last_name || '').toLowerCase();
                        const fullName = `${firstName} ${lastName}`.trim();
                        const fullNameReverse = `${lastName} ${firstName}`.trim(); // Support "Last First" search
                        const email = (user.email || '').toLowerCase();
                        const userType = (user.user_type_name || '').toLowerCase();
                        const externalId = (user.external_user_id || '').toLowerCase();

                        // For numeric searches, don't match partial IDs in other fields
                        if (search.match(/^\d+$/)) {
                            // Only allow partial numeric matches in external_user_id
                            if (!externalId.includes(searchLower)) {
                                return false; // No partial numeric ID matching in other fields
                            }
                        }

                        // For text searches, check all fields
                        const matchesFirstName = firstName.includes(searchLower);
                        const matchesLastName = lastName.includes(searchLower);
                        const matchesFullName = fullName.includes(searchLower);
                        const matchesFullNameReverse = fullNameReverse.includes(searchLower);
                        const matchesEmail = email.includes(searchLower);
                        const matchesUserType = userType.includes(searchLower);
                        const matchesExternalId = externalId.includes(searchLower);

                        if (!matchesFirstName && !matchesLastName && !matchesFullName &&
                            !matchesFullNameReverse && !matchesEmail && !matchesUserType && !matchesExternalId) {
                            return false;
                        }
                    }

                    // Status filter
                    if (status && user.status !== status) return false;

                    // Online status filter
                    if (online !== '' && ((online === '1' && user.is_online != 1) || (online === '0' && user.is_online == 1))) return false;

                    return true;
                });

                // Re-render table with filtered data
                renderUsersTable(filteredUsers);

                // Update pagination info
                const total = filteredUsers.length;
                $('#paginationInfo').html(`Showing ${Math.min(1, total)} to ${Math.min(total, total)} of ${total} users`);
                $('#tableInfo').html(`${total} total users`);

                // Hide pagination controls for filtered results
                $('#paginationControls').html('');
            }

            // ====================================
            // FILTER VISIBILITY HELPER
            // ====================================

            // Show/hide filter action buttons based on filter state
            function updateFilterVisibility() {
                const activeTab = $('.nav-tabs .nav-link.active').attr('data-bs-target');

                // Always show filters for these tabs to allow resetting to "All"
                const filterableTabs = ['#subscribers', '#walk-in-guests', '#admins', '#attendants'];
                const shouldShow = filterableTabs.includes(activeTab);

                // Show/hide entire filter actions container
                if (shouldShow) {
                    $('#filterActionsContainer').removeClass('filter-actions-hidden').addClass('filter-actions-visible');
                } else {
                    $('#filterActionsContainer').removeClass('filter-actions-visible').addClass('filter-actions-hidden');
                }
            }

            // Add input event listeners to show filter buttons (but NOT trigger requests)
            $('#sharedSearchInput').off('input').on('input', function () {
                updateFilterVisibility();
            });

            $('#sharedFilterStatus, #sharedFilterOnline, #sharedFilterRole, #sharedFilterArea, #sharedFilterAttendant, #sharedFilterVehicleType, #sharedFilterDateRange').off('change').on('change', function () {
                updateFilterVisibility();
            });

            // ====================================
            // EVENT HANDLERS - FILTERS (Manual Apply)
            // ====================================

            // Apply Filter Button - Triggers filter application for active tab
            $('#sharedApplyFiltersBtn').off('click').on('click', function () {
                const activeTab = $('.nav-tabs .nav-link.active').attr('data-bs-target');

                // Collect filter values
                const searchValue = $('#sharedSearchInput').val();
                const statusValue = $('#sharedFilterStatus').val();
                const onlineValue = $('#sharedFilterOnline').val();
                const roleValue = $('#sharedFilterRole').val();
                const areaValue = $('#sharedFilterArea').val();
                const attendantValue = $('#sharedFilterAttendant').val();
                const vehicleTypeValue = $('#sharedFilterVehicleType').val();
                const dateRangeValue = $('#sharedFilterDateRange').val();

                if (activeTab === '#subscribers') {
                    currentFilters.search = searchValue;
                    currentFilters.status = statusValue;
                    currentFilters.is_online = onlineValue;
                    currentPage = 1;
                    applyDynamicFilters();
                } else if (activeTab === '#walk-in-guests') {
                    guestFilters.search = searchValue;
                    guestFilters.attendant_id = attendantValue;
                    guestFilters.vehicle_type = vehicleTypeValue; // Fixed: use vehicle_type instead of vehicle_type_id
                    guestFilters.date_range = dateRangeValue;
                    guestCurrentPage = 1;
                    loadWalkInGuests();
                } else if (activeTab === '#admins') {
                    adminsFilters.search = searchValue;
                    adminsFilters.status = statusValue;
                    adminsFilters.is_online = onlineValue;
                    adminsCurrentPage = 1;
                    loadAdmins();
                } else if (activeTab === '#attendants') {
                    attendantsFilters.search = searchValue;
                    attendantsFilters.status = statusValue;
                    attendantsFilters.is_online = onlineValue;
                    attendantsFilters.assigned_area_id = areaValue;
                    attendantsCurrentPage = 1;
                    loadAttendants();
                }

                updateFilterVisibility();
            });

            // Clear filters
            $('#sharedClearFiltersBtn').off('click').on('click', function () {
                // Reset all inputs
                $('#sharedSearchInput').val('');
                $('#sharedFilterStatus, #sharedFilterOnline, #sharedFilterRole, #sharedFilterArea, #sharedFilterAttendant, #sharedFilterVehicleType, #sharedFilterDateRange').val('');

                const activeTab = $('.nav-tabs .nav-link.active').attr('data-bs-target');

                if (activeTab === '#subscribers') {
                    currentFilters = {};
                    currentPage = 1;
                    // Use dynamic filtering instead of reload for subscribers
                    if (allUsersData.length > 0) {
                        loadUsers(); // Reload to be safe and simple
                    } else {
                        loadUsers();
                    }
                } else if (activeTab === '#walk-in-guests') {
                    guestFilters = {};
                    guestCurrentPage = 1;
                    loadWalkInGuests();
                } else if (activeTab === '#admins') {
                    adminsFilters = {};
                    adminsCurrentPage = 1;
                    loadAdmins();
                } else if (activeTab === '#attendants') {
                    attendantsFilters = {};
                    attendantsCurrentPage = 1;
                    loadAttendants();
                }

                updateFilterVisibility();
            });

            // Refresh
            $('#sharedRefreshBtn').off('click').on('click', function () {
                const activeTab = $('.nav-tabs .nav-link.active').attr('data-bs-target');

                if (activeTab === '#subscribers') {
                    loadUsers();
                } else if (activeTab === '#walk-in-guests') {
                    loadWalkInGuests();
                } else if (activeTab === '#admins') {
                    loadAdmins();
                } else if (activeTab === '#attendants') {
                    loadAttendants();
                }
            });

            // Build export parameters (Legacy helper, mostly handled in individual exports now)
            function buildExportParams() {
                return ''; // Not used directly anymore
            }

            // ====================================
            // ADD USER
            // ====================================
            $('#addUserBtn').off('click').on('click', function () {
                // Blur any active element to prevent aria-hidden warnings
                if (document.activeElement && document.activeElement.blur) {
                    document.activeElement.blur();
                }

                clearValidationErrors();
                resetUserPasswordStrength();

                // Reset form
                $('#crudForm')[0].reset();
                $('#crudEntityId').val('');
                $('#crudAction').val('add');
                $('#crudEntityType').val('users');

                // Reset confirmation button and footer
                resetConfirmationButton();
                $('#crudConfirmFooter').hide();
                $('#crudNormalFooter').show();

                // Set modal mode
                $('#crudFormModal').removeClass('mode-edit').addClass('mode-add');

                // Update title
                $('#crudModalIcon').removeClass().addClass('fas fa-user-plus me-2');
                $('#crudModalTitleText').text('Add New User');
                $('#crudSubmitText').text('Add');

                // Hide all entity fields, show only users
                $('.entity-fields').hide();
                $('.fields-users').show();

                // Show modal
                const bsModal = bootstrap.Modal.getOrCreateInstance($('#crudFormModal')[0], {
                    backdrop: 'static',
                    keyboard: false,
                    focus: false  // Prevent auto-focus to avoid aria-hidden issues
                });
                bsModal.show();

                // Focus on first field after modal is shown
                setTimeout(() => {
                    $('#userFirstName').focus();
                }, 500);
            });

            // ====================================
            // VIEW USER
            // ====================================
            $(document).off('click', '.view-user-btn').on('click', '.view-user-btn', function () {
                const userId = $(this).data('id');

                // Fetch user data and show in view modal
                $.ajax({
                    url: `${baseUrl}users/get/${userId}`,
                    method: 'GET',
                    success: function (response) {
                        if (response.success) {
                            openViewModal(userId, response.data);
                        }
                    }
                });
            });

            // ====================================
            // OPEN VIEW MODAL
            // ====================================
            window.openViewModal = function (userId, userData) {
                const modal = $('#viewDetailsModal');

                // Blur any active element to prevent aria-hidden warnings
                if (document.activeElement && document.activeElement.blur) {
                    document.activeElement.blur();
                }

                // Store data for edit button
                modal.data('user-id', userId);
                modal.data('user-data', userData);

                // Update title
                $('#viewModalTitleText').text('User Details');

                // Hide all view content, show loading
                $('.view-content').hide();
                $('#viewDetailsLoading').show();

                // Show modal
                const bsModal = bootstrap.Modal.getOrCreateInstance(modal[0], {
                    backdrop: true,
                    keyboard: true,
                    focus: false  // Prevent auto-focus to avoid aria-hidden issues
                });
                bsModal.show();

                // Display user data
                setTimeout(function () {
                    displayUserViewData(userData);
                }, 300);
            };

            // ====================================
            // DISPLAY USER VIEW DATA
            // ====================================
            function displayUserViewData(user) {
                $('#viewDetailsLoading').hide();
                $('.view-users').show();

                // Format dates
                const createdDate = user.created_at ? new Date(user.created_at).toLocaleDateString('en-US', {
                    year: 'numeric',
                    month: 'long',
                    day: 'numeric'
                }) : '-';

                const lastActivity = user.last_activity_at ? new Date(user.last_activity_at).toLocaleDateString('en-US', {
                    year: 'numeric',
                    month: 'long',
                    day: 'numeric',
                    hour: '2-digit',
                    minute: '2-digit'
                }) : 'Never';

                // Set avatar
                const firstName = user.first_name || 'U';
                const firstLetter = firstName.charAt(0).toUpperCase();
                const hasProfilePic = user.profile_picture && user.profile_picture !== '';
                const avatarSrc = hasProfilePic
                    ? `${baseUrl}uploads/profiles/${user.profile_picture}`
                    : `data:image/svg+xml;utf8,<svg xmlns="http://www.w3.org/2000/svg" width="120" height="120" viewBox="0 0 120 120"><rect width="120" height="120" fill="%23800000"/><text x="50%" y="50%" dominant-baseline="middle" text-anchor="middle" font-family="Arial, sans-serif" font-size="50" fill="%23ffffff">${firstLetter}</text></svg>`;

                // Update fields
                $('#viewUserAvatar').attr('src', avatarSrc);
                $('#viewUserFullName').text((user.first_name || '') + ' ' + (user.last_name || ''));
                $('#viewUserId').text(user.user_id || '-');
                $('#viewUserEmail').text(user.email || '-');
                $('#viewUserType').text(user.user_type_name || 'Subscriber');
                $('#viewUserHourBalance').text(`${user.hour_balance || 0} hrs`);

                // Online status
                const onlineStatus = (user.is_online == 1 || user.is_online === true)
                    ? '<span class="badge bg-success"><i class="fas fa-circle"></i> Online</span>'
                    : '<span class="badge bg-secondary"><i class="fas fa-circle"></i> Offline</span>';
                $('#viewUserOnline').html(onlineStatus);

                // Status badge
                const statusBadge = getStatusBadge(user.status);
                $('#viewUserStatusBadge').html(statusBadge);

                $('#viewUserCreatedAt').text(createdDate);
                $('#viewUserLastActivity').text(lastActivity);
            }

            // Edit from view modal
            $('#viewEditBtn').off('click.users').on('click.users', function (e) {
                const modal = $('#viewDetailsModal');
                const userId = modal.data('user-id');
                const userData = modal.data('user-data');

                // Only handle if it's users - check if user data exists
                if (!userId && !userData) {
                    return; // Let subscriptions handler take over
                }

                e.stopImmediatePropagation();
                e.preventDefault();

                // Blur active element before hiding modal
                if (document.activeElement) {
                    document.activeElement.blur();
                }

                // Close view modal
                bootstrap.Modal.getInstance(modal[0]).hide();

                // Wait a bit then open edit modal
                setTimeout(function () {
                    if (userData) {
                        openEditUserModal(userId, userData);
                    } else {
                        // Fetch if not available
                        $.ajax({
                            url: `${baseUrl}users/get/${userId}`,
                            method: 'GET',
                            success: function (response) {
                                if (response.success) {
                                    openEditUserModal(userId, response.data);
                                }
                            }
                        });
                    }
                }, 300);
            });

            // ====================================
            // EDIT USER
            // ====================================
            $(document).off('click', '.edit-user-btn').on('click', '.edit-user-btn', function () {
                // Blur any active element to prevent aria-hidden warnings
                if (document.activeElement && document.activeElement.blur) {
                    document.activeElement.blur();
                }

                clearValidationErrors();

                const userId = $(this).data('id');
                const userData = $(this).data('user');

                // If we have the data already, use it
                if (userData) {
                    openEditUserModal(userId, userData);
                } else {
                    // Fetch from server
                    $.ajax({
                        url: `${baseUrl}users/get/${userId}`,
                        method: 'GET',
                        success: function (response) {
                            if (response.success) {
                                openEditUserModal(userId, response.data);
                            }
                        }
                    });
                }
            });


            // ====================================
            // DELETE USER - Using Modal
            // ====================================
            $(document).off('click', '.delete-user-btn').on('click', '.delete-user-btn', function () {
                const userId = $(this).data('id');
                const userName = $(this).data('name');
                const balance = parseFloat($(this).data('balance') || 0);

                if (balance > 0) {
                    showSuccessModal(
                        'Deletion Blocked',
                        `Subscriber "${userName}" cannot be deleted because they still have an active balance of ${balance} hrs. Please exhaust or refund the balance first.`
                    );
                    return;
                }

                // Open delete confirmation modal
                openDeleteModal(userId, userName, 'users');
            });

            // window.openDeleteModal moved to scripts.php

            // ====================================
            // TABLE SORTING FUNCTIONALITY
            // ====================================

            // Initialize sorting for users table
            function initializeUserTableSorting() {
                let sortOrder = {}; // Store sort order for each column

                $('#usersTable th.sortable').off('click').on('click', function () {
                    const $th = $(this);
                    const column = $th.data('column');
                    const $table = $('#usersTable');
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
            initializeUserTableSorting();

            // ====================================
            // DYNAMIC TABLE FUNCTIONS
            // ====================================

            // Add user to table dynamically
            function addUserToTable(userData) {
                const statusBadge = getStatusBadge(userData.status);
                const onlineBadge = (userData.is_online == 1 || userData.is_online === true) ?
                    '<span class="badge bg-success"><i class="fas fa-circle"></i> Online</span>' :
                    '<span class="badge bg-secondary"><i class="fas fa-circle"></i> Offline</span>';

                // Store user data as JSON for easy access
                const userDataJson = JSON.stringify(userData).replace(/"/g, '&quot;');

                const userRow = `
                <tr data-user-id="${userData.user_id}">
                    <td>#</td>
                    <td>
                        <strong>${escapeHtml(userData.first_name)} ${escapeHtml(userData.last_name)}</strong>
                        ${userData.external_user_id ? `<br><small class="text-muted">${escapeHtml(userData.external_user_id)}</small>` : ''}
                    </td>
                    <td>${escapeHtml(userData.email)}</td>
                    <td>
                        ${parseFloat(userData.hour_balance || 0) > 0
                        ? `<span class="badge bg-info">${userData.hour_balance} hrs</span>`
                        : `<span class="badge bg-secondary opacity-75">Unused</span>`}
                    </td>
                    <td>${statusBadge}</td>
                    <td>${onlineBadge}</td>
                    <td>
                        <button class="btn btn-sm btn-outline-primary view-user-btn" 
                                data-id="${userData.user_id}" 
                                title="View"
                                style="border-color: #800000; color: #800000;">
                            <i class="fas fa-eye"></i>
                        </button>
                        <button class="btn btn-sm btn-outline-secondary edit-user-btn" 
                                data-id="${userData.user_id}"
                                data-user='${userDataJson}'
                                title="Edit"
                                style="border-color: #6c757d; color: #6c757d;">
                            <i class="fas fa-pen"></i>
                        </button>
                        <button class="btn btn-sm btn-outline-danger delete-user-btn" 
                                data-id="${userData.user_id}" 
                                data-name="${escapeHtml(userData.first_name + ' ' + userData.last_name)}" 
                                data-balance="${userData.hour_balance || 0}"
                                title="Delete"
                                style="border-color: #dc3545; color: #dc3545;">
                            <i class="fas fa-trash"></i>
                        </button>
                    </td>
                </tr>
            `;

                // Add at the top for ascending order (newest first)
                $('#userTableBody').prepend(userRow);

                // Recalculate numbering
                recalculateRowNumbers('#userTableBody', 'subscribers');

                // Fade in effect
                $(`#userTableBody tr[data-user-id="${userData.user_id}"]`).hide().fadeIn(500);
            }

            // Update staff in table dynamically
            function updateStaffInTable(userData) {
                console.log('Attempting to update staff row for ID:', userData.user_id);
                // Try both number and string selectors just in case
                let row = $(`#staffTableBody tr[data-user-id="${userData.user_id}"]`);

                if (row.length === 0) {
                    console.warn(`Row for user_id ${userData.user_id} not found in staff table.`);
                    return;
                }

                console.log('Row found, updating columns...');

                if (row.length) {
                    const statusBadge = getStatusBadge(userData.status);

                    // Logic to match renderStaffTable
                    let roleBadge = '';
                    if (userData.user_type_id == 3) {
                        roleBadge = '<span class="badge bg-danger">Admin</span>';
                    } else {
                        // Default to Attendant/Info if not admin
                        roleBadge = '<span class="badge bg-info">Attendant</span>';
                    }

                    const onlineBadge = (userData.is_online == 1 || userData.is_online === true) ?
                        '<span class="badge bg-success"><i class="fas fa-circle"></i> Online</span>' :
                        '<span class="badge bg-secondary"><i class="fas fa-circle"></i> Offline</span>';

                    // Store user data as JSON for easy access
                    const userDataJson = JSON.stringify(userData).replace(/"/g, '&quot;');

                    // Helper to check for value
                    // Match renderStaffTable: Just text or "Not Assigned" (gray), NO BADGE via bg-warning
                    const areaName = userData.parking_area_name || '<span class="text-muted">Not Assigned</span>';

                    // Update columns in correct order
                    // td:eq(0) is ID - PRESERVE SEQUENTIAL NUMBER, DON'T OVERWRITE WITH DB ID
                    // row.find('td:eq(0)').text(userData.user_id || '').addClass('ps-4'); // REMOVED BUGGY LINE

                    // td:eq(1) is Name/Email
                    row.find('td:eq(1)').html(`
                        <strong>${escapeHtml(userData.first_name || '')} ${escapeHtml(userData.last_name || '')}</strong><br>
                        <small class="text-muted">${escapeHtml(userData.email)}</small>
                    `);

                    // td:eq(2) is Role - match renderStaffTable logic
                    row.find('td:eq(2)').html(roleBadge);

                    // td:eq(3) is Assigned Area - NO BADGE
                    row.find('td:eq(3)').html(areaName);

                    // td:eq(4) is Status
                    row.find('td:eq(4)').html(statusBadge);

                    // td:eq(5) is Online
                    row.find('td:eq(5)').html(onlineBadge);

                    // td:eq(6) is Actions - don't modify structure
                    // Just update button data attributes
                    row.find('.edit-staff-btn').data('id', userData.user_id).data('user', userData);
                    row.find('.delete-staff-btn').data('id', userData.user_id).data('name', `${userData.first_name} ${userData.last_name}`);

                    console.log('Staff row updated successfully.');
                }
            }

            // Update user in table dynamically
            function updateUserInTable(userData) {
                console.log('Attempting to update user row for ID:', userData.user_id);
                const row = $(`#userTableBody tr[data-user-id="${userData.user_id}"]`);
                if (row.length) {
                    const statusBadge = getStatusBadge(userData.status);
                    const onlineBadge = (userData.is_online == 1 || userData.is_online === true) ?
                        '<span class="badge bg-success"><i class="fas fa-circle"></i> Online</span>' :
                        '<span class="badge bg-secondary"><i class="fas fa-circle"></i> Offline</span>';

                    // Store user data as JSON for easy access
                    const userDataJson = JSON.stringify(userData).replace(/"/g, '&quot;');

                    // td:eq(0) is ID - PRESERVE SEQUENTIAL NUMBER, DON'T OVERWRITE WITH DB ID
                    // row.find('td:eq(0)').text(userData.user_id); // REMOVED BUGGY LINE

                    row.find('td:eq(1)').html(`
                    <strong>${escapeHtml(userData.first_name)} ${escapeHtml(userData.last_name)}</strong>
                    ${userData.external_user_id ? `<br><small class="text-muted">${escapeHtml(userData.external_user_id)}</small>` : ''}
                `);
                    row.find('td:eq(2)').text(escapeHtml(userData.email));
                    row.find('td:eq(3)').html(
                        parseFloat(userData.hour_balance || 0) > 0
                            ? `<span class="badge bg-info">${userData.hour_balance} hrs</span>`
                            : `<span class="badge bg-secondary opacity-75">Unused</span>`
                    );
                    row.find('td:eq(4)').html(statusBadge);
                    row.find('td:eq(5)').html(onlineBadge);

                    // Update button data attributes
                    row.find('.edit-user-btn').data('id', userData.user_id).data('user', userData);
                    row.find('.delete-user-btn')
                        .data('id', userData.user_id)
                        .data('name', `${userData.first_name} ${userData.last_name}`)
                        .data('balance', userData.hour_balance || 0);

                    console.log('User row updated successfully.');
                }
            }

            // Add admin to table dynamically
            function addAdminToTable(userData) {
                const statusBadge = getStatusBadge(userData.status);
                const roleBadge = '<span class="badge bg-danger">Admin</span>';

                const onlineBadge = (userData.is_online == 1 || userData.is_online === true) ?
                    '<span class="badge bg-success"><i class="fas fa-circle"></i> Online</span>' :
                    '<span class="badge bg-secondary"><i class="fas fa-circle"></i> Offline</span>';

                const assignedArea = '<span class="text-muted">Not Assigned</span>';
                const userDataJson = JSON.stringify(userData).replace(/"/g, '&quot;');

                const adminRow = `
                <tr data-user-id="${userData.user_id}">
                    <td class="ps-4">#</td>
                    <td>
                        <strong>${escapeHtml(userData.first_name)} ${escapeHtml(userData.last_name)}</strong><br>
                        <small class="text-muted">${escapeHtml(userData.email)}</small>
                    </td>
                    <td>${roleBadge}</td>
                    <td>${assignedArea}</td>
                    <td>${statusBadge}</td>
                    <td>${onlineBadge}</td>
                    <td class="text-end pe-4">
                        <button class="btn btn-sm btn-outline-primary view-staff-btn" 
                                data-id="${userData.user_id}" 
                                title="View Details"
                                style="border-color: #800000; color: #800000;">
                            <i class="fas fa-eye"></i>
                        </button>
                        <button class="btn btn-sm btn-outline-secondary edit-staff-btn" 
                                data-id="${userData.user_id}"
                                data-user='${userDataJson}'
                                title="Edit"
                                style="border-color: #6c757d; color: #6c757d;">
                            <i class="fas fa-pen"></i>
                        </button>
                        <button class="btn btn-sm btn-outline-danger delete-staff-btn" 
                                data-id="${userData.user_id}" 
                                data-name="${escapeHtml(userData.first_name + ' ' + userData.last_name)}" 
                                title="Delete"
                                style="border-color: #dc3545; color: #dc3545;">
                            <i class="fas fa-trash"></i>
                        </button>
                    </td>
                </tr>
                `;

                $('#adminsTableBody').prepend(adminRow);
                recalculateRowNumbers('#adminsTableBody', 'admins');
                $(`#adminsTableBody tr[data-user-id="${userData.user_id}"]`).hide().fadeIn(500);
            }

            // Add attendant to table dynamically
            function addAttendantToTable(userData) {
                const statusBadge = getStatusBadge(userData.status);
                const roleBadge = '<span class="badge bg-info">Attendant</span>';

                const onlineBadge = (userData.is_online == 1 || userData.is_online === true) ?
                    '<span class="badge bg-success"><i class="fas fa-circle"></i> Online</span>' :
                    '<span class="badge bg-secondary"><i class="fas fa-circle"></i> Offline</span>';

                const areaName = userData.parking_area_name || '<span class="text-muted">Not Assigned</span>';
                const userDataJson = JSON.stringify(userData).replace(/"/g, '&quot;');

                const attendantRow = `
                <tr data-user-id="${userData.user_id}">
                    <td class="ps-4">#</td>
                    <td>
                        <strong>${escapeHtml(userData.first_name)} ${escapeHtml(userData.last_name)}</strong><br>
                        <small class="text-muted">${escapeHtml(userData.email)}</small>
                    </td>
                    <td>${roleBadge}</td>
                    <td>${areaName}</td>
                    <td>${statusBadge}</td>
                    <td>${onlineBadge}</td>
                    <td class="text-end pe-4">
                        <button class="btn btn-sm btn-outline-primary view-staff-btn" 
                                data-id="${userData.user_id}" 
                                title="View Details"
                                style="border-color: #800000; color: #800000;">
                            <i class="fas fa-eye"></i>
                        </button>
                        <button class="btn btn-sm btn-outline-secondary edit-staff-btn" 
                                data-id="${userData.user_id}"
                                data-user='${userDataJson}'
                                title="Edit"
                                style="border-color: #6c757d; color: #6c757d;">
                            <i class="fas fa-pen"></i>
                        </button>
                        <button class="btn btn-sm btn-outline-danger delete-staff-btn" 
                                data-id="${userData.user_id}" 
                                data-name="${escapeHtml(userData.first_name + ' ' + userData.last_name)}" 
                                title="Delete"
                                style="border-color: #dc3545; color: #dc3545;">
                            <i class="fas fa-trash"></i>
                        </button>
                    </td>
                </tr>
                `;

                $('#attendantsTableBody').prepend(attendantRow);
                recalculateRowNumbers('#attendantsTableBody', 'attendants');
                $(`#attendantsTableBody tr[data-user-id="${userData.user_id}"]`).hide().fadeIn(500);
            }

            // Helper to recalculate row numbers sequentially
            function recalculateRowNumbers(tbodyId, type) {
                let currentPageVal = currentPage;
                let perPageVal = perPage;

                if (type === 'admins') {
                    currentPageVal = window.adminsCurrentPage || 1;
                    perPageVal = window.adminsPerPage || 25;
                } else if (type === 'attendants') {
                    currentPageVal = window.attendantsCurrentPage || 1;
                    perPageVal = window.attendantsPerPage || 25;
                }

                const startIdx = (currentPageVal - 1) * perPageVal + 1;

                $(tbodyId + ' tr').each(function (idx) {
                    // Skip loading or no-data rows
                    if ($(this).find('td[colspan]').length) return;

                    $(this).find('td:first').html('#' + (startIdx + idx));
                });
            }

            // Remove user from table dynamically
            function removeUserFromTable(userId) {
                $(`#userTableBody tr[data-user-id="${userId}"]`).fadeOut(300, function () {
                    $(this).remove();
                    recalculateRowNumbers('#userTableBody', 'subscribers');
                });

                // Check admins table
                $(`#adminsTableBody tr[data-user-id="${userId}"]`).fadeOut(300, function () {
                    $(this).remove();
                    recalculateRowNumbers('#adminsTableBody', 'admins');
                });

                // Check attendants table
                $(`#attendantsTableBody tr[data-user-id="${userId}"]`).fadeOut(300, function () {
                    $(this).remove();
                    recalculateRowNumbers('#attendantsTableBody', 'attendants');
                });
            }

            // showSuccessModal moved to scripts.php

            // ====================================
            // CONFIRM DELETE
            // ====================================
            // Store original if it exists to preserve the chain
            const originalConfirmDelete = window.confirmDelete;

            window.confirmDelete = function () {
                const entity = $('#deleteEntityType').val();

                // Handle different entity types
                if (entity === 'users') {
                    const userId = $('#deleteEntityId').val();
                    const deleteBtn = $('#confirmDeleteBtn');
                    const originalText = deleteBtn.html();

                    deleteBtn.prop('disabled', true).html('<i class="fas fa-spinner fa-spin me-1"></i> Deleting...');

                    ajaxWithCSRF(`${baseUrl}users/delete/${userId}`, {
                        method: 'POST',
                        data: {},
                        success: function (response) {
                            // Blur active element before hiding modal (fixes aria-hidden warning)
                            if (document.activeElement) {
                                document.activeElement.blur();
                            }

                            const deleteConfirmModal = document.getElementById('deleteConfirmModal');
                            let bsModal = bootstrap.Modal.getInstance(deleteConfirmModal);
                            if (bsModal) bsModal.hide();

                            if (response.success) {
                                // Show success modal
                                showSuccessModal('User Deleted Successfully', `User "${$('#deleteEntityLabel').text()}" has been removed from the system.`);
                                // Remove from table dynamically instead of reloading
                                removeUserFromTable(userId);
                            } else {
                                showSuccessModal('Delete Failed', response.message || 'Failed to delete user');
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

                            const errorMsg = xhr.responseJSON?.message || 'Error deleting user. Please try again.';
                            showSuccessModal('Delete Error', errorMsg);
                        },
                        complete: function () {
                            deleteBtn.prop('disabled', false).html(originalText);
                        }
                    });
                } else if (entity === 'parking-section') {
                    // Handle parking section deletion
                    const sectionId = $('#deleteEntityId').val();
                    const deleteBtn = $('#confirmDeleteBtn');
                    const originalText = deleteBtn.html();

                    deleteBtn.prop('disabled', true).html('<i class="fas fa-spinner fa-spin me-1"></i> Deleting...');

                    $.ajax({
                        url: `${baseUrl}parking/areas/sections/delete/${sectionId}`,
                        method: 'POST',
                        success: function (response) {
                            // Blur active element before hiding modal (fixes aria-hidden warning)
                            if (document.activeElement) {
                                document.activeElement.blur();
                            }

                            bootstrap.Modal.getInstance($('#deleteConfirmModal')[0]).hide();

                            if (response.success) {
                                // Show success modal
                                showSuccessModal('Section Deleted Successfully', `Section "${$('#deleteEntityLabel').text()}" has been removed from the system.`);
                                // Update UI via hook (no reload)
                                if (typeof window.onParkingSectionDeleted === 'function') {
                                    window.onParkingSectionDeleted(sectionId);
                                }
                            } else {
                                showSuccessModal('Delete Failed', response.message || 'Failed to delete section');
                            }
                        },
                        error: function (xhr) {
                            // Blur active element before hiding modal
                            if (document.activeElement) {
                                document.activeElement.blur();
                            }

                            bootstrap.Modal.getInstance($('#deleteConfirmModal')[0]).hide();

                            const errorMsg = xhr.responseJSON?.message || 'Error deleting section. Please try again.';
                            showSuccessModal('Delete Error', errorMsg);
                        },
                        complete: function () {
                            deleteBtn.prop('disabled', false).html(originalText);
                        }
                    });
                } else {
                    // Call original handler for other entity types (attendants, subscriptions, etc.)
                    if (originalConfirmDelete && typeof originalConfirmDelete === 'function') {
                        originalConfirmDelete();
                    }
                }
            };

            // Attach confirmDelete to the button click
            $('#confirmDeleteBtn').off('click').on('click', function () {
                confirmDelete();
            });

            // ====================================
            // SUBMIT FORM
            // ====================================
            $('#crudSubmitBtn').off('click.users').on('click.users', function (e) {
                const entity = $('#crudEntityType').val();

                if (entity !== 'users' && entity !== 'attendants') {
                    return;
                }

                // Stop propagation to prevent other handlers
                e.stopImmediatePropagation();

                clearValidationErrors();

                const action = $('#crudAction').val();
                let formData = {};

                // Collect data based on entity type
                if (entity === 'users') {
                    formData = {
                        first_name: $('#userFirstName').val().trim(),
                        last_name: $('#userLastName').val().trim(),
                        email: $('#userEmail').val().trim(),
                        password: $('#userPassword').val(),
                        user_type_id: $('#userTypeId').val(),
                        hour_balance: $('#userHourBalance').val() || 0
                    };

                    // Status logic for users
                    let status = 'active';
                    if (action === 'edit' && $('#userSuspendAccount').is(':checked')) {
                        status = 'suspended';
                    }
                    formData.status = status;

                } else if (entity === 'attendants') {
                    formData = {
                        first_name: $('#attendantFirstName').val().trim(),
                        last_name: $('#attendantLastName').val().trim(),
                        email: $('#attendantEmail').val().trim(),
                        password: $('#attendantPassword').val(),
                        user_type_id: $('#attendantUserTypeId').is(':visible') ? $('#attendantUserTypeId').val() : $('#hiddenUserTypeId').val(),
                        assigned_area_id: $('#attendantAssignedArea').val()
                    };

                    console.log('Form data being sent to backend:', formData);

                    // Status logic for attendants
                    let status = 'active';
                    if (action === 'edit' && $('#attendantSuspendAccount').is(':checked')) {
                        status = 'suspended';
                    }
                    formData.status = status;
                }

                // Client-side validation - show errors below inputs
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
                } else if (action === 'add' && formData.password && formData.password.length < 8) {
                    errors.password = 'Password must be at least 8 characters';
                    hasErrors = true;
                }

                if (!formData.user_type_id) {
                    errors.user_type_id = 'Role/Type is required';
                    hasErrors = true;
                }

                // Check for duplicate names (client-side validation)
                if (action === 'add' && formData.first_name && formData.last_name) {
                    const fullName = `${formData.first_name} ${formData.last_name}`.toLowerCase();
                    // Check against visible table rows (simple check)
                    const tableId = entity === 'users' ? '#userTableBody' : '#staffTableBody';
                    const existingUsers = $(tableId + ' tr').map(function () {
                        const nameText = $(this).find('td:nth-child(2) strong').text().toLowerCase();
                        return nameText;
                    }).get();

                    if (existingUsers.includes(fullName)) {
                        errors.first_name = 'A user with this name already exists';
                        errors.last_name = 'A user with this name already exists';
                        hasErrors = true;
                    }
                }

                // Show validation errors if any
                if (hasErrors) {
                    showValidationErrors(errors);
                    // Focus on first invalid field
                    const firstErrorField = Object.keys(errors)[0];
                    $(`#crudFormModal [name="${firstErrorField}"]`).focus();
                    return;
                }

                // Store form data for confirmation
                window.pendingCrudFormData = formData;
                window.pendingCrudAction = action;

                // Build confirmation summary
                const roleName = entity === 'users'
                    ? ($('#userTypeId option:selected').text() || 'N/A')
                    : ($('#attendantUserTypeId option:selected').text() || 'N/A');

                let summaryHtml = `
                    <div class="row">
                        <div class="col-md-6"><strong>Name:</strong></div>
                        <div class="col-md-6">${escapeHtml(formData.first_name + ' ' + formData.last_name)}</div>
                    </div>
                    <div class="row">
                        <div class="col-md-6"><strong>Email:</strong></div>
                        <div class="col-md-6">${escapeHtml(formData.email)}</div>
                    </div>
                    <div class="row">
                        <div class="col-md-6"><strong>Role/Type:</strong></div>
                        <div class="col-md-6">${roleName}</div>
                    </div>
                `;

                if (entity === 'users') {
                    summaryHtml += `
                    <div class="row">
                        <div class="col-md-6"><strong>Hour Balance:</strong></div>
                        <div class="col-md-6">${formData.hour_balance || 0} hrs</div>
                    </div>`;
                } else if (entity === 'attendants' && formData.assigned_area_id) {
                    summaryHtml += `
                    <div class="row">
                        <div class="col-md-6"><strong>Assigned Area:</strong></div>
                        <div class="col-md-6">${$('#attendantAssignedArea option:selected').text() || 'N/A'}</div>
                    </div>`;
                }

                summaryHtml += `
                    <div class="row">
                        <div class="col-md-6"><strong>Status:</strong></div>
                        <div class="col-md-6">${formData.status || 'active'}</div>
                    </div>
                    ${action === 'add' && formData.password ? '<div class="row"><div class="col-md-6"><strong>Password:</strong></div><div class="col-md-6">••••••••</div></div>' : ''}
                `;

                // Change to confirmation view
                const message = action === 'add'
                    ? 'Are you sure you want to add this user?'
                    : 'Are you sure you want to update this user?';
                const description = action === 'add'
                    ? `You are about to add "${formData.first_name} ${formData.last_name}" to the system.`
                    : `You are about to update user "${formData.first_name} ${formData.last_name}".`;

                $('#crudConfirmTitle').text('Confirm ' + (action === 'add' ? 'Add User' : 'Update User'));
                $('#crudConfirmMessage').text(message);
                $('#crudConfirmDescription').text(description);
                $('#crudConfirmSummary').html(summaryHtml);
                $('#crudConfirmYesText').text(action === 'add' ? 'Yes, Add User' : 'Yes, Update User');

                // Hide form section, show confirmation section
                $('#crudFormSection').hide();
                $('#crudConfirmSection').show();

                // Hide normal footer, show confirmation footer
                $('#crudNormalFooter').hide();
                resetConfirmationButton();
                $('#crudConfirmFooter').show();
            });

            // Cancel confirmation (No button)
            $('#crudConfirmCancelBtn').off('click').on('click', function () {
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

            // Confirm button (Yes button)
            $('#crudConfirmYesBtn').off('click.users').on('click.users', function (e) {
                // Check entity type FIRST - only handle users and attendants
                const entity = $('#crudEntityType').val();

                if (entity !== 'users' && entity !== 'attendants') {
                    return; // Let other handlers (subscriptions) handle it
                }

                // Stop propagation to prevent other handlers
                e.stopImmediatePropagation();

                // Get stored form data
                const formData = window.pendingCrudFormData;
                const action = window.pendingCrudAction;
                const id = $('#crudEntityId').val();

                // Check if formData exists
                if (!formData) {
                    console.error('No form data found in window.pendingCrudFormData');
                    showSuccessModal('Error', 'Form data is missing. Please try again.');
                    return;
                }

                // Remove password if empty for edit
                if (action === 'edit' && !formData.password) {
                    delete formData.password;
                }

                let url = '';
                if (entity === 'users') {
                    url = action === 'add'
                        ? `${baseUrl}users/create`
                        : `${baseUrl}users/update/${id}`;
                } else {
                    url = action === 'add'
                        ? `${baseUrl}attendants/create`
                        : `${baseUrl}attendants/update/${id}`;
                }

                const method = 'POST';

                // Show loading state
                const confirmBtn = $('#crudConfirmYesBtn');
                const originalText = confirmBtn.html();
                confirmBtn.prop('disabled', true).html('<span class="spinner-border spinner-border-sm me-2"></span>Processing...');

                ajaxWithCSRF(url, {
                    method: method,
                    data: formData,
                    success: function (response) {
                        console.log('CRUD Success:', response);
                        if (response.success) {
                            try {
                                // Close form modal properly
                                const modalEl = document.getElementById('crudFormModal');
                                let bsModal = bootstrap.Modal.getInstance(modalEl);

                                // If not found, try to create new instance or hide anyway
                                if (bsModal) {
                                    bsModal.hide();
                                } else {
                                    $(modalEl).modal('hide');
                                }

                                // Force remove backdrop if it persists (common Bootstrap bug)
                                setTimeout(() => {
                                    $('.modal-backdrop').remove();
                                    $('body').removeClass('modal-open').css('padding-right', '');
                                }, 500);

                                // Reset footer
                                $('#crudConfirmFooter').hide();
                                $('#crudNormalFooter').show();

                                // Show success modal
                                showSuccessModal(action === 'add' ? 'User Added Successfully' : 'User Updated Successfully',
                                    action === 'add'
                                        ? `User "${formData.first_name} ${formData.last_name}" has been added to the system.`
                                        : `User "${formData.first_name} ${formData.last_name}" has been updated successfully.`);

                                // Update table dynamically instead of reloading
                                if (action === 'add' && response.data) {
                                    if (entity === 'attendants') {
                                        if (response.data.user_type_id == 3) {
                                            console.log('Adding new admin to table');
                                            addAdminToTable(response.data);
                                        } else {
                                            console.log('Adding new attendant to table');
                                            addAttendantToTable(response.data);
                                        }
                                    } else {
                                        console.log('Adding new user to table');
                                        addUserToTable(response.data);
                                    }
                                } else if (action === 'edit' && response.data) {
                                    if (entity === 'attendants') {
                                        console.log('Updating staff in table');
                                        updateStaffInTable(response.data);
                                    } else {
                                        console.log('Updating subscriber in table');
                                        updateUserInTable(response.data);
                                    }
                                }

                                // Clear stored data after successful operation
                                delete window.pendingCrudFormData;
                                delete window.pendingCrudAction;
                            } catch (err) {
                                console.error('Error in CRUD success handler logic:', err);
                            }
                        } else {
                            console.warn('CRUD Operation failed:', response);
                            if (response.errors) {
                                revertToFormSection();
                                showValidationErrors(response.errors);
                            } else {
                                showSuccessModal('Error', response.message || 'Operation failed');
                            }
                        }
                    },
                    error: function (xhr, status, error) {
                        console.error('CRUD AJAX Error:', status, error, xhr.responseText);
                        const response = xhr.responseJSON;
                        if (response && response.errors) {
                            revertToFormSection();
                            showValidationErrors(response.errors);
                        } else {
                            showSuccessModal('Error', 'Server error. Please try again.');
                        }
                    },
                    complete: function () {
                        console.log('CRUD AJAX Complete');
                        confirmBtn.prop('disabled', false).html(originalText);
                    }
                });
            });

            // ====================================
            // HELPER FUNCTIONS
            // ====================================
            function resetConfirmationButton() {
                const confirmBtn = $('#crudConfirmYesBtn');
                const resetText = $('#crudConfirmYesText').text() || 'Yes, Confirm';
                confirmBtn.prop('disabled', false).html(resetText);
                console.log('Confirmation button reset to:', resetText);
            }

            function revertToFormSection() {
                // Show form section, hide confirmation section
                $('#crudFormSection').show();
                $('#crudConfirmSection').hide();

                // Hide confirmation footer, show normal footer
                $('#crudConfirmFooter').hide();
                $('#crudNormalFooter').show();

                // Reset button state
                resetConfirmationButton();
                console.log('UI reverted to form section');
            }

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
                        // Make sure the parent form group has the error styling
                        input.closest('.mb-3').addClass('has-error');
                    }
                    if (errorDiv.length) {
                        errorDiv.text(errors[field]).show();
                    }
                });
            }

            // Password strength helpers
            function initUserPasswordStrength() {
                if (!window.PasswordStrength) {
                    console.warn('PasswordStrength helper not found');
                    return;
                }

                $(document).off('input.usersPassword').on('input.usersPassword', '#userPassword', function () {
                    window.PasswordStrength.update(this, '#userPasswordStrengthBar', '#userPasswordStrengthText');
                });

                $('#crudFormModal').off('hidden.bs.modal.usersPassword').on('hidden.bs.modal.usersPassword', function () {
                    resetUserPasswordStrength();
                });
            }

            function resetUserPasswordStrength() {
                if (window.PasswordStrength) {
                    window.PasswordStrength.reset('#userPasswordStrengthBar', '#userPasswordStrengthText');
                }
            }

            // Open CRUD Modal
            function openCrudModal(action, entity, data = null, forceTypeId = null) {
                // Reset form
                $('#crudForm')[0].reset();
                clearValidationErrors();
                $('#crudFormSection').show();
                $('#crudConfirmSection').hide();
                $('#crudNormalFooter').show();
                $('#crudConfirmFooter').hide();

                // Set hidden inputs
                $('#crudAction').val(action);
                $('#crudEntityType').val(entity);
                $('#crudEntityId').val(data ? (data.user_id || data.id) : '');

                // Update Modal Title and specific fields
                let title = '';
                if (entity === 'users') {
                    title = action === 'add' ? 'Add New Subscriber' : 'Edit Subscriber';
                    $('.entity-fields').hide(); // Hide all entity fields
                    $('.fields-users').show(); // Show only user fields

                    // Remove any static role display from previous staff edits
                    $('#userUserTypeId').closest('.mb-3').next('.form-control.bg-light').remove();
                    $('#hiddenUserTypeId').remove();
                    $('#userUserTypeId').prop('disabled', false).closest('.mb-3').show(); // Ensure dropdown is visible and enabled

                    if (action === 'edit' && data) {
                        $('#userFirstName').val(data.first_name);
                        $('#userLastName').val(data.last_name);
                        $('#userEmail').val(data.email);
                        $('#userHourBalance').val(data.hour_balance);
                        $('#userTypeId').val(1); // Subscriber

                        if (data.status === 'suspended') {
                            $('#userSuspendAccount').prop('checked', true);
                        } else {
                            $('#userSuspendAccount').prop('checked', false);
                        }
                    } else {
                        $('#userTypeId').val(1); // Default to Subscriber
                        $('#userSuspendAccount').prop('checked', false); // Default to not suspended for add
                    }
                } else {
                    // Staff (Admins or Attendants)
                    $('.entity-fields').hide(); // Hide all entity fields
                    $('.fields-attendants').show(); // Show only attendant fields

                    // Populate Role Dropdown (hidden if static)
                    const roleOptions = '<option value="">Select Role</option>' +
                        '<option value="3">Administrator</option>' +
                        '<option value="2">Parking Attendant</option>';
                    $('#attendantUserTypeId').html(roleOptions);

                    // Load Parking Areas
                    $.ajax({
                        url: `${baseUrl}attendants/getParkingAreas`,
                        method: 'GET',
                        success: function (response) {
                            if (response.success && response.data) {
                                let areaOptions = '<option value="">Select Area (Optional)</option>';
                                response.data.forEach(area => {
                                    areaOptions += `<option value="${area.parking_area_id}">${area.parking_area_name}</option>`;
                                });
                                $('#attendantAssignedArea').html(areaOptions);

                                // Set selected area if editing
                                if (data && data.assigned_area_id) {
                                    $('#attendantAssignedArea').val(data.assigned_area_id);
                                }
                            }
                        }
                    });

                    // Determine User Type (Admin vs Attendant)
                    let typeId = forceTypeId;
                    if (!typeId && data) {
                        typeId = data.user_type_id;
                    }

                    // Set Title explicitly based on Type
                    if (typeId == 3) { // Admin
                        title = action === 'add' ? 'Add Administrator' : 'Edit Administrator';
                    } else if (typeId == 2) { // Attendant
                        title = action === 'add' ? 'Add Attendant' : 'Edit Attendant';
                    } else {
                        title = action === 'add' ? 'Add Staff Member' : 'Edit Staff Member';
                    }

                    // Handle Static User Type Logic (Text Box vs Dropdown)
                    if ($('#attendantUserTypeId').length) {
                        // Reset: Show dropdown, remove any static text, remove hidden input
                        $('#attendantUserTypeId').show().prop('disabled', false);
                        $('#attendantUserTypeId').closest('.mb-3').find('.static-role-text').remove();
                        $('#hiddenUserTypeId').remove();

                        if (typeId) {
                            // STATIC MODE: Hide dropdown, show readonly text
                            $('#attendantUserTypeId').hide();

                            const roleName = typeId == 3 ? 'Administrator' : (typeId == 2 ? 'Parking Attendant' : 'Staff');
                            const staticRoleHtml = `<input type="text" class="form-control static-role-text" value="${roleName}" readonly style="background-color: #e9ecef;">`;

                            $('#attendantUserTypeId').after(staticRoleHtml);

                            // Add hidden input for form submission
                            $('<input>').attr({
                                type: 'hidden',
                                id: 'hiddenUserTypeId',
                                name: 'user_type_id',
                                value: typeId
                            }).insertBefore('#attendantUserTypeId');

                            // Handle visibility of Assigned Area based on type
                            toggleAssignedAreaField(typeId);
                        } else {
                            // DYNAMIC MODE: Show dropdown (already reset above)
                            toggleAssignedAreaField(''); // Reset/Show based on selection (or hide if none)
                        }
                    }

                    if (action === 'edit' && data) {
                        $('#attendantFirstName').val(data.first_name);
                        $('#attendantLastName').val(data.last_name);
                        $('#attendantEmail').val(data.email);
                        $('#attendantAssignedArea').val(data.assigned_area_id);

                        if (data.status === 'suspended') {
                            $('#attendantSuspendAccount').prop('checked', true);
                        } else {
                            $('#attendantSuspendAccount').prop('checked', false);
                        }
                    } else {
                        // Reset defaults for Add
                        if (typeId == 2) {
                            $('#attendantAssignedAreaContainer').show();
                        }
                        $('#attendantSuspendAccount').prop('checked', false); // Default to not suspended for add
                    }
                }

                $('#crudModalTitleText').text(title);

                // Show Modal
                const modal = new bootstrap.Modal(document.getElementById('crudFormModal'));
                modal.show();
            }

            // Reset footer when modal is closed
            $('#crudFormModal').off('hidden.bs.modal').on('hidden.bs.modal', function () {
                // Show form section, hide confirmation section
                $('#crudFormSection').show();
                $('#crudConfirmSection').hide();

                $('#crudConfirmFooter').hide();
                $('#crudNormalFooter').show();
                clearValidationErrors();
                // Clear stored data
                delete window.pendingCrudFormData;
                delete window.pendingCrudAction;

                // Remove any dynamically added hidden inputs for user_type_id
                $('#hiddenUserTypeId').remove();
                // Re-enable and show user type dropdowns if they were disabled/hidden
                $('#userUserTypeId').prop('disabled', false).closest('.mb-3').show();
                $('#attendantUserTypeId').prop('disabled', false).closest('.mb-3').show();
                // Remove any static role display
                $('#userUserTypeId').closest('.mb-3').next('.form-control.bg-light').remove();
                $('#attendantUserTypeId').closest('.mb-3').next('.form-control.bg-light').remove();
            });

            // ====================================
            // TAB SWITCHING - Walk-in Guests
            // ====================================

            // Initialize tab switching
            $('[data-bs-toggle="tab"]').on('shown.bs.tab', function (e) {
                const targetTab = $(e.target).attr('data-bs-target');

                // Update filters visibility
                updateFiltersForTab(targetTab);

                if (targetTab === '#walk-in-guests') {
                    // Load walk-in guests when switching to that tab -- only if not loaded?
                    // For now, always reload to ensure fresh data
                    loadWalkInGuests();
                } else if (targetTab === '#subscribers') {
                    // Reload subscribers when switching back
                    loadUsers();
                } else if (targetTab === '#staff') {
                    // Load staff when switching to that tab
                    loadStaff();
                }
            });

            // ====================================
            // WALK-IN GUESTS MANAGEMENT
            // ====================================

            let guestCurrentPage = 1;
            let guestPerPage = window.APP_RECORDS_PER_PAGE || 25;
            let guestFilters = {};
            let allGuestsData = [];
            let attendantsList = [];

            // Load attendants list for filter dropdown
            function loadAttendantsList() {
                $.ajax({
                    url: `${baseUrl}users/getAttendantsList`,
                    method: 'GET',
                    success: function (response) {
                        if (response.success) {
                            attendantsList = response.data;
                            populateAttendantsDropdown();
                        }
                    }
                });
            }

            function populateAttendantsDropdown() {
                let options = '<option value="">All Attendants</option>';
                attendantsList.forEach(attendant => {
                    options += `<option value="${attendant.user_id}">${escapeHtml(attendant.first_name + ' ' + attendant.last_name)}</option>`;
                });
                $('#sharedFilterAttendant').html(options);
            }

            // Load vehicle types for filter dropdown
            function loadVehicleTypes() {
                $.ajax({
                    url: `${baseUrl}users/getVehicleTypes`,
                    method: 'GET',
                    success: function (response) {
                        if (response.success && response.data) {
                            populateVehicleTypesDropdown(response.data);
                        }
                    },
                    error: function () {
                        console.error('Failed to load vehicle types');
                    }
                });
            }

            function populateVehicleTypesDropdown(types) {
                let options = '<option value="">All Vehicle Types</option>';
                types.forEach(type => {
                    options += `<option value="${escapeHtml(type.value)}">${escapeHtml(type.label)}</option>`;
                });
                $('#sharedFilterVehicleType').html(options);
            }

            // Load staff user types for filter
            function loadStaffUserTypes() {
                return $.ajax({
                    url: `${baseUrl}users/getStaffUserTypes`,
                    method: 'GET',
                    success: function (response) {
                        if (response.success && response.data) {
                            let options = '<option value="">All Roles</option>';
                            response.data.forEach(type => {
                                options += `<option value="${type.user_type_id}">${escapeHtml(type.user_type_name)}</option>`;
                            });
                            $('#sharedFilterRole').html(options);

                            // Also populate the modal dropdown if exists
                            if ($('#attendantUserTypeId').length) {
                                let modalOptions = '<option value="" disabled selected>Select Role</option>';
                                response.data.forEach(type => {
                                    modalOptions += `<option value="${type.user_type_id}">${escapeHtml(type.user_type_name)}</option>`;
                                });
                                $('#attendantUserTypeId').html(modalOptions);
                            }
                        }
                    }
                });
            }

            // Load parking areas for filter
            function loadParkingAreas() {
                return $.ajax({
                    url: `${baseUrl}users/getParkingAreas`,
                    method: 'GET',
                    success: function (response) {
                        if (response.success && response.data) {
                            let options = '<option value="">All Areas</option>';
                            response.data.forEach(area => {
                                options += `<option value="${area.parking_area_id}">${escapeHtml(area.parking_area_name)}</option>`;
                            });
                            $('#sharedFilterArea').html(options);

                            // Also populate the modal dropdown if exists
                            if ($('#attendantAssignedArea').length) {
                                let modalOptions = '<option value="">Select Area (Optional)</option>';
                                response.data.forEach(area => {
                                    modalOptions += `<option value="${area.parking_area_id}">${escapeHtml(area.parking_area_name)}</option>`;
                                });
                                $('#attendantAssignedArea').html(modalOptions);
                            }
                        }
                    }
                });
            }

            // Populate staff dropdowns helper
            function populateStaffDropdowns() {
                const p1 = loadStaffUserTypes();
                const p2 = loadParkingAreas();
                return $.when(p1, p2);
            }



            // Update filters visibility based on tab
            function updateFiltersForTab(tabId) {
                // Hide all specific filters first
                $('.filter-field').not('.filter-search').hide();

                // Show filters based on tab
                if (tabId === '#subscribers') {
                    $('.filter-status, .filter-online-status').show();
                } else if (tabId === '#staff') {
                    $('.filter-role, .filter-area, .filter-status, .filter-online-status').show();
                } else if (tabId === '#walk-in-guests') {
                    $('.filter-attendant, .filter-vehicle-type, .filter-date-range').show();
                }

                // Reset filter values when switching
                $('#sharedFiltersCard select').val('');
                $('#sharedSearchInput').val('');
            }

            // Load walk-in guests
            function loadWalkInGuests() {
                const params = new URLSearchParams({
                    page: guestCurrentPage,
                    per_page: guestPerPage,
                    ...guestFilters
                });

                $.ajax({
                    url: `${baseUrl}users/getWalkInGuests?${params}`,
                    method: 'GET',
                    beforeSend: function () {
                        $('#guestsTableBody').html(`
                        <tr>
                            <td colspan="7" class="text-center py-5">
                                <div class="spinner-border text-primary" role="status"></div>
                                <p class="mt-2 text-muted">Loading walk-in guests...</p>
                            </td>
                        </tr>
                    `);
                    },
                    success: function (response) {
                        if (response.success && response.data) {
                            allGuestsData = response.data.data || [];
                            renderGuestsTable(allGuestsData);
                            renderGuestsPagination(response.data);
                            if (response.stats) {
                                updateStats(response.stats, 'guests');
                            }
                        }
                    },
                    error: function () {
                        $('#guestsTableBody').html(`
                        <tr>
                            <td colspan="7" class="text-center py-5 text-danger">
                                <i class="fas fa-exclamation-triangle fa-3x mb-3"></i>
                                <p>Error loading walk-in guests. Please try again.</p>
                            </td>
                        </tr>
                    `);
                    }
                });
            }

            // Render walk-in guests table
            function renderGuestsTable(guests) {
                let html = '';

                if (!guests || guests.length === 0) {
                    html = `
                    <tr>
                        <td colspan="7" class="text-center py-5">
                            <i class="fas fa-user-clock fa-3x text-muted mb-3"></i>
                            <p class="text-muted">No walk-in guests found</p>
                        </td>
                    </tr>
                `;
                } else {
                    guests.forEach((guest, index) => {
                        const statusBadge = getReservationStatusBadge(guest.reservation_status);
                        const createdDate = guest.created_at ? new Date(guest.created_at).toLocaleDateString('en-US', {
                            month: 'short', day: 'numeric', year: 'numeric', hour: '2-digit', minute: '2-digit'
                        }) : '-';

                        const startIdx = (guestCurrentPage - 1) * guestPerPage + 1;

                        html += `
                        <tr data-guest-id="${guest.guest_booking_id}">
                            <td>#${startIdx + index}</td>
                            <td>
                                <strong>${escapeHtml(guest.guest_name)}</strong><br>
                                <small class="text-muted">${escapeHtml(guest.guest_email || '')}</small>
                            </td>
                            <td>
                                <span class="badge bg-secondary">${escapeHtml(guest.vehicle_type || 'N/A')}</span><br>
                                <small>${escapeHtml(guest.vehicle_brand || '')} ${escapeHtml(guest.vehicle_color || '')}</small><br>
                                <small class="text-muted">${escapeHtml(guest.plate_number || '')}</small>
                            </td>
                            <td>
                                <strong>${escapeHtml(guest.attendant_name)}</strong><br>
                                <small class="text-muted">${escapeHtml(guest.attendant_role || '')}</small>
                            </td>
                            <td>
                                #${guest.reservation_id}<br>
                                ${statusBadge}
                            </td>
                            <td><small>${createdDate}</small></td>
                            <td>
                                <button class="btn btn-sm btn-outline-primary view-guest-btn" 
                                        data-id="${guest.guest_booking_id}" 
                                        title="View Details"
                                        style="border-color: #800000; color: #800000;">
                                    <i class="fas fa-eye"></i>
                                </button>
                            </td>
                        </tr>
                    `;
                    });
                }

                $('#guestsTableBody').html(html);
            }

            // Get reservation status badge
            function getReservationStatusBadge(status) {
                const badges = {
                    'confirmed': '<span class="badge bg-success">Confirmed</span>',
                    'pending': '<span class="badge bg-warning">Pending</span>',
                    'cancelled': '<span class="badge bg-danger">Cancelled</span>',
                    'completed': '<span class="badge bg-info">Completed</span>'
                };
                return badges[status] || '<span class="badge bg-secondary">Unknown</span>';
            }

            // Render guests pagination
            function renderGuestsPagination(paginationData) {
                const { current_page, per_page, total, from, to } = paginationData;

                guestCurrentPage = parseInt(current_page, 10);
                guestPerPage = parseInt(per_page, 10);
                $('#guestPerPageSelect').val(guestPerPage);

                $('#guestPaginationInfo').html(`Showing ${from || 0} to ${to || 0} of ${total} walk-in guests`);
                $('#guestTableInfo').html(`${total} total walk-in guests`);

                const totalPages = Math.ceil(total / per_page);
                let paginationHtml = '';

                // Previous button
                paginationHtml += current_page === 1
                    ? '<li class="page-item disabled"><span class="page-link">Previous</span></li>'
                    : `<li class="page-item"><a class="page-link guest-page-link" href="#" data-page="${current_page - 1}">Previous</a></li>`;

                // Page numbers
                for (let i = 1; i <= totalPages; i++) {
                    if (i === 1 || i === totalPages || (i >= current_page - 2 && i <= current_page + 2)) {
                        paginationHtml += `
                        <li class="page-item ${i === current_page ? 'active' : ''}">
                            <a class="page-link guest-page-link" href="#" data-page="${i}">${i}</a>
                        </li>
                    `;
                    } else if (i === current_page - 3 || i === current_page + 3) {
                        paginationHtml += '<li class="page-item disabled"><span class="page-link">...</span></li>';
                    }
                }

                // Next button
                paginationHtml += (current_page === totalPages || totalPages === 0)
                    ? '<li class="page-item disabled"><span class="page-link">Next</span></li>'
                    : `<li class="page-item"><a class="page-link guest-page-link" href="#" data-page="${current_page + 1}">Next</a></li>`;

                $('#guestPaginationControls').html(paginationHtml);
            }

            // Event handlers for walk-in guests
            $(document).on('click', '.guest-page-link', function (e) {
                e.preventDefault();
                const page = parseInt($(this).data('page'), 10);
                if (page && page > 0 && page !== guestCurrentPage) {
                    guestCurrentPage = page;
                    loadWalkInGuests();
                }
            });

            $('#guestPerPageSelect').on('change', function () {
                guestPerPage = parseInt($(this).val());
                guestCurrentPage = 1;
                loadWalkInGuests();
            });

            // Export walk-in guests
            $('#exportGuestsBtn').on('click', function () {
                const params = buildGuestExportParams();
                const exportUrl = baseUrl + 'users/exportWalkInGuests' + (params ? '?' + params : '');
                window.location.href = exportUrl;
            });

            function buildGuestExportParams() {
                const params = [];
                if (guestFilters.search) params.push('search=' + encodeURIComponent(guestFilters.search));
                if (guestFilters.attendant_id) params.push('attendant_id=' + encodeURIComponent(guestFilters.attendant_id));
                if (guestFilters.vehicle_type) params.push('vehicle_type=' + encodeURIComponent(guestFilters.vehicle_type));
                if (guestFilters.date_range) params.push('date_range=' + encodeURIComponent(guestFilters.date_range));
                return params.join('&');
            }

            // View guest booking details
            $(document).on('click', '.view-guest-btn', function () {
                const guestId = $(this).data('id');

                $.ajax({
                    url: `${baseUrl}users/getWalkInGuestDetails/${guestId}`,
                    method: 'GET',
                    success: function (response) {
                        if (response.success) {
                            showGuestDetailsModal(response.data);
                        }
                    },
                    error: function () {
                        alert('Error loading guest details. Please try again.');
                    }
                });
            });

            // Show guest details modal
            function showGuestDetailsModal(guest) {
                const modal = $('#viewDetailsModal');

                if (document.activeElement && document.activeElement.blur) {
                    document.activeElement.blur();
                }

                $('#viewModalTitleText').text('Walk-in Guest Details');

                $('.view-content').hide();
                $('#viewDetailsLoading').show();

                const bsModal = bootstrap.Modal.getOrCreateInstance(modal[0], {
                    backdrop: true,
                    keyboard: true,
                    focus: false
                });
                bsModal.show();

                setTimeout(function () {
                    displayGuestViewData(guest);
                }, 300);
            }

            // Display guest view data
            function displayGuestViewData(guest) {
                $('#viewDetailsLoading').hide();
                $('.view-content').hide(); // Hide all view sections
                $('.view-guests').show(); // Show guest dedicated section

                // Format dates
                const createdDate = guest.created_at ? new Date(guest.created_at).toLocaleDateString('en-US', {
                    year: 'numeric',
                    month: 'long',
                    day: 'numeric',
                    hour: '2-digit',
                    minute: '2-digit'
                }) : '-';

                const startTime = guest.start_time ? new Date(guest.start_time).toLocaleString('en-US') : 'N/A';
                const endTime = guest.end_time ? new Date(guest.end_time).toLocaleString('en-US') : 'N/A';

                // Set avatar
                const initial = guest.guest_name ? guest.guest_name.charAt(0).toUpperCase() : 'G';
                const avatarSrc = `data:image/svg+xml;utf8,<svg xmlns="http://www.w3.org/2000/svg" width="120" height="120" viewBox="0 0 120 120"><rect width="120" height="120" fill="%23800000"/><text x="50%" y="50%" dominant-baseline="middle" text-anchor="middle" font-family="Arial, sans-serif" font-size="50" fill="%23ffffff">${initial}</text></svg>`;

                // Update Header & Avatar
                $('#viewGuestAvatar').attr('src', avatarSrc);
                $('#viewGuestFullName').text(guest.guest_name || 'N/A');
                $('#viewGuestIdDisplay').text(`Booking #${guest.guest_booking_id}`);

                // Basic Info
                $('#viewGuestEmail').text(guest.guest_email || 'N/A');
                $('#viewGuestCreatedAt').text(createdDate);

                // Vehicle Details
                $('#viewGuestPlate').text(guest.plate_number || 'N/A');
                $('#viewGuestVehicleType').text(guest.vehicle_type || 'N/A');
                $('#viewGuestVehicleInfo').text(`${guest.vehicle_brand || ''} ${guest.vehicle_color || ''}`.trim() || 'N/A');

                // Reservation Details
                $('#viewGuestResStatus').html(getReservationStatusBadge(guest.reservation_status));
                $('#viewGuestStartTime').text(startTime);
                $('#viewGuestEndTime').text(endTime);

                // Processed By
                const attendantRole = guest.attendant_role ? ` (${guest.attendant_role})` : '';
                $('#viewGuestAttendant').text(`${guest.attendant_name}${attendantRole}`);

                // QR Code Section
                if (guest.qr_code) {
                    $('#viewGuestQRCode').attr('src', guest.qr_code);
                    $('#viewGuestQRSection').show();
                } else {
                    $('#viewGuestQRSection').hide();
                }
            }

            // Load attendants list on page load for filters
            loadAttendantsList();

            // Load vehicle types for filter dropdown
            loadVehicleTypes();

            // ====================================
            // ADMINISTRATORS MANAGEMENT
            // ====================================

            let allAdminsData = [];

            // Load admins list
            function loadAdmins() {
                // Ensure user_type_id is set to 3 for Admins
                adminsFilters.user_type_id = 3;

                const params = new URLSearchParams({
                    page: adminsCurrentPage,
                    per_page: adminsPerPage,
                    ...adminsFilters
                });

                $.ajax({
                    url: `${baseUrl}users/list?${params}`,
                    method: 'GET',
                    beforeSend: function () {
                        $('#adminsTableBody').html(`
                            <tr>
                                <td colspan="7" class="text-center py-5">
                                    <div class="spinner-border text-primary" role="status"></div>
                                    <p class="mt-2 text-muted">Loading administrators...</p>
                                </td>
                            </tr>
                        `);
                    },
                    success: function (response) {
                        if (response.success && response.data) {
                            let adminData = Array.isArray(response.data) ? response.data : (response.data.data || []);
                            allAdminsData = adminData;
                            renderAdminsTable(adminData);

                            if (response.pagination) {
                                renderAdminsPagination(response.pagination);
                            }
                            if (response.stats) {
                                updateStats(response.stats, 'admins');
                            }
                        }
                    },
                    error: function () {
                        $('#adminsTableBody').html(`
                            <tr>
                                <td colspan="7" class="text-center py-5 text-danger">
                                    <i class="fas fa-exclamation-triangle fa-3x mb-3"></i>
                                    <p>Error loading administrators. Please try again.</p>
                                </td>
                            </tr>
                        `);
                    }
                });
            }

            // Render admins table
            function renderAdminsTable(admins) {
                let html = '';

                if (!admins || admins.length === 0) {
                    html = `
                        <tr>
                            <td colspan="7" class="text-center py-5">
                                <i class="fas fa-user-shield fa-3x text-muted mb-3"></i>
                                <p class="text-muted">No administrators found</p>
                            </td>
                        </tr>
                    `;
                } else {
                    admins.forEach((admin, index) => {
                        const statusBadge = getStatusBadge(admin.status);
                        const roleBadge = '<span class="badge bg-danger">Admin</span>';

                        const onlineStatus = (admin.is_online == 1 || admin.is_online === true)
                            ? '<span class="badge bg-success"><i class="fas fa-circle"></i> Online</span>'
                            : '<span class="badge bg-secondary"><i class="fas fa-circle"></i> Offline</span>';

                        const assignedArea = '<span class="text-muted">Not Assigned</span>';
                        const adminData = JSON.stringify(admin).replace(/"/g, '&quot;');
                        const startIdx = (adminsCurrentPage - 1) * adminsPerPage + 1;

                        html += `
                            <tr data-user-id="${admin.user_id}">
                                <td class="ps-4">#${startIdx + index}</td>
                                <td>
                                    <strong>${escapeHtml(admin.first_name || '')} ${escapeHtml(admin.last_name || '')}</strong><br>
                                    <small class="text-muted">${escapeHtml(admin.email)}</small>
                                </td>
                                <td>${roleBadge}</td>
                                <td>${assignedArea}</td>
                                <td>${statusBadge}</td>
                                <td>${onlineStatus}</td>
                                <td class="text-end pe-4">
                                    <button class="btn btn-sm btn-outline-primary view-staff-btn" 
                                            data-id="${admin.user_id}" 
                                            title="View Details"
                                            style="border-color: #800000; color: #800000;">
                                        <i class="fas fa-eye"></i>
                                    </button>
                                    <button class="btn btn-sm btn-outline-secondary edit-staff-btn" 
                                            data-id="${admin.user_id}"
                                            data-user='${adminData}'
                                            title="Edit"
                                            style="border-color: #6c757d; color: #6c757d;">
                                        <i class="fas fa-pen"></i>
                                    </button>
                                    <button class="btn btn-sm btn-outline-danger delete-staff-btn" 
                                            data-id="${admin.user_id}" 
                                            data-name="${escapeHtml(admin.first_name + ' ' + admin.last_name)}" 
                                            title="Delete"
                                            style="border-color: #dc3545; color: #dc3545;">
                                        <i class="fas fa-trash"></i>
                                    </button>
                                </td>
                            </tr>
                        `;
                    });
                }
                $('#adminsTableBody').html(html);
            }

            // Render admins pagination
            function renderAdminsPagination(paginationData) {
                const { current_page, per_page, total, from, to } = paginationData;

                adminsCurrentPage = parseInt(current_page, 10);
                adminsPerPage = parseInt(per_page, 10);
                $('#adminsPerPageSelect').val(adminsPerPage);

                $('#adminsPaginationInfo').html(`Showing ${from || 0} to ${to || 0} of ${total} administrators`);

                const totalPages = Math.ceil(total / per_page);
                let paginationHtml = '';

                // Previous button
                paginationHtml += current_page === 1
                    ? '<li class="page-item disabled"><span class="page-link">Previous</span></li>'
                    : `<li class="page-item"><a class="page-link admins-page-link" href="#" data-page="${current_page - 1}">Previous</a></li>`;

                for (let i = 1; i <= totalPages; i++) {
                    if (i === 1 || i === totalPages || (i >= current_page - 2 && i <= current_page + 2)) {
                        paginationHtml += `
                            <li class="page-item ${i === current_page ? 'active' : ''}">
                                <a class="page-link admins-page-link" href="#" data-page="${i}">${i}</a>
                            </li>
                        `;
                    } else if (i === current_page - 3 || i === current_page + 3) {
                        paginationHtml += '<li class="page-item disabled"><span class="page-link">...</span></li>';
                    }
                }

                paginationHtml += (current_page === totalPages || totalPages === 0)
                    ? '<li class="page-item disabled"><span class="page-link">Next</span></li>'
                    : `<li class="page-item"><a class="page-link admins-page-link" href="#" data-page="${current_page + 1}">Next</a></li>`;

                $('#adminsPaginationControls').html(paginationHtml);
            }

            // Events for Admins
            $(document).on('click', '.admins-page-link', function (e) {
                e.preventDefault();
                const page = parseInt($(this).data('page'), 10);
                if (page && page > 0 && page !== adminsCurrentPage) {
                    adminsCurrentPage = page;
                    loadAdmins();
                }
            });

            $('#adminsPerPageSelect').on('change', function () {
                adminsPerPage = parseInt($(this).val());
                adminsCurrentPage = 1;
                loadAdmins();
            });

            $('#exportAdminsBtn').on('click', function () {
                const params = new URLSearchParams(adminsFilters);
                // Ensure we only get admins
                params.set('user_type_id', 3);
                const exportUrl = baseUrl + 'users/exportStaff?' + params.toString();
                window.location.href = exportUrl;
            });


            // ====================================
            // ATTENDANTS MANAGEMENT
            // ====================================

            let allAttendantsData = [];

            // Load attendants list
            function loadAttendants() {
                // Ensure user_type_id is set to 2 for Attendants (assuming 2 is attendant)
                // If Attendant ID is different, update here. Based on logic usually Admin=3, User=1.
                // Checking previous code: "userData.user_type_id == 2" was used for Attendant.
                attendantsFilters.user_type_id = 2;

                const params = new URLSearchParams({
                    page: attendantsCurrentPage,
                    per_page: attendantsPerPage,
                    ...attendantsFilters
                });

                $.ajax({
                    url: `${baseUrl}users/list?${params}`,
                    method: 'GET',
                    beforeSend: function () {
                        $('#attendantsTableBody').html(`
                            <tr>
                                <td colspan="7" class="text-center py-5">
                                    <div class="spinner-border text-primary" role="status"></div>
                                    <p class="mt-2 text-muted">Loading attendants...</p>
                                </td>
                            </tr>
                        `);
                    },
                    success: function (response) {
                        if (response.success && response.data) {
                            let attendantData = Array.isArray(response.data) ? response.data : (response.data.data || []);
                            allAttendantsData = attendantData;
                            renderAttendantsTable(attendantData);

                            if (response.pagination) {
                                renderAttendantsPagination(response.pagination);
                            }
                            if (response.stats) {
                                updateStats(response.stats, 'attendants');
                            }
                        }
                    },
                    error: function () {
                        $('#attendantsTableBody').html(`
                            <tr>
                                <td colspan="7" class="text-center py-5 text-danger">
                                    <i class="fas fa-exclamation-triangle fa-3x mb-3"></i>
                                    <p>Error loading attendants. Please try again.</p>
                                </td>
                            </tr>
                        `);
                    }
                });
            }

            // Render attendants table
            function renderAttendantsTable(attendants) {
                let html = '';

                if (!attendants || attendants.length === 0) {
                    html = `
                        <tr>
                            <td colspan="7" class="text-center py-5">
                                <i class="fas fa-user-tie fa-3x text-muted mb-3"></i>
                                <p class="text-muted">No attendants found</p>
                            </td>
                        </tr>
                    `;
                } else {
                    attendants.forEach((attendant, index) => {
                        const statusBadge = getStatusBadge(attendant.status);
                        const roleBadge = '<span class="badge bg-info">Attendant</span>';

                        const onlineStatus = (attendant.is_online == 1 || attendant.is_online === true)
                            ? '<span class="badge bg-success"><i class="fas fa-circle"></i> Online</span>'
                            : '<span class="badge bg-secondary"><i class="fas fa-circle"></i> Offline</span>';

                        const assignedArea = attendant.parking_area_name
                            ? escapeHtml(attendant.parking_area_name)
                            : '<span class="text-muted">Not Assigned</span>';

                        const attendantData = JSON.stringify(attendant).replace(/"/g, '&quot;');
                        const startIdx = (attendantsCurrentPage - 1) * attendantsPerPage + 1;

                        html += `
                            <tr data-user-id="${attendant.user_id}">
                                <td class="ps-4">#${startIdx + index}</td>
                                <td>
                                    <strong>${escapeHtml(attendant.first_name || '')} ${escapeHtml(attendant.last_name || '')}</strong><br>
                                    <small class="text-muted">${escapeHtml(attendant.email)}</small>
                                </td>
                                <td>${roleBadge}</td>
                                <td>${assignedArea}</td>
                                <td>${statusBadge}</td>
                                <td>${onlineStatus}</td>
                                <td class="text-end pe-4">
                                    <button class="btn btn-sm btn-outline-primary view-staff-btn" 
                                            data-id="${attendant.user_id}" 
                                            title="View Details"
                                            style="border-color: #800000; color: #800000;">
                                        <i class="fas fa-eye"></i>
                                    </button>
                                    <button class="btn btn-sm btn-outline-secondary edit-staff-btn" 
                                            data-id="${attendant.user_id}"
                                            data-user='${attendantData}'
                                            title="Edit"
                                            style="border-color: #6c757d; color: #6c757d;">
                                        <i class="fas fa-pen"></i>
                                    </button>
                                    <button class="btn btn-sm btn-outline-danger delete-staff-btn" 
                                            data-id="${attendant.user_id}" 
                                            data-name="${escapeHtml(attendant.first_name + ' ' + attendant.last_name)}" 
                                            title="Delete"
                                            style="border-color: #dc3545; color: #dc3545;">
                                        <i class="fas fa-trash"></i>
                                    </button>
                                </td>
                            </tr>
                        `;
                    });
                }
                $('#attendantsTableBody').html(html);
            }

            // Render attendants pagination
            function renderAttendantsPagination(paginationData) {
                const { current_page, per_page, total, from, to } = paginationData;

                attendantsCurrentPage = parseInt(current_page, 10);
                attendantsPerPage = parseInt(per_page, 10);
                $('#attendantsPerPageSelect').val(attendantsPerPage);

                $('#attendantsPaginationInfo').html(`Showing ${from || 0} to ${to || 0} of ${total} attendants`);

                const totalPages = Math.ceil(total / per_page);
                let paginationHtml = '';

                // Previous button
                paginationHtml += current_page === 1
                    ? '<li class="page-item disabled"><span class="page-link">Previous</span></li>'
                    : `<li class="page-item"><a class="page-link attendants-page-link" href="#" data-page="${current_page - 1}">Previous</a></li>`;

                for (let i = 1; i <= totalPages; i++) {
                    if (i === 1 || i === totalPages || (i >= current_page - 2 && i <= current_page + 2)) {
                        paginationHtml += `
                            <li class="page-item ${i === current_page ? 'active' : ''}">
                                <a class="page-link attendants-page-link" href="#" data-page="${i}">${i}</a>
                            </li>
                        `;
                    } else if (i === current_page - 3 || i === current_page + 3) {
                        paginationHtml += '<li class="page-item disabled"><span class="page-link">...</span></li>';
                    }
                }

                paginationHtml += (current_page === totalPages || totalPages === 0)
                    ? '<li class="page-item disabled"><span class="page-link">Next</span></li>'
                    : `<li class="page-item"><a class="page-link attendants-page-link" href="#" data-page="${current_page + 1}">Next</a></li>`;

                $('#attendantsPaginationControls').html(paginationHtml);
            }

            // Events for Attendants
            $(document).on('click', '.attendants-page-link', function (e) {
                e.preventDefault();
                const page = parseInt($(this).data('page'), 10);
                if (page && page > 0 && page !== attendantsCurrentPage) {
                    attendantsCurrentPage = page;
                    loadAttendants();
                }
            });

            $('#attendantsPerPageSelect').on('change', function () {
                attendantsPerPage = parseInt($(this).val());
                attendantsCurrentPage = 1;
                loadAttendants();
            });

            $('#exportAttendantsBtn').on('click', function () {
                const params = new URLSearchParams(attendantsFilters);
                // Ensure we only get attendants
                params.set('user_type_id', 2);
                const exportUrl = baseUrl + 'users/exportStaff?' + params.toString();
                window.location.href = exportUrl;
            });

            // ====================================
            // ADD USER DROPDOWN HANDLERS
            // ====================================

            // Add Admin Button
            $('#addAdminBtn').on('click', function (e) {
                e.preventDefault();
                openCrudModal('add', 'attendants', null, 3); // 3 = Admin
            });

            // Add Attendant Button
            $('#addAttendantBtn').on('click', function (e) {
                e.preventDefault();
                openCrudModal('add', 'attendants', null, 2); // 2 = Attendant
            });

            // Add Subscriber Button
            $('#addSubscriberBtn, #addUserBtn').on('click', function (e) {
                e.preventDefault();
                $('#crudModalTitleText').text('Add New Staff Member'); // Updated title

                // Populate dropdowns for staff
                populateStaffDropdowns();

                $('#crudConfirmFooter').hide();
                $('#crudNormalFooter').show();

                $('#crudFormModal').removeClass('mode-edit').addClass('mode-add');
                $('#crudModalIcon').removeClass().addClass('fas fa-user-plus me-2');
                $('#crudSubmitText').text('Add');

                // Hide all entity fields, show only attendants
                $('.entity-fields').hide();
                $('.fields-attendants').show();

                // Reset checkbox state
                $('#attendantSuspendAccount').prop('checked', false);

                // Initialize assigned area field visibility (default to hidden)
                toggleAssignedAreaField(3); // Default to Admin role (hidden)

                const bsModal = bootstrap.Modal.getOrCreateInstance($('#crudFormModal')[0]);
                bsModal.show();
            });

            // ====================================
            // STAFF ACTIONS (View/Edit/Delete)
            // ====================================

            // View Staff
            $(document).on('click', '.view-staff-btn', function () {
                const userId = $(this).data('id');

                $.ajax({
                    url: `${baseUrl}users/getStaffDetails/${userId}`,
                    method: 'GET',
                    success: function (response) {
                        if (response.success) {
                            showStaffDetailsModal(response.data);
                        }
                    }
                });
            });

            function showStaffDetailsModal(user) {
                const modal = $('#viewDetailsModal');

                $('#viewModalTitleText').text('Staff Details');
                $('.view-content').hide();
                $('#viewDetailsLoading').show();

                const bsModal = bootstrap.Modal.getOrCreateInstance(modal[0]);
                bsModal.show();

                setTimeout(function () {
                    displayStaffViewData(user);
                }, 300);
            }

            function displayStaffViewData(user) {
                $('#viewDetailsLoading').hide();
                $('.view-attendants').show();

                const createdDate = user.created_at ? new Date(user.created_at).toLocaleDateString() : '-';
                const lastActivity = user.last_activity_at ? new Date(user.last_activity_at).toLocaleString() : 'Never';

                // Avatar
                const initial = (user.first_name || 'U').charAt(0).toUpperCase();
                const avatarSrc = user.profile_picture
                    ? `${baseUrl}uploads/profiles/${user.profile_picture}`
                    : `data:image/svg+xml;utf8,<svg xmlns="http://www.w3.org/2000/svg" width="120" height="120" viewBox="0 0 120 120"><rect width="120" height="120" fill="%23800000"/><text x="50%" y="50%" dominant-baseline="middle" text-anchor="middle" font-family="Arial, sans-serif" font-size="50" fill="%23ffffff">${initial}</text></svg>`;

                $('#viewAttendantAvatar').attr('src', avatarSrc);
                $('#viewAttendantFullName').text(`${user.first_name} ${user.last_name}`);
                $('#viewAttendantStatusBadge').html(getStatusBadge(user.status));

                $('#viewAttendantId').text(`Staff ID: ${user.user_id}`);
                $('#viewAttendantEmail').text(user.email);
                $('#viewAttendantRole').text(user.user_type_id == 3 ? 'Administrator' : 'Parking Attendant');
                $('#viewAttendantArea').text(user.parking_area_name || 'All Areas');

                const onlineStatus = (user.is_online == 1)
                    ? '<span class="badge bg-success"><i class="fas fa-circle"></i> Online</span>'
                    : '<span class="badge bg-secondary"><i class="fas fa-circle"></i> Offline</span>';
                $('#viewAttendantOnline').html(onlineStatus);

                $('#viewAttendantCreatedAt').text(createdDate);
                $('#viewAttendantLastActivity').text(lastActivity);
            }

            // Edit User (opens modal)
            $(document).on('click', '.edit-user-btn', function () {
                const userId = $(this).data('id');
                // Get data from row or fetch if needed. 
                // Currently user data is attached to the button in renderUsersTable?
                // Let's check renderUsersTable... it likely has data-user attribute.
                // If not, we might need to fetch it.
                // Assuming data-user attribute exists based on standard practice here.
                let userData = $(this).data('user');

                if (userData) {
                    openCrudModal('edit', 'users', userData);
                } else {
                    // Fallback fetch if data attribute is missing
                    $.ajax({
                        url: `${baseUrl}users/get/${userId}`, // specific user endpoint
                        method: 'GET',
                        success: function (response) {
                            if (response.success) {
                                openCrudModal('edit', 'users', response.data);
                            }
                        }
                    });
                }
            });



            // Edit Staff (opens modal)
            $(document).on('click', '.edit-staff-btn', function () {
                const userId = $(this).data('id');
                // We fetch fresh data to ensure we have the latest status/role
                $.ajax({
                    url: `${baseUrl}attendants/get/${userId}`,
                    method: 'GET',
                    success: function (response) {
                        if (response.success) {
                            openCrudModal('edit', 'attendants', response.data);
                        } else {
                            showSuccessModal('Error', 'Error loading attendant data');
                        }
                    },
                    error: function () {
                        showSuccessModal('Error', 'Failed to load attendant data');
                    }
                });
            });



            // Toggle Assigned Area field based on role selection
            function toggleAssignedAreaField(userTypeId) {
                const assignedAreaField = $('#attendantAssignedArea').closest('.mb-3');
                const assignedAreaLabel = $('label[for="attendantAssignedArea"]');

                console.log('Toggling assigned area field for role:', userTypeId);

                if (userTypeId == 3) { // Admin role
                    if (!assignedAreaField.is(':hidden')) {
                        assignedAreaField.hide();
                        if (assignedAreaLabel.length) assignedAreaLabel.hide();
                        $('#attendantAssignedArea').val(''); // Clear the value
                        console.log('Hiding assigned area field for Admin role');
                    }
                } else { // Attendant role (2) or others
                    if (assignedAreaField.is(':hidden')) {
                        assignedAreaField.show();
                        if (assignedAreaLabel.length) assignedAreaLabel.show();
                        console.log('Showing assigned area field for Attendant role');
                    }
                }
            }

            // Role change event handler for both add and edit modals
            $(document).on('change', '#attendantUserTypeId', function () {
                const selectedRoleId = $(this).val();
                toggleAssignedAreaField(selectedRoleId);
            });

            // Delete Staff
            $(document).on('click', '.delete-staff-btn', function () {
                const userId = $(this).data('id');
                const userName = $(this).data('name');

                // Open delete confirmation modal
                openDeleteModal(userId, userName, 'users');
            });

            // If we reach here, we're on the users page and have initialized it
            // Return early to prevent other page scripts from running
            return;
        }

        // If NOT on users page, call original initPageScripts (for dashboard/analytics/logs)
        if (originalInitPageScripts && typeof originalInitPageScripts === 'function') {
            originalInitPageScripts();
        }
    };
} else {
    // If initPageScripts doesn't exist yet, create it
    window.initPageScripts = function () {
        // Check if we're on the users page
        if ($('#usersTable').length > 0) {
            console.log('Users page initialized (initPageScripts not defined yet)');
            // This shouldn't happen as dashboard.js should define it first
        }
    };
}

