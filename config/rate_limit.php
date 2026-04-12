<?php
/**
 * DB-backed rate limiter.
 *
 * @param mysqli $conn       Active DB connection
 * @param string $key        Unique bucket key (e.g. "login_127.0.0.1")
 * @param int    $max        Max allowed requests within the window
 * @param int    $window     Window size in seconds
 * @param string $message    Custom error message shown to the user
 *
 * Exits with HTTP 429 if the limit is exceeded.
 * - Browser requests  → styled HTML error page
 * - AJAX/JSON requests → JSON response
 */
function rate_limit(mysqli $conn, string $key, int $max, int $window, string $message = 'Too many requests. Please try again later.'): void
{
    $now         = time();
    $windowStart = $now - $window;

    // Purge expired buckets
    $stmt = $conn->prepare("DELETE FROM rate_limits WHERE window_start < ?");
    $stmt->bind_param("i", $windowStart);
    $stmt->execute();
    $stmt->close();

    $stmt = $conn->prepare("SELECT requests, window_start FROM rate_limits WHERE rl_key = ? LIMIT 1");
    $stmt->bind_param("s", $key);
    $stmt->execute();
    $row = $stmt->get_result()->fetch_assoc();
    $stmt->close();

    if (!$row) {
        $stmt = $conn->prepare("INSERT INTO rate_limits (rl_key, requests, window_start) VALUES (?, 1, ?)");
        $stmt->bind_param("si", $key, $now);
        $stmt->execute();
        $stmt->close();
        return;
    }

    if ((int)$row['window_start'] < $windowStart) {
        $stmt = $conn->prepare("UPDATE rate_limits SET requests = 1, window_start = ? WHERE rl_key = ?");
        $stmt->bind_param("is", $now, $key);
        $stmt->execute();
        $stmt->close();
        return;
    }

    if ((int)$row['requests'] >= $max) {
        $retryAfter = ((int)$row['window_start'] + $window) - $now;

        http_response_code(429);
        header("Retry-After: $retryAfter");

        // Detect AJAX / JSON requests
        $isAjax = (
            str_contains($_SERVER['HTTP_X_REQUESTED_WITH'] ?? '', 'XMLHttpRequest') ||
            str_contains($_SERVER['HTTP_ACCEPT']           ?? '', 'application/json') ||
            str_contains($_SERVER['CONTENT_TYPE']          ?? '', 'application/json')
        );

        if ($isAjax) {
            header('Content-Type: application/json; charset=utf-8');
            echo json_encode(['ok' => false, 'error' => $message]);
            exit;
        }

        // ── Styled HTML 429 page ──────────────────────────────────────
        $mins = (int)ceil($retryAfter / 60);
        $waitLabel = $mins <= 1 ? 'a moment' : "$mins minutes";
        $safeMsg   = htmlspecialchars($message, ENT_QUOTES, 'UTF-8');
        ?>
<!doctype html>
<html lang="en">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width,initial-scale=1">
  <title>429 – Too Many Requests</title>
  <link rel="icon" type="image/x-icon" href="/assets/img/favicon/favicon.ico">
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600;700;800&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/7.0.1/css/all.min.css">
  <style>
    *, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }

    body {
      font-family: 'Inter', system-ui, sans-serif;
      background: #f0f2ff;
      min-height: 100vh;
      display: flex;
      align-items: center;
      justify-content: center;
      padding: 24px;
    }

    .card {
      background: #fff;
      border-radius: 20px;
      box-shadow: 0 20px 60px rgba(46, 49, 146, 0.12);
      max-width: 480px;
      width: 100%;
      overflow: hidden;
      text-align: center;
    }

    .card-top {
      background: linear-gradient(135deg, #2E3192, #1fb6ff);
      padding: 40px 32px 36px;
      color: #fff;
    }

    .icon-ring {
      width: 72px;
      height: 72px;
      border-radius: 50%;
      background: rgba(255, 255, 255, 0.15);
      display: flex;
      align-items: center;
      justify-content: center;
      margin: 0 auto 20px;
      font-size: 30px;
    }

    .card-top h1 {
      font-size: 26px;
      font-weight: 800;
      letter-spacing: -0.3px;
      margin-bottom: 6px;
    }

    .card-top p {
      font-size: 14px;
      opacity: 0.88;
      line-height: 1.5;
    }

    .card-body {
      padding: 28px 32px 32px;
    }

    .message-box {
      background: #f4f5ff;
      border: 1px solid #dde0f7;
      border-radius: 12px;
      padding: 16px 20px;
      font-size: 14px;
      color: #1e2375;
      line-height: 1.6;
      margin-bottom: 20px;
      text-align: left;
    }

    .message-box i {
      margin-right: 8px;
      color: #2E3192;
    }

    .wait-badge {
      display: inline-flex;
      align-items: center;
      gap: 7px;
      background: #fff4e5;
      border: 1px solid #fcd9a0;
      color: #92520a;
      border-radius: 999px;
      padding: 6px 16px;
      font-size: 13px;
      font-weight: 600;
      margin-bottom: 24px;
    }

    .wait-badge i { font-size: 13px; }

    .btn-back {
      display: inline-flex;
      align-items: center;
      gap: 8px;
      background: #2E3192;
      color: #fff;
      border: none;
      border-radius: 10px;
      padding: 12px 28px;
      font-size: 15px;
      font-weight: 700;
      cursor: pointer;
      text-decoration: none;
      transition: background 0.2s;
    }

    .btn-back:hover { background: #1e2375; }

    .footer-note {
      margin-top: 20px;
      font-size: 12px;
      color: #9ca3af;
    }
  </style>
</head>
<body>
  <div class="card">
    <div class="card-top">
      <div class="icon-ring">
        <i class="fa-solid fa-shield-halved"></i>
      </div>
      <h1>Too Many Requests</h1>
      <p>Our security system has temporarily limited access from your connection.</p>
    </div>

    <div class="card-body">
      <div class="message-box">
        <i class="fa-solid fa-circle-info"></i><?= $safeMsg ?>
      </div>

      <div class="wait-badge">
        <i class="fa-regular fa-clock"></i>
        Please try again in <?= $waitLabel ?>
      </div>

      <br>

      <a href="javascript:history.back()" class="btn-back">
        <i class="fa-solid fa-arrow-left"></i> Go Back
      </a>

      <p class="footer-note">
        If you believe this is a mistake, please contact your system administrator.
      </p>
    </div>
  </div>
</body>
</html>
        <?php
        exit;
    }

    $stmt = $conn->prepare("UPDATE rate_limits SET requests = requests + 1 WHERE rl_key = ?");
    $stmt->bind_param("s", $key);
    $stmt->execute();
    $stmt->close();
}
