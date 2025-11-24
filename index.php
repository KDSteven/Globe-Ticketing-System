<?php
$ok    = isset($_GET['ok']) ? (bool) $_GET['ok'] : false;
$error = isset($_GET['error']) ? urldecode($_GET['error']) : null;
?>
<!doctype html>
<html lang="en">
<head>
  <meta charset="utf-8" />
  <title>Data Agreements & Contracts — Request Portal</title>
  <meta name="viewport" content="width=device-width,initial-scale=1" />
  <meta name="color-scheme" content="light" />
  <link rel="stylesheet" href="/assets/css/toast.css">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
  <style>
    @import url('https://fonts.googleapis.com/css2?family=Inter:ital,opsz,wght@0,14..32,100..900;1,14..32,100..900&family=JetBrains+Mono:ital,wght@0,100..800;1,100..800&display=swap');
    /* ---------- Design Tokens ---------- */
    :root{
      --brand:        #2E3192;    /* Globe deep blue */
      --brand-2:      #3844c8;    /* accent */
      --ink:          #0f172a;    /* slate-900 */
      --muted:        #475569;    /* slate-600 */
      --bg:           #f1f3f6;    /* page */
      --card:         rgba(255,255,255,0.86);
      --border:       rgba(15,23,42,.12);
      --shadow:       0 8px 32px rgba(2,6,23,.10), 0 2px 10px rgba(2,6,23,.06);
      --radius:       16px;
      --radius-lg:    18px;
      --focus:        0 0 0 3px rgba(46,49,146,.22);
    }

    /* ---------- Base ---------- */
    * { box-sizing: border-box }
    html,body { height: 100% }
    body{
      margin: 0;
      font-family: system-ui,-apple-system,Segoe UI,Roboto,Arial,sans-serif;
      color: var(--ink);
      background: var(--bg);
      background: linear-gradient(180deg, #f5f6fa 0%, #d1d1d1ff 100%);
      font-family: "Inter", sans-serif !important;
    }
    a { color: inherit; text-decoration: none }
    a:focus-visible, button:focus-visible { outline: none; box-shadow: var(--focus); border-radius: 10px }

    /* ---------- Hero ---------- */
    .hero{
      position: relative;
      overflow: hidden;
      border-radius: 20px;
      margin: clamp(16px, 3vw, 28px) auto 18px;
      width: min(1120px, 92vw);
      padding: clamp(22px, 3.8vw, 36px);
      color: #fff;
      background:
        linear-gradient(135deg, rgba(46,49,146,.92), rgba(56,68,200,.88)),
        #1b2560;
      box-shadow: 0 8px 36px rgba(2,6,23,.15);
    }
    /* Hero background image */
    .hero::before{
      content:"";
      position:absolute; inset:0;
      background: url('/img/Globetower.jpg') center/cover no-repeat;
      mix-blend-mode: overlay;       /* keeps brand color dominant */
      opacity: .35;                  /* subtle */
    }
    /* Globe watermark */
    .hero::after{
      content:"";
      position:absolute;
      right: -60px; top: -30px;
      width: 360px; height: 360px;
      background: url('/img/Globe.png') center/contain no-repeat;
      opacity: .18; filter: drop-shadow(0 10px 20px rgba(0,0,0,.28));
      pointer-events: none;
    }
    .hero h1{
      margin: 0 0 8px 0;
      font-size: clamp(22px, 2.6vw, 34px);
      font-weight: 900;
      letter-spacing: .2px;
    }
    .hero p{
      margin: 0;
      font-size: clamp(14px, 1.2vw, 16px);
      opacity: .95;
    }

    /* ---------- Cards Row ---------- */
    .row{
      width: min(1120px, 92vw);
      margin: 0 auto 56px;
      display: grid;
      grid-template-columns: repeat(2, minmax(260px, 1fr));
      gap: clamp(14px, 2vw, 22px);
    }
    @media (max-width: 760px){
      .row{ grid-template-columns: 1fr }
    }

    /* ===== Refined Card Design ===== */
.card {
  position: relative;
  background: linear-gradient(180deg, rgba(255,255,255,0.96) 0%, rgba(245,247,255,0.9) 100%);
  border: 1px solid rgba(46,49,146,0.1);
  border-top: 4px solid var(--brand);
  border-radius: 18px;
  padding: 32px 26px 26px;
  box-shadow:
    0 4px 16px rgba(0,0,0,0.05),
    0 8px 24px rgba(46,49,146,0.08);
  transition: all 0.25s ease;
  overflow: hidden;
  backdrop-filter: blur(8px);
}

.card:hover {
  transform: translateY(-4px);
  box-shadow:
    0 10px 28px rgba(0,0,0,0.08),
    0 6px 18px rgba(46,49,146,0.15);
  border-top-color: var(--brand-2);
}

/* Icon style (top-left badge) */
.card .icon {
  width: 46px;
  height: 46px;
  display: grid;
  place-items: center;
  border-radius: 50%;
  background: linear-gradient(145deg, #e8ebff, #f6f7ff);
  border: 1px solid rgba(46,49,146,0.2);
  box-shadow: inset 0 1px 3px rgba(255,255,255,0.5);
  margin-bottom: 16px;
}

.card svg {
  width: 22px;
  height: 22px;
  fill: var(--brand);
}

/* Title and description */
.card h2 {
  font-size: 20px;
  font-weight: 700;
  color: var(--ink);
  margin-bottom: 6px;
}

.card p {
  color: var(--muted);
  font-size: 14px;
  line-height: 1.5;
  margin-bottom: 22px;
  max-width: 360px;
}

/* Button group */
.actions {
  display: flex;
  justify-content: flex-start;
}

.btn {
  display: inline-flex;
  align-items: center;
  justify-content: center;
  height: 40px;
  padding: 0 18px;
  border-radius: 10px;
  font-weight: 700;
  transition: all 0.2s ease;
}

/* Primary button */
.btn-primary {
  background: linear-gradient(135deg, var(--brand), #25297e);
  box-shadow: 0 4px 12px rgba(46,49,146,0.3);
  color: #fff;
  border: none;
}
.btn-primary:hover {
  background: linear-gradient(135deg, #25297e, var(--brand));
  box-shadow: 0 6px 18px rgba(46,49,146,0.35);
  transform: translateY(-1px);
}

/* Outline button */
.btn-outline {
  border: 2px solid var(--brand);
  color: var(--brand);
  background: #fff;
}
.btn-outline:hover {
  background: #f1f3ff;
  box-shadow: 0 4px 10px rgba(46,49,146,0.15);
}
    /* ---------- Footer ---------- */
    .footer{
      width: min(1120px, 92vw);
      margin: 12px auto 36px;
      color: #6b7280;
      font-size: 12.5px;
      text-align: center;
    }

    /* ---------- Subtle page background extras ---------- */
    .page{
      position: relative;
      min-height: 100dvh;
      display: grid;
      grid-template-rows: auto 1fr auto;
    }
    .page::before{
      /* soft dots/noise layer */
      content:"";
      position: fixed; inset: 0;
      background:
        radial-gradient(600px 280px at 15% 12%, rgba(56,68,200,.10), transparent 60%),
        radial-gradient(700px 300px at 90% 8%, rgba(46,49,146,.12), transparent 65%);
      pointer-events: none;
      z-index: 0;
    }

    @media (prefers-reduced-motion: reduce){
      .card, .btn, .row, .hero { transition: none !important }
    } 
  </style>
</head>
<body>
  
  <?php if ($ok): ?>
    <script>
    document.addEventListener('DOMContentLoaded', () => {
      toast.success('Request Submitted!', 'Your request was submitted successfully and will be reviewed shortly.');
    });
    </script>
    <?php elseif ($error): ?>
    <script>
    document.addEventListener('DOMContentLoaded', () => {
      toast.error('Submission failed', <?= json_encode($error) ?>);
    });
    </script>
  <?php endif; ?>

  <div class="page">
    <!-- Hero -->
    <header class="hero" role="banner" aria-label="Portal header">
      <h1>Data Agreements &amp; Contracts — Request Portal</h1>
      <p>Submit a new request or sign in as a reviewer to manage tickets.</p>
    </header>

    <!-- Cards -->
    <main class="row" role="main" aria-label="Primary actions">
      <!-- Submit Ticket -->
      <section class="card" aria-labelledby="submit-title">
        <div class="icon" aria-hidden="true">
          <!-- Paper plane icon -->
          <svg viewBox="0 0 24 24"><path d="M3.4 20.6c-.4.4-1 .2-1.1-.3L.0 3.6C-.1 2.9.7 2.4 1.3 2.7l21.1 9.2c.6.3.6 1.2 0 1.4L12.7 17l-3.3 6.6c-.3.6-1.2.6-1.5 0l-1.3-3.2-3.2.2c-.4 0-.7-.2-.9-.5l1.9- .5z"/></svg>
        </div>
        <h2 id="submit-title">Submit a Ticket</h2>
        <p>Requestors can file a new review request with attachments.</p>
        <div class="actions">
          <a class="btn btn-primary" href="/customer_portal.php" aria-label="Open Request Form">Open Request Form</a>
        </div>
      </section>

      <!-- Lawyer Login -->
      <section class="card" aria-labelledby="lawyer-title">
        <div class="icon" aria-hidden="true">
          <!-- Shield/check icon -->
          <svg viewBox="0 0 24 24"><path d="M12 2l7 3v6c0 5-3.8 9.7-7 11-3.2-1.3-7-6-7-11V5l7-3zm0 5l-4 4 1.4 1.4L12 9.8l2.6 2.6L16 11l-4-4z"/></svg>
        </div>
        <h2 id="lawyer-title">Lawyer Dashboard</h2>
        <p>Sign in to view KPIs, update statuses, and track due dates.</p>
        <div class="actions">
          <a class="btn btn-outline" href="/login.php" aria-label="Go to Lawyer Login">Lawyer Login</a>
        </div>
      </section>
    </main>

    <!-- Footer -->
    <footer class="footer">
      © 2025 Globe • AI and Privacy Governance • All Rights Reserved
    </footer>
  </div>
  <script src="/assets/js/toast.js"></script>
</body>
</html>
