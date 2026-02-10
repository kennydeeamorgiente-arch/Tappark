<!-- ============================================
   LOGOUT CONFIRMATION MODAL
   Bootstrap modal for logout confirmation
   ============================================ -->
<div class="modal fade" id="logoutConfirmModal" tabindex="-1" aria-labelledby="logoutConfirmModalLabel" aria-hidden="true" data-bs-backdrop="static" data-bs-keyboard="false">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <!-- Modal Header -->
            <div class="modal-header" style="background: linear-gradient(135deg, #800000 0%, #990000 100%); border: none;">
                <h5 class="modal-title text-white fw-bold" id="logoutConfirmModalLabel">
                    Confirm Logout
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            
            <!-- Modal Body -->
            <div class="modal-body text-center py-4">
                <!-- SVG Icon -->
                <div class="mb-4">
                    <svg width="80" height="80" viewBox="0 0 80 80" fill="none" xmlns="http://www.w3.org/2000/svg" class="logout-svg-icon">
                        <circle cx="40" cy="40" r="38" fill="#fff5f5" stroke="#f8d7da" stroke-width="2"/>
                        <path d="M30 40L35 35M35 35L30 30M35 35H50C55 35 60 40 60 45V55" stroke="#800000" stroke-width="3" stroke-linecap="round" stroke-linejoin="round"/>
                        <path d="M25 30V50" stroke="#800000" stroke-width="2.5" stroke-linecap="round"/>
                    </svg>
                </div>
                
                <!-- Message -->
                <h5 class="mb-2 fw-semibold">Are you sure you want to logout?</h5>
                <p class="text-muted mb-0">
                    You will need to login again to access the system.
                </p>
            </div>
            
            <!-- Modal Footer -->
            <div class="modal-footer justify-content-center border-0 pt-0">
                <button type="button" class="btn btn-secondary px-4" data-bs-dismiss="modal">
                    Cancel
                </button>
                <button type="button" class="btn btn-danger px-4" id="confirmLogoutBtn">
                    Yes, Logout
                </button>
            </div>
        </div>
    </div>
</div>

<style>
/* Logout Modal Styles */
#logoutConfirmModal .modal-content {
    border-radius: 12px;
    border: none;
    box-shadow: 0 10px 40px rgba(0, 0, 0, 0.2);
    overflow: hidden;
}

#logoutConfirmModal .modal-header {
    border: none;
    padding: 1rem 1.5rem;
}

#logoutConfirmModal .modal-title {
    font-weight: 600;
}

#logoutConfirmModal .modal-body {
    padding: 2rem 1.5rem 1rem;
}

#logoutConfirmModal .logout-svg-icon {
    display: block;
    margin: 0 auto;
    transition: transform 0.3s ease;
}

#logoutConfirmModal .logout-svg-icon:hover {
    transform: scale(1.05);
}

#logoutConfirmModal .modal-footer {
    padding: 1rem 1.5rem 1.5rem;
    gap: 0.75rem;
}

#logoutConfirmModal .modal-footer .btn {
    border-radius: 8px;
    font-weight: 500;
    padding: 0.5rem 1.5rem;
}

/* Dark Mode */
[data-bs-theme="dark"] #logoutConfirmModal .modal-content {
    background: var(--card-bg, #2a1f1f);
}

[data-bs-theme="dark"] #logoutConfirmModal .modal-body h5 {
    color: var(--text-color, #e8d4d4);
}

[data-bs-theme="dark"] #logoutConfirmModal .logout-svg-icon circle {
    fill: #3a2020;
    stroke: rgba(128, 0, 0, 0.3);
}

[data-bs-theme="dark"] #logoutConfirmModal .logout-svg-icon path {
    stroke: #ff9999;
}

/* Prevent body scroll when modal is open - Strong method */
body.modal-open {
    overflow: hidden !important;
    position: fixed !important;
    width: 100% !important;
    height: 100% !important;
    top: 0 !important;
    left: 0 !important;
    right: 0 !important;
    bottom: 0 !important;
}

/* Prevent scroll on html as well */
html.modal-open {
    overflow: hidden !important;
    height: 100% !important;
}

/* Prevent scroll on main content wrapper */
.modal-open .content-wrapper,
.modal-open .main-content {
    overflow: hidden !important;
    pointer-events: none;
}
</style>


