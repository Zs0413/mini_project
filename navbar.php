<!-- navbar.php -->
<nav class="navbar navbar-expand-lg navbar-dark bg-success shadow">
    <div class="container-fluid px-5">
        <a class="navbar-brand fw-bold" href="booking.php">SPLASH MANIA</a>
        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
            <span class="navbar-toggler-icon"></span>
        </button>
        <div class="collapse navbar-collapse justify-content-end" id="navbarNav">
            <ul class="navbar-nav">
                <?php if(isset($_SESSION['user_id'])): ?>
                    <!-- Menu for LOGGED IN users -->
                    <li class="nav-item"><a class="nav-link" href="booking.php">Home</a></li>
                    <li class="nav-item"><a class="nav-link" href="gallery.php">Gallery</a></li>
                    <li class="nav-item"><a class="nav-link" href="history.php">Booking History</a></li>
                    <li class="nav-item"><a class="nav-link" href="logout.php">Logout</a></li>
                <?php else: ?>
                    <!-- Menu for PUBLIC (Not logged in) visitors -->
                    <li class="nav-item"><a class="nav-link fw-bold text-warning" href="gallery.php"><i class="fa-solid fa-camera me-1"></i> Park Gallery</a></li>
                    <li class="nav-item"><a class="nav-link" href="login.php">Login / Register</a></li>
                <?php endif; ?>
            </ul>
        </div>
    </div>
</nav>