<?php
require __DIR__ . '/../config/db.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $id     = (int)($_POST['id'] ?? 0);
    $status = trim($_POST['status'] ?? '');

    if ($id <= 0 || $status === '') {
        die('Invalid request.');
    }

    // Update statement — sets completed_at only when status = Completed
    if ($status === 'Completed') {
        $stmt = $conn->prepare("UPDATE tickets SET status=?, completed_at=NOW() WHERE id=?");
        $stmt->bind_param("si", $status, $id);
    } else {
        $stmt = $conn->prepare("UPDATE tickets SET status=?, completed_at=NULL WHERE id=?");
        $stmt->bind_param("si", $status, $id);
    }

    if ($stmt->execute()) {
        header('Location: ../admin_dashboard.php?msg=updated');
        exit;
    } else {
        die('DB error: ' . $stmt->error);
    }
}
?>
