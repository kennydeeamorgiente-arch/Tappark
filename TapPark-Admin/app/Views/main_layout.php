<!DOCTYPE html>
<html lang="en" data-bs-theme="light">
<head>
    <?= view('partials/head') ?>
</head>
<body>
    <!-- Hidden CSRF Token for JavaScript -->
    <input type="hidden" name="csrf_test_name" value="<?= csrf_hash() ?>">
    
    <!-- Sidebar -->
    <?= view('partials/sidebar') ?>
    
    <!-- Main Content -->
    <main class="main-content" id="mainContent">
        <!-- Header / Top Navbar -->
        <?= view('partials/header') ?>
        
        <!-- Content Area (AJAX loads here) -->
        <div class="content-wrapper" id="contentArea" data-allow-unsafe-html="true">
            <!-- Default: Load dashboard on initial page load -->
            <div class="container-fluid py-4">
                <div class="text-center py-5">
                    <div class="spinner-border text-primary mb-3" role="status">
                        <span class="visually-hidden">Loading...</span>
                    </div>
                    <p class="mt-3">Loading Dashboard...</p>
                </div>
            </div>
        </div>
    </main>
    
    <!-- Sidebar Overlay (for mobile) -->
    <div class="sidebar-overlay" id="sidebarOverlay"></div>
    
    <!-- Global Modals -->
    <?= view('partials/modals/logout_modal') ?>
    <?= view('partials/modals/profile_modal') ?>
    <?= view('partials/modals/change_password_modal') ?>
    <?= view('partials/modals/crud_form_modal') ?>
    <?= view('partials/modals/delete_confirm_modal') ?>
    <?= view('partials/modals/view_details_modal') ?>
    <?= view('partials/modals/success_modal') ?>
    
    <!-- Scroll to Top Button -->
    <button id="scrollToTopBtn" class="scroll-to-top-btn" title="Back to top">
        <i class="fas fa-chevron-up"></i>
        <span class="btn-tooltip">Top</span>
    </button>
    
    <!-- Scripts -->
    <?= view('partials/scripts') ?>

    <script>
        $(document).ready(function() {
            <?php if (session()->getFlashdata('welcome_message')): ?>
                if (typeof showToast === 'function') {
                    showToast('<?= session()->getFlashdata('welcome_message') ?>', 'success');
                }
            <?php endif; ?>
        });
    </script>
</body>
</html>

