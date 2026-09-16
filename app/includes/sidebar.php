<?php
$current_page = basename($_SERVER['PHP_SELF']);
?>
<aside class="sidebar" id="sidebar">
    <div class="sidebar-logo">
        <div class="sidebar-logo-icon">
            <i class="bi bi-wifi"></i>
        </div>
        <div class="sidebar-logo-text">RT/RW Net</div>
    </div>

    <nav>
        <a href="dashboard.php" class="menu-item <?= $current_page == 'dashboard.php' ? 'active' : '' ?>">
            <i class="bi bi-house-door menu-icon"></i>
            Dashboard
        </a>

        <div class="menu-divider"></div>

        <a href="pelanggan.php" class="menu-item <?= $current_page == 'pelanggan.php' ? 'active' : '' ?>">
            <i class="bi bi-people menu-icon"></i>
            Pelanggan
        </a>

        <a href="paket.php" class="menu-item <?= $current_page == 'paket.php' ? 'active' : '' ?>">
            <i class="bi bi-box-seam menu-icon"></i>
            Paket
        </a>

        <a href="#" class="menu-item">
            <i class="bi bi-receipt menu-icon"></i>
            Tagihan
        </a>

        <a href="#" class="menu-item">
            <i class="bi bi-credit-card menu-icon"></i>
            Pembayaran
        </a>

        <div class="menu-divider"></div>

        <a href="psb.php" class="menu-item <?= $current_page == 'psb.php' ? 'active' : '' ?>">
            <i class="bi bi-tools menu-icon"></i>
            PSB
        </a>

        <a href="#" class="menu-item">
            <i class="bi bi-map menu-icon"></i>
            Peta
        </a>

        <a href="#" class="menu-item">
            <i class="bi bi-graph-up menu-icon"></i>
            Laporan
        </a>

        <div class="menu-divider"></div>

        <?php if (isset($_SESSION['role']) && $_SESSION['role'] == 'admin'): ?>
            <a href="setting.php" class="menu-item <?= $current_page == 'setting.php' ? 'active' : '' ?>">
                <i class="bi bi-gear menu-icon"></i>
                Setting
            </a>
        <?php endif; ?>

        <a href="logout.php" class="menu-item" style="color: rgba(255,255,255,0.5);">
            <i class="bi bi-box-arrow-right menu-icon"></i>
            Logout
        </a>
    </nav>
</aside>