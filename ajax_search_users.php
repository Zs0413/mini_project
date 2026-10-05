<?php
// Start session and connect database
session_start();
include 'db.php';

// Check admin authorization
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    echo json_encode(['html' => '<tr><td colspan="7">Unauthorized</td></tr>', 'has_more' => false]);
    exit();
}

// Get search parameters
$search = isset($_POST['query']) ? trim($_POST['query']) : '';
$searchTerm = "%" . $search . "%";

// Set pagination limit to 8 records per page
$limit = 8; 
$page = isset($_POST['page']) ? (int)$_POST['page'] : 1;
if ($page < 1) $page = 1;
$offset = ($page - 1) * $limit;

// Calculate total pages for customer accounts
$count_stmt = $conn->prepare("SELECT COUNT(id) as total FROM users WHERE role = 'customer' AND (fullname LIKE ? OR username LIKE ?)");
$count_stmt->bind_param("ss", $searchTerm, $searchTerm);
$count_stmt->execute();
$total_users = $count_stmt->get_result()->fetch_assoc()['total'];
$total_pages = ceil($total_users / $limit);
$count_stmt->close();

// Fetch users with pagination limit
$stmt = $conn->prepare("SELECT * FROM users WHERE role = 'customer' AND (fullname LIKE ? OR username LIKE ?) ORDER BY regdate DESC LIMIT ? OFFSET ?");
$stmt->bind_param("ssii", $searchTerm, $searchTerm, $limit, $offset);
$stmt->execute();
$users = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
$stmt->close();

// Generate HTML table rows
$html = "";
if (count($users) > 0) {
    $counter = $offset + 1;
    foreach ($users as $u) {
        $initial = strtoupper(substr($u['fullname'], 0, 1));
        $reg_date = date("d M Y", strtotime($u['regdate']));
        $safe_name = htmlspecialchars(addslashes($u['fullname']));
        
        $html .= "<tr class='align-middle text-center animated-row'>";
        $html .= "<td><input class='form-check-input border-secondary shadow-sm user-checkbox' type='checkbox' value='" . $u['id'] . "'></td>";
        $html .= "<td><span class='text-muted fw-bold'>" . $counter++ . "</span></td>";
        
        $html .= "<td class='text-start ps-4'>
                    <div class='d-flex align-items-center'>
                        <div class='avatar-circle me-3'>{$initial}</div>
                        <div>
                            <div class='fw-bold text-dark'>" . htmlspecialchars($u['fullname']) . "</div>
                            <div class='text-muted small'>" . htmlspecialchars($u['email']) . "</div>
                        </div>
                    </div>
                  </td>";
                  
        $html .= "<td><span class='badge bg-light text-dark border px-2 py-1'>@" . htmlspecialchars($u['username']) . "</span></td>";
        $html .= "<td>" . htmlspecialchars($u['phone']) . "</td>";
        $html .= "<td><span class='text-muted'>{$reg_date}</span></td>";
        $html .= "<td>
                    <button type='button' class='btn btn-icon btn-outline-danger shadow-none' onclick=\"triggerDelete({$u['id']}, '{$safe_name}')\" title='Delete Account'>
                        <i class='fa-regular fa-trash-can'></i>
                    </button>
                  </td>";
        $html .= "</tr>";
    }
} else {
    // Display empty state message on the first page
    if ($page == 1) {
        $html .= "<tr>
                    <td colspan='7' class='py-5 text-center text-muted'>
                        <i class='fa-solid fa-user-slash fa-3x mb-3 text-light'></i>
                        <h5>No accounts found</h5>
                        <p class='small'>Try searching with a different keyword.</p>
                    </td>
                  </tr>";
    }
}

// Check if more pages exist
$has_more = ($page < $total_pages);

// Return JSON response
header('Content-Type: application/json');
echo json_encode([
    'html' => $html,
    'has_more' => $has_more
]);

$conn->close();
?>