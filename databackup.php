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
// Flash toast helpers (PRG)
// ---------------------------
function set_flash_toast(string $type, string $text): void {
    $_SESSION['flash_toast'] = ['type' => $type, 'text' => $text];
}
function pop_flash_toast(): ?array {
    if (empty($_SESSION['flash_toast'])) return null;
    $t = $_SESSION['flash_toast'];
    unset($_SESSION['flash_toast']);
    return $t;
}

// ---------------------------
// Handle actions
// ---------------------------
$action = $_GET['action'] ?? null;

// 1) Download backup (this response becomes a FILE, so no toast can run here)
if ($action === 'backup_now') {
    $result = create_backup_sql($conn);

    if ($result['ok']) {
        // update meta
        $meta = read_meta();
        $meta['last_file'] = basename($result['file']);
        $meta['last_time'] = date('c');
        write_meta($meta);

        // download immediately (this exits)
        download_file($result['file'], basename($result['file']));
    } else {
        // Can't toast here because it's a download request; just show a message if opened in tab
        http_response_code(500);
        echo "Backup failed: " . h($result['error'] ?? 'Unknown error');
        exit;
    }
}

// 2) Restore database (POST -> set flash -> redirect -> toast on GET)
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['restore'])) {
    if (!isset($_FILES['sql_file']) || $_FILES['sql_file']['error'] !== UPLOAD_ERR_OK) {
        set_flash_toast('error', 'Please upload a valid .sql file.');
        header('Location: databackup.php');
        exit;
    }

    $tmp  = $_FILES['sql_file']['tmp_name'];
    $name = $_FILES['sql_file']['name'];

    if (strtolower(pathinfo($name, PATHINFO_EXTENSION)) !== 'sql') {
        set_flash_toast('error', 'Only .sql files are allowed.');
        header('Location: databackup.php');
        exit;
    }

    $restore = run_sql_file($conn, $tmp);
    if ($restore['ok']) {
        set_flash_toast('success', 'Database restored successfully.');
    } else {
        set_flash_toast('error', $restore['error'] ?? 'Restore failed.');
    }

    header('Location: databackup.php');
    exit;
}

// ---------------------------
// Auto-backup check (runs on normal GET page load)
// ---------------------------
$auto = auto_backup_if_needed($conn);
if (!empty($auto['did_backup']) && !empty($auto['file'])) {
    // Optional: toast once when auto-backup happens
    // (If you don’t want this, remove this block)
    $autoText = "Auto-backup created at milestone {$auto['milestone']} tickets. File: " . basename($auto['file']);
    set_flash_toast('success', $autoText);
}

// ---------------------------
// Page data
// ---------------------------
$flash = pop_flash_toast();

$meta            = read_meta();
$ticketCount     = get_ticket_count($conn);
$currentMilestone = intdiv($ticketCount, BACKUP_EVERY) * BACKUP_EVERY;
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>Data Backup</title>

  <!-- Use absolute paths consistently (like your working pages) -->
  <link rel="stylesheet" href="/assets/css/admin.css">
  <link rel="stylesheet" href="/assets/css/notifications.css">
  <link rel="stylesheet" href="/assets/css/toast.css">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/7.0.1/css/all.min.css">

  <style>
    /* Page-only styling (doesn't touch sidebar/brandbar) */
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
// same pattern as admin_dashboard.php
$brand = [
  'showMenuToggle' => true,
  'showNotif'      => true,
];
include __DIR__ . '/assets/partials/brandbar.php';
?>

<!-- SIDEBAR (match admin_dashboard.php exactly) -->
<aside id="offcanvas" aria-hidden="true">
  <?php require_once __DIR__ . '/assets/partials/sidebar_common.php'; ?>
</aside>
<div id="sbBackdrop" aria-hidden="true"></div>

<!-- MAIN CONTENT (match admin_dashboard.php wrapper) -->
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
        <p><b>Last backup time:</b> <?php echo $meta['last_time'] ? h($meta['last_time']) : 'None'; ?></p>
      </div>

      <div class="col card">
        <h3>Download Backup Now</h3>
        <p class="hint">Creates a new backup and downloads it immediately.</p>

        <!-- IMPORTANT:
             open in NEW TAB so current page can show toast (download response exits) -->
        <a
          class="btn btn-primary"
          id="backupNowBtn"
          href="databackup.php?action=backup_now"
          target="_blank"
          rel="noopener"
        >Create & Download Backup</a>

        <p class="hint" style="margin-top:10px;">
          (Uses PHP export for reliability.)
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
          <button type="submit" name="restore" class="btn btn-danger">Restore from .sql</button>
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

<script>
  // Flash toast from PHP (restore / auto-backup)
  const flash = <?= json_encode($flash ?? null) ?>;

  function fireToast(text, type) {
    // Prefer your project toast function(s)
    if (typeof window.showToast === "function") return window.showToast(text, type);
    if (typeof window.toast === "function") return window.toast(text, type);

    // Fallback: basic toast if no function is found
    const box = document.createElement("div");
    box.textContent = text;
    box.style.position = "fixed";
    box.style.right = "16px";
    box.style.bottom = "16px";
    box.style.padding = "12px 14px";
    box.style.borderRadius = "10px";
    box.style.background = (type === "error") ? "#b91c1c" : "#2b2f8f";
    box.style.color = "#fff";
    box.style.zIndex = 99999;
    box.style.boxShadow = "0 8px 20px rgba(0,0,0,.2)";
    document.body.appendChild(box);
    setTimeout(() => box.remove(), 2500);
  }

  document.addEventListener("DOMContentLoaded", () => {
    // Show toast after restore / auto-backup (flash)
    if (flash && flash.text) {
      fireToast(flash.text, flash.type || "success");
    }

    // Show toast immediately when clicking download (page stays open because target=_blank)
    const btn = document.getElementById("backupNowBtn");
    if (btn) {
      btn.addEventListener("click", () => {
        fireToast("Generating backup… your download will start in a new tab.", "success");
      });
    }
  });
</script>

</body>
</html>
