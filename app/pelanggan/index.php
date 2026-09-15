<?php
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../includes/auth.php';
require_login();

$pdo = db();
$psb = $pdo->query("SELECT p.*, pk.nama_paket, u.nama as teknisi_nama FROM psb p LEFT JOIN paket pk ON p.paket_id = pk.id LEFT JOIN users u ON p.teknisi_id = u.id ORDER BY p.created_at DESC")->fetchAll();

$page_title = 'PSB (Pemasangan Baru)';
?>
<!DOCTYPE html>
<html lang="id" data-theme="light">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>PSB - Billing RT/RW Net</title>
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
                        <h2 class="card-title">Daftar Permintaan PSB</h2>
                        <a href="daftar.php" target="_blank" class="btn btn-secondary">
                            <i class="bi bi-box-arrow-up-right"></i>
                            Buka Form Publik
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
                                    <th>Foto</th>
                                    <th>Nama</th>
                                    <th>No HP</th>
                                    <th>Paket</th>
                                    <th>Desa</th>
                                    <th>Status</th>
                                    <th>Aksi</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if (empty($psb)): ?>
                                    <tr>
                                        <td colspan="8" style="text-align: center; color: var(--text-secondary); padding: 40px;">
                                            <i class="bi bi-inbox" style="font-size: 48px; display: block; margin-bottom: 12px;"></i>
                                            Belum ada permintaan PSB.
                                        </td>
                                    </tr>
                                <?php else: ?>
                                    <?php foreach ($psb as $i => $p): ?>
                                        <tr>
                                            <td><?= $i + 1 ?></td>
                                            <td>
                                                <?php if ($p['foto_rumah']): ?>
                                                    <a href="../uploads/foto_psb/<?= e($p['foto_rumah']) ?>" target="_blank">
                                                        <img src="../uploads/foto_psb/<?= e($p['foto_rumah']) ?>" 
                                                             style="width: 60px; height: 60px; object-fit: cover; border-radius: 8px; cursor: pointer;"
                                                             alt="Foto Rumah">
                                                    </a>
                                                <?php else: ?>
                                                    <span style="color: var(--text-secondary);">-</span>
                                                <?php endif; ?>
                                            </td>
                                            <td><strong><?= e($p['nama']) ?></strong></td>
                                            <td><?= e($p['no_hp']) ?></td>
                                            <td><?= e($p['nama_paket'] ?? '-') ?></td>
                                            <td><?= e($p['desa'] ?? '-') ?></td>
                                            <td>
                                                <?php
                                                $status_map = [
                                                    'baru' => ['label' => 'Baru', 'class' => 'badge-primary'],
                                                    'survey' => ['label' => 'Survey', 'class' => 'badge-warning'],
                                                    'menunggu_bayar' => ['label' => 'Menunggu Bayar', 'class' => 'badge-warning'],
                                                    'dikerjakan' => ['label' => 'Dikerjakan', 'class' => 'badge-primary'],
                                                    'selesai' => ['label' => 'Selesai', 'class' => 'badge-success'],
                                                    'ditolak' => ['label' => 'Ditolak', 'class' => 'badge-danger'],
                                                ];
                                                $st = $status_map[$p['status']] ?? ['label' => ucfirst($p['status']), 'class' => ''];
                                                ?>
                                                <span class="badge <?= $st['class'] ?>"><?= $st['label'] ?></span>
                                            </td>
                                            <td>
                                                <?php if ($p['latitude'] && $p['longitude']): ?>
                                                    <a href="https://maps.google.com/?q=<?= $p['latitude'] ?>,<?= $p['longitude'] ?>" target="_blank" class="btn btn-secondary btn-sm" title="Lihat di Google Maps">
                                                        <i class="bi bi-geo-alt"></i>
                                                    </a>
                                                <?php endif; ?>
                                                <a href="edit.php?id=<?= $p['id'] ?>" class="btn btn-warning btn-sm" title="Edit">
                                                    <i class="bi bi-pencil"></i>
                                                </a>
                                                <a href="hapus.php?id=<?= $p['id'] ?>&csrf_token=<?= csrf_token() ?>" 
                                                   class="btn btn-danger btn-sm" 
                                                   title="Hapus" 
                                                   onclick="return confirm('Yakin mau hapus data PSB ini?')">
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