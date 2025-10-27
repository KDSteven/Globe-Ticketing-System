<?php
require __DIR__ . '/../config/db.php';
session_start();

$next = isset($_GET['next']) ? $_GET['next'] : '../admin_dashboard.php';
$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  $email = trim($_POST['email'] ?? '');
  $pass  = $_POST['password'] ?? '';

  if ($email === '' || $pass === '') {
    $error = 'Email and password are required.';
  } else {
    $stmt = $conn->prepare("SELECT id, name, email, pass_hash, role FROM lawyers WHERE email=? LIMIT 1");
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $res = $stmt->get_result();

    if ($row = $res->fetch_assoc()) {
      if (password_verify($pass, $row['pass_hash'])) {
        $_SESSION['lawyer_id']   = (int)$row['id'];
        $_SESSION['lawyer_name'] = $row['name'];
        $_SESSION['lawyer_role'] = $row['role'];
        header('Location: ' . $next);
        exit;
      }
    }
    $error = 'Invalid credentials.';
  }
}

// Redirect back to the login page with error
header('Location: ../login.php?error=' . urlencode($error));
exit;
