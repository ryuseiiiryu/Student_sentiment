<?php
// submit_feedback.php
require_once __DIR__ . '/config.php';
if (!isset($conn) || !$conn) {
    die('DB connection error');
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: frontend/feedback.html');
    exit;
}

// simple server-side validation
$course = trim($_POST['course'] ?? '');
$subject = trim($_POST['subject'] ?? '');
$comment = trim($_POST['comment'] ?? '');
$name = trim($_POST['name'] ?? 'Anonymous');

if ($course === '' || $subject === '' || $comment === '') {
    echo "Required fields are missing.";
    exit;
}

// default sentiment unknown — if you have ML endpoint, call it here
$sentiment = 'unknown'; // placeholder - later integrate ML

$stmt = $conn->prepare("INSERT INTO feedback (student_name, course, course_section, subject, comment, sentiment, created_at) VALUES (?, ?, ?, ?, ?, ?, NOW())");
$course_section = ''; // if you have separate field use it; else empty
$stmt->bind_param('ssssss', $name, $course, $course_section, $subject, $comment, $sentiment);
try {
    $stmt->execute();
    header('Location: thanks.php'); // or frontend/thankyou page
    exit;
} catch (Exception $e) {
    echo "DB error: " . $e->getMessage();
    exit;
}
