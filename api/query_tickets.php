<?php

// --- Search + Pagination ---
$q       = trim($_GET['q'] ?? '');
$page    = max(1, (int)($_GET['page'] ?? 1));
$perPage = 10;

// --- Status filter ---
$allowedStatuses = ['', 'Pending', 'For Revisions', 'Completed', 'Closed - No Response', 'Overdue'];
$status = $_GET['status'] ?? '';
if (!in_array($status, $allowedStatuses, true)) {
    $status = '';
}

// Build WHERE + params
$conditions = ['1'];
$types      = '';
$params     = [];

if ($q !== '') {
    // Search includes reviewer name
    $conditions[] = "(t.ticket_code LIKE ? 
                   OR t.full_name LIKE ? 
                   OR t.email LIKE ? 
                   OR t.contract_type LIKE ? 
                   OR l.name LIKE ?
                   OR t.status LIKE ?)";

    $like = "%{$q}%";
    array_push($params, $like, $like, $like, $like, $like, $like);
    $types .= 'ssssss';
}

if ($status !== '') {
    if ($status === 'Overdue') {
        $conditions[] = "(t.due_date < CURDATE() AND t.status NOT IN ('Completed','Closed - No Response'))";
    } else {
        $conditions[] = "t.status = ?";
        $params[] = $status;
        $types   .= 's';
    }
}

$where = implode(' AND ', $conditions);
$usePrepared = ($types !== '');

// COUNT total rows
$countSql = "
SELECT COUNT(*) c
FROM tickets t
LEFT JOIN lawyers l ON l.email = t.assigned_lawyer
WHERE $where
";

if (!$usePrepared) {

    $countRes = $conn->query($countSql);
    $total = (int) $countRes->fetch_assoc()['c'];

} else {

    $stmtCnt = $conn->prepare($countSql);
    $stmtCnt->bind_param($types, ...$params);
    $stmtCnt->execute();
    $resCnt = $stmtCnt->get_result();
    $total  = (int) $resCnt->fetch_assoc()['c'];
    $stmtCnt->close();
}

$totalPages = max(1, ceil($total / $perPage));
if ($page > $totalPages) $page = $totalPages;

$offset = ($page - 1) * $perPage;

// MAIN QUERY
$selectSql = "
SELECT 
    t.id,
    t.ticket_code,
    t.created_at,
    t.completed_at,
    t.full_name,
    t.email,
    t.priority,
    t.due_date,
    t.assigned_lawyer,
    t.contract_type,
    t.status,
    t.remarks,

    l.name  AS reviewer_name,
    l.email AS reviewer_email

FROM tickets t
LEFT JOIN lawyers l 
       ON l.email = t.assigned_lawyer

WHERE $where
ORDER BY t.id DESC
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
