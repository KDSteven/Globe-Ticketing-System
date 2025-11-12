<?php
// api/due_soon.php
session_start();
header('Content-Type: application/json');

if (empty($_SESSION['lawyer_id'])) {
  http_response_code(401);
  echo json_encode(['error'=>'unauthenticated']); exit;
}

require __DIR__ . '/../config/db.php';

$lawyerId = (int)$_SESSION['lawyer_id'];

// If you only store DATE in due_date, use DATEDIFF = 1 (calendar-day based).
// For hour-accurate alerts, switch to DATETIME (due_at) and TIMESTAMPDIFF.
$useDateOnly = true;

if ($useDateOnly) {
  $sql = "
    SELECT t.id, t.ticket_code, t.full_name, t.email, t.contract_type,
           t.priority, t.due_date AS due_text, t.status
    FROM tickets t
    LEFT JOIN ticket_alert_ack a
      ON a.ticket_id = t.id
     AND a.lawyer_id = ?
     AND a.alert_type = 'pre_overdue_24h'
    WHERE t.status <> 'Completed'
      AND t.due_date IS NOT NULL
      AND DATEDIFF(t.due_date, CURDATE()) = 1
      AND a.ticket_id IS NULL
    ORDER BY t.due_date ASC
    LIMIT 50
  ";
  $stmt = $conn->prepare($sql);
  $stmt->bind_param('i', $lawyerId);
} else {
  $sql = "
    SELECT t.id, t.ticket_code, t.full_name, t.email, t.contract_type,
           t.priority, DATE_FORMAT(t.due_at, '%Y-%m-%d %H:%i') AS due_text, t.status
    FROM tickets t
    LEFT JOIN ticket_alert_ack a
      ON a.ticket_id = t.id
     AND a.lawyer_id = ?
     AND a.alert_type = 'pre_overdue_24h'
    WHERE t.status <> 'Completed'
      AND t.due_at IS NOT NULL
      AND TIMESTAMPDIFF(HOUR, NOW(), t.due_at) BETWEEN 1 AND 24
      AND a.ticket_id IS NULL
    ORDER BY t.due_at ASC
    LIMIT 50
  ";
  $stmt = $conn->prepare($sql);
  $stmt->bind_param('i', $lawyerId);
}

$stmt->execute();
$res = $stmt->get_result();
$data = [];
while ($r = $res->fetch_assoc()) $data[] = $r;
$stmt->close();

echo json_encode(['items' => $data]);
