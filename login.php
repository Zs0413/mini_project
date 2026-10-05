<?php
// Start session and connect database
session_start();
include 'header.php';
include 'db.php';

$error_msg = "";

// Process login authentication
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $username = htmlspecialchars(trim($_POST['username']));
    $password = trim($_POST['password']);
    
    // Prepare and execute query
    $stmt = $conn->prepare("SELECT * FROM users WHERE username = ? LIMIT 1");
    $stmt->bind_param("s", $username);
    $stmt->execute();
    $result = $stmt->get_result();
    $user = $result->fetch_assoc();
    
    // Verify user credentials
    if ($user && password_verify($password, $user['password'])) {
        $_SESSION['user_id'] = $user['id'];
        $_SESSION['fullname'] = $user['fullname'];
        $_SESSION['role'] = $user['role'];
        
        // Route users based on privileges
        if ($user['role'] == 'admin') {
            header("Location: admin_dashboard.php");
        } else {
            header("Location: booking.php");
        }
        exit();
    } else {
        $error_msg = "Invalid username or password!";
    }
    $stmt->close();
}
$conn->close();
?>

<style>
    /* Fullscreen Background Slider Styles */
    body { margin: 0; overflow-x: hidden; }
    .bg-slider { position: fixed; top: 0; left: 0; width: 100vw; height: 100vh; z-index: -1; }
    .bg-slider .carousel-item { height: 100vh; transition: transform 2s ease, opacity .5s ease-out; }
    .bg-slider img { object-fit: cover; height: 100%; width: 100%; filter: brightness(0.45); }
    /* Glassmorphism Card Style */
    .glass-card { background: rgba(255, 255, 255, 0.92); backdrop-filter: blur(12px); border-radius: 16px; border: 1px solid rgba(255,255,255,0.3); box-shadow: 0 15px 35px rgba(0,0,0,0.3); }
    /* Hide footer on auth pages */
    footer { display: none !important; }
    .login-wrapper { min-height: 100vh; display: flex; align-items: center; justify-content: center; padding: 20px; }
</style>

<!-- Background Carousel -->
<div id="loginCarousel" class="carousel slide carousel-fade bg-slider" data-bs-ride="carousel" data-bs-interval="4000">
    <div class="carousel-inner">
        <div class="carousel-item active"><img src="images/image1.jpg" alt="Slide 1"></div>
        <div class="carousel-item"><img src="images/image2.jpg" alt="Slide 2"></div>
        <div class="carousel-item"><img src="images/image3.jpg" alt="Slide 3"></div>
    </div>
</div>

<!-- Authentication Interface -->
<div class="login-wrapper">
    <div class="col-md-6 col-lg-4">
        <div class="card glass-card">
            <div class="card-header bg-success text-white text-center py-4" style="border-radius: 16px 16px 0 0;">
                <h3 class="mb-0 fw-bold"><i class="fa-solid fa-water me-2"></i>Splash Mania</h3>
                <p class="mb-0 small text-white-50">Welcome to the adventure</p>
            </div>
            
            <div class="card-body p-4 p-md-5">
                <?php if($error_msg) echo "<div class='alert alert-danger shadow-sm'>$error_msg</div>"; ?>
                
                <form method="POST">
                    <div class="mb-4">
                        <label class="form-label fw-bold text-muted small text-uppercase">Username</label>
                        <div class="input-group">
                            <span class="input-group-text bg-light border-end-0"><i class="fa-solid fa-user text-muted"></i></span>
                            <input type="text" name="username" class="form-control bg-light border-start-0" placeholder="Enter username" required autofocus>
                        </div>
                    </div>
                    
                    <div class="mb-4">
                        <label class="form-label fw-bold text-muted small text-uppercase">Password</label>
                        <div class="input-group">
                            <span class="input-group-text bg-light border-end-0"><i class="fa-solid fa-lock text-muted"></i></span>
                            <input type="password" name="password" class="form-control bg-light border-start-0" placeholder="Enter password" required>
                        </div>
                    </div>
                    
                    <button type="submit" class="btn btn-success btn-lg w-100 fw-bold shadow-sm rounded-pill mt-3">Secure Login</button>
                </form>
                
                <div class="text-center mt-4">
                    <p class="text-muted mb-3">No account? <a href="register.php" class="text-success fw-bold text-decoration-none">Register here</a></p>
                    <hr class="text-muted">
                    <!-- Public Access Button for Gallery -->
                    <p class="text-muted small mb-2">Just browsing?</p>
                    <a href="gallery.php" class="btn btn-outline-success w-100 fw-bold rounded-pill">
                        <i class="fa-solid fa-images me-1"></i> Explore Park Gallery
                    </a>
                </div>
            </div>
        </div>
    </div>
</div>

<?php include 'footer.php'; ?>