<?php
session_start();

if (empty($_SESSION['lawyer_id']) || ($_SESSION['lawyer_role'] ?? '') !== 'admin') {
    header('Location: login.php');
    exit;
}

require_once __DIR__ . '/config/db.php';
require_once __DIR__ . '/utils/db_backup.php';
require_once __DIR__ . '/utils/helpers.php';

if (!isset($conn) || !($conn instanceof mysqli)) {
    die("Database connection not found. Check config/db.php.");
}

// ---------------------------
// Handle actions
// ---------------------------
$action = $_GET['action'] ?? null;

// 1) Download backup (response is a FILE; cannot show toast on that response)
if ($action === 'backup_now') {
    $result = create_backup_sql($conn);

    if ($result['ok']) {
        $meta = read_meta();
        $meta['last_file'] = basename($result['file']);
        $meta['last_time'] = date('c');
        write_meta($meta);

        download_file($result['file'], basename($result['file']));
    } else {
        // Redirect back with error toast (this works if opened in same tab)
        header('Location: databackup.php?error=' . urlencode('Backup failed: ' . ($result['error'] ?? 'Unknown error')));
        exit;
    }
}

// 2) Restore database (POST -> redirect -> showToast.js handles ok/error)
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['restore'])) {

    if (!isset($_FILES['sql_file']) || $_FILES['sql_file']['error'] !== UPLOAD_ERR_OK) {
        header('Location: databackup.php?error=' . urlencode('Please upload a valid .sql file.'));
        exit;
    }

    $tmp  = $_FILES['sql_file']['tmp_name'];
    $name = $_FILES['sql_file']['name'];

    if (strtolower(pathinfo($name, PATHINFO_EXTENSION)) !== 'sql') {
        header('Location: databackup.php?error=' . urlencode('Only .sql files are allowed.'));
        exit;
    }

    $restore = run_sql_file($conn, $tmp);

    if ($restore['ok']) {
        header('Location: databackup.php?ok=' . urlencode('Database restored successfully.'));
    } else {
        header('Location: databackup.php?error=' . urlencode($restore['error'] ?? 'Restore failed.'));
    }
    exit;
}

// ---------------------------
// Auto-backup check (runs on normal GET page load)
// ---------------------------
$auto = auto_backup_if_needed($conn);

// OPTIONAL: show toast when auto-backup occurs
// This MUST be done via redirect so showToast.js can read the query string.
if (!empty($auto['did_backup']) && !empty($auto['file']) && empty($_GET['ok']) && empty($_GET['error'])) {
    $msg = "Auto-backup created at milestone {$auto['milestone']} tickets. File: " . basename($auto['file']);
    header('Location: databackup.php?ok=' . urlencode($msg));
    exit;
}

// ---------------------------
// Page data
// ---------------------------
$meta             = read_meta();
$ticketCount      = get_ticket_count($conn);
$currentMilestone = intdiv($ticketCount, BACKUP_EVERY) * BACKUP_EVERY;
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>Data Backup – Data Agreements & Contracts</title>

  <link rel="stylesheet" href="/assets/css/admin.css">
  <link rel="stylesheet" href="/assets/css/notifications.css">
  <link rel="stylesheet" href="/assets/css/toast.css">
  <link rel="icon" type="image/x-icon" href="/assets/img/favicon/favicon.ico">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/7.0.1/css/all.min.css">

</head>
<body>

<?php
$brand = [
  'showMenuToggle' => true,
  'showNotif'      => true,
];
include __DIR__ . '/assets/partials/brandbar.php';
?>

<aside id="offcanvas" aria-hidden="true">
  <?php require_once __DIR__ . '/assets/partials/sidebar_common.php'; ?>
</aside>
<div id="sbBackdrop" aria-hidden="true"></div>

<main class="container-fluid page" id="mainContent">

  <h1 class="page-title">Data Backup</h1>
  <p class="hint" style="padding: 0 24px 16px;">
    Auto-backup runs when total tickets reach every <b><?php echo (int)BACKUP_EVERY; ?></b> tickets (50, 100, 150…).
  </p>

  <!-- STATUS KPI CARDS -->
  <section class="kpi-grid" style="padding: 0 24px; margin-bottom: 24px;">
    <div class="kpi-card kpi-blue">
      <div class="kpi-icon"><i class="fa-solid fa-ticket"></i></div>
      <div class="kpi-value"><?= (int)$ticketCount ?></div>
      <div class="kpi-label">Total Tickets</div>
    </div>
    <div class="kpi-card kpi-purple">
      <div class="kpi-icon"><i class="fa-solid fa-flag-checkered"></i></div>
      <div class="kpi-value"><?= (int)$currentMilestone ?></div>
      <div class="kpi-label">Current Milestone</div>
    </div>
    <div class="kpi-card kpi-green">
      <div class="kpi-icon"><i class="fa-solid fa-database"></i></div>
      <div class="kpi-value" style="font-size:13px; font-weight:700; word-break:break-all; margin-top:10px;">
        <?= $meta['last_file'] ? h($meta['last_file']) : 'None' ?>
      </div>
      <div class="kpi-label">Last Backup File</div>
    </div>
    <div class="kpi-card kpi-gray">
      <div class="kpi-icon"><i class="fa-regular fa-clock"></i></div>
      <div class="kpi-value" style="font-size:13px; font-weight:700; margin-top:10px;">
        <?= $meta['last_time'] ? h(date('M j, Y g:i A', strtotime($meta['last_time']))) : 'Never' ?>
      </div>
      <div class="kpi-label">Last Backup Time</div>
    </div>
  </section>

  <!-- ACTION CARDS -->
  <div class="backup-actions" style="padding: 0 24px;">

    <div class="card">
      <h3><i class="fa-solid fa-cloud-arrow-down"></i> Download Backup</h3>
      <p class="hint" style="margin: 8px 0 16px;">Creates a new .sql backup and downloads it immediately.</p>
      <!-- NOTE: this triggers a file download; showToast.js cannot intercept that response -->
      <a class="btn primary" href="databackup.php?action=backup_now" target="_blank" rel="noopener">
        <i class="fa-solid fa-download"></i> Create & Download
      </a>
      <p class="hint" style="margin-top: 8px;">Opens in a new tab.</p>
    </div>

    <div class="card">
      <h3><i class="fa-solid fa-rotate-left"></i> Restore Database</h3>
      <p class="hint" style="margin: 8px 0 16px;">
        <i class="fa-solid fa-triangle-exclamation" style="color:#e53935"></i>
        Restoring overwrites all existing tables (drops and recreates). Only upload trusted backups.
      </p>
      <form method="POST" enctype="multipart/form-data">
        <input type="file" name="sql_file" accept=".sql" required class="file-input" style="margin-bottom: 12px;">
        <br>
        <button type="submit" name="restore" class="btn danger">
          <i class="fa-solid fa-database"></i> Restore Database
        </button>
      </form>
    </div>

  </div>

</main>

<!-- Scripts -->
<script src="/assets/js/sidebar.js"></script>
<script src="/assets/js/notification.js"></script>
<script src="/assets/js/toast.js"></script>
<script src="/assets/js/showToast.js"></script>
<script src="/assets/js/functions.js"></script>

</body>
</html>
