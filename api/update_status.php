<?php
require __DIR__ . '/../config/db.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $id     = (int)($_POST['id'] ?? 0);
    $status = trim($_POST['status'] ?? '');

    if ($id <= 0 || $status === '') {
        die('Invalid request.');
    }

    // Allowed statuses (prevents typos from UI or malicious posts)
    $allowed = ['Pending', 'In Review', 'For Revisions', 'Completed', 'Closed - No Response'];
    if (!in_array($status, $allowed, true)) {
        die('Invalid status.');
    }

    // Fetch current SLA fields
    $stmt = $conn->prepare("
        SELECT status, sla_state, sla_last_started_at, sla_active_seconds
        FROM tickets
        WHERE id=?
        LIMIT 1
    ");
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $ticket = $stmt->get_result()->fetch_assoc();

    if (!$ticket) {
        die('Ticket not found.');
    }

    $oldStatus = $ticket['status'];
    $slaState  = $ticket['sla_state'];
    $lastStart = $ticket['sla_last_started_at'];
    $activeSec = (int)$ticket['sla_active_seconds'];

    // Helper: pause SLA (adds elapsed time to sla_active_seconds)
    $pauseSla = function () use ($conn, $id, $lastStart, $activeSec) {
        if ($lastStart) {
            $stmt = $conn->prepare("
                UPDATE tickets
                SET
                    sla_active_seconds = sla_active_seconds + TIMESTAMPDIFF(SECOND, sla_last_started_at, NOW()),
                    sla_state = 'paused',
                    sla_paused_at = NOW(),
                    waiting_started_at = NOW()
                WHERE id = ?
            ");
            $stmt->bind_param("i", $id);
            $stmt->execute();
        } else {
            // No last start recorded (edge case) — just mark paused
            $stmt = $conn->prepare("
                UPDATE tickets
                SET
                    sla_state = 'paused',
                    sla_paused_at = NOW(),
                    waiting_started_at = NOW()
                WHERE id = ?
            ");
            $stmt->bind_param("i", $id);
            $stmt->execute();
        }
    };

    // Helper: resume SLA
    $resumeSla = function () use ($conn, $id) {
        $stmt = $conn->prepare("
            UPDATE tickets
            SET
                sla_state = 'running',
                sla_last_started_at = NOW(),
                sla_paused_at = NULL,
                waiting_started_at = NULL
            WHERE id = ?
        ");
        $stmt->bind_param("i", $id);
        $stmt->execute();
    };

    // Helper: stop SLA (finalize + capture any running time)
    $stopSla = function () use ($conn, $id, $lastStart) {
        if ($lastStart) {
            $stmt = $conn->prepare("
                UPDATE tickets
                SET
                    sla_active_seconds = sla_active_seconds + TIMESTAMPDIFF(SECOND, sla_last_started_at, NOW()),
                    sla_state = 'stopped',
                    sla_last_started_at = NULL,
                    sla_paused_at = NULL,
                    waiting_started_at = NULL
                WHERE id = ?
            ");
            $stmt->bind_param("i", $id);
            $stmt->execute();
        } else {
            $stmt = $conn->prepare("
                UPDATE tickets
                SET
                    sla_state = 'stopped',
                    sla_last_started_at = NULL,
                    sla_paused_at = NULL,
                    waiting_started_at = NULL
                WHERE id = ?
            ");
            $stmt->bind_param("i", $id);
            $stmt->execute();
        }
    };

    // 1) Apply SLA state transitions based on NEW status
    if ($status === 'For Revisions') {
        // Pause SLA so vendor delays won't count against attorney
        $pauseSla();
    } elseif ($status === 'Pending' || $status === 'In Review') {
        // Resume SLA
        $resumeSla();
    } elseif ($status === 'Completed' || $status === 'Closed - No Response') {
        // Stop SLA and finalize
        $stopSla();
    }

    // 2) completed_at behavior
    if ($status === 'Completed' || $status === 'Closed - No Response') {
        $stmt = $conn->prepare("UPDATE tickets SET status=?, completed_at=NOW() WHERE id=?");
        $stmt->bind_param("si", $status, $id);
    } else {
        $stmt = $conn->prepare("UPDATE tickets SET status=?, completed_at=NULL WHERE id=?");
        $stmt->bind_param("si", $status, $id);
    }

    if ($stmt->execute()) {
        header('Location: ../tickets.php?msg=updated');
        exit;
    } else {
        die('DB error: ' . $stmt->error);
    }
}
?>