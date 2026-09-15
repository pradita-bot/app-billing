<?php
// Prevent direct access
if (!defined('APP_LOADED')) {
    define('APP_LOADED', true);
}
?>
<header class="header">
    <button class="mobile-menu-btn" onclick="toggleSidebar()">
        <i class="bi bi-list"></i>
    </button>

    <div class="header-left">
        <h1 class="header-title"><?= $page_title ?? 'Dashboard' ?></h1>
    </div>

    <div class="header-right">
        <div class="theme-toggle" onclick="toggleTheme()" title="Ganti Tema">
            <i class="bi bi-moon-fill" id="theme-icon"></i>
        </div>

        <?php if (isset($_SESSION['user_id'])): ?>
            <div class="user-info">
                <div class="user-avatar"><?= strtoupper(substr($_SESSION['nama'], 0, 1)) ?></div>
                <div class="user-detail">
                    <div class="user-name"><?= htmlspecialchars($_SESSION['nama']) ?></div>
                    <div class="user-role"><?= ucfirst($_SESSION['role']) ?></div>
                </div>
            </div>

            <a href="<?= BASE_URL ?>/logout.php" class="btn btn-logout" title="Logout">
                <i class="bi bi-box-arrow-right"></i>
            </a>
        <?php endif; ?>
    </div>
</header>