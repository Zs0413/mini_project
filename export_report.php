<?php
// Start session and connect database
session_start();
include 'db.php';

// Security check: Only admins can export data
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    die("Access Denied. You do not have permission to view this file.");
}

// 1. Set headers to force the browser to download the file as a CSV
$filename = "SplashMania_Financial_Report_" . date('Y-m-d_Hi') . ".csv";
header('Content-Type: text/csv; charset=utf-8');
header('Content-Disposition: attachment; filename="' . $filename . '"');
header('Pragma: no-cache');
header('Expires: 0');

// 2. Open output stream directly to the browser
$output = fopen('php://output', 'w');

// Add UTF-8 BOM so Excel opens the file perfectly without character corruption
fputs($output, "\xEF\xBB\xBF");

// 3. Write the CSV Column Headers
fputcsv($output, [
    'Booking Ref', 
    'Customer Name', 
    'Phone Number', 
    'Travel Date', 
    'Transaction Time', 
    'Total Paid (RM)'
]);

// 4. Fetch all booking records from the database
$query = "
    SELECT 
        b.id, 
        u.fullname, 
        u.phone, 
        b.booking_date, 
        b.created_at, 
        b.total_price 
    FROM bookings b 
    JOIN users u ON b.user_id = u.id 
    ORDER BY b.created_at DESC
";
$result = $conn->query($query);

// 5. Loop through the data and write each row to the CSV
if ($result && $result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        
        $formatted_phone = "\t" . $row['phone'];
        
        $travel_date = date("d M Y", strtotime($row['booking_date']));
        $transaction_time = date("d M Y, H:i", strtotime($row['created_at']));
        
        fputcsv($output, [
            '#SM-' . str_pad($row['id'], 4, '0', STR_PAD_LEFT),
            htmlspecialchars($row['fullname']),
            $formatted_phone,
            $travel_date,
            $transaction_time,
            number_format($row['total_price'], 2)
        ]);
    }
} else {
    // If no data exists
    fputcsv($output, ['No records found matching the criteria.']);
}

// 6. Close the output stream and database connection
fclose($output);
$conn->close();
exit();
?>