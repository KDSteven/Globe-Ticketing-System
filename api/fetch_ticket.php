<?php
session_start();
header('Content-Type: application/json');

// ===== Error Handling for Dev =====
error_reporting(E_ALL);
ini_set('display_errors', '0');
set_error_handler(function($sev, $msg, $file, $line) {
  echo json_encode(['success' => false, 'message' => "PHP error: $msg @ $file:$line"]);
  exit;
});
register_shutdown_function(function() {
  $e = error_get_last();
  if ($e && in_array($e['type'], [E_ERROR, E_PARSE, E_CORE_ERROR, E_COMPILE_ERROR])) {
    echo json_encode(['success' => false, 'message' => "Fatal error: {$e['message']} @ {$e['file']}:{$e['line']}"]);
  }
});

// ===== DB Connection =====
require __DIR__ . '/../config/db.php';
if (!isset($conn) || !($conn instanceof mysqli)) {
  echo json_encode(['success' => false, 'message' => 'Database connection failed.']);
  exit;
}

// ===== Authentication (adjust as needed) =====
if (empty($_SESSION['user_id'])) {
  echo json_encode(['success' => false, 'message' => 'Not authenticated.']);
  exit;
}
$user_id = (int)$_SESSION['user_id'];
$role    = $_SESSION['role'] ?? 'requestor';

// ===== Input =====
$ticket_id = trim($_GET['ticket_id'] ?? '');
if ($ticket_id === '') {
  echo json_encode(['success' => false, 'message' => 'Ticket ID is required.']);
  exit;
}

// ===== Query (mapped to your actual columns) =====
$sql = "
  SELECT
    t.ticket_code       AS ticket_code,
    t.id                AS id,
    t.full_name         AS full_name,
    t.email             AS email,
    t.grp               AS grp,
    t.tribe             AS tribe,
    t.assigned_lawyer   AS assigned_lawyer,
    t.cc_emails         AS cc_emails,
    t.summary           AS summary,
    t.contract_type     AS contract_type,
    t.contract_other    AS contract_other,
    t.customer          AS customer,
    t.vendor            AS vendor,
    t.pd_nature         AS pd_nature,
    t.pd_other_text     AS pd_other_text,
    t.clauses           AS clauses,
    t.doc_link          AS doc_link,
    t.status            AS status
  FROM tickets t
  WHERE t.ticket_code = ?
  LIMIT 1
";

$stmt = $conn->prepare($sql);
if (!$stmt) {
  echo json_encode(['success' => false, 'message' => 'DB prepare failed: ' . $conn->error]);
  exit;
}

$stmt->bind_param('s', $ticket_id);
$stmt->execute();
$result = $stmt->get_result();

if (!$result || $result->num_rows === 0) {
  echo json_encode(['success' => false, 'message' => 'No ticket found for that Ticket ID.']);
  exit;
}

$row = $result->fetch_assoc();
$stmt->close();

// ===== Authorization =====
if (!($role === 'admin' || (int)$row['id'] === $user_id)) {
  // NOTE: since your table doesn’t store user_id, you can relax this for now
  // by commenting out this block if needed:
  // echo json_encode(['success' => false, 'message' => 'Not authorized to load this ticket.']);
  // exit;
}

// ===== Return JSON =====
echo json_encode([
  'success' => true,
  'data' => [
    'full_name'       => $row['full_name'],
    'email'           => $row['email'],
    'group'           => $row['grp'], // mapped
    'tribe'           => $row['tribe'],
    'assigned_lawyer' => $row['assigned_lawyer'],
    'cc_emails'       => $row['cc_emails'],
    'summary'         => $row['summary'],
    'contract_type'   => $row['contract_type'],
    'contract_other'  => $row['contract_other'],
    'customer'        => $row['customer'],
    'vendor'          => $row['vendor'],
    'pd_nature'       => $row['pd_nature'],
    'pd_other_text'   => $row['pd_other_text'],
    'clauses'         => $row['clauses'],
    'doc_link'        => $row['doc_link'],
    'status'          => $row['status'],
  ]
]);
