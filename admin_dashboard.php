<?php
session_start();

if (empty($_SESSION['lawyer_id']) || ($_SESSION['lawyer_role'] ?? '') !== 'admin') {
    header('Location: login.php');
    exit;
}

require __DIR__ . '/config/db.php';
function h($s){ return htmlspecialchars($s, ENT_QUOTES, 'UTF-8'); }

/* ------------------------------------------------------
   READ FILTER INPUTS
------------------------------------------------------ */
$period = $_GET['period'] ?? 'all';
$date     = $_GET['date']    ?? date('Y-m-d');
$month    = $_GET['month']   ?? date('Y-m');
$year     = $_GET['year']    ?? date('Y');
$quarter  = $_GET['quarter'] ?? 'Q1';

/* ------------------------------------------------------
   DATE CONDITION BUILDER
------------------------------------------------------ */
function dateCondition($period, $date, $month, $quarter, $year) {
    switch ($period) {

        case "all":
            return "1"; // no filtering

        case "daily":
            return "DATE(created_at) = '$date'";

        case "monthly":
            return "DATE_FORMAT(created_at,'%Y-%m') = '$month'";

        case "quarterly":
            $ranges = [
                'Q1' => ['01-01', '03-31'],
                'Q2' => ['04-01', '06-30'],
                'Q3' => ['07-01', '09-30'],
                'Q4' => ['10-01', '12-31']
            ];
            [$start, $end] = $ranges[$quarter];
            return "created_at BETWEEN '$year-$start' AND '$year-$end'";

        case "yearly":
            return "YEAR(created_at) = '$year'";
    }
}


$where = dateCondition($period, $date, $month, $quarter, $year);
$whereSQL = "WHERE $where";

/* ------------------------------------------------------
   KPI QUERIES — FILTERED
------------------------------------------------------ */
function fetchCount($conn, $whereSQL, $condition){
    return $conn->query("
        SELECT COUNT(*) c 
        FROM tickets 
        $whereSQL AND $condition
    ")->fetch_assoc()['c'];
}

/* ------------------------------------------------------
   COMPLETION TIMING METRICS
------------------------------------------------------ */

// Completed ON TIME
$completedOnTime = $conn->query("
    SELECT COUNT(*) AS c
    FROM tickets
    $whereSQL
      AND status='Completed'
      AND completed_at IS NOT NULL
      AND DATE(completed_at) <= DATE(due_date)
")->fetch_assoc()['c'];

// Completed LATE
$completedLate = $conn->query("
    SELECT COUNT(*) AS c
    FROM tickets
    $whereSQL
      AND status='Completed'
      AND completed_at IS NOT NULL
      AND DATE(completed_at) > DATE(due_date)
")->fetch_assoc()['c'];

$completed = fetchCount($conn, $whereSQL, "status='Completed'");
$overdue   = fetchCount($conn, $whereSQL, "due_date < CURDATE() AND status <> 'Completed'");
$revisions = fetchCount($conn, $whereSQL, "status='For Revisions'");
$pending   = fetchCount($conn, $whereSQL, "status='Pending'");

/* ------------------------------------------------------
   REVIEWER WORKLOAD
------------------------------------------------------ */
$reviewSQL = "
    SELECT assigned_lawyer, COUNT(*) AS total
    FROM tickets
    $whereSQL
      AND assigned_lawyer IS NOT NULL
      AND assigned_lawyer <> ''
    GROUP BY assigned_lawyer
    ORDER BY total DESC
";

$reviewers = [];
$ticketCount = [];
$res = $conn->query($reviewSQL);

while ($row = $res->fetch_assoc()) {
    $clean = preg_replace('/<[^>]*>/', '', $row['assigned_lawyer']);
    $reviewers[] = trim($clean);
    $ticketCount[] = (int)$row['total'];
}

/* ------------------------------------------------------
   TAT — BAR CHART (OPTION A: Only show days with data)
------------------------------------------------------ */

$tatSQL = "
    SELECT 
        created_at,
        completed_at,
        TIMESTAMPDIFF(SECOND, created_at, completed_at) / 86400 AS tat
    FROM tickets
    $whereSQL 
      AND status='Completed' 
      AND completed_at IS NOT NULL
";

$tres = $conn->query($tatSQL);

$rawTAT = [];
while ($row = $tres->fetch_assoc()) {

    if ($period == "daily") {
        $label = date("H", strtotime($row['completed_at']));
    } 
    elseif ($period == "monthly") {
        $label = date("j", strtotime($row['completed_at']));
    } 
    elseif ($period == "quarterly") {
        $label = date("Y-m", strtotime($row['completed_at']));
    } 
    else { // yearly
        $label = date("Y-m", strtotime($row['completed_at']));
    }

    if (!isset($rawTAT[$label])) {
        $rawTAT[$label] = [];
    }

    $rawTAT[$label][] = (float)$row['tat'];
}
/* ------------------------------------------------------
   DYNAMIC TICKET VOLUME BASED ON PERIOD
------------------------------------------------------ */

$volumeLabels = [];
$volumeCounts = [];

if ($period == "daily") {

    $volumeSQL = "
        SELECT HOUR(created_at) AS label, COUNT(*) AS total
        FROM tickets
        $whereSQL
        GROUP BY HOUR(created_at)
        ORDER BY label ASC
    ";

} elseif ($period == "monthly") {

    $volumeSQL = "
        SELECT DAY(created_at) AS label, COUNT(*) AS total
        FROM tickets
        $whereSQL
        GROUP BY DAY(created_at)
        ORDER BY label ASC
    ";

} elseif ($period == "quarterly") {

    $volumeSQL = "
        SELECT DATE_FORMAT(created_at, '%Y-%m') AS label, COUNT(*) AS total
        FROM tickets
        $whereSQL
        GROUP BY DATE_FORMAT(created_at, '%Y-%m')
        ORDER BY label ASC
    ";

} else { // yearly

    $volumeSQL = "
        SELECT DATE_FORMAT(created_at, '%Y-%m') AS label, COUNT(*) AS total
        FROM tickets
        $whereSQL
        GROUP BY DATE_FORMAT(created_at, '%Y-%m')
        ORDER BY label ASC
    ";
}

$vres = $conn->query($volumeSQL);

while ($row = $vres->fetch_assoc()) {
    $volumeLabels[] = $row['label'];
    $volumeCounts[] = (int)$row['total'];
}

?>

<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8" />
    <title>Admin Dashboard – Data Agreements & Contracts</title>
    <meta name="viewport" content="width=device-width,initial-scale=1" />
    <link rel="stylesheet" href="assets/css/admin.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/7.0.1/css/all.min.css">
    <link rel="icon" type="image/x-icon" href="/assets/img/favicon/favicon.ico">
    
</head>

<body>

<?php
$brand = [
    'showMenuToggle' => true,
    'showNotif'      => true,
];
include __DIR__ . '/assets/partials/brandbar.php';
?>
<?php
// Fetch the lawyer's name from the database using the session ID
$userName = '';
if (!empty($_SESSION['lawyer_id'])) {
    $stmt = $conn->prepare("SELECT name FROM lawyers WHERE id = ?");
    $stmt->bind_param("i", $_SESSION['lawyer_id']);
    $stmt->execute();
    $result = $stmt->get_result();
    if ($row = $result->fetch_assoc()) {
        $userName = $row['name'];
    }
    $stmt->close();
}
?>

<div class="welcome-message">
    <p>Hello Admin, <strong><?= h($userName) ?>!</strong></p>
</div>


<div class="date-filter-bar">
    <form method="GET" id="filterForm">

        <!-- PERIOD SELECTOR -->
        <select name="period" id="periodSelect" onchange="document.getElementById('filterForm').submit()">
            <option value="all" <?= ($period=='all'?'selected':'') ?>>All Time</option>
            <option value="daily"    <?= ($period=='daily'?'selected':'') ?>>Daily</option>
            <option value="monthly"  <?= ($period=='monthly'?'selected':'') ?>>Monthly</option>
            <option value="quarterly"<?= ($period=='quarterly'?'selected':'') ?>>Quarterly</option>
            <option value="yearly"   <?= ($period=='yearly'?'selected':'') ?>>Yearly</option>
        </select>

        <!-- DYNAMIC DATE INPUT -->
        <span id="dateContainer">
            <!-- injected automatically by JS -->
        </span>

        <button type="submit" class="btn primary">Apply</button>
    </form>
    <button type="button" class="btn secondary" id="addHolidayBtn">
        + Add Holiday
    </button>
</div>

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
        <a href="manage_account.php">Account Management</a>
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

    <!-- KPI GRID -->
    <section class="kpi-grid">

        <div class="kpi-card kpi-green">
            <div class="kpi-header">
                <span class="kpi-label">Total Completed</span>
                <span class="kpi-meta">as of <?= h(date('M j, Y')) ?></span>
            </div>
            <div class="kpi-value"><?= $completed ?></div>
        </div>
        <div class="kpi-card kpi-purple">
            <div class="kpi-header">
                <span class="kpi-label">Completed (On Time)</span>
                <span class="kpi-meta">as of <?= h(date('M j, Y')) ?></span>
            </div>
            <div class="kpi-value"><?= $completedOnTime ?></div>
        </div>

        <div class="kpi-card kpi-gray">
            <div class="kpi-header">
                <span class="kpi-label">Completed (Late)</span>
                <span class="kpi-meta">as of <?= h(date('M j, Y')) ?></span>
            </div>
            <div class="kpi-value"><?= $completedLate ?></div>
        </div>
        
        <div class="kpi-card kpi-amber">
            <div class="kpi-header">
                <span class="kpi-label">Overdue</span>
                <span class="kpi-meta">as of <?= h(date('M j, Y')) ?></span>
            </div>
            <div class="kpi-value"><?= $overdue ?></div>
        </div>

        <div class="kpi-card kpi-red">
            <div class="kpi-header">
                <span class="kpi-label">For Revisions</span>
                <span class="kpi-meta">as of <?= h(date('M j, Y')) ?></span>
            </div>
            <div class="kpi-value"><?= $revisions ?></div>
        </div>

        <div class="kpi-card kpi-blue">
            <div class="kpi-header">
                <span class="kpi-label">Pending</span>
                <span class="kpi-meta">as of <?= h(date('M j, Y')) ?></span>
            </div>
            <div class="kpi-value"><?= $pending ?></div>
        </div>

    </section>


<div class="chart-grid">
    <div class="chart-card">
        <h3>Reviewer Workload</h3>
        <ul class="reviewer-list">
            <?php
            $maxCount = !empty($ticketCount) ? max($ticketCount) : 1; // Avoid division by zero
            foreach ($reviewers as $index => $reviewer) {
                $count = $ticketCount[$index];
                $percentage = ($count / $maxCount) * 100;
                echo "<li class='reviewer-item'>
                        <div class='reviewer-info'>
                            <span class='reviewer-name'>" . h($reviewer) . "</span>
                            <span class='reviewer-count'>$count tickets</span>
                        </div>
                        <div class='countbar-container'>
                            <div class='countbar' style='width: {$percentage}%'></div>
                        </div>
                    </li>";
            }
            ?>
        </ul>
    </div>

    <div class="chart-card">
        <h3>Monthly Ticket Volume</h3>
        <div class="chart-container">
            <canvas id="ticketVolumeChart"></canvas>
        </div>
    </div>
</div>

<!-- DUE SOON MODAL -->
<div id="dueSoonModal" class="ds-modal">
  <div class="ds-modal-content">
    
    <h2 class="ds-modal-title">Tickets Due in 24 Hours</h2>

    <table class="ds-table">
      <tbody id="dueSoonTbody"></tbody>
    </table>

    <div class="ds-buttons">
      <button id="snoozeBtn" class="ds-btn ds-btn-secondary">Snooze (5s)</button>
      <button id="ackBtn" class="ds-btn ds-btn-primary">Acknowledge</button>
    </div>

  </div>
</div>

<!-- ADD HOLIDAY MODAL -->
<div id="holidayModal" class="modal-overlay" style="display:none;">
    <div class="modal-box">
        <h2>Add Sudden Holiday</h2>

        <form id="holidayForm" method="POST" action="/api/save_holiday.php">
            <label>Date:</label>
            <input type="date" name="date" required>

            <label>Description:</label>
            <input type="text" name="description" required>

            <div class="modal-actions">
                <button type="submit" class="btn primary">Save</button>
                <button type="button" id="closeHolidayModal" class="btn secondary">Cancel</button>
            </div>
        </form>
    </div>
</div>

<!-- Sound -->
<audio id="alertSound">
  <source src="/assets/sounds/notify.mp3" type="audio/mpeg">
</audio>

      <footer class="site-footer">
        <p>© 2025 Globe • AI and Privacy Governance • All Rights Reserved</p>
      </footer>

</main>
<script>
    window.filterData = {
        period: "<?= $period ?>",
        date: "<?= $date ?>",
        month: "<?= $month ?>",
        quarter: "<?= $quarter ?>",
        year: "<?= $year ?>"
    };
</script>

<script src="/assets/js/dateFilter.js"></script>

<script>
    // Call JS function using PHP values passed above
    renderDateInput(
        filterData.period,
        filterData.date,
        filterData.month,
        filterData.quarter,
        filterData.year
    );
</script>
<script>
    window.volumeLabels = <?= json_encode($volumeLabels) ?>;
    window.volumeCounts = <?= json_encode($volumeCounts) ?>;
</script>
<script src="/assets/js/sidebar.js"></script>
<script src="/assets/js/notification.js"></script>
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script src="/assets/js/charts.js"></script>
<script src="/assets/js/showToast.js"></script>
<script src="/assets/js/functions.js"></script>

<script>
    const reviewerLabels = <?= json_encode($reviewers) ?>;
    const reviewerData   = <?= json_encode($ticketCount) ?>;


    loadTicketVolumeChart(window.volumeLabels, window.volumeCounts);
</script>

</body>
</html>
