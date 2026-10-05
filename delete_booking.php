<?php
// Start or resume the session
session_start();

// Include the database connection file
include 'db.php';

// Check if user is logged in; if not, redirect to the login page
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

// Retrieve the booking ID from the URL, default to 0 if not set
$booking_id = $_GET['id'] ?? 0;

// Retrieve the logged-in user's ID from the session
$user_id = $_SESSION['user_id'];

// ==========================================
// NEW CONSTRAINT: Validate Booking Status Before Deletion
// ==========================================
$check_stmt = $conn->prepare("SELECT status FROM bookings WHERE id = ? AND user_id = ?");
$check_stmt->bind_param("ii", $booking_id, $user_id);
$check_stmt->execute();
$result = $check_stmt->get_result();
$booking = $result->fetch_assoc();
$check_stmt->close();

// If booking doesn't exist or doesn't belong to the user
if (!$booking) {
    die("Error: Booking not found or access denied.");
}

// Prevent deletion if the booking is currently being processed or already confirmed
$protected_statuses = ['Paid', 'Re-upload', 'Confirm'];
if (in_array($booking['status'], $protected_statuses)) {
    die("Action Denied: You cannot delete a booking that is currently being processed by admin or already confirmed.");
}

// ==========================================
// Prepare the DELETE statement using MySQLi placeholders (?)
$stmt = $conn->prepare("DELETE FROM bookings WHERE id = ? AND user_id = ?");
$stmt->bind_param("ii", $booking_id, $user_id);

// Execute the statement and check if deletion was successful
if ($stmt->execute()) {
    header("Location: history.php?msg=Deleted Successfully!");
} else {
    echo "Error deleting booking: " . $stmt->error;
}
$stmt->close();
$conn->close();
?>