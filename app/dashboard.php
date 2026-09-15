<?php
require_once __DIR__ . '/config/database.php';
require_once __DIR__ . '/includes/auth.php';
require_login();

$pdo = db();

// Data statistik
$stat = [
    'pelanggan_aktif'  => $pdo->query("SELECT COUNT(*) FROM pelanggan WHERE status = 'aktif'")->fetchColumn(),
    'pelanggan_isolir' => $pdo->query("SELECT COUNT(*) FROM pelanggan WHERE status = 'isolir'")->fetchColumn(),
    'tagihan_lunas'    => $pdo->query("SELECT COUNT(*) FROM tagihan WHERE status = 'lunas'")->fetchColumn(),
    'tagihan_belum'    => $pdo->query("SELECT COUNT(*) FROM tagihan WHERE status != 'lunas'")->fetchColumn(),
    'psb_antrian'      => $pdo->query("SELECT COUNT(*) FROM psb WHERE status NOT IN ('selesai','ditolak')")->fetchColumn(),
    'total_pelanggan'  => $pdo->query("SELECT COUNT(*) FROM pelanggan")->fetchColumn(),
];

$page_title = 'Dashboard';
?>
<!DOCTYPE html>
<html lang="id" data-theme="light">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Dashboard - Billing RT/RW Net</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" rel="stylesheet">
    <link href="assets/css/style.css" rel="stylesheet">
</head>
<body>
    <div class="layout">
        <?php include 'includes/sidebar.php'; ?>

        <div class="main">
            <?php include 'includes/header.php'; ?>

            <div class="content">
                <!-- Stats -->
                <div class="stats-grid">
                    <div class="stat-card">
                        <div class="stat-header">
                            <span class="stat-label">Pelanggan Aktif</span>
                            <div class="stat-icon success">
                                <i class="bi bi-check-circle"></i>
                            </div>
                        </div>
                        <div class="stat-value"><?= $stat['pelanggan_aktif'] ?></div>
                        <div class="stat-desc">dari <?= $stat['total_pelanggan'] ?> total pelanggan</div>
                    </div>

                    <div class="stat-card">
                        <div class="stat-header">
                            <span class="stat-label">Pelanggan Isolir</span>
                            <div class="stat-icon danger">
                                <i class="bi bi-slash-circle"></i>
                            </div>
                        </div>
                        <div class="stat-value"><?= $stat['pelanggan_isolir'] ?></div>
                        <div class="stat-desc">perlu ditindaklanjuti</div>
                    </div>

                    <div class="stat-card">
                        <div class="stat-header">
                            <span class="stat-label">Tagihan Lunas</span>
                            <div class="stat-icon primary">
                                <i class="bi bi-cash-stack"></i>
                            </div>
                        </div>
                        <div class="stat-value"><?= $stat['tagihan_lunas'] ?></div>
                        <div class="stat-desc">sudah dibayar</div>
                    </div>

                    <div class="stat-card">
                        <div class="stat-header">
                            <span class="stat-label">Belum Bayar</span>
                            <div class="stat-icon warning">
                                <i class="bi bi-hourglass-split"></i>
                            </div>
                        </div>
                        <div class="stat-value"><?= $stat['tagihan_belum'] ?></div>
                        <div class="stat-desc">menunggu pembayaran</div>
                    </div>
                </div>

                <!-- Info Cards -->
                <div class="card">
                    <div class="card-title" style="margin-bottom: 16px;">
                        <i class="bi bi-clipboard-check"></i>
                        Antrian PSB
                    </div>
                    <p style="color: var(--text-secondary); font-size: 14px;">
                        Ada <strong><?= $stat['psb_antrian'] ?></strong> permintaan pemasangan baru yang menunggu diproses.
                    </p>
                </div>

                <div class="card">
                    <div class="card-title" style="margin-bottom: 16px;">
                        <i class="bi bi-rocket-takeoff"></i>
                        Modul Selanjutnya
                    </div>
                    <p style="color: var(--text-secondary); font-size: 14px; line-height: 1.8;">
                        • CRUD Paket Internet <span class="badge badge-success">Selesai</span><br>
                        • CRUD Pelanggan (dengan MAC address & koordinat peta)<br>
                        • Generate Tagihan Bulanan<br>
                        • Halaman PSB Publik + Peta<br>
                        • Sistem Pembayaran (Gateway + Upload Bukti)<br>
                        • WhatsApp Reminder Otomatis<br>
                        • Integrasi MikroTik (Isolir/Un-isolir)
                    </p>
                </div>
            </div>
        </div>
    </div>

    <?php include 'includes/footer.php'; ?>
</body>
</html>