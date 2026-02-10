// reports.js
// Reports-specific JavaScript with Chart.js integration

// Chart instances stored globally to allow destruction
window.reportsCharts = window.reportsCharts || {
    revenuePlanChart: null,
    revenueAreaChart: null,
    revenueHourChart: null,
    revenueDayChart: null,
    bookingsDayChart: null,
    bookingsAreaChart: null,
    peakHoursChart: null,
    vehicleTypesChart: null,
    hourlyOccupancyChart: null,
    userGrowthChart: null,
    userActivityChart: null,
    revenueTrendChart: null,
    subscriptionChart: null,
    bookingStatusChart: null,
    reportsGuestBookingsTrendChart: null,
    reportsGuestBookingsVehicleChart: null,
    reportsGuestBookingsAttendantChart: null
};

// Utility function to get chart colors based on theme
function getReportsChartColors() {
    const isDarkMode = document.documentElement.getAttribute('data-bs-theme') === 'dark';

    if (isDarkMode) {
        return {
            maroon: 'rgba(179, 51, 51, 0.9)',
            maroonLight: 'rgba(179, 51, 51, 0.2)',
            gray: 'rgba(140, 140, 140, 0.8)',
            grayLight: 'rgba(140, 140, 140, 0.2)',
            borderColor: 'rgba(255, 255, 255, 0.3)'
        };
    } else {
        return {
            maroon: '#800000',
            maroonLight: 'rgba(128, 0, 0, 0.1)',
            gray: '#383838',
            grayLight: 'rgba(56, 56, 56, 0.1)',
            borderColor: '#fff'
        };
    }
}

function initFeedbackPagination() {
    const feedbackTableBody = document.getElementById('feedbackTableBody');
    const prevBtn = document.getElementById('feedbackPrevPage');
    const nextBtn = document.getElementById('feedbackNextPage');
    const summaryEl = document.getElementById('feedbackPaginationSummary');
    const statusEl = document.getElementById('feedbackPaginationStatus');

    if (!feedbackTableBody) {
        return;
    }

    const feedbackData = Array.isArray(window.reportsData?.feedbackList)
        ? window.reportsData.feedbackList
        : [];
    const paginationSettings = window.reportsData?.feedbackPagination || {};
    const pageSize = paginationSettings.pageSize || 5;
    const totalRows = feedbackData.length;
    const totalPages = Math.max(1, Math.ceil(totalRows / pageSize));
    let currentPage = 1;

    function updateControls() {
        if (prevBtn) {
            prevBtn.disabled = currentPage <= 1;
        }
        if (nextBtn) {
            nextBtn.disabled = currentPage >= totalPages || totalRows <= pageSize;
        }
        if (statusEl) {
            statusEl.textContent = totalRows === 0 ? '0 / 0' : `${currentPage} / ${totalPages}`;
        }
    }

    function updateSummary(start, end) {
        if (!summaryEl) {
            return;
        }
        if (totalRows === 0) {
            summaryEl.textContent = 'No feedback entries available';
            return;
        }
        summaryEl.textContent = `Showing ${start}-${end} of ${totalRows} feedback entries`;
    }

    function renderFeedbackRow(row, index) {
        const fullName = `${row?.first_name || ''} ${row?.last_name || ''}`.trim();
        const email = row?.email || '';
        const rating = row?.rating ?? 0;
        const content = row?.content || '';
        const createdAt = row?.created_at || '';

        return `
            <tr>
                <td>#${index}</td>
                <td>
                    <div class="fw-semibold">${escapeHtml(fullName)}</div>
                    <div class="small text-muted">${escapeHtml(email)}</div>
                </td>
                <td>
                    <span class="badge bg-secondary">${escapeHtml(rating)} / 5</span>
                </td>
                <td style="max-width: 420px;">
                    <div class="text-truncate" title="${escapeHtml(content)}">
                        ${escapeHtml(content)}
                    </div>
                </td>
                <td class="small text-muted">${escapeHtml(createdAt)}</td>
            </tr>
        `;
    }

    function renderTable(page) {
        if (totalRows === 0) {
            feedbackTableBody.innerHTML = `
                <tr class="feedback-empty-row">
                    <td colspan="4" class="text-center text-muted py-4">No feedback found for the selected period.</td>
                </tr>
            `;
            updateSummary(0, 0);
            updateControls();
            return;
        }

        const startIndex = (page - 1) * pageSize;
        const endIndex = Math.min(startIndex + pageSize, totalRows);
        const rows = feedbackData.slice(startIndex, endIndex).map((row, i) => renderFeedbackRow(row, startIndex + i + 1)).join('');
        feedbackTableBody.innerHTML = rows;
        updateSummary(startIndex + 1, endIndex);
        updateControls();
    }

    if (prevBtn) {
        prevBtn.addEventListener('click', function () {
            if (currentPage > 1) {
                currentPage -= 1;
                renderTable(currentPage);
            }
        });
    }

    if (nextBtn) {
        nextBtn.addEventListener('click', function () {
            if (currentPage < totalPages) {
                currentPage += 1;
                renderTable(currentPage);
            }
        });
    }

    renderTable(currentPage);
}

// Reports-specific initialization function
function initReportsCharts() {
    console.log('📊 Reports initialized');

    // Check if Chart.js is loaded
    if (typeof Chart === 'undefined') {
        console.error('❌ Chart.js is not loaded!');
        return;
    }

    console.log('✅ Chart.js version:', Chart.version);
    window.applyChartThemeDefaults?.();

    // Check if reports data is available
    if (typeof window.reportsData === 'undefined') {
        console.error('❌ Reports data not found! Make sure window.reportsData is set in the view.');
        console.log('⏳ Retrying in 200ms...');
        // Retry after a short delay (in case data is still being set)
        setTimeout(function () {
            if (typeof window.reportsData !== 'undefined') {
                initReportsCharts();
            } else {
                console.error('❌ Reports data still not available after retry');
            }
        }, 200);
        return;
    }

    // Check if we're actually on the reports page (canvas elements should exist)
    const testCanvas = document.getElementById('revenuePlanChart');
    if (!testCanvas) {
        console.warn('⚠️ Reports canvas elements not found. Charts may not render correctly.');
        console.log('⏳ Retrying in 200ms...');
        setTimeout(function () {
            const retryCanvas = document.getElementById('revenuePlanChart');
            if (retryCanvas) {
                initReportsCharts();
            } else {
                console.error('❌ Reports canvas elements still not found after retry');
            }
        }, 200);
        return;
    }

    // Initialize charts
    initializeCharts();
    window.refreshExistingChartsTheme?.();
}

// Function to clear "No Data" messages
function clearNoDataMessages(canvasId) {
    var canvas = document.getElementById(canvasId);
    if (!canvas || !canvas.parentNode) return;

    canvas.style.display = 'block';
    // Remove any "No Data" divs that were added
    var noDataDivs = canvas.parentNode.querySelectorAll('.text-center.text-muted.py-5');
    noDataDivs.forEach(function (div) { div.remove(); });
}

// Function to show "No Data" message
function showNoDataMessage(canvasId, iconClass, titleText, subtitleText) {
    var canvas = document.getElementById(canvasId);
    if (!canvas || !canvas.parentNode) return;

    // Clear existing placeholders first
    var existing = canvas.parentNode.querySelectorAll('.text-center.text-muted.py-5');
    existing.forEach(function (div) { div.remove(); });

    canvas.style.display = 'none';
    var noDataDiv = document.createElement('div');
    noDataDiv.className = 'text-center text-muted py-5';
    noDataDiv.innerHTML = '<i class="' + iconClass + ' fa-3x mb-3 opacity-50"></i>' +
        '<h5>' + titleText + '</h5>' +
        '<p class="small">' + subtitleText + '</p>';
    canvas.parentNode.appendChild(noDataDiv);
}

function hasAnyPositiveValue(values) {
    return Array.isArray(values) && values.some(function (v) { return (parseFloat(v) || 0) > 0; });
}

function escapeHtml(value) {
    if (value === null || value === undefined) {
        return '';
    }
    return String(value)
        .replace(/&/g, '&amp;')
        .replace(/</g, '&lt;')
        .replace(/>/g, '&gt;')
        .replace(/"/g, '&quot;')
        .replace(/'/g, '&#39;');
}

// Initialize all reports charts
function initializeChartsLegacy() {
    // Destroy existing charts if they exist
    Object.keys(window.reportsCharts).forEach(key => {
        if (window.reportsCharts[key]) {
            window.reportsCharts[key].destroy();
            window.reportsCharts[key] = null;
        }
    });

    const chartColors = getReportsChartColors();

    // ====================================
    // REVENUE BY PLAN CHART (Pie Chart)
    // ====================================
    const revenuePlanCanvas = document.getElementById('revenuePlanChart');
    if (revenuePlanCanvas) {
        clearNoDataMessages('revenuePlanChart');
        const revenuePlanData = window.reportsData?.revenueByPlan || [];
        const revenuePlanCtx = revenuePlanCanvas.getContext('2d');

        // Filter out plans with 0 revenue
        const plansWithRevenue = revenuePlanData.filter(item => (parseFloat(item.total_revenue) || 0) > 0);

        // Always create chart, even with no data
        const planLabels = plansWithRevenue.length > 0
            ? plansWithRevenue.map(item => item.plan_name)
            : ['No Data'];
        const planData = plansWithRevenue.length > 0
            ? plansWithRevenue.map(item => parseFloat(item.total_revenue) || 0)
            : [1]; // Dummy data to show empty state

        window.reportsCharts.revenuePlanChart = new Chart(revenuePlanCtx, {
            type: 'pie',
            data: {
                labels: planLabels,
                datasets: [{
                    data: planData,
                    backgroundColor: plansWithRevenue.length > 0 ? [
                        chartColors.maroon,
                        chartColors.gray,
                        '#990000',
                        '#666666',
                        '#800000',
                        '#383838'
                    ] : ['rgba(200, 200, 200, 0.3)'],
                    borderColor: chartColors.borderColor,
                    borderWidth: 2
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        position: 'bottom',
                        labels: {
                            font: { size: 12 },
                            padding: 10
                        }
                    },
                    tooltip: {
                        enabled: plansWithRevenue.length > 0,
                        callbacks: {
                            label: function (context) {
                                if (plansWithRevenue.length === 0) return 'No data available';
                                const value = context.parsed || 0;
                                return context.label + ': ₱' + value.toLocaleString();
                            }
                        }
                    }
                }
            }
        });
    }

    // ====================================
    // REVENUE BY AREA CHART (Bar Chart)
    // ====================================
    const revenueAreaCanvas = document.getElementById('revenueAreaChart');
    if (revenueAreaCanvas) {
        clearNoDataMessages('revenueAreaChart');
        const revenueAreaData = window.reportsData?.revenueByArea || [];
        const revenueAreaCtx = revenueAreaCanvas.getContext('2d');

        // Always create chart, even with no data
        const hasData = revenueAreaData.length > 0;
        const labels = hasData
            ? revenueAreaData.map(item => item.parking_area_name)
            : ['No Data'];
        const data = hasData
            ? revenueAreaData.map(item => parseFloat(item.total_bookings) || 0)
            : [0];

        const hasRealData = hasAnyPositiveValue(data);
        if (!hasRealData) {
            showNoDataMessage('revenueAreaChart', 'fas fa-map-marked-alt', 'No Data Available', 'No area bookings found for the selected period');
        } else {
            window.reportsCharts.revenueAreaChart = new Chart(revenueAreaCtx, {
                type: 'bar',
                data: {
                    labels: labels,
                    datasets: [{
                        label: 'Bookings',
                        data: data,
                        backgroundColor: hasData ? chartColors.maroon : 'rgba(200, 200, 200, 0.3)',
                        borderColor: hasData ? '#660000' : 'rgba(200, 200, 200, 0.5)',
                        borderWidth: 2
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        legend: {
                            display: false
                        },
                        tooltip: {
                            enabled: hasData
                        }
                    },
                    scales: {
                        y: {
                            beginAtZero: true,
                            grid: {
                                display: true,
                                color: 'rgba(0, 0, 0, 0.1)'
                            },
                            ticks: {
                                stepSize: 1,
                                font: { size: 12 }
                            }
                        },
                        x: {
                            grid: {
                                display: true,
                                color: 'rgba(0, 0, 0, 0.1)'
                            },
                            ticks: {
                                font: { size: 12 }
                            }
                        }
                    }
                }
            });
        }
    }

    // ====================================
    // PEAK HOURS CHART (Line Chart)
    // ====================================
    const peakHoursCanvas = document.getElementById('peakHoursChart');
    if (peakHoursCanvas) {
        clearNoDataMessages('peakHoursChart');
        const peakHoursDataRaw = window.reportsData?.peakHours || [];
        const peakHoursData = Array.isArray(peakHoursDataRaw.data)
            ? peakHoursDataRaw.data
            : peakHoursDataRaw;
        const peakHourLabels = Array.isArray(peakHoursDataRaw.labels)
            ? peakHoursDataRaw.labels
            : (Array.isArray(peakHoursData) ? peakHoursData.map(item => item.hour) : []);
        const peakHoursCtx = peakHoursCanvas.getContext('2d');

        const hasLabels = peakHourLabels.length > 0;
        const hasValues = Array.isArray(peakHoursData) && peakHoursData.length > 0;
        const labels = hasLabels ? peakHourLabels : ['No Data'];
        const data = hasValues
            ? peakHoursData.map(value => typeof value === 'number'
                ? value
                : (parseFloat(value.bookings) || 0))
            : [0];

        const hasRealData = hasAnyPositiveValue(data);
        if (!hasRealData) {
            showNoDataMessage('peakHoursChart', 'fas fa-clock', 'No Data Available', 'No peak hours found for the selected period');
        } else {
            window.reportsCharts.peakHoursChart = new Chart(peakHoursCtx, {
                type: 'line',
                data: {
                    labels: labels,
                    datasets: [{
                        label: 'Bookings',
                        data: data,
                        backgroundColor: hasData ? chartColors.maroon : 'rgba(200, 200, 200, 0.3)',
                        borderColor: hasData ? '#660000' : 'rgba(200, 200, 200, 0.5)',
                        borderWidth: 2
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        legend: {
                            display: false
                        },
                        tooltip: {
                            enabled: hasData
                        }
                    },
                    scales: {
                        y: {
                            beginAtZero: true,
                            grid: {
                                display: true,
                                color: 'rgba(0, 0, 0, 0.1)'
                            },
                            ticks: {
                                stepSize: 1,
                                font: { size: 12 }
                            }
                        },
                        x: {
                            grid: {
                                display: true,
                                color: 'rgba(0, 0, 0, 0.1)'
                            },
                            ticks: {
                                font: { size: 12 }
                            }
                        }
                    }
                }
            });
        }
    }

    // ====================================
    // VEHICLE TYPES CHART (Bar Chart)
    // ====================================
    const vehicleTypesCanvas = document.getElementById('vehicleTypesChart');
    if (vehicleTypesCanvas) {
        clearNoDataMessages('vehicleTypesChart');
        const vehicleTypesData = window.reportsData?.vehicleTypes || [];
        const vehicleTypesCtx = vehicleTypesCanvas.getContext('2d');

        // Always create chart, even with no data
        const hasData = vehicleTypesData.length > 0;
        const labels = hasData
            ? vehicleTypesData.map(item => item.vehicle_type)
            : ['No Data'];
        const data = hasData
            ? vehicleTypesData.map(item => parseFloat(item.bookings) || 0)
            : [0];

        const hasRealData = hasAnyPositiveValue(data);
        if (!hasRealData) {
            showNoDataMessage('vehicleTypesChart', 'fas fa-car', 'No Data Available', 'No vehicle types found for the selected period');
        } else {
            window.reportsCharts.vehicleTypesChart = new Chart(vehicleTypesCtx, {
                type: 'bar',
                data: {
                    labels: labels,
                    datasets: [{
                        label: 'Bookings',
                        data: data,
                        backgroundColor: hasData ? chartColors.maroon : 'rgba(200, 200, 200, 0.3)',
                        borderColor: hasData ? '#660000' : 'rgba(200, 200, 200, 0.5)',
                        borderWidth: 2
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        legend: {
                            display: false
                        },
                        tooltip: {
                            enabled: hasData
                        }
                    },
                    scales: {
                        y: {
                            beginAtZero: true,
                            grid: {
                                display: true,
                                color: 'rgba(0, 0, 0, 0.1)'
                            },
                            ticks: {
                                stepSize: 1,
                                font: { size: 12 }
                            }
                        },
                        x: {
                            grid: {
                                display: true,
                                color: 'rgba(0, 0, 0, 0.1)'
                            },
                            ticks: {
                                font: { size: 12 }
                            }
                        }
                    }
                }
            });
        }
    }

    // ====================================
    // USER GROWTH CHART (Line Chart)
    // ====================================
    const userGrowthCanvas = document.getElementById('userGrowthChart');
    if (userGrowthCanvas) {
        clearNoDataMessages('userGrowthChart');
        const userGrowthData = window.reportsData?.userGrowth || [];
        const userGrowthCtx = userGrowthCanvas.getContext('2d');

        // Always create chart, even with no data
        const hasData = userGrowthData.length > 0;
        const labels = hasData
            ? userGrowthData.map(item => item.date)
            : ['No Data'];
        const data = hasData
            ? userGrowthData.map(item => parseFloat(item.users) || 0)
            : [0];

        const hasRealData = hasAnyPositiveValue(data);
        if (!hasRealData) {
            showNoDataMessage('userGrowthChart', 'fas fa-user', 'No Data Available', 'No user growth found for the selected period');
        } else {
            window.reportsCharts.userGrowthChart = new Chart(userGrowthCtx, {
                type: 'line',
                data: {
                    labels: labels,
                    datasets: [{
                        label: 'Users',
                        data: data,
                        backgroundColor: hasData ? chartColors.maroon : 'rgba(200, 200, 200, 0.3)',
                        borderColor: hasData ? '#660000' : 'rgba(200, 200, 200, 0.5)',
                        borderWidth: 2
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        legend: {
                            display: false
                        },
                        tooltip: {
                            enabled: hasData
                        }
                    },
                    scales: {
                        y: {
                            beginAtZero: true,
                            grid: {
                                display: true,
                                color: 'rgba(0, 0, 0, 0.1)'
                            },
                            ticks: {
                                stepSize: 1,
                                font: { size: 12 }
                            }
                        },
                        x: {
                            grid: {
                                display: true,
                                color: 'rgba(0, 0, 0, 0.1)'
                            },
                            ticks: {
                                font: { size: 12 }
                            }
                        }
                    }
                }
            });
        }
    }

    // ====================================
    // REVENUE TREND CHART (Line Chart)
    // ====================================
    const revenueTrendCanvas = document.getElementById('revenueTrendChart');
    if (revenueTrendCanvas) {
        clearNoDataMessages('revenueTrendChart');
        const revenueTrendData = window.reportsData?.revenueTrend || [];
        const revenueTrendCtx = revenueTrendCanvas.getContext('2d');

        // Always create chart, even with no data
        const hasData = revenueTrendData.length > 0;
        const labels = hasData
            ? revenueTrendData.map(item => item.date)
            : ['No Data'];
        const data = hasData
            ? revenueTrendData.map(item => parseFloat(item.revenue) || 0)
            : [0];

        const hasRealData = hasAnyPositiveValue(data);
        if (!hasRealData) {
            showNoDataMessage('revenueTrendChart', 'fas fa-chart-line', 'No Data Available', 'No revenue trend found for the selected period');
        } else {
            window.reportsCharts.revenueTrendChart = new Chart(revenueTrendCtx, {
                type: 'line',
                data: {
                    labels: labels,
                    datasets: [{
                        label: 'Revenue',
                        data: data,
                        backgroundColor: hasData ? chartColors.maroon : 'rgba(200, 200, 200, 0.3)',
                        borderColor: hasData ? '#660000' : 'rgba(200, 200, 200, 0.5)',
                        borderWidth: 2
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        legend: {
                            display: false
                        },
                        tooltip: {
                            enabled: hasData
                        }
                    },
                    scales: {
                        y: {
                            beginAtZero: true,
                            grid: {
                                display: true,
                                color: 'rgba(0, 0, 0, 0.1)'
                            },
                            ticks: {
                                stepSize: 1,
                                font: { size: 12 }
                            }
                        },
                        x: {
                            grid: {
                                display: true,
                                color: 'rgba(0, 0, 0, 0.1)'
                            },
                            ticks: {
                                font: { size: 12 }
                            }
                        }
                    }
                }
            });
        }
    }
}

// Override or extend initPageScripts for reports page
// This will be called from the reports view
// Store original if it exists (from dashboard.js)
const originalInitPageScripts = window.initPageScripts;

window.initPageScripts = function () {
    // Check if we're on reports page by checking for reports-specific canvas elements
    const reportsCanvas = document.getElementById('revenuePlanChart');
    if (reportsCanvas && typeof window.reportsData !== 'undefined') {
        // Clean up any dashboard charts that might exist
        if (typeof window.dashboardCharts !== 'undefined') {
            Object.keys(window.dashboardCharts).forEach(key => {
                const chart = window.dashboardCharts[key];
                if (chart) {
                    try {
                        chart.destroy();
                    } catch (error) {
                        console.warn(`⚠️ Error destroying dashboard chart ${key}:`, error);
                    }
                    window.dashboardCharts[key] = null;
                }
            });
        }
        initReportsCharts();
        return;
    }

    // Check if we're on dashboard page by checking for dashboard-specific canvas elements
    const dashboardCanvas = document.getElementById('revenueChart');
    if (dashboardCanvas && typeof window.dashboardData !== 'undefined' && typeof window.initDashboardCharts === 'function') {
        // Clean up reports charts if they exist
        if (typeof window.reportsCharts !== 'undefined') {
            Object.keys(window.reportsCharts).forEach(key => {
                const chart = window.reportsCharts[key];
                if (chart) {
                    try {
                        chart.destroy();
                    } catch (error) {
                        console.warn(`⚠️ Error destroying reports chart ${key}:`, error);
                    }
                    window.reportsCharts[key] = null;
                }
            });
        }
        window.initDashboardCharts();
        return;
    }

    // Fallback to original if it exists
    if (originalInitPageScripts && typeof originalInitPageScripts === 'function') {
        originalInitPageScripts();
    }
};

function initializeCharts() {
    // Destroy existing charts if they exist
    Object.keys(window.reportsCharts).forEach(key => {
        if (window.reportsCharts[key]) {
            window.reportsCharts[key].destroy();
            window.reportsCharts[key] = null;
        }
    });

    const chartColors = getReportsChartColors();

    // ====================================
    // REVENUE BY PLAN CHART (Pie Chart)
    // ====================================
    const revenuePlanCanvas = document.getElementById('revenuePlanChart');
    if (revenuePlanCanvas) {
        const revenuePlanData = window.reportsData?.revenueByPlan || [];
        const revenuePlanCtx = revenuePlanCanvas.getContext('2d');

        // Filter out plans with 0 revenue
        const plansWithRevenue = revenuePlanData.filter(item => (parseFloat(item.total_revenue) || 0) > 0);

        // Always create chart, even with no data
        const planLabels = plansWithRevenue.length > 0
            ? plansWithRevenue.map(item => item.plan_name)
            : ['No Data'];
        const planData = plansWithRevenue.length > 0
            ? plansWithRevenue.map(item => parseFloat(item.total_revenue) || 0)
            : [1]; // Dummy data to show empty state

        window.reportsCharts.revenuePlanChart = new Chart(revenuePlanCtx, {
            type: 'pie',
            data: {
                labels: planLabels,
                datasets: [{
                    data: planData,
                    backgroundColor: plansWithRevenue.length > 0 ? [
                        chartColors.maroon,
                        chartColors.gray,
                        '#990000',
                        '#666666',
                        '#800000',
                        '#383838'
                    ] : ['rgba(200, 200, 200, 0.3)'],
                    borderColor: chartColors.borderColor,
                    borderWidth: 2
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        position: 'bottom',
                        labels: {
                            font: { size: 12 },
                            padding: 10
                        }
                    },
                    tooltip: {
                        enabled: plansWithRevenue.length > 0,
                        callbacks: {
                            label: function (context) {
                                if (plansWithRevenue.length === 0) return 'No data available';
                                const value = context.parsed || 0;
                                return context.label + ': ₱' + value.toLocaleString();
                            }
                        }
                    }
                }
            }
        });
    }

    // ====================================
    // REVENUE BY AREA CHART (Bar Chart)
    // ====================================
    const revenueAreaCanvas = document.getElementById('revenueAreaChart');
    if (revenueAreaCanvas) {
        clearNoDataMessages('revenueAreaChart');
        const revenueAreaData = window.reportsData?.revenueByArea || [];
        const revenueAreaCtx = revenueAreaCanvas.getContext('2d');

        // Always create chart, even with no data
        const hasData = revenueAreaData.length > 0;
        const labels = hasData
            ? revenueAreaData.map(item => item.parking_area_name)
            : ['No Data'];
        const data = hasData
            ? revenueAreaData.map(item => parseFloat(item.total_bookings) || 0)
            : [0];

        const hasRealData = hasAnyPositiveValue(data);
        if (!hasRealData) {
            showNoDataMessage('revenueAreaChart', 'fas fa-map-marked-alt', 'No Data Available', 'No area bookings found for the selected period');
        } else {
            clearNoDataMessages('revenueAreaChart');
            window.reportsCharts.revenueAreaChart = new Chart(revenueAreaCtx, {
                type: 'bar',
                data: {
                    labels: labels,
                    datasets: [{
                        label: 'Bookings',
                        data: data,
                        backgroundColor: chartColors.maroon,
                        borderColor: '#660000',
                        borderWidth: 2
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        legend: { display: false },
                        tooltip: { enabled: true }
                    },
                    scales: {
                        y: {
                            beginAtZero: true,
                            grid: { display: true, color: 'rgba(0, 0, 0, 0.1)' },
                            ticks: { stepSize: 1, font: { size: 12 } }
                        },
                        x: {
                            grid: { display: true, color: 'rgba(0, 0, 0, 0.1)' },
                            ticks: { font: { size: 12 } }
                        }
                    }
                }
            });
        }
    }
    // ====================================
    // PEAK HOURS CHART (Line Chart)
    // ====================================
    const peakHoursCanvas = document.getElementById('peakHoursChart');
    if (peakHoursCanvas) {
        clearNoDataMessages('peakHoursChart');
        const rawPeakHours = window.reportsData?.peakHours || null;
        const peakHoursCtx = peakHoursCanvas.getContext('2d');

        // Format data for Chart.js
        // If data is already formatted (has labels and data), use it directly
        // Otherwise, transform from raw database format: [{hour: 8, booking_count: 5}, ...]
        let peakHoursData;
        if (rawPeakHours && rawPeakHours.labels && rawPeakHours.data) {
            // Already formatted
            peakHoursData = rawPeakHours;
        } else if (Array.isArray(rawPeakHours) && rawPeakHours.length > 0) {
            // Raw database format - need to transform
            // Initialize all 24 hours with 0
            const hourData = {};
            for (let h = 0; h < 24; h++) {
                hourData[h] = 0;
            }

            // Fill in actual data
            rawPeakHours.forEach(item => {
                const hour = parseInt(item.hour || item.hour_number || 0);
                const count = parseInt(item.booking_count || item.count || 0);
                if (hour >= 0 && hour < 24) {
                    hourData[hour] = count;
                }
            });

            // Convert to Chart.js format
            peakHoursData = {
                labels: [],
                data: []
            };

            for (let h = 0; h < 24; h++) {
                peakHoursData.labels.push(h + ':00');
                peakHoursData.data.push(hourData[h]);
            }
        } else {
            // Fallback to empty data with all 24 hours
            peakHoursData = {
                labels: [],
                data: []
            };
            for (let h = 0; h < 24; h++) {
                peakHoursData.labels.push(h + ':00');
                peakHoursData.data.push(0);
            }
        }

        const hasData = peakHoursData.data && peakHoursData.data.some(val => val > 0);

        if (!hasData) {
            showNoDataMessage('peakHoursChart', 'fas fa-clock', 'No Data Available', 'No peak hours data found for the selected period');
        } else {
            clearNoDataMessages('peakHoursChart');
            window.reportsCharts.peakHoursChart = new Chart(peakHoursCtx, {
                type: 'line',
                data: {
                    labels: peakHoursData.labels || [],
                    datasets: [{
                        label: 'Bookings',
                        data: peakHoursData.data || [],
                        borderColor: hasData ? chartColors.maroon : 'rgba(200, 200, 200, 0.5)',
                        backgroundColor: hasData ? chartColors.maroonLight : 'rgba(200, 200, 200, 0.1)',
                        borderWidth: 3,
                        tension: 0.4,
                        fill: true,
                        pointRadius: hasData ? 4 : 0,
                        pointHoverRadius: hasData ? 6 : 0
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        legend: {
                            display: false
                        },
                        tooltip: {
                            enabled: hasData
                        }
                    },
                    scales: {
                        y: {
                            beginAtZero: true,
                            grid: {
                                display: true,
                                color: 'rgba(0, 0, 0, 0.1)'
                            },
                            ticks: {
                                font: { size: 12 }
                            }
                        },
                        x: {
                            grid: {
                                display: true,
                                color: 'rgba(0, 0, 0, 0.1)'
                            },
                            ticks: {
                                font: { size: 12 }
                            }
                        }
                    }
                }
            });
        }
    }

    // ====================================
    // VEHICLE TYPES CHART (Doughnut Chart)
    // ====================================
    const vehicleTypesCanvas = document.getElementById('vehicleTypesChart');
    if (vehicleTypesCanvas) {
        clearNoDataMessages('vehicleTypesChart');
        const vehicleTypesData = window.reportsData?.vehicleTypes || [];
        const vehicleTypesCtx = vehicleTypesCanvas.getContext('2d');

        // Create map for all vehicle types
        const vehicleTypeMap = {
            'Car': 0,
            'Motorcycle': 0,
            'Bicycle': 0
        };

        vehicleTypesData.forEach(item => {
            vehicleTypeMap[item.vehicle_type] = parseInt(item.count) || 0;
        });

        const hasData = Object.values(vehicleTypeMap).some(val => val > 0);
        const dataValues = Object.values(vehicleTypeMap);

        if (!hasData) {
            showNoDataMessage('vehicleTypesChart', 'fas fa-car', 'No Data Available', 'No vehicle type data found for the selected period');
        } else {
            clearNoDataMessages('vehicleTypesChart');
            window.reportsCharts.vehicleTypesChart = new Chart(vehicleTypesCtx, {
                type: 'doughnut',
                data: {
                    labels: Object.keys(vehicleTypeMap),
                    datasets: [{
                        data: dataValues,
                        backgroundColor: [
                            chartColors.maroon,
                            chartColors.gray,
                            '#666666'
                        ],
                        borderColor: chartColors.borderColor,
                        borderWidth: 2
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        legend: {
                            position: 'bottom',
                            labels: {
                                font: { size: 12 }
                            }
                        },
                        tooltip: {
                            enabled: true
                        }
                    }
                }
            });
        }
    }

    // Feedback Rating Distribution Chart
    const feedbackRatingCanvas = document.getElementById('feedbackRatingChart');
    if (feedbackRatingCanvas) {
        clearNoDataMessages('feedbackRatingChart');
        const feedbackDist = window.reportsData?.feedbackRatingDistribution || [];
        const feedbackCtx = feedbackRatingCanvas.getContext('2d');

        const hasData = Array.isArray(feedbackDist) && feedbackDist.some(item => (parseInt(item.count) || 0) > 0);
        if (!hasData) {
            showNoDataMessage('feedbackRatingChart', 'fas fa-star', 'No Data Available', 'No feedback ratings found for the selected period');
        } else {
            const labels = feedbackDist.map(item => (item.rating || 0) + ' Star');
            const data = feedbackDist.map(item => parseInt(item.count) || 0);

            window.reportsCharts.feedbackRatingChart = new Chart(feedbackCtx, {
                type: 'bar',
                data: {
                    labels: labels,
                    datasets: [{
                        label: 'Count',
                        data: data,
                        backgroundColor: chartColors.maroon,
                        borderColor: '#660000',
                        borderWidth: 2
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        legend: {
                            display: false
                        }
                    },
                    scales: {
                        y: {
                            beginAtZero: true,
                            ticks: {
                                precision: 0
                            }
                        }
                    }
                }
            });
        }
    }

    // ====================================
    // USER GROWTH CHART (Line Chart)
    // ====================================
    const userGrowthCanvas = document.getElementById('userGrowthChart');
    if (userGrowthCanvas) {
        clearNoDataMessages('userGrowthChart');
        const userGrowthData = window.reportsData?.userGrowth || [];
        const userGrowthCtx = userGrowthCanvas.getContext('2d');

        const hasData = userGrowthData.length > 0;
        const labels = hasData
            ? userGrowthData.map(item => item.month)
            : ['No Data'];
        const data = hasData
            ? userGrowthData.map(item => parseInt(item.new_users) || 0)
            : [0];

        const hasRealData = hasAnyPositiveValue(data);
        if (!hasRealData) {
            showNoDataMessage('userGrowthChart', 'fas fa-users', 'No Data Available', 'No user growth data found for the selected period');
        } else {
            clearNoDataMessages('userGrowthChart');
            window.reportsCharts.userGrowthChart = new Chart(userGrowthCtx, {
                type: 'line',
                data: {
                    labels: labels,
                    datasets: [{
                        label: 'New Users',
                        data: data,
                        borderColor: chartColors.maroon,
                        backgroundColor: chartColors.maroonLight,
                        borderWidth: 2,
                        tension: 0.4,
                        fill: true,
                        pointRadius: 4,
                        pointHoverRadius: 6
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        legend: {
                            display: false
                        },
                        tooltip: {
                            enabled: true
                        }
                    },
                    scales: {
                        y: {
                            beginAtZero: true,
                            grid: {
                                display: true,
                                color: 'rgba(0, 0, 0, 0.1)'
                            },
                            ticks: {
                                font: { size: 12 }
                            }
                        },
                        x: {
                            grid: {
                                display: true,
                                color: 'rgba(0, 0, 0, 0.1)'
                            },
                            ticks: {
                                font: { size: 12 }
                            }
                        }
                    }
                }
            });
        }
    }

    // ====================================
    // REVENUE TREND CHART (Line Chart)
    // ====================================
    const revenueTrendCanvas = document.getElementById('revenueTrendChart');
    if (revenueTrendCanvas) {
        clearNoDataMessages('revenueTrendChart');
        const revenueTrendData = window.reportsData?.revenueTrend || [];
        const revenueTrendCtx = revenueTrendCanvas.getContext('2d');

        const hasData = revenueTrendData.length > 0;
        const labels = hasData
            ? revenueTrendData.map(item => item.date)
            : ['No Data'];
        const data = hasData
            ? revenueTrendData.map(item => parseFloat(item.daily_revenue) || 0)
            : [0];

        const hasRealData = hasAnyPositiveValue(data);
        if (!hasRealData) {
            showNoDataMessage('revenueTrendChart', 'fas fa-chart-line', 'No Data Available', 'No revenue trend data found for the selected period');
        } else {
            clearNoDataMessages('revenueTrendChart');

            window.reportsCharts.revenueTrendChart = new Chart(revenueTrendCtx, {
                type: 'line',
                data: {
                    labels: labels,
                    datasets: [{
                        label: 'Daily Revenue',
                        data: data,
                        borderColor: hasData ? chartColors.gray : 'rgba(200, 200, 200, 0.5)',
                        backgroundColor: hasData ? chartColors.grayLight : 'rgba(200, 200, 200, 0.1)',
                        borderWidth: 2,
                        tension: 0.4,
                        fill: true,
                        pointRadius: hasData ? 4 : 0,
                        pointHoverRadius: hasData ? 6 : 0
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        legend: {
                            display: false
                        },
                        tooltip: {
                            enabled: hasData,
                            callbacks: {
                                label: function (context) {
                                    return '₱' + context.parsed.y.toLocaleString();
                                }
                            }
                        }
                    },
                    scales: {
                        y: {
                            beginAtZero: true,
                            grid: {
                                display: true,
                                color: 'rgba(0, 0, 0, 0.1)'
                            },
                            ticks: {
                                callback: function (value) {
                                    return '₱' + value.toLocaleString();
                                },
                                font: { size: 12 }
                            }
                        },
                        x: {
                            grid: {
                                display: true,
                                color: 'rgba(0, 0, 0, 0.1)'
                            },
                            ticks: {
                                font: { size: 12 }
                            }
                        }
                    }
                }
            });
        }
    }

    console.log('✅ All reports charts initialized');
    initFeedbackPagination();

    // ====================================
    // ENHANCED CHARTS - NEW ADDITIONS
    // ====================================

    // Revenue by Hour Chart
    const revenueHourCanvas = document.getElementById('revenueHourChart');
    if (revenueHourCanvas) {
        clearNoDataMessages('revenueHourChart');
        const revenueHourData = window.reportsData?.revenueByHour || [];
        const revenueHourCtx = revenueHourCanvas.getContext('2d');

        const hasData = revenueHourData.length > 0 && revenueHourData.some(item => parseFloat(item.revenue) > 0);

        if (!hasData) {
            // Show "No Data" message
            revenueHourCanvas.style.display = 'none';
            const noDataDiv = document.createElement('div');
            noDataDiv.className = 'text-center text-muted py-5';
            noDataDiv.innerHTML = '<i class="fas fa-chart-bar fa-3x mb-3 opacity-50"></i><h5>No Data Available</h5><p class="small">No revenue data found for the selected period</p>';
            revenueHourCanvas.parentNode.appendChild(noDataDiv);
        } else {
            const labels = revenueHourData.map(item => item.hour + ':00');
            const data = revenueHourData.map(item => parseFloat(item.revenue) || 0);

            window.reportsCharts.revenueHourChart = new Chart(revenueHourCtx, {
                type: 'bar',
                data: {
                    labels: labels,
                    datasets: [{
                        label: 'Revenue (₱)',
                        data: data,
                        backgroundColor: chartColors.maroonLight,
                        borderColor: chartColors.maroon,
                        borderWidth: 1
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        legend: { display: false },
                        tooltip: {
                            callbacks: {
                                label: function (context) {
                                    return '₱' + context.parsed.y.toLocaleString();
                                }
                            }
                        }
                    },
                    scales: {
                        y: { beginAtZero: true }
                    }
                }
            });
        }
    }

    // Revenue by Day of Week Chart
    const revenueDayCanvas = document.getElementById('revenueDayChart');
    if (revenueDayCanvas) {
        clearNoDataMessages('revenueDayChart');
        const revenueDayData = window.reportsData?.revenueByDayOfWeek || [];
        const revenueDayCtx = revenueDayCanvas.getContext('2d');

        const hasData = revenueDayData.length > 0 && revenueDayData.some(item => parseFloat(item.revenue) > 0);

        if (!hasData) {
            revenueDayCanvas.style.display = 'none';
            const noDataDiv = document.createElement('div');
            noDataDiv.className = 'text-center text-muted py-5';
            noDataDiv.innerHTML = '<i class="fas fa-calendar-week fa-3x mb-3 opacity-50"></i><h5>No Data Available</h5><p class="small">No weekly revenue data found</p>';
            revenueDayCanvas.parentNode.appendChild(noDataDiv);
        } else {
            const labels = revenueDayData.map(item => item.day_name);
            const data = revenueDayData.map(item => parseFloat(item.revenue) || 0);

            window.reportsCharts.revenueDayChart = new Chart(revenueDayCtx, {
                type: 'bar',
                data: {
                    labels: labels,
                    datasets: [{
                        label: 'Revenue (₱)',
                        data: data,
                        backgroundColor: chartColors.maroonLight || 'rgba(128, 0, 0, 0.3)',
                        borderColor: chartColors.maroon || '#800000',
                        borderWidth: 1
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        legend: { display: false },
                        tooltip: {
                            callbacks: {
                                label: function (context) {
                                    return '₱' + context.parsed.y.toLocaleString();
                                }
                            }
                        }
                    },
                    scales: {
                        y: { beginAtZero: true }
                    }
                }
            });
        }
    }

    // Bookings by Day of Week Chart
    const bookingsDayCanvas = document.getElementById('bookingsDayChart');
    if (bookingsDayCanvas) {
        clearNoDataMessages('bookingsDayChart');
        const bookingsDayData = window.reportsData?.bookingsByDayOfWeek || [];
        const bookingsDayCtx = bookingsDayCanvas.getContext('2d');

        const hasData = bookingsDayData.length > 0 && bookingsDayData.some(item => parseInt(item.booking_count) > 0);

        if (!hasData) {
            bookingsDayCanvas.style.display = 'none';
            const noDataDiv = document.createElement('div');
            noDataDiv.className = 'text-center text-muted py-5';
            noDataDiv.innerHTML = '<i class="fas fa-calendar-alt fa-3x mb-3 opacity-50"></i><h5>No Data Available</h5><p class="small">No weekly booking data found</p>';
            bookingsDayCanvas.parentNode.appendChild(noDataDiv);
        } else {
            const labels = bookingsDayData.map(item => item.day_name);
            const data = bookingsDayData.map(item => parseInt(item.booking_count) || 0);

            window.reportsCharts.bookingsDayChart = new Chart(bookingsDayCtx, {
                type: 'line',
                data: {
                    labels: labels,
                    datasets: [{
                        label: 'Bookings',
                        data: data,
                        borderColor: chartColors.maroon || '#800000',
                        backgroundColor: chartColors.maroonLight || 'rgba(128, 0, 0, 0.1)',
                        borderWidth: 2,
                        tension: 0.4,
                        fill: true
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        legend: { display: false }
                    },
                    scales: {
                        y: { beginAtZero: true }
                    }
                }
            });
        }
    }

    // Bookings by Area Chart
    const bookingsAreaCanvas = document.getElementById('bookingsAreaChart');
    if (bookingsAreaCanvas) {
        const bookingsAreaData = window.reportsData?.revenueByArea || [];
        const bookingsAreaCtx = bookingsAreaCanvas.getContext('2d');

        const hasData = bookingsAreaData.length > 0;
        const labels = hasData ? bookingsAreaData.map(item => item.parking_area_name) : ['No Data'];
        const data = hasData ? bookingsAreaData.map(item => parseInt(item.total_bookings) || 0) : [0];

        window.reportsCharts.bookingsAreaChart = new Chart(bookingsAreaCtx, {
            type: 'bar',
            data: {
                labels: labels,
                datasets: [{
                    label: 'Bookings',
                    data: data,
                    backgroundColor: hasData ? (chartColors.maroonLight || 'rgba(128, 0, 0, 0.3)') : 'rgba(200, 200, 200, 0.3)',
                    borderColor: hasData ? (chartColors.maroon || '#800000') : 'rgba(200, 200, 200, 0.5)',
                    borderWidth: 1
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: { display: false }
                },
                scales: {
                    y: { beginAtZero: true }
                }
            }
        });
    }

    // Hourly Occupancy Chart
    const hourlyOccupancyCanvas = document.getElementById('hourlyOccupancyChart');
    if (hourlyOccupancyCanvas) {
        clearNoDataMessages('hourlyOccupancyChart');
        const hourlyOccupancyRaw = window.reportsData?.hourlyOccupancy || [];
        const hourlyOccupancyData = Array.isArray(hourlyOccupancyRaw.data)
            ? hourlyOccupancyRaw.data
            : hourlyOccupancyRaw;
        const hourlyOccupancyLabels = Array.isArray(hourlyOccupancyRaw.labels)
            ? hourlyOccupancyRaw.labels
            : (Array.isArray(hourlyOccupancyData) ? hourlyOccupancyData.map(item => item.hour) : []);
        const hourlyOccupancyCtx = hourlyOccupancyCanvas.getContext('2d');

        const hasData = Array.isArray(hourlyOccupancyData) && hourlyOccupancyData.some(item => {
            if (typeof item === 'number') {
                return item > 0;
            }
            return (parseInt(item.bookings) || 0) > 0;
        });

        if (!hasData) {
            hourlyOccupancyCanvas.style.display = 'none';
            const noDataDiv = document.createElement('div');
            noDataDiv.className = 'text-center text-muted py-5';
            noDataDiv.innerHTML = '<i class="fas fa-fire fa-3x mb-3 opacity-50"></i><h5>No Data Available</h5><p class="small">No hourly occupancy data found</p>';
            hourlyOccupancyCanvas.parentNode.appendChild(noDataDiv);
        } else {
            const labels = hourlyOccupancyLabels.length > 0
                ? hourlyOccupancyLabels
                : Array.from({ length: hourlyOccupancyData.length }, (_, idx) => idx);
            const data = hourlyOccupancyData.map(item => typeof item === 'number'
                ? item
                : (parseInt(item.bookings) || 0));

            // Create heatmap-like effect with gradient colors
            const backgroundColors = data.map(value => {
                if (value === 0) return 'rgba(200, 200, 200, 0.3)';
                const intensity = Math.min(value / Math.max(...data), 1);
                return `rgba(128, 0, 0, ${0.3 + intensity * 0.7})`;
            });

            window.reportsCharts.hourlyOccupancyChart = new Chart(hourlyOccupancyCtx, {
                type: 'bar',
                data: {
                    labels: labels,
                    datasets: [{
                        label: 'Occupancy',
                        data: data,
                        backgroundColor: backgroundColors,
                        borderColor: '#800000',
                        borderWidth: 1
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        legend: { display: false },
                        title: {
                            display: true,
                            text: 'Darker colors indicate higher occupancy'
                        }
                    },
                    scales: {
                        y: { beginAtZero: true }
                    }
                }
            });
        }
    }

    // User Activity Chart (Active vs Inactive)
    const userActivityCanvas = document.getElementById('userActivityChart');
    if (userActivityCanvas) {
        clearNoDataMessages('userActivityChart');
        const userAnalytics = window.reportsData?.userAnalytics || {};
        const userActivityCtx = userActivityCanvas.getContext('2d');

        const activeUsers = userAnalytics.active_users || 0;
        const totalUsers = userAnalytics.total_users || 0;
        const hasData = totalUsers > 0;

        if (!hasData) {
            userActivityCanvas.style.display = 'none';
            const noDataDiv = document.createElement('div');
            noDataDiv.className = 'text-center text-muted py-5';
            noDataDiv.innerHTML = '<i class="fas fa-users fa-3x mb-3 opacity-50"></i><h5>No Data Available</h5><p class="small">No user activity data found</p>';
            userActivityCanvas.parentNode.appendChild(noDataDiv);
        } else {
            const inactiveUsers = Math.max(0, totalUsers - activeUsers);

            window.reportsCharts.userActivityChart = new Chart(userActivityCtx, {
                type: 'doughnut',
                data: {
                    labels: ['Active Users', 'Inactive Users'],
                    datasets: [{
                        data: [activeUsers, inactiveUsers],
                        backgroundColor: [chartColors.maroon || '#800000', chartColors.gray || '#383838'],
                        borderWidth: 2
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        legend: { position: 'bottom' }
                    }
                }
            });
        }
    }

    // Subscription Chart
    const subscriptionCanvas = document.getElementById('subscriptionChart');
    if (subscriptionCanvas) {
        clearNoDataMessages('subscriptionChart');
        const subscriptionData = window.reportsData?.subscriptionDistribution || [];
        const subscriptionCtx = subscriptionCanvas.getContext('2d');

        const hasData = subscriptionData.length > 0 && subscriptionData.some(item => parseInt(item.subscription_count) > 0);

        if (!hasData) {
            subscriptionCanvas.style.display = 'none';
            const noDataDiv = document.createElement('div');
            noDataDiv.className = 'text-center text-muted py-5';
            noDataDiv.innerHTML = '<i class="fas fa-chart-pie fa-3x mb-3 opacity-50"></i><h5>No Data Available</h5><p class="small">No subscription data found</p>';
            subscriptionCanvas.parentNode.appendChild(noDataDiv);
        } else {
            const labels = subscriptionData.map(item => item.plan_name);
            const data = subscriptionData.map(item => parseInt(item.subscription_count) || 0);

            window.reportsCharts.subscriptionChart = new Chart(subscriptionCtx, {
                type: 'pie',
                data: {
                    labels: labels,
                    datasets: [{
                        data: data,
                        backgroundColor: [
                            chartColors.maroon,
                            chartColors.gray,
                            '#6c757d',
                            '#17a2b8'
                        ],
                        borderWidth: 2
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        legend: { position: 'bottom' }
                    }
                }
            });
        }
    }

    // Booking Status Chart
    const bookingStatusCanvas = document.getElementById('bookingStatusChart');
    if (bookingStatusCanvas) {
        clearNoDataMessages('bookingStatusChart');
        const bookingStatusData = window.reportsData?.bookingStatusBreakdown || [];
        const bookingStatusCtx = bookingStatusCanvas.getContext('2d');

        const hasData = bookingStatusData.length > 0 && bookingStatusData.some(item => parseInt(item.count) > 0);

        if (!hasData) {
            bookingStatusCanvas.style.display = 'none';
            const noDataDiv = document.createElement('div');
            noDataDiv.className = 'text-center text-muted py-5';
            noDataDiv.innerHTML = '<i class="fas fa-tasks fa-3x mb-3 opacity-50"></i><h5>No Data Available</h5><p class="small">No booking status data found</p>';
            bookingStatusCanvas.parentNode.appendChild(noDataDiv);
        } else {
            const labels = bookingStatusData.map(item => item.status);
            const data = bookingStatusData.map(item => parseInt(item.count) || 0);

            window.reportsCharts.bookingStatusChart = new Chart(bookingStatusCtx, {
                type: 'doughnut',
                data: {
                    labels: labels,
                    datasets: [{
                        data: data,
                        backgroundColor: [
                            '#800000', // completed - maroon
                            '#cc5500', // pending - orange maroon
                            '#660000', // cancelled - dark maroon
                            '#b30000'  // other - medium maroon
                        ],
                        borderWidth: 2
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        legend: { position: 'bottom' }
                    }
                }
            });
        }
    }

    // ====================================
    // GUEST BOOKINGS TREND CHART (Line Chart)
    // ====================================
    const guestBookingsTrendCanvas = document.getElementById('reportsGuestBookingsTrendChart');
    if (guestBookingsTrendCanvas && window.reportsData?.guestBookingsStats) {
        clearNoDataMessages('reportsGuestBookingsTrendChart');
        const guestBookingsTrendCtx = guestBookingsTrendCanvas.getContext('2d');
        const guestBookingsData = window.reportsData.guestBookingsStats;

        const bookingsByDate = guestBookingsData.guest_bookings_by_date || [];
        const labels = bookingsByDate.map(item => {
            const date = new Date(item.date);
            return date.toLocaleDateString('en-US', { month: 'short', day: 'numeric' });
        });
        const data = bookingsByDate.map(item => item.count);

        if (data.length === 0 || !hasAnyPositiveValue(data)) {
            showNoDataMessage('reportsGuestBookingsTrendChart', 'fas fa-user-clock', 'No Data Available', 'No guest bookings data found for the selected period');
        } else {
            window.reportsCharts.reportsGuestBookingsTrendChart = new Chart(guestBookingsTrendCtx, {
                type: 'line',
                data: {
                    labels: labels,
                    datasets: [{
                        label: 'Guest Bookings',
                        data: data,
                        borderColor: chartColors.maroon,
                        backgroundColor: chartColors.maroonLight,
                        borderWidth: 3,
                        fill: true,
                        tension: 0.4,
                        pointRadius: 5,
                        pointHoverRadius: 7,
                        pointBackgroundColor: chartColors.maroon,
                        pointBorderColor: '#fff',
                        pointBorderWidth: 2
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        legend: {
                            display: false
                        },
                        tooltip: {
                            backgroundColor: 'rgba(0, 0, 0, 0.8)',
                            titleColor: '#fff',
                            bodyColor: '#fff',
                            borderColor: chartColors.maroon,
                            borderWidth: 1,
                            padding: 12,
                            displayColors: false,
                            callbacks: {
                                label: function (context) {
                                    return 'Bookings: ' + context.parsed.y;
                                }
                            }
                        }
                    },
                    scales: {
                        x: {
                            grid: {
                                display: false
                            },
                            ticks: {
                                color: chartColors.gray
                            }
                        },
                        y: {
                            beginAtZero: true,
                            grid: {
                                color: chartColors.grayLight,
                                borderDash: [5, 5]
                            },
                            ticks: {
                                color: chartColors.gray,
                                stepSize: 1
                            }
                        }
                    }
                }
            });
        }
    }

    // ====================================
    // GUEST BOOKINGS VEHICLE TYPE CHART (Doughnut Chart)
    // ====================================
    const guestBookingsVehicleCanvas = document.getElementById('reportsGuestBookingsVehicleChart');
    if (guestBookingsVehicleCanvas && window.reportsData?.guestBookingsStats) {
        clearNoDataMessages('reportsGuestBookingsVehicleChart');
        const guestBookingsVehicleCtx = guestBookingsVehicleCanvas.getContext('2d');
        const guestBookingsData = window.reportsData.guestBookingsStats;

        const bookingsByVehicle = guestBookingsData.guest_bookings_by_vehicle || [];
        const vehicleLabels = bookingsByVehicle.map(item => item.vehicle_type || 'Unknown');
        const vehicleData = bookingsByVehicle.map(item => item.count);

        if (vehicleData.length === 0 || !hasAnyPositiveValue(vehicleData)) {
            showNoDataMessage('reportsGuestBookingsVehicleChart', 'fas fa-car', 'No Data Available', 'No vehicle data found');
        } else {
            const vehicleColors = [
                '#800000',  // deep maroon (match booking status)
                '#a83232',  // medium maroon
                '#cc5500',  // warm orange accent
                '#d9793f',  // soft amber
                '#f2a65a',  // light orange
                '#ffd7b5'   // pastel highlight
            ];

            window.reportsCharts.reportsGuestBookingsVehicleChart = new Chart(guestBookingsVehicleCtx, {
                type: 'doughnut',
                data: {
                    labels: vehicleLabels,
                    datasets: [{
                        data: vehicleData,
                        backgroundColor: vehicleColors.slice(0, vehicleLabels.length),
                        borderWidth: 2,
                        borderColor: chartColors.borderColor
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        legend: {
                            display: true,
                            position: 'bottom',
                            labels: {
                                padding: 15,
                                usePointStyle: false,
                                boxWidth: 18,
                                boxHeight: 12,
                                font: {
                                    size: 11,
                                    weight: '500'
                                }
                            }
                        },
                        tooltip: {
                            backgroundColor: 'rgba(0, 0, 0, 0.8)',
                            titleColor: '#fff',
                            bodyColor: '#fff',
                            borderColor: chartColors.maroon,
                            borderWidth: 1,
                            padding: 10,
                            callbacks: {
                                label: function (context) {
                                    const total = context.dataset.data.reduce((a, b) => a + b, 0);
                                    const percentage = ((context.parsed / total) * 100).toFixed(1);
                                    return context.label + ': ' + context.parsed + ' (' + percentage + '%)';
                                }
                            }
                        }
                    }
                }
            });
        }
    }

    // ====================================
    // GUEST BOOKINGS ATTENDANT CHART (Bar Chart)
    // ====================================
    const guestBookingsAttendantCanvas = document.getElementById('reportsGuestBookingsAttendantChart');
    if (guestBookingsAttendantCanvas && window.reportsData?.guestBookingsStats) {
        clearNoDataMessages('reportsGuestBookingsAttendantChart');
        const guestBookingsAttendantCtx = guestBookingsAttendantCanvas.getContext('2d');
        const guestBookingsData = window.reportsData.guestBookingsStats;

        const bookingsByAttendant = guestBookingsData.guest_bookings_by_attendant || [];
        const attendantLabels = bookingsByAttendant.map(item => {
            const name = item.attendant_name || 'Unknown';
            return name.length > 15 ? name.substring(0, 15) + '...' : name;
        });
        const attendantData = bookingsByAttendant.map(item => item.count);

        if (attendantData.length === 0 || !hasAnyPositiveValue(attendantData)) {
            showNoDataMessage('reportsGuestBookingsAttendantChart', 'fas fa-user-tie', 'No Data Available', 'No attendant data found');
        } else {
            window.reportsCharts.reportsGuestBookingsAttendantChart = new Chart(guestBookingsAttendantCtx, {
                type: 'bar',
                data: {
                    labels: attendantLabels,
                    datasets: [{
                        label: 'Guest Bookings',
                        data: attendantData,
                        backgroundColor: chartColors.maroon,
                        borderColor: chartColors.maroon,
                        borderWidth: 1
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        legend: {
                            display: false
                        },
                        tooltip: {
                            backgroundColor: 'rgba(0, 0, 0, 0.8)',
                            titleColor: '#fff',
                            bodyColor: '#fff',
                            borderColor: chartColors.maroon,
                            borderWidth: 1,
                            padding: 12,
                            displayColors: false,
                            callbacks: {
                                label: function (context) {
                                    return 'Bookings: ' + context.parsed.y;
                                }
                            }
                        }
                    },
                    scales: {
                        x: {
                            grid: {
                                display: false
                            },
                            ticks: {
                                color: chartColors.gray
                            }
                        },
                        y: {
                            beginAtZero: true,
                            grid: {
                                color: chartColors.grayLight,
                                borderDash: [5, 5]
                            },
                            ticks: {
                                color: chartColors.gray,
                                stepSize: 1
                            }
                        }
                    }
                }
            });
        }
    }

    console.log('✅ All enhanced reports charts initialized');
}

// Global function to load reports with filter (AJAX)
// Make sure this is available globally
window.loadReportsWithFilter = function (filter, startDate = null, endDate = null) {
    // Ensure BASE_URL is defined (should be set in scripts.php)
    const baseUrl = (typeof BASE_URL !== 'undefined') ? BASE_URL : (typeof window.BASE_URL !== 'undefined' ? window.BASE_URL : '/');
    let url = baseUrl + 'reports?filter=' + filter + '&filter_change=1';

    if (filter === 'custom' && startDate && endDate) {
        url += '&start_date=' + startDate + '&end_date=' + endDate;
        console.log('🔄 Loading reports with custom filter:', filter, 'from', startDate, 'to', endDate);
    } else {
        console.log('🔄 Loading reports with filter:', filter);
    }

    // Show loading animation in reports content area only
    $('#reportsContent').html(`
        <div class="d-flex justify-content-center align-items-center" style="min-height: 400px;">
            <div class="text-center">
                <div class="spinner-border text-primary mb-3" role="status">
                    <span class="visually-hidden">Loading...</span>
                </div>
                <h5 class="mb-2">Loading Reports...</h5>
                <p class="text-muted">Please wait while we update your data</p>
            </div>
        </div>
    `);

    // Load new data via AJAX
    $.ajax({
        url: url,
        type: 'GET',
        timeout: 10000,
        headers: {
            'X-Requested-With': 'XMLHttpRequest'
        },
        success: function (html) {
            // Replace only the reports content (not the filter)
            $('#reportsContent').html(html);

            // Update filter component display if custom dates were used
            setTimeout(function () {
                if (filter === 'custom' && startDate && endDate) {
                    if (typeof window.updateReportsFilterDisplay === 'function') {
                        window.updateReportsFilterDisplay('custom', startDate, endDate);
                    }
                } else if (filter) {
                    if (typeof window.updateReportsFilterDisplay === 'function') {
                        window.updateReportsFilterDisplay(filter, null, null);
                    }
                }

                // Re-initialize page scripts (which will recreate charts with new data)
                if (typeof window.initPageScripts === 'function') {
                    window.initPageScripts();
                }
            }, 150);

            console.log('✅ Reports updated with filter:', filter || 'today');
        },
        error: function (xhr, status, error) {
            console.error('❌ Error loading reports:', error);
            $('#reportsContent').html(`
                <div class="d-flex justify-content-center align-items-center" style="min-height: 400px;">
                    <div class="text-center">
                        <div class="text-danger mb-3">
                            <i class="fas fa-exclamation-triangle fa-3x"></i>
                        </div>
                        <h5 class="text-danger mb-2">Error Loading Reports</h5>
                        <p class="text-muted mb-3">Failed to load reports data. Please try again.</p>
                        <button class="btn btn-primary" onclick="window.loadReportsWithFilter('${filter}', '${startDate}', '${endDate}')">
                            <i class="fas fa-refresh me-2"></i>Retry
                        </button>
                    </div>
                </div>
            `);
        }
    });
};

// Log that the function is defined (for debugging)
console.log('✅ loadReportsWithFilter function defined:', typeof window.loadReportsWithFilter);
