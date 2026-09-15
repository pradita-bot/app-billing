<?php
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../includes/auth.php';
require_login();

$pdo = db();
$psb = $pdo->query("SELECT p.*, u.nama as teknisi_nama FROM psb p LEFT JOIN users u ON p.teknisi_id = u.id ORDER BY p.created_at DESC")->fetchAll();

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
                                    <th>Nama</th>
                                    <th>No HP</th>
                                    <th>Desa</th>
                                    <th>Status</th>
                                    <th>Tanggal Daftar</th>
                                    <th>Aksi</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if (empty($psb)): ?>
                                    <tr>
                                        <td colspan="7" style="text-align: center; color: var(--text-secondary); padding: 40px;">
                                            <i class="bi bi-inbox" style="font-size: 48px; display: block; margin-bottom: 12px;"></i>
                                            Belum ada permintaan PSB.
                                        </td>
                                    </tr>
                                <?php else: ?>
                                    <?php foreach ($psb as $i => $p): ?>
                                        <tr>
                                            <td><?= $i + 1 ?></td>
                                            <td><strong><?= htmlspecialchars($p['nama']) ?></strong></td>
                                            <td><?= htmlspecialchars($p['no_hp']) ?></td>
                                            <td><?= htmlspecialchars($p['desa'] ?? '-') ?></td>
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
                                            <td><?= date('d M Y', strtotime($p['created_at'])) ?></td>
                                            <td>
                                                <a href="edit.php?id=<?= $p['id'] ?>" class="btn btn-warning btn-sm" title="Edit">
                                                    <i class="bi bi-pencil"></i>
                                                </a>
                                                <a href="hapus.php?id=<?= $p['id'] ?>" class="btn btn-danger btn-sm" title="Hapus" onclick="return confirm('Yakin mau hapus data PSB ini?')">
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