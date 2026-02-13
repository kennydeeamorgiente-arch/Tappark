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
        <div class="card-body" id="feedback-comments-thread">
            <?php if (empty($comments)) : ?>
                <div class="text-muted no-replies-msg">No replies yet.</div>
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
        <div class="card-header bg-transparent d-flex justify-content-between align-items-center">
            <h5 class="mb-0">Post Admin Reply</h5>
            <div class="d-flex align-items-center gap-2">
                <label class="small text-muted mb-0">Quick Reply:</label>
                <select class="form-select form-select-sm" id="cannedResponseSelect" style="width: auto; min-width: 180px;">
                    <option value="">-- Select Template --</option>
                    <option value="Thank you for your feedback! We have noted this and will look into it.">Thank You / Noted</option>
                    <option value="We apologize for the inconvenience. Our team is working to resolve this as soon as possible.">Apology / Fixing</option>
                    <option value="Thank you for sharing your thoughts. We appreciate your input to help us improve our service.">Appreciation / UX</option>
                    <option value="This issue has been resolved. Thank you for your patience.">Issue Resolved</option>
                    <option value="We need more details regarding this. Could you please provide more information?">Requesting Info</option>
                </select>
            </div>
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
        var cannedSelect = document.getElementById('cannedResponseSelect');
        var commentEl = document.getElementById('adminReplyComment');

        if (!btn) return;

        // Canned Response Logic
        if (cannedSelect && commentEl) {
            cannedSelect.addEventListener('change', function() {
                var selectedValue = this.value;
                if (selectedValue) {
                    commentEl.value = selectedValue;
                    // Reset select after populating
                    this.value = '';
                }
            });
        }

        btn.addEventListener('click', function() {
            var comment = commentEl ? commentEl.value : '';

            if (!comment || !comment.trim()) {
                if (typeof showToast === 'function') {
                    showToast('Please enter a reply.', 'error');
                } else {
                    alert('Please enter a reply.');
                }
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
                        // Clear textarea
                        if (commentEl) commentEl.value = '';
                        
                        // Show success toast
                        if (typeof showToast === 'function') {
                            showToast('Reply posted successfully!', 'success');
                        }

                        // Build new comment HTML
                        var c = resp.comment;
                        var name = (c.role === 'admin') ? 'Admin' : ((c.first_name || '') + ' ' + (c.last_name || '')).trim();
                        
                        var commentHtml = `
                            <div class="p-3 rounded border border-primary-subtle bg-primary-subtle bg-opacity-10 animate__animated animate__fadeIn">
                                <div class="d-flex justify-content-between align-items-center">
                                    <div class="fw-semibold">
                                        ${name}
                                        <span class="badge bg-secondary ms-2">${c.role}</span>
                                    </div>
                                    <div class="small text-muted">${c.created_at}</div>
                                </div>
                                <div class="mt-2">
                                    ${c.comment.replace(/\n/g, '<br>')}
                                </div>
                            </div>
                        `;

                        var thread = $('#feedback-comments-thread');
                        var list = thread.find('.d-flex.flex-column');
                        
                        if (list.length === 0) {
                            // If first reply, remove empty msg and create list
                            thread.find('.no-replies-msg').remove();
                            thread.append('<div class="d-flex flex-column gap-3">' + commentHtml + '</div>');
                        } else {
                            list.append(commentHtml);
                        }

                        // Scroll to bottom of thread if needed
                        return;
                    }
                    
                    if (typeof showToast === 'function') {
                        showToast((resp && resp.message) ? resp.message : 'Failed to post reply.', 'error');
                    } else {
                        alert((resp && resp.message) ? resp.message : 'Failed to post reply.');
                    }
                },
                error: function(xhr) {
                    btn.disabled = false;
                    alert('Failed to post reply.');
                }
            });
        });
    })();
</script>
