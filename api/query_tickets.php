<?php

// --- Search + Pagination ---
$q       = trim($_GET['q'] ?? '');
$page    = max(1, (int)($_GET['page'] ?? 1));
$perPage = 10;

// --- Status filter ---
$allowedStatuses = ['', 'Pending', 'For Revisions', 'Completed', 'Overdue'];
$status = $_GET['status'] ?? '';
if (!in_array($status, $allowedStatuses, true)) {
  $status = '';
}

// Build WHERE + params
$conditions = ['1'];
$types      = '';
$params     = [];

if ($q !== '') {
  $conditions[] = "(ticket_code LIKE ? OR full_name LIKE ? OR email LIKE ? OR contract_type LIKE ? OR assigned_lawyer LIKE ? OR status LIKE ?)";
  $like = "%{$q}%";
  array_push($params, $like, $like, $like, $like, $like, $like);
  $types .= str_repeat('s', 6);
}

if ($status !== '') {
  if ($status === 'Overdue') {
    $conditions[] = "(due_date < CURDATE() AND status <> 'Completed')";
  } else {
    $conditions[] = "status = ?";
    $params[] = $status;
    $types   .= 's';
  }
}

$where = implode(' AND ', $conditions);
$usePrepared = ($types !== '');

// COUNT total
if (!$usePrepared) {

  $countRes = $conn->query("SELECT COUNT(*) c FROM tickets WHERE $where");
  $total = (int)$countRes->fetch_assoc()['c'];

} else {

  $stmtCnt = $conn->prepare("SELECT COUNT(*) c FROM tickets WHERE $where");
  $stmtCnt->bind_param($types, ...$params);
  $stmtCnt->execute();
  $resCnt = $stmtCnt->get_result();
  $total  = (int)$resCnt->fetch_assoc()['c'];
  $stmtCnt->close();
}

$totalPages = max(1, ceil($total / $perPage));

if ($page > $totalPages) $page = $totalPages;

$offset = ($page - 1) * $perPage;

// Fetch page rows
$selectSql = "
SELECT 
    id,
    ticket_code,
    created_at,
    completed_at,   -- ADD THIS LINE
    full_name,
    email,
    priority,
    due_date,
    assigned_lawyer,
    contract_type,
    status,
    remarks
FROM tickets
  WHERE $where
  ORDER BY id DESC
  LIMIT ? OFFSET ?
";

if (!$usePrepared) {

  $stmt = $conn->prepare($selectSql);
  $stmt->bind_param("ii", $perPage, $offset);

} else {

  $typesPage  = $types . "ii";
  $paramsPage = $params;
  $paramsPage[] = $perPage;
  $paramsPage[] = $offset;

  $stmt = $conn->prepare($selectSql);
  $stmt->bind_param($typesPage, ...$paramsPage);
}

$stmt->execute();
$rows = $stmt->get_result();
