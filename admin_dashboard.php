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

// --- Status filter ---
$allowedStatuses = ['', 'Pending', 'For Revisions', 'Completed', 'Overdue'];
$status = $_GET['status'] ?? '';
if (!in_array($status, $allowedStatuses, true)) {
  $status = '';
}


// Build WHERE and params (prepared statements)
$conditions = ['1'];   // always true, so we can safely AND things
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
    // "Overdue" is not a DB status; compute it: past due & not completed
    $conditions[] = "(due_date IS NOT NULL AND due_date < CURDATE() AND status <> 'Completed')";
    // no params for this condition
  } else {
    $conditions[] = "status = ?";
    $params[] = $status;
    $types    .= 's';
  }
}

$where = implode(' AND ', $conditions);
$usePrepared = ($types !== '');


// COUNT total
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

if (!$usePrepared) {
  $stmt = $conn->prepare($selectSql);
  $stmt->bind_param('ii', $perPage, $offset);
} else {
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
function build_page_link($page, $q, $status) {
  $qs = ['page' => $page];
  if ($q !== '')       $qs['q'] = $q;
  if ($status !== '')  $qs['status'] = $status;
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
  <link rel="stylesheet" href="/assets/css/toast.css">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/7.0.1/css/all.min.css" integrity="sha512-2SwdPD6INVrV/lHTZbO2nodKhrnDdJK9/kg2XD1r9uGqPo1cUbujc+IYdlYdEErWNu69gVcYgdxlmVmzTWnetw==" crossorigin="anonymous" referrerpolicy="no-referrer" />
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
    .status-filter{
      height:36px;
      padding:0 10px;
      border:1px solid #cfd3dc;
      border-radius:8px;
      background:#fff;
      font-size:.95rem;
    }
    .status-filter:focus{outline:none;border-color:#2E3192}
    .notif-wrapper{position:relative;margin-left:auto;}
    .notif-bell{
      background:none;border:none;color:#fff;cursor:pointer;
      font-size:22px;position:relative;
    }
    .notif-badge{
      position:absolute;top:-6px;right:-8px;background:#e53935;
      color:#fff;font-size:11px;border-radius:50%;padding:2px 6px;
    }
    .notif-dropdown{
      position:absolute;right:0;top:38px;width:320px;
      background:#fff;border-radius:10px;box-shadow:0 6px 16px rgba(0,0,0,.25);
      display:none;overflow:hidden;z-index:3000;
    }
    .notif-dropdown.show{display:block;}
    .notif-header{
      background:#2E3192;color:#fff;font-weight:700;padding:10px 14px;
    }
    .notif-list{max-height:350px;overflow-y:auto;}
    .notif-item{
      padding:10px 14px;border-bottom:1px solid #eee;cursor:pointer;
    }
    .notif-item:hover{background:#f7f7ff;}

  </style>
</head>
<body>

<?php
$brand = [
  'showMenuToggle' => true, // hide ☰
  'showNotif'      => true, // hide bell
];
include __DIR__ . '/assets/partials/brandbar.php';
?>


<aside id="offcanvas" aria-hidden="true">
  <div class="sb-head">
    <span>Navigation</span>
    <button id="sbClose" aria-label="Close menu">✕</button>
  </div>
  <nav class="sb-nav">
    <a href="admin_dashboard.php">Dashboard</a>
    <a href="admin_dashboard.php?q=">All Tickets</a>
    <a href="admin_dashboard.php?q=Pending">Pending</a>
    <a href="admin_dashboard.php?q=For%20Revisions">For Revisions</a>
    <a href="admin_dashboard.php?q=Completed">Completed</a>
    <a href="admin_dashboard.php?q=Overdue">Overdue</a>
    <hr>
    <a href="/api/logout.php">Logout</a>
  </nav>
</aside>

<div id="sbBackdrop" aria-hidden="true"></div>


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

    <!-- Status Filter -->
    <select name="status" class="status-filter" onchange="this.form.submit()">
      <option value=""               <?= $status===''?'selected':''; ?>>All</option>
      <option value="Pending"        <?= $status==='Pending'?'selected':''; ?>>Pending</option>
      <option value="For Revisions"  <?= $status==='For Revisions'?'selected':''; ?>>For Revisions</option>
      <option value="Completed"      <?= $status==='Completed'?'selected':''; ?>>Completed</option>
      <option value="Overdue"        <?= $status==='Overdue'?'selected':''; ?>>Overdue</option>
    </select>

    <?php if ($page !== 1): ?><input type="hidden" name="page" value="1"><?php endif; ?>
    <button class="btn" type="submit">Search</button>

    <?php if ($q !== '' || $status !== ''): ?>
      <a class="btn ghost" href="?">Clear</a>
    <?php endif; ?>
  </form>

  <div class="muted">
    Showing <?= ($total===0?0:$offset+1) ?>–<?= min($offset+$perPage,$total) ?> of <?= $total ?>
  </div>
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
          echo '<a href="'.h(build_page_link(1,$q,$status)).'">« First</a>';
          echo '<a href="'.h(build_page_link($prev,$q,$status)).'">‹ Prev</a>';
        } else {
          echo '<span class="muted">« First</span>';
          echo '<span class="muted">‹ Prev</span>';
        }

        // windowed page numbers
        $start = max(1, $page - 2);
        $end   = min($totalPages, $page + 2);
        for ($p=$start; $p<=$end; $p++) {
          if ($p == $page) echo '<span class="active">'.h($p).'</span>';
          else echo '<a href="'.h(build_page_link($p,$q,$status)).'">'.h($p).'</a>';
        }

        // “Next” and “Last”
        if ($page < $totalPages) {
          echo '<a href="'.h(build_page_link($next,$q,$status)).'">Next ›</a>';
          echo '<a href="'.h(build_page_link($totalPages,$q,$status)).'">Last »</a>';
        } else {
          echo '<span class="muted">Next ›</span>';
          echo '<span class="muted">Last »</span>';
        }
      ?>
    </div>
  </section>
</main>

    <!-- Notification sound -->
    <audio id="alertSound" preload="auto">
      <source src="/assets/sounds/notify.mp3" type="audio/mpeg">
    </audio>

    <!-- Modal -->
    <div id="dueSoonModal" style="display:none;position:fixed;inset:0;z-index:2000;">
      <div style="position:absolute;inset:0;background:rgba(0,0,0,.45)"></div>
      <div style="position:relative;max-width:720px;margin:8vh auto;background:#fff;border-radius:12px;overflow:hidden;box-shadow:0 10px 30px rgba(0,0,0,.25)">
        <div style="background:#ffecb5;color:#664d03;padding:12px 16px;font-weight:700;">
          ⚠ Tickets due within 24 hours
        </div>
        <div style="padding:12px 16px;max-height:60vh;overflow:auto;">
          <table style="width:100%;border-collapse:collapse;">
            <thead>
              <tr style="text-align:left;font-weight:700;border-bottom:1px solid #eee">
                <th style="padding:8px">Ticket</th>
                <th style="padding:8px">Requestor</th>
                <th style="padding:8px">Contract</th>
                <th style="padding:8px">Priority</th>
                <th style="padding:8px">Due</th>
              </tr>
            </thead>
            <tbody id="dueSoonTbody"></tbody>
          </table>
        </div>
        <div style="display:flex;gap:8px;justify-content:flex-end;padding:12px 16px;border-top:1px solid #eee">
          <button id="snoozeBtn" style="padding:8px 12px;border-radius:8px;border:1px solid #bbb;background:#fff;cursor:pointer">Snooze 10 min</button>
          <button id="ackBtn"    style="padding:8px 12px;border-radius:8px;border:0;background:#2E3192;color:#fff;cursor:pointer">Acknowledge</button>
        </div>
      </div>
    </div>
<script src="/assets/js/notification.js"></script>
<script src="/assets/js/sidebar.js"></script>
<script src="/assets/js/toast.js"></script>
<script src="/assets/js/showToast.js"></script>
</body>
</html>
