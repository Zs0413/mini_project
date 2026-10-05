<?php
// Start session and connect database
session_start();
include 'db.php';

// Stop normal users
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header("Location: login.php");
    exit();
}

// 1. Generate CSRF Token for security
if (empty($_SESSION['csrf_token'])) {$_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

// 2. Handle SINGLE Soft Delete via Secure POST
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['delete_user_id'])) {
    if (!hash_equals($_SESSION['csrf_token'], $_POST['csrf_token'])) {$_SESSION['error_msg'] = "Security validation failed! Request denied.";
    } else {
        $delete_id = (int)$_POST['delete_user_id'];
        
        $stmt =$conn->prepare("UPDATE users SET role = 'deleted', password = 'DELETED_ACCOUNT' WHERE id = ? AND role != 'admin'");
        $stmt->bind_param("i", $delete_id);
        
        if ($stmt->execute()) {$_SESSION['success_msg'] = "Customer account safely removed. Order history is preserved.";
        } else {
            $_SESSION['error_msg'] = "Failed to remove customer account.";
        }
        $stmt->close();
    }
    header("Location: admin_users.php");
    exit();
}

// 3. Handle BULK Soft Delete via Secure POST
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['bulk_delete_ids'])) {
    if (!hash_equals($_SESSION['csrf_token'], $_POST['csrf_token'])) {$_SESSION['error_msg'] = "Security validation failed! Request denied.";
    } else {
        $ids = explode(',',$_POST['bulk_delete_ids']);
        $ids = array_map('intval',$ids);
        $ids = array_filter($ids); 
        if (!empty($ids)) {$placeholders = implode(',', array_fill(0, count($ids), '?'));$types = str_repeat('i', count($ids));$sql = "UPDATE users SET role = 'deleted', password = 'DELETED_ACCOUNT' WHERE id IN ($placeholders) AND role != 'admin'";
            $stmt =$conn->prepare($sql);$stmt->bind_param($types, ...$ids);
            
            if ($stmt->execute()) {
                $_SESSION['success_msg'] = count($ids) . " selected customer accounts were safely removed.";
            } else {
                $_SESSION['error_msg'] = "Failed to remove selected accounts.";
            }
            $stmt->close();
        }
    }
    header("Location: admin_users.php");
    exit();
}

include 'header.php';
include 'admin_sidebar.php';
?>

<!-- Custom UI Styles -->
<style>
    .avatar-circle {
        width: 40px; height: 40px; border-radius: 50%;
        background-color: rgba(25, 135, 84, 0.1); color: #198754;
        display: flex; align-items: center; justify-content: center; font-weight: 800; font-size: 1.2rem;
    }
    .btn-icon {
        width: 35px; height: 35px; border-radius: 8px; display: inline-flex; align-items: center;
        justify-content: center; padding: 0; transition: all 0.2s ease; border: none; background: rgba(220, 53, 69, 0.05);
    }
    .btn-icon:hover {
        background: #dc3545; color: white !important; transform: translateY(-2px); box-shadow: 0 4px 10px rgba(220, 53, 69, 0.2);
    }
    .search-wrapper {
        border-radius: 30px; overflow: hidden; box-shadow: 0 4px 15px rgba(0,0,0,0.03);
        border: 1px solid rgba(25, 135, 84, 0.2); background: #fff;
    }
    .search-wrapper input:focus { box-shadow: none; }
    
    /* Animation for newly appended rows */
    @keyframes fadeIn {
        from { opacity: 0; transform: translateY(10px); }
        to { opacity: 1; transform: translateY(0); }
    }
    .animated-row { animation: fadeIn 0.4s ease forwards; }
</style>

<div class="main-content">
    
    <?php if (isset($_SESSION['success_msg'])): ?>
        <div class="alert alert-success alert-dismissible fade show shadow-sm border-0 rounded-4" role="alert">
            <i class="fa-solid fa-check-circle me-2"></i> <?= htmlspecialchars($_SESSION['success_msg']) ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
        <?php unset($_SESSION['success_msg']); ?>
    <?php endif; ?>

    <?php if (isset($_SESSION['error_msg'])): ?>
        <div class="alert alert-danger alert-dismissible fade show shadow-sm border-0 rounded-4" role="alert">
            <i class="fa-solid fa-triangle-exclamation me-2"></i> <?= htmlspecialchars($_SESSION['error_msg']) ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
        <?php unset($_SESSION['error_msg']); ?>
    <?php endif; ?>

    <div class="d-flex justify-content-between align-items-center mb-5">
        <div>
            <h2 class="fw-bold text-dark mb-1">User Management</h2>
            <p class="text-muted mb-0">Search, manage, and bulk remove customer accounts.</p>
        </div>
    </div>

    <div class="card border-0 shadow-sm overflow-hidden" style="border-radius: 16px;">
        
        <div class="card-header bg-white p-4 border-bottom-0 d-flex justify-content-between align-items-center gap-3">
            <div class="input-group input-group-lg search-wrapper w-100">
                <span class="input-group-text bg-transparent border-0 text-success ps-4">
                    <i class="fa-solid fa-magnifying-glass"></i>
                </span>
                <input type="text" id="liveSearch" class="form-control border-0 bg-transparent" placeholder="Type name or username to search instantly...">
            </div>
            
            <button class="btn btn-danger btn-lg text-nowrap shadow-sm fw-bold" id="bulkDeleteBtn" style="border-radius: 30px; display: none;">
                <i class="fa-solid fa-trash-can me-2"></i> <span id="bulkDeleteText">Delete</span>
            </button>
        </div>

        <div class="card-body p-0">
            <table class="table table-hover align-middle mb-0 text-center">
                <thead class="bg-light text-muted small text-uppercase" style="position: sticky; top: 0; z-index: 10;">
                    <tr>
                        <th class="py-3 px-4 text-center" style="width: 60px;">
                            <input class="form-check-input border-secondary shadow-sm" type="checkbox" id="selectAll">
                        </th>
                        <th class="py-3 text-center">ID.</th>
                        <th class="py-3 text-start ps-4">Customer Details</th>
                        <th class="py-3 text-center">Username</th>
                        <th class="py-3 text-center">Phone</th>
                        <th class="py-3 text-center">Joined Date</th>
                        <th class="py-3 text-center text-danger">Action</th>
                    </tr>
                </thead>
                <tbody id="userTableBody" class="bg-white">
                    <!-- Data injected by AJAX -->
                </tbody>
            </table>
        </div>
        
        <!-- Infinite Scroll Loading Indicator -->
        <div class="card-footer bg-white border-top-0 py-4 text-center" id="loadingIndicator" style="display: none;">
            <span class="text-success fw-bold">
                <i class="fa-solid fa-circle-notch fa-spin me-2"></i> Loading more users...
            </span>
        </div>
    </div>
</div>

<form id="deleteForm" method="POST" style="display: none;">
    <input type="hidden" name="csrf_token" value="<?= $_SESSION['csrf_token'] ?>">
    <input type="hidden" name="delete_user_id" id="deleteUserId">
</form>

<form id="bulkDeleteForm" method="POST" style="display: none;">
    <input type="hidden" name="csrf_token" value="<?= $_SESSION['csrf_token'] ?>">
    <input type="hidden" name="bulk_delete_ids" id="bulkDeleteIds">
</form>

<script>
document.addEventListener("DOMContentLoaded", function() {
    
    let currentQuery = '';
    let currentPage = 1;
    let isLoading = false;
    let hasMore = true; 
    
    function fetchUsers(query, page = 1, append = false) {
        if (isLoading || (!hasMore && append)) return;
        
        isLoading = true;
        
        if (append) {
            document.getElementById('loadingIndicator').style.display = 'block';
        }

        let formData = new FormData();
        formData.append('query', query);
        formData.append('page', page);
        
        fetch('ajax_search_users.php', { method: 'POST', body: formData })
        .then(response => response.json())
        .then(data => {
            const tbody = document.getElementById('userTableBody');
            
            if (append) {
                tbody.insertAdjacentHTML('beforeend', data.html);
            } else {
                tbody.innerHTML = data.html;
                document.getElementById('selectAll').checked = false;
            }
            
            hasMore = data.has_more;
            currentPage = page;
            isLoading = false;
            document.getElementById('loadingIndicator').style.display = 'none';
            
            toggleBulkDeleteBtn();
        })
        .catch(() => {
            isLoading = false;
            document.getElementById('loadingIndicator').style.display = 'none';
        });
    }
    
    fetchUsers('', 1, false);

    let searchTimer;
    document.getElementById('liveSearch').addEventListener('keyup', function() {
        clearTimeout(searchTimer);
        currentQuery = this.value;
        searchTimer = setTimeout(function() {
            hasMore = true;
            fetchUsers(currentQuery, 1, false);
        }, 400); 
    });

    window.addEventListener('scroll', function() {
        if ((window.innerHeight + window.scrollY) >= document.body.offsetHeight - 200) {
            if (!isLoading && hasMore) {
                fetchUsers(currentQuery, currentPage + 1, true);
            }
        }
    });

    document.getElementById('selectAll').addEventListener('change', function() {
        let checkboxes = document.querySelectorAll('.user-checkbox');
        checkboxes.forEach(cb => cb.checked = this.checked);
        toggleBulkDeleteBtn();
    });

    document.getElementById('userTableBody').addEventListener('change', function(e) {
        if (e.target && e.target.classList.contains('user-checkbox')) {
            toggleBulkDeleteBtn();
            let totalBoxes = document.querySelectorAll('.user-checkbox').length;
            let checkedBoxes = document.querySelectorAll('.user-checkbox:checked').length;
            document.getElementById('selectAll').checked = (totalBoxes === checkedBoxes && totalBoxes > 0);
        }
    });

    function toggleBulkDeleteBtn() {
        let checkedCount = document.querySelectorAll('.user-checkbox:checked').length;
        let btn = document.getElementById('bulkDeleteBtn');
        let btnText = document.getElementById('bulkDeleteText');
        
        if (checkedCount > 0) {
            btn.style.display = 'block';
            btnText.innerText = `Delete (${checkedCount})`;
        } else {
            btn.style.display = 'none';
        }
    }

    document.getElementById('bulkDeleteBtn').addEventListener('click', function() {
        let checkedBoxes = document.querySelectorAll('.user-checkbox:checked');
        let ids = Array.from(checkedBoxes).map(cb => cb.value).join(',');
        
        if (confirm(`WARNING: Are you sure you want to completely remove the ${checkedBoxes.length} selected customers?\n\nTheir past booking records will be safely preserved for revenue calculations.`)) {
            document.getElementById('bulkDeleteIds').value = ids;
            document.getElementById('bulkDeleteForm').submit();
        }
    });
});

function triggerDelete(id, name) {
    if (confirm(`WARNING: Are you sure you want to remove customer '${name}'?`)) {
        document.getElementById('deleteUserId').value = id;
        document.getElementById('deleteForm').submit();
    }
}
</script>

<?php include 'footer.php'; ?>