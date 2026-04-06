<?php
require __DIR__ . '/../config/db.php';
session_start();

if ($_SESSION['lawyer_role'] !== 'admin') {
    http_response_code(403);
    echo json_encode(['error' => 'Forbidden']);
    exit;
}

$date = $_POST['date'] ?? null;
$description = $_POST['description'] ?? null;

if (!$date || !$description) {
    die("Missing fields");
}

$stmt = $conn->prepare("INSERT IGNORE INTO holidays (date, description) VALUES (?, ?)");
$stmt->bind_param("ss", $date, $description);   
$stmt->execute();
$stmt->close();

$ref = strtok($_SERVER['HTTP_REFERER'], '?'); // remove old query string
header("Location: $ref?ok=Holiday added successfully!");
exit;
?>
