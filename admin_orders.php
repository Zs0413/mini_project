<?php
// Start session and connect database
session_start();
include 'db.php';

// Validate administrative access
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header("Location: login.php");
    exit();
}

include 'header.php';
include 'admin_sidebar.php';
?>

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
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h2 class="fw-bold text-dark mb-1">Order Management</h2>
            <p class="text-muted mb-0">Search, verify receipts, and process customer orders.</p>
        </div>
        <a href="admin_dashboard.php" class="btn btn-light border shadow-sm"><i class="fa-solid fa-arrow-left me-2"></i>Back to Dashboard</a>
    </div>

    <!-- Auto-Search Bar -->
    <div class="card border-0 shadow-sm mb-4" style="border-radius: 16px;">
        <div class="card-body p-4">
            <h6 class="fw-bold text-success mb-3"><i class="fa-solid fa-filter me-2"></i>Live Smart Filter</h6>
            <div class="row g-3">
                <div class="col-md-4">
                    <label class="form-label text-muted small fw-bold">Search by Name / Ref ID</label>
                    <div class="input-group">
                        <span class="input-group-text bg-light border-end-0"><i class="fa-solid fa-magnifying-glass text-muted"></i></span>
                        <input type="text" id="searchKeyword" class="form-control bg-light border-start-0" placeholder="Type to search..." onkeyup="liveSearch()">
                    </div>
                </div>
                <div class="col-md-4">
                    <label class="form-label text-muted small fw-bold">Filter by Travel Date</label>
                    <input type="date" id="searchDate" class="form-control bg-light" onchange="liveSearch()">
                </div>
                <div class="col-md-4">
                    <label class="form-label text-muted small fw-bold">Filter by Status</label>
                    <select id="searchStatus" class="form-select bg-light" onchange="liveSearch()">
                        <option value="">All Statuses</option>
                        <option value="Paid">First Upload (Verifying)</option>
                        <option value="Re-upload">Re-uploaded (Verifying)</option>
                        <option value="Pending">Pending Upload</option>
                        <option value="Confirm">Success</option>
                        <option value="Rejected">Rejected</option>
                    </select>
                </div>
            </div>
        </div>
    </div>

    <!-- Data Table -->
    <div class="card border-0 shadow-sm" style="border-radius: 16px;">
        <div class="card-body p-0">
            <table class="table table-hover align-middle mb-0 text-center">
                <thead class="bg-success text-white" style="position: sticky; top: 0; z-index: 1;">
                    <tr>
                        <th class="py-3">Ref ID</th>
                        <th class="py-3">Customer</th>
                        <th class="py-3">Tickets (Type & Qty)</th>
                        <th class="py-3">Travel Date</th>
                        <th class="py-3">Paid (RM)</th>
                        <th class="py-3">Status</th>
                        <th class="py-3">Action & Verification</th>
                    </tr>
                </thead>
                <tbody id="ordersTableBody" class="bg-white">
                    <!-- Populated via AJAX Auto-search -->
                </tbody>
            </table>
        </div>
    </div>
</div>

<script>
// Auto-Search AJAX Implementation
function liveSearch() {
    let keyword = document.getElementById('searchKeyword').value;
    let date = document.getElementById('searchDate').value;
    let status = document.getElementById('searchStatus').value;
    
    let queryParams = new URLSearchParams({
        keyword: keyword,
        date: date,
        status: status
    });
    fetch(`ajax_orders_list.php?${queryParams.toString()}`)
    .then(response => response.text())
    .then(data => {
        document.getElementById('ordersTableBody').innerHTML = data;
    });
}

// Trigger initial load when page opens
document.addEventListener("DOMContentLoaded", function() {
    liveSearch();
});
</script>

<?php include 'footer.php'; ?>