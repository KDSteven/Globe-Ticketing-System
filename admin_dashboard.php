<?php
session_start();

if (empty($_SESSION['lawyer_id']) || ($_SESSION['lawyer_role'] ?? '') !== 'admin') {
    header('Location: login.php');
    exit;
}

require __DIR__ . '/config/db.php';
require_once __DIR__ . '/utils/helpers.php';

/* ------------------------------------------------------
   READ FILTER INPUTS — validated against whitelist/regex
------------------------------------------------------ */
$validPeriods  = ['all', 'daily', 'monthly', 'quarterly', 'yearly'];
$validQuarters = ['Q1', 'Q2', 'Q3', 'Q4'];
$period  = in_array($_GET['period']  ?? '', $validPeriods,  true) ? $_GET['period']  : 'all';
$quarter = in_array($_GET['quarter'] ?? '', $validQuarters, true) ? $_GET['quarter'] : 'Q1';
$date    = preg_match('/^\d{4}-\d{2}-\d{2}$/', $_GET['date']  ?? '') ? $_GET['date']  : date('Y-m-d');
$month   = preg_match('/^\d{4}-\d{2}$/',       $_GET['month'] ?? '') ? $_GET['month'] : date('Y-m');
$year    = preg_match('/^\d{4}$/',             $_GET['year']  ?? '') ? $_GET['year']  : date('Y');

$where = dateCondition($period, $date, $month, $quarter, $year);
$whereSQL = "WHERE $where";

/* ------------------------------------------------------
   KPI QUERIES — FILTERED
------------------------------------------------------ */
function fetchCount($conn, $whereSQL, $condition)
{
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
   CONTRACT REVIEW RATE (Overall + Breakdown)
------------------------------------------------------ */

// Overall
$overallRow = $conn->query("
    SELECT 
        COUNT(*) AS total,
        SUM(status='Completed') AS reviewed
    FROM tickets
    $whereSQL
")->fetch_assoc();

$overallTotal    = (int)($overallRow['total'] ?? 0);
$overallReviewed = (int)($overallRow['reviewed'] ?? 0);
$overallRate     = $overallTotal > 0 ? round(($overallReviewed / $overallTotal) * 100, 2) : 0; //Do not add Past SLA in the rate

// Breakdown per contract_type
$contractBreakdown = $conn->query("
    SELECT 
        contract_type,
        COUNT(*) AS total,
        SUM(status='Completed') AS reviewed
    FROM tickets
    $whereSQL
    GROUP BY contract_type
    ORDER BY contract_type ASC
")->fetch_all(MYSQLI_ASSOC);


/* ------------------------------------------------------
   REVIEWER WORKLOAD
------------------------------------------------------ */
$whereSQL_T = preg_replace('/\bcreated_at\b/', 't.created_at', $whereSQL);

$reviewSQL = "
  SELECT
    COALESCE(l.name, t.assigned_lawyer) AS reviewer_name,
    t.assigned_lawyer,

    COUNT(*) AS total,

    SUM(CASE WHEN t.status <> 'Completed' THEN 1 ELSE 0 END) AS pending_count,

    SUM(CASE WHEN t.status <> 'Completed'
              AND t.due_date IS NOT NULL
              AND DATEDIFF(t.due_date, CURDATE()) = 1
        THEN 1 ELSE 0 END) AS due_tomorrow,

    SUM(CASE WHEN t.status <> 'Completed'
              AND t.due_date IS NOT NULL
              AND t.due_date < CURDATE()
        THEN 1 ELSE 0 END) AS overdue,

    ROUND(SUM(
      CASE WHEN t.status <> 'Completed'
           THEN GREATEST(TIMESTAMPDIFF(HOUR, t.created_at, NOW()), 0)
           ELSE 0
      END
    ), 1) AS pending_workload_hours,

    ROUND(AVG(
      CASE WHEN t.status = 'Completed' AND t.completed_at IS NOT NULL
           THEN TIMESTAMPDIFF(HOUR, t.created_at, t.completed_at)
           ELSE NULL
      END
    ), 1) AS avg_handle_hours,

    (
      SUM(CASE WHEN t.status <> 'Completed'
                AND t.due_date IS NOT NULL
                AND t.due_date < CURDATE()
          THEN 8 ELSE 0 END)
      +
      SUM(CASE WHEN t.status <> 'Completed'
                AND t.due_date IS NOT NULL
                AND DATEDIFF(t.due_date, CURDATE()) = 1
          THEN 5 ELSE 0 END)
      +
      SUM(CASE WHEN t.status <> 'Completed'
                AND t.due_date IS NOT NULL
                AND DATEDIFF(t.due_date, CURDATE()) = 2
          THEN 3 ELSE 0 END)
      +
      SUM(CASE WHEN t.status <> 'Completed' THEN 1 ELSE 0 END)
    ) AS risk_score

  FROM tickets t
  LEFT JOIN lawyers l
    ON l.email = t.assigned_lawyer
  $whereSQL_T
    AND t.assigned_lawyer IS NOT NULL
    AND t.assigned_lawyer <> ''
  GROUP BY t.assigned_lawyer, reviewer_name
  ORDER BY risk_score DESC, pending_workload_hours DESC
";


$reviewers = [];
$ticketCount = [];
$res = $conn->query($reviewSQL);

while ($row = $res->fetch_assoc()) {
    $reviewers[] = trim($row['reviewer_name']);

    $kpiData[] = [
        'risk_score'            => (int)$row['risk_score'],
        'pending_count'         => (int)$row['pending_count'],
        'due_tomorrow'          => (int)$row['due_tomorrow'],
        'overdue'               => (int)$row['overdue'],
        'avg_handle_hours'      => (float)$row['avg_handle_hours'],
        'pending_workload_hours' => (float)$row['pending_workload_hours'],
    ];
}

$hasVolumeData = !empty($volumeLabels) && !empty($volumeCounts);
$hasReviewerData = !empty($reviewers);

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
    } elseif ($period == "monthly") {
        $label = date("j", strtotime($row['completed_at']));
    } elseif ($period == "quarterly") {
        $label = date("Y-m", strtotime($row['completed_at']));
    } else { // yearly
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
    <link rel="stylesheet" href="assets/css/notifications.css">
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

        <div class="filter-left">
            <form method="GET" id="filterForm">
                <select name="period" id="periodSelect"
                    onchange="document.getElementById('filterForm').submit()">
                    <option value="all" <?= ($period == 'all' ? 'selected' : '') ?>>All Time</option>
                    <option value="daily" <?= ($period == 'daily' ? 'selected' : '') ?>>Daily</option>
                    <option value="monthly" <?= ($period == 'monthly' ? 'selected' : '') ?>>Monthly</option>
                    <option value="quarterly" <?= ($period == 'quarterly' ? 'selected' : '') ?>>Quarterly</option>
                    <option value="yearly" <?= ($period == 'yearly' ? 'selected' : '') ?>>Yearly</option>
                </select>

                <span id="dateContainer"></span>

                <button type="submit" class="btn primary">Apply</button>
            </form>
        </div>

        <button type="button" class="btn secondary add-holiday-btn" id="addHolidayBtn">
            <i class="fa-solid fa-calendar-plus"></i>
            Add Holiday
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
            <a href="manage_account.php">My Account</a>
            <a href="manage_lawyers.php">Manage Lawyers</a>
            <a href="manage_routing.php">Routing Rules</a>
            <a href="databackup.php">Data Backup</a>
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
                    <span class="kpi-label"><i class="fa-solid fa-circle-check kpi-icon"></i> Total Completed</span>
                    <span class="kpi-meta">as of <?= h(date('M j, Y')) ?></span>
                </div>
                <div class="kpi-value"><?= $completed ?></div>
            </div>
            <div class="kpi-card kpi-purple">
                <div class="kpi-header">
                    <span class="kpi-label"><i class="fa-solid fa-clock kpi-icon"></i> Completed On-Time</span>
                    <span class="kpi-meta">as of <?= h(date('M j, Y')) ?></span>
                </div>
                <div class="kpi-value"><?= $completedOnTime ?></div>
            </div>

            <div class="kpi-card kpi-gray">
                <div class="kpi-header">
                    <span class="kpi-label"><i class="fa-solid fa-triangle-exclamation kpi-icon"></i> Completed Past SLA</span>
                    <span class="kpi-meta">as of <?= h(date('M j, Y')) ?></span>
                </div>
                <div class="kpi-value"><?= $completedLate ?></div>
            </div>

            <div class="kpi-card kpi-amber">
                <div class="kpi-header">
                    <span class="kpi-label"><i class="fa-solid fa-calendar-xmark kpi-icon"></i> Overdue</span>
                    <span class="kpi-meta">as of <?= h(date('M j, Y')) ?></span>
                </div>
                <div class="kpi-value"><?= $overdue ?></div>
            </div>

            <div class="kpi-card kpi-red">
                <div class="kpi-header">
                    <span class="kpi-label"><i class="fa-solid fa-rotate-left kpi-icon"></i> For Revisions</span>
                    <span class="kpi-meta">as of <?= h(date('M j, Y')) ?></span>
                </div>
                <div class="kpi-value"><?= $revisions ?></div>
            </div>

            <div class="kpi-card kpi-blue">
                <div class="kpi-header">
                    <span class="kpi-label"><i class="fa-solid fa-hourglass-half kpi-icon"></i> Pending</span>
                    <span class="kpi-meta">as of <?= h(date('M j, Y')) ?></span>
                </div>
                <div class="kpi-value"><?= $pending ?></div>
            </div>

        </section>


        <!-- ===== Dashboard Main Grid (Option A) ===== -->
        <section class="dash-grid">

            <!-- LEFT: Contract Review -->
            <div class="dash-left">
                <section class="contract-card">
                    <div class="contract-card-top">
                        <div>
                            <div class="contract-card-title">Contract Review</div>
                            <div class="contract-card-rate"><?= number_format($overallRate, 2) ?>%</div>
                            <div class="contract-card-sub">Overall Contract Review Rate</div>
                            <div class="rate-bar-track">
                                <div class="rate-bar-fill" style="width: <?= min($overallRate, 100) ?>%"></div>
                            </div>
                        </div>

                        <div class="contract-card-icon">
                            <i class="fa-regular fa-file-lines"></i>
                        </div>
                    </div>

                    <div class="contract-card-table">
                        <div class="contract-row contract-head">
                            <span>Contract Type</span>
                            <span>Total</span>
                            <span>% Reviewed</span>
                        </div>

                        <?php if (empty($contractBreakdown)): ?>
                            <div class="contract-empty">No data for this date.</div>
                        <?php else: ?>
                            <?php foreach ($contractBreakdown as $r):
                                $t = (int)$r['total'];
                                $rev = (int)$r['reviewed'];
                                $rate = $t > 0 ? round(($rev / $t) * 100, 2) : 0;
                            ?>
                                <div class="contract-row">
                                    <span><?= h($r['contract_type']) ?></span>
                                    <span><?= $t ?></span>
                                    <span><?= number_format($rate, 2) ?>%</span>
                                </div>
                            <?php endforeach;
                                $grandTotal    = array_sum(array_column($contractBreakdown, 'total'));
                                $grandReviewed = array_sum(array_column($contractBreakdown, 'reviewed'));
                                $grandRate     = $grandTotal > 0 ? round(($grandReviewed / $grandTotal) * 100, 2) : 0;
                            ?>
                                <div class="contract-row contract-total">
                                    <span>Total</span>
                                    <span><?= $grandTotal ?></span>
                                    <span><?= number_format($grandRate, 2) ?>%</span>
                                </div>
                        <?php endif; ?>
                    </div>
                </section>
            </div>

            <!-- RIGHT: Reviewer Workload -->
            <div class="dash-right">
                <div class="chart-card">
                    <h3>Reviewer Workload</h3>

                    <?php if (!$hasReviewerData): ?>
                        <p class="empty-state">No data for this date.</p>
                    <?php else: ?>
                        <ul class="reviewer-list reviewer-list--v2">
                            <?php
                            $maxRisk = 1;
                            foreach ($kpiData as $k) $maxRisk = max($maxRisk, (int)$k['risk_score']);

                            foreach ($reviewers as $index => $reviewer) {
                                $k = $kpiData[$index];

                                $risk    = (int)$k['risk_score'];
                                $pending = (int)$k['pending_count'];
                                $due1    = (int)$k['due_tomorrow'];
                                $overdue = (int)$k['overdue'];
                                $avgH    = is_numeric($k['avg_handle_hours']) ? (float)$k['avg_handle_hours'] : 0.0;
                                $workH   = is_numeric($k['pending_workload_hours']) ? (float)$k['pending_workload_hours'] : 0.0;

                                $pct = $maxRisk > 0 ? ($risk / $maxRisk) * 100 : 0;

                                if ($risk >= 25 || $overdue > 0) {
                                    $riskLabel = "HIGH";
                                    $riskClass = "risk-high";
                                } elseif ($risk >= 12) {
                                    $riskLabel = "MED";
                                    $riskClass = "risk-med";
                                } else {
                                    $riskLabel = "LOW";
                                    $riskClass = "risk-low";
                                }

                                $initial = mb_strtoupper(mb_substr(trim($reviewer), 0, 1));
                                echo "
                                    <li class='rw-item'>
                                    <div class='rw-top'>
                                        <div class='rw-avatar'>{$initial}</div>
                                        <div class='rw-name'>" . h($reviewer) . "</div>
                                        <div class='rw-badges'>
                                        <span class='rw-badge {$riskClass}'>{$riskLabel}</span>
                                        <span class='rw-risk'>Risk <strong>{$risk}</strong></span>
                                        </div>
                                    </div>

                                    <div class='rw-bar'>
                                        <div class='rw-bar-fill' style='width: {$pct}%'></div>
                                    </div>

                                        <div class='rw-stats'>
                                        Pending <strong>{$pending}</strong>
                                        · Overdue <strong>{$overdue}</strong>
                                        · Due tomorrow <strong>{$due1}</strong>
                                        · Workload <strong>".number_format($workH,1)."h</strong>
                                        · Avg <strong>".number_format($avgH,1)."h</strong>
                                        </div>
                                    </li>";
                            }
                            ?>
                        </ul>


                    <?php endif; ?>
                </div>
            </div>
            <!-- FULL WIDTH: Monthly Ticket Volume -->
            <div class="dash-wide">
                <div class="chart-card">
                    <h3>Monthly Ticket Volume</h3>

                    <?php if (empty($volumeLabels) || array_sum($volumeCounts) === 0): ?>
                        <p class="empty-state">No data for this date.</p>
                    <?php else: ?>
                        <div class="chart-container">
                            <canvas id="ticketVolumeChart"></canvas>
                        </div>
                    <?php endif; ?>
                </div>
            </div>

        </section>


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
        const reviewerData = <?= json_encode(array_column($kpiData, 'risk_score')) ?>;

        if (window.volumeLabels && window.volumeLabels.length > 0) {
            loadTicketVolumeChart(window.volumeLabels, window.volumeCounts);
        }
    </script>

</body>

</html>