<?php
// Include components and database connection
include 'header.php';
include 'db.php';

$success_msg = $error_msg = "";

// Process registration data
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $fullname = htmlspecialchars(trim($_POST['fullname']));
    $email = htmlspecialchars(trim($_POST['email']));
    $username = htmlspecialchars(trim($_POST['username']));
    $password = $_POST['password'];
    $confirm_password = $_POST['confirm_password'];
    $phone = htmlspecialchars(trim($_POST['phone']));
    
    // Validate input completeness
    if (empty($fullname) || empty($username) || empty($password) || empty($confirm_password) || empty($phone)) {
        $error_msg = "All required fields must be filled!";
    } 
    // Validate password consistency
    elseif ($password !== $confirm_password) {
        $error_msg = "Passwords do not match. Please try again!";
    } 
    else {
        // Prevent duplicate registration
        $check_stmt = $conn->prepare("SELECT id FROM users WHERE username = ? OR email = ? LIMIT 1");
        $check_stmt->bind_param("ss", $username, $email);
        $check_stmt->execute();
        $check_result = $check_stmt->get_result();
        
        if ($check_result->num_rows > 0) {
            $error_msg = "Username or Email is already registered!";
        } else {
            // Apply security hash
            $hashed_password = password_hash($password, PASSWORD_BCRYPT);
            
            // Force role to 'customer'
            $role = 'customer';
            
            // Execute data insertion
            $stmt = $conn->prepare("INSERT INTO users (fullname, email, username, password, phone, role) VALUES (?, ?, ?, ?, ?, ?)");
            $stmt->bind_param("ssssss", $fullname, $email, $username, $hashed_password, $phone, $role);
            
            if ($stmt->execute()) {
                $success_msg = "Registration successful! <a href='login.php' class='alert-link fw-bold'>Login here</a>.";
            } else {
                $error_msg = "Error: " . $stmt->error;
            }
            $stmt->close();
        }
        $check_stmt->close();
    }
    $conn->close();
}
?>

<style>
    body { margin: 0; overflow-x: hidden; }
    .bg-slider { position: fixed; top: 0; left: 0; width: 100vw; height: 100vh; z-index: -1; }
    .bg-slider .carousel-item { height: 100vh; transition: transform 2s ease, opacity .5s ease-out; }
    .bg-slider img { object-fit: cover; height: 100%; width: 100%; filter: brightness(0.45); }
    .glass-card { background: rgba(255, 255, 255, 0.95); backdrop-filter: blur(12px); border-radius: 16px; border: 1px solid rgba(255,255,255,0.3); box-shadow: 0 15px 35px rgba(0,0,0,0.3); }
    footer { display: none !important; }
    .auth-wrapper { min-height: 100vh; display: flex; align-items: center; justify-content: center; padding: 40px 20px; }
</style>

<div id="registerCarousel" class="carousel slide carousel-fade bg-slider" data-bs-ride="carousel" data-bs-interval="4000">
    <div class="carousel-inner">
        <div class="carousel-item active"><img src="images/image3.jpg" alt="Slide 1"></div>
        <div class="carousel-item"><img src="images/image1.jpg" alt="Slide 2"></div>
        <div class="carousel-item"><img src="images/image2.jpg" alt="Slide 3"></div>
    </div>
</div>

<div class="auth-wrapper">
    <div class="col-md-8 col-lg-6">
        <div class="card glass-card">
            <div class="card-header bg-success text-white text-center py-4" style="border-radius: 16px 16px 0 0;">
                <h4 class="mb-0 fw-bold">Create an Account</h4>
            </div>
            
            <div class="card-body p-4 p-md-5">
                <?php if($error_msg) echo "<div class='alert alert-danger shadow-sm'>$error_msg</div>"; ?>
                <?php if($success_msg) echo "<div class='alert alert-success shadow-sm'>$success_msg</div>"; ?>
                
                <form method="POST">
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label text-muted small fw-bold">Full Name *</label>
                            <input type="text" name="fullname" class="form-control bg-light" required>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label text-muted small fw-bold">Username *</label>
                            <input type="text" name="username" class="form-control bg-light" required>
                        </div>
                    </div>
                    
                    <div class="mb-3">
                        <label class="form-label text-muted small fw-bold">Email Address *</label>
                        <input type="email" name="email" class="form-control bg-light" required>
                    </div>
                    
                    <div class="row mb-3">
                        <div class="col-md-6 mb-3 mb-md-0">
                            <label class="form-label text-muted small fw-bold">Password *</label>
                            <input type="password" name="password" class="form-control bg-light" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label text-muted small fw-bold">Confirm Password *</label>
                            <input type="password" name="confirm_password" class="form-control bg-light" required>
                        </div>
                    </div>
                    
                    <div class="mb-4">
                        <label class="form-label text-muted small fw-bold">Phone Number *</label>
                        <input type="text" name="phone" class="form-control bg-light" required>
                    </div>
                    
                    <button type="submit" class="btn btn-success btn-lg w-100 py-3 fw-bold rounded-pill shadow-sm mt-2">Register Now</button>
                    
                    <div class="text-center mt-4">
                        <a href="login.php" class="text-decoration-none text-muted">Already have an account? <span class="text-success fw-bold">Login here</span></a>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<?php include 'footer.php'; ?>