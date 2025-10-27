<?php
session_start();
$error = $_GET['error'] ?? '';
?>
<!doctype html>
<html lang="en">
<head>
  <meta charset="utf-8">
  <title>Lawyer Login</title>
  <meta name="viewport" content="width=device-width,initial-scale=1">
  <style>
    body{margin:0;font-family:system-ui,Segoe UI,Arial,sans-serif;background:#eef2f7}
    .wrap{max-width:420px;margin:10vh auto;padding:0 16px}
    .card{background:#fff;border-radius:12px;box-shadow:0 4px 14px rgba(0,0,0,.08);padding:22px}
    h1{margin:0 0 14px 0;color:#2E3192}
    .field{margin:10px 0}
    label{display:block;font-weight:700;margin-bottom:6px}
    input{width:100%;padding:10px;border:1px solid #cbd5e1;border-radius:8px}
    .btn{width:100%;margin-top:12px;background:#2E3192;color:#fff;border:0;border-radius:8px;padding:10px 14px;font-weight:700;cursor:pointer}
    .err{color:#b91c1c;margin:8px 0 0 0;font-weight:600}
    .meta{margin-top:10px}
    a{color:#2E3192;text-decoration:none}
  </style>
</head>
<body>
  <div class="wrap">
    <div class="card">
      <h1>Lawyer Login</h1>
      <?php if ($error): ?><div class="err"><?= htmlspecialchars($error) ?></div><?php endif; ?>
      <form method="post" action="api/login.php">
        <div class="field">
          <label for="email">Email</label>
          <input type="email" id="email" name="email" required>
        </div>
        <div class="field">
          <label for="password">Password</label>
          <input type="password" id="password" name="password" required>
        </div>
        <button class="btn" type="submit">Sign in</button>
      </form>
      <div class="meta"><a href="/portal.php">← Back to Portal</a></div>
    </div>
  </div>
</body>
</html>
