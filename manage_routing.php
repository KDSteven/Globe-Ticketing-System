<?php
session_start();
if (($_SESSION['lawyer_role'] ?? '') !== 'admin') {
    header("Location: lawyer_dashboard.php");
    exit;
}

require __DIR__ . '/config/db.php';

function h($s){ return htmlspecialchars($s, ENT_QUOTES, 'UTF-8'); }

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
    SELECT r.id, r.ticket_type, r.active,
           r.assigned_lawyer,
           r.cc_emails,   /* <-- add this */
           l.name AS lawyer_name
    FROM routing_rules r
    JOIN lawyers l ON r.assigned_lawyer = l.id
    ORDER BY r.ticket_type ASC
")->fetch_all(MYSQLI_ASSOC);

?>
<!doctype html>
<html>
<head>
    <title>Routing Rules – Data Agreements & Contracts</title>
    <meta name="viewport" content="width=device-width,initial-scale=1" />
    <link rel="stylesheet" href="assets/css/admin.css">
    <link rel="stylesheet" href="assets/css/manage_lawyers.css">
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
    <div class="sb-head">
        <span>Navigation</span>
        <button id="sbClose" aria-label="Close">✕</button>
    </div>

    <nav class="sb-nav">
        <a href="admin_dashboard.php">Admin Dashboard</a>
        <a href="tickets.php">All Tickets</a>
        <hr>
        <a href="manage_lawyers.php">Manage Lawyers</a>
        <a href="manage_routing.php" class="active">Routing Rules</a>
        <a href="manage_holidays.php">Holidays</a>
        <a href="settings.php">System Settings</a>
        <hr>
        <a href="/api/logout.php">Logout</a>
    </nav>
</aside>

<div id="sbBackdrop" aria-hidden="true"></div>

<main class="container-fluid page" id="mainContent">

<h1 class="page-title">Routing Rules</h1>

<div class="actions-bar" style="margin-bottom:20px;">
    <button class="btn primary" id="openAddRuleModal">+ Add Routing Rule</button>
</div>

<div class="card" style="padding:20px;">
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
    <td><?= h($row['ticket_type']) ?></td>
    <td><?= h($row['lawyer_name']) ?></td>
    <td><?= $row['active'] ? "Active" : "Disabled" ?></td>

    <td>
        <button class="btn small editRuleBtn"
            data-id="<?= $row['id'] ?>"
            data-type="<?= h($row['ticket_type']) ?>"
            data-lawyer="<?= $row['assigned_lawyer'] ?>"
            data-active="<?= $row['active'] ?>"
            data-cc="<?= h($row['cc_emails'] ?? '') ?>"
        >
            Edit
        </button>

        <button class="btn small danger deleteRuleBtn"
                data-id="<?= $row['id'] ?>"
                data-type="<?= h($row['ticket_type']) ?>">
            Delete
        </button>
    </td>
</tr>
<?php endforeach; ?>
</tbody>
</table>
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

            <label>Ticket Type:</label>
            <select name="ticket_type" required>
                <option value="">Select type…</option>
                <?php foreach ($routingConfig as $type => $lawyerKey): ?>
                    <option value="<?= h($type) ?>"><?= h($type) ?></option>
                <?php endforeach; ?>
            </select>

            <label>Assign to Lawyer:</label>
            <select name="assigned_lawyer" required>
                <?php foreach ($lawyers as $l): ?>
                    <option value="<?= $l['id'] ?>"><?= h($l['name']) ?></option>
                <?php endforeach; ?>
            </select>

            <label>CC Lawyers:</label>
            <select name="cc_emails[]" id="addRuleCC">
                <?php foreach ($lawyers as $l): ?>
                    <option value="<?= h($l['email']) ?>">
                        <?= h($l['email']) ?>
                    </option>
                <?php endforeach; ?>
            </select>

            <label>Status:</label>
            <select name="active">
                <option value="1">Active</option>
                <option value="0">Disabled</option>
            </select>

            <div class="modal-actions">
                <button class="btn primary">Save Rule</button>
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

            <label>Ticket Type:</label>
            <select name="ticket_type" id="editRuleType" required>
                <?php foreach ($routingConfig as $type => $lawyerKey): ?>
                    <option value="<?= h($type) ?>"><?= h($type) ?></option>
                <?php endforeach; ?>
            </select>

            <label>Assign to Lawyer:</label>
            <select name="assigned_lawyer" id="editRuleLawyer" required>
                <?php foreach ($lawyers as $l): ?>
                    <option value="<?= $l['id'] ?>"><?= h($l['name']) ?></option>
                <?php endforeach; ?>
            </select>

            <label>CC Lawyers:</label>
            <select name="cc_emails[]" id="editRuleCC">
                <?php foreach ($lawyers as $l): ?>
                    <option value="<?= h($l['email']) ?>">
                        <?= h($l['email']) ?>
                    </option>
                <?php endforeach; ?>
            </select>

            <label>Status:</label>
            <select name="active" id="editRuleActive">
                <option value="1">Active</option>
                <option value="0">Disabled</option>
            </select>

            <div class="modal-actions">
                <button class="btn primary">Update Rule</button>
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
<script src="assets/js/showToast.js"></script>
<script src="assets/js/functions.js"></script>

<script>
document.getElementById("openAddRuleModal").onclick = () =>
    document.getElementById("addRuleModal").style.display = "flex";

document.getElementById("closeAddRuleModal").onclick = () =>
    document.getElementById("addRuleModal").style.display = "none";

document.querySelectorAll(".editRuleBtn").forEach(btn => {
    btn.onclick = () => {
        document.getElementById("editRuleId").value = btn.dataset.id;
        document.getElementById("editRuleType").value = btn.dataset.type;
        document.getElementById("editRuleLawyer").value = btn.dataset.lawyer;
        document.getElementById("editRuleActive").value = btn.dataset.active;
        document.getElementById("editRuleModal").style.display = "flex";

        let cc = btn.dataset.cc ? btn.dataset.cc.split(",") : [];
        let ccSelect = document.getElementById("editRuleCC");

        [...ccSelect.options].forEach(opt => {
            opt.selected = cc.includes(opt.value);
        });
    };
});

document.getElementById("closeEditRuleModal").onclick = () =>
    document.getElementById("editRuleModal").style.display = "none";
</script>

<?php if (!empty($_GET['success'])): ?>
<script>
toast.success("Success", <?= json_encode($_GET['success']) ?>);
</script>
<?php endif; ?>

</body>
</html>
