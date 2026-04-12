<?php
session_start();
require_once __DIR__ . '/../utils/auth.php';
requireAdmin();
require __DIR__ . '/../config/db.php';

$id   = $_POST['id'];
$name = trim($_POST['name']);
$role = $_POST['role'];

$stmt = $conn->prepare("UPDATE lawyers SET name=?, role=? WHERE id=?");
$stmt->bind_param("ssi", $name, $role, $id);
$stmt->execute();

header("Location: ../manage_lawyers.php?ok=Lawyer updated");
exit;

