<?php
session_start();
if (($_SESSION['lawyer_role'] ?? '') !== 'admin') {
    header("Location: lawyer_dashboard.php");
    exit;
}

require __DIR__ . '/config/db.php';
require_once __DIR__ . '/utils/helpers.php';

/* ---------------------------------------------------
   FETCH CONTRACT TYPES FROM CENTRAL CONFIG
--------------------------------------------------- */
$routingConfig = require __DIR__ . '/config/routing_rules.php'; 
// Example: ["DPA" => "ALEX", "DSA" => "FRANCINE", ...]
// Used ONLY for dropdown of ticket types, NOT for assignments

/* ---------------------------------------------------
   FETCH LAWYERS LIST
--------------------------------------------------- */
$lawyers = $conn->query("
    SELECT id, name, email
    FROM lawyers
    WHERE role='admin' OR role='lawyer'
    ORDER BY name ASC
")->fetch_all(MYSQLI_ASSOC);

/* ---------------------------------------------------
   FETCH ROUTING RULES FROM DB
--------------------------------------------------- */
$rules = $conn->query("
    SELECT r.id,
           r.ticket_type,
           r.display_name,
           r.active,
           r.assigned_lawyer,
           r.cc_emails,
           l.name AS lawyer_name
    FROM routing_rules r
    JOIN lawyers l ON r.assigned_lawyer = l.id
    ORDER BY r.ticket_type ASC
")->fetch_all(MYSQLI_ASSOC);


$success = $_GET['success'] ?? null;
$error   = $_GET['error'] ?? null;

?>
<!doctype html>
<html>
<head>
    <title>Routing Rules – Data Agreements & Contracts</title>
    <meta name="viewport" content="width=device-width,initial-scale=1" />
    <link rel="stylesheet" href="assets/css/admin.css">
    <link rel="stylesheet" href="assets/css/manage_lawyers.css">
    <link rel="stylesheet" href="assets/css/toast.css">
    <link rel="stylesheet" href="assets/css/notifications.css">
    <link rel="icon" type="image/x-icon" href="/assets/img/favicon/favicon.ico">
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
<!-- SIDEBAR -->
<aside id="offcanvas" aria-hidden="true">
    <?php include __DIR__ . '/assets/partials/sidebar_common.php'; ?>
</aside>
<div id="sbBackdrop" aria-hidden="true"></div>

<main class="container-fluid page" id="mainContent">

<h1 class="page-title">Routing Rules</h1>

<div class="actions-bar" style="margin-bottom:20px;">
    <button class="btn primary" id="openAddRuleModal">+ Add Routing Rule</button>
</div>

<div class="card" style="padding:20px;">
    <div class="search-input-wrap" style="max-width:300px; margin-bottom:15px;">
        <i class="fa-solid fa-magnifying-glass search-icon"></i>
        <input type="text" id="routingSearch" placeholder="Search ticket type…">
    </div>
    <div class="table-scroll">
        <table class="data-table">
            <thead>
            <tr>
                <th>Ticket Type</th>
                <th>Assigned Lawyer</th>
                <th>Status</th>
                <th width="240px">Actions</th>
            </tr>
            </thead>

            <tbody>
            <?php foreach ($rules as $row): ?>
            <tr>
                <td><?= h(($row['display_name'] ?? '') ?: $row['ticket_type']) ?></td>
                <td><?= h($row['lawyer_name']) ?></td>
                <td>
                    <?php if ($row['active']): ?>
                        <span class="status-badge badge-active">Active</span>
                    <?php else: ?>
                        <span class="status-badge badge-disabled">Disabled</span>
                    <?php endif; ?>
                </td>

                <td>
                <button class="btn small editRuleBtn"
                    title="Edit"
                    data-id="<?= (int)$row['id'] ?>"
                    data-type="<?= h($row['ticket_type']) ?>"
                    data-display="<?= h($row['display_name'] ?? '') ?>"
                    data-lawyer="<?= (int)$row['assigned_lawyer'] ?>"
                    data-active="<?= (int)$row['active'] ?>"
                    data-cc="<?= h($row['cc_emails'] ?? '') ?>"
                >
                    <i class="fa-solid fa-pen-to-square"></i>
                </button>

                    <button class="btn small danger deleteRuleBtn"
                            title="Delete"
                            data-id="<?= $row['id'] ?>"
                            data-type="<?= h($row['ticket_type']) ?>">
                        <i class="fa-solid fa-trash"></i>
                    </button>
                </td>
            </tr>
            <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>


<footer class="site-footer">
  <p>© 2025 Globe • AI and Privacy Governance • All Rights Reserved</p>
</footer>

</main>

<!-- ADD RULE MODAL -->
<div id="addRuleModal" class="modal-overlay" style="display:none;">
    <div class="modal-box">
        <h2>Add Routing Rule</h2>

        <form method="POST" action="api/routing_add.php">

            <div class="form-group">
                <label>Ticket Type:</label>
                <select name="ticket_type" required>
                    <option value="" disabled selected>Select type…</option>
                    <?php foreach ($routingConfig as $group => $items): ?>
                        <?php if ($group === "_LAWYERS") continue; ?>
                        <optgroup label="<?= h($group) ?>">
                            <?php foreach ($items as $ticketType => $lawyerKey): ?>
                                <option value="<?= h($ticketType) ?>"><?= h($ticketType) ?></option>
                            <?php endforeach; ?>
                        </optgroup>
                    <?php endforeach; ?>
                </select>
            </div>

            <div class="form-group">
                <label>Assign to Lawyer:</label>
                <select name="assigned_lawyer" required>
                    <option value="" disabled selected>Assign a Lawyer…</option>
                    <?php foreach ($lawyers as $l): ?>
                        <option value="<?= $l['id'] ?>"><?= h($l['name']) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>

            <div class="form-group">
                <label>CC Lawyers:</label>
                <select name="cc_emails[]" id="addRuleCC">
                    <option value="" disabled selected>Select a Lawyer…</option>
                    <?php foreach ($lawyers as $l): ?>
                        <option value="<?= h($l['email']) ?>"><?= h($l['email']) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>

            <div class="form-group">
                <label>Status:</label>
                <select name="active">
                    <option value="1">Active</option>
                    <option value="0">Disabled</option>
                </select>
            </div>

            <div class="modal-actions">
                <button type="submit" class="btn primary">Save Rule</button>
                <button type="button" id="closeAddRuleModal" class="btn secondary">Cancel</button>
            </div>

        </form>
    </div>
</div>

<!-- EDIT RULE MODAL -->
<div id="editRuleModal" class="modal-overlay" style="display:none;">
    <div class="modal-box">
        <h2>Edit Routing Rule</h2>

        <form method="POST" action="api/routing_update.php">

            <input type="hidden" name="id" id="editRuleId">

            <div class="form-group">
                <label>Ticket Type:</label>
                <select name="ticket_type" id="editRuleType" required>
                    <?php foreach ($routingConfig as $group => $items): ?>
                        <?php if ($group === "_LAWYERS") continue; ?>
                        <optgroup label="<?= h($group) ?>">
                            <?php foreach ($items as $ticketType => $lawyerKey): ?>
                                <option value="<?= h($ticketType) ?>"><?= h($ticketType) ?></option>
                            <?php endforeach; ?>
                        </optgroup>
                    <?php endforeach; ?>
                </select>
            </div>

            <div class="form-group">
                <label>Display Name (Admin label):</label>
                <input type="text" name="display_name" id="editRuleDisplay" placeholder="e.g. NDA (Standard)">
            </div>

            <div class="form-group">
                <label>Assign to Lawyer:</label>
                <select name="assigned_lawyer" id="editRuleLawyer" required>
                    <?php foreach ($lawyers as $l): ?>
                        <option value="<?= $l['id'] ?>"><?= h($l['name']) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>

            <div class="form-group">
                <label>CC Lawyers:</label>
                <select name="cc_emails[]" id="editRuleCC">
                    <?php foreach ($lawyers as $l): ?>
                        <option value="<?= h($l['email']) ?>"><?= h($l['email']) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>

            <div class="form-group">
                <label>Status:</label>
                <select name="active" id="editRuleActive">
                    <option value="1">Active</option>
                    <option value="0">Disabled</option>
                </select>
            </div>

            <div class="modal-actions">
                <button type="submit" class="btn primary">Save</button>
                <button type="button" id="closeEditRuleModal" class="btn secondary">Cancel</button>
            </div>
        </form>
    </div>
</div>

<!-- DELETE ROUTE MODAL -->
<div id="deleteRuleModal" class="modal-overlay" style="display:none;">
    <div class="modal-box" style="max-width:400px;">
        <h2>Delete Routing Rule</h2>

        <p id="deleteRuleLabel" style="margin: 15px 0; font-weight:500;"></p>

        <form method="GET" action="api/routing_delete.php">
            <input type="hidden" name="id" id="deleteRuleId">

            <div class="modal-actions">
                <button class="btn danger">Delete</button>
                <button type="button" id="closeDeleteRuleModal" class="btn secondary">
                    Cancel
                </button>
            </div>
        </form>
    </div>
</div>


<!-- JS -->
<script src="/assets/js/sidebar.js"></script>
<script src="/assets/js/notification.js"></script>
<script src="/assets/js/Toast.js"></script>
<script src="/assets/js/showToast.js"></script>
<script src="assets/js/functions.js"></script>
<script src="/assets/js/manage-routing.js"></script>
<?php if (!empty($_GET['success'])): ?>
<script>toast.success("Success", <?= json_encode(h($_GET['success'])) ?>);</script>
<?php endif; ?>

</body>
</html>
