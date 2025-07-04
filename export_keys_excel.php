<?php
include "config.php";

// Set headers to force download as Excel CSV
header('Content-Type: text/csv; charset=utf-8');
header('Content-Disposition: attachment; filename=class_secret_keys.csv');

// Open output stream
$output = fopen('php://output', 'w');

// Output the column headings
fputcsv($output, ['Class Name', 'Secret Key']);

// Fetch all class keys with class names
$sql = "SELECT c.name AS class_name, ck.secret_key 
        FROM class_keys ck
        JOIN class c ON ck.class_id = c.id
        ORDER BY c.id DESC";

$result = $conn->query($sql);

if ($result && $result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        fputcsv($output, [$row['class_name'], $row['secret_key']]);
    }
} else {
    fputcsv($output, ['No data found']);
}

fclose($output);
exit;
