<?php
// Initialize session and database connection
session_start();
include 'db.php';

// Authenticate admin user
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header("Location: login.php");
    exit();
}

// Create new album
if (isset($_POST['create_album'])) {
    $type = $_POST['type']; 
    $title = trim($_POST['album_title']);
    $date_of_activity = !empty($_POST['date_of_activity']) ? $_POST['date_of_activity'] : null;
    $start_promo = !empty($_POST['start_promotion_date']) ? $_POST['start_promotion_date'] : null;
    $end_promo = !empty($_POST['end_promotion_date']) ? $_POST['end_promotion_date'] : null;
    $about = trim($_POST['about']);

    $stmt = $conn->prepare("INSERT INTO albums (type, title, date_of_activity, start_promotion_date, end_promotion_date, about) VALUES (?, ?, ?, ?, ?, ?)");
    $stmt->bind_param("ssssss", $type, $title, $date_of_activity, $start_promo, $end_promo, $about);
    $stmt->execute();
    $stmt->close();
    
    $_SESSION['success_msg'] = "Album created successfully.";
    header("Location: admin_gallery.php");
    exit();
}

// Rename album
if (isset($_POST['edit_album'])) {
    $edit_id = (int)$_POST['edit_album_id'];
    $new_title = trim($_POST['new_album_title']);
    
    $stmt = $conn->prepare("UPDATE albums SET title = ? WHERE id = ?");
    $stmt->bind_param("si", $new_title, $edit_id);
    $stmt->execute();
    $stmt->close();
    
    $_SESSION['success_msg'] = "Album title updated successfully.";
    header("Location: admin_gallery.php");
    exit();
}

// Toggle album visibility
if (isset($_GET['toggle_album']) && isset($_GET['current_status'])) {
    $album_id = (int)$_GET['toggle_album'];
    $new_status = ($_GET['current_status'] === 'visible') ? 'hidden' : 'visible';
    
    $stmt = $conn->prepare("UPDATE albums SET status = ? WHERE id = ?");
    $stmt->bind_param("si", $new_status, $album_id);
    $stmt->execute();
    $stmt->close();
    
    $_SESSION['success_msg'] = "Album visibility updated.";
    $filter_param = isset($_GET['filter_album']) ? "&filter_album=" . (int)$_GET['filter_album'] : "";
    header("Location: admin_gallery.php?" . ltrim($filter_param, '&'));
    exit();
}

// Toggle photo visibility
if (isset($_GET['toggle_photo']) && isset($_GET['current_status'])) {
    $photo_id = (int)$_GET['toggle_photo'];
    $new_status = ($_GET['current_status'] === 'visible') ? 'hidden' : 'visible';
    
    $stmt = $conn->prepare("UPDATE gallery SET status = ? WHERE id = ?");
    if($stmt) {
        $stmt->bind_param("si", $new_status, $photo_id);
        $stmt->execute();
        $stmt->close();
    }
    
    $_SESSION['success_msg'] = "Photo visibility updated.";
    $filter_param = isset($_GET['filter_album']) ? "&filter_album=" . (int)$_GET['filter_album'] : "";
    header("Location: admin_gallery.php?" . ltrim($filter_param, '&'));
    exit();
}

// Delete empty album
if (isset($_GET['delete_album_id'])) {
    $album_id = (int)$_GET['delete_album_id'];
    
    $stmt = $conn->prepare("SELECT COUNT(*) as photo_count FROM gallery WHERE album_id = ?");
    $stmt->bind_param("i", $album_id);
    $stmt->execute();
    $result = $stmt->get_result()->fetch_assoc();
    $photo_count = $result['photo_count'];
    $stmt->close();
    
    if ($photo_count > 0) {
        $_SESSION['error_msg'] = "Cannot delete album. It contains $photo_count photos.";
    } else {
        $stmt = $conn->prepare("DELETE FROM albums WHERE id = ?");
        $stmt->bind_param("i", $album_id);
        if($stmt->execute()) {
            $_SESSION['success_msg'] = "Empty album deleted successfully.";
        }
        $stmt->close();
    }
    header("Location: admin_gallery.php");
    exit();
}

// Upload multiple photos
if (isset($_POST['upload_picture'])) {
    $album_id = (int)$_POST['album_id'];
    $caption = trim($_POST['caption']);
    
    $stmt_album = $conn->prepare("SELECT title FROM albums WHERE id = ?");
    $stmt_album->bind_param("i", $album_id);
    $stmt_album->execute();
    $album_data = $stmt_album->get_result()->fetch_assoc();
    $stmt_album->close();
    
    $clean_title = preg_replace('/[^A-Za-z0-9\-]/', '_', $album_data['title']);
    $target_dir = "uploads/" . date('Y/m') . "/";
    if (!is_dir($target_dir)) { mkdir($target_dir, 0777, true); }
    
    $total_files = count($_FILES['images']['name']);
    $success_count = 0;
    
    for ($i = 0; $i < $total_files; $i++) {
        if ($_FILES['images']['error'][$i] !== UPLOAD_ERR_OK) continue;
        
        $tmp_name = $_FILES['images']['tmp_name'][$i];
        $img_info = @getimagesize($tmp_name);
        if ($img_info === false) continue;
        
        $mime = $img_info['mime'];
        $file_name = $clean_title . "_" . uniqid() . "_" . $i . ".webp";
        $target_file = $target_dir . $file_name;
        
        $source_img = null;
        switch ($mime) {
            case 'image/jpeg': $source_img = imagecreatefromjpeg($tmp_name); break;
            case 'image/png':
                $source_img = imagecreatefrompng($tmp_name);
                imagepalettetotruecolor($source_img);
                imagealphablending($source_img, true);
                imagesavealpha($source_img, true);
                break;
            case 'image/webp': $source_img = imagecreatefromwebp($tmp_name); break;
            default: continue 2; 
        }
        
        if ($source_img && imagewebp($source_img, $target_file, 80)) {
            imagedestroy($source_img);
            $stmt = $conn->prepare("INSERT INTO gallery (album_id, image_path, caption) VALUES (?, ?, ?)");
            $stmt->bind_param("iss", $album_id, $target_file, $caption);
            $stmt->execute();
            $stmt->close();
            $success_count++;
        }
    }
    
    $_SESSION['success_msg'] = "Successfully uploaded $success_count photos.";
    header("Location: admin_gallery.php?filter_album=" . $album_id); 
    exit();
}

// Delete single photo
if (isset($_GET['delete_id'])) {
    $id = (int)$_GET['delete_id'];
    $stmt = $conn->prepare("SELECT image_path FROM gallery WHERE id = ?");
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($row = $result->fetch_assoc()) {
        if (file_exists($row['image_path'])) { unlink($row['image_path']); }
    }
    $stmt->close();
    
    $stmt = $conn->prepare("DELETE FROM gallery WHERE id = ?");
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $stmt->close();
    
    $_SESSION['success_msg'] = "Photo deleted successfully.";
    $filter_param = isset($_GET['filter_album']) ? "?filter_album=" . (int)$_GET['filter_album'] : "";
    header("Location: admin_gallery.php" . $filter_param);
    exit();
}

// Bulk delete photos
if (isset($_POST['bulk_delete']) && !empty($_POST['photo_ids'])) {
    $photo_ids = $_POST['photo_ids']; 
    $deleted_count = 0;
    
    foreach ($photo_ids as $p_id) {
        $id = (int)$p_id;
        $stmt = $conn->prepare("SELECT image_path FROM gallery WHERE id = ?");
        $stmt->bind_param("i", $id);
        $stmt->execute();
        $res = $stmt->get_result();
        if ($row = $res->fetch_assoc()) {
            if (file_exists($row['image_path'])) unlink($row['image_path']);
        }
        $stmt->close();
        
        $stmt = $conn->prepare("DELETE FROM gallery WHERE id = ?");
        $stmt->bind_param("i", $id);
        if($stmt->execute()) {
            $deleted_count++;
        }
        $stmt->close();
    }
    
    $_SESSION['success_msg'] = "$deleted_count photos deleted successfully.";
    $filter_param = isset($_GET['filter_album']) ? "?filter_album=" . (int)$_GET['filter_album'] : "";
    header("Location: admin_gallery.php" . $filter_param);
    exit();
}

// Edit photo details
if (isset($_POST['edit_photo'])) {
    $photo_id = (int)$_POST['photo_id'];
    $new_album_id = (int)$_POST['new_album_id'];
    $new_caption = trim($_POST['new_caption']);
    
    $stmt = $conn->prepare("UPDATE gallery SET album_id = ?, caption = ? WHERE id = ?");
    $stmt->bind_param("isi", $new_album_id, $new_caption, $photo_id);
    $stmt->execute();
    $stmt->close();
    
    $_SESSION['success_msg'] = "Photo details updated successfully.";
    $filter_param = isset($_GET['filter_album']) ? "?filter_album=" . (int)$_GET['filter_album'] : "";
    header("Location: admin_gallery.php" . $filter_param);
    exit();
}

// Fetch initial data
$albums = $conn->query("SELECT * FROM albums ORDER BY created_at DESC")->fetch_all(MYSQLI_ASSOC);
$active_filter_id = isset($_GET['filter_album']) ? (int)$_GET['filter_album'] : 0;
$active_filter_name = "Live Gallery Feed (All Photos)";

// Check status column
$statusColExists = $conn->query("SHOW COLUMNS FROM gallery LIKE 'status'")->num_rows > 0;
$selectStatus = $statusColExists ? "g.status AS photo_status" : "NULL AS photo_status";

if ($active_filter_id > 0) {
    $stmt = $conn->prepare("SELECT g.*, $selectStatus, a.title AS album_title, a.status, a.type FROM gallery g JOIN albums a ON g.album_id = a.id WHERE g.album_id = ? ORDER BY g.uploaded_at DESC");
    $stmt->bind_param("i", $active_filter_id);
    $stmt->execute();
    $gallery_items = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
    $stmt->close();
    
    foreach ($albums as $a) {
        if ($a['id'] == $active_filter_id) {
            $active_filter_name = "Filtered by: " . htmlspecialchars($a['title']);
            break;
        }
    }
} else {
    $query = "SELECT g.*, $selectStatus, a.title AS album_title, a.status, a.type FROM gallery g JOIN albums a ON g.album_id = a.id ORDER BY g.uploaded_at DESC";
    $gallery_items = $conn->query($query)->fetch_all(MYSQLI_ASSOC);
}

// Load views
include 'header.php';
include 'admin_sidebar.php';
?>

<style>
    .card-modern { border-radius: 16px; border: none; box-shadow: 0 8px 30px rgba(0,0,0,0.04); background: #fff; }
    
    .upload-box { border: 2px dashed rgba(25, 135, 84, 0.4); border-radius: 12px; background-color: #fafdfb; padding: 30px 20px; transition: all 0.3s ease; cursor: pointer; }
    .upload-box:hover { background-color: #f0f9f4; border-color: #198754; }
    
    .list-group-item { transition: all 0.2s ease; border-color: rgba(0,0,0,0.05); }
    .list-group-item:hover { background-color: #f8f9fa; }
    .album-active { background-color: #e8f5e9 !important; border-left: 4px solid #198754 !important; }
    .album-link { cursor: pointer; transition: color 0.2s; text-decoration: none; }
    .album-link:hover { color: #198754 !important; }
    
    .gallery-card { border-radius: 12px; overflow: hidden; border: none; box-shadow: 0 4px 15px rgba(0,0,0,0.05); position: relative; background: #000; }
    .gallery-img { width: 100%; height: 250px; object-fit: cover; transition: transform 0.5s ease, opacity 0.3s; }
    .gallery-card:hover .gallery-img { transform: scale(1.05); }
    
    .hidden-img { opacity: 0.4; filter: grayscale(100%); }
    .hidden-badge { position: absolute; top: 12px; left: 12px; z-index: 50; font-size: 0.7rem; letter-spacing: 1px;}
    .gallery-overlay { position: absolute; bottom: 0; left: 0; right: 0; background: linear-gradient(to top, rgba(0,0,0,0.9), transparent); padding: 40px 15px 15px 15px; color: white; pointer-events: none; }
    
    .action-group { position: absolute; top: 50%; left: 50%; transform: translate(-50%, -50%); z-index: 50; display: flex; gap: 15px; opacity: 0; transition: opacity 0.3s ease; }
    .gallery-card:hover .action-group { opacity: 1; }
    
    .action-btn { width: 40px; height: 40px; display: flex; align-items: center; justify-content: center; background: rgba(255, 255, 255, 0.95); border-radius: 50%; text-decoration: none; transition: 0.2s; box-shadow: 0 4px 10px rgba(0,0,0,0.3); cursor: pointer; border: none; font-size: 1.1rem;}
    .action-btn-eye { color: #198754; }
    .action-btn-eye:hover { background: #198754; color: white; transform: scale(1.1); }
    .action-btn-edit { color: #0d6efd; }
    .action-btn-edit:hover { background: #0d6efd; color: white; transform: scale(1.1); }
    .action-btn-delete { color: #dc3545; }
    .action-btn-delete:hover { background: #dc3545; color: white; transform: scale(1.1); }
    
    .bulk-checkbox { position: absolute; top: 12px; right: 12px; z-index: 50; width: 22px; height: 22px; cursor: pointer; box-shadow: 0 2px 5px rgba(0,0,0,0.4); border-radius: 4px; accent-color: #dc3545; }
    
    .form-control, .form-select { border-radius: 8px; padding: 10px 15px; border: 1px solid rgba(0,0,0,0.1); }
    .form-control:focus, .form-select:focus { box-shadow: 0 0 0 3px rgba(25, 135, 84, 0.1); border-color: #198754;}
    
    .album-toolbar-btn { color: #adb5bd; transition: color 0.2s; display: inline-flex; align-items: center; justify-content: center; padding: 2px 5px; }
    .btn-edit-album:hover { color: #0d6efd; }
    .btn-del-album:hover { color: #dc3545; }
    
    .smart-filter-bar { background: #fff; border-radius: 12px; box-shadow: 0 4px 15px rgba(0,0,0,0.03); padding: 15px 20px; margin-bottom: 25px; border: 1px solid rgba(0,0,0,0.05); }
</style>

<div class="main-content">
    
    <!-- Notifications -->
    <?php if(isset($_SESSION['error_msg'])): ?>
        <div class="alert alert-danger alert-dismissible fade show border-0 shadow-sm" role="alert">
            <i class="fa-solid fa-triangle-exclamation me-2"></i> <?= $_SESSION['error_msg'] ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
        <?php unset($_SESSION['error_msg']); ?>
    <?php endif; ?>
    
    <?php if(isset($_SESSION['success_msg'])): ?>
        <div class="alert alert-success alert-dismissible fade show border-0 shadow-sm" role="alert">
            <i class="fa-solid fa-check-circle me-2"></i> <?= $_SESSION['success_msg'] ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
        <?php unset($_SESSION['success_msg']); ?>
    <?php endif; ?>

    <!-- Header -->
    <div class="mb-5">
        <h2 class="fw-bold text-dark mb-1">Gallery Studio</h2>
        <p class="text-muted">Manage albums and upload high-resolution park imagery.</p>
    </div>

    <div class="row mb-5">
        <!-- Album Management -->
        <div class="col-lg-4 mb-4">
            <div class="card-modern p-4 mb-4">
                <form method="POST">
                    <label class="form-label fw-bold text-dark mb-3"><i class="fa-solid fa-folder-plus text-success me-2"></i>Create Album / Promotion</label>
                    
                    <select name="type" id="albumTypeSelect" class="form-select bg-light mb-2" onchange="toggleDateFields()" required>
                        <option value="Gallery">Gallery (Normal Photo Album)</option>
                        <option value="Promotion">Promotion (Marketing Campaign)</option>
                    </select>
                    
                    <input type="text" name="album_title" class="form-control bg-light mb-2" placeholder="Title (e.g. Summer Splash)" required>
                    
                    <div id="galleryDateField">
                        <label class="small text-muted">Date of Activity</label>
                        <input type="date" name="date_of_activity" class="form-control bg-light mb-2">
                    </div>
                    
                    <div id="promoDateFields" style="display: none;">
                        <div class="row g-2 mb-2">
                            <div class="col-6">
                                <label class="small text-muted">Start Date</label>
                                <input type="date" name="start_promotion_date" class="form-control bg-light">
                            </div>
                            <div class="col-6">
                                <label class="small text-muted">End Date</label>
                                <input type="date" name="end_promotion_date" class="form-control bg-light">
                            </div>
                        </div>
                    </div>
                    
                    <textarea name="about" class="form-control bg-light mb-3" rows="2" placeholder="About / Description..."></textarea>
                    
                    <button type="submit" name="create_album" class="btn btn-success w-100 fw-bold">Create Now</button>
                </form>
            </div>

            <!-- Album List -->
            <div class="card-modern overflow-hidden">
                <div class="bg-white px-4 py-3 border-bottom d-flex justify-content-between align-items-center">
                    <span class="fw-bold text-dark"><i class="fa-solid fa-layer-group text-success me-2"></i>Directory</span>
                    <span class="badge bg-success bg-opacity-10 text-success rounded-pill px-3"><?= count($albums) ?></span>
                </div>
                
                <div class="bg-light p-2 text-center border-bottom">
                    <a href="admin_gallery.php" class="text-decoration-none small fw-bold <?= ($active_filter_id === 0) ? 'text-success' : 'text-muted' ?>">
                        <i class="fa-solid fa-border-all me-1"></i> View All Photos
                    </a>
                </div>

                <div class="p-0" style="max-height: 300px; overflow-y: auto;">
                    <ul class="list-group list-group-flush">
                        <?php foreach ($albums as $a): ?>
                            <?php 
                                $currentStatus = ($a['status'] === 'hidden') ? 'hidden' : 'visible';
                                $isHidden = ($currentStatus === 'hidden');
                                $isActive = ($active_filter_id === (int)$a['id']);
                            ?>
                            <li class="list-group-item d-flex justify-content-between align-items-center px-4 py-3 <?= $isHidden ? 'bg-light' : '' ?> <?= $isActive ? 'album-active' : '' ?>">
                                <div class="d-flex align-items-center flex-wrap gap-2">
                                    <a href="admin_gallery.php?filter_album=<?= $a['id'] ?>" class="album-link fw-bold <?= $isHidden ? 'text-muted text-decoration-line-through' : 'text-dark' ?> text-break">
                                        <?= htmlspecialchars($a['title']) ?>
                                    </a>
                                    <?php if(isset($a['type']) && $a['type'] === 'Promotion'): ?>
                                        <span class="badge bg-danger shadow-sm" style="font-size: 0.65rem;">Promo</span>
                                    <?php else: ?>
                                        <span class="badge bg-success shadow-sm" style="font-size: 0.65rem;">Gallery</span>
                                    <?php endif; ?>
                                </div>
                                
                                <div class="d-flex align-items-center gap-1 ms-2 flex-shrink-0">
                                    <button type="button" class="btn btn-link album-toolbar-btn btn-edit-album" 
                                            onclick="openEditModal(<?= $a['id'] ?>, '<?= htmlspecialchars(addslashes($a['title'])) ?>')" title="Edit Album">
                                        <i class="fa-solid fa-pen-to-square"></i>
                                    </button>
                                    
                                    <a href="admin_gallery.php?toggle_album=<?= $a['id'] ?>&current_status=<?= $currentStatus ?>&filter_album=<?= $active_filter_id ?>" 
                                        class="album-toolbar-btn text-decoration-none <?= $isHidden ? 'text-success' : 'text-danger' ?>"
                                       title="<?= $isHidden ? 'Click to Show Album' : 'Click to Hide Album' ?>">
                                        <?= $isHidden ? '<i class="fa-regular fa-eye"></i>' : '<i class="fa-regular fa-eye-slash"></i>' ?>
                                    </a>
                                    
                                    <a href="admin_gallery.php?delete_album_id=<?= $a['id'] ?>" class="album-toolbar-btn btn-del-album" 
                                        onclick="return confirm('Attempting to delete album. Note: Only empty albums can be deleted. Proceed?');" title="Delete Album">
                                        <i class="fa-regular fa-trash-can"></i>
                                    </a>
                                </div>
                            </li>
                        <?php endforeach; ?>
                    </ul>
                </div>
            </div>
        </div>

        <!-- Upload Form -->
        <div class="col-lg-8">
            <div class="card-modern h-100 p-4 p-md-5">
                <h5 class="fw-bold text-dark mb-4"><i class="fa-solid fa-cloud-arrow-up text-success me-2"></i>Batch Upload Engine</h5>
                <form method="POST" enctype="multipart/form-data">
                    <div class="row mb-4">
                        <div class="col-md-6 mb-3 mb-md-0">
                            <label class="form-label fw-bold text-muted small">Select Destination Album</label>
                            <select name="album_id" class="form-select bg-light border-0" required>
                                <option value="">-- Choose Album --</option>
                                <?php foreach ($albums as $a): ?>
                                    <option value="<?= $a['id'] ?>" <?= ($active_filter_id === (int)$a['id']) ? 'selected' : '' ?>>
                                        <?= htmlspecialchars($a['title']) ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-bold text-muted small">Global Caption (Optional)</label>
                            <input type="text" name="caption" class="form-control bg-light border-0" placeholder="Describe these photos...">
                        </div>
                    </div>
                    
                    <div class="upload-box text-center mb-4 position-relative">
                        <i class="fa-solid fa-images fa-3x text-success mb-3 opacity-75"></i>
                        <h6 class="fw-bold text-dark mb-1">Select or Drag Images Here</h6>
                        <p class="text-muted small mb-4">Auto WEBP compression applied. Hold Ctrl/Cmd to select multiple.</p>
                        <input type="file" name="images[]" class="form-control" accept="image/png, image/jpeg, image/webp" multiple required>
                    </div>
                    
                    <div class="text-end">
                        <button type="submit" name="upload_picture" class="btn btn-success px-5 py-2 fw-bold shadow-sm rounded-pill">
                            Publish Now <i class="fa-solid fa-paper-plane ms-2"></i>
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Gallery Grid -->
    <form method="POST" id="bulkDeleteForm" action="admin_gallery.php?filter_album=<?= $active_filter_id ?>">
        
        <div class="d-flex justify-content-between align-items-center mb-3 mt-5">
            <div>
                <h5 class="fw-bold text-dark mb-1"><?= $active_filter_name ?></h5>
                
                <?php if($active_filter_id > 0): ?>
                    <a href="admin_gallery.php" class="btn btn-sm btn-outline-secondary rounded-pill fw-bold ms-2">
                        <i class="fa-solid fa-arrow-left me-1"></i> Back to All Photos
                    </a>
                <?php endif; ?>
            </div>
            
            <?php if (count($gallery_items) > 0): ?>
            <div class="d-flex align-items-center bg-white px-3 py-2 rounded-pill shadow-sm border">
                <div class="form-check mb-0 me-4">
                    <input class="form-check-input" type="checkbox" id="selectAllCheckbox" style="cursor: pointer; width: 18px; height: 18px;">
                    <label class="form-check-label fw-bold text-muted ms-1" for="selectAllCheckbox" style="cursor: pointer;">
                        Select All
                    </label>
                </div>
                <button type="submit" name="bulk_delete" id="bulkDeleteBtn" class="btn btn-danger btn-sm rounded-pill px-3 fw-bold" style="display: none;" onclick="return confirm('WARNING: Are you sure you want to permanently delete the selected photos?');">
                    <i class="fa-regular fa-trash-can me-1"></i> Delete Selected (<span id="selectedCount">0</span>)
                </button>
            </div>
            <?php endif; ?>
        </div>

        <!-- Filter Bar -->
        <?php if (count($gallery_items) > 0): ?>
        <div class="smart-filter-bar d-flex align-items-center justify-content-between">
            <div class="d-flex align-items-center w-100">
                <i class="fa-solid fa-magnifying-glass text-muted me-3"></i>
                <input type="text" id="instantSearch" class="form-control border-0 shadow-none bg-transparent p-0" style="font-size: 1.05rem;" placeholder="Instant search by caption, album, or date (e.g. 'Oct', 'Food')...">
            </div>
            <div class="ms-4 border-start ps-4 d-flex" style="min-width: 320px; gap:10px;">
                <select id="instantTypeDropdown" class="form-select border-0 bg-light text-muted fw-bold">
                    <option value="">All Categories</option>
                    <option value="gallery">Gallery</option>
                    <option value="promotion">Promotion</option>
                </select>
                <select id="instantDropdown" class="form-select border-0 bg-light text-muted fw-bold">
                    <option value="">All Albums</option>
                    <?php foreach ($albums as $a): ?>
                        <option value="<?= strtolower($a['title']) ?>" data-type="<?= strtolower($a['type']) ?>">
                            <?= htmlspecialchars($a['title']) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
        </div>
        <?php endif; ?>
        
        <!-- Image Grid -->
        <div class="row" id="galleryContainer">
            <?php if (count($gallery_items) > 0): ?>
                <?php foreach ($gallery_items as $item): ?>
                    <?php 
                        $isAlbumHidden = ($item['status'] === 'hidden');
                        $isPhotoHidden = ($item['photo_status'] === 'hidden');
                        $isHidden = $isAlbumHidden || $isPhotoHidden;
                        $photoCurrentStatus = $isPhotoHidden ? 'hidden' : 'visible';
                        $upload_date = date("d M Y", strtotime($item['uploaded_at']));
                    ?>
                    <div class="col-xl-3 col-lg-4 col-md-6 mb-4 gallery-item-wrap" 
                         data-album="<?= strtolower(htmlspecialchars($item['album_title'])) ?>" 
                         data-type="<?= strtolower(htmlspecialchars($item['type'])) ?>" 
                         data-caption="<?= strtolower(htmlspecialchars($item['caption'])) ?>"
                         data-date="<?= strtolower($upload_date) ?>">
                        
                        <div class="gallery-card">
                            <input type="checkbox" name="photo_ids[]" value="<?= $item['id'] ?>" class="form-check-input bulk-checkbox photo-cb">
                            
                            <?php if($isHidden): ?>
                                <span class="badge bg-danger hidden-badge shadow">HIDDEN</span>
                            <?php endif; ?>
                            
                            <img src="<?= htmlspecialchars($item['image_path']) ?>" 
                                 class="gallery-img <?= $isHidden ? 'hidden-img' : '' ?>" 
                                 style="cursor: zoom-in;" 
                                 onclick="openLightbox('<?= htmlspecialchars($item['image_path']) ?>', '<?= htmlspecialchars(addslashes($item['caption'])) ?>')"
                                 title="Click to view full screen">
                            
                            <div class="action-group">
                                <a href="admin_gallery.php?toggle_photo=<?= $item['id'] ?>&current_status=<?= $photoCurrentStatus ?>&filter_album=<?= $active_filter_id ?>" class="action-btn action-btn-eye" 
                                    style="color: <?= $isPhotoHidden ? '#198754' : '#6c757d' ?>;" title="<?= $isPhotoHidden ? 'Show Picture' : 'Hide Picture' ?>">
                                    <i class="<?= $isPhotoHidden ? 'fa-solid fa-eye' : 'fa-solid fa-eye-slash' ?>"></i>
                                </a>
                                <button type="button" class="action-btn action-btn-edit" 
                                        onclick="openPhotoEditModal(<?= $item['id'] ?>, <?= $item['album_id'] ?>, '<?= htmlspecialchars(addslashes($item['caption'])) ?>')" title="Edit Details">
                                    <i class="fa-solid fa-pen"></i>
                                </button>
                                <a href="admin_gallery.php?delete_id=<?= $item['id'] ?>&filter_album=<?= $active_filter_id ?>" class="action-btn action-btn-delete" 
                                    onclick="return confirm('Permanently delete this photo?');" title="Delete">
                                    <i class="fa-regular fa-trash-can"></i>
                                </a>
                            </div>
                            <div class="gallery-overlay">
                                <div class="d-flex justify-content-between align-items-center mb-1">
                                    <span class="badge bg-white text-dark rounded-1"><i class="fa-solid fa-folder me-1 text-success"></i> <?= htmlspecialchars($item['album_title']) ?></span>
                                    <small class="text-white-50 fw-bold" style="font-size: 0.7rem;"><i class="fa-regular fa-calendar me-1"></i><?= $upload_date ?></small>
                                </div>
                                <p class="small mb-0 text-truncate text-white" title="<?= htmlspecialchars($item['caption']) ?>">
                                    <?= htmlspecialchars($item['caption']) ?: 'No description' ?>
                                </p>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            <?php else: ?>
                <div class="col-12 text-center text-muted py-5">
                    <i class="fa-regular fa-folder-open fa-4x mb-3 text-light"></i>
                    <h5 class="text-secondary">No photos found in this album</h5>
                    <p class="small">Upload some pictures to get started.</p>
                </div>
            <?php endif; ?>
        </div>
    </form>
</div>

<!-- Modals -->
<div class="modal fade" id="lightboxModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-xl">
        <div class="modal-content bg-transparent border-0 shadow-none">
            <div class="modal-body text-center position-relative p-0">
                <button type="button" class="btn-close btn-close-white position-absolute top-0 end-0 m-3" data-bs-dismiss="modal" style="z-index: 1050; opacity: 0.8;"></button>
                <img id="lightboxImage" src="" class="img-fluid rounded-3 shadow-lg" style="max-height: 85vh; object-fit: contain;">
                <p id="lightboxCaption" class="text-white mt-3 fw-bold fs-5" style="text-shadow: 0 2px 4px rgba(0,0,0,0.8);"></p>
            </div>
        </div>
    </div>
</div>

<div class="modal fade" id="editAlbumModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow-lg" style="border-radius: 16px;">
            <div class="modal-header border-bottom-0 pb-0">
                <h5 class="modal-title fw-bold text-dark"><i class="fa-solid fa-pen-to-square text-primary me-2"></i>Edit Album Name</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form method="POST">
                <div class="modal-body pt-3 pb-4">
                    <input type="hidden" name="edit_album_id" id="edit_album_id">
                    <label class="form-label fw-bold text-muted small">Album Title</label>
                    <input type="text" name="new_album_title" id="new_album_title" class="form-control bg-light" required>
                </div>
                <div class="modal-footer border-top-0 pt-0">
                    <button type="button" class="btn btn-light" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" name="edit_album" class="btn btn-primary px-4 fw-bold shadow-sm">Save Changes</button>
                </div>
            </form>
        </div>
    </div>
</div>

<div class="modal fade" id="editPhotoModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow-lg" style="border-radius: 16px;">
            <div class="modal-header border-bottom-0 pb-0">
                <h5 class="modal-title fw-bold text-dark"><i class="fa-solid fa-image text-primary me-2"></i>Edit Photo Details</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form method="POST" action="admin_gallery.php?filter_album=<?= $active_filter_id ?>">
                <div class="modal-body pt-3 pb-4">
                    <input type="hidden" name="photo_id" id="edit_photo_id">
                    
                    <div class="mb-3">
                        <label class="form-label fw-bold text-muted small">Move to Album</label>
                        <select name="new_album_id" id="edit_photo_album" class="form-select bg-light" required>
                            <?php foreach ($albums as $a): ?>
                                <option value="<?= $a['id'] ?>"><?= htmlspecialchars($a['title']) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    
                    <div>
                        <label class="form-label fw-bold text-muted small">Update Caption</label>
                        <textarea name="new_caption" id="edit_photo_caption" class="form-control bg-light" rows="3" placeholder="Enter a new description..."></textarea>
                    </div>
                </div>
                <div class="modal-footer border-top-0 pt-0">
                    <button type="button" class="btn btn-light" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" name="edit_photo" class="btn btn-primary px-4 fw-bold shadow-sm">Update Photo</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- JS Scripts -->
<script>
function toggleDateFields() {
    let typeSelect = document.getElementById("albumTypeSelect").value;
    let galleryField = document.getElementById("galleryDateField");
    let promoFields = document.getElementById("promoDateFields");
    if (typeSelect === "Promotion") {
        galleryField.style.display = "none";
        promoFields.style.display = "block";
    } else {
        galleryField.style.display = "block";
        promoFields.style.display = "none";
    }
}

function openLightbox(imageSrc, caption) {
    document.getElementById('lightboxImage').src = imageSrc;
    document.getElementById('lightboxCaption').innerText = caption ? caption : 'No description available';
    new bootstrap.Modal(document.getElementById('lightboxModal')).show();
}

function openEditModal(id, title) {
    document.getElementById('edit_album_id').value = id;
    document.getElementById('new_album_title').value = title;
    new bootstrap.Modal(document.getElementById('editAlbumModal')).show();
}

function openPhotoEditModal(photo_id, album_id, caption) {
    document.getElementById('edit_photo_id').value = photo_id;
    document.getElementById('edit_photo_album').value = album_id;
    document.getElementById('edit_photo_caption').value = caption;
    new bootstrap.Modal(document.getElementById('editPhotoModal')).show();
}

document.addEventListener('DOMContentLoaded', function() {
    toggleDateFields();
    
    const selectAllCb = document.getElementById('selectAllCheckbox');
    const photoCbs = document.querySelectorAll('.photo-cb');
    const bulkBtn = document.getElementById('bulkDeleteBtn');
    const selectedCountSpan = document.getElementById('selectedCount');
    
    function updateBulkButton() {
        let checkedCount = 0;
        photoCbs.forEach(cb => { 
            if(cb.checked && cb.closest('.gallery-item-wrap').style.display !== 'none') {
                checkedCount++; 
            }
        });
        
        if(checkedCount > 0) {
            bulkBtn.style.display = 'inline-block';
            selectedCountSpan.innerText = checkedCount;
        } else {
            bulkBtn.style.display = 'none';
        }
    }
    
    if(selectAllCb) {
        selectAllCb.addEventListener('change', function() {
            photoCbs.forEach(cb => {
                if(cb.closest('.gallery-item-wrap').style.display !== 'none') {
                    cb.checked = this.checked;
                }
            });
            updateBulkButton();
        });
    }
    
    photoCbs.forEach(cb => {
        cb.addEventListener('change', function() {
            if(!this.checked && selectAllCb) selectAllCb.checked = false;
            updateBulkButton();
        });
    });

    const searchInput = document.getElementById('instantSearch');
    const filterDropdown = document.getElementById('instantDropdown');
    const typeDropdown = document.getElementById('instantTypeDropdown');
    const galleryItems = document.querySelectorAll('.gallery-item-wrap');
    
    function filterAlbumOptions() {
        if (!typeDropdown || !filterDropdown) return;
        
        const selectedType = typeDropdown.value.toLowerCase();
        const albumOptions = filterDropdown.querySelectorAll('option:not([value=""])');
        
        filterDropdown.value = ""; 
        
        albumOptions.forEach(option => {
            const optionType = option.getAttribute('data-type');
            if (selectedType === "" || optionType === selectedType) {
                option.style.display = 'block';
                option.hidden = false;
            } else {
                option.style.display = 'none';
                option.hidden = true;
            }
        });
        
        filterGallery();
    }
    
    function filterGallery() {
        if(!searchInput || !filterDropdown) return;
        
        const searchText = searchInput.value.toLowerCase();
        const categoryFilter = filterDropdown.value.toLowerCase();
        const typeFilter = typeDropdown ? typeDropdown.value.toLowerCase() : "";
        
        galleryItems.forEach(item => {
            const albumName = item.getAttribute('data-album');
            const typeText = item.getAttribute('data-type');
            const captionText = item.getAttribute('data-caption');
            const dateText = item.getAttribute('data-date');
            
            const matchesCategory = (categoryFilter === "" || albumName === categoryFilter);
            const matchesType = (typeFilter === "" || typeText === typeFilter);
            const matchesSearch = (searchText === "" || 
                                     albumName.includes(searchText) || 
                                     captionText.includes(searchText) || 
                                     dateText.includes(searchText));
            
            if (matchesCategory && matchesSearch && matchesType) {
                item.style.display = 'block'; 
            } else {
                item.style.display = 'none'; 
                const cb = item.querySelector('.photo-cb');
                if(cb) cb.checked = false; 
            }
        });
        
        if(selectAllCb) selectAllCb.checked = false;
        updateBulkButton();
    }
    
    if(searchInput) searchInput.addEventListener('input', filterGallery);
    if(filterDropdown) filterDropdown.addEventListener('change', filterGallery);
    if(typeDropdown) typeDropdown.addEventListener('change', filterAlbumOptions);
});
</script>

<?php include 'footer.php'; ?>