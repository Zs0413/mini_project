<?php
// Start session and connect database
session_start();
include 'db.php';

// Validate user session
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

$error_msg = "";
$user_id = $_SESSION['user_id'];
$booking_id = isset($_GET['id']) ? (int)$_GET['id'] : (isset($_POST['booking_id']) ? (int)$_POST['booking_id'] : 0);

// Verify booking belongs to user and is either Pending or Rejected
$stmt = $conn->prepare("SELECT * FROM bookings WHERE id = ? AND user_id = ? AND status IN ('Pending', 'Rejected')");
$stmt->bind_param("ii", $booking_id, $user_id);
$stmt->execute();
$booking = $stmt->get_result()->fetch_assoc();
$stmt->close();

if (!$booking) {
    header("Location: history.php?msg=Invalid booking or already paid.");
    exit();
}

// Process file upload
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['upload'])) {
    if (isset($_FILES['receipt']) && $_FILES['receipt']['error'] == 0) {
        $file = $_FILES['receipt'];
        
        // Validate file mime type
        $allowed_types = ['image/jpeg', 'image/png', 'application/pdf'];
        if (!in_array($file['type'], $allowed_types)) {
            $error_msg = "Error: Only JPG, PNG, or PDF files are allowed.";
        } 
        // Validate file size limit (2MB)
        elseif ($file['size'] > 2097152) {
            $error_msg = "Error: File size must be less than 2MB.";
        } 
        else {
            $upload_dir = 'uploads/receipts/' . date('Y/m') . '/';
            if (!is_dir($upload_dir)) {
                mkdir($upload_dir, 0777, true);
            }
            
            $file_ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
            $ref_id_str = "SM-" . str_pad($booking_id, 4, '0', STR_PAD_LEFT);
            $random_hash = substr(md5(uniqid(rand(), true)), 0, 6);
            
            $new_filename = $ref_id_str . "_" . $random_hash . "." . $file_ext;
            $target_path = $upload_dir . $new_filename;
            
            // Save file and update database record
            if (move_uploaded_file($file['tmp_name'], $target_path)) {
                
                // Determine new status based on current status
                $new_status = ($booking['status'] == 'Rejected') ? 'Re-upload' : 'Paid';
                
                $update_stmt = $conn->prepare("UPDATE bookings SET status = ?, receipt_path = ? WHERE id = ?");
                $update_stmt->bind_param("ssi", $new_status, $target_path, $booking_id);
                
                if ($update_stmt->execute()) {
                    header("Location: history.php?msg=Receipt submitted successfully. Waiting for admin verification.");
                    exit();
                } else {
                    $error_msg = "Database error during update.";
                }
                $update_stmt->close();
            } else {
                $error_msg = "Error saving file to server.";
            }
        }
    } else {
        $error_msg = "No file uploaded or upload error occurred.";
    }
}
$conn->close();

// Include navigation components
include 'header.php';
include 'navbar.php';
?>

<!-- Main Content -->
<div class="content-wrapper">
    <div class="container mt-5 mb-5" style="max-width: 600px;">
        <div class="card shadow border-0 rounded-4">
            <div class="card-header bg-success text-white text-center py-3 rounded-top-4">
                <h4 class="mb-0 fw-bold"><i class="fa-solid fa-file-invoice-dollar me-2"></i>Upload Payment Receipt</h4>
            </div>
            
            <div class="card-body p-4 p-md-5">
                <?php if($error_msg): ?>
                    <div class="alert alert-danger shadow-sm"><?= $error_msg ?></div>
                <?php endif; ?>
                
                <!-- Order Summary Box -->
                <div class="bg-light p-4 rounded-3 mb-4 text-center border">
                    <p class="text-muted small fw-bold text-uppercase mb-1">Booking Reference</p>
                    <h5 class="fw-bold text-dark mb-3">#SM-<?= str_pad($booking['id'], 4, '0', STR_PAD_LEFT) ?></h5>
                    
                    <div class="row">
                        <div class="col-6 border-end">
                            <span class="text-muted small d-block">Travel Date</span>
                            <strong class="text-dark"><?= date("d M Y", strtotime($booking['booking_date'])) ?></strong>
                        </div>
                        <div class="col-6">
                            <span class="text-muted small d-block">Amount Due</span>
                            <strong class="text-success fs-5">RM <?= number_format($booking['total_price'], 2) ?></strong>
                        </div>
                    </div>
                </div>
                
                <!-- Upload Form -->
                <form method="POST" enctype="multipart/form-data">
                    <input type="hidden" name="booking_id" value="<?= $booking['id'] ?>">
                    
                    <div class="mb-4">
                        <label class="form-label fw-bold text-dark">Select Receipt File <span class="text-danger">*</span></label>
                        <input type="file" name="receipt" class="form-control form-control-lg border-success" accept=".jpg,.png,.pdf" required>
                        <div class="form-text mt-2"><i class="fa-solid fa-circle-info me-1"></i>Accepted formats: JPG, PNG, PDF (Max 2MB).</div>
                    </div>
                    
                    <div class="d-grid gap-2">
                        <button type="submit" name="upload" class="btn btn-success btn-lg fw-bold shadow-sm">
                            Submit Payment Proof <i class="fa-solid fa-paper-plane ms-1"></i>
                        </button>
                        <a href="history.php" class="btn btn-light border fw-bold text-muted mt-2">Cancel & Return</a>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<?php include 'footer.php'; ?>