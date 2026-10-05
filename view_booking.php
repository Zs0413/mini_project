<?php
// Start session and include database
session_start();
include 'db.php';

// Check if user is logged in and temp booking exists
if (!isset($_SESSION['user_id']) || !isset($_SESSION['temp_booking'])) {
    header("Location: booking.php");
    exit();
}

$booking_data = $_SESSION['temp_booking'];
$ticket_prices = ["adult" => 125.00, "child" => 95.00, "senior" => 95.00];

// Handle confirmation submission (When user clicks "Confirm & Pay Now")
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['confirm_payment'])) {
    $user_id = $_SESSION['user_id'];
    $booking_date = $booking_data['booking_date'];
    $total_price = $booking_data['total_price'];
    
    $conn->begin_transaction();
    try {
        // 1. Insert into main bookings table
        $stmt = $conn->prepare("INSERT INTO bookings (user_id, booking_date, total_price) VALUES (?, ?, ?)");
        $stmt->bind_param("isd", $user_id, $booking_date, $total_price);
        $stmt->execute();
        $booking_id = $conn->insert_id;
        $stmt->close();
        
        // 2. Insert into booking_details table
        $stmt_details = $conn->prepare("INSERT INTO booking_details (booking_id, ticket_type, quantity, price_per_unit, subtotal) VALUES (?, ?, ?, ?, ?)");
        
        $selected_tickets = [
            'adult' => $booking_data['adult_qty'],
            'child' => $booking_data['child_qty'],
            'senior' => $booking_data['senior_qty']
        ];
        
        foreach ($selected_tickets as $type => $qty) {
            if ($qty > 0) {
                $price = $ticket_prices[$type];
                $subtotal = $price * $qty;
                $stmt_details->bind_param("isidd", $booking_id, $type, $qty, $price, $subtotal);
                $stmt_details->execute();
            }
        }
        $stmt_details->close();
        $conn->commit();
        
        // Save last inserted booking id to session for the success page
        $_SESSION['success_booking_id'] = $booking_id;
        
        // Clear temp booking data
        unset($_SESSION['temp_booking']);
        
        // Redirect to success confirmation page
        header("Location: success_booking.php");
        exit();
    } catch (Exception $e) {
        $conn->rollback();
        $error_msg = "Database Error: " . $e->getMessage();
    }
}

// Include layout files
include 'header.php';
include 'navbar.php';
?>

<div class="content-wrapper">
    <div class="container mt-5 mb-5" style="max-width: 650px;">
        <?php if(isset($error_msg)) echo "<div class='alert alert-danger'>$error_msg</div>"; ?>
        
        <div class="card shadow border-0 rounded-4">
            <div class="card-header bg-success text-white text-center py-3 rounded-top-4">
                <h3 class="mb-0 fw-bold">View Booking Details</h3>
            </div>
            
            <div class="card-body p-4">
                <!-- Customer Name & Travel Date info row -->
                <div class="row mb-4">
                    <div class="col-6">
                        <span class="text-muted small d-block">Customer Name</span>
                        <strong class="fs-5"><?= htmlspecialchars($_SESSION['fullname']) ?></strong>
                    </div>
                    <div class="col-6">
                        <span class="text-muted small d-block">Travel Date</span>
                        <strong class="fs-5"><?= htmlspecialchars($booking_data['booking_date']) ?></strong>
                    </div>
                </div>
                <hr class="text-muted">
                
                <h5 class="fw-bold mb-3">Ticket Breakdown</h5>
                <table class="table table-bordered text-center align-middle">
                    <thead class="table-light">
                        <tr>
                            <th class="text-start">Ticket Category</th>
                            <th>Qty</th>
                            <th class="text-end">Subtotal</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if($booking_data['child_qty'] > 0): ?>
                        <tr>
                            <td class="text-start">Children (RM <?= number_format($ticket_prices['child'], 2) ?>)</td>
                            <td><?= $booking_data['child_qty'] ?></td>
                            <td class="text-end">RM <?= number_format($ticket_prices['child'] * $booking_data['child_qty'], 2) ?></td>
                        </tr>
                        <?php endif; ?>
                        
                        <?php if($booking_data['adult_qty'] > 0): ?>
                        <tr>
                            <td class="text-start">Adult (RM <?= number_format($ticket_prices['adult'], 2) ?>)</td>
                            <td><?= $booking_data['adult_qty'] ?></td>
                            <td class="text-end">RM <?= number_format($ticket_prices['adult'] * $booking_data['adult_qty'], 2) ?></td>
                        </tr>
                        <?php endif; ?>
                        
                        <?php if($booking_data['senior_qty'] > 0): ?>
                        <tr>
                            <td class="text-start">Senior Citizen (RM <?= number_format($ticket_prices['senior'], 2) ?>)</td>
                            <td><?= $booking_data['senior_qty'] ?></td>
                            <td class="text-end">RM <?= number_format($ticket_prices['senior'] * $booking_data['senior_qty'], 2) ?></td>
                        </tr>
                        <?php endif; ?>
                        
                        <tr class="table-light fw-bold">
                            <td colspan="2" class="text-end">Grand Total</td>
                            <td class="text-end text-success fs-5">RM <?= number_format($booking_data['total_price'], 2) ?></td>
                        </tr>
                    </tbody>
                </table>
                
                <!-- Action Forms -->
                <form method="POST" class="mt-4">
                    <button type="submit" name="confirm_payment" class="btn btn-success w-100 py-3 fw-bold fs-5 mb-2 shadow-sm">
                        Confirm & Pay Now
                    </button>
                    <a href="booking.php" class="btn btn-outline-success w-100 py-3 fw-bold fs-5 mb-2 fw-bold">
                        Go Back
                    </a>
                </form>
            </div>
        </div>
    </div>
</div>

<?php include 'footer.php'; ?>