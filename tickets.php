<?php
session_start();
if (empty($_SESSION['lawyer_id'])) {
  header('Location: login.php');
  exit;
}

require __DIR__ . '/config/db.php';
require __DIR__ . '/utils/pagination.php';

// include your query logic for search, pagination, filters
include __DIR__ . '/api/query_tickets.php';

function h($s){ return htmlspecialchars((string)$s, ENT_QUOTES, 'UTF-8'); }
function status_class($status, $due_date, $completed_at = null) {

    $today = date('Y-m-d');
    $due = $due_date ? date('Y-m-d', strtotime($due_date)) : null;
    $done = $completed_at ? date('Y-m-d', strtotime($completed_at)) : null;

    // COMPLETED
    if ($status === 'Completed') {

        // Completed but after due date
        if ($done && $due && $done > $due) {
            return 'row-completed-late';
        }

        // Completed normally
        return 'row-completed';
    }

    // FOR REVISIONS (and overdue)
    if ($status === 'For Revisions') {
        if ($due && $due < $today) {
            return 'row-revisions-overdue';
        }
        return 'row-revisions';
    }

    // PENDING AND OVERDUE
    if ($due && $due < $today) {
        return 'row-overdue';
    }

    return 'row-pending';
}


?>
<!doctype html>
<html lang="en">
<head>
  <meta charset="utf-8" />
  <title>Tickets – Data Agreements & Contracts</title>
  <meta name="viewport" content="width=device-width,initial-scale=1" />
  <link rel="stylesheet" href="assets/css/admin.css">
  <link rel="stylesheet" href="assets/css/notifications.css">
  <script src="/assets/js/notification.js"></script>
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/7.0.1/css/all.min.css">
  <link rel="icon" type="image/x-icon" href="/assets/img/favicon/favicon.ico">
</head>
<body>
<?php
$brand = ['showMenuToggle'=>true,'showNotif'=>true];
include __DIR__ . '/assets/partials/brandbar.php';
?>

<!-- SIDEBAR -->
<!-- SIDEBAR -->
<aside id="offcanvas" aria-hidden="true">
    <?php include __DIR__ . '/assets/partials/sidebar_common.php'; ?>
</aside>
<div id="sbBackdrop" aria-hidden="true"></div>


<div id="sbBackdrop" aria-hidden="true"></div>

<main class="container-fluid page" id="mainContent">

<div class="legend">
    <span class="dot dot-green"></span> Completed
    <span class="dot dot-blue"></span> Completed (Past Due)
    <span class="dot dot-red"></span> Overdue
    <span class="dot dot-gray"></span> Pending
    <span class="dot dot-amber"></span> For revisions
</div>

  <!-- Search + Filter Toolbar -->
  <div class="toolbar">
    <form class="search" method="get" action="">
      <input type="text" name="q" value="<?= h($q) ?>" placeholder="Search ticket, requestor, email…">

      <select name="status" class="status-filter" onchange="this.form.submit()">
        <option value=""               <?= $status===''?'selected':''; ?>>All</option>
        <option value="Pending"        <?= $status==='Pending'?'selected':''; ?>>Pending</option>
        <option value="For Revisions"  <?= $status==='For Revisions'?'selected':''; ?>>For Revisions</option>
        <option value="Completed"      <?= $status==='Completed'?'selected':''; ?>>Completed</option>
        <option value="Overdue"        <?= $status==='Overdue'?'selected':''; ?>>Overdue</option>
      </select>

      <button class="btn" type="submit">Search</button>
      <?php if ($q !== '' || $status !== ''): ?>
        <a class="btn ghost" href="tickets.php">Clear</a>
      <?php endif; ?>
    </form>
      <?php
      $from = ($total === 0) ? 0 : ($offset + 1);
      $to   = min($offset + $perPage, $total);
      ?>

      <div class="muted">
        Showing <?= $from ?>–<?= $to ?> of <?= $total ?>
        &nbsp;•&nbsp;
        Page <?= (int)$page ?> of <?= (int)$totalPages ?>
      </div>
  </div>
  

  <!-- Ticket Table -->
  <section class="table-wrap">
    <table class="tickets">
      <thead>
        <tr>
          <th>TICKET ID</th>
          <th>TIMESTAMP</th>
          <th>REQUESTOR</th>
          <th>EMAIL</th>
          <!-- <th>PRIORITY</th> -->
          <th>DUE DATE</th>
          <th>REVIEWER</th>
          <th>CONTRACT TYPE</th>
          <th>ACTION</th>
          <th>REMARKS</th>
        </tr>
      </thead>
      <tbody>
        <?php while ($r = $rows->fetch_assoc()):
         $class = status_class($r['status'], $r['due_date'] ?? null,$r['completed_at'] ?? null);
          $reviewerName = $r['reviewer_name'] ?? 'Unassigned';
        ?>
        <tr class="<?= $class ?>">
          <td><?= h($r['ticket_code']) ?></td>
          <td><?= h(date('M d, Y', strtotime($r['created_at']))) ?></td>
          <td><?= h($r['full_name']) ?></td>
          <td><a href="mailto:<?= h($r['email']) ?>"><?= h($r['email']) ?></a></td>
          <!-- <td><?= h($r['priority']) ?></td> -->
          <td><?= h(date('M d, Y', strtotime($r['due_date']))) ?></td>
          <td><?= h($reviewerName) ?></td>
          <td><?= h($r['contract_type']) ?></td>

          <td>
            <form method="post" action="api/update_status.php">
              <input type="hidden" name="id" value="<?= (int)$r['id'] ?>">
              <select name="status" onchange="this.form.submit()"
                <?= $r['status']==='Completed'?'disabled':''; ?>>
                <option value="Pending"        <?= $r['status']==='Pending'?'selected':''; ?>>Pending</option>
                <option value="For Revisions"  <?= $r['status']==='For Revisions'?'selected':''; ?>>For Revisions</option>
                <option value="Completed"      <?= $r['status']==='Completed'?'selected':''; ?>>Completed</option>
              </select>
            </form>
          </td>

        <td style="text-align:center;">
          <?php $hasRemarks = trim((string)($r['remarks'] ?? '')) !== ''; ?>

          <button
            type="button"
            class="icon-btn openRemarksModal <?= $hasRemarks ? 'has-remarks' : 'no-remarks' ?>"
            title="<?= $hasRemarks ? 'View / Edit Remarks' : 'Add Remarks' ?>"
            data-id="<?= (int)$r['id'] ?>"
            data-ticket="<?= h($r['ticket_code']) ?>"
            data-remarks="<?= h($r['remarks'] ?? '') ?>"
          >
            <i class="fa-solid fa-pen-to-square"></i>
          </button>
        </td>

        </tr>
      <?php endwhile; ?>
      </tbody>
    </table>

<!-- REMARKS MODAL -->
<div id="remarksModal" class="modal-overlay" style="display:none;">
  <div class="modal-box" style="max-width:520px;">
    <h2 id="remarksModalTitle">Remarks</h2>

    <form method="post" action="api/update_remarks.php" id="remarksForm">
      <input type="hidden" name="id" id="remarksTicketId">

      <label for="remarksText" style="display:block;margin-top:10px;">Remarks:</label>
      <textarea
        id="remarksText"
        name="remarks"
        rows="6"
        style="width:100%; resize:vertical;"
        placeholder="Type remarks here..."
      ></textarea>

      <div class="modal-actions" style="margin-top:12px;">
        <button type="submit" class="btn primary">Save</button>
        <button type="button" class="btn secondary" id="closeRemarksModal">Cancel</button>
      </div>
    </form>
  </div>
</div>

    <!-- Pagination -->
    <?= build_pagination($page, $totalPages, $q, $status) ?>
  </section>

        <footer class="site-footer">
        <p>© 2025 Globe • AI and Privacy Governance • All Rights Reserved</p>
      </footer>
</main>

<script src="/assets/js/sidebar.js"></script>
<script>
document.addEventListener('DOMContentLoaded', () => {
  const modal = document.getElementById('remarksModal');
  if (!modal) return;

  const titleEl = document.getElementById('remarksModalTitle');
  const idEl = document.getElementById('remarksTicketId');
  const textEl = document.getElementById('remarksText');
  const closeBtn = document.getElementById('closeRemarksModal');

  function openModal({ id, ticket, remarks }) {
    idEl.value = id;
    textEl.value = remarks || '';
    titleEl.textContent = `Remarks — ${ticket}`;
    modal.style.display = 'flex';
    setTimeout(() => textEl.focus(), 50);
  }

  function closeModal() {
    modal.style.display = 'none';
    idEl.value = '';
    textEl.value = '';
  }

  document.addEventListener('click', (e) => {
    const btn = e.target.closest('.openRemarksModal');
    if (!btn) return;

    openModal({
      id: btn.dataset.id,
      ticket: btn.dataset.ticket,
      remarks: btn.dataset.remarks
    });
  });

  closeBtn.addEventListener('click', closeModal);

  // Click outside closes
  modal.addEventListener('click', (e) => {
    if (e.target === modal) closeModal();
  });

  // ESC closes
  document.addEventListener('keydown', (e) => {
    if (e.key === 'Escape' && modal.style.display !== 'none') closeModal();
  });
});
</script>
</body>
</html>
