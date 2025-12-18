<?php
// api/due_soon.php
session_start();
header("Content-Type: application/json");
ob_clean();

if (empty($_SESSION['lawyer_id'])) {
  http_response_code(401);
  echo json_encode(['error'=>'unauthenticated']); 
  exit;
}

require __DIR__ . '/../config/db.php';

$lawyerId = (int)$_SESSION['lawyer_id'];

// Using DATE only
$sql = "
  SELECT 
    t.id,
    t.ticket_code,
    t.full_name,
    t.email,
    t.contract_type,
    t.priority,
    t.due_date AS due_text,
    t.status,

    l.name AS reviewer_name

  FROM tickets t

  LEFT JOIN lawyers l
    ON l.email = t.assigned_lawyer

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
$stmt->execute();
$res = $stmt->get_result();

$data = [];
while ($r = $res->fetch_assoc()) {
  $data[] = $r;
}
$stmt->close();

/* --------------------------------------------------------
   🔥 TEST MODE (FORCE MODAL DATA)
   Enable this block to ALWAYS trigger the modal
--------------------------------------------------------- */

/* --------------------------------------------------------
   NORMAL MODE — Return real DB data
--------------------------------------------------------- */

echo json_encode(['items' => $data]);
exit;
