<?php
// Start session and include database
session_start();
include 'db.php';

// Check if user is logged in and success booking ID exists
if (!isset($_SESSION['user_id']) || !isset($_SESSION['success_booking_id'])) {
    header("Location: booking.php");
    exit();
}

$booking_id = $_SESSION['success_booking_id'];
$user_id = $_SESSION['user_id'];

// Fetch booking details from database to display receipt confirmation
$stmt = $conn->prepare("SELECT b.*, u.fullname FROM bookings b JOIN users u ON b.user_id = u.id WHERE b.id = ? AND b.user_id = ?");
$stmt->bind_param("ii", $booking_id, $user_id);
$stmt->execute();
$booking_info = $stmt->get_result()->fetch_assoc();
$stmt->close();

$stmt_details = $conn->prepare("SELECT * FROM booking_details WHERE booking_id = ?");
$stmt_details->bind_param("i", $booking_id);
$stmt_details->execute();
$details_result = $stmt_details->get_result()->fetch_all(MYSQLI_ASSOC);
$stmt_details->close();

include 'header.php';
include 'navbar.php';
?>

<div class="content-wrapper">
    <div class="container mt-5 mb-5" style="max-width: 650px;">
        <div class="card shadow border-0 rounded-4">
            <div class="card-header bg-success text-white text-center py-3 rounded-top-4">
                <h3 class="mb-0 fw-bold">Confirm Booking Details</h3>
            </div>
            
            <div class="card-body p-4">
                <div class="mb-3">
                    <p class="mb-1"><strong>Customer:</strong> <?= htmlspecialchars($booking_info['fullname']) ?></p>
                    <p class="mb-3"><strong>Travel Date:</strong> <?= htmlspecialchars($booking_info['booking_date']) ?></p>
                </div>
                <table class="table table-bordered text-center align-middle">
                    <thead class="table-success">
                        <tr>
                            <th class="text-start">Category</th>
                            <th>Quantity</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach($details_result as $item): ?>
                        <tr>
                            <td class="text-start"><?= ucfirst(htmlspecialchars($item['ticket_type'])) ?></td>
                            <td><?= $item['quantity'] ?></td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
                
                <div class="card bg-light border-0 text-center py-3 my-4 shadow-sm">
                    <span class="text-muted small">Total Paid</span>
                    <h2 class="text-success fw-bold mb-0">RM <?= number_format($booking_info['total_price'], 2) ?></h2>
                </div>
                
                <div class="d-grid gap-2">
                    <a href="print_ticket.php?id=<?= $booking_id ?>" target="_blank" class="btn btn-success py-3 fw-bold fs-5 shadow-sm">
                        Print Receipt
                    </a>
                    <a href="history.php" class="btn btn-outline-success py-2 fw-bold"> Go To History </a>
                </div>
            </div>
        </div>
    </div>
</div>

<?php 
// Clean up the success session ID so refreshing or reopening behaves correctly
unset($_SESSION['success_booking_id']);
include 'footer.php'; 
?>