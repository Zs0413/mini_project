<?php
// Start session and connect database
session_start();
include 'db.php';

// Include navigation components
include 'header.php';
include 'navbar.php';

// Validate user session
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}
$user_id = $_SESSION['user_id'];

// Retrieve booking history with ticket quantities and status
$query = "
    SELECT b.id, b.booking_date, b.total_price, b.status, b.receipt_path,
        IFNULL(SUM(CASE WHEN bd.ticket_type = 'adult' THEN bd.quantity ELSE 0 END), 0) AS adult_qty,
        IFNULL(SUM(CASE WHEN bd.ticket_type = 'child' THEN bd.quantity ELSE 0 END), 0) AS child_qty,
        IFNULL(SUM(CASE WHEN bd.ticket_type = 'senior' THEN bd.quantity ELSE 0 END), 0) AS senior_qty
    FROM bookings b
    LEFT JOIN booking_details bd ON b.id = bd.booking_id
    WHERE b.user_id = ? 
    GROUP BY b.id
    ORDER BY b.booking_date DESC
";

$stmt = $conn->prepare($query);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();
$bookings = $result->fetch_all(MYSQLI_ASSOC);
$stmt->close();
?>

<!-- Main Content -->
<div class="content-wrapper">
    <div class="container mt-5 mb-5">
        
        <!-- Header Section -->
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h2 class="text-success fw-bold mb-0">My Booking History</h2>
            <div>
                <a href="booking.php" class="btn btn-success shadow-sm">New Booking</a>
                <a href="logout.php" class="btn btn-secondary shadow-sm">Logout</a>
            </div>
        </div>
        
        <!-- System Messages -->
        <?php if(isset($_GET['msg'])): ?>
            <div class="alert alert-success shadow-sm">
                <?= htmlspecialchars($_GET['msg']) ?>
            </div>
        <?php endif; ?>
        
        <!-- Booking Records Table -->
        <div class="table-responsive bg-white shadow-sm rounded p-3">
            <table class="table table-bordered table-hover align-middle text-center mb-0">
                <thead class="table-success">
                    <tr>
                        <th>No.</th>
                        <th>Booking Date</th>
                        <th>Total Price (RM)</th>
                        <th>Status</th>
                        <th>Receipt</th>
                        <th>Action</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (count($bookings) > 0): ?>
                        <?php $counter = 1; ?>
                        <?php foreach ($bookings as $row): ?>
                            <tr>
                                <td class="fw-bold text-muted"><?= $counter++; ?></td>
                                <td><?= htmlspecialchars($row['booking_date']) ?></td>
                                <td class="fw-bold text-success">RM <?= number_format($row['total_price'], 2) ?></td>
                                
                                <!-- Booking Status Display -->
                                <td>
                                    <?php if($row['status'] == 'Pending'): ?>
                                        <span class="badge bg-warning text-dark">Pending Upload</span>
                                    <?php elseif($row['status'] == 'Paid'): ?>
                                        <span class="badge bg-info text-dark">Verifying</span>
                                    <?php elseif($row['status'] == 'Re-upload'): ?>
                                        <span class="badge bg-primary text-white">Re-uploaded (Verifying)</span>
                                    <?php elseif($row['status'] == 'Confirm'): ?>
                                        <span class="badge bg-success">Success</span>
                                    <?php elseif($row['status'] == 'Rejected'): ?>
                                        <span class="badge bg-danger">Rejected</span>
                                    <?php endif; ?>
                                </td>
                                
                                <!-- Receipt Management & Upload -->
                                <td>
                                    <?php if($row['status'] == 'Pending' || $row['status'] == 'Rejected'): ?>
                                        <a href="upload_receipt.php?id=<?= $row['id'] ?>" class="btn btn-sm btn-success shadow-sm fw-bold">
                                            <i class="fa-solid fa-cloud-arrow-up me-1"></i>Upload
                                        </a>
                                    <?php endif; ?>
                                    
                                    <?php if(!empty($row['receipt_path'])): ?>
                                        <a href="<?= htmlspecialchars($row['receipt_path']) ?>" target="_blank" class="btn btn-sm btn-outline-info mt-1">
                                            <i class="fa-solid fa-eye me-1"></i>View
                                        </a>
                                    <?php endif; ?>
                                </td>

                                <!-- Action Commands -->
                                <td>
                                    <?php if($row['status'] == 'Pending' || $row['status'] == 'Rejected'): ?>
                                        <a href="edit_booking.php?id=<?= $row['id'] ?>" class="btn btn-warning btn-sm fw-bold">Edit</a>
                                        <a href="delete_booking.php?id=<?= $row['id'] ?>" class="btn btn-danger btn-sm fw-bold" onclick="return confirm('Are you sure you want to delete this booking?');">Delete</a>
                                    <?php endif; ?>
                                    
                                    <?php if($row['status'] == 'Confirm'): ?>
                                        <a href="print_ticket.php?id=<?= $row['id'] ?>" class="btn btn-success btn-sm text-white fw-bold" target="_blank">Print</a>
                                    <?php endif; ?>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="6" class="text-center text-muted py-4">No bookings found. Time to plan an adventure!</td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
        
    </div>
</div>

<?php include 'footer.php'; ?>