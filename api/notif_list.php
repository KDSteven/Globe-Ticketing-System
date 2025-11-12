<?php
// api/notif_list.php
session_start();
header('Content-Type: application/json');
if (empty($_SESSION['lawyer_id'])) { http_response_code(401); echo json_encode([]); exit; }
require __DIR__ . '/../config/db.php';

/*
  If you only have DATE in due_date:
    - Overdue: due_date < CURDATE() and not Completed
    - Due soon: DATEDIFF(due_date, CURDATE()) = 1
  (If you later add due_at DATETIME, adjust to TIMESTAMPDIFF for hour-precision.)
*/

$data = [];

/* Overdue first (most urgent) */
$over = $conn->query("
  SELECT id, ticket_code, full_name, due_date AS due_text, status
  FROM tickets
  WHERE status <> 'Completed'
    AND due_date IS NOT NULL
    AND due_date < CURDATE()
  ORDER BY due_date ASC
  LIMIT 200
");
while ($r = $over->fetch_assoc()) {
  $r['type'] = 'overdue';
  $data[] = $r;
}

/* Due within the next 24h (calendar-day based) */
$soon = $conn->query("
  SELECT id, ticket_code, full_name, due_date AS due_text, status
  FROM tickets
  WHERE status <> 'Completed'
    AND due_date IS NOT NULL
    AND DATEDIFF(due_date, CURDATE()) = 1
  ORDER BY due_date ASC
  LIMIT 200
");
while ($r = $soon->fetch_assoc()) {
  $r['type'] = 'due_soon';
  $data[] = $r;
}

echo json_encode(['items' => $data, 'count' => count($data)]);
