<?php
// Start session and connect database
session_start();
include 'db.php';

// Function 1: Format Booking ID (e.g., 1 -> #SM-0001)
function formatRefID($id) {
    return "#SM-" . str_pad($id, 4, '0', STR_PAD_LEFT);
}

// Function 2: Handle Booking Status Update
function updateBookingStatus($conn, $booking_id,$new_status) {
    $stmt =$conn->prepare("UPDATE bookings SET status = ? WHERE id = ?");
    $stmt->bind_param("si", $new_status, $booking_id);$success = $stmt->execute();$stmt->close();
    
    return $success;
}

// ==========================================
// Validate administrative access
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    die("Access Denied");
}

// Process record updates
if (isset($_GET['id']) && isset($_GET['action'])) {
    $booking_id = (int)$_GET['id'];          
    $formatted_id = formatRefID($booking_id);
    
    // Handle status confirmation
    if ($_GET['action'] == 'confirm') {
        
        if(updateBookingStatus($conn,$booking_id, 'Confirm')) {
            // Set success notification message
            $_SESSION['success_msg'] = "Success: Booking {$formatted_id} has been approved.";
        }
    }
    
    // Handle status rejection and completely remove the invalid receipt
    if ($_GET['action'] == 'reject') {
        
        // Find the old receipt file path
        $stmt =$conn->prepare("SELECT receipt_path FROM bookings WHERE id = ?");
        $stmt->bind_param("i", $booking_id);$stmt->execute();
        $result =$stmt->get_result();
        
        if ($row =$result->fetch_assoc()) {
            // Delete the file from the server storage if it exists
            if (!empty($row['receipt_path']) && file_exists($row['receipt_path'])) {
                unlink($row['receipt_path']);
            }
        }
        $stmt->close();
        
        // Update status to Rejected and clear the receipt path in database
        $update_stmt =$conn->prepare("UPDATE bookings SET status = 'Rejected', receipt_path = NULL WHERE id = ?");
        $update_stmt->bind_param("i", $booking_id);
        
        if($update_stmt->execute()) {
            // Set error/warning notification message
            $_SESSION['error_msg'] = "Rejected: Booking {$formatted_id} payment proof has been rejected.";
        }
        $update_stmt->close();
    }
}

// Determine redirect destination based on origin
$redirect = isset($_GET['source']) &&$_GET['source'] == 'orders' ? 'admin_orders.php' : 'admin_dashboard.php';
header("Location: " . $redirect);
exit();
?>