<!-- Activity Logs Table -->
<div class="card analytics-card-modern shadow-sm">
    <div class="card-header bg-transparent border-0 d-flex justify-content-between align-items-center flex-wrap gap-2">
        <div>
            <h5 class="mb-1 fw-bold">
                <i class="fas fa-history text-maroon me-2"></i>Activity Logs
            </h5>
            <small class="text-muted" id="logsCountInfo">
                Showing <?= ($pagination['showing_from'] ?? 0) ?> to <?= ($pagination['showing_to'] ?? 0) ?> of <?= number_format($pagination['total'] ?? 0) ?> entries
            </small>
        </div>
        
        <!-- Pagination Controls in Header -->
        <div id="logsPaginationContainer">
            <?php if (($pagination['total_pages'] ?? 0) > 1): ?>
                <nav aria-label="Activity logs pagination">
                    <ul class="pagination mb-0">
                        <!-- Previous Button -->
                        <li class="page-item <?= ($pagination['current_page'] ?? 1) <= 1 ? 'disabled' : '' ?>">
                            <a class="page-link" href="#" data-page="<?= ($pagination['current_page'] ?? 1) - 1 ?>" aria-label="Previous">
                                <span aria-hidden="true">&laquo;</span>
                            </a>
                        </li>
                        
                        <?php
                        // Calculate page range to display
                        $maxPages = 7; // Show max 7 page numbers
                        $currentPage = $pagination['current_page'] ?? 1;
                        $totalPages = $pagination['total_pages'] ?? 1;
                        $startPage = max(1, $currentPage - 3);
                        $endPage = min($totalPages, $startPage + $maxPages - 1);
                        
                        // Adjust start if we're near the end
                        if ($endPage - $startPage < $maxPages - 1) {
                            $startPage = max(1, $endPage - $maxPages + 1);
                        }
                        
                        // First page
                        if ($startPage > 1):
                        ?>
                            <li class="page-item">
                                <a class="page-link" href="#" data-page="1">1</a>
                            </li>
                            <?php if ($startPage > 2): ?>
                                <li class="page-item disabled"><span class="page-link">...</span></li>
                            <?php endif; ?>
                        <?php endif; ?>
                        
                        <!-- Page Numbers -->
                        <?php for ($i = $startPage; $i <= $endPage; $i++): ?>
                            <li class="page-item <?= $i == $currentPage ? 'active' : '' ?>">
                                <a class="page-link" href="#" data-page="<?= $i ?>"><?= $i ?></a>
                            </li>
                        <?php endfor; ?>
                        
                        <!-- Last page -->
                        <?php if ($endPage < $totalPages): ?>
                            <?php if ($endPage < $totalPages - 1): ?>
                                <li class="page-item disabled"><span class="page-link">...</span></li>
                            <?php endif; ?>
                            <li class="page-item">
                                <a class="page-link" href="#" data-page="<?= $totalPages ?>"><?= $totalPages ?></a>
                            </li>
                        <?php endif; ?>
                        
                        <!-- Next Button -->
                        <li class="page-item <?= $currentPage >= $totalPages ? 'disabled' : '' ?>">
                            <a class="page-link" href="#" data-page="<?= $currentPage + 1 ?>" aria-label="Next">
                                <span aria-hidden="true">&raquo;</span>
                            </a>
                        </li>
                    </ul>
                </nav>
            <?php endif; ?>
        </div>
    </div>
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover mb-0 logs-table">
                <thead class="table-maroon">
                    <tr>
                        <th class="text-center" style="width: 5%;">#</th>
                        <th style="width: 15%;">Timestamp</th>
                        <th style="width: 15%;">User</th>
                        <th style="width: 10%;">Action</th>
                        <th>Description</th>
                        <th style="width: 10%;">Target ID</th>
                    </tr>
                </thead>
                <tbody id="logsTableBody">
                    <?php if (!empty($logs)): ?>
                        <?php 
                        $counter = (int)($pagination['showing_from'] ?? 1);
                        foreach ($logs as $index => $log): 
                        ?>
                            <tr>
                                <td class="text-center">#<?= $counter + $index ?></td>
                                <td>
                                    <small>
                                        <?= date('M d, Y', strtotime($log['timestamp'])) ?><br>
                                        <span class="text-muted"><?= date('h:i A', strtotime($log['timestamp'])) ?></span>
                                    </small>
                                </td>
                                <td>
                                    <?php if (($log['user_id'] ?? 0) > 0 && !empty($log['first_name'])): ?>
                                        <div class="fw-bold"><?= esc($log['first_name'] . ' ' . ($log['last_name'] ?? '')) ?></div>
                                        <small class="text-muted"><?= esc($log['email'] ?? '') ?></small>
                                    <?php else: ?>
                                        <span class="text-muted">System</span>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <?php
                                    $badgeClass = 'secondary';
                                    $actionType = $log['action_type'] ?? 'GENERAL';
                                    
                                    switch ($actionType) {
                                        case 'CREATE':
                                            $badgeClass = 'success';
                                            break;
                                        case 'UPDATE':
                                        case 'STATUS_CHANGE':
                                            $badgeClass = 'info';
                                            break;
                                        case 'DELETE':
                                            $badgeClass = 'danger';
                                            break;
                                        case 'LOGIN':
                                            $badgeClass = 'primary';
                                            break;
                                        case 'LOGOUT':
                                            $badgeClass = 'dark';
                                            break;
                                        default:
                                            $badgeClass = 'secondary';
                                    }
                                    ?>
                                    <span class="badge bg-<?= $badgeClass ?>"><?= esc($actionType) ?></span>
                                </td>
                                <td><?= esc($log['description'] ?? '') ?></td>
                                <td>
                                    <?php if (!empty($log['target_id'])): ?>
                                        <span class="badge bg-light text-dark">#<?= esc($log['target_id']) ?></span>
                                    <?php else: ?>
                                        <span class="text-muted">-</span>
                                    <?php endif; ?>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="6" class="text-center py-4">
                                <i class="fas fa-inbox fa-3x text-muted mb-3"></i>
                                <p class="text-muted">No activity logs found</p>
                            </td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

