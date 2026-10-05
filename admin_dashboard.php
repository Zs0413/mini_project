<?php
// Start session and connect database
session_start();
include 'db.php';

// Validate administrative access
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header("Location: login.php");
    exit();
}

// Fetch KPI statistics
$rev_query =$conn->query("SELECT SUM(total_price) as revenue FROM bookings");
$revenue =$rev_query->fetch_assoc()['revenue'] ?? 0;

$book_query =$conn->query("SELECT COUNT(id) as total_bookings FROM bookings");
$total_bookings =$book_query->fetch_assoc()['total_bookings'] ?? 0;

$user_query =$conn->query("SELECT COUNT(id) as total_users FROM users WHERE role = 'customer'");
$total_users =$user_query->fetch_assoc()['total_users'] ?? 0;

$album_query =$conn->query("SELECT COUNT(id) as total_albums FROM albums WHERE status = 'visible'");
$total_albums =$album_query->fetch_assoc()['total_albums'] ?? 0;

include 'header.php';
include 'admin_sidebar.php';
?>

<style>
    /* KPI Card Styles */
    .kpi-card { border: none; border-radius: 16px; background: #fff; box-shadow: 0 4px 20px rgba(0,0,0,0.03); padding: 25px; display: flex; align-items: center; transition: transform 0.3s; }
    .kpi-card:hover { transform: translateY(-5px); }
    .kpi-icon { width: 60px; height: 60px; border-radius: 14px; display: flex; align-items: center; justify-content: center; font-size: 1.5rem; margin-right: 20px; }
    .icon-revenue { background: rgba(25, 135, 84, 0.1); color: #198754; }
    .icon-booking { background: rgba(13, 110, 253, 0.1); color: #0d6efd; }
    .icon-user { background: rgba(255, 193, 7, 0.1); color: #ffc107; }
    .icon-gallery { background: rgba(111, 66, 193, 0.1); color: #6f42c1; }
    
    /* Hide scrollbar for a cleaner live feed look */
    .live-table-container::-webkit-scrollbar { display: none; }
    .live-table-container { -ms-overflow-style: none; scrollbar-width: none; }
    
    @keyframes pulse {
        0% { opacity: 1; transform: scale(1); }
        50% { opacity: 0.5; transform: scale(1.1); }
        100% { opacity: 1; transform: scale(1); }
    }
    .pulse-animation { animation: pulse 1.5s infinite; }
</style>

<div class="main-content">
    
    <!-- System Notification Messages -->
    <?php if(isset($_SESSION['success_msg'])): ?>
        <div class="alert alert-success alert-dismissible fade show border-0 shadow-sm rounded-3" role="alert">
            <i class="fa-solid fa-check-circle me-2"></i> <?= $_SESSION['success_msg'] ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
        <?php unset($_SESSION['success_msg']); ?>
    <?php endif; ?>

    <?php if(isset($_SESSION['error_msg'])): ?>
        <div class="alert alert-danger alert-dismissible fade show border-0 shadow-sm rounded-3" role="alert">
            <i class="fa-solid fa-triangle-exclamation me-2"></i> <?= $_SESSION['error_msg'] ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
        <?php unset($_SESSION['error_msg']); ?>
    <?php endif; ?>

    <!-- Header Section -->
    <div class="d-flex justify-content-between align-items-center mb-5">
        <div>
            <h2 class="fw-bold text-dark mb-1">Command Center</h2>
            <p class="text-muted mb-0">Welcome back, <?= htmlspecialchars($_SESSION['fullname']) ?>.</p>
        </div>
        <div class="d-flex align-items-center">
            <span class="badge bg-white text-dark shadow-sm border px-3 py-2 rounded-pill fs-6" id="realtimeClock">
                <i class="fa-regular fa-clock me-2 text-success"></i> <?= date("d M Y, h:i A") ?>
            </span>
        </div>
    </div>

    <!-- KPI Summary Section -->
    <div class="row mb-5">
        <div class="col-xl-3 col-md-6 mb-4">
            <div class="kpi-card">
                <div class="kpi-icon icon-revenue"><i class="fa-solid fa-wallet"></i></div>
                <div>
                    <p class="text-muted small fw-bold mb-1 text-uppercase">Total Revenue</p>
                    <h3 class="fw-bold mb-0 text-dark">RM <?= number_format($revenue, 2) ?></h3>
                </div>
            </div>
        </div>
        <div class="col-xl-3 col-md-6 mb-4">
            <div class="kpi-card">
                <div class="kpi-icon icon-booking"><i class="fa-solid fa-ticket"></i></div>
                <div>
                    <p class="text-muted small fw-bold mb-1 text-uppercase">Total Bookings</p>
                    <h3 class="fw-bold mb-0 text-dark"><?= $total_bookings ?></h3>
                </div>
            </div>
        </div>
        <div class="col-xl-3 col-md-6 mb-4">
            <div class="kpi-card">
                <div class="kpi-icon icon-user"><i class="fa-solid fa-user-group"></i></div>
                <div>
                    <p class="text-muted small fw-bold mb-1 text-uppercase">Registered Users</p>
                    <h3 class="fw-bold mb-0 text-dark"><?= $total_users ?></h3>
                </div>
            </div>
        </div>
        <div class="col-xl-3 col-md-6 mb-4">
            <div class="kpi-card">
                <div class="kpi-icon icon-gallery"><i class="fa-regular fa-image"></i></div>
                <div>
                    <p class="text-muted small fw-bold mb-1 text-uppercase">Active Albums</p>
                    <h3 class="fw-bold mb-0 text-dark"><?= $total_albums ?></h3>
                </div>
            </div>
        </div>
    </div>

    <div class="row">
        <!-- Left Panel: Live Feed (Auto-scrolling) -->
        <div class="col-lg-8 mb-4">
            <div class="card border-0 shadow-sm" style="border-radius: 16px;">
                <div class="card-header bg-white border-0 py-3 d-flex justify-content-between align-items-center" style="border-radius: 16px 16px 0 0;">
                    <h5 class="fw-bold mb-0"><i class="fa-solid fa-satellite-dish text-danger me-2 pulse-animation"></i> Live Order Feed</h5>
                    <a href="admin_orders.php" class="btn btn-sm btn-primary fw-bold shadow-sm px-3">View Full Details <i class="fa-solid fa-arrow-right ms-1"></i></a>
                </div>
                
                <div class="card-body p-0 live-table-container" id="tableContainer" style="height: 350px; overflow-y: auto;">
                    <table class="table table-hover align-middle mb-0 text-center" id="feedTable">
                        <thead class="bg-light text-muted" style="position: sticky; top: 0; z-index: 1;">
                            <tr>
                                <th class="py-3">Ref ID</th>
                                <th class="py-3">Customer</th>
                                <th class="py-3">Travel Date</th>
                                <th class="py-3">Paid</th>
                                <th class="py-3">Status</th>
                            </tr>
                        </thead>
                        <tbody id="bookingTableBody" class="bg-white">
                            <!-- Populated via AJAX -->
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <!-- Right Panel: Expanded Quick Links -->
        <div class="col-lg-4 mb-4">
            <div class="card border-0 shadow-sm h-100" style="border-radius: 16px;">
                <div class="card-header bg-white border-0 py-3">
                    <h5 class="fw-bold mb-0"><i class="fa-solid fa-bolt text-warning me-2"></i> Quick Actions</h5>
                </div>
                <div class="card-body">
                    <div class="d-grid gap-3">
                        <a href="admin_orders.php" class="btn btn-outline-info p-3 rounded-4 text-start d-flex align-items-center">
                            <i class="fa-solid fa-list-check fa-2x me-3"></i>
                            <div>
                                <h6 class="fw-bold mb-1">Process Orders</h6>
                                <small class="text-muted">Approve or reject receipts</small>
                            </div>
                        </a>
                        
                        <a href="admin_gallery.php" class="btn btn-outline-success p-3 rounded-4 text-start d-flex align-items-center">
                            <i class="fa-solid fa-camera-retro fa-2x me-3"></i>
                            <div>
                                <h6 class="fw-bold mb-1">Upload Photos</h6>
                                <small class="text-muted">Manage park gallery</small>
                            </div>
                        </a>
                        
                        <a href="admin_users.php" class="btn btn-outline-primary p-3 rounded-4 text-start d-flex align-items-center">
                            <i class="fa-solid fa-users-gear fa-2x me-3"></i>
                            <div>
                                <h6 class="fw-bold mb-1">Manage Users</h6>
                                <small class="text-muted">Search and remove accounts</small>
                            </div>
                        </a>
                        
                        <a href="reports.php" class="btn btn-outline-secondary p-3 rounded-4 text-start d-flex align-items-center">
                            <i class="fa-solid fa-file-invoice-dollar fa-2x me-3"></i>
                            <div>
                                <h6 class="fw-bold mb-1">Financial Reports</h6>
                                <small class="text-muted">Export and analyze revenue</small>
                            </div>
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
document.addEventListener("DOMContentLoaded", function() {
    
    function updateClock() {
        const clockElement = document.getElementById('realtimeClock');
        if (!clockElement) return;
        const now = new Date();
        const options = { day: '2-digit', month: 'short', year: 'numeric' };
        const dateString = now.toLocaleDateString('en-GB', options);
        
        const timeString = now.toLocaleTimeString('en-US', { 
            hour: '2-digit', 
            minute: '2-digit',
            second: '2-digit',
            hour12: true 
        });
        clockElement.innerHTML = `<i class="fa-regular fa-clock me-2 text-success"></i> ${dateString}, ${timeString}`;
    }
    updateClock();
    setInterval(updateClock, 1000);

    // Fetch Live Dashboard Feed
    function fetchDashboardFeed() {
        fetch(`ajax_dashboard_feed.php`)
        .then(response => response.text())
        .then(data => {
            document.getElementById('bookingTableBody').innerHTML = data;
        });
    }
    fetchDashboardFeed();
    setInterval(fetchDashboardFeed, 10000);

    // Smooth Auto-Scrolling Logic
    const container = document.getElementById('tableContainer');
    let scrollSpeed = 1; 
    let scrollInterval;

    function startAutoScroll() {
        scrollInterval = setInterval(() => {
            if (container.scrollTop + container.clientHeight >= container.scrollHeight - 1) {
                container.scrollTop = 0;
            } else {
                container.scrollTop += scrollSpeed;
            }
        }, 50); 
    }

    function stopAutoScroll() {
        clearInterval(scrollInterval);
    }

    startAutoScroll();
    container.addEventListener('mouseenter', stopAutoScroll);
    container.addEventListener('mouseleave', startAutoScroll);
});
</script>

<?php include 'footer.php'; ?>