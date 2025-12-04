<?php
session_start();
if (($_SESSION['lawyer_role'] ?? '') !== 'admin') {
    header("Location: lawyer_dashboard.php");
    exit;
}

require __DIR__ . '/config/db.php';
function h($s){ return htmlspecialchars($s, ENT_QUOTES, 'UTF-8'); }

$result = $conn->query("SELECT id, name, email, role, created_at FROM lawyers ORDER BY created_at DESC");
?>
<!doctype html>
<html>
<head>
    <title>Manage Lawyers – Data Agreements & Contracts</title>
    <meta name="viewport" content="width=device-width,initial-scale=1" />
    <link rel="stylesheet" href="assets/css/admin.css">
    <link rel="stylesheet" href="assets/css/manage_lawyers.css">
    <link rel="stylesheet" href="assets/css/toast.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/7.0.1/css/all.min.css">
</head>

<body>

<?php
$brand = [
    "showMenuToggle" => true,
    "showNotif"      => true, // ensures bell icon appears
];
include __DIR__ . '/assets/partials/brandbar.php';
?>

<!-- SIDEBAR -->
<aside id="offcanvas" aria-hidden="true">
    <div class="sb-head">
        <span>Navigation</span>
        <button id="sbClose" aria-label="Close">✕</button>
    </div>

    <nav class="sb-nav">
        <a href="admin_dashboard.php">Admin Dashboard</a>
        <a href="tickets.php">All Tickets</a>
        <hr>
        <a href="manage_lawyers.php">Manage Lawyers</a>
        <a href="manage_routing.php">Routing Rules</a>
        <a href="manage_holidays.php">Holidays</a>
        <a href="settings.php">System Settings</a>
        <hr>
        <a href="/api/logout.php">Logout</a>
    </nav>
</aside>

<div id="sbBackdrop" aria-hidden="true"></div>

<!-- MAIN CONTENT -->
<main class="container-fluid page" id="mainContent">

<h1 class="page-title">Manage Lawyers</h1>

<div class="actions-bar" style="margin-bottom: 20px;">
    <button class="btn primary" id="openAddLawyerModal">+ Add New Lawyer</button>
</div>

<div class="card" style="padding: 20px;">

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
        <?php while ($row = $result->fetch_assoc()): ?>
        <tr>
            <td><?= h($row['name']) ?></td>
            <td><?= h($row['email']) ?></td>
            <td><?= h($row['role']) ?></td>
            <td><?= h(date("M d, Y", strtotime($row['created_at']))) ?></td>
            <td>
                <button class="btn small editLawyerBtn" data-id="<?= $row['id'] ?>" data-name="<?= h($row['name']) ?>"
                        data-email="<?= h($row['email']) ?>"
                        data-role="<?= $row['role'] ?>">Edit</button>

                <?php if ($row['role'] !== 'admin'): ?>
                <a class="btn small danger"
                   onclick="return confirm('Delete this lawyer? This action cannot be undone.')"
                   href="api/lawyer_delete.php?id=<?= $row['id'] ?>">
                   Delete
                </a>
                <?php endif; ?>

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

<footer class="site-footer">
  <p>© 2025 Globe • AI and Privacy Governance • All Rights Reserved</p>
</footer>

</main>

<!-- JS Files needed for sidebar + notifications -->
<script src="assets/js/sidebar.js"></script>
<script src="assets/js/notification.js"></script>
<script src="assets/js/toast.js"></script>       <!-- MUST LOAD BEFORE showToast.js -->
<script src="assets/js/showToast.js"></script>   <!-- your URL-based toast handler -->
<script src="assets/js/functions.js"></script>
</body>
</html>
