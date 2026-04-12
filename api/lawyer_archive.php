<?php
session_start();
require_once __DIR__ . '/../utils/auth.php';
requireAdmin();
require __DIR__ . '/../config/db.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    exit('Method Not Allowed');
}

if (!isset($_POST['id'])) {
    http_response_code(400);
    exit('Missing id');
}

$id = (int)$_POST['id'];

// block archiving admins (recommended)
$stmt = $conn->prepare("UPDATE lawyers
                        SET archived_at = NOW(), archived_by = ?
                        WHERE id = ? AND role <> 'admin' AND archived_at IS NULL");
$archiverId = (int)($_SESSION['lawyer_id'] ?? 0); // if you have it; else 0 is fine
$stmt->bind_param("ii", $archiverId, $id);
$stmt->execute();

header("Location: ../manage_lawyers.php?ok=" . urlencode("Account archived"));
exit;
