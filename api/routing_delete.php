<?php
require __DIR__ . '/../config/db.php';

if (!isset($_GET['id'])) {
    die("Missing rule ID.");
}

$id = intval($_GET['id']);
    
// DELETE the rule
$stmt = $conn->prepare("DELETE FROM routing_rules WHERE id = ?");
$stmt->bind_param("i", $id);

if ($stmt->execute()) {
    header("Location: /manage_routing.php?success=Routing rule deleted.");
    exit;
} else {
    echo "Error deleting rule.";
}
