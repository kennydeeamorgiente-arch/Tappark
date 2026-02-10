<?php
/**
 * Reusable Date Filter Component
 * 
 * Usage:
 * <?= view('partials/components/date_filter', [
 *     'filter' => $filter ?? 'today',
 *     'customStart' => $customStart ?? null,
 *     'customEnd' => $customEnd ?? null,
 *     'filterCallback' => 'loadDashboardWithFilter' // JavaScript function name to call
 * ]) ?>
 * 
 * @var string $filter Current filter value (today, last_7_days, 30_days, this_year, last_year, custom)
 * @var string|null $customStart Custom start date (Y-m-d format)
 * @var string|null $customEnd Custom end date (Y-m-d format)
 * @var string $filterCallback JavaScript function name to call when filter changes (default: 'loadDashboardWithFilter')
 * @var string $componentId Unique ID for this filter instance (default: 'dateFilter')
 */

// Default values
$filter = $filter ?? 'today';
$customStart = $customStart ?? null;
$customEnd = $customEnd ?? null;
$filterCallback = $filterCallback ?? 'loadDashboardWithFilter';
$componentId = $componentId ?? 'dateFilter';

// Filter options for dropdown
$filterOptions = [
    ['value' => 'today', 'label' => 'Today'],
    ['value' => 'last_7_days', 'label' => 'Last 7 Days'],
    ['value' => '30_days', 'label' => '30 Days'],
    ['value' => 'this_year', 'label' => 'This Year'],
    ['value' => 'last_year', 'label' => 'Last Year'],
    ['value' => 'custom', 'label' => 'Custom Range']
];
?>

<!-- Date Filter Component -->
<style>
.dropdown-menu {
    min-width: 200px;
    max-width: 220px;
    border: 1px solid rgba(128, 0, 0, 0.15);
    border-radius: 0.75rem;
    box-shadow: 0 0.5rem 1.5rem rgba(128, 0, 0, 0.15), 0 0.25rem 0.5rem rgba(0, 0, 0, 0.08);
    background-color: white;
    padding: 0.5rem;
    margin-top: 0.5rem;
    right: 0;
    left: auto;
    position: absolute;
    z-index: 10; /* Lower z-index to stay within page context */
    backdrop-filter: blur(10px);
    -webkit-backdrop-filter: blur(10px);
    animation: dropdownFadeIn 0.2s ease-out;
}

@keyframes dropdownFadeIn {
    from {
        opacity: 0;
        transform: translateY(-0.5rem) scale(0.95);
    }
    to {
        opacity: 1;
        transform: translateY(0) scale(1);
    }
}

.dropdown-item {
    padding: 0.625rem 1rem;
    color: #212529;
    text-decoration: none;
    display: block;
    transition: all 0.2s cubic-bezier(0.4, 0, 0.2, 1);
    cursor: pointer;
    font-size: 0.875rem;
    font-weight: 500;
    border-radius: 0.5rem;
    margin: 0.125rem 0;
    position: relative;
    overflow: hidden;
}

.dropdown-item::before {
    content: '';
    position: absolute;
    top: 0;
    left: -100%;
    width: 100%;
    height: 100%;
    background: linear-gradient(90deg, transparent, rgba(255, 255, 255, 0.2), transparent);
    transition: left 0.5s;
}

.dropdown-item:hover::before {
    left: 100%;
}

.dropdown-item:hover {
    background: linear-gradient(135deg, #800000 0%, #990000 100%);
    color: white;
    transform: translateX(0.25rem);
    box-shadow: 0 0.25rem 0.5rem rgba(128, 0, 0, 0.2);
}

.dropdown-item.active {
    background: linear-gradient(135deg, #800000 0%, #990000 100%);
    color: white;
    font-weight: 600;
    box-shadow: 0 0.25rem 0.5rem rgba(128, 0, 0, 0.2);
    position: relative;
}

.dropdown-item.active::after {
    content: '✓';
    position: absolute;
    right: 1rem;
    top: 50%;
    transform: translateY(-50%);
    font-size: 0.75rem;
    font-weight: bold;
}

.btn.dropdown-toggle::after {
    display: inline-block;
    margin-left: 0.255em;
    vertical-align: 0.255em;
    content: "";
    border-top: 0.3em solid;
    border-right: 0.3em solid transparent;
    border-bottom: 0;
    border-left: 0.3em solid transparent;
    transition: transform 0.2s ease;
}

.btn.dropdown-toggle:hover::after {
    transform: rotate(180deg);
}

.btn.dropdown-toggle.show::after {
    transform: rotate(180deg);
}

/* Custom date range adjustments */
.date-range-panel {
    margin-top: 0.75rem;
    padding: 1rem;
    border: 1px solid rgba(128, 0, 0, 0.15);
    border-radius: 0.75rem;
    background: linear-gradient(135deg, #f8f9fa 0%, #ffffff 100%);
    box-shadow: inset 0 0.125rem 0.25rem rgba(0, 0, 0, 0.06);
}

.date-range-panel .form-control {
    font-size: 0.875rem;
    padding: 0.5rem 0.75rem;
    border: 1px solid rgba(128, 0, 0, 0.15);
    border-radius: 0.5rem;
    transition: all 0.2s ease;
}

.date-range-panel .form-control:focus {
    border-color: #800000;
    box-shadow: 0 0 0 0.2rem rgba(128, 0, 0, 0.15);
}

.date-range-panel .btn {
    font-size: 0.875rem;
    padding: 0.5rem 1rem;
    border-radius: 0.5rem;
    font-weight: 500;
    transition: all 0.2s ease;
}

.date-range-panel .btn:hover {
    transform: translateY(-0.125rem);
    box-shadow: 0 0.25rem 0.5rem rgba(128, 0, 0, 0.2);
}

/* Ensure dropdown container stays within flow */
.dropdown {
    position: relative;
    z-index: 10;
}

/* Modern button styling */
.btn.dropdown-toggle {
    background: linear-gradient(135deg, #800000 0%, #990000 100%);
    border: 1px solid rgba(128, 0, 0, 0.3);
    color: white;
    font-weight: 500;
    padding: 0.5rem 1rem;
    border-radius: 0.5rem;
    transition: all 0.2s ease;
    box-shadow: 0 0.125rem 0.25rem rgba(128, 0, 0, 0.1);
}

.btn.dropdown-toggle:hover {
    background: linear-gradient(135deg, #990000 0%, #aa0000 100%);
    border-color: rgba(128, 0, 0, 0.5);
    transform: translateY(-0.125rem);
    box-shadow: 0 0.25rem 0.5rem rgba(128, 0, 0, 0.2);
}

.btn.dropdown-toggle:focus {
    box-shadow: 0 0 0 0.2rem rgba(128, 0, 0, 0.25);
}

/* Dark mode support */
[data-bs-theme="dark"] .dropdown-menu {
    background: linear-gradient(135deg, #2d3436 0%, #343a40 100%);
    border-color: rgba(128, 0, 0, 0.3);
    color: #e8d4d4;
}

[data-bs-theme="dark"] .dropdown-item {
    color: #e8d4d4;
}

[data-bs-theme="dark"] .dropdown-item:hover {
    background: linear-gradient(135deg, #661f1f 0%, #7a2f2f 100%);
    color: #ffb3b3;
}

[data-bs-theme="dark"] .dropdown-item.active {
    background: linear-gradient(135deg, #661f1f 0%, #7a2f2f 100%);
    color: #ffb3b3;
}

[data-bs-theme="dark"] .date-range-panel {
    background: linear-gradient(135deg, #2d3436 0%, #343a40 100%);
    border-color: rgba(128, 0, 0, 0.3);
}
</style>

<div class="row mb-4">
    <div class="col-12">
        <div class="card shadow-sm">
            <div class="card-body">
                <div class="d-flex flex-wrap align-items-center justify-content-between gap-3">
                    <h5 class="mb-0"><i class="fas fa-filter me-2"></i>Date Range</h5>
                    
                    <div class="d-flex align-items-center gap-3 flex-wrap">
                        <!-- Show custom date range when selected -->
                        <div class="date-range-info" id="<?= $componentId ?>Info" style="display: <?= ($filter === 'custom' && $customStart && $customEnd) ? 'block' : 'none' ?>;">
                            <small class="text-muted">
                                <i class="fas fa-info-circle me-1"></i>
                                Showing data from 
                                <strong id="<?= $componentId ?>StartDateDisplay"><?= ($filter === 'custom' && $customStart) ? date('M d, Y', strtotime($customStart)) : '' ?></strong> 
                                to <strong id="<?= $componentId ?>EndDateDisplay"><?= ($filter === 'custom' && $customEnd) ? date('M d, Y', strtotime($customEnd)) : '' ?></strong>
                            </small>
                        </div>
                        
                        <!-- Custom Dropdown for filter selection -->
                        <div class="dropdown position-relative">
                            <button class="btn dropdown-toggle" type="button" id="<?= $componentId ?>Dropdown" aria-expanded="false"
                                    style="background-color: #800000; border-color: #800000; color: white;">
                                <i class="fas fa-calendar me-1"></i>
                                <span id="<?= $componentId ?>DropdownLabel">
                                    <?php
                                    $currentLabel = 'Today';
                                    foreach ($filterOptions as $opt) {
                                        if ($opt['value'] === $filter) {
                                            $currentLabel = $opt['label'];
                                            break;
                                        }
                                    }
                                    echo $currentLabel;
                                    ?>
                                </span>
                            </button>
                            <ul class="dropdown-menu" id="<?= $componentId ?>DropdownMenu" style="border-color: #800000; display: none; position: absolute; top: 100%; right: 0; left: auto; z-index: 10;">
                                <?php foreach ($filterOptions as $opt): ?>
                                <li>
                                    <a class="dropdown-item filter-option <?= $filter === $opt['value'] ? 'active' : '' ?>" 
                                       href="#" 
                                       data-filter="<?= $opt['value'] ?>"
                                       data-component-id="<?= $componentId ?>"
                                       style="<?= $filter === $opt['value'] ? 'background-color: #800000; color: white;' : '' ?>">
                                        <?= $opt['label'] ?>
                                    </a>
                                </li>
                                <?php endforeach; ?>
                            </ul>
                        </div>
                    </div>
                </div>
                
                <!-- Custom Date Range (Hidden by default) -->
                <div class="date-range-panel" id="<?= $componentId ?>CustomRange" style="display: <?= ($filter === 'custom') ? 'block' : 'none' ?>;">
                    <div class="row g-2">
                        <div class="col-md-4">
                            <label class="form-label small">Start Date</label>
                            <input type="date" 
                                   class="form-control" 
                                   id="<?= $componentId ?>StartDate" 
                                   value="<?= $customStart ?? '' ?>">
                        </div>
                        <div class="col-md-4">
                            <label class="form-label small">End Date</label>
                            <input type="date" 
                                   class="form-control" 
                                   id="<?= $componentId ?>EndDate" 
                                   value="<?= $customEnd ?? '' ?>">
                        </div>
                        <div class="col-md-4 d-flex align-items-end gap-2">
                            <button type="button" 
                                    class="btn" 
                                    id="<?= $componentId ?>ApplyBtn"
                                    style="background-color: #800000; border-color: #800000; color: white;">
                                <i class="fas fa-check me-1"></i> Apply
                            </button>
                            <button type="button" 
                                    class="btn btn-outline-secondary" 
                                    id="<?= $componentId ?>CancelBtn"
                                    style="border-color: #800000; color: #800000;">
                                Cancel
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Initialize filter component JavaScript -->
<script>
(function() {
    'use strict';
    
    const componentId = '<?= $componentId ?>';
    const filterCallback = '<?= $filterCallback ?>';
    
    // Unbind old handlers first to prevent duplicates
    $(`.filter-option[data-component-id="${componentId}"]`).off('click');
    $(`#${componentId}ApplyBtn`).off('click');
    $(`#${componentId}CancelBtn`).off('click');
    $(`#${componentId}Dropdown`).off('click');
    
    // Custom dropdown toggle
    $(`#${componentId}Dropdown`).on('click', function(e) {
        e.preventDefault();
        e.stopPropagation();
        
        const $menu = $(`#${componentId}DropdownMenu`);
        const isHidden = $menu.is(':hidden');
        
        // Close all other dropdowns within the same container
        $('.dropdown-menu').not($menu).hide();
        
        // Toggle this dropdown with smooth animation
        if (isHidden) {
            $menu.slideDown(200);
            $(this).attr('aria-expanded', 'true');
        } else {
            $menu.slideUp(200);
            $(this).attr('aria-expanded', 'false');
        }
    });
    
    // Close dropdown when clicking outside (but within page container)
    $(document).on('click', function(e) {
        if (!$(e.target).closest(`#${componentId}Dropdown, #${componentId}DropdownMenu`).length) {
            $(`#${componentId}DropdownMenu`).slideUp(200);
            $(`#${componentId}Dropdown`).attr('aria-expanded', 'false');
        }
    });
    
    // Filter option clicks
    $(`.filter-option[data-component-id="${componentId}"]`).on('click', function(e) {
        e.preventDefault();
        e.stopPropagation();
        const filter = $(this).data('filter');
        
        // Make sure filter is defined
        if (!filter) {
            console.error('Filter value is undefined for option:', this);
            return;
        }
        
        // Update active state in dropdown - remove all active states first
        $(`.filter-option[data-component-id="${componentId}"]`).removeClass('active');
        $(`.filter-option[data-component-id="${componentId}"]`).css('background-color', 'transparent');
        $(`.filter-option[data-component-id="${componentId}"]`).css('color', '#212529');
        
        // Add active to clicked item
        $(this).addClass('active');
        $(this).css('background-color', '#800000');
        $(this).css('color', 'white');
        
        // Update dropdown button label
        const label = $(this).text();
        $(`#${componentId}DropdownLabel`).text(label);
        
        // Close dropdown with smooth animation
        $(`#${componentId}DropdownMenu`).slideUp(200);
        $(`#${componentId}Dropdown`).attr('aria-expanded', 'false');
        
        // Hide custom date range if open
        $(`#${componentId}CustomRange`).slideUp(300);
        
        // Hide the date range info when switching to non-custom filters
        $(`#${componentId}Info`).slideUp(200);
        
        // Call the filter callback function
        const tryCallFilter = function(attempts = 0) {
            if (typeof window[filterCallback] === 'function') {
                if (filter === 'custom') {
                    // For custom, just show the date range picker (don't call callback yet)
                    $(`#${componentId}CustomRange`).slideDown(300);
                } else {
                    // For preset filters, call the callback immediately
                    window[filterCallback](filter);
                }
            } else if (attempts < 10) {
                setTimeout(function() {
                    tryCallFilter(attempts + 1);
                }, 100);
            } else {
                console.error(`Filter callback function "${filterCallback}" not found after ${attempts} attempts!`);
                alert('Filter function not available. Please refresh the page.');
            }
        };
        tryCallFilter();
    });
    
    // Add hover effects for dropdown items
    $(`.filter-option[data-component-id="${componentId}"]`).on('mouseenter', function() {
        if (!$(this).hasClass('active')) {
            $(this).css('background-color', '#800000');
            $(this).css('color', 'white');
        }
    });
    
    $(`.filter-option[data-component-id="${componentId}"]`).on('mouseleave', function() {
        if (!$(this).hasClass('active')) {
            $(this).css('background-color', 'transparent');
            $(this).css('color', '#212529');
        }
    });
    
    // Apply custom range
    $(`#${componentId}ApplyBtn`).on('click', function() {
        const startDate = $(`#${componentId}StartDate`).val();
        const endDate = $(`#${componentId}EndDate`).val();
        
        if (!startDate || !endDate) {
            alert('Please select both start and end dates');
            return;
        }
        
        // Update dropdown label to show custom range
        $(`#${componentId}DropdownLabel`).text('Custom Range');
        
        // Hide the custom date range panel
        $(`#${componentId}CustomRange`).slideUp(300);
        
        // Format dates for display
        const formatDateForDisplay = function(dateString) {
            const date = new Date(dateString + 'T00:00:00');
            const months = ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun', 'Jul', 'Aug', 'Sep', 'Oct', 'Nov', 'Dec'];
            return months[date.getMonth()] + ' ' + date.getDate() + ', ' + date.getFullYear();
        };
        
        // Update the date range info display
        const $infoDiv = $(`#${componentId}Info`);
        const $startDisplay = $(`#${componentId}StartDateDisplay`);
        const $endDisplay = $(`#${componentId}EndDateDisplay`);
        
        if ($startDisplay.length && $endDisplay.length) {
            $startDisplay.text(formatDateForDisplay(startDate));
            $endDisplay.text(formatDateForDisplay(endDate));
            $infoDiv.show().css('display', 'block');
        }
        
        // Call the filter callback function with custom dates
        const tryCallCustomFilter = function(attempts = 0) {
            if (typeof window[filterCallback] === 'function') {
                window[filterCallback]('custom', startDate, endDate);
            } else if (attempts < 20) {
                setTimeout(function() {
                    tryCallCustomFilter(attempts + 1);
                }, 100);
            } else {
                console.error(`❌ Filter callback function "${filterCallback}" not found after ${attempts} attempts!`);
                alert('Filter function not available. Please refresh the page.');
            }
        };
        tryCallCustomFilter();
    });
    
    // Cancel custom range
    $(`#${componentId}CancelBtn`).on('click', function() {
        $(`#${componentId}CustomRange`).slideUp(300);
        // Reset to previous filter or default to 'today'
        const previousFilter = '<?= $filter !== 'custom' ? $filter : 'today' ?>';
        const $previousOption = $(`.filter-option[data-filter="${previousFilter}"]`);
        if ($previousOption.length) {
            $previousOption.trigger('click');
        }
    });
})();
</script>

