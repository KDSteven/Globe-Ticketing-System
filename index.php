<!doctype html>
<html lang="en">
<head>
  <meta charset="utf-8">
  <title>Contracts Portal</title>
  <meta name="viewport" content="width=device-width,initial-scale=1">
  <style>
    body{margin:0;font-family:system-ui,Segoe UI,Arial,sans-serif;background:#f1f3f6;color:#0f1621}
    .wrap{max-width:960px;margin:6vh auto;padding:0 16px}
    .hero{background:#2E3192;color:#fff;border-radius:14px;padding:24px 20px;margin-bottom:22px}
    h1{margin:0;font-size:28px}
    .grid{display:grid;grid-template-columns:repeat(auto-fit,minmax(260px,1fr));gap:18px}
    .card{background:#fff;border-radius:12px;box-shadow:0 4px 12px rgba(0,0,0,.07);padding:20px}
    .card h2{margin:0 0 8px 0}
    .card p{color:#475569;margin:0 0 14px 0}
    .btn{display:inline-block;background:#2E3192;color:#fff;text-decoration:none;padding:10px 14px;border-radius:8px;font-weight:700}
    .btn.ghost{background:#fff;color:#2E3192;border:1px solid #2E3192}
  </style>
</head>
<body>
  <div class="wrap">
    <div class="hero">
      <h1>Data Agreements & Contracts – Request Portal</h1>
      <p>Submit a new request or sign in as a reviewer to manage tickets.</p>
    </div>

    <div class="grid">
      <div class="card">
        <h2>Submit a Ticket</h2>
        <p>Requestors can file a new review request with attachments.</p>
        <a class="btn" href="/customer_portal.php">Open Request Form</a>
      </div>
      <div class="card">
        <h2>Lawyer Dashboard</h2>
        <p>Sign in to view KPIs, update statuses, and track due dates.</p>
        <a class="btn ghost" href="/login.php">Lawyer Login</a>
      </div>
    </div>
  </div>
</body>
</html>
