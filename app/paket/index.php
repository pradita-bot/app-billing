<?php
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../includes/auth.php';
require_login();

$pdo = db();
$paket = $pdo->query("SELECT * FROM paket ORDER BY created_at DESC")->fetchAll();
$page_title = 'Paket Internet';
?>
<!DOCTYPE html>
<html lang="id" data-theme="light">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Paket Internet - Billing RT/RW Net</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" rel="stylesheet">
    <link href="../assets/css/style.css" rel="stylesheet">
</head>
<body>
    <div class="layout">
        <?php include __DIR__ . '/../includes/sidebar.php'; ?>

        <div class="main">
            <?php include __DIR__ . '/../includes/header.php'; ?>

            <div class="content">
                <div class="card">
                    <div class="card-header">
                        <h2 class="card-title">Daftar Paket Internet</h2>
                        <a href="tambah.php" class="btn btn-primary">
                            <i class="bi bi-plus-lg"></i>
                            Tambah Paket
                        </a>
                    </div>

                    <?php if (isset($_SESSION['flash'])): ?>
                        <div class="alert alert-<?= $_SESSION['flash']['type'] ?>">
                            <i class="bi bi-check-circle"></i>
                            <?= $_SESSION['flash']['message'] ?>
                        </div>
                        <?php unset($_SESSION['flash']); ?>
                    <?php endif; ?>

                    <div class="table-wrapper">
                        <table>
                            <thead>
                                <tr>
                                    <th>No</th>
                                    <th>Nama Paket</th>
                                    <th>Kecepatan</th>
                                    <th>Harga/Bulan</th>
                                    <th>Status</th>
                                    <th>Aksi</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if (empty($paket)): ?>
                                    <tr>
                                        <td colspan="6" style="text-align: center; color: var(--text-secondary);">
                                            Belum ada paket. Klik "Tambah Paket" untuk membuat.
                                        </td>
                                    </tr>
                                <?php else: ?>
                                    <?php foreach ($paket as $i => $p): ?>
                                        <tr>
                                            <td><?= $i + 1 ?></td>
                                            <td><strong><?= e($p['nama_paket']) ?></strong></td>
                                            <td><?= e($p['kecepatan']) ?></td>
                                            <td>Rp <?= number_format($p['harga_bulanan'], 0, ',', '.') ?></td>
                                            <td>
                                                <?php if ($p['status'] == 1): ?>
                                                    <span class="badge badge-success">Aktif</span>
                                                <?php else: ?>
                                                    <span class="badge badge-danger">Nonaktif</span>
                                                <?php endif; ?>
                                            </td>
                                            <td>
                                                <a href="edit.php?id=<?= $p['id'] ?>" class="btn btn-warning btn-sm" title="Edit">
                                                    <i class="bi bi-pencil"></i>
                                                </a>
                                                <a href="hapus.php?id=<?= $p['id'] ?>&csrf_token=<?= csrf_token() ?>" 
                                                   class="btn btn-danger btn-sm" 
                                                   title="Hapus" 
                                                   onclick="return confirm('Yakin mau hapus paket ini?')">
                                                    <i class="bi bi-trash"></i>
                                                </a>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <?php include __DIR__ . '/../includes/footer.php'; ?>
</body>
</html>