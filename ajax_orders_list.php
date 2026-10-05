<?php
// Start session and connect database
session_start();
include 'db.php';

// Validate administrative access
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    exit('Unauthorized access');
}

// Retrieve smart filter parameters
$search_keyword = isset($_GET['keyword']) ? trim($_GET['keyword']) : '';
$search_date = isset($_GET['date']) ? trim($_GET['date']) : '';
$search_status = isset($_GET['status']) ? trim($_GET['status']) : '';

// Construct dynamic SQL statement WITH ticket details via subquery
$query = "
    SELECT b.*, u.fullname,
       (SELECT GROUP_CONCAT(CONCAT(ticket_type, ':', quantity) SEPARATOR ',') 
        FROM booking_details bd 
        WHERE bd.booking_id = b.id AND bd.quantity > 0) AS tickets_info
    FROM bookings b 
    JOIN users u ON b.user_id = u.id 
    WHERE 1=1
";
$params = [];
$types = "";

// Smart Keyword Search: Match by Customer Name OR Formatted Ref ID
if ($search_keyword !== '') {
    $query .= " AND (u.fullname LIKE ? OR CONCAT('#SM-', LPAD(b.id, 4, '0')) LIKE ?)";
    $keyword_param = "%" . $search_keyword . "%";
    $params[] = $keyword_param;
    $params[] = $keyword_param;
    $types .= "ss";
}

if ($search_date !== '') {
    $query .= " AND b.booking_date = ?";
    $params[] = $search_date;
    $types .= "s";
}

if ($search_status !== '') {
    $query .= " AND b.status = ?";
    $params[] = $search_status;
    $types .= "s";
}

// Apply action-driven priority sorting logic
$query .= " 
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
";

// Execute query using prepared statements
$stmt = $conn->prepare($query);
if (!empty($params)) {
    $stmt->bind_param($types, ...$params);
}
$stmt->execute();
$bookings = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
$stmt->close();

// Output HTML rows or empty state
if (count($bookings) > 0) {
    foreach ($bookings as $row) {
        echo "<tr>";
        echo "<td class='fw-bold text-muted'>#SM-" . str_pad($row['id'], 4, '0', STR_PAD_LEFT) . "</td>";
        echo "<td>" . htmlspecialchars($row['fullname']) . "</td>";
        
        echo "<td>"; 
        if (!empty($row['tickets_info'])) {
            echo "<div class='d-flex flex-column gap-1 align-items-center justify-content-center'>";
            $ticket_array = explode(',', $row['tickets_info']);
            
            foreach ($ticket_array as $t) {
                $parts = explode(':', $t);
                if (count($parts) == 2) {
                    $type = strtolower($parts[0]);
                    $qty = $parts[1];
                    $type_label = ucfirst($type);
                    
                    $bg_class = "bg-secondary text-white";
                    if ($type == 'adult') {
                        $bg_class = "bg-primary bg-opacity-10 text-primary border border-primary";
                    } elseif ($type == 'child') {
                        $bg_class = "bg-info bg-opacity-10 text-info border border-info";
                    } elseif ($type == 'senior') {
                        $bg_class = "bg-warning bg-opacity-10 text-warning border border-warning";
                    }
                    
                    echo "<span class='badge {$bg_class} rounded-pill px-3 py-1' style='font-size: 0.75rem; letter-spacing: 0.5px;'>
                            {$type_label} <span class='fw-bold ms-1' style='font-size: 0.85rem;'>x{$qty}</span>
                          </span>";
                }
            }
            echo "</div>";
        } else {
            echo "<span class='text-muted small fst-italic'>N/A</span>";
        }
        echo "</td>";
        
        echo "<td>" . htmlspecialchars($row['booking_date']) . "</td>";
        echo "<td class='fw-bold text-success'>" . number_format($row['total_price'], 2) . "</td>";
        
        echo "<td>";
        if ($row['status'] == 'Pending') echo "<span class='badge bg-warning text-dark rounded-pill px-3'>Pending Upload</span>";
        if ($row['status'] == 'Paid') echo "<span class='badge bg-info text-dark rounded-pill px-3'>Action Needed</span>";
        if ($row['status'] == 'Re-upload') echo "<span class='badge bg-primary text-white rounded-pill px-3'>Re-uploaded</span>";
        if ($row['status'] == 'Confirm') echo "<span class='badge bg-success rounded-pill px-3'>Success</span>";
        if ($row['status'] == 'Rejected') echo "<span class='badge bg-danger rounded-pill px-3'>Rejected</span>";
        echo "</td>";
        
        echo "<td>";
        if (!empty($row['receipt_path'])) {
            echo "<a href='" . htmlspecialchars($row['receipt_path']) . "' target='_blank' class='btn btn-sm btn-outline-primary rounded-pill mb-1 me-1'><i class='fa-solid fa-file-invoice me-1'></i>Receipt</a>";
        }
        
        // Provide Approve and Reject options if status requires verification
        if ($row['status'] == 'Paid' || $row['status'] == 'Re-upload') {
            echo "<a href='admin_update_status.php?id={$row['id']}&action=confirm&source=orders' class='btn btn-sm btn-success rounded-pill fw-bold me-1 mb-1' onclick=\"return confirm('Confirm payment for this booking?');\"><i class='fa-solid fa-check me-1'></i>Approve</a>";
            
            echo "<a href='admin_update_status.php?id={$row['id']}&action=reject&source=orders' class='btn btn-sm btn-danger rounded-pill fw-bold mb-1' onclick=\"return confirm('Reject this receipt? The customer will need to upload again.');\"><i class='fa-solid fa-xmark me-1'></i>Reject</a>";
        }
        
        // Provide indicator if waiting for customer
        if ($row['status'] == 'Pending' || $row['status'] == 'Rejected') {
            echo "<span class='text-muted small fst-italic'>Waiting for customer...</span>";
        }
        
        echo "</td>";
        echo "</tr>";
    }
} else {
    echo "<tr><td colspan='7' class='py-5 text-muted'>No orders match your smart filter criteria.</td></tr>";
}
$conn->close();
?>