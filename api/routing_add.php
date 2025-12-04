<?php
session_start();

if (($_SESSION['lawyer_role'] ?? '') !== 'admin') {
    die("Unauthorized");
}

require __DIR__ . '/../config/db.php';

$ticket_type     = trim($_POST['ticket_type'] ?? '');
$assigned_lawyer = intval($_POST['assigned_lawyer'] ?? 0);
$active          = intval($_POST['active'] ?? 1);

// CC emails is an array from <select multiple>
$cc_emails = $_POST['cc_emails'] ?? [];
$cc_string = implode(",", array_filter($cc_emails));

if ($ticket_type === '' || $assigned_lawyer === 0) {
    header("Location: /manage_routing.php?error=Missing+fields");
    exit;
}

$stmt = $conn->prepare("
    INSERT INTO routing_rules (ticket_type, assigned_lawyer, active, cc_emails)
    VALUES (?, ?, ?, ?)
");
$stmt->bind_param("siis", $ticket_type, $assigned_lawyer, $active, $cc_string);
$stmt->execute();

header("Location: /manage_routing.php?success=Rule+Added");
exit;
