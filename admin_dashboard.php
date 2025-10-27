<?php
session_start();
if (empty($_SESSION['lawyer_id'])) {
  header('Location: login.php');
  exit;
}

require __DIR__ . '/config/db.php';

function h($s){ return htmlspecialchars((string)$s, ENT_QUOTES, 'UTF-8'); }

// --- KPIs ---
$today = date('Y-m-d');

$qCompleted = $conn->query("SELECT COUNT(*) c FROM tickets WHERE status='Completed'");
$completed  = (int)$qCompleted->fetch_assoc()['c'];

$qOverdue = $conn->query("
  SELECT COUNT(*) c
  FROM tickets
  WHERE due_date IS NOT NULL
    AND due_date < CURDATE()
    AND status <> 'Completed'
");
$overdue = (int)$qOverdue->fetch_assoc()['c'];

$qRevs = $conn->query("SELECT COUNT(*) c FROM tickets WHERE status='For Revisions'");
$revisions = (int)$qRevs->fetch_assoc()['c'];

$qPending = $conn->query("SELECT COUNT(*) c FROM tickets WHERE status='Pending'");
$pending  = (int)$qPending->fetch_assoc()['c'];

// --- Search + Pagination ---
$q       = trim($_GET['q'] ?? '');
$page    = max(1, (int)($_GET['page'] ?? 1));
$perPage = 10;

// Build WHERE and params (prepared statements)
$where  = '1';
$types  = '';
$params = [];

if ($q !== '') {
  $where = "(ticket_code LIKE ? OR full_name LIKE ? OR email LIKE ? OR contract_type LIKE ? OR assigned_lawyer LIKE ? OR status LIKE ?)";
  $like  = "%{$q}%";
  $params = [$like, $like, $like, $like, $like, $like];
  $types  = str_repeat('s', 6);
}

// COUNT total
if ($q === '') {
  $countRes = $conn->query("SELECT COUNT(*) c FROM tickets WHERE $where");
  $total = (int) $countRes->fetch_assoc()['c'];
} else {
  $stmtCnt = $conn->prepare("SELECT COUNT(*) c FROM tickets WHERE $where");
  $stmtCnt->bind_param($types, ...$params);
  $stmtCnt->execute();
  $resCnt = $stmtCnt->get_result();
  $total  = (int) $resCnt->fetch_assoc()['c'];
  $stmtCnt->close();
}

$totalPages = max(1, (int)ceil($total / $perPage));
if ($page > $totalPages) $page = $totalPages;
$offset = ($page - 1) * $perPage;

// Fetch page rows
$selectSql = "
  SELECT id, ticket_code, created_at, full_name, email,
         priority, due_date, assigned_lawyer, contract_type, status
  FROM tickets
  WHERE $where
  ORDER BY id DESC
  LIMIT ? OFFSET ?
";

if ($q === '') {
  // No params other than limit/offset
  $stmt = $conn->prepare($selectSql);
  $stmt->bind_param('ii', $perPage, $offset);
} else {
  // Add limit/offset to param list
  $typesPage  = $types . 'ii';
  $paramsPage = $params;
  $paramsPage[] = $perPage;
  $paramsPage[] = $offset;

  $stmt = $conn->prepare($selectSql);
  $stmt->bind_param($typesPage, ...$paramsPage);
}

$stmt->execute();
$rows = $stmt->get_result();

// Row class by status / due date
function status_class($status, $due) {
  $status = (string)$status;
  if ($status === 'Completed') return 'row-completed';
  if ($status === 'For Revisions') return 'row-revisions';
  if ($due && $status !== 'Completed' && $due < date('Y-m-d')) return 'row-overdue';
  return 'row-pending';
}

// Build pagination links keeping the search query
function build_page_link($page, $q) {
  $qs = ['page' => $page];
  if ($q !== '') $qs['q'] = $q;
  return '?' . http_build_query($qs);
}
?>
<!doctype html>
<html lang="en">
<head>
  <meta charset="utf-8" />
  <title>Admin Dashboard – Data Agreements & Contracts</title>
  <meta name="viewport" content="width=device-width,initial-scale=1" />
  <link rel="stylesheet" href="assets/css/admin.css">
  <style>
    /* minimal UI for search + pagination */
    .toolbar{display:flex;gap:12px;align-items:center;justify-content:space-between;margin:10px 2px 14px}
    .toolbar .search{display:flex;gap:6px;align-items:center}
    .toolbar input[type="text"]{height:36px;padding:6px 10px;border:1px solid #cfd3dc;border-radius:8px;min-width:280px}
    .toolbar .btn{height:36px;padding:0 12px;border-radius:8px;border:1px solid #2E3192;background:#2E3192;color:#fff;cursor:pointer}
    .toolbar .btn.ghost{background:#fff;color:#2E3192}
    .pagination{display:flex;gap:6px;align-items:center;justify-content:flex-end;margin:12px 0}
    .pagination a,.pagination span{
      display:inline-block;padding:6px 10px;border:1px solid #cfd3dc;border-radius:6px;text-decoration:none;color:#1f2352;background:#fff
    }
    .pagination .active{background:#2E3192;color:#fff;border-color:#2E3192}
    .pagination .muted{opacity:.6;pointer-events:none}
    /* (Optional) widen the container/table if you want more width */
    .container{max-width:min(95vw,1600px);padding:0 20px}
    .table-wrap{width:100%;overflow-x:auto}
  </style>
</head>
<body>

<header class="brandbar">
  <div class="brandbar__inner container">
    <div class="brandbar__logo" aria-hidden="true">
      <svg viewBox="0 0 64 64" role="img" aria-label="Logo">
        <circle cx="32" cy="32" r="30" fill="#fff"></circle>
        <circle cx="20" cy="24" r="5" fill="#2E3192"></circle>
        <circle cx="44" cy="24" r="5" fill="#2E3192"></circle>
        <rect x="18" y="38" width="28" height="4" rx="2" fill="#2E3192"></rect>
      </svg>
    </div>
    <div class="brandbar__title">
      <div>Data Agreements and</div>
      <div>Contracts Review Request Form</div>
    </div>
  </div>
</header>

<main class="container page">
  <section class="kpi-grid">
    <div class="kpi-card kpi-green">
      <div class="kpi-title">Completed as of <?= h($today) ?></div>
      <div class="kpi-value"><?= $completed ?></div>
    </div>
    <div class="kpi-card kpi-red">
      <div class="kpi-title">Total Overdue as of <?= h($today) ?></div>
      <div class="kpi-value"><?= $overdue ?></div>
    </div>
    <div class="kpi-card kpi-amber">
      <div class="kpi-title">For Revisions as of <?= h($today) ?></div>
      <div class="kpi-value"><?= $revisions ?></div>
    </div>
    <div class="kpi-card kpi-gray">
      <div class="kpi-title">Total Pending as of <?= h($today) ?></div>
      <div class="kpi-value"><?= $pending ?></div>
    </div>
  </section>

  <div class="legend">
    <span class="dot dot-green"></span> Completed
    <span class="dot dot-red"></span> Overdue
    <span class="dot dot-gray"></span> Pending
    <span class="dot dot-amber"></span> For revisions
  </div>

  <!-- Toolbar: Search + count -->
  <div class="toolbar">
    <form class="search" method="get" action="">
      <input type="text" name="q" value="<?= h($q) ?>" placeholder="Search ticket, requestor, email, contract type, reviewer, status…">
      <?php if ($page !== 1): ?><input type="hidden" name="page" value="1"><?php endif; ?>
      <button class="btn" type="submit">Search</button>
      <?php if ($q !== ''): ?>
        <a class="btn ghost" href="?">Clear</a>
      <?php endif; ?>
    </form>
    <div class="muted">Showing <?= ($total===0?0:$offset+1) ?>–<?= min($offset+$perPage,$total) ?> of <?= $total ?></div>
  </div>

  <section class="table-wrap">
    <table class="tickets">
      <thead>
        <tr>
          <th>TICKET ID</th>
          <th>TIMESTAMP</th>
          <th>REQUESTOR</th>
          <th>EMAIL</th>
          <th>PRIORITY</th>
          <th>DUE DATE</th>
          <th>REVIEWER</th>
          <th>CONTRACT TYPE</th>
          <th>ACTION</th>
          <th>STATUS</th>
        </tr>
      </thead>
      <tbody>
        <?php while ($r = $rows->fetch_assoc()):
          $class = status_class($r['status'], $r['due_date']);
          $reviewerName = preg_replace('/\s*<[^>]*>/', '', $r['assigned_lawyer']); // strip email
        ?>
        <tr class="<?= $class ?>">
          <td><?= h($r['ticket_code']) ?></td>
          <td><?= h($r['created_at']) ?></td>
          <td><?= h($r['full_name']) ?></td>
          <td><a href="mailto:<?= h($r['email']) ?>"><?= h($r['email']) ?></a></td>
          <td><?= h($r['priority']) ?></td>
          <td><?= h($r['due_date']) ?></td>
          <td><?= h($reviewerName) ?></td>
          <td><?= h($r['contract_type']) ?></td>
          <td class="actions">
            <form class="inline" method="post" action="api/update_status.php" style="display:inline;">
              <input type="hidden" name="id" value="<?= (int)$r['id'] ?>">
              <select name="status" class="status-select" onchange="this.form.submit()" <?= $r['status']==='Completed'?'disabled':''; ?>>
                <option value="Pending" <?= $r['status']==='Pending'?'selected':''; ?>>Pending</option>
                <option value="For Revisions" <?= $r['status']==='For Revisions'?'selected':''; ?>>For Revisions</option>
                <option value="Completed" <?= $r['status']==='Completed'?'selected':''; ?>>Completed</option>
              </select>
            </form>
          </td>
          <td><?= h($r['status']) ?></td>
        </tr>
        <?php endwhile; $stmt->close(); ?>
      </tbody>
    </table>

    <!-- Pagination -->
    <div class="pagination">
      <?php
        $prev = max(1, $page-1);
        $next = min($totalPages, $page+1);

        // “First” and “Prev”
        if ($page > 1) {
          echo '<a href="'.h(build_page_link(1,$q)).'">« First</a>';
          echo '<a href="'.h(build_page_link($prev,$q)).'">‹ Prev</a>';
        } else {
          echo '<span class="muted">« First</span>';
          echo '<span class="muted">‹ Prev</span>';
        }

        // windowed page numbers
        $start = max(1, $page - 2);
        $end   = min($totalPages, $page + 2);
        for ($p=$start; $p<=$end; $p++) {
          if ($p == $page) echo '<span class="active">'.h($p).'</span>';
          else echo '<a href="'.h(build_page_link($p,$q)).'">'.h($p).'</a>';
        }

        // “Next” and “Last”
        if ($page < $totalPages) {
          echo '<a href="'.h(build_page_link($next,$q)).'">Next ›</a>';
          echo '<a href="'.h(build_page_link($totalPages,$q)).'">Last »</a>';
        } else {
          echo '<span class="muted">Next ›</span>';
          echo '<span class="muted">Last »</span>';
        }
      ?>
    </div>
  </section>
</main>

</body>
</html>
