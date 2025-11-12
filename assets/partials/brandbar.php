<?php
// partials/brandbar.php

$brand = array_merge([
  'titleLines'     => ['Data Agreements and', 'Contracts Review Request Form'],
  'logo'           => '/img/Globe Only.png',
  'logoutHref'     => '/api/logout.php',
  'showNotif'      => true,   // bell on/off
  'showMenuToggle' => true,   // sidebar toggle on/off
], $brand ?? []);
?>

<header class="brandbar">
  <div class="brandbar__inner container">

    <?php if (!empty($brand['showMenuToggle'])): ?>
      <button id="sbToggle" class="menu-btn" aria-label="Open menu">☰</button>
    <?php endif; ?>

    <!-- Logo + Title -->
    <div class="brandbar__title">
      <img src="<?= htmlspecialchars($brand['logo']) ?>" alt="Logo" class="brand-logo" />
      <div>
        <div><?= htmlspecialchars($brand['titleLines'][0]) ?></div>
        <div><?= htmlspecialchars($brand['titleLines'][1] ?? '') ?></div>
      </div>
    </div>

    <?php if (!empty($brand['showNotif'])): ?>
      <div class="notif-wrapper">
        <button id="notifBell" class="notif-bell" aria-label="Notifications">
          <i class="fa-solid fa-bell"></i>
          <span id="notifBadge" class="notif-badge" style="display:none;">0</span>
        </button>
        <div id="notifDropdown" class="notif-dropdown" role="menu" aria-hidden="true">
          <div class="notif-header">Notifications</div>
          <div id="notifList" class="notif-list"></div>
        </div>
      </div>
    <?php endif; ?>

    <!-- Optional logout block (kept commented)
    <form class="logout-form" method="post" action="<?= htmlspecialchars($brand['logoutHref']) ?>">
      <button class="logout-btn" type="submit">Logout</button>
    </form>
    -->
  </div>
</header>
