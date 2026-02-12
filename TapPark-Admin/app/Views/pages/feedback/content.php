<div class="card shadow-sm">
    <div class="card-header">
        <div class="d-flex justify-content-between align-items-center flex-wrap gap-3">
            <div>
                <h5 class="mb-0">Feedback List</h5>
                <small class="text-secondary">
                    <?php if (!empty($pagination)) : ?>
                        Showing <?= (int)($pagination['showing_from'] ?? 0) ?> to <?= (int)($pagination['showing_to'] ?? 0) ?> of <?= (int)($pagination['total'] ?? 0) ?> entries
                    <?php else : ?>
                        &nbsp;
                    <?php endif; ?>
                </small>
            </div>
            <div class="d-flex align-items-center gap-2">
                <label class="mb-0">Per Page:</label>
                <select class="form-select form-select-sm" style="min-width: 80px; width: auto;" id="feedbackPerPageSelect">
                    <?php 
                    $globalPerPage = session('app_settings')['records_per_page'] ?? 25;
                    $pp = (int)($pagination['per_page'] ?? $per_page ?? $globalPerPage); 
                    ?>
                    <option value="10" <?= $pp === 10 ? 'selected' : '' ?>>10</option>
                    <option value="25" <?= $pp === 25 ? 'selected' : '' ?>>25</option>
                    <option value="50" <?= $pp === 50 ? 'selected' : '' ?>>50</option>
                    <option value="100" <?= $pp === 100 ? 'selected' : '' ?>>100</option>
                </select>
                <button class="btn btn-success btn-sm" id="exportFeedbackBtn">
                    <i class="fas fa-file-excel me-2"></i>Export Feedback to CSV
                </button>
            </div>
        </div>
    </div>
    <div class="card-body">
        <input type="hidden" id="feedbackCurrentPage" value="<?= esc((string)($pagination['current_page'] ?? 1)) ?>">
        <input type="hidden" id="feedbackCurrentSortBy" value="<?= esc((string)($sort_by ?? '')) ?>">
        <input type="hidden" id="feedbackCurrentSortDir" value="<?= esc((string)($sort_dir ?? '')) ?>">
        <div class="table-responsive">
            <table class="table table-hover align-middle">
                <thead>
                    <tr>
                        <th>
                            <a href="#" class="feedback-sortable text-decoration-none text-body" data-sort-by="feedback_id">
                                ID
                                <i class="fas fa-sort text-secondary ms-1"></i>
                            </a>
                        </th>
                        <th>User</th>
                        <th>
                            <a href="#" class="feedback-sortable text-decoration-none text-body" data-sort-by="rating">
                                Rating
                                <i class="fas fa-sort text-secondary ms-1"></i>
                            </a>
                        </th>
                        <th>Feedback</th>
                        <th>Subscription</th>
                        <th>
                            <a href="#" class="feedback-sortable text-decoration-none text-body" data-sort-by="created_at">
                                Date
                                <i class="fas fa-sort text-secondary ms-1"></i>
                            </a>
                        </th>
                        <th class="text-end">Action</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($feedbacks)) : ?>
                        <tr>
                            <td colspan="7" class="text-center text-secondary py-4">No feedback found.</td>
                        </tr>
                    <?php else : ?>
                        <?php 
                        $counter = (int)($pagination['showing_from'] ?? 1);
                        foreach ($feedbacks as $index => $item) : 
                        ?>
                            <tr>
                                <td>#<?= $counter + $index ?></td>
                                <td>
                                    <div class="fw-semibold">
                                        <?= esc(trim(($item['first_name'] ?? '') . ' ' . ($item['last_name'] ?? ''))) ?>
                                    </div>
                                    <div class="small text-secondary"><?= esc($item['email'] ?? '') ?></div>
                                </td>
                                <td>
                                    <span class="badge bg-primary">
                                        <?= esc($item['rating']) ?>/5
                                    </span>
                                </td>
                                <td style="max-width: 420px;">
                                    <div class="text-truncate" title="<?= esc($item['content'] ?? '') ?>">
                                        <?= esc($item['content'] ?? '') ?>
                                    </div>
                                </td>
                                <td>
                                    <span class="badge bg-secondary">
                                        #<?= esc($item['subscription_id']) ?>
                                    </span>
                                    <?php if (!empty($item['subscription_status'])) : ?>
                                        <div class="small text-secondary"><?= esc($item['subscription_status']) ?></div>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <div class="small">
                                        <?= esc($item['created_at']) ?>
                                    </div>
                                </td>
                                <td class="text-end">
                                    <button
                                        type="button"
                                        class="btn btn-sm btn-outline-primary"
                                        onclick="loadPage('feedback/view/<?= esc($item['feedback_id']) ?>', 'Feedback')">
                                        View
                                    </button>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
    <?php if (!empty($pagination) && (int)($pagination['total_pages'] ?? 1) > 1) : ?>
        <div class="card-footer">
            <div class="d-flex justify-content-between align-items-center">
                <div id="feedbackPaginationInfo"></div>
                <nav>
                    <ul class="pagination mb-0" id="feedbackPagination">
                        <?php
                            $current = (int)($pagination['current_page'] ?? 1);
                            $totalPages = (int)($pagination['total_pages'] ?? 1);

                            $prevDisabled = $current <= 1;
                            $nextDisabled = $current >= $totalPages;
                        ?>

                        <li class="page-item <?= $prevDisabled ? 'disabled' : '' ?>">
                            <a class="page-link" href="#" data-page="<?= $current - 1 ?>">Previous</a>
                        </li>

                        <?php
                            $start = max(1, $current - 2);
                            $end = min($totalPages, $current + 2);

                            if ($start > 1) {
                                echo '<li class="page-item"><a class="page-link" href="#" data-page="1">1</a></li>';
                                if ($start > 2) {
                                    echo '<li class="page-item disabled"><span class="page-link">...</span></li>';
                                }
                            }

                            for ($i = $start; $i <= $end; $i++) {
                                $active = $i === $current ? 'active' : '';
                                echo '<li class="page-item ' . $active . '"><a class="page-link" href="#" data-page="' . $i . '">' . $i . '</a></li>';
                            }

                            if ($end < $totalPages) {
                                if ($end < $totalPages - 1) {
                                    echo '<li class="page-item disabled"><span class="page-link">...</span></li>';
                                }
                                echo '<li class="page-item"><a class="page-link" href="#" data-page="' . $totalPages . '">' . $totalPages . '</a></li>';
                            }
                        ?>

                        <li class="page-item <?= $nextDisabled ? 'disabled' : '' ?>">
                            <a class="page-link" href="#" data-page="<?= $current + 1 ?>">Next</a>
                        </li>
                    </ul>
                </nav>
            </div>
        </div>
    <?php endif; ?>
</div>
