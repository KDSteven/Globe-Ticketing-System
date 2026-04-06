<?php
declare(strict_types=1);
header('Content-Type: application/json');

require_once __DIR__ . '/../config/db.php';       // must define $conn = new mysqli(...)
require_once __DIR__ . '/../vendor/autoload.php'; // PHPMailer
$mailCfg = require __DIR__ . '/../config/mail.php';

use PHPMailer\PHPMailer\PHPMailer;

try {
  // ----------------------------
  // Parse JSON
  // ----------------------------
  $data  = json_decode(file_get_contents('php://input'), true, flags: JSON_THROW_ON_ERROR);
  $email = trim($data['email'] ?? '');

  if ($email === '') {
    echo json_encode(['ok' => false, 'error' => 'Email is required']); exit;
  }

  // ----------------------------
  // 1) Ensure account exists
  // ----------------------------
  $stmt = $conn->prepare('SELECT id, name FROM lawyers WHERE email=? LIMIT 1');
  $stmt->bind_param('s', $email);
  $stmt->execute();
  $user = $stmt->get_result()->fetch_assoc();
  $stmt->close();

  if (!$user) {
    echo json_encode(['ok' => false, 'error' => 'Email not found']); exit;
  }

  // ----------------------------
  // 2) Rate limit: 3 OTP requests per hour per IP+email
  // ----------------------------
  $ip = $_SERVER['REMOTE_ADDR'] ?? 'unknown';
  rate_limit($conn, 'otp_' . $ip . '_' . $email, 3, 3600, 'Too many password reset attempts. Please wait an hour before requesting a new code.');

  // ----------------------------
  // 3) Generate + upsert OTP (hashed)
  // ----------------------------
  $otp     = (string) random_int(100000, 999999);
  $otpHash = password_hash($otp, PASSWORD_BCRYPT);
  $expires = date('Y-m-d H:i:s', time() + 600); // 10 minutes

  // Upsert by email (require UNIQUE KEY on password_resets.email)
  $sql = "
    INSERT INTO password_resets (email, otp_hash, expires_at, attempts, created_at)
    VALUES (?, ?, ?, 0, NOW())
    ON DUPLICATE KEY UPDATE
      otp_hash=VALUES(otp_hash),
      expires_at=VALUES(expires_at),
      attempts=0,
      created_at=NOW()
  ";
  $stmt = $conn->prepare($sql);
  $stmt->bind_param('sss', $email, $otpHash, $expires);
  $stmt->execute();
  $stmt->close();

  // ----------------------------
  // 4) Send via PHPMailer
  // ----------------------------
  $m = new PHPMailer(true);
  $m->isSMTP();
  $m->Host       = $mailCfg['host'];
  $m->Port       = (int) $mailCfg['port'];
  $m->SMTPAuth   = true;
  $m->SMTPSecure = strtolower($mailCfg['encryption'] ?? 'tls'); // 'tls' or 'ssl'
  $m->Username   = $mailCfg['username'];
  $m->Password   = $mailCfg['password'];
  $m->CharSet    = 'UTF-8';

  $m->setFrom($mailCfg['from_email'], $mailCfg['from_name']);
  if (!empty($mailCfg['bcc'])) {
    $m->addBCC($mailCfg['bcc']);
  }
  $m->addAddress($email, $user['name'] ?? '');

  // ----- Email look & content -----
  $brandTitle = 'Data Agreements & Contracts';
  $primary    = '#2c288f';
  $ink        = '#0f172a';
  $muted      = '#475569';
  $border     = '#e2e8f0';
  $bg         = '#f8fafc';
  $white      = '#ffffff';

  $displayName = htmlspecialchars($user['name'] ?? '');
  $helloName   = $displayName ? (' ' . $displayName) : '';
  $otpPretty   = implode(' ', str_split($otp)); // e.g. "1 2 3 4 5 6"

  $m->Subject = 'Your Verification Code (expires in 10 minutes)';
  $m->isHTML(true);

  $m->Body = <<<HTML
<!doctype html>
<html>
<head>
  <meta charset="utf-8">
  <meta name="x-apple-disable-message-reformatting">
  <meta name="color-scheme" content="light">
  <meta name="supported-color-schemes" content="light">
  <title>OTP Verification</title>
  <style>
    @media (max-width:600px){
      .container{width:100% !important; margin:0 !important; border-radius:0 !important;}
      .pad{padding:20px !important;}
      .otp{font-size:28px !important; letter-spacing:6px !important;}
    }
  </style>
</head>
<body style="margin:0; padding:0; background:{$bg}; font-family:ui-sans-serif, -apple-system, Segoe UI, Roboto, Helvetica, Arial, 'Apple Color Emoji','Segoe UI Emoji';">
  <div style="display:none; max-height:0; overflow:hidden; opacity:0; color:transparent;">
    Use this code to verify your account. Expires in 10 minutes.
  </div>

  <table role="presentation" width="100%" cellspacing="0" cellpadding="0" style="background:{$bg};">
    <tr>
      <td align="center" style="padding:32px 16px;">
        <table class="container" role="presentation" width="640" cellspacing="0" cellpadding="0" style="width:640px; max-width:640px; background:{$white}; border-radius:12px; overflow:hidden; border:1px solid {$border};">
          <tr>
            <td style="background:{$primary}; color:#fff; padding:20px 24px; font-size:18px; font-weight:700;">
              {$brandTitle} — OTP Verification
            </td>
          </tr>

          <tr>
            <td class="pad" style="padding:28px 28px 8px 28px; color:{$ink};">
              <p style="margin:0 0 12px 0; font-size:14px; color:{$muted};">Good day{$helloName},</p>
              <p style="margin:0 0 18px 0; font-size:16px; line-height:1.5; color:{$ink};">
                Use the one-time verification code below to continue. This code will expire in <strong>10 minutes</strong>.
              </p>

              <table role="presentation" width="100%" cellspacing="0" cellpadding="0" style="margin:8px 0 16px 0;">
                <tr>
                  <td style="background:#f1f5f9; border:1px dashed {$border}; border-radius:10px; text-align:center; padding:18px;">
                    <div class="otp" style="font-size:32px; font-weight:800; letter-spacing:8px; color:{$ink};">
                      {$otpPretty}
                    </div>
                    <div style="font-size:12px; margin-top:8px; color:{$muted};">Enter this code on the verification page</div>
                  </td>
                </tr>
              </table>

              <table role="presentation" width="100%" cellspacing="0" cellpadding="0" style="border-top:1px solid {$border}; margin-top:8px;">
                <tr>
                  <td style="padding:14px 0; font-size:13px; color:{$muted};">
                    <strong style="display:inline-block; width:130px; color:{$ink};">Reason</strong>
                    Password reset / sign-in verification
                  </td>
                </tr>
                <tr>
                  <td style="border-top:1px solid {$border}; padding:14px 0; font-size:13px; color:{$muted};">
                    <strong style="display:inline-block; width:130px; color:{$ink};">Expiry</strong>
                    10 minutes from receipt
                  </td>
                </tr>
              </table>

              <p style="margin:16px 0 0 0; font-size:13px; color:{$muted};">
                If you didn’t request this code, you can safely ignore this email.
              </p>
            </td>
          </tr>

          <tr>
            <td style="padding:18px 28px 28px 28px; color:{$muted}; font-size:12px; border-top:1px solid {$border};">
              <div style="margin-bottom:6px;"><strong style="color:{$ink};">{$brandTitle}</strong></div>
              <div style="line-height:1.5;">
                This is an automated message. Replies to this email are not monitored.
              </div>
            </td>
          </tr>
        </table>
      </td>
    </tr>
  </table>
</body>
</html>
HTML;

  // Plain-text fallback
  $greeting   = $displayName ? "Hi {$displayName}," : "Hi,";
  $m->AltBody = $greeting . "\n\n"
    . "Your verification code is: {$otp}\n"
    . "It expires in 10 minutes. If you didn't request this, ignore this message.";

  // Send
  $m->send();

  echo json_encode(['ok' => true]); exit;

} catch (\PHPMailer\PHPMailer\Exception $e) {
  // PHPMailer-specific error
  echo json_encode(['ok' => false, 'error' => 'Mailer error: ' . $e->getMessage()]); exit;

} catch (\Throwable $e) {
  // Everything else
  echo json_encode(['ok' => false, 'error' => 'Server error: ' . $e->getMessage()]); exit;
}
