<?php
session_start();
require_once __DIR__ . '/../utils/auth.php';
requireAdmin();
require __DIR__ . '/../config/db.php';

$ticket_type     = trim($_POST['ticket_type'] ?? '');
$assigned_lawyer = intval($_POST['assigned_lawyer'] ?? 0);
$active          = intval($_POST['active'] ?? 1);

$cc_emails = $_POST['cc_emails'] ?? [];
$cc_string = implode(",", array_filter($cc_emails));

if ($ticket_type === '' || $assigned_lawyer === 0) {
    header("Location: /manage_routing.php?error=Missing+fields");
    exit;
}

// PREVENT DUPLICATES
$check = $conn->prepare("SELECT id FROM routing_rules WHERE ticket_type = ?");
$check->bind_param("s", $ticket_type);
$check->execute();
$check->store_result();

if ($check->num_rows > 0) {
    header("Location: /manage_routing.php?error=Ticket+type+already+exists");
    exit;
}

// INSERT NEW RULE
$stmt = $conn->prepare("
    INSERT INTO routing_rules (ticket_type, assigned_lawyer, active, cc_emails)
    VALUES (?, ?, ?, ?)
");
$stmt->bind_param("siis", $ticket_type, $assigned_lawyer, $active, $cc_string);
$stmt->execute();

header("Location: /manage_routing.php?ok=Rule+Added");
exit;
