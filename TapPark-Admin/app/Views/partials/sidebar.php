<!-- Sidebar Overlay for Mobile -->
<div class="sidebar-overlay" id="sidebarOverlay"></div>

<!-- Sidebar -->
<aside class="sidebar" id="sidebar">
    <!-- Sidebar Header -->
    <div class="sidebar-header">
        <img id="sidebarLogo" src="<?= base_url('assets/images/LOGOTAPPARKMINI.png') ?>" alt="TapPark Logo" class="sidebar-logo" 
             onerror="this.style.display='none'">
        <h2 class="sidebar-title">TapPark</h2>
        <p class="sidebar-subtitle">Admin Dashboard</p>
    </div>
    
    <!-- Sidebar Menu -->
    <ul class="sidebar-menu">
        <!-- Dashboard -->
        <li class="menu-item">
            <a href="#" class="menu-link" data-route="dashboard" data-title="Dashboard">
                <i class="fas fa-chart-bar"></i>
                <span>Dashboard</span>
            </a>
        </li>

        <!-- Reports -->
        <li class="menu-item">
            <a href="#" class="menu-link" data-route="reports" data-title="Reports">
                <i class="fas fa-chart-line"></i>
                <span>Reports</span>
            </a>
        </li>

        <!-- Feedback -->
        <li class="menu-item">
            <a href="#" class="menu-link" data-route="feedback" data-title="Feedback">
                <i class="fas fa-star"></i>
                <span>Feedback</span>
            </a>
        </li>
        
        <!-- User Management -->
        <li class="menu-item">
            <a href="#" class="menu-link" data-route="users" data-title="User Management">
                <i class="fas fa-users-cog"></i>
                <span>Users</span>
            </a>
        </li>
        
        <!-- Subscription Management -->
        <li class="menu-item">
            <a href="#" class="menu-link" data-route="subscriptions" data-title="Subscription Management">
                <i class="fas fa-crown"></i>
                <span>Subscriptions</span>
            </a>
        </li>

        <!-- Area & Section Management -->
        <li class="menu-item">
            <a href="#" class="menu-link" data-route="parking/areas" data-title="Area & Section Management">
                <i class="fas fa-parking"></i>
                <span>Areas &amp; Sections</span>
            </a>
        </li>

        <!-- Parking Overview & Layout -->
        <li class="menu-item">
            <a href="#" class="menu-link" data-route="parking/overview" data-title="Parking Overview">
                <i class="fas fa-th-large"></i>
                <span>Parking Layout</span>
            </a>
        </li>
        

        
        <!-- Logs -->
        <li class="menu-item">
            <a href="#" class="menu-link" data-route="logs" data-title="Activity Logs">
                <i class="fas fa-history"></i>
                <span>Logs</span>
            </a>
        </li>
    </ul>
    
    <!-- Bottom Section -->
    <div class="sidebar-footer">
        <!-- Logout Button -->
        <button class="logout-btn-full" onclick="confirmLogout()">
            <i class="fas fa-sign-out-alt"></i>
            <span>Logout</span>
        </button>

        <!-- User Profile & Settings Card -->
        <div class="user-profile-card" onclick="openProfileModal()" title="Profile & Settings">
            <div class="user-avatar-img">
                <?php 
                // Fetch profile picture from database
                $userModel = new \App\Models\UserModel();
                $userId = session()->get('user_id');
                $profilePic = null;
                
                if ($userId) {
                    $userData = $userModel->find($userId);
                    if ($userData && !empty($userData['profile_picture'])) {
                        $profilePic = $userData['profile_picture'];
                    }
                }
                
                // Fallback to session if database fetch failed
                if (empty($profilePic)) {
                    $profilePic = session()->get('profile_picture');
                }
                
                $firstName = session()->get('first_name') ?? 'A';
                $firstLetter = strtoupper(substr($firstName, 0, 1));
                $sidebarAvatarSrc = !empty($profilePic) && file_exists(ROOTPATH . 'public/uploads/profiles/' . $profilePic)
                    ? base_url('uploads/profiles/' . $profilePic)
                    : 'data:image/svg+xml;base64,' . base64_encode('<svg width="44" height="44" xmlns="http://www.w3.org/2000/svg"><rect width="44" height="44" fill="#800000"/><text x="50%" y="50%" font-family="Arial, sans-serif" font-size="20" font-weight="bold" fill="#ffffff" text-anchor="middle" dominant-baseline="central">' . htmlspecialchars($firstLetter) . '</text></svg>');
                ?>
                <img src="<?= esc($sidebarAvatarSrc) ?>" 
                     alt="User Avatar" 
                     id="sidebarUserAvatar">
            </div>
            <div class="user-info">
                <div class="user-name"><?= esc(session()->get('first_name') . ' ' . session()->get('last_name') ?? 'Admin User') ?></div>
                <div class="user-email"><?= esc(session()->get('email') ?? 'admin@tappark.com') ?></div>
            </div>
            <div class="user-actions">
                <i class="fas fa-user-cog" title="Profile & Settings"></i>
            </div>
        </div>
    </div>
</aside>

