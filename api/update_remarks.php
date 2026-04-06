<?php
require __DIR__ . '/../config/db.php';
session_start();
if (empty($_SESSION['lawyer_id'])) {
    header("Location: ../login.php");
    exit;
}

// Ensure remarks + id are present
if (!isset($_POST['id']) || !isset($_POST['remarks'])) {
    die("Invalid request");
}

$id = (int)$_POST['id'];
$remarks = trim($_POST['remarks']);

// Prepare & update database
$stmt = $conn->prepare("UPDATE tickets SET remarks=? WHERE id=?");
$stmt->bind_param("si", $remarks, $id);
$stmt->execute();
$stmt->close();

// Redirect back to tickets list
header("Location: ../tickets.php");
exit;
