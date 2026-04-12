<?php
// api/ack_alerts.php
session_start();
require_once __DIR__ . '/../utils/auth.php';
requireLawyer();
require __DIR__ . '/../config/db.php';
header('Content-Type: application/json');

$lawyerId = (int)$_SESSION['lawyer_id'];

// Expect JSON: { "ticket_ids": [1,2,3], "alert_type": "pre_overdue_24h" }
$raw = file_get_contents('php://input');
$body = json_decode($raw, true);

$ids = array_map('intval', $body['ticket_ids'] ?? []);
$alertType = $body['alert_type'] ?? 'pre_overdue_24h';

if (!$ids) { echo json_encode(['ok'=>true]); exit; }

$now = date('Y-m-d H:i:s');
$stmt = $conn->prepare("INSERT IGNORE INTO ticket_alert_ack(ticket_id, lawyer_id, alert_type, acknowledged_at) VALUES (?, ?, ?, ?)");
foreach ($ids as $tid) {
  $stmt->bind_param('iiss', $tid, $lawyerId, $alertType, $now);
  $stmt->execute();
}
$stmt->close();

echo json_encode(['ok' => true]);
