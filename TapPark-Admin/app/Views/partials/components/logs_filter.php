<?php
/**
 * Logs Filter Component
 * 
 * Filter component specifically for activity logs page
 * Includes: Action Type, Date Range, Search, Per Page
 */

// Get filter values
$actionType = $filters['action_type'] ?? 'all';
$startDate = $filters['start_date'] ?? null;
$endDate = $filters['end_date'] ?? null;
$search = $filters['search'] ?? '';
$perPage = $filters['per_page'] ?? session('app_settings')['records_per_page'] ?? 25;
?>

<div class="card shadow-sm mb-4" id="logsFilterCard">
    <div class="card-body" style="padding: 1.5rem;">
        <div class="compact-filter-row">
            <div class="compact-filter-field compact-filter-search">
                <label class="form-label">
                    <i class="fas fa-search me-2"></i>Search Description
                </label>
                <input type="text"
                    class="form-control"
                    id="logsSearchInput"
                    placeholder="Search logs..."
                    value="<?= esc($search) ?>">
            </div>

            <div class="compact-filter-field compact-filter-medium">
                <label class="form-label">
                    <i class="fas fa-bolt me-2"></i>Action Type
                </label>
                <select class="form-select" id="logsActionType">
                    <option value="all" <?= $actionType === 'all' ? 'selected' : '' ?>>All Actions</option>
                    <option value="NULL" <?= $actionType === 'NULL' ? 'selected' : '' ?>>General Activity</option>
                    <?php if (!empty($actionTypes)): ?>
                        <?php foreach ($actionTypes as $type): ?>
                            <?php if (!empty($type['action_type'])): ?>
                                <option value="<?= esc($type['action_type']) ?>"
                                    <?= $actionType === $type['action_type'] ? 'selected' : '' ?>>
                                    <?= esc($type['action_type']) ?>
                                </option>
                            <?php endif; ?>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </select>
            </div>

            <div class="compact-filter-field compact-filter-small">
                <label class="form-label">
                    <i class="fas fa-calendar-alt me-2"></i>Start Date
                </label>
                <input type="date"
                    class="form-control"
                    id="logsStartDate"
                    value="<?= esc($startDate ?? '') ?>">
            </div>

            <div class="compact-filter-field compact-filter-small">
                <label class="form-label">
                    <i class="fas fa-calendar-alt me-2"></i>End Date
                </label>
                <input type="date"
                    class="form-control"
                    id="logsEndDate"
                    value="<?= esc($endDate ?? '') ?>">
            </div>

            <div class="compact-filter-field" style="flex: 0.55 1 110px;">
                <label class="form-label">
                    <i class="fas fa-list me-2"></i>Per Page
                </label>
                <select class="form-select" id="logsPerPage">
                    <?php $globalPerPage = session('app_settings')['records_per_page'] ?? 25; ?>
                    <option value="10" <?= $perPage == 10 || ($perPage == 25 && $globalPerPage == 10) ? 'selected' : '' ?>>10</option>
                    <option value="25" <?= $perPage == 25 && $globalPerPage == 25 ? 'selected' : '' ?>>25</option>
                    <option value="50" <?= $perPage == 50 || ($perPage == 25 && $globalPerPage == 50) ? 'selected' : '' ?>>50</option>
                    <option value="100" <?= $perPage == 100 || ($perPage == 25 && $globalPerPage == 100) ? 'selected' : '' ?>>100</option>
                </select>
            </div>

            <div class="compact-filter-actions">
            <button class="btn btn-primary btn-sm" id="logsApplyFilterBtn">
                <i class="fas fa-filter me-1"></i>Apply Filter
            </button>
            <button class="btn btn-secondary btn-sm" id="logsClearFilterBtn">
                <i class="fas fa-times me-1"></i>Clear Filters
            </button>
            <button class="btn btn-success btn-sm" id="logsExportBtn">
                <i class="fas fa-file-excel me-1"></i>Export to CSV
            </button>
            </div>
        </div>
    </div>
</div>

<style>
/* Logs filter action buttons aligned with other filter rows */
#logsFilterCard .compact-filter-actions .btn {
    border-radius: 8px !important;
    padding: 0.5rem 1rem !important;
    min-width: 120px;
    font-weight: 500;
}
</style>

<script>
// Logs Filter Handler
$(document).ready(function() {
    // Apply filter button
    $('#logsApplyFilterBtn').on('click', function() {
        loadLogsWithFilter();
    });

    // Listen for global records per page updates
    document.addEventListener('app-records-per-page-updated', function (e) {
        const newPerPage = e.detail.perPage;
        console.log('Logs page: Records per page updated to', newPerPage);
        $('#logsPerPage').val(newPerPage);
        loadLogsWithFilter(1);
    });
    
    // Clear filter button
    $('#logsClearFilterBtn').on('click', function() {
        $('#logsSearchInput').val('');
        $('#logsActionType').val('all');
        $('#logsStartDate').val('');
        $('#logsEndDate').val('');
        $('#logsPerPage').val('25');
        loadLogsWithFilter();
    });
    
    // Enter key on search input
    $('#logsSearchInput').on('keypress', function(e) {
        if (e.which === 13) { // Enter key
            loadLogsWithFilter();
        }
    });
    
    // Export button
    $('#logsExportBtn').on('click', function() {
        const params = buildLogsFilterParams();
        const exportUrl = BASE_URL + 'logs/export' + (params ? '?' + params : '');
        window.location.href = exportUrl;
    });
    
    // Build filter parameters
    function buildLogsFilterParams() {
        const params = [];
        const search = $('#logsSearchInput').val().trim();
        const actionType = $('#logsActionType').val();
        const startDate = $('#logsStartDate').val();
        const endDate = $('#logsEndDate').val();
        const perPage = $('#logsPerPage').val();
        
        if (search) params.push('search=' + encodeURIComponent(search));
        if (actionType && actionType !== 'all') params.push('action_type=' + encodeURIComponent(actionType));
        if (startDate) params.push('start_date=' + encodeURIComponent(startDate));
        if (endDate) params.push('end_date=' + encodeURIComponent(endDate));
        params.push('per_page=' + encodeURIComponent(perPage));
        
        return params.join('&');
    }
    
    // Load logs with filters
    window.loadLogsWithFilter = function(page = 1) {
        const params = buildLogsFilterParams();
        const url = BASE_URL + 'logs/api?page=' + page + (params ? '&' + params : '');
        
        // Show loading
        $('#logsTableBody').html('<tr><td colspan="6" class="text-center py-4"><div class="spinner-border text-primary" role="status"><span class="visually-hidden">Loading...</span></div></td></tr>');
        
        $.ajax({
            url: url,
            type: 'GET',
            dataType: 'json',
            success: function(response) {
                if (response.success && response.data) {
                    if (typeof window.updateLogsTable === 'function') {
                        window.updateLogsTable(response.data.logs);
                    }
                    if (typeof window.updateLogsStats === 'function') {
                        window.updateLogsStats(response.data.summary);
                    }
                    if (typeof window.updateActiveUsers === 'function') {
                        console.log('📊 Active Users from API:', response.data.activeUsers);
                        console.log('📊 Active Users count:', response.data.activeUsers ? response.data.activeUsers.length : 0);
                        if (response.data.activeUsers && response.data.activeUsers.length > 0) {
                            console.log('📊 First user sample:', response.data.activeUsers[0]);
                        }
                        window.updateActiveUsers(response.data.activeUsers);
                    }
                    if (typeof window.updateLogsPagination === 'function') {
                        window.updateLogsPagination(response.data.pagination);
                    }
                } else {
                    $('#logsTableBody').html('<tr><td colspan="6" class="text-center py-4"><p class="text-danger">Error loading data</p></td></tr>');
                }
            },
            error: function(xhr, status, error) {
                console.error('Error loading logs:', error);
                $('#logsTableBody').html('<tr><td colspan="6" class="text-center py-4"><p class="text-danger">Error loading data. Please try again.</p></td></tr>');
            }
        });
    };
});

// Make functions global for use in logs.js
window.updateLogsTable = function(logs) {
    const tbody = $('#logsTableBody');
    tbody.empty();
    
    if (logs && logs.length > 0) {
        logs.forEach(function(log) {
            let badgeClass = 'secondary';
            const actionType = log.action_type || 'GENERAL';
            
            switch (actionType) {
                case 'CREATE':
                    badgeClass = 'success';
                    break;
                case 'UPDATE':
                case 'STATUS_CHANGE':
                    badgeClass = 'info';
                    break;
                case 'DELETE':
                    badgeClass = 'danger';
                    break;
                case 'LOGIN':
                    badgeClass = 'primary';
                    break;
                case 'LOGOUT':
                    badgeClass = 'dark';
                    break;
                default:
                    badgeClass = 'secondary';
            }
            
            const timestamp = new Date(log.timestamp);
            const dateStr = timestamp.toLocaleDateString('en-US', { month: 'short', day: 'numeric', year: 'numeric' });
            const timeStr = timestamp.toLocaleTimeString('en-US', { hour: 'numeric', minute: '2-digit', hour12: true });
            
            let userHtml = '<span class="text-muted">System</span>';
            if (log.user_id > 0 && log.first_name) {
                userHtml = `<div class="fw-bold">${escapeHtml(log.first_name + ' ' + (log.last_name || ''))}</div><small class="text-muted">${escapeHtml(log.email || '')}</small>`;
            }
            
            let targetIdHtml = '<span class="text-muted">-</span>';
            if (log.target_id) {
                targetIdHtml = `<span class="badge bg-light text-dark">#${escapeHtml(log.target_id)}</span>`;
            }
            
            const row = `
                <tr>
                    <td class="text-center">${log.logs_id}</td>
                    <td><small>${dateStr}<br><span class="text-muted">${timeStr}</span></small></td>
                    <td>${userHtml}</td>
                    <td><span class="badge bg-${badgeClass}">${escapeHtml(actionType)}</span></td>
                    <td>${escapeHtml(log.description || '')}</td>
                    <td>${targetIdHtml}</td>
                </tr>
            `;
            tbody.append(row);
        });
    } else {
        tbody.html('<tr><td colspan="6" class="text-center py-4"><i class="fas fa-inbox fa-3x text-muted mb-3"></i><p class="text-muted">No activity logs found</p></td></tr>');
    }
}

// Update stats
window.updateLogsStats = function(summary) {
    if (summary) {
        $('#statTotalActivities').text(formatNumber ? formatNumber(summary.total_activities || 0) : (summary.total_activities || 0).toLocaleString());
        $('#statActiveUsers').text(formatNumber ? formatNumber(summary.active_users || 0) : (summary.active_users || 0).toLocaleString());
        $('#statFailedLogins').text(formatNumber ? formatNumber(summary.failed_logins || 0) : (summary.failed_logins || 0).toLocaleString());
        
        let topAction = 'N/A';
        if (summary.activities_by_type && summary.activities_by_type.length > 0) {
            topAction = summary.activities_by_type[0].action_type || 'N/A';
        }
        $('#statTopAction').text(topAction);
    }
}

// Update active users list
window.updateActiveUsers = function(activeUsers) {
    const container = $('#activeUsersList');
    container.empty();
    
    console.log('🔍 updateActiveUsers called with:', activeUsers);
    
    if (activeUsers && activeUsers.length > 0) {
        const listGroup = $('<div class="list-group list-group-flush"></div>');
        activeUsers.forEach(function(user) {
            // Convert activity_count to number (it might be a string from the database)
            const activityCount = parseInt(user.activity_count) || 0;
            console.log('🔍 Processing user:', user.first_name, 'activity_count:', user.activity_count, 'parsed:', activityCount);
            
            const item = `
                <div class="list-group-item px-3 d-flex justify-content-between align-items-center">
                    <div class="flex-grow-1">
                        <div class="fw-bold small">${escapeHtml((user.first_name || '') + ' ' + (user.last_name || ''))}</div>
                        <small class="text-muted d-block" style="font-size: 0.75rem;">${escapeHtml(user.email || '')}</small>
                    </div>
                    <span class="badge bg-primary rounded-pill ms-2">${typeof formatNumber === 'function' ? formatNumber(activityCount) : activityCount.toLocaleString()}</span>
                </div>
            `;
            listGroup.append(item);
        });
        container.append(listGroup);
    } else {
        container.html('<div class="p-3"><p class="text-muted text-center my-3 small">No activity data available</p></div>');
    }
}

// Update pagination
window.updateLogsPagination = function(pagination) {
    const container = $('#logsPaginationContainer');
    container.empty();
    
    if (pagination.total_pages > 1) {
        const currentPage = pagination.current_page || 1;
        const totalPages = pagination.total_pages || 1;
        
        let html = '<nav aria-label="Activity logs pagination"><ul class="pagination mb-0">';
        
        // Previous
        html += `<li class="page-item ${currentPage <= 1 ? 'disabled' : ''}"><a class="page-link" href="#" data-page="${currentPage - 1}" aria-label="Previous"><span aria-hidden="true">&laquo;</span></a></li>`;
        
        // Page numbers
        const maxPages = 7;
        let startPage = Math.max(1, currentPage - 3);
        let endPage = Math.min(totalPages, startPage + maxPages - 1);
        if (endPage - startPage < maxPages - 1) {
            startPage = Math.max(1, endPage - maxPages + 1);
        }
        
        if (startPage > 1) {
            html += '<li class="page-item"><a class="page-link" href="#" data-page="1">1</a></li>';
            if (startPage > 2) html += '<li class="page-item disabled"><span class="page-link">...</span></li>';
        }
        
        for (let i = startPage; i <= endPage; i++) {
            html += `<li class="page-item ${i == currentPage ? 'active' : ''}"><a class="page-link" href="#" data-page="${i}">${i}</a></li>`;
        }
        
        if (endPage < totalPages) {
            if (endPage < totalPages - 1) html += '<li class="page-item disabled"><span class="page-link">...</span></li>';
            html += `<li class="page-item"><a class="page-link" href="#" data-page="${totalPages}">${totalPages}</a></li>`;
        }
        
        // Next
        html += `<li class="page-item ${currentPage >= totalPages ? 'disabled' : ''}"><a class="page-link" href="#" data-page="${currentPage + 1}" aria-label="Next"><span aria-hidden="true">&raquo;</span></a></li>`;
        
        html += '</ul></nav>';
        container.html(html);
        
        // Pagination click handler
        container.find('.page-link').on('click', function(e) {
            e.preventDefault();
            const page = $(this).data('page');
            if (page && !$(this).parent().hasClass('disabled') && !$(this).parent().hasClass('active')) {
                loadLogsWithFilter(page);
            }
        });
    }
    
    // Update count info
    $('#logsCountInfo').text(`(Showing ${pagination.showing_from || 0} to ${pagination.showing_to || 0} of ${formatNumber ? formatNumber(pagination.total || 0) : (pagination.total || 0).toLocaleString()} entries)`);
}

// Utility functions
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

function formatNumber(num) {
    // Convert to number if it's a string
    const number = typeof num === 'number' ? num : parseInt(num) || 0;
    return number.toLocaleString();
}
</script>

