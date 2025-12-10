<?php
session_start();

if (($_SESSION['lawyer_role'] ?? '') !== 'admin') {
    die("Unauthorized");
}

require __DIR__ . '/../config/db.php';

$id              = intval($_POST['id'] ?? 0);
$ticket_type     = trim($_POST['ticket_type'] ?? '');
$assigned_lawyer = intval($_POST['assigned_lawyer'] ?? 0);
$active          = intval($_POST['active'] ?? 1);

// CC emails (array)
$cc_emails = $_POST['cc_emails'] ?? [];
$cc_string = implode(",", array_filter($cc_emails));

if ($id === 0 || $ticket_type === '' || $assigned_lawyer === 0) {
    header("Location: /manage_routing.php?error=Missing+fields");
    exit;
}

$stmt = $conn->prepare("
    UPDATE routing_rules 
    SET ticket_type=?, assigned_lawyer=?, active=?, cc_emails=?
    WHERE id=?
");
$stmt->bind_param("siisi", $ticket_type, $assigned_lawyer, $active, $cc_string, $id);
$stmt->execute();

header("Location: /manage_routing.php?ok=Rule+Updated");
exit;
