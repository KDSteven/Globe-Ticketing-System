<?php
session_start();
require_once __DIR__ . '/../utils/auth.php';
requireAdmin();
require __DIR__ . '/../config/db.php';

$id = $_GET['id'];

$stmt = $conn->prepare("DELETE FROM lawyers WHERE id=?");
$stmt->bind_param("i", $id);
$stmt->execute();

header("Location: ../manage_lawyers.php?ok=Lawyer deleted");
exit;
