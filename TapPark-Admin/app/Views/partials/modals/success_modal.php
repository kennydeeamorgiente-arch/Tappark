<!-- ============================================
   SUCCESS MODAL
   Used for all success messages across the application
   ============================================ -->
<div class="modal fade" id="successModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <!-- Modal Header -->
            <div class="modal-header" style="background: linear-gradient(135deg, #800000 0%, #990000 100%);">
                <h5 class="modal-title text-white" id="successModalTitle">
                    <i class="fas fa-check-circle me-2"></i>
                    <span>Success</span>
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            
            <!-- Modal Body -->
            <div class="modal-body">
                <div class="text-center py-3">
                    <div class="mb-3">
                        <i class="fas fa-check-circle text-success" style="font-size: 3rem; color: #800000;"></i>
                    </div>
                    <p class="mb-0" id="successModalMessage">Operation completed successfully!</p>
                </div>
            </div>
            
            <!-- Modal Footer -->
            <div class="modal-footer">
                <button type="button" class="btn" style="background-color: #800000; border-color: #800000; color: white;" data-bs-dismiss="modal">
                    <i class="fas fa-check me-2"></i>OK
                </button>
            </div>
        </div>
    </div>
</div>
