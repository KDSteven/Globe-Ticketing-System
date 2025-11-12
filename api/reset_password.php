<?php
declare(strict_types=1);
header('Content-Type: application/json');

require_once __DIR__ . '/../config/db.php'; // $conn = new mysqli(...)

try {
  $data  = json_decode(file_get_contents('php://input'), true, flags: JSON_THROW_ON_ERROR);
  $email = trim($data['email'] ?? '');
  $otp   = trim($data['otp'] ?? '');
  $new   = (string)($data['new_password'] ?? '');

  if ($email === '' || $otp === '' || $new === '') {
    echo json_encode(['ok' => false, 'error' => 'Missing fields']); exit;
  }
  if (strlen($new) < 8 || !preg_match('/[A-Za-z]/', $new) || !preg_match('/\d/', $new)) {
    echo json_encode(['ok' => false, 'error' => 'Weak password']); exit;
  }

  // 1) fetch OTP row
  $stmt = $conn->prepare('SELECT otp_hash, expires_at, attempts FROM password_resets WHERE email=? LIMIT 1');
  $stmt->bind_param('s', $email);
  $stmt->execute();
  $row = $stmt->get_result()->fetch_assoc();
  $stmt->close();

  if (!$row) { echo json_encode(['ok'=>false,'error'=>'No active OTP. Request a new one.']); exit; }
  if ((int)$row['attempts'] >= 5) { echo json_encode(['ok'=>false,'error'=>'Too many attempts. Request a new OTP.']); exit; }
  if (time() > strtotime($row['expires_at'])) { echo json_encode(['ok'=>false,'error'=>'OTP expired. Request a new one.']); exit; }

  if (!password_verify($otp, $row['otp_hash'])) {
    $stmt = $conn->prepare('UPDATE password_resets SET attempts=attempts+1 WHERE email=?');
    $stmt->bind_param('s', $email);
    $stmt->execute(); $stmt->close();
    echo json_encode(['ok'=>false,'error'=>'Invalid OTP']); exit;
  }

  // 2) update password and clear reset (transaction)
  $conn->begin_transaction();
  try {
    $newHash = password_hash($new, PASSWORD_BCRYPT);

    $stmt = $conn->prepare('UPDATE lawyers SET pass_hash=? WHERE email=?');
    $stmt->bind_param('ss', $newHash, $email);
    $stmt->execute(); $stmt->close();

    $stmt = $conn->prepare('DELETE FROM password_resets WHERE email=?');
    $stmt->bind_param('s', $email);
    $stmt->execute(); $stmt->close();

    $conn->commit();
    echo json_encode(['ok'=>true]); exit;
  } catch (Throwable $t) {
    $conn->rollback();
    throw $t;
  }
} catch (Throwable $e) {
  echo json_encode(['ok'=>false,'error'=>'Server error: '.$e->getMessage()]); exit;
}
