<?php
session_start();
require_once __DIR__ . '/../utils/auth.php';
requireAdmin();
require __DIR__ . '/../config/db.php';

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
