<?php
session_start();
require_once __DIR__ . '/../utils/auth.php';
requireAdmin();
require __DIR__ . '/../config/db.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    exit('Method Not Allowed');
}

$id = (int)($_POST['id'] ?? 0);

// only allow deleting archived non-admin accounts
$stmt = $conn->prepare("DELETE FROM lawyers
                        WHERE id = ? AND role <> 'admin' AND archived_at IS NOT NULL");
$stmt->bind_param("i", $id);
$stmt->execute();

header("Location: ../manage_lawyers.php?view=archived&ok=Account permanently deleted");
exit;
