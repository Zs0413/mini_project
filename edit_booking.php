<?php
// Start or resume the session
session_start();

// Include the database connection file
include 'header.php';
include 'db.php';

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

$error_msg = "";
$booking_id = $_GET['id'] ?? 0;
$user_id = $_SESSION['user_id'];

// 1. Fetch main booking (Changed to MySQLi syntax using '?')
$stmt = $conn->prepare("SELECT * FROM bookings WHERE id = ? AND user_id = ?");
$stmt->bind_param("ii", $booking_id, $user_id);
$stmt->execute();
$result_main = $stmt->get_result();
$booking = $result_main->fetch_assoc();
$stmt->close();

// Terminate if booking does not exist or doesn't belong to the user
if (!$booking) {
    die("Booking not found!");
}

// Validate minimum 1 day before modification
if ((strtotime($booking['booking_date']) - strtotime(date("Y-m-d"))) / 86400 < 1) {
    die("Cannot modify a booking less than 1 day before the booking date.");
}

// 2. Fetch current ticket quantities (Changed to MySQLi syntax)
$stmt_qty = $conn->prepare("SELECT ticket_type, quantity FROM booking_details WHERE booking_id = ?");
$stmt_qty->bind_param("i", $booking_id);
$stmt_qty->execute();
$result_qty = $stmt_qty->get_result();
$current_qty = ['adult'=>0, 'child'=>0, 'senior'=>0];

while($row = $result_qty->fetch_assoc()) {
    $current_qty[$row['ticket_type']] = $row['quantity'];
}
$stmt_qty->close();

$ticket_prices = ["adult" => 125, "child" => 95, "senior" => 95];

// Handle form submission
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $new_date = $_POST['booking_date'];
    $adult_qty = (int)$_POST['adult_qty'];
    $child_qty = (int)$_POST['child_qty'];
    $senior_qty = (int)$_POST['senior_qty'];
    
    // Calculate new total price
    $total_price = ($adult_qty * $ticket_prices['adult']) + ($child_qty * $ticket_prices['child']) + ($senior_qty * $ticket_prices['senior']);
    
    try {
        // Begin transaction for MySQLi
        $conn->begin_transaction();
        
        // 3. Update main table (Changed to MySQLi syntax)
        $update = $conn->prepare("UPDATE bookings SET booking_date = ?, total_price = ? WHERE id = ?");
        $update->bind_param("sdi", $new_date, $total_price, $booking_id);
        $update->execute();
        $update->close();
        
        // 4. Delete old details (Changed to MySQLi syntax)
        $del = $conn->prepare("DELETE FROM booking_details WHERE booking_id = ?");
        $del->bind_param("i", $booking_id);
        $del->execute();
        $del->close();
        
        // 5. Insert new details (Changed to MySQLi syntax)
        $stmt_details = $conn->prepare("INSERT INTO booking_details (booking_id, ticket_type, quantity, price_per_unit, subtotal) VALUES (?, ?, ?, ?, ?)");
        $tickets = ['adult' => $adult_qty, 'child' => $child_qty, 'senior' => $senior_qty];
        
        foreach ($tickets as $type => $qty) {
            if ($qty > 0) {
                $price = $ticket_prices[$type];
                $subtotal = $price * $qty;
                // Bind: i(int), s(string), i(int), d(double), d(double)
                $stmt_details->bind_param("isidd", $booking_id, $type, $qty, $price, $subtotal);
                $stmt_details->execute();
            }
        }
        $stmt_details->close();
        
        // Commit transaction
        $conn->commit();
        header("Location: history.php?msg=Booking Updated Successfully!");
        exit();
        
    } catch(Exception $e) {
        $conn->rollback();
        $error_msg = "Error updating database.";
    }
}
$conn->close();
?>

<!-- Top Navigation Bar to match other pages -->
<nav class="navbar navbar-expand-lg navbar-dark bg-success shadow">
    <div class="container-fluid px-5">
        <a class="navbar-brand fw-bold" href="booking.php">SPLASH MANIA</a>
    </div>
</nav>

<div class="container mt-5">
    <?php if($error_msg) echo "<div class='alert alert-danger'>$error_msg</div>"; ?>
    
    <div class="card shadow col-md-8 mx-auto border-0">
        <div class="card-header bg-success text-dark text-center py-3">
            <h4 class="mb-0 fw-bold">Edit Ticket Booking</h4>
        </div>
        <div class="card-body p-4">
            <form method="POST">
                <div class="mb-4">
                    <label class="fw-bold mb-2">Booking Date</label>
                    <input type="date" name="booking_date" class="form-control form-control-lg" value="<?= htmlspecialchars($booking['booking_date']) ?>" min="<?= date('Y-m-d', strtotime('+1 day')) ?>" required>
                </div>
                
                <table class="table table-bordered text-center align-middle mb-4">
                    <thead class="table-light">
                        <tr>
                            <th>Category</th>
                            <th>Quantity</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr>
                            <td class="text-start fw-bold">Adult (RM 125.00)</td>
                            <td style="width: 150px;">
                                <input type="number" name="adult_qty" class="form-control text-center" value="<?= $current_qty['adult'] ?>" min="0">
                            </td>
                        </tr>
                        <tr>
                            <td class="text-start fw-bold">Child (RM 95.00)</td>
                            <td>
                                <input type="number" name="child_qty" class="form-control text-center" value="<?= $current_qty['child'] ?>" min="0">
                            </td>
                        </tr>
                        <tr>
                            <td class="text-start fw-bold">Senior Citizen (RM 95.00)</td>
                            <td>
                                <input type="number" name="senior_qty" class="form-control text-center" value="<?= $current_qty['senior'] ?>" min="0">
                            </td>
                        </tr>
                    </tbody>
                </table>
                
                <div class="d-flex justify-content-between">
                    <a href="history.php" class="btn btn-secondary btn-lg px-4">Cancel</a>
                    <button type="submit" class="btn bg-success btn-lg px-5 fw-bold">Update Booking</button>
                </div>
            </form>
        </div>
    </div>
</div>
<?php include 'footer.php'; ?>