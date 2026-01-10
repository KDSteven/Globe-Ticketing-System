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


<div id="sbBackdrop" aria-hidden="true"></div>

<main class="container-fluid page" id="mainContent">

<h1 class="page-title">Routing Rules</h1>

<div class="actions-bar" style="margin-bottom:20px;">
    <button class="btn primary" id="openAddRuleModal">+ Add Routing Rule</button>
</div>

<div class="card" style="padding:20px;">
    <div class="search-bar" style="margin-bottom:15px; max-width:300px;">
        <input 
            type="text" 
            id="routingSearch" 
            class="big-input" 
            placeholder="Search ticket type..."
            style="padding:8px 12px;"
        >
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
                        <span class="status-badge status-active">Active</span>
                    <?php else: ?>
                        <span class="status-badge status-disabled">Disabled</span>
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

            <label>Assign to Lawyer:</label>
            <select name="assigned_lawyer" required>
                <option value="" disabled selected>Assign a Lawyer… </option>
                <?php foreach ($lawyers as $l): ?>
                    <option value="<?= $l['id'] ?>"><?= h($l['name']) ?></option>
                <?php endforeach; ?>
            </select>

            <label>CC Lawyers:</label>
            <select name="cc_emails[]" id="addRuleCC">
                <option value="" disabled selected>Select a Lawyer... </option>
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

                <?php foreach ($routingConfig as $group => $items): ?>
                    <?php if ($group === "_LAWYERS") continue; ?>

                    <optgroup label="<?= h($group) ?>">

                        <?php foreach ($items as $ticketType => $lawyerKey): ?>
                            <option value="<?= h($ticketType) ?>"><?= h($ticketType) ?></option>
                        <?php endforeach; ?>

                    </optgroup>

                <?php endforeach; ?>
            </select>

            <label>Display Name (Admin label):</label>
            <input type="text" name="display_name" id="editRuleDisplay" placeholder="e.g. NDA (Standard)">


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

                <button class="btn primary">Save</button>
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

<script>
document.getElementById("openAddRuleModal").onclick = () =>
    document.getElementById("addRuleModal").style.display = "flex";

document.getElementById("closeAddRuleModal").onclick = () =>
    document.getElementById("addRuleModal").style.display = "none";

document.querySelectorAll(".editRuleBtn").forEach(btn => {
    btn.onclick = () => {
        document.getElementById("editRuleId").value = btn.dataset.id;
        document.getElementById("editRuleDisplay").value = btn.dataset.display || "";
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

<script>
document.getElementById("routingSearch").addEventListener("keyup", function() {
    const term = this.value.toLowerCase();
    const rows = document.querySelectorAll(".data-table tbody tr");

    rows.forEach(row => {
        const ticketType = row.cells[0].innerText.toLowerCase();
        const lawyer     = row.cells[1].innerText.toLowerCase();
        const status     = row.cells[2].innerText.toLowerCase();

        if (
            ticketType.includes(term) || 
            lawyer.includes(term) ||
            status.includes(term)
        ) {
            row.style.display = "";
        } else {
            row.style.display = "none";
        }
    });
});
</script>

</body>
</html>
