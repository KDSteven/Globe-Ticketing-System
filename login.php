<?php
session_start();
$error = $_GET['error'] ?? '';
?>
<!doctype html>
<html lang="en">
<head>
  <meta charset="utf-8">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
  <link rel="stylesheet" href="/assets/css/login.css">
  <link rel="stylesheet" href="/assets/css/toast.css">
  <link rel="icon" type="image/x-icon" href="/assets/img/favicon/favicon.ico">
  <title>Login Page</title>
  <meta name="viewport" content="width=device-width,initial-scale=1">
</head>
<body>
  
<div id="toast-root" aria-live="polite" aria-atomic="true"></div>

<main class="auth">
  <!-- LEFT PANEL -->
<!-- LEFT PANEL -->
<section class="auth__panel">
  <div class="panel__inner">

    <!-- BRAND + SUBTITLE (shared) -->
    <h1 class="brand">
      <span class="brand-accent">AI & Privacy</span> Lawyers
    </h1>
    <p class="subtitle" id="panelSubtitle">Sign in to start your session.</p>



    <!-- ============= VIEW A: LOGIN ============= -->
  <div class="panel-switch" id="panelSwitch">
    <div id="view-login" class="view is-active">
      <form method="post" action="api/login.php" class="form" id="loginForm">
        <div class="field has-icon">
          <label for="email">Email Address</label>
          <i class="fa-solid fa-envelope input-icon" aria-hidden="true"></i>
          <input type="email" id="email" name="email" placeholder="Enter Your Globe Email"
                 autocomplete="username" required>
        </div>

        <div class="field has-icon" style="position:relative;">
          <label for="password">Password</label>
          <i class="fa-solid fa-lock input-icon" aria-hidden="true"></i>
          <input type="password" id="password" name="password" placeholder="Your password"
                 autocomplete="current-password" required>
          <button type="button" class="pw-toggle" data-toggle="#password" aria-label="Show password">
            <i class="fa-solid fa-eye" aria-hidden="true"></i>
          </button>
        </div>

        <div class="row-between">
          <button type="button" class="link-muted" id="gotoForgot">Forgot password?</button>
        </div>

        <button class="btn" type="submit">Sign in</button>
      </form>

      <p class="tos">
        By using this service, you understand and agree to the Globe Online Services
        <a href="#" class="link-muted">Terms of Use</a> and <a href="#" class="link-muted">Privacy Statement</a>.
      </p>
      <div class="meta"><a href="index.php">← Back to Portal</a></div>
      <footer class="site-footer">
        <p>© 2025 Globe • AI and Privacy Governance • All Rights Reserved</p>
      </footer>
    </div>

    <!-- ============= VIEW B: RESET (OTP) ============= -->
    <div id="view-reset" class="view is-hidden">
      <!-- STEP 1: Enter email, send OTP -->
      <form class="form" id="stepEmailForm" autocomplete="off">
        <div class="field has-icon">
          <label for="fp_email">Email Address</label>
          <i class="fa-solid fa-envelope input-icon" aria-hidden="true"></i>
          <input type="email" id="fp_email" name="email" placeholder="Enter your Globe Email" required>
        </div>

        <button class="btn" type="submit" id="sendOtpBtn">
          <span class="btn-text">Send OTP</span>
          <span class="btn-spinner" hidden><i class="fa-solid fa-circle-notch fa-spin"></i></span>
        </button>

        <p class="hint" id="emailStepHint">We’ll email a 6-digit code that expires in 10 minutes.</p>
      </form>

      <!-- STEP 2: Verify OTP + (then) New password -->
      <form class="form" id="stepResetForm" hidden autocomplete="off">
        <div class="field has-icon">
          <label for="otp">One-Time Passcode (OTP)</label>
          <i class="fa-solid fa-key input-icon" aria-hidden="true"></i>
          <input type="text" id="otp" name="otp" inputmode="numeric" pattern="\d{6}" maxlength="6"
                placeholder="6-digit code" required>
          <div class="hint">
            <span id="otpCountdown">10:00</span> •
            <button type="button" class="link-muted" id="resendOtpBtn" disabled>Resend OTP</button>
          </div>
        </div>

        <!-- Password block appears only after OTP is verified -->
        <div id="pwBlock" hidden aria-hidden="true">
          <div class="field has-icon" style="position:relative;">
            <label for="new_password">New Password</label>
            <i class="fa-solid fa-lock input-icon" aria-hidden="true"></i>
            <input type="password" id="new_password" name="new_password"
                  placeholder="At least 8 chars, with letters & numbers">
            <button type="button" class="pw-toggle" data-toggle="#new_password" aria-label="Show password">
              <i class="fa-solid fa-eye"></i>
            </button>
            <small class="hint">Use at least 8 characters with a mix of letters and numbers.</small>
          </div>

          <div class="field has-icon" style="position:relative;">
            <label for="confirm_password">Confirm Password</label>
            <i class="fa-solid fa-lock input-icon" aria-hidden="true"></i>
            <input type="password" id="confirm_password" name="confirm_password" placeholder="Re-type password">
            <button type="button" class="pw-toggle" data-toggle="#confirm_password" aria-label="Show password">
              <i class="fa-solid fa-eye"></i>
            </button>
          </div>

          <button class="btn" type="submit" id="resetBtn">
            <span class="btn-text">Reset Password</span>
            <span class="btn-spinner" hidden><i class="fa-solid fa-circle-notch fa-spin"></i></span>
          </button>
        </div>

        <!-- carry email from step 1 -->
        <input type="hidden" id="reset_email" name="email">
      </form>

      <div class="meta"><button type="button" class="link-muted" id="backToLogin">← Back to Sign in</button></div>
      <footer class="site-footer">
        <p>© 2025 Globe • AI and Privacy Governance • All Rights Reserved</p>
      </footer>
    </div>
  </div>
</div>
</section>

  <!-- RIGHT HERO -->
  <section class="auth__hero" aria-hidden="true">
    <img src="/img/Globetower.jpg" alt="">
  </section>
</main>
<script src="/assets/js/login.js" defer></script>
<script src="/assets/js/toast.js"></script>
<script src="/assets/js/showToast.js"></script>


</body>
</html>
