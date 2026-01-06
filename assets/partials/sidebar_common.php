<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

$role = $_SESSION['lawyer_role'] ?? '';
$dashboard = ($role === 'admin') ? 'admin_dashboard.php' : 'lawyer_dashboard.php';
?>

<div class="sb-head">
    <span>Navigation</span>
    <button id="sbClose" aria-label="Close">✕</button>
</div>

<nav class="sb-nav">

<?php if ($role === 'admin'): ?>

    <a href="admin_dashboard.php">Admin Dashboard</a>
    <a href="tickets.php">All Tickets</a>

    <hr>

    <!-- THIS IS WHAT YOU WERE MISSING -->
    <a href="manage_account.php">My Account</a>

    <a href="manage_lawyers.php">Manage Lawyers</a>
    <a href="manage_routing.php">Routing Rules</a>
    <a href="databackup.php">Data Backup</a>
    <!-- <a href="manage_holidays.php">Holidays</a>
    <a href="settings.php">System Settings</a> -->

<?php else: ?>

    <a href="<?= $dashboard ?>">Dashboard</a>
    <a href="tickets.php">All Tickets</a>
    <a href="tickets.php?status=Pending">Pending</a>
    <a href="tickets.php?status=For%20Revisions">For Revisions</a>
    <a href="tickets.php?status=Completed">Completed</a>
    <a href="tickets.php?status=Overdue">Overdue</a>

    <hr>

    <!-- LAWYERS ALSO SEE ACCOUNT PAGE -->
    <a href="manage_account.php">My Account</a>

<?php endif; ?>

    <hr>
    <a href="/api/logout.php">Logout</a>
</nav>
