<?php
session_start();

if (empty($_SESSION['lawyer_id']) || ($_SESSION['lawyer_role'] ?? '') !== 'admin') {
    header('Location: login.php');
    exit;
}

require_once __DIR__ . '/config/db.php';
require_once __DIR__ . '/utils/db_backup.php';

if (!isset($conn) || !($conn instanceof mysqli)) {
    die("Database connection not found. Check config/db.php.");
}

function h($s){ return htmlspecialchars($s, ENT_QUOTES, 'UTF-8'); }

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

  <style>
    .backup-page { padding: 20px; }
    .backup-page .card{
      background:#fff;
      border-radius:10px;
      padding:16px;
      margin-bottom:16px;
      box-shadow:0 1px 6px rgba(0,0,0,.08);
    }
    .backup-page .row{ display:flex; gap:16px; flex-wrap:wrap; }
    .backup-page .col{ flex:1 1 360px; }
    .backup-page .btn{
      display:inline-block;
      padding:10px 14px;
      border-radius:8px;
      border:0;
      cursor:pointer;
      text-decoration:none;
    }
    .backup-page .btn-primary{ background:#2b2f8f; color:#fff; }
    .backup-page .btn-danger{ background:#b91c1c; color:#fff; }
    .backup-page .hint{ color:#555; font-size:.95rem; }
    .backup-page code{ background:#f3f4f6; padding:2px 6px; border-radius:6px; }
  </style>
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
  <div class="backup-page">

    <h2>Data Backup</h2>
    <p class="hint">
      Auto-backup runs when total tickets reach every <b><?php echo (int)BACKUP_EVERY; ?></b> tickets (50, 100, 150...).
    </p>

    <div class="row">
      <div class="col card">
        <h3>Status</h3>
        <p><b>Total tickets:</b> <?php echo (int)$ticketCount; ?></p>
        <p><b>Current milestone:</b> <?php echo (int)$currentMilestone; ?></p>
        <p><b>Last backup file:</b> <?php echo $meta['last_file'] ? h($meta['last_file']) : 'None'; ?></p>
        <p><b>Last backup time:</b> 
        <?php
        if ($meta['last_time']) {
            echo h(date('M j, Y \a\t g:i A', strtotime($meta['last_time'])));
        } else {
            echo 'None';
        }
        ?>

      </div>

      <div class="col card">
        <h3>Download Backup Now</h3>
        <p class="hint">Creates a new backup and downloads it immediately.</p>

        <!-- NOTE: this triggers download; showToast.js cannot show toast for that response -->
        <a class="btn btn-primary"
           href="databackup.php?action=backup_now"
           target="_blank"
           rel="noopener">
          Create & Download Backup
        </a>

        <p class="hint" style="margin-top:10px;">
          (Download opens in a new tab.)
        </p>
      </div>

      <div class="col card">
        <h3>Restore Database</h3>
        <p class="hint">
          ⚠️ Restoring will overwrite existing tables (drops and recreates). Only upload backups you trust.
        </p>
        <form method="POST" enctype="multipart/form-data">
          <input type="file" name="sql_file" accept=".sql" required />
          <br><br>
          <button type="submit" name="restore" class="btn btn-danger">Restore Database</button>
        </form>
      </div>
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
