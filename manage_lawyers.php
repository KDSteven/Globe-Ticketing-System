<?php
session_start();
if (($_SESSION['lawyer_role'] ?? '') !== 'admin') {
    header("Location: lawyer_dashboard.php");
    exit;
}

require __DIR__ . '/config/db.php';
function h($s){ return htmlspecialchars($s, ENT_QUOTES, 'UTF-8'); }

$view = (isset($_GET['view']) && $_GET['view'] === 'archived') ? 'archived' : 'active';

$activeResult = $conn->query("
    SELECT id, name, email, role, created_at
    FROM lawyers
    WHERE archived_at IS NULL
    ORDER BY created_at DESC
");

$archivedResult = $conn->query("
    SELECT id, name, email, role, archived_at
    FROM lawyers
    WHERE archived_at IS NOT NULL
    ORDER BY archived_at DESC
");
?>
<!doctype html>
<html>
<head>
    <title>Manage Lawyers – Data Agreements & Contracts</title>
    <meta name="viewport" content="width=device-width,initial-scale=1" />
    <link rel="stylesheet" href="assets/css/admin.css">
    <link rel="stylesheet" href="assets/css/manage_lawyers.css">
    <link rel="stylesheet" href="assets/css/toast.css">
    <link rel="stylesheet" href="assets/css/notifications.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/7.0.1/css/all.min.css">
</head>

<body>

<?php
$brand = [
    "showMenuToggle" => true,
    "showNotif"      => true,
];
include __DIR__ . '/assets/partials/brandbar.php';
?>

<!-- SIDEBAR -->
<aside id="offcanvas" aria-hidden="true">
    <?php include __DIR__ . '/assets/partials/sidebar_common.php'; ?>
</aside>
<div id="sbBackdrop" aria-hidden="true"></div>

<!-- MAIN CONTENT -->
<main class="container-fluid page" id="mainContent">

<h1 class="page-title">Manage Lawyers</h1>

<div class="actions-bar" style="margin-bottom: 20px; display:flex; gap:10px;">
    <button class="btn primary" id="openAddLawyerModal">+ Add New Lawyer</button>

    <?php if ($view === 'active'): ?>
        <a class="btn secondary" href="manage_lawyers.php?view=archived" style="text-decoration:none;">Archived Accounts</a>
    <?php else: ?>
        <a class="btn secondary" href="manage_lawyers.php" style="text-decoration:none;">Back to Active</a>
    <?php endif; ?>
</div>

<!-- ACTIVE LAWYERS TABLE -->
<div class="card" style="padding:20px; <?= $view === 'archived' ? 'display:none;' : '' ?>">
    <table class="data-table">
        <thead>
            <tr>
                <th>Name</th>
                <th>Email (Globe Only)</th>
                <th>Role</th>
                <th>Created</th>
                <th style="width: 260px;">Actions</th>
            </tr>
        </thead>

        <tbody>
        <?php while ($row = $activeResult->fetch_assoc()): ?>
            <tr>
                <td><?= h($row['name']) ?></td>
                <td><?= h($row['email']) ?></td>
                <td><?= h($row['role']) ?></td>
                <td><?= h(date("M d, Y", strtotime($row['created_at']))) ?></td>
                <td>
                    <button class="btn small editLawyerBtn"
                            title="Edit Lawyer"
                            data-id="<?= (int)$row['id'] ?>"
                            data-name="<?= h($row['name']) ?>"
                            data-email="<?= h($row['email']) ?>"
                            data-role="<?= h($row['role']) ?>">
                        <i class="fa-solid fa-pen-to-square"></i>
                    </button>

                    <?php if ($row['role'] !== 'admin'): ?>
                    <button
                        type="button"
                        class="btn small danger openConfirmModal"
                        title="Archive Account"
                        data-action="api/lawyer_archive.php"
                        data-id="<?= (int)$row['id'] ?>"
                        data-title="Archive Lawyer Account"
                        data-message="Archive this account? You can restore it later."
                        data-confirm="Archive">
                        <i class="fa-solid fa-box-archive"></i>
                    </button>
                    <?php endif; ?>
                </td>
            </tr>
        <?php endwhile; ?>
        </tbody>
    </table>
</div>

<!-- ARCHIVED LAWYERS TABLE -->
<div class="card" style="padding:20px; <?= $view === 'active' ? 'display:none;' : '' ?>">
    <table class="data-table">
        <thead>
            <tr>
                <th>Name</th>
                <th>Email</th>
                <th>Role</th>
                <th>Archived</th>
                <th style="width: 260px;">Actions</th>
            </tr>
        </thead>

        <tbody>
        <?php while ($row = $archivedResult->fetch_assoc()): ?>
            <tr>
                <td><?= h($row['name']) ?></td>
                <td><?= h($row['email']) ?></td>
                <td><?= h($row['role']) ?></td>
                <td><?= h(date("M d, Y", strtotime($row['archived_at']))) ?></td>
                <td>
                    <button
                        type="button"
                        class="btn small openConfirmModal"
                        title="Restore Account"
                        data-action="api/lawyer_restore.php"
                        data-id="<?= (int)$row['id'] ?>"
                        data-title="Restore Lawyer Account"
                        data-message="Restore this account and allow access again?"
                        data-confirm="Restore">
                        <i class="fa-solid fa-rotate-left"></i>
                    </button>

                    <button
                        type="button"
                        class="btn small danger openConfirmModal"
                        title="Delete Permanently"
                        data-action="api/lawyer_purge.php"
                        data-id="<?= (int)$row['id'] ?>"
                        data-title="Delete Lawyer Account"
                        data-message="Permanently delete this account? This cannot be undone."
                        data-confirm="Delete">
                        <i class="fa-solid fa-trash"></i>
                    </button>
                </td>
            </tr>
        <?php endwhile; ?>
        </tbody>
    </table>
</div>

<!-- ADD NEW LAWYER MODAL -->
<div id="addLawyerModal" class="modal-overlay" style="display:none;">
    <div class="modal-box">
        <h2>Add New Lawyer</h2>
        <form method="POST" action="api/lawyer_add.php">
            <label>Name:</label>
            <input type="text" name="name" required>

            <label>Email (must be @globe.com.ph):</label>
            <input type="email" name="email" required>

            <label>Temporary Password:</label>
            <input type="password" name="password" required>

            <label>Role:</label>
            <select name="role">
                <option value="lawyer">Lawyer</option>
                <option value="admin">Admin</option>
            </select>

            <div class="modal-actions">
                <button type="submit" class="btn primary">Create Lawyer</button>
                <button type="button" id="closeAddLawyerModal" class="btn secondary">Cancel</button>
            </div>
        </form>
    </div>
</div>

<!-- EDIT LAWYER MODAL -->
<div id="editLawyerModal" class="modal-overlay" style="display:none;">
    <div class="modal-box">
        <h2>Edit Lawyer</h2>
        <form method="POST" action="api/lawyer_update.php">
            <input type="hidden" name="id" id="editLawyerId">

            <label>Name:</label>
            <input type="text" name="name" id="editLawyerName" required>

            <label>Email (cannot be edited):</label>
            <input type="email" id="editLawyerEmail" disabled>

            <label>Role:</label>
            <select name="role" id="editLawyerRole">
                <option value="lawyer">Lawyer</option>
                <option value="admin">Admin</option>
            </select>

            <div class="modal-actions">
                <button type="submit" class="btn primary">Save Changes</button>
                <button type="button" id="closeEditLawyerModal" class="btn secondary">Cancel</button>
            </div>
        </form>
    </div>
</div>

<!-- CONFIRM ACTION MODAL (template-based) -->
<div id="confirmActionModal" class="modal-overlay" style="display:none;">
  <div class="modal-box" style="max-width:400px;">
    <h2 id="confirmActionTitle">Confirm</h2>
    <p id="confirmActionLabel" style="margin: 15px 0; font-weight:500;"></p>

    <form method="POST" id="confirmActionForm">
      <input type="hidden" name="id" id="confirmActionId">
      <div class="modal-actions">
        <button type="submit" class="btn danger" id="confirmActionBtn">Confirm</button>
        <button type="button" id="closeConfirmActionModal" class="btn secondary">Cancel</button>
      </div>
    </form>
  </div>
</div>

<footer class="site-footer">
  <p>© 2025 Globe • AI and Privacy Governance • All Rights Reserved</p>
</footer>

</main>

<!-- JS Files needed for sidebar + notifications -->
<script src="assets/js/sidebar.js"></script>
<script src="assets/js/notification.js"></script>
<script src="assets/js/toast.js"></script>
<script src="assets/js/showToast.js"></script>
<script src="assets/js/functions.js"></script>
</body>
</html>
