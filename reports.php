<?php
// Start session and connect database
session_start();
include 'db.php';

// Stop normal users
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header("Location: login.php");
    exit();
}

// 1. Capture the filter parameter (default is '30days')
$filter = isset($_GET['filter']) ? $_GET['filter'] : '30days';

$calendar = [];
$total_revenue = 0;
$total_bookings = 0;

if ($filter === 'all_time') {
    $title_suffix = "All Time";
    $breakdown_label = "Monthly Breakdown";
    
    $min_date_query = $conn->query("SELECT MIN(created_at) as first_date FROM bookings");
    $first_date = $min_date_query->fetch_assoc()['first_date'];
    
    if (!$first_date) {
        $first_date = date('Y-m-01');
    }
    
    $start = new DateTime(date('Y-m-01', strtotime($first_date)));
    $end = new DateTime(date('Y-m-01'));
    $end->modify('+1 month'); 
    
    $interval = DateInterval::createFromDateString('1 month');
    $period = new DatePeriod($start, $interval, $end);
    
    foreach ($period as $dt) {
        $calendar[$dt->format("Y-m")] = 0; 
    }
    
    $chart_query = $conn->query("
        SELECT DATE_FORMAT(created_at, '%Y-%m') as trans_date, SUM(total_price) as daily_revenue, COUNT(id) as daily_bookings 
        FROM bookings 
        GROUP BY DATE_FORMAT(created_at, '%Y-%m')
    ");
} else {
    $title_suffix = "Last 30 Days";
    $breakdown_label = "Daily Breakdown";
    
    for ($i = 29; $i >= 0; $i--) {
        $date_string = date('Y-m-d', strtotime("-$i days"));
        $calendar[$date_string] = 0; 
    }
    
    $chart_query = $conn->query("
        SELECT DATE(created_at) as trans_date, SUM(total_price) as daily_revenue, COUNT(id) as daily_bookings 
        FROM bookings 
        WHERE created_at >= DATE_SUB(CURDATE(), INTERVAL 30 DAY) 
        GROUP BY DATE(created_at)
    ");
}

// 2. Merge database results into the continuous calendar
while($row = $chart_query->fetch_assoc()) {
    $date_key = $row['trans_date'];
    
    if (isset($calendar[$date_key])) {
        $calendar[$date_key] = $row['daily_revenue'];
    }
    $total_revenue += $row['daily_revenue'];
    $total_bookings += $row['daily_bookings'];
}

// 3. Format arrays for Chart.js display
$dates = [];
$revenues = [];

foreach ($calendar as $date_key => $revenue) {
    if ($filter === 'all_time') {
        $dates[] = date("M Y", strtotime($date_key . "-01")); 
    } else {
        $dates[] = date("d M", strtotime($date_key)); 
    }
    $revenues[] = $revenue;
}

include 'header.php';
include 'admin_sidebar.php';
?>

<div class="main-content">
    
    <!-- Page Header and Filters -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h2 class="fw-bold text-dark mb-1">Financial Reports</h2>
            <p class="text-muted mb-0">Analyze revenue trends and export financial data.</p>
        </div>
        
        <div class="d-flex align-items-center gap-3">
            <form method="GET" class="mb-0">
                <select name="filter" class="form-select shadow-sm border-0 fw-bold text-success" style="border-radius: 8px; cursor: pointer; background-color: white;" onchange="this.form.submit()">
                    <option value="30days" <?= $filter === '30days' ? 'selected' : '' ?>>Last 30 Days (Daily)</option>
                    <option value="all_time" <?= $filter === 'all_time' ? 'selected' : '' ?>>All Time (Monthly)</option>
                </select>
            </form>
            <a href="export_report.php?filter=<?= $filter ?>" class="btn btn-outline-success shadow-sm rounded-pill px-4 py-2 fw-bold transition-all">
                <i class="fa-solid fa-file-csv me-2"></i> CSV
            </a>
            <a href="print_report.php?filter=<?= $filter ?>" target="_blank" class="btn btn-success shadow-sm rounded-pill px-4 py-2 fw-bold transition-all">
                <i class="fa-solid fa-file-pdf me-2"></i> Print PDF
            </a>
        </div>
    </div>

    <!-- Mini Summary Cards -->
    <div class="row mb-4">
        <div class="col-md-6">
            <div class="card border-0 shadow-sm" style="border-radius: 12px; background: linear-gradient(135deg, #198754, #146c43);">
                <div class="card-body p-4 text-white">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <p class="mb-1 text-white-50 fw-bold text-uppercase small">Revenue (<?= $title_suffix ?>)</p>
                            <h3 class="fw-bold mb-0">RM <?= number_format($total_revenue, 2) ?></h3>
                        </div>
                        <div class="bg-white bg-opacity-25 rounded-circle d-flex align-items-center justify-content-center" style="width: 50px; height: 50px;">
                            <i class="fa-solid fa-wallet fs-4"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-6">
            <div class="card border-0 shadow-sm" style="border-radius: 12px; background: white;">
                <div class="card-body p-4">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <p class="mb-1 text-muted fw-bold text-uppercase small">Ticket Volume (<?= $title_suffix ?>)</p>
                            <h3 class="fw-bold mb-0 text-dark"><?= $total_bookings ?> <span class="fs-6 text-muted fw-normal">Orders</span></h3>
                        </div>
                        <div class="bg-success bg-opacity-10 text-success rounded-circle d-flex align-items-center justify-content-center" style="width: 50px; height: 50px;">
                            <i class="fa-solid fa-ticket fs-4"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Chart Container -->
    <div class="row">
        <div class="col-12">
            <div class="card border-0 shadow-sm" style="border-radius: 16px;">
                <div class="card-header bg-white border-0 py-4 px-4 d-flex justify-content-between align-items-center">
                    <h5 class="fw-bold mb-0"><i class="fa-solid fa-chart-area text-success me-2"></i> Revenue Trend Analysis</h5>
                    <span class="badge bg-light text-muted border"><?= $breakdown_label ?></span>
                </div>
                <div class="card-body px-4 pb-5">
                    <div style="position: relative; height: 360px; width: 100%;">
                        <canvas id="revenueChart"></canvas>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Load Chart.js Library -->
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
document.addEventListener("DOMContentLoaded", function() {
    const ctx = document.getElementById('revenueChart').getContext('2d');
    
    let gradientFill = ctx.createLinearGradient(0, 0, 0, 360);
    gradientFill.addColorStop(0, 'rgba(25, 135, 84, 0.4)'); 
    gradientFill.addColorStop(1, 'rgba(25, 135, 84, 0.0)'); 
    
    new Chart(ctx, {
        type: 'line', 
        data: {
            labels: <?= json_encode($dates) ?>,
            datasets: [{
                label: 'Revenue',
                data: <?= json_encode($revenues) ?>,
                backgroundColor: gradientFill,
                borderColor: '#198754',
                borderWidth: 3,
                fill: true, 
                tension: 0.4, 
                pointBackgroundColor: '#ffffff',
                pointBorderColor: '#198754',
                pointBorderWidth: 2,
                pointRadius: <?= $filter === 'all_time' ? 4 : 0 ?>,
                pointHoverRadius: 6, 
                pointHitRadius: 15
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false, 
            scales: { 
                y: { 
                    beginAtZero: true,
                    grid: { color: 'rgba(0, 0, 0, 0.03)', drawBorder: false },
                    ticks: {
                        callback: function(value) { return 'RM ' + value; }, 
                        font: { family: "'Inter', 'Segoe UI', sans-serif", size: 12, color: '#adb5bd' },
                        padding: 15
                    },
                    border: { display: false }
                },
                x: {
                    grid: { display: false, drawBorder: false },
                    ticks: {
                        maxTicksLimit: <?= $filter === 'all_time' ? 12 : 10 ?>,
                        maxRotation: 0,
                        font: { family: "'Inter', 'Segoe UI', sans-serif", size: 12, weight: '500' },
                        color: '#6c757d',
                        padding: 10
                    },
                    border: { display: false }
                }
            },
            plugins: {
                legend: { display: false },
                tooltip: {
                    backgroundColor: 'rgba(25, 135, 84, 0.95)',
                    padding: 15,
                    titleFont: { size: 13, family: "'Inter', 'Segoe UI', sans-serif", weight: 'normal' },
                    titleColor: 'rgba(255, 255, 255, 0.8)',
                    bodyFont: { size: 15, family: "'Inter', 'Segoe UI', sans-serif", weight: 'bold' },
                    displayColors: false, 
                    cornerRadius: 8,
                    callbacks: {
                        label: function(context) {
                            return 'RM ' + context.parsed.y.toLocaleString(undefined, {minimumFractionDigits: 2, maximumFractionDigits: 2});
                        }
                    }
                }
            },
            interaction: { intersect: false, mode: 'index' }
        }
    });
});
</script>

<?php include 'footer.php'; ?>