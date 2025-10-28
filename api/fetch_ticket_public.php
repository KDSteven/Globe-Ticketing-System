<?php
// api/fetch_ticket_public.php
// Public, read-only prefill endpoint for the Requestor portal (NO session required).

header('Content-Type: application/json');

// ---- Defensive error catcher so you always get JSON (not blank screens) ----
error_reporting(E_ALL);
ini_set('display_errors', '0');
set_error_handler(function($sev,$msg,$file,$line){
  echo json_encode(['success'=>false,'message'=>"PHP error: $msg @ $file:$line"]);
  exit;
});
register_shutdown_function(function(){
  $e = error_get_last();
  if ($e && in_array($e['type'],[E_ERROR,E_PARSE,E_CORE_ERROR,E_COMPILE_ERROR])){
    echo json_encode(['success'=>false,'message'=>"Fatal: {$e['message']} @ {$e['file']}:{$e['line']}"]);
  }
});

// ---- DB connection (make sure this path is correct) ----
require __DIR__ . '/../config/db.php';
if (!isset($conn) || !($conn instanceof mysqli)) {
  echo json_encode(['success'=>false,'message'=>'Database connection not available.']);
  exit;
}

// ---- Input ----
$ticket_code = trim($_GET['ticket_id'] ?? '');
if ($ticket_code === '') {
  echo json_encode(['success'=>false,'message'=>'Ticket ID is required.']);
  exit;
}

// ---- Query mapped to YOUR tickets table schema ----
// Columns from your screenshot:
// id, ticket_code, created_at, full_name, email, priority, due_date, completed_at,
// grp, tribe, assigned_lawyer, cc_emails, summary, contract_type, contract_other,
// customer, vendor, pd_nature, pd_other_text, clauses, doc_link, status
$sql = "
  SELECT
    t.full_name,
    t.email,
    t.grp,
    t.tribe,
    t.assigned_lawyer,
    t.cc_emails,
    t.summary,
    t.contract_type,
    t.contract_other,
    t.customer,
    t.vendor,
    t.pd_nature,
    t.pd_other_text,
    t.clauses,
    t.doc_link
  FROM tickets t
  WHERE t.ticket_code = ?
  LIMIT 1
";

$stmt = $conn->prepare($sql);
if (!$stmt) {
  echo json_encode(['success'=>false,'message'=>'DB prepare failed: '.$conn->error]); exit;
}
$stmt->bind_param('s', $ticket_code);
$stmt->execute();
$res = $stmt->get_result();
if (!$res || $res->num_rows === 0) {
  echo json_encode(['success'=>false,'message'=>'No ticket found for that ID.']); exit;
}
$row = $res->fetch_assoc();
$stmt->close();

// ---- Return ONLY prefill-safe fields ----
echo json_encode([
  'success' => true,
  'data' => [
    'full_name'       => (string)$row['full_name'],
    'email'           => (string)$row['email'],
    'group'           => (string)$row['grp'],
    'tribe'           => (string)$row['tribe'],
    'assigned_lawyer' => (string)$row['assigned_lawyer'],
    'cc_emails'       => (string)$row['cc_emails'],
    'summary'         => (string)$row['summary'],
    'contract_type'   => (string)$row['contract_type'],
    'contract_other'  => (string)$row['contract_other'],
    'customer'        => (string)$row['customer'],
    'vendor'          => (string)$row['vendor'],
    'pd_nature'       => (string)$row['pd_nature'],
    'pd_other_text'   => (string)$row['pd_other_text'],
    'clauses'         => (string)$row['clauses'],
    'doc_link'        => (string)$row['doc_link'],
  ],
]);
