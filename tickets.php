<?php
session_start();
if (empty($_SESSION['lawyer_id'])) {
  header('Location: login.php');
  exit;
}

require __DIR__ . '/config/db.php';
require __DIR__ . '/utils/pagination.php';
require_once __DIR__ . '/utils/helpers.php';

// include your query logic for search, pagination, filters
include __DIR__ . '/api/query_tickets.php';
function status_class($status, $due_date, $completed_at = null)
{

  $today = date('Y-m-d');
  $due = $due_date ? date('Y-m-d', strtotime($due_date)) : null;
  $done = $completed_at ? date('Y-m-d', strtotime($completed_at)) : null;

  // FINAL STATES (Completed / Closed)
  if ($status === 'Completed' || $status === 'Closed - No Response') {

    // If you want "Closed - No Response" to have its own color,
    // return a new class like 'row-closed' (recommended).
    if ($status === 'Closed - No Response') {
      return 'row-closed';
    }

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
  $brand = ['showMenuToggle' => true, 'showNotif' => true];
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
      <a href="tickets.php?status=Completed" class="legend-link <?= $status === 'Completed' ? 'active-filter' : '' ?>"><span class="dot dot-green"></span> Completed</a>
      <a href="tickets.php?status=Completed" class="legend-link <?= $status === 'Completed' ? 'active-filter' : '' ?>"><span class="dot dot-blue"></span> Completed (Past Due)</a>
      <a href="tickets.php?status=Overdue" class="legend-link <?= $status === 'Overdue' ? 'active-filter' : '' ?>"><span class="dot dot-red"></span> Overdue</a>
      <a href="tickets.php?status=Pending" class="legend-link <?= $status === 'Pending' ? 'active-filter' : '' ?>"><span class="dot dot-gray"></span> Pending</a>
      <a href="tickets.php?status=For+Revisions" class="legend-link <?= $status === 'For Revisions' ? 'active-filter' : '' ?>"><span class="dot dot-amber"></span> For Revisions</a>
      <a href="tickets.php?status=Closed+-+No+Response" class="legend-link <?= $status === 'Closed - No Response' ? 'active-filter' : '' ?>"><span class="dot dot-black"></span> Closed - No Response</a>
      <?php if ($status !== ''): ?>
        <a href="tickets.php" class="legend-link" style="color:#6b7280;font-size:12px;"><i class="fa-solid fa-xmark"></i> Clear filter</a>
      <?php endif; ?>
    </div>

    <!-- Search + Filter Toolbar -->
    <div class="toolbar">
      <form class="search" method="get" action="">
        <div class="search-input-wrap">
          <i class="fa-solid fa-magnifying-glass search-icon"></i>
          <input type="text" name="q" value="<?= h($q) ?>" placeholder="Search ticket, requestor, email…">
        </div>

        <select name="status" class="status-filter" onchange="this.form.submit()">
          <option value="" <?= $status === '' ? 'selected' : ''; ?>>All</option>
          <option value="Pending" <?= $status === 'Pending' ? 'selected' : ''; ?>>Pending</option>
          <option value="For Revisions" <?= $status === 'For Revisions' ? 'selected' : ''; ?>>For Revisions</option>
          <option value="Completed" <?= $status === 'Completed' ? 'selected' : ''; ?>>Completed</option>
          <option value="Closed - No Response" <?= $status === 'Closed - No Response' ? 'selected' : ''; ?>>Closed - No Response</option>
          <option value="Overdue" <?= $status === 'Overdue' ? 'selected' : ''; ?>>Overdue</option>
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
            <th>STATUS</th>
            <th>ACTION</th>
            <th>REMARKS</th>
          </tr>
        </thead>
        <tbody>
          <?php
          $badgeMap = [
            'row-pending'          => 'badge-pending',
            'row-overdue'          => 'badge-overdue',
            'row-revisions'        => 'badge-revisions',
            'row-revisions-overdue'=> 'badge-revisions-late',
            'row-completed'        => 'badge-completed',
            'row-completed-late'   => 'badge-completed-late',
            'row-closed'           => 'badge-closed',
          ];
          while ($r = $rows->fetch_assoc()):
            $class = status_class($r['status'], $r['due_date'] ?? null, $r['completed_at'] ?? null);
            $badgeClass = $badgeMap[$class] ?? 'badge-pending';
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
                <span class="status-badge <?= $badgeClass ?>"><?= h($r['status']) ?></span>
              </td>

              <td>
                <form method="post" action="api/update_status.php">
                  <input type="hidden" name="id" value="<?= (int)$r['id'] ?>">
                  <select name="status" onchange="this.form.submit()"
                    <?= in_array($r['status'], ['Completed', 'Closed - No Response'], true) ? 'disabled title="Status is locked for completed or closed tickets"' : '' ?>>
                    <option value="Pending" <?= $r['status'] === 'Pending' ? 'selected' : ''; ?>>Pending</option>
                    <option value="For Revisions" <?= $r['status'] === 'For Revisions' ? 'selected' : ''; ?>>For Revisions</option>
                    <option value="Completed" <?= $r['status'] === 'Completed' ? 'selected' : ''; ?>>Completed</option>
                    <option value="Closed - No Response" <?= $r['status'] === 'Closed - No Response' ? 'selected' : ''; ?>>
                      Closed - No Response
                    </option>
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
                  data-remarks="<?= h($r['remarks'] ?? '') ?>">
                  <i class="fa-solid fa-pen-to-square"></i>
                </button>
              </td>

            </tr>
          <?php endwhile; ?>
          <?php if ($total === 0): ?>
            <tr>
              <td colspan="10" class="empty-state">
                <i class="fa-solid fa-ticket"></i>
                No tickets found<?= ($q !== '' || $status !== '') ? ' — try a different search or filter' : '' ?>.
              </td>
            </tr>
          <?php endif; ?>
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
              placeholder="Type remarks here..."></textarea>

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
  <script src="/assets/js/showToast.js"></script>
  <script src="/assets/js/remarks-modal.js"></script>
</body>

</html>