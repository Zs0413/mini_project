<?php
// Start or resume the session
session_start();

// Include the database connection file
include 'db.php';

// Retrieve the booking ID from the URL, default to 0 if not set
$booking_id = $_GET['id'] ?? 0;

// Retrieve the logged-in user's ID from the session
$user_id = $_SESSION['user_id'];

// 1. Fetch main ticket data
$stmt = $conn->prepare("SELECT b.*, u.fullname, u.phone FROM bookings b JOIN users u ON b.user_id = u.id WHERE b.id = ? AND b.user_id = ?");
$stmt->bind_param("ii", $booking_id, $user_id);
$stmt->execute();
$result_main = $stmt->get_result();
$ticket_main = $result_main->fetch_assoc();
$stmt->close();

// Terminate script if the ticket is not found or doesn't belong to the user
if (!$ticket_main) {
    die("Ticket not found!");
}

// 2. Fetch ticket details
$stmt_details = $conn->prepare("SELECT * FROM booking_details WHERE booking_id = ?");
$stmt_details->bind_param("i", $booking_id);
$stmt_details->execute();
$result_details = $stmt_details->get_result();
$ticket_details = $result_details->fetch_all(MYSQLI_ASSOC);
$stmt_details->close();

$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <title>Print Ticket - Splash Mania</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        .ticket-box {
            border: 2px dashed #198754;
            padding: 30px 10px;
            margin-top: 50px;
            max-width: 600px;
        }
        .ticket-header {
            padding: 0 20px;
        }
        @media print {
            .no-print { display: none; }
        }
    </style>
</head>
<body>
<div class="container d-flex justify-content-center mb-5">
    <div class="ticket-box bg-light shadow w-100">
        <h2 class="text-center text-success fw-bold">SPLASH MANIA</h2>
        <h6 class="text-center text-muted">e-Ticket</h6>
        <hr>
        <p class="mb-1"><strong>Name:</strong> <?= htmlspecialchars($ticket_main['fullname']) ?></p>
        <p class="mb-3"><strong>Date:</strong> <?= htmlspecialchars($ticket_main['booking_date']) ?></p>
        
        <table class="table table-sm table-bordered text-center align-middle">
            <thead class="table-success">
                <tr>
                    <th>Type</th>
                    <th>Qty</th>
                    <th>Subtotal</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach($ticket_details as $item): ?>
                <tr>
                    <td><?= ucfirst(htmlspecialchars($item['ticket_type'])) ?></td>
                    <td><?= htmlspecialchars($item['quantity']) ?></td>
                    <td>RM <?= number_format($item['subtotal'], 2) ?></td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
        
        <h4 class="text-end text-success fw-bold mt-3">Total: RM <?= number_format($ticket_main['total_price'], 2) ?></h4>
        
        <div class="text-center mt-4 no-print">
            <button onclick="window.print()" class="btn btn-success px-4 me-2">Print Now</button>
            <a href="history.php" class="btn btn-secondary px-4">Back to History</a>
        </div>
    </div>
</div>
</body>
</html>