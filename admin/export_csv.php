<?php
// admin/export_csv.php
require_once __DIR__ . '/../config.php';
if (!isset($conn) || !$conn) {
    die('DB connection not available.');
}
header('Content-Type: text/csv; charset=utf-8');
header('Content-Disposition: attachment; filename=feedback_export_' . date('Ymd_His') . '.csv');

$output = fopen('php://output', 'w');
fputcsv($output, ['id','student_name','course','subject','comment','sentiment','created_at']);

$res = $conn->query("SELECT id, student_name, course, subject, comment, sentiment, created_at FROM feedback ORDER BY id DESC");
if ($res) {
    while ($row = $res->fetch_assoc()) {
        fputcsv($output, $row);
    }
}
fclose($output);
exit;
