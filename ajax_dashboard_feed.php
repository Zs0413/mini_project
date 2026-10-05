<?php
// Start session and connect database
session_start();
include 'db.php';

// Validate administrative access
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    exit('Unauthorized access');
}

// Fetch latest 50 orders using priority-based status sorting
$query = "
    SELECT b.*, u.fullname 
    FROM bookings b 
    JOIN users u ON b.user_id = u.id 
    ORDER BY 
        CASE b.status 
            WHEN 'Paid' THEN 1 
            WHEN 'Re-upload' THEN 2 
            WHEN 'Pending' THEN 3 
            WHEN 'Rejected' THEN 4 
            WHEN 'Confirm' THEN 5 
            ELSE 6 
        END ASC,
        b.created_at DESC 
    LIMIT 50
";
$result = $conn->query($query);
$bookings = $result->fetch_all(MYSQLI_ASSOC);

// Generate HTML table structure without action buttons
foreach ($bookings as $row) {
    echo "<tr>";
    echo "<td class='py-3'>#SM-" . str_pad($row['id'], 4, '0', STR_PAD_LEFT) . "</td>";
    echo "<td class='py-3'>" . htmlspecialchars($row['fullname']) . "</td>";
    echo "<td class='py-3'>" . htmlspecialchars($row['booking_date']) . "</td>";
    echo "<td class='py-3 fw-bold text-success'>RM " . number_format($row['total_price'], 2) . "</td>";
    
    // Output status badges
    echo "<td class='py-3'>";
    if ($row['status'] == 'Pending') echo "<span class='badge bg-warning text-dark'>Pending</span>";
    if ($row['status'] == 'Paid') echo "<span class='badge bg-info text-dark'>Verifying</span>";
    if ($row['status'] == 'Re-upload') echo "<span class='badge bg-primary text-white'>Re-uploaded</span>";
    if ($row['status'] == 'Confirm') echo "<span class='badge bg-success'>Success</span>";
    if ($row['status'] == 'Rejected') echo "<span class='badge bg-danger'>Rejected</span>";
    echo "</td>";
    
    echo "</tr>";
}

$conn->close();
?>