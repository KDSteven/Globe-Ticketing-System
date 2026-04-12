<?php
session_start();
require_once __DIR__ . '/../utils/auth.php';
requireAdmin();
require __DIR__ . '/../config/db.php';

$id              = (int)($_POST['id'] ?? 0);
$ticket_type     = trim($_POST['ticket_type'] ?? '');
$display_name    = trim($_POST['display_name'] ?? '');
$assigned_lawyer = (int)($_POST['assigned_lawyer'] ?? 0);
$active          = (int)($_POST['active'] ?? 1);

// CC emails (array)
$cc_emails = $_POST['cc_emails'] ?? [];
$cc_string = implode(",", array_filter($cc_emails));

if ($id === 0 || $ticket_type === '' || $assigned_lawyer === 0) {
    header("Location: /manage_routing.php?error=Missing+fields");
    exit;
}

// If display name is blank, default to ticket_type
if ($display_name === '') {
    $display_name = $ticket_type;
}

// Optional but recommended: prevent duplicate ticket_type keys
$check = $conn->prepare("SELECT id FROM routing_rules WHERE ticket_type = ? AND id <> ?");
$check->bind_param("si", $ticket_type, $id);
$check->execute();
$check->store_result();
if ($check->num_rows > 0) {
    header("Location: /manage_routing.php?error=Ticket+type+already+exists");
    exit;
}

$stmt = $conn->prepare("
    UPDATE routing_rules 
    SET ticket_type=?, display_name=?, assigned_lawyer=?, active=?, cc_emails=?
    WHERE id=?
");
$stmt->bind_param("ssissi", $ticket_type, $display_name, $assigned_lawyer, $active, $cc_string, $id);
$stmt->execute();

header("Location: /manage_routing.php?ok=Rule+Updated");
exit;
