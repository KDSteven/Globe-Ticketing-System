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
/* Base reset + theming */
:root{
  --brand:#2E3192;
  --bg:#eef2f7;
  --card:#ffffff;
  --text:#0f172a;
  --muted:#64748b;
  --border:#cbd5e1;
  --border-strong:#94a3b8;
  --danger:#b91c1c;
  --success:#059669;
  --radius:12px;
  --shadow:0 6px 18px rgba(2,6,23,.10);
}

*{box-sizing:border-box}
html,body{height:100%}
body{
  margin:0;
  font-family: system-ui, -apple-system, Segoe UI, Roboto, Arial, sans-serif;
  background:var(--bg);
  color:var(--text);
  line-height:1.4;
}

/* Layout */
.wrap{
  max-width: 440px;
  margin: clamp(48px, 12vh, 120px) auto;
  padding: 0 16px;
}
.card{
  background:var(--card);
  border-radius:var(--radius);
  box-shadow:var(--shadow);
  padding: 24px 22px;
}

/* Heading */
h1{
  margin: 0 0 12px 0;
  color: var(--brand);
  font-size: clamp(22px, 2.2vw, 28px);
  letter-spacing:.2px;
}

/* Fields */
.field{ margin: 12px 0 14px; }
label{
  display:block;
  font-weight: 700;
  margin-bottom: 6px;
  color: var(--text);
}

/* Inputs */
input[type="email"],
input[type="password"],
input[type="text"]{
  width:100%;
  padding:12px 12px;
  border:1px solid var(--border);
  border-radius:10px;
  background:#fff;
  transition: border-color .15s ease, box-shadow .15s ease, background .15s ease;
  font-size: 15px;
}

/* States */
input::placeholder{ color:#94a3b8; }
input:focus{
  outline: none;
  border-color: var(--brand);
  box-shadow: 0 0 0 3px rgba(46,49,146,.15);
}
input:focus-visible{ outline: none; }

/* Autofill (Chrome) */
input:-webkit-autofill{
  -webkit-box-shadow: 0 0 0 1000px #fff inset, 0 0 0 3px rgba(46,49,146,.15);
  -webkit-text-fill-color: var(--text);
}

/* Button */
.btn{
  width:100%;
  margin-top: 12px;
  background: var(--brand);
  color:#fff;
  border:0;
  border-radius:10px;
  padding: 12px 14px;
  font-weight: 700;
  cursor:pointer;
  transition: transform .04s ease, background .15s ease, box-shadow .15s ease, opacity .15s ease;
  box-shadow: 0 2px 0 rgba(2,6,23,.10);
}
.btn:hover{ background:#25297e; }
.btn:active{ transform: translateY(1px); }
.btn:disabled,
.btn[aria-busy="true"]{
  opacity:.65;
  cursor:not-allowed;
}

/* Helper text + links */
.meta{ margin-top:12px; font-size:14px; color:var(--muted); }
a{ color:var(--brand); text-decoration:none; }
a:hover{ text-decoration:underline; }

/* Alerts / errors */
.err{ color:var(--danger); margin: 8px 0 0; font-weight:600; }
.alert{
  padding:10px 12px;
  border-radius:10px;
  margin: 0 0 12px;
  font-size:14px;
  border:1px solid;
}
.alert.error{ border-color:#fecaca; background:#fee2e2; color:#7f1d1d; }
.alert.success{ border-color:#bbf7d0; background:#dcfce7; color:#064e3b; }

/* Small screens: tighten spacing a bit */
@media (max-width:420px){
  .card{ padding:20px 16px; }
  .btn{ padding:11px 12px; }
}

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
      <div class="meta"><a href="index.php">← Back to Portal</a></div>
    </div>
  </div>
</body>
</html>
