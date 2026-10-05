<style>
    /* Sidebar Base Styles */
    .admin-sidebar {
        width: 260px;
        height: 100vh;
        position: fixed;
        top: 0;
        left: 0;
        background-color: #198754 !important;
        z-index: 1040;
        transition: transform 0.3s ease-in-out;
        display: flex !important;
        flex-direction: column;
    }
    /* Main Content Adjustment */
    .main-content {
        margin-left: 260px !important;
        padding: 30px;
        min-height: 100vh;
        background-color: #f8f9fa;
    }
    /* Sidebar Link Styles */
    .sidebar-link {
        color: rgba(255, 255, 255, 0.7);
        padding: 14px 20px;
        display: flex;
        align-items: center;
        text-decoration: none;
        font-weight: 500;
        border-radius: 10px;
        margin: 0 15px 8px 15px;
        transition: all 0.2s ease;
    }
    .sidebar-link:hover {
        background-color: rgba(255, 255, 255, 0.15);
        color: #ffffff;
        transform: translateX(5px);
    }
    .sidebar-link.active {
        background-color: #ffffff;
        color: #198754;
        font-weight: 700;
        box-shadow: 0 4px 10px rgba(0, 0, 0, 0.1);
    }
    /* Mobile Topbar Hidden on Desktop */
    .mobile-topbar {
        display: none;
        background-color: #198754;
        padding: 15px 20px;
        color: white;
    }
    /* Responsive Mobile Layout */
    @media (max-width: 991.98px) {
        .main-content {
            margin-left: 0 !important;
            padding: 20px 15px;
        }
        .mobile-topbar {
            display: flex;
            justify-content: space-between;
            align-items: center;
            position: sticky;
            top: 0;
            z-index: 1030;
        }
    }
</style>

<!-- Mobile Topbar -->
<div class="mobile-topbar shadow-sm d-lg-none">
    <h5 class="mb-0 fw-bold tracking-wider">
        <i class="fa-solid fa-water me-2"></i>SPLASH ADMIN
    </h5>
    <!-- Offcanvas trigger -->
    <button class="btn btn-outline-light btn-sm shadow-none" type="button" data-bs-toggle="offcanvas" data-bs-target="#adminSidebar">
        <i class="fa-solid fa-bars fs-5"></i>
    </button>
</div>

<!-- Responsive Sidebar -->
<div class="offcanvas-lg offcanvas-start admin-sidebar bg-success shadow-lg" tabindex="-1" id="adminSidebar">
    <!-- Sidebar Header -->
    <div class="offcanvas-header d-flex flex-column align-items-start px-4 pt-4 pb-3 border-bottom border-white border-opacity-25 flex-shrink-0">
        <div class="d-flex justify-content-between align-items-center w-100">
            <h4 class="text-white fw-bold mb-0 tracking-wider">
                <i class="fa-solid fa-water me-2"></i>SPLASH
            </h4>
            <button type="button" class="btn-close btn-close-white d-lg-none shadow-none" data-bs-dismiss="offcanvas" data-bs-target="#adminSidebar"></button>
        </div>
        <span class="badge bg-white text-success mt-2 px-3 py-1 rounded-pill shadow-sm">Command Center</span>
    </div>

    <!-- Sidebar Menu Links -->
    <div class="offcanvas-body flex-column px-0 py-4 overflow-y-auto flex-grow-1">
        <?php $curr = basename($_SERVER['PHP_SELF']); ?>
        <nav class="w-100">
            <a href="admin_dashboard.php" class="sidebar-link <?= $curr == 'admin_dashboard.php' ? 'active' : '' ?>">
                <i class="fa-solid fa-chart-pie me-3 fs-5"></i> Dashboard
            </a>
            <a href="admin_orders.php" class="sidebar-link <?= $curr == 'admin_orders.php' ? 'active' : '' ?>">
                <i class="fa-solid fa-list-check me-3 fs-5"></i> Order Management
            </a>
            <a href="admin_users.php" class="sidebar-link <?= $curr == 'admin_users.php' ? 'active' : '' ?>">
                <i class="fa-solid fa-users me-3 fs-5"></i> Manage Users
            </a>
            <a href="admin_gallery.php" class="sidebar-link <?= $curr == 'admin_gallery.php' ? 'active' : '' ?>">
                <i class="fa-solid fa-images me-3 fs-5"></i> Gallery Studio
            </a>
            <a href="reports.php" class="sidebar-link <?= $curr == 'reports.php' ? 'active' : '' ?>">
                <i class="fa-solid fa-file-invoice-dollar me-3 fs-5"></i> Reports & Analytics
            </a>
        </nav>
    </div>

    <!-- Logout Button -->
    <div class="px-4 pb-4 pt-3 flex-shrink-0" style="background-color: #198754;">
        <hr class="border-white border-opacity-25 mb-4 mt-0">
        <a href="logout.php" class="btn btn-light text-danger w-100 fw-bold rounded-pill py-2 shadow-sm d-flex justify-content-center align-items-center">
            <i class="fa-solid fa-arrow-right-from-bracket me-2"></i> Logout
        </a>
    </div>
</div>