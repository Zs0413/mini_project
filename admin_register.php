<?php
// Include header and database connection
include 'header.php';
include 'db.php';

$success_msg = $error_msg = "";

// Process form submission
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    
    // Sanitize user inputs
    $fullname = htmlspecialchars(trim($_POST['fullname']));
    $email = htmlspecialchars(trim($_POST['email']));
    $username = htmlspecialchars(trim($_POST['username']));
    $password = $_POST['password'];
    $confirm_password = $_POST['confirm_password'];
    $phone = htmlspecialchars(trim($_POST['phone']));
    
    // Validate empty fields
    if (empty($fullname) || empty($username) || empty($password) || empty($confirm_password) || empty($phone)) {
        $error_msg = "All fields are required!";
    } 
    // Validate password match
    elseif ($password !== $confirm_password) {
        $error_msg = "Passwords do not match. Please try again!";
    } 
    else {
        // Check if username or email already exists
        $check_stmt = $conn->prepare("SELECT id FROM users WHERE username = ? OR email = ? LIMIT 1");
        $check_stmt->bind_param("ss", $username, $email);
        $check_stmt->execute();
        $check_result = $check_stmt->get_result();
        
        if ($check_result->num_rows > 0) {
            $error_msg = "Username or Email is already registered!";
        } else {
            // Hash password for security
            $hashed_password = password_hash($password, PASSWORD_BCRYPT);
            
            // Insert new admin into database (Force role as 'admin')
            $stmt = $conn->prepare("INSERT INTO users (fullname, email, username, password, phone, role) VALUES (?, ?, ?, ?, ?, 'admin')");
            $stmt->bind_param("sssss", $fullname, $email, $username, $hashed_password, $phone);
            
            // Check if insertion is successful
            if ($stmt->execute()) {
                $success_msg = "Admin Registration successful! <a href='login.php' class='alert-link fw-bold'>Login here</a>.";
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

<div class="container mt-5 mb-5 d-flex align-items-center justify-content-center" style="min-height: 75vh;">
    <div class="card shadow-lg col-md-6 mx-auto border-0 rounded-4">
        <div class="card-header bg-success text-white text-center py-4 rounded-top-4 border-0">
            <h4 class="mb-0 fw-bold"><i class="fa-solid fa-user-shield me-2"></i>Admin Registration</h4>
        </div>
        
        <div class="card-body p-4 p-md-5 bg-white rounded-bottom-4">
            <!-- Display system messages -->
            <?php if($error_msg) echo "<div class='alert alert-danger shadow-sm'>$error_msg</div>"; ?>
            <?php if($success_msg) echo "<div class='alert alert-success shadow-sm'>$success_msg</div>"; ?>
            
            <form method="POST">
                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label class="form-label text-muted small fw-bold">Full Name</label>
                        <input type="text" name="fullname" class="form-control bg-light" required>
                    </div>
                    <div class="col-md-6 mb-3">
                        <label class="form-label text-muted small fw-bold">Username</label>
                        <input type="text" name="username" class="form-control bg-light" required>
                    </div>
                </div>
                
                <div class="mb-3">
                    <label class="form-label text-muted small fw-bold">Email Address</label>
                    <input type="email" name="email" class="form-control bg-light" required>
                </div>
                
                <div class="row mb-3">
                    <div class="col-md-6 mb-3 mb-md-0">
                        <label class="form-label text-muted small fw-bold">Password</label>
                        <input type="password" name="password" class="form-control bg-light" required>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label text-muted small fw-bold">Confirm Password</label>
                        <input type="password" name="confirm_password" class="form-control bg-light" required>
                    </div>
                </div>
                
                <div class="mb-4">
                    <label class="form-label text-muted small fw-bold">Phone Number</label>
                    <input type="text" name="phone" class="form-control bg-light" required>
                </div>
                
                <button type="submit" class="btn btn-success btn-lg w-100 py-3 fw-bold rounded-pill shadow-sm mt-3">Register Admin</button>
                
                <div class="text-center mt-4">
                    <a href="login.php" class="text-decoration-none text-muted">Back to <span class="text-success fw-bold">Login</span></a>
                </div>
            </form>
        </div>
    </div>
</div>

<?php include 'footer.php'; ?>