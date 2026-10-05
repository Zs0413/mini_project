<?php
// Start session and connect database
session_start();
include 'db.php';

// Check if gallery.status column exists to prevent crash if SQL was not executed
$statusColExists = $conn->query("SHOW COLUMNS FROM gallery LIKE 'status'")->num_rows > 0;
$photoStatusCondition = $statusColExists ? "AND (g.status = 'visible' OR g.status IS NULL)" : "";

// Fetch visible pictures and album details for public view
$query = "SELECT g.*, a.title AS album_title, a.type, a.date_of_activity, a.start_promotion_date, a.end_promotion_date, a.about
          FROM gallery g 
          JOIN albums a ON g.album_id = a.id 
          WHERE a.status = 'visible' $photoStatusCondition 
          ORDER BY g.uploaded_at DESC";
$gallery_items = $conn->query($query)->fetch_all(MYSQLI_ASSOC);

// Extract top 5 recent images for the top carousel slider
$carousel_items = array_slice($gallery_items, 0, 5);

// Include navigation components
include 'header.php';
include 'navbar.php';
?>

<style>
    /* Carousel specific styles */
    .gallery-carousel { height: 60vh; min-height: 400px; background-color: #000; position: relative; }
    .gallery-carousel .carousel-item { height: 60vh; min-height: 400px; }
    .gallery-carousel img { object-fit: cover; height: 100%; width: 100%; opacity: 0.6; transition: opacity 0.5s ease; }
    .carousel-caption-custom { position: absolute; bottom: 20%; left: 10%; right: 10%; text-align: center; z-index: 10; text-shadow: 0 2px 10px rgba(0,0,0,0.8); }
    /* Interactive grid image styles */
    .grid-img-interactive { cursor: zoom-in; transition: transform 0.5s ease; }
    .grid-img-interactive:hover { transform: scale(1.05); }
    /* Lightbox overlay styling */
    .modal-backdrop.show { opacity: 0.9; }
</style>

<div class="content-wrapper">
    <!-- Top Dynamic Image Carousel -->
    <?php if (count($carousel_items) > 0): ?>
    <div id="mainGalleryCarousel" class="carousel slide carousel-fade gallery-carousel shadow-sm" data-bs-ride="carousel" data-bs-interval="4000">
        
        <!-- Carousel Indicators -->
        <div class="carousel-indicators">
            <?php foreach ($carousel_items as $index => $item): ?>
                <button type="button" data-bs-target="#mainGalleryCarousel" data-bs-slide-to="<?= $index ?>" class="<?= $index === 0 ? 'active' : '' ?>"></button>
            <?php endforeach; ?>
        </div>
        
        <!-- Carousel Images -->
        <div class="carousel-inner">
            <?php foreach ($carousel_items as $index => $item): ?>
                <div class="carousel-item <?= $index === 0 ? 'active' : '' ?>">
                    <img src="<?= htmlspecialchars($item['image_path']) ?>" class="d-block w-100" alt="Slider Image">
                    <div class="carousel-caption-custom text-white">
                        <?php if($item['type'] == 'Promotion'): ?>
                            <span class="badge bg-danger px-3 py-2 fs-6 rounded-pill mb-3 shadow"><i class="fa-solid fa-tags me-2"></i>Latest Promotion</span>
                        <?php else: ?>
                            <span class="badge bg-success px-3 py-2 fs-6 rounded-pill mb-3 shadow"><i class="fa-solid fa-camera me-2"></i>Featured Highlight</span>
                        <?php endif; ?>
                        
                        <h1 class="display-4 fw-bold mb-2"><?= htmlspecialchars($item['album_title']) ?></h1>
                        <p class="lead fs-4 mb-4 d-none d-md-block">"<?= htmlspecialchars($item['caption']) ?: 'Discover amazing moments at Splash Mania' ?>"</p>
                        
                        <?php if(!isset($_SESSION['user_id'])): ?>
                            <a href="login.php" class="btn btn-success btn-lg fw-bold px-5 rounded-pill shadow-lg mt-2">Book Tickets Now</a>
                        <?php endif; ?>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
        
        <!-- Carousel Navigation Controls -->
        <button class="carousel-control-prev" type="button" data-bs-target="#mainGalleryCarousel" data-bs-slide="prev">
            <span class="carousel-control-prev-icon" aria-hidden="true"></span>
            <span class="visually-hidden">Previous</span>
        </button>
        <button class="carousel-control-next" type="button" data-bs-target="#mainGalleryCarousel" data-bs-slide="next">
            <span class="carousel-control-next-icon" aria-hidden="true"></span>
            <span class="visually-hidden">Next</span>
        </button>
    </div>
    <?php else: ?>
    <!-- Fallback Header if no images exist -->
    <div class="bg-dark text-white text-center py-5 shadow-sm">
        <h1 class="fw-bold display-4">Park Gallery</h1>
        <p class="lead">Check back later for amazing park moments!</p>
    </div>
    <?php endif; ?>

    <!-- Gallery Grid Area -->
    <div class="container mt-5 mb-5">
        <div class="row">
            <?php if (count($gallery_items) > 0): ?>
                <?php foreach ($gallery_items as $index => $item): ?>
                    <div class="col-md-4 col-sm-6 mb-4">
                        <div class="card shadow-sm h-100 border-0 rounded-4 overflow-hidden">
                            
                            <!-- Image Section with Lightbox Trigger -->
                            <div class="position-relative overflow-hidden">
                                <img src="<?= htmlspecialchars($item['image_path']) ?>" 
                                     class="card-img-top grid-img-interactive" 
                                     style="height: 250px; object-fit: cover;" 
                                     alt="Gallery Image"
                                     onclick="openPublicLightbox('<?= htmlspecialchars($item['image_path']) ?>', '<?= htmlspecialchars(addslashes($item['caption'])) ?>', '<?= htmlspecialchars(addslashes($item['album_title'])) ?>')">
                                
                                <!-- Dynamic Badge -->
                                <div class="position-absolute top-0 end-0 m-3" style="pointer-events: none;">
                                    <?php if($item['type'] == 'Promotion'): ?>
                                        <span class="badge bg-danger px-3 py-2 rounded-pill shadow-sm"><i class="fa-solid fa-tags me-1"></i>Promo</span>
                                    <?php else: ?>
                                        <span class="badge bg-success px-3 py-2 rounded-pill shadow-sm"><i class="fa-solid fa-camera me-1"></i>Gallery</span>
                                    <?php endif; ?>
                                </div>
                            </div>

                            <!-- Content Details Section -->
                            <div class="card-body p-4 d-flex flex-column">
                                <h5 class="fw-bold text-dark mb-2"><?= htmlspecialchars($item['album_title']) ?></h5>
                                
                                <div class="mb-3 small text-muted">
                                    <?php if(!empty($item['date_of_activity'])): ?>
                                        <div><i class="fa-regular fa-calendar-check me-2"></i>Date: <?= date("d M Y", strtotime($item['date_of_activity'])) ?></div>
                                    <?php endif; ?>
                                    
                                    <?php if(!empty($item['start_promotion_date']) && !empty($item['end_promotion_date'])): ?>
                                        <div><i class="fa-solid fa-hourglass-half me-2"></i>Valid: <?= date("d M Y", strtotime($item['start_promotion_date'])) ?> - <?= date("d M Y", strtotime($item['end_promotion_date'])) ?></div>
                                    <?php endif; ?>
                                </div>

                                <?php if(!empty($item['about'])): ?>
                                    <p class="card-text text-secondary small border-start border-3 border-success ps-2 mb-3">
                                        <?= nl2br(htmlspecialchars($item['about'])) ?>
                                    </p>
                                <?php endif; ?>
                                
                                <p class="card-text text-muted mt-auto fst-italic">
                                    "<?= htmlspecialchars($item['caption']) ?: 'Experience the thrill with us!' ?>"
                                </p>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>
    </div>
</div>

<!-- Lightbox Interactive Modal -->
<div class="modal fade" id="publicLightboxModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-xl">
        <div class="modal-content bg-transparent border-0 shadow-none">
            <div class="modal-body text-center position-relative p-0">
                <button type="button" class="btn-close btn-close-white position-absolute top-0 end-0 m-3" data-bs-dismiss="modal" style="z-index: 1050; opacity: 0.8;"></button>
                <img id="publicLightboxImage" src="" class="img-fluid rounded-3 shadow-lg" style="max-height: 85vh; object-fit: contain;">
                
                <div class="mt-3 text-white p-3 rounded-3" style="background: rgba(0,0,0,0.6); display: inline-block;">
                    <h4 id="publicLightboxTitle" class="fw-bold mb-1 text-success"></h4>
                    <p id="publicLightboxCaption" class="mb-0 text-white-50"></p>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Lightbox Interactivity Script -->
<script>
function openPublicLightbox(imageSrc, caption, title) {
    document.getElementById('publicLightboxImage').src = imageSrc;
    document.getElementById('publicLightboxTitle').innerText = title;
    document.getElementById('publicLightboxCaption').innerText = caption ? '"' + caption + '"' : 'No description available';
    
    // Trigger bootstrap modal instance
    new bootstrap.Modal(document.getElementById('publicLightboxModal')).show();
}
</script>

<?php include 'footer.php'; ?>