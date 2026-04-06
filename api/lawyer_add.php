<?php
require __DIR__ . '/../config/db.php';
session_start();
if (($_SESSION['lawyer_role'] ?? '') !== 'admin') exit;

$name  = trim($_POST['name']);
$email = trim($_POST['email']);
$pass  = trim($_POST['password']);
$role  = $_POST['role'];

// Validate Globe email
if (!preg_match('/@globe\.com\.ph$/', $email)) {
    die("Invalid email — must be a Globe corporate email.");
}

$hash = password_hash($pass, PASSWORD_DEFAULT);

$stmt = $conn->prepare("INSERT INTO lawyers (name, email, pass_hash, role) VALUES (?, ?, ?, ?)");
$stmt->bind_param("ssss", $name, $email, $hash, $role);
$stmt->execute();

header("Location: ../manage_lawyers.php?ok=Lawyer added successfully");
exit;
