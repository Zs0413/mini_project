<?php
// Start or resume the session
session_start();

// Include the database connection file
include 'db.php';

// Check if the user is logged in; if not, redirect to the login page
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

$error_msg = "";

// Define ticket prices per category
$ticket_prices = [
    "adult" => 125.00,
    "child" => 95.00,
    "senior" => 95.00
];

// Handle Booking Form Submission
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    
    // Sanitize and capture input values
    $booking_date = $_POST['booking_date'];
    $adult_qty = (int)$_POST['adult_qty'];
    $child_qty = (int)$_POST['child_qty'];
    $senior_qty = (int)$_POST['senior_qty'];
    
    // Validation: Check if the booking date is in the past
    $today = date("Y-m-d");
    if ($booking_date < $today) {
        $error_msg = "Cannot book for past dates!";
    } 
    // Validation: Check if at least one ticket is selected
    elseif ($adult_qty == 0 && $child_qty == 0 && $senior_qty == 0) {
        $error_msg = "Please select at least one ticket!";
    } 
    else {
        // Calculate the total price based on quantities and rates
        $total_price = ($adult_qty * $ticket_prices['adult']) +
                       ($child_qty * $ticket_prices['child']) +
                       ($senior_qty * $ticket_prices['senior']);
        
        // Temporarily store booking data in a session to pass to the review page (view_booking.php)
        $_SESSION['temp_booking'] = [
            'booking_date' => $booking_date,
            'adult_qty' => $adult_qty,
            'child_qty' => $child_qty,
            'senior_qty' => $senior_qty,
            'total_price' => $total_price
        ];
        
        // Redirect to the booking details review page
        header("Location: view_booking.php");
        exit();
    }
}

// Fetch complete user info for the Profile Card from the database safely
$user_id = $_SESSION['user_id'];
$stmt_user = $conn->prepare("SELECT * FROM users WHERE id = ?");
$stmt_user->bind_param("i", $user_id);
$stmt_user->execute();
$result_user = $stmt_user->get_result();
$current_user = $result_user->fetch_assoc();
$stmt_user->close();
$conn->close();

// CRITICAL FIX: Fallback safety check. 
// If the user is not found in the database (e.g. database was cleared), force a re-login.
if (!$current_user) {
    session_destroy();
    header("Location: login.php");
    exit();
}

// Include global header and navigation bar
include 'header.php';
include 'navbar.php';
?>

<!-- Content wrapper to push the footer down if content is short -->
<div class="content-wrapper">
    <!-- Hero Carousel Section with Dark Overlay -->
    <div id="splashCarousel" class="carousel slide hero-carousel shadow-sm" data-bs-ride="carousel" data-bs-interval="3000">
        <div class="carousel-inner">
            <div class="carousel-item active">
                <img src="images/image1.jpg" class="d-block w-100" alt="Slide 1">
                <div class="carousel-caption d-none d-md-block bg-dark bg-opacity-50 rounded p-3">
                    <h1 class="fw-bold">Experience the Thrill</h1>
                    <p>Unforgettable moments at Splash Mania Theme Park</p>
                </div>
            </div>
            <div class="carousel-item">
                <img src="images/image2.jpg" class="d-block w-100" alt="Slide 2">
                <div class="carousel-caption d-none d-md-block bg-dark bg-opacity-50 rounded p-3">
                    <h1 class="fw-bold">Family Fun Awaits</h1>
                    <p>Create lasting memories with your loved ones</p>
                </div>
            </div>
            <div class="carousel-item">
                <img src="images/image3.jpg" class="d-block w-100" alt="Slide 3">
                <div class="carousel-caption d-none d-md-block bg-dark bg-opacity-50 rounded p-3">
                    <h1 class="fw-bold">Dive Into Adventure</h1>
                    <p>Enjoy unlimited rides and amazing water attractions</p>
                </div>
            </div>
        </div>
        <button class="carousel-control-prev" type="button" data-bs-target="#splashCarousel" data-bs-slide="prev">
            <span class="carousel-control-prev-icon"></span>
        </button>
        <button class="carousel-control-next" type="button" data-bs-target="#splashCarousel" data-bs-slide="next">
            <span class="carousel-control-next-icon"></span>
        </button>
    </div>

    <!-- Main Dashboard Container -->
    <div class="container mt-4 mb-5">
        
        <!-- Display error message if validation fails -->
        <?php if($error_msg): ?>
            <div class="alert alert-danger shadow-sm"><?= $error_msg; ?></div>
        <?php endif; ?>

        <div class="row">
            <!-- Left Column: User Profile Information Card -->
            <div class="col-md-4 mb-4">
                <div class="card shadow-sm border-0 border-start border-success border-4 rounded-3">
                    <div class="card-body p-4">
                        <h5 class="card-title text-success mb-4 fw-bold">
                            <i class="fa-solid fa-id-card-clip me-2"></i>Profile Info
                        </h5>
                        <hr>
                        <!-- Added '??' Null Coalescing Operator for extra safety in output -->
                        <p class="mb-1 fw-bold text-dark">Name:</p>
                        <p class="text-muted mb-3"><?= htmlspecialchars($current_user['fullname'] ?? 'N/A') ?></p>
                        
                        <p class="mb-1 fw-bold text-dark">Email:</p>
                        <p class="text-muted mb-3"><?= htmlspecialchars($current_user['email'] ?? 'N/A') ?></p>

                        <p class="mb-1 fw-bold text-dark">Username:</p>
                        <p class="text-muted mb-3"><?= htmlspecialchars($current_user['username'] ?? 'N/A') ?></p>
                        
                        <p class="mb-1 fw-bold text-dark">Phone:</p>
                        <p class="text-muted mb-3"><?= htmlspecialchars($current_user['phone'] ?? 'N/A') ?></p>
                        
                        <p class="mb-1 fw-bold text-dark">Member Since:</p>
                        <p class="text-muted mb-0"><?= isset($current_user['regdate']) ? date("d M Y", strtotime($current_user['regdate'])) : 'N/A' ?></p>
                    </div>
                </div>
            </div>

            <!-- Right Column: Ticket Selection Form -->
            <div class="col-md-8">
                <div class="card shadow-sm border-0 rounded-3">
                    <div class="card-body p-4">
                        <h4 class="text-center mb-4 fw-bold text-success">Ticket Selection</h4>
                        
                        <form method="POST">
                            <!-- Travel Date Input -->
                            <div class="mb-4">
                                <label class="fw-bold mb-2">Travel Date:</label>
                                <!-- min attribute prevents selecting past dates -->
                                <input type="date" name="booking_date" class="form-control form-control-lg" min="<?= date('Y-m-d'); ?>" required>
                            </div>

                            <!-- Ticket Rates Table -->
                            <table class="table table-bordered text-center align-middle">
                                <thead class="table-success">
                                    <tr>
                                        <th class="text-start">Category</th>
                                        <th>Price (RM)</th>
                                        <th>Quantity</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <tr>
                                        <td class="text-start fw-bold">Adult<br><small class="text-muted fw-normal">Height above 120cm</small></td>
                                        <td class="fw-semibold">125.00</td>
                                        <td style="width: 150px;">
                                            <input type="number" name="adult_qty" class="form-control text-center" value="0" min="0">
                                        </td>
                                    </tr>
                                    <tr>
                                        <td class="text-start fw-bold">Child<br><small class="text-muted fw-normal">Height 90cm - 119cm</small></td>
                                        <td class="fw-semibold">95.00</td>
                                        <td>
                                            <input type="number" name="child_qty" class="form-control text-center" value="0" min="0">
                                        </td>
                                    </tr>
                                    <tr>
                                        <td class="text-start fw-bold">Senior Citizen<br><small class="text-muted fw-normal">60 years old and above</small></td>
                                        <td class="fw-semibold">95.00</td>
                                        <td>
                                            <input type="number" name="senior_qty" class="form-control text-center" value="0" min="0">
                                        </td>
                                    </tr>
                                </tbody>
                            </table>
                            
                            <!-- Proceed Button -->
                            <div class="d-flex justify-content-end mt-4">
                                <button type="submit" class="btn btn-success btn-lg px-5 fw-bold shadow-sm">Book Now</button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
<!-- End of content-wrapper -->

<?php
// Include global footer and JavaScript scripts
include 'footer.php';
?>