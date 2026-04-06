<?php
require __DIR__ . '/../config/db.php';
session_start();
header('Content-Type: application/json');

if (!isset($_SESSION['lawyer_id'])) {
    echo json_encode(['ok' => false, 'error' => 'Unauthorized.']);
    exit;
}

$lawyerId = $_SESSION['lawyer_id'];

// Get JSON payload
$data = json_decode(file_get_contents('php://input'), true);

$current = trim($data['current'] ?? '');
$newpw   = trim($data['new'] ?? '');
$confirm = trim($data['confirm'] ?? '');

if ($current === '' || $newpw === '' || $confirm === '') {
    echo json_encode(['ok' => false, 'error' => 'All fields are required.']);
    exit;
}

if ($newpw !== $confirm) {
    echo json_encode(['ok' => false, 'error' => 'New passwords do not match.']);
    exit;
}

if (strlen($newpw) < 8 || !preg_match('/[A-Za-z]/', $newpw) || !preg_match('/[0-9]/', $newpw)) {
    echo json_encode(['ok' => false, 'error' => 'Password must be at least 8 characters with letters and numbers.']);
    exit;
}

// Fetch existing hash
$stmt = $conn->prepare("SELECT pass_hash FROM lawyers WHERE id=? LIMIT 1");
$stmt->bind_param("i", $lawyerId);
$stmt->execute();
$res = $stmt->get_result()->fetch_assoc();

if (!$res || !password_verify($current, $res['pass_hash'])) {
    echo json_encode(['ok' => false, 'error' => 'Current password is incorrect.']);
    exit;
}

// Save new password
$newHash = password_hash($newpw, PASSWORD_DEFAULT);

$update = $conn->prepare("UPDATE lawyers SET pass_hash=? WHERE id=?");
$update->bind_param("si", $newHash, $lawyerId);
$update->execute();

echo json_encode(['ok' => true, 'message' => 'Password updated successfully.']);
exit;
?>
