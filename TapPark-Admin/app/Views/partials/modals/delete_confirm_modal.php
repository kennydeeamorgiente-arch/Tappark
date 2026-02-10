<!-- ============================================
   UNIFIED DELETE CONFIRMATION MODAL
   One modal for delete confirmation across all entities
   ============================================ -->
<div class="modal fade" id="deleteConfirmModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <!-- Modal Header -->
            <div class="modal-header" style="background: linear-gradient(135deg, #800000 0%, #990000 100%);">
                <h5 class="modal-title text-white">
                    <i class="fas fa-exclamation-triangle me-2"></i>
                    Confirm Delete
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            
            <!-- Modal Body -->
            <div class="modal-body text-center py-4">
                <!-- Hidden Fields -->
                <input type="hidden" id="deleteEntityType" value="">
                <input type="hidden" id="deleteEntityId" value="">
                
                <!-- Warning Icon -->
                <div class="mb-3">
                    <div class="delete-icon-wrapper">
                        <i class="fas fa-trash-alt"></i>
                    </div>
                </div>
                
                <!-- Message -->
                <h5 class="mb-2 fw-semibold">Are you sure?</h5>
                <p class="text-muted mb-1">
                    You are about to delete 
                    <strong id="deleteEntityLabel" class="text-maroon">this item</strong>.
                </p>
                <p class="text-muted small mb-0">
                    This action cannot be undone.
                </p>
            </div>
            
            <!-- Modal Footer -->
            <div class="modal-footer justify-content-center border-0 pt-0">
                <button type="button" class="btn btn-secondary px-4" data-bs-dismiss="modal">
                    <i class="fas fa-times me-1"></i> Cancel
                </button>
                <button type="button" class="btn btn-maroon px-4" id="confirmDeleteBtn">
                    <i class="fas fa-trash me-1"></i> Yes, Delete
                </button>
            </div>
        </div>
    </div>
</div>

<style>
/* Delete Modal Styles */
#deleteConfirmModal .modal-content {
    border-radius: 12px;
    border: none;
    box-shadow: 0 10px 40px rgba(0, 0, 0, 0.2);
    overflow: hidden;
}

#deleteConfirmModal .modal-header {
    border: none;
    padding: 1rem 1.5rem;
}

#deleteConfirmModal .modal-title {
    font-weight: 600;
}

#deleteConfirmModal .modal-body {
    padding: 2rem 1.5rem 1rem;
}

#deleteConfirmModal .delete-icon-wrapper {
    width: 80px;
    height: 80px;
    border-radius: 50%;
    background: linear-gradient(135deg, #fff5f5 0%, #ffe0e0 100%);
    display: flex;
    align-items: center;
    justify-content: center;
    margin: 0 auto;
    border: 2px solid rgba(128, 0, 0, 0.2);
}

#deleteConfirmModal .delete-icon-wrapper i {
    font-size: 2rem;
    color: #800000;
}

#deleteConfirmModal .modal-footer {
    padding: 1rem 1.5rem 1.5rem;
    gap: 0.75rem;
}

#deleteConfirmModal .modal-footer .btn {
    border-radius: 8px;
    font-weight: 500;
    padding: 0.5rem 1.5rem;
}

/* Dark Mode */
[data-bs-theme="dark"] #deleteConfirmModal .modal-content {
    background: var(--card-bg, #ffffff);
}

[data-bs-theme="dark"] #deleteConfirmModal .delete-icon-wrapper {
    background: linear-gradient(135deg, #3a2020 0%, #4a2a2a 100%);
    border-color: rgba(220, 53, 69, 0.3);
}

[data-bs-theme="dark"] #deleteConfirmModal h5 {
    color: var(--text-color, #333333);
}
</style>

