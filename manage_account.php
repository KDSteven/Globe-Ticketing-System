<?php
session_start();
if (!isset($_SESSION['lawyer_id'])) {
    header("Location: login.php");
    exit;
}

$name = $_SESSION['lawyer_name'];
$email = $_SESSION['lawyer_email'];
$role = $_SESSION['lawyer_role'];
?>

<!doctype html>
<html lang="en">
<head>
<meta charset="utf-8" />
<title>My Account – Data Agreements & Contracts</title>
<meta name="viewport" content="width=device-width, initial-scale=1" />
<link rel="stylesheet" href="assets/css/admin.css">
<link rel="stylesheet" href="assets/css/manage_account.css">
<link rel="stylesheet" href="assets/css/toast.css">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
</head>

<body>

<?php
$brand = [
  'showMenuToggle' => true,
  'showNotif'      => true,
];
include __DIR__ . '/assets/partials/brandbar.php';
?>

<!-- SIDEBAR -->
<aside id="offcanvas" aria-hidden="true">
    <?php include __DIR__ . '/assets/partials/sidebar_common.php'; ?>
</aside>
<div id="sbBackdrop" aria-hidden="true"></div>


<main class="container page">

<h1 class="page-title">My Account</h1>

<div class="account-grid">

  <!-- ===================== PROFILE ===================== -->
  <section class="card account-section">
    <h2><i class="fa-solid fa-user"></i> Profile</h2>
    <p><strong>Name:</strong> <?= htmlspecialchars($name) ?></p>
    <p><strong>Email:</strong> <?= htmlspecialchars($email) ?></p>
    <p><strong>Role:</strong> <?= htmlspecialchars($role) ?></p>
  </section>

  <!-- ===================== CHANGE PASSWORD ===================== -->
 <section class="card account-section">
    <h2><i class="fa-solid fa-key"></i> Change Password</h2>

    <form id="changePwForm" class="account-form">

        <div class="form-group">
            <label for="curr_pw">Current Password</label>
            <div class="input-wrap">
                <input type="password" id="curr_pw" required>
                <i class="fa-solid fa-lock icon"></i>
            </div>
        </div>

        <div class="form-group">
            <label for="new_pw">New Password</label>
            <div class="input-wrap">
                <input type="password" id="new_pw" required>
                <i class="fa-solid fa-key icon"></i>
            </div>
            <small class="hint">Use at least 8 characters including letters and numbers.</small>
        </div>

        <div class="form-group">
            <label for="confirm_pw">Confirm New Password</label>
            <div class="input-wrap">
                <input type="password" id="confirm_pw" required>
                <i class="fa-solid fa-check icon"></i>
                <small id="pwMatchMsg" class="pw-hint"></small>
            </div>
        </div>

        <button type="submit" class="btn primary update-btn">
            Update Password
        </button>

    </form>
</section>

  <!-- ===================== MFA / OKTA VERIFICATION ===================== -->
  <section class="card account-section">
    <h2><i class="fa-solid fa-shield-halved"></i> Multi-Factor Authentication</h2>

    <p>We use <strong>Okta Verify</strong> for multi-factor authentication (MFA).</p>

    <!-- Placeholder -->
    <div id="mfaStatusBox" class="mfa-box inactive">
      <i class="fa-solid fa-circle-xmark"></i>
      MFA Not Enabled
    </div>

    <button class="btn primary" id="setupMfaBtn">
      Enable Okta Verify
    </button>

    <button class="btn danger" id="disableMfaBtn" style="display:none;">
      Disable Okta Verify
    </button>

    <p class="hint">Your administrator may require MFA depending on your role.</p>
  </section>

  <!-- ===================== CONNECTED DEVICES (UI Only) ===================== -->
  <section class="card account-section">
    <h2><i class="fa-solid fa-mobile-screen"></i> Devices</h2>

    <p class="hint">This will show devices linked via Okta Verify once integration is enabled.</p>

    <ul class="device-list" id="deviceList">
      <li>No devices connected.</li>
    </ul>
  </section>

</div>

<footer class="site-footer">
  <p>© 2025 Globe • AI and Privacy Governance • All Rights Reserved</p>
</footer>

<script src="/assets/js/sidebar.js"></script>
<script src="/assets/js/toast.js"></script>
<script src="/assets/js/showToast.js"></script>
<script src="/assets/js/accounts.js"></script>
</body>
</html>

