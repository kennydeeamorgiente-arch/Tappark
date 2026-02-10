<?php
    $feedbackId = $feedback['feedback_id'] ?? null;
?>

<div class="container-fluid">
    <div class="card mb-4 border-0 shadow-sm">
        <div class="card-body py-4">
            <div class="d-flex justify-content-between align-items-center flex-wrap gap-3">
                <div>
                    <h2 class="mb-2 fw-bold">
                        <i class="fas fa-comment-dots me-3 text-primary"></i>Feedback Thread
                    </h2>
                    <p class="mb-0 text-muted">View and reply to feedback</p>
                </div>
                <div>
                    <button type="button" class="btn btn-outline-secondary" onclick="loadPage('feedback', 'Feedback')">
                        Back
                    </button>
                </div>
            </div>
        </div>
    </div>

    <div class="card shadow-sm mb-4">
        <div class="card-header bg-transparent">
            <div class="d-flex justify-content-between align-items-center flex-wrap gap-2">
                <div>
                    <div class="fw-bold">
                        #<?= esc($feedback['feedback_id'] ?? '') ?> - <?= esc(trim(($feedback['first_name'] ?? '') . ' ' . ($feedback['last_name'] ?? ''))) ?>
                    </div>
                    <div class="text-muted small"><?= esc($feedback['email'] ?? '') ?></div>
                </div>
                <div class="text-end">
                    <span class="badge bg-primary"><?= esc($feedback['rating'] ?? 0) ?>/5</span>
                    <div class="small text-muted"><?= esc($feedback['created_at'] ?? '') ?></div>
                </div>
            </div>
        </div>
        <div class="card-body">
            <div class="mb-0">
                <?= esc($feedback['content'] ?? '') ?>
            </div>
        </div>
    </div>

    <div class="card shadow-sm mb-4">
        <div class="card-header bg-transparent">
            <h5 class="mb-0">Replies</h5>
        </div>
        <div class="card-body">
            <?php if (empty($comments)) : ?>
                <div class="text-muted">No replies yet.</div>
            <?php else : ?>
                <div class="d-flex flex-column gap-3">
                    <?php foreach ($comments as $c) : ?>
                        <div class="p-3 rounded border">
                            <div class="d-flex justify-content-between align-items-center">
                                <div class="fw-semibold">
                                    <?php if (($c['role'] ?? '') === 'admin') : ?>
                                        Admin
                                    <?php else : ?>
                                        <?= esc(trim(($c['first_name'] ?? '') . ' ' . ($c['last_name'] ?? ''))) ?>
                                    <?php endif; ?>
                                    <span class="badge bg-secondary ms-2"><?= esc($c['role'] ?? '') ?></span>
                                </div>
                                <div class="small text-muted"><?= esc($c['created_at'] ?? '') ?></div>
                            </div>
                            <div class="mt-2">
                                <?= esc($c['comment'] ?? '') ?>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </div>
    </div>

    <div class="card shadow-sm">
        <div class="card-header bg-transparent">
            <h5 class="mb-0">Post Admin Reply</h5>
        </div>
        <div class="card-body">
            <div class="mb-3">
                <textarea class="form-control" id="adminReplyComment" rows="4" placeholder="Write your reply..."></textarea>
            </div>
            <button type="button" class="btn btn-maroon" id="sendAdminReplyBtn">Send Reply</button>
        </div>
    </div>
</div>

<script>
    (function() {
        var btn = document.getElementById('sendAdminReplyBtn');
        if (!btn) return;

        btn.addEventListener('click', function() {
            var commentEl = document.getElementById('adminReplyComment');
            var comment = commentEl ? commentEl.value : '';

            if (!comment || !comment.trim()) {
                alert('Please enter a reply.');
                return;
            }

            btn.disabled = true;

            $.ajax({
                url: BASE_URL + 'feedback/reply',
                type: 'POST',
                data: {
                    feedback_id: '<?= esc($feedbackId) ?>',
                    comment: comment
                },
                success: function(resp) {
                    btn.disabled = false;
                    if (resp && resp.success) {
                        loadPage('feedback/view/<?= esc($feedbackId) ?>', 'Feedback');
                        return;
                    }
                    alert((resp && resp.message) ? resp.message : 'Failed to post reply.');
                },
                error: function(xhr) {
                    btn.disabled = false;
                    alert('Failed to post reply.');
                }
            });
        });
    })();
</script>
