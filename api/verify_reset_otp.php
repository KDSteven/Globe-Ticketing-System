<?php
declare(strict_types=1);
header('Content-Type: application/json');

require_once __DIR__ . '/../config/db.php';

try {
  $data = json_decode(file_get_contents('php://input'), true, flags: JSON_THROW_ON_ERROR);
  $email = trim($data['email'] ?? '');
  $otp   = trim($data['otp']   ?? '');

  if ($email === '' || !preg_match('/^\d{6}$/', $otp)) {
    echo json_encode(['ok' => false, 'error' => 'Invalid input']); exit;
  }

  // Fetch latest reset row
  $stmt = $conn->prepare('SELECT otp_hash, expires_at, attempts FROM password_resets WHERE email=? LIMIT 1');
  $stmt->bind_param('s', $email);
  $stmt->execute();
  $row = $stmt->get_result()->fetch_assoc();
  $stmt->close();

  if (!$row) {
    echo json_encode(['ok' => false, 'error' => 'No OTP sent for this email']); exit;
  }

  // Expired?
  if (strtotime($row['expires_at']) < time()) {
    echo json_encode(['ok' => false, 'error' => 'OTP expired']); exit;
  }

  // Lock after too many tries (e.g., 5)
  if ((int)$row['attempts'] >= 5) {
    echo json_encode(['ok' => false, 'error' => 'Too many attempts. Please resend OTP.']); exit;
  }

  $match = password_verify($otp, $row['otp_hash'] ?? '');

  if (!$match) {
    // increment attempts
    $stmt = $conn->prepare('UPDATE password_resets SET attempts = attempts + 1 WHERE email=? LIMIT 1');
    $stmt->bind_param('s', $email);
    $stmt->execute();
    $stmt->close();
    echo json_encode(['ok' => false, 'error' => 'Incorrect code']); exit;
  }

  // Mark as verified (optional but nice for step 2)
  $stmt = $conn->prepare('UPDATE password_resets SET verified_at = NOW() WHERE email=? LIMIT 1');
  $stmt->bind_param('s', $email);
  $stmt->execute();
  $stmt->close();

  echo json_encode(['ok' => true]); exit;

} catch (Throwable $e) {
  echo json_encode(['ok' => false, 'error' => 'Server error: '.$e->getMessage()]); exit;
}
