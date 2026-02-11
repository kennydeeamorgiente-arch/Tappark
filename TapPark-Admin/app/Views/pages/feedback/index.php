<div class="container-fluid">
    <div class="card mb-4 border-0 shadow-sm">
        <div class="card-body py-4">
            <div class="d-flex justify-content-between align-items-center flex-wrap gap-3">
                <div>
                    <h2 class="mb-2 fw-bold">
                        <i class="fas fa-star me-3 text-primary"></i>Feedback
                    </h2>
                    <p class="mb-0 text-secondary">View user feedback and reply as admin</p>
                </div>
            </div>
        </div>
    </div>

    <!-- Stats Cards -->
    <div class="row g-4 mb-4">
        <!-- Total Feedback -->
        <div class="col-xl-3 col-lg-3 col-md-6 col-sm-6">
            <div class="card stats-card-modern h-100 border-0 shadow-sm hover-lift stats-card-maroon">
                <div class="card-body text-white position-relative overflow-hidden p-4">
                    <div class="d-flex align-items-center justify-content-between mb-3">
                        <div class="flex-grow-1">
                            <h5 class="mb-2 fw-semibold" style="font-size: 0.95rem; opacity: 0.9;">Total Feedback</h5>
                            <h2 class="mb-0 fw-bold text-white stat-value" id="stat-total" style="font-size: 2.5rem; line-height: 1.2;">
                                -
                            </h2>
                        </div>
                        <div class="stats-icon" style="font-size: 2.5rem; opacity: 0.2;">
                            <i class="fas fa-comments"></i>
                        </div>
                    </div>
                    <p class="mb-0 text-white" style="opacity: 0.8; font-size: 0.9rem;">
                        <i class="fas fa-clock me-2"></i>All time
                    </p>
                </div>
            </div>
        </div>
        
        <!-- Average Rating -->
        <div class="col-xl-3 col-lg-3 col-md-6 col-sm-6">
            <div class="card stats-card-modern h-100 border-0 shadow-sm hover-lift stats-card-maroon">
                <div class="card-body text-white position-relative overflow-hidden p-4">
                    <div class="d-flex align-items-center justify-content-between mb-3">
                        <div class="flex-grow-1">
                            <h5 class="mb-2 fw-semibold" style="font-size: 0.95rem; opacity: 0.9;">Average Rating</h5>
                            <h2 class="mb-0 fw-bold text-white stat-value" id="stat-average" style="font-size: 2.5rem; line-height: 1.2;">
                                -
                            </h2>
                        </div>
                        <div class="stats-icon" style="font-size: 2.5rem; opacity: 0.2;">
                            <i class="fas fa-star"></i>
                        </div>
                    </div>
                    <p class="mb-0 text-white" style="opacity: 0.8; font-size: 0.9rem;">
                        <i class="fas fa-star-half-alt me-2"></i>Out of 5.0
                    </p>
                </div>
            </div>
        </div>

        <!-- Positive Feedback -->
        <div class="col-xl-3 col-lg-3 col-md-6 col-sm-6">
            <div class="card stats-card-modern h-100 border-0 shadow-sm hover-lift stats-card-maroon">
                <div class="card-body text-white position-relative overflow-hidden p-4">
                    <div class="d-flex align-items-center justify-content-between mb-3">
                        <div class="flex-grow-1">
                            <h5 class="mb-2 fw-semibold" style="font-size: 0.95rem; opacity: 0.9;">Positive</h5>
                            <h2 class="mb-0 fw-bold text-white stat-value" id="stat-positive" style="font-size: 2.5rem; line-height: 1.2;">
                                -
                            </h2>
                        </div>
                        <div class="stats-icon" style="font-size: 2.5rem; opacity: 0.2;">
                            <i class="fas fa-thumbs-up"></i>
                        </div>
                    </div>
                    <p class="mb-0 text-white" style="opacity: 0.8; font-size: 0.9rem;">
                        <i class="fas fa-check-circle me-2"></i>4-5 Stars
                    </p>
                </div>
            </div>
        </div>

        <!-- Critical Issues -->
        <div class="col-xl-3 col-lg-3 col-md-6 col-sm-6">
            <div class="card stats-card-modern h-100 border-0 shadow-sm hover-lift stats-card-maroon">
                <div class="card-body text-white position-relative overflow-hidden p-4">
                    <div class="d-flex align-items-center justify-content-between mb-3">
                        <div class="flex-grow-1">
                            <h5 class="mb-2 fw-semibold" style="font-size: 0.95rem; opacity: 0.9;">Critical Issues</h5>
                            <h2 class="mb-0 fw-bold text-white stat-value" id="stat-critical" style="font-size: 2.5rem; line-height: 1.2;">
                                -
                            </h2>
                        </div>
                        <div class="stats-icon" style="font-size: 2.5rem; opacity: 0.2;">
                            <i class="fas fa-exclamation-circle"></i>
                        </div>
                    </div>
                    <p class="mb-0 text-white" style="opacity: 0.8; font-size: 0.9rem;">
                        <i class="fas fa-exclamation-triangle me-2"></i>1-3 Stars
                    </p>
                </div>
            </div>
        </div>
    </div>

    <div class="card shadow-sm mb-4" id="feedbackFiltersCard">
        <div class="card-body" style="padding: 1.5rem;">
            <div class="d-flex flex-wrap gap-3 align-items-end">
                <!-- Data storage for logic -->
                <input type="hidden" id="filterChanged" value="0">

                <div class="flex-grow-1" style="min-width: 200px;">
                    <label class="form-label"><i class="fas fa-filter me-2"></i>Rating</label>
                    <select class="form-select" id="feedbackRatingFilter">
                        <option value="">All Ratings</option>
                        <option value="5" <?= (string)($rating ?? '') === '5' ? 'selected' : '' ?>>5 Stars</option>
                        <option value="4" <?= (string)($rating ?? '') === '4' ? 'selected' : '' ?>>4 Stars</option>
                        <option value="3" <?= (string)($rating ?? '') === '3' ? 'selected' : '' ?>>3 Stars</option>
                        <option value="2" <?= (string)($rating ?? '') === '2' ? 'selected' : '' ?>>2 Stars</option>
                        <option value="1" <?= (string)($rating ?? '') === '1' ? 'selected' : '' ?>>1 Star</option>
                    </select>
                </div>
                <div class="flex-grow-1" style="min-width: 150px;">
                    <label class="form-label"><i class="fas fa-calendar-alt me-2"></i>Date From</label>
                    <input type="date" class="form-control" id="feedbackDateFrom" value="<?= esc((string)($date_from ?? '')) ?>">
                </div>
                <div class="flex-grow-1" style="min-width: 150px;">
                    <label class="form-label"><i class="fas fa-calendar-alt me-2"></i>Date To</label>
                    <input type="date" class="form-control" id="feedbackDateTo" value="<?= esc((string)($date_to ?? '')) ?>">
                </div>
                
                <!-- Action Buttons: Apply & Clear -->
                <div class="d-flex gap-2" id="feedbackFilterActions">
                    <button type="button" class="btn btn-primary" id="applyFeedbackFiltersBtn">
                        <i class="fas fa-filter me-2"></i>Apply
                    </button>
                    <button type="button" class="btn btn-secondary" id="clearFeedbackFiltersBtn">
                        <i class="fas fa-times me-2"></i>Clear
                    </button>
                </div>
            </div>
        </div>
    </div>

    <div id="feedbackContent" class="position-relative" style="min-height: 200px;">
        <!-- Loading Spinner Overlay -->
        <div id="feedbackLoadingOverlay" class="position-absolute top-0 start-0 w-100 h-100 d-none justify-content-center align-items-center" style="background: rgba(255,255,255,0.7); z-index: 10; border-radius: 8px;">
            <div class="spinner-border text-primary" role="status">
                <span class="visually-hidden">Loading...</span>
            </div>
        </div>
        <div id="feedbackListContainer">
            <?= view('pages/feedback/content', [
            'feedbacks' => $feedbacks ?? [],
            'pagination' => $pagination ?? null,
            'rating' => $rating ?? null,
            'status' => $status ?? null,
            'date_from' => $date_from ?? null,
            'date_to' => $date_to ?? null,
            'per_page' => $per_page ?? null,
            'sort_by' => $sort_by ?? null,
            'sort_dir' => $sort_dir ?? null
        ]) ?>
        </div>
    </div>
</div>

<script>
(function() {
    const $rating = $('#feedbackRatingFilter');
    const $dateFrom = $('#feedbackDateFrom');
    const $dateTo = $('#feedbackDateTo');
    const $clear = $('#clearFeedbackFiltersBtn');

    function getBaseUrl() {
        return (typeof window.APP_BASE_URL !== 'undefined' && window.APP_BASE_URL) ? window.APP_BASE_URL : (typeof window.BASE_URL !== 'undefined' ? window.BASE_URL : '');
    }

    function getCurrentParams() {
        return {
            rating: $rating.val() || '',
            date_from: $dateFrom.val() || '',
            date_to: $dateTo.val() || '',
            per_page: $('#feedbackPerPageSelect').val() || '',
            page: $('#feedbackCurrentPage').val() || '1',
            sort_by: $('#feedbackCurrentSortBy').val() || '',
            sort_dir: $('#feedbackCurrentSortDir').val() || ''
        };
    }

    function buildQuery(params) {
        const qs = new URLSearchParams();
        Object.keys(params || {}).forEach(k => {
            const v = params[k];
            if (v !== null && v !== undefined && String(v) !== '' && k !== 'list_only') {
                qs.set(k, v);
            }
        });
        return qs.toString();
    }

    function fetchStats() {
        $.ajax({
            url: getBaseUrl() + 'feedback/stats',
            method: 'GET',
            success: function(stats) {
                if (stats) {
                    $('#stat-total').text(Number(stats.total).toLocaleString());
                    $('#stat-average').text(Number(stats.average || 0).toFixed(1));
                    $('#stat-positive').text(Number(stats.positive).toLocaleString());
                    $('#stat-critical').text(Number(stats.critical).toLocaleString());
                }
            },
            error: function() {
                $('.stat-value').text('0');
            }
        });
    }

    function updateList(partialParams) {
        const params = Object.assign({}, getCurrentParams(), partialParams || {});
        params.list_only = '1';
        const url = getBaseUrl() + 'feedback?' + buildQuery(params) + '&list_only=1';

        // Show Spinner
        $('#feedbackLoadingOverlay').removeClass('d-none').addClass('d-flex');

        $.ajax({
            url: url,
            method: 'GET',
            success: function(html) {
                $('#feedbackLoadingOverlay').removeClass('d-flex').addClass('d-none');
                $('#feedbackListContainer').html(html);
            },
            error: function() {
                $('#feedbackLoadingOverlay').removeClass('d-flex').addClass('d-none');
                if (typeof window.loadPage === 'function') {
                    const fullUrl = 'feedback?' + buildQuery(params);
                    window.loadPage(fullUrl, 'Feedback');
                } else {
                    window.location.href = 'feedback?' + buildQuery(params);
                }
            }
        });
    }

    function buildUrl(rating) {
        const params = new URLSearchParams();
        if (rating) {
            params.set('rating', rating);
        }
        const qs = params.toString();
        return qs ? ('feedback?' + qs) : 'feedback';
    }

    // --- UI Logic for Filters ---
    
    // Apply Button Click
    $('#applyFeedbackFiltersBtn').off('click').on('click', function() {
        updateList({
            rating: $rating.val() || '',
            date_from: $dateFrom.val() || '',
            date_to: $dateTo.val() || '',
            page: '1'
        });
        // Optional: Hide actions after apply? users usually keep them visible or disable them
        // For consistency with Users page (if that was the logic), we usually just load
    });

    // Clear Button Click
    $clear.off('click').on('click', function() {
        $rating.val('');
        $dateFrom.val('');
        $dateTo.val('');
        
        // Hide actions since we reset
        // Or trigger update immediately for Clear
        updateList({
            rating: '',
            date_from: '',
            date_to: '',
            page: '1',
            sort_by: '',
            sort_dir: ''
        });
    });

    $(document).off('change', '#feedbackPerPageSelect').on('change', '#feedbackPerPageSelect', function() {
        updateList({
            per_page: $(this).val(),
            page: '1'
        });
    });

    $(document).off('click', '.feedback-sortable').on('click', '.feedback-sortable', function(e) {
        e.preventDefault();
        const sortBy = $(this).data('sort-by');
        if (!sortBy) return;
        const currentBy = $('#feedbackCurrentSortBy').val() || '';
        const currentDir = ($('#feedbackCurrentSortDir').val() || 'desc').toLowerCase();
        let nextDir = 'asc';
        if (currentBy === sortBy) {
            nextDir = currentDir === 'asc' ? 'desc' : 'asc';
        }
        updateList({
            sort_by: sortBy,
            sort_dir: nextDir,
            page: '1'
        });
    });

    $(document).off('click', '#feedbackPagination a.page-link').on('click', '#feedbackPagination a.page-link', function(e) {
        e.preventDefault();
        if ($(this).parent().hasClass('disabled') || $(this).parent().hasClass('active')) {
            return;
        }
        const page = $(this).data('page');
        if (!page) return;
        updateList({
            page: String(page)
        });
    });

    $(document).off('click', '#exportFeedbackBtn').on('click', '#exportFeedbackBtn', function() {
        const params = getCurrentParams();
        const exportUrl = getBaseUrl() + 'feedback/export?' + buildQuery(params);
        window.location.href = exportUrl;
    });

    // Initial Load
    fetchStats();
})();
</script>
