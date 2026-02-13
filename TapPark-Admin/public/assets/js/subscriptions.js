/**
 * Subscription Management JavaScript
 * Handles all subscription plan CRUD operations, filtering, pagination
 */

// Extend initPageScripts for subscriptions
(function () {
    const baseUrl = window.BASE_URL || '';

    // Check if we're on the subscriptions page
    if (!$('#plansTable').length) {
        return; // Not on subscriptions page
    }

    console.log('Subscriptions page initialized');

    // Global variables
    let currentPage = 1;
    let perPage = window.APP_RECORDS_PER_PAGE || 25;
    let currentFilters = {};

    // Listen for global records per page updates
    document.addEventListener('app-records-per-page-updated', function (e) {
        const newPerPage = e.detail.perPage;
        console.log('Subscriptions page: Records per page updated to', newPerPage);
        perPage = newPerPage;
        $('#perPageSelect').val(newPerPage);
        currentPage = 1;
        loadPlans();
    });

    // Initialize shared filters for subscriptions
    if (typeof window.initSharedFilters === 'function') {
        window.initSharedFilters('subscriptions');
    }

    // Show export button for subscriptions
    $('#sharedExportBtn').css('display', 'block');

    // ====================================
    // FORMAT CURRENCY HELPER
    // ====================================
    function formatCurrency(amount) {
        if (isNaN(amount) || amount === null || amount === undefined) {
            return '0.00';
        }
        // Format with commas and 2 decimal places
        return parseFloat(amount).toFixed(2).replace(/\B(?=(\d{3})+(?!\d))/g, ',');
    }

    // Initialize
    loadPlans();

    // ====================================
    // LOAD PLANS
    // ====================================
    function loadPlans() {
        const params = new URLSearchParams({
            page: currentPage,
            per_page: perPage,
            ...currentFilters
        });

        $.ajax({
            url: `${baseUrl}subscriptions/list?${params}`,
            method: 'GET',
            beforeSend: function () {
                $('#planTableBody').html(`
                    <tr>
                        <td colspan="7" class="text-center py-5">
                            <div class="spinner-border text-primary" role="status"></div>
                            <p class="mt-2 text-muted">Loading plans...</p>
                        </td>
                    </tr>
                `);
            },
            success: function (response) {
                if (response.success) {
                    // Store all data for dynamic filtering
                    allPlansData = response.data;
                    renderPlansTable(response.data);
                    renderPagination(response.pagination);
                    updateStats(response.stats);
                    // Clear filters
                    currentFilters = {};
                    $('#sharedSearchInput').val('');
                    $('#sharedFilterPriceRange').val('');
                    $('#sharedFilterHoursRange').val('');
                    $('#sharedFilterPlanStatus').val('');
                }
            },
            error: function () {
                $('#planTableBody').html(`
                    <tr>
                        <td colspan="7" class="text-center py-5 text-danger">
                            <i class="fas fa-exclamation-triangle fa-3x mb-3"></i>
                            <p>Error loading plans. Please try again.</p>
                        </td>
                    </tr>
                `);
            }
        });
    }

    // ====================================
    // RENDER PLANS TABLE
    // ====================================
    function renderPlansTable(plans) {
        let html = '';

        if (!plans || plans.length === 0) {
            html = `
                <tr>
                    <td colspan="7" class="text-center py-5">
                        <i class="fas fa-crown fa-3x text-muted mb-3"></i>
                        <p class="text-muted">No plans found</p>
                    </td>
                </tr>
            `;
        } else {
            plans.forEach((plan, index) => {
                const startIdx = (currentPage - 1) * perPage + 1;
                html += `
                    <tr data-plan-id="${plan.plan_id}">
                        <td>#${startIdx + index}</td>
                        <td><strong>${escapeHtml(plan.plan_name || '-')}</strong></td>
                        <td><span class="badge bg-success">₱${formatCurrency(parseFloat(plan.cost || 0))}</span></td>
                        <td><span class="badge bg-info">${plan.number_of_hours || 0} hrs</span></td>
                        <td>
                            <span class="badge bg-primary">${plan.total_subscribers || 0} total</span>
                            <span class="badge bg-success ms-1">${plan.active_subscribers || 0} active</span>
                        </td>
                        <td>${plan.description ? escapeHtml(plan.description.substring(0, 50)) + '...' : 'N/A'}</td>
                        <td>
                            <div class="btn-group" role="group">
                                <button class="btn btn-sm btn-outline-primary view-plan-btn" 
                                        data-id="${plan.plan_id}" 
                                        title="View"
                                        style="border-color: #800000; color: #800000;">
                                    <i class="fas fa-eye"></i>
                                </button>
                                <button class="btn btn-sm btn-outline-secondary edit-plan-btn" 
                                        data-id="${plan.plan_id}"
                                        data-plan='${JSON.stringify(plan).replace(/"/g, '&quot;')}'
                                        title="Edit"
                                        style="border-color: var(--text-color); color: var(--text-color);">
                                    <i class="fas fa-pen"></i>
                                </button>
                                <button class="btn btn-sm btn-outline-danger delete-plan-btn" 
                                        data-id="${plan.plan_id}" 
                                        data-name="${escapeHtml(plan.plan_name)}" 
                                        data-total="${plan.total_subscribers || 0}"
                                        data-active="${plan.active_subscribers || 0}"
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

        $('#planTableBody').html(html);
    }

    // ====================================
    // RENDER PAGINATION
    // ====================================
    function renderPagination(pagination) {
        const { current_page, per_page, total, total_pages, showing_from, showing_to } = pagination;

        currentPage = parseInt(current_page, 10);
        perPage = parseInt(per_page, 10);
        $('#perPageSelect').val(perPage);

        $('#paginationInfo').html(`Showing ${showing_from || 0} to ${showing_to || 0} of ${total} plans`);
        $('#tableInfo').html(`${total} total plans`);

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
            stats = { total_plans: 0, total_subscribers: 0, active_subscribers: 0, total_revenue: 0 };
        }

        const totalPlans = parseInt(stats.total_plans) || 0;
        const totalSubscribers = parseInt(stats.total_subscribers) || 0;
        const activeSubscribers = parseInt(stats.active_subscribers) || 0;
        const totalRevenue = parseFloat(stats.total_revenue) || 0;

        const totalPlansEl = document.getElementById('statTotalPlans');
        const totalSubscribersEl = document.getElementById('statTotalSubscribers');
        const activeSubscribersEl = document.getElementById('statActiveSubscribers');
        const totalRevenueEl = document.getElementById('statTotalRevenue');

        if (totalPlansEl) {
            totalPlansEl.textContent = totalPlans;
            totalPlansEl.classList.add('text-white');
        }
        if (totalSubscribersEl) {
            totalSubscribersEl.textContent = totalSubscribers;
            totalSubscribersEl.classList.add('text-white');
        }
        if (activeSubscribersEl) {
            activeSubscribersEl.textContent = activeSubscribers;
            activeSubscribersEl.classList.add('text-white');
        }
        if (totalRevenueEl) {
            totalRevenueEl.textContent = '₱' + formatCurrency(totalRevenue);
            totalRevenueEl.classList.add('text-white');
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
    // FILTER VISIBILITY MANAGEMENT
    // ====================================

    function updateFilterVisibility() {
        // Always show filter actions to allow resetting to default
        $('#filterActionsContainer').removeClass('filter-actions-hidden').addClass('filter-actions-visible');
    }

    function checkActiveFilters() {
        const search = $('#sharedSearchInput').val().trim();
        const priceRange = $('#sharedFilterPriceRange').val();
        const hoursRange = $('#sharedFilterHoursRange').val();

        // Return true if ANY filter has a value (active filters)
        return !!(search || priceRange || hoursRange);
    }

    // Update filter visibility on any filter change
    $('#sharedSearchInput, #sharedFilterPriceRange, #sharedFilterHoursRange').off('input.filter-visibility change.filter-visibility').on('input.filter-visibility change.filter-visibility', function () {
        updateFilterVisibility();
    });

    // Update filter visibility on document ready
    $(document).ready(function () {
        setTimeout(updateFilterVisibility, 200);
    });

    // Export to CSV - New button
    $('#exportSubscriptionsBtn').off('click').on('click', function () {
        const params = buildExportParams();
        const exportUrl = baseUrl + 'subscriptions/export' + (params ? '?' + params : '');
        window.location.href = exportUrl;
    });

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
            loadPlans();
        }
        return false;
    });

    // Per page change
    $('#perPageSelect').off('change').on('change', function () {
        perPage = parseInt($(this).val());
        currentPage = 1;
        loadPlans();
    });

    // ====================================
    // DYNAMIC FILTERING (No Reload)
    // ====================================

    // Store all plans data for filtering
    let allPlansData = [];

    // Dynamic filter function
    function applyDynamicFilters() {
        const search = $('#sharedSearchInput').val().trim().toLowerCase();
        const priceRange = $('#sharedFilterPriceRange').val();
        const hoursRange = $('#sharedFilterHoursRange').val();

        const filteredPlans = allPlansData.filter(plan => {
            // Search filter
            if (search) {
                // Check for exact plan_id match first (prioritize exact ID match)
                if (search.match(/^\d+$/) && plan.plan_id.toString() === search) {
                    return true; // Exact numeric ID match - include immediately
                }

                // If not exact ID match, then search in other fields (plan_name, description)
                const searchText = `${plan.plan_name} ${plan.description}`.toLowerCase();

                // For numeric searches, don't match partial plan_id in other fields
                if (search.match(/^\d+$/)) {
                    return false; // No partial numeric ID matching in non-ID fields
                }

                // For text searches, allow normal contains search in other fields
                if (!searchText.includes(search)) {
                    return false;
                }
            }

            // Price range filter
            if (priceRange) {
                const cost = parseFloat(plan.cost || 0);
                if (priceRange === '0-100' && (cost < 0 || cost > 100)) return false;
                if (priceRange === '100-500' && (cost < 100 || cost > 500)) return false;
                if (priceRange === '500-1000' && (cost < 500 || cost > 1000)) return false;
                if (priceRange === '1000-5000' && (cost < 1000 || cost > 5000)) return false;
                if (priceRange === '5000+' && cost < 5000) return false;
            }

            // Hours range filter
            if (hoursRange) {
                const hours = parseInt(plan.number_of_hours || 0);
                if (hoursRange === '1-10' && (hours < 1 || hours > 10)) return false;
                if (hoursRange === '10-50' && (hours < 10 || hours > 50)) return false;
                if (hoursRange === '50-100' && (hours < 50 || hours > 100)) return false;
                if (hoursRange === '100-500' && (hours < 100 || hours > 500)) return false;
                if (hoursRange === '500+' && hours < 500) return false;
            }

            return true;
        });

        // Re-render table with filtered data
        renderPlansTable(filteredPlans);

        // Update pagination info
        const total = filteredPlans.length;
        $('#paginationInfo').html(`Showing ${Math.min(1, total)} to ${Math.min(total, total)} of ${total} plans`);
        $('#tableInfo').html(`${total} total plans`);

        // Hide pagination controls for filtered results
        $('#paginationControls').html('');
    }

    // ====================================
    // EVENT HANDLERS - FILTERS (Dynamic)
    // ====================================

    // Search input handler - remove automatic filtering
    $('#sharedSearchInput').off('input').on('input', function () {
        updateFilterVisibility();
    });

    // Filter changes - remove automatic filtering
    $('#sharedFilterPriceRange, #sharedFilterHoursRange').off('change').on('change', function () {
        updateFilterVisibility();
    });

    // Apply Filter button handler
    $('#sharedApplyFiltersBtn').off('click').on('click', function () {
        currentFilters.search = $('#sharedSearchInput').val().trim();
        currentFilters.price_range = $('#sharedFilterPriceRange').val();
        currentFilters.hours_range = $('#sharedFilterHoursRange').val();
        currentPage = 1;
        applyDynamicFilters();
        updateFilterVisibility();
    });

    // Clear filters
    $('#sharedClearFiltersBtn').off('click').on('click', function () {
        $('#sharedSearchInput').val('');
        $('#sharedFilterPriceRange, #sharedFilterHoursRange').val('');
        currentFilters = {};
        currentPage = 1;

        // Use dynamic filtering instead of reload
        if (allPlansData.length > 0) {
            renderPlansTable(allPlansData);
            renderPagination({ current_page: 1, per_page: perPage, total: allPlansData.length, total_pages: 1, showing_from: 1, showing_to: allPlansData.length });
            $('#paginationInfo').html(`Showing 1 to ${allPlansData.length} of ${allPlansData.length} plans`);
            $('#tableInfo').html(`${allPlansData.length} total plans`);
        } else {
            loadPlans();
        }

        updateFilterVisibility();
    });

    // Refresh
    $('#sharedRefreshBtn').off('click').on('click', function () {
        loadPlans();
    });

    // Build export parameters from current filters
    function buildExportParams() {
        const params = [];
        const search = $('#sharedSearchInput').val().trim();
        const priceRange = $('#sharedFilterPriceRange').val();
        const hoursRange = $('#sharedFilterHoursRange').val();

        if (search) params.push('search=' + encodeURIComponent(search));
        if (priceRange) params.push('price_range=' + encodeURIComponent(priceRange));
        if (hoursRange) params.push('hours_range=' + encodeURIComponent(hoursRange));

        return params.join('&');
    }

    // ====================================
    // TABLE SORTING FUNCTIONALITY
    // ====================================

    // Initialize sorting for subscriptions table
    function initializeSubscriptionTableSorting() {
        let sortOrder = {}; // Store sort order for each column

        $('#plansTable th.sortable').off('click').on('click', function () {
            const $th = $(this);
            const column = $th.data('column');
            const $table = $('#plansTable');
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
                if (column === 'plan_id' || column === 'cost' || column === 'hours' || column === 'subscribers') {
                    // Clean numeric values (remove currency symbols, text, etc.)
                    const aNum = parseFloat(aValue.replace(/[^0-9.-]/g, '')) || 0;
                    const bNum = parseFloat(bValue.replace(/[^0-9.-]/g, '')) || 0;
                    return sortOrder[column] === 'asc'
                        ? aNum - bNum
                        : bNum - aNum;
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
    initializeSubscriptionTableSorting();

    // ====================================
    // DYNAMIC TABLE FUNCTIONS
    // ====================================

    // Add plan to table dynamically
    function addPlanToTable(planData) {
        // Since we're prepend-ing, the sequence numbering might be complex. 
        // For simplicity and consistency with other sections, we'll reload the page or re-render if needed, 
        // but for immediate display, let's use a temporary '#' marker or similar.
        // Actually, the best approach is to reload the current page.
        loadPlans();
    }

    // Update plan in table dynamically
    function updatePlanInTable(planData) {
        const row = $(`#planTableBody tr[data-plan-id="${planData.plan_id}"]`);
        if (row.length) {
            // No longer updating the ID column as it's sequential
            row.find('td:eq(1)').html(`<strong>${escapeHtml(planData.plan_name || '-')}</strong>`);
            row.find('td:eq(2)').html(`<span class="badge bg-success">₱${formatCurrency(parseFloat(planData.cost || 0))}</span>`);
            row.find('td:eq(3)').html(`<span class="badge bg-info">${planData.number_of_hours || 0} hrs</span>`);
            row.find('td:eq(4)').html(`
                <span class="badge bg-primary">${planData.total_subscribers || 0} total</span>
                <span class="badge bg-success ms-1">${planData.active_subscribers || 0} active</span>
            `);
            row.find('td:eq(5)').text(planData.description ? escapeHtml(planData.description.substring(0, 50)) + '...' : 'N/A');

            // Update button data attributes
            row.find('.edit-plan-btn').data('id', planData.plan_id).data('plan', JSON.stringify(planData).replace(/"/g, '&quot;'));
            row.find('.delete-plan-btn')
                .data('id', planData.plan_id)
                .data('name', planData.plan_name)
                .attr('data-total', planData.total_subscribers || 0)
                .attr('data-active', planData.active_subscribers || 0);
        }
    }

    // Remove plan from table dynamically
    function removePlanFromTable(planId) {
        $(`#planTableBody tr[data-plan-id="${planId}"]`).fadeOut(300, function () {
            $(this).remove();
        });
    }

    // ====================================
    // SUCCESS MODAL FUNCTION
    // ====================================
    // showSuccessModal moved to scripts.php

    // ====================================
    // ADD PLAN
    // ====================================
    $('#addPlanBtn').off('click').on('click', function () {
        // Blur any active element
        if (document.activeElement && document.activeElement.blur) {
            document.activeElement.blur();
        }

        clearValidationErrors();

        // Reset form
        $('#crudForm')[0].reset();
        $('#crudEntityId').val('');
        $('#crudAction').val('add');
        $('#crudEntityType').val('subscriptions');

        // Reset footer to normal
        $('#crudConfirmFooter').hide();
        $('#crudNormalFooter').show();

        // Set modal mode
        $('#crudFormModal').removeClass('mode-edit').addClass('mode-add');

        // Update title
        $('#crudModalIcon').removeClass().addClass('fas fa-crown me-2');
        $('#crudModalTitleText').text('Add New Plan');
        $('#crudSubmitText').text('Add');

        // Hide all entity fields, show only subscriptions
        $('.entity-fields').hide();
        $('.fields-subscriptions').show();

        // Show modal
        const bsModal = bootstrap.Modal.getOrCreateInstance($('#crudFormModal')[0], {
            backdrop: true,
            keyboard: true,
            focus: false
        });
        bsModal.show();

        // Focus on first field
        setTimeout(() => {
            $('#planName').focus();
        }, 500);
    });

    // ====================================
    // VIEW PLAN
    // ====================================
    $(document).off('click.subscriptions', '.view-plan-btn').on('click.subscriptions', '.view-plan-btn', function () {
        const planId = $(this).data('id');

        if (!planId) {
            console.error('Plan ID is missing');
            return;
        }

        // Fetch plan data and show in view modal
        $.ajax({
            url: `${baseUrl}subscriptions/get/${planId}`,
            method: 'GET',
            success: function (response) {
                if (response.success) {
                    openViewPlanModal(planId, response.data, 'subscriptions');
                }
            },
            error: function (xhr) {
                console.error('Error fetching plan:', xhr);
                showSuccessModal('Error', 'Error loading plan details. Please try again.');
            }
        });
    });

    // ====================================
    // OPEN VIEW PLAN MODAL
    // ====================================
    window.openViewPlanModal = function (planId, planData, entityType) {
        // Only handle if it's subscriptions
        if (entityType && entityType !== 'subscriptions') {
            return; // Let other handlers take care of it
        }

        entityType = entityType || 'subscriptions';
        const modal = $('#viewDetailsModal');

        // Blur any active element
        if (document.activeElement && document.activeElement.blur) {
            document.activeElement.blur();
        }

        // Store data for edit button
        modal.data('plan-id', planId);
        modal.data('plan-data', planData);

        // Update title
        $('#viewModalTitleText').text('Plan Details');

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

        // Display plan data
        setTimeout(function () {
            displayPlanViewData(planData);
        }, 300);

        // Handle Edit button click in view modal - use immediate binding
        $('#viewEditBtn').off('click.subscriptions').on('click.subscriptions', function (e) {
            e.stopImmediatePropagation();
            e.preventDefault();

            // Blur the button to prevent aria-hidden warning
            if (document.activeElement && document.activeElement.blur) {
                document.activeElement.blur();
            }

            // Close view modal
            bsModal.hide();

            // Small delay to ensure modal is closed
            setTimeout(function () {
                // Open edit modal
                openEditPlanModal(planId, planData);
            }, 300);

            return false;
        });
    };

    // ====================================
    // DISPLAY PLAN VIEW DATA
    // ====================================
    function displayPlanViewData(plan) {
        $('#viewDetailsLoading').hide();
        $('.view-content').hide();
        $('.view-subscriptions').show();

        // Calculate cost per hour
        const cost = parseFloat(plan.cost || 0);
        const hours = parseFloat(plan.number_of_hours || 0);
        const costPerHour = hours > 0 ? (cost / hours) : 0;

        // Update fields
        $('#viewPlanIdBadge').text('Plan ID: ' + (plan.plan_id || '-'));
        $('#viewPlanName').text(plan.plan_name || '-');
        $('#viewPlanPrice').text('₱' + formatCurrency(cost));
        $('#viewPlanHours').text((hours || 0));
        $('#viewPlanCostPerHour').text('₱' + formatCurrency(costPerHour));
        $('#viewPlanSubscribers').text(plan.total_subscribers || 0);
        $('#viewPlanActive').text(plan.active_subscribers || 0);
        $('#viewPlanDescription').text(plan.description || 'No description provided');
    }

    // ====================================
    // EDIT PLAN
    // ====================================
    $(document).off('click.subscriptions', '.edit-plan-btn').on('click.subscriptions', '.edit-plan-btn', function () {
        // Blur any active element
        if (document.activeElement && document.activeElement.blur) {
            document.activeElement.blur();
        }

        clearValidationErrors();

        const planId = $(this).data('id');

        if (!planId) {
            console.error('Plan ID is missing');
            return;
        }

        let planData = $(this).data('plan');

        // Try to parse planData if it's a string
        if (typeof planData === 'string') {
            try {
                planData = JSON.parse(planData.replace(/&quot;/g, '"'));
            } catch (e) {
                planData = null;
            }
        }

        // If we have the data already, use it
        if (planData && planData.plan_id) {
            openEditPlanModal(planId, planData);
        } else {
            // Fetch from server
            $.ajax({
                url: `${baseUrl}subscriptions/get/${planId}`,
                method: 'GET',
                success: function (response) {
                    if (response.success) {
                        openEditPlanModal(planId, response.data);
                    } else {
                        showSuccessModal('Error', 'Error loading plan data. Please try again.');
                    }
                },
                error: function (xhr) {
                    console.error('Error fetching plan:', xhr);
                    showSuccessModal('Error', 'Error loading plan details. Please try again.');
                }
            });
        }
    });

    function openEditPlanModal(planId, planData) {
        // Reset form
        $('#crudForm')[0].reset();
        $('#crudEntityId').val(planId);
        $('#crudAction').val('edit');
        $('#crudEntityType').val('subscriptions');

        // Reset footer to normal
        $('#crudConfirmFooter').hide();
        $('#crudNormalFooter').show();

        // Set modal mode
        $('#crudFormModal').removeClass('mode-add').addClass('mode-edit');

        // Update title
        $('#crudModalIcon').removeClass().addClass('fas fa-edit me-2');
        $('#crudModalTitleText').text('Edit Plan');
        $('#crudSubmitText').text('Update');

        // Hide all entity fields, show only subscriptions
        $('.entity-fields').hide();
        $('.fields-subscriptions').show();

        // Fill form
        $('#planName').val(planData.plan_name || '');
        $('#planCost').val(planData.cost || 0);
        $('#planHours').val(planData.number_of_hours || 0);
        $('#planDescription').val(planData.description || '');

        // Show modal
        const bsModal = bootstrap.Modal.getOrCreateInstance($('#crudFormModal')[0], {
            backdrop: true,
            keyboard: true,
            focus: false
        });
        bsModal.show();
    }

    // ====================================
    // DELETE PLAN
    // ====================================
    $(document).off('click.subscriptions', '.delete-plan-btn').on('click.subscriptions', '.delete-plan-btn', function () {
        // Blur any active element
        if (document.activeElement && document.activeElement.blur) {
            document.activeElement.blur();
        }

        const planId = $(this).data('id');
        const planName = $(this).data('name');
        const totalSubscribers = parseInt($(this).data('total')) || 0;
        const activeSubscribers = parseInt($(this).data('active')) || 0;

        if (!planId) {
            console.error('Plan ID is missing');
            return;
        }

        // Check if plan can be deleted (client-side check for better UX)
        if (totalSubscribers > 0) {
            showSuccessModal('Cannot Delete Plan', `Plan "${planName}" cannot be deleted because it has ${totalSubscribers} subscriber(s) (${activeSubscribers} active).`);
            return;
        }

        // Use the global delete modal function
        if (typeof window.openDeleteModal === 'function') {
            window.openDeleteModal(planId, planName, 'subscriptions');
        } else {
            // Fallback if function doesn't exist
            if (confirm(`Are you sure you want to delete plan "${planName}" ? `)) {
                $.ajax({
                    url: `${baseUrl} subscriptions / delete/${planId}`,
                    method: 'POST',
                    data: {},
                    success: function (response) {
                        if (response.success) {
                            showSuccessModal('Plan Deleted Successfully', 'Plan has been removed from the system.');
                            // Remove from table dynamically instead of reloading
                            removePlanFromTable(planId);
                            if (response.stats) {
                                updateStats(response.stats);
                            }
                        } else {
                            showSuccessModal('Delete Failed', response.message || 'Failed to delete plan');
                        }
                    },
                    error: function (xhr) {
                        const errorMsg = xhr.responseJSON?.message || 'Error deleting plan. Please try again.';
                        showSuccessModal('Delete Error', errorMsg);
                    }
                });
            }
        }
    });

    // Extend the global confirmDelete to handle subscriptions
    // Store original if it exists
    const originalConfirmDelete = window.confirmDelete;

    // Override confirmDelete to handle both users and subscriptions
    window.confirmDelete = function () {
        const entity = $('#deleteEntityType').val();

        // Handle subscriptions
        if (entity === 'subscriptions') {
            const planId = $('#deleteEntityId').val();
            const deleteBtn = $('#confirmDeleteBtn');
            const originalText = deleteBtn.html();

            if (!planId) {
                showSuccessModal('Error', 'Plan ID is missing');
                return;
            }

            deleteBtn.prop('disabled', true).html('<span class="spinner-border spinner-border-sm me-2"></span>Deleting...');

            ajaxWithCSRF(`${baseUrl}subscriptions/delete/${planId}`, {
                method: 'POST',
                data: {},
                success: function (response) {
                    if (document.activeElement) {
                        document.activeElement.blur();
                    }

                    bootstrap.Modal.getInstance($('#deleteConfirmModal')[0]).hide();

                    if (response.success) {
                        showSuccessModal('Plan Deleted Successfully', 'Plan has been removed from the system.');
                        // Remove from table dynamically instead of reloading
                        removePlanFromTable(planId);
                        if (response.stats) {
                            updateStats(response.stats);
                        }
                    } else {
                        showSuccessModal('Delete Failed', response.message || 'Failed to delete plan');
                    }
                },
                error: function (xhr) {
                    if (document.activeElement) {
                        document.activeElement.blur();
                    }

                    bootstrap.Modal.getInstance($('#deleteConfirmModal')[0]).hide();

                    const errorMsg = xhr.responseJSON?.message || 'Error deleting plan. Please try again.';
                    showSuccessModal('Delete Error', errorMsg);
                },
                complete: function () {
                    deleteBtn.prop('disabled', false).html(originalText);
                }
            });
            return;
        }

        // Call original handler for other entity types (users, etc.)
        if (originalConfirmDelete && typeof originalConfirmDelete === 'function') {
            originalConfirmDelete();
        }
    };

    // ====================================
    // FORM SUBMIT HANDLER
    // ====================================
    $('#crudSubmitBtn').off('click.subscriptions').on('click.subscriptions', function (e) {
        const entity = $('#crudEntityType').val();

        if (entity !== 'subscriptions') {
            return;
        }

        // Stop propagation to prevent other handlers
        e.stopImmediatePropagation();

        clearValidationErrors();

        const action = $('#crudAction').val();

        // Get form data for validation
        const formData = {
            plan_name: $('#planName').val().trim(),
            cost: $('#planCost').val(),
            number_of_hours: $('#planHours').val(),
            description: $('#planDescription').val().trim()
        };

        // Client-side validation
        let hasErrors = false;
        const errors = {};

        if (!formData.plan_name) {
            errors.plan_name = 'Plan name is required';
            hasErrors = true;
        } else if (formData.plan_name.length < 3) {
            errors.plan_name = 'Plan name must be at least 3 characters';
            hasErrors = true;
        }

        if (!formData.cost || parseFloat(formData.cost) < 0) {
            errors.cost = 'Cost must be a valid number (0 or greater)';
            hasErrors = true;
        }

        if (!formData.number_of_hours || parseInt(formData.number_of_hours) < 1) {
            errors.number_of_hours = 'Hours must be at least 1';
            hasErrors = true;
        }

        // Check for duplicate plan names (client-side validation)
        if (action === 'add' && formData.plan_name) {
            const planName = formData.plan_name.toLowerCase();
            const existingPlans = $('#planTableBody tr').map(function () {
                const nameText = $(this).find('td:nth-child(2)').text().toLowerCase();
                return nameText;
            }).get();

            if (existingPlans.includes(planName)) {
                errors.plan_name = 'A plan with this name already exists';
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
                <div class="col-md-6"><strong>Plan Name:</strong></div>
                <div class="col-md-6">${escapeHtml(formData.plan_name)}</div>
            </div>
            <div class="row">
                <div class="col-md-6"><strong>Cost:</strong></div>
                <div class="col-md-6">₱${formatCurrency(parseFloat(formData.cost || 0))}</div>
            </div>
            <div class="row">
                <div class="col-md-6"><strong>Hours Included:</strong></div>
                <div class="col-md-6">${formData.number_of_hours || 0} hrs</div>
            </div>
            <div class="row">
                <div class="col-md-6"><strong>Cost per Hour:</strong></div>
                <div class="col-md-6">₱${formatCurrency(parseFloat(formData.cost || 0) / parseFloat(formData.number_of_hours || 1))}/hr</div>
            </div>
            ${formData.description ? `<div class="row"><div class="col-md-6"><strong>Description:</strong></div><div class="col-md-6">${escapeHtml(formData.description.substring(0, 50))}${formData.description.length > 50 ? '...' : ''}</div></div>` : ''}
        `;

        // Change to confirmation view
        const message = action === 'add'
            ? 'Are you sure you want to add this plan?'
            : 'Are you sure you want to update this plan?';
        const description = action === 'add'
            ? `You are about to add "${formData.plan_name}" to the system.`
            : `You are about to update plan "${formData.plan_name}".`;

        $('#crudConfirmTitle').text('Confirm ' + (action === 'add' ? 'Add Plan' : 'Update Plan'));
        $('#crudConfirmMessage').text(message);
        $('#crudConfirmDescription').text(description);
        $('#crudConfirmSummary').html(summaryHtml);
        $('#crudConfirmYesText').text(action === 'add' ? 'Yes, Add Plan' : 'Yes, Update Plan');

        // Hide form section, show confirmation section
        $('#crudFormSection').hide();
        $('#crudConfirmSection').show();

        // Hide normal footer, show confirmation footer
        $('#crudNormalFooter').hide();
        $('#crudConfirmFooter').show();
    });

    // Cancel confirmation (No button)
    $('#crudConfirmCancelBtn').off('click.subscriptions').on('click.subscriptions', function () {
        // Check if we are on the users management page - if so, don't let subscriptions.js handle it
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

    // Confirm button (Yes button) - handle subscriptions
    // Use direct binding instead of document delegation to ensure it runs first
    $('#crudConfirmYesBtn').off('click.subscriptions').on('click.subscriptions', function (e) {
        // Check if we are on the users management page - if so, don't let subscriptions.js handle it
        if (window.location.pathname.includes('/users')) {
            return;
        }

        // Check entity type FIRST before anything else
        const entity = $('#crudEntityType').val();

        if (entity !== 'subscriptions') {
            return; // Let users.js handle it
        }

        // Stop propagation IMMEDIATELY to prevent other handlers
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

        const url = action === 'add'
            ? `${baseUrl}subscriptions/create`
            : `${baseUrl}subscriptions/update/${id}`;

        const method = 'POST';

        // Show loading state
        const confirmBtn = $('#crudConfirmYesBtn');
        const originalText = confirmBtn.html();
        confirmBtn.prop('disabled', true).html('<span class="spinner-border spinner-border-sm me-2"></span>Processing...');

        console.log('Subscriptions: Submitting to', url, 'with data:', formData);

        $.ajax({
            url: url,
            method: method,
            data: formData,
            success: function (response) {
                console.log('Subscriptions: Success response', response);
                if (response.success) {
                    // Close form modal
                    const bsModal = bootstrap.Modal.getInstance($('#crudFormModal')[0]);
                    bsModal.hide();

                    // Reset footer
                    $('#crudConfirmFooter').hide();
                    $('#crudNormalFooter').show();
                    showSuccessModal(action === 'add' ? 'Plan Added Successfully' : 'Plan Updated Successfully',
                        action === 'add'
                            ? `Plan "${formData.plan_name}" has been added to the system.`
                            : `Plan "${formData.plan_name}" has been updated successfully.`);

                    // Update table dynamically instead of reloading
                    if (action === 'add' && response.data) {
                        addPlanToTable(response.data);
                    } else if (action === 'edit' && response.data) {
                        updatePlanInTable(response.data);
                    }

                    if (response.stats) {
                        updateStats(response.stats);
                    }

                    // Clear stored data after successful operation
                    delete window.pendingCrudFormData;
                    delete window.pendingCrudAction;
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
                console.error('Subscriptions: Error response', xhr);
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
                // Only clear stored data on success, not on error
                // This allows retry on failed requests
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

    // Reset footer when modal is closed
    $('#crudFormModal').off('hidden.bs.modal').on('hidden.bs.modal', function () {
        // Show form section, hide confirmation section
        $('#crudFormSection').show();
        $('#crudConfirmSection').hide();

        $('#crudConfirmFooter').hide();
        $('#crudNormalFooter').show();
        clearValidationErrors();
    });

    // Extend window.initPageScripts
    const originalInitPageScripts = window.initPageScripts;
    window.initPageScripts = function () {
        if (originalInitPageScripts) {
            originalInitPageScripts();
        }
        // Subscriptions initialization is already done above
    };
})();

