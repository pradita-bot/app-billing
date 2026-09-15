<?php
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../includes/auth.php';
require_login();

$pdo = db();

// Filter status
$filter = $_GET['status'] ?? '';
$search = $_GET['search'] ?? '';

$sql = "SELECT p.*, pk.nama_paket 
        FROM pelanggan p 
        LEFT JOIN paket pk ON p.paket_id = pk.id 
        WHERE 1=1";

$params = [];

if ($filter !== '') {
    $sql .= " AND p.status = ?";
    $params[] = $filter;
}

if ($search !== '') {
    $sql .= " AND (p.nama LIKE ? OR p.no_hp LIKE ? OR p.kode_pelanggan LIKE ?)";
    $search_term = "%$search%";
    $params[] = $search_term;
    $params[] = $search_term;
    $params[] = $search_term;
}

$sql .= " ORDER BY p.created_at DESC";

$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$pelanggan = $stmt->fetchAll();

$page_title = 'Data Pelanggan';
?>
<!DOCTYPE html>
<html lang="id" data-theme="light">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Data Pelanggan - Billing RT/RW Net</title>
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
                        <h2 class="card-title">Data Pelanggan</h2>
                        <a href="tambah.php" class="btn btn-primary">
                            <i class="bi bi-plus-lg"></i>
                            Tambah Pelanggan
                        </a>
                    </div>

                    <?php if (isset($_SESSION['flash'])): ?>
                        <div class="alert alert-<?= $_SESSION['flash']['type'] ?>">
                            <i class="bi bi-check-circle"></i>
                            <?= $_SESSION['flash']['message'] ?>
                        </div>
                        <?php unset($_SESSION['flash']); ?>
                    <?php endif; ?>

                    <!-- Filter -->
                    <form method="get" style="margin-bottom: 20px; display: flex; gap: 12px; flex-wrap: wrap;">
                        <input type="text" name="search" class="form-input" style="max-width: 300px;" 
                               placeholder="Cari nama, no HP, atau kode..." value="<?= htmlspecialchars($search) ?>">
                        <select name="status" class="form-input" style="max-width: 180px;">
                            <option value="">Semua Status</option>
                            <option value="aktif" <?= $filter == 'aktif' ? 'selected' : '' ?>>Aktif</option>
                            <option value="isolir" <?= $filter == 'isolir' ? 'selected' : '' ?>>Isolir</option>
                            <option value="berhenti" <?= $filter == 'berhenti' ? 'selected' : '' ?>>Berhenti</option>
                        </select>
                        <button type="submit" class="btn btn-primary">
                            <i class="bi bi-search"></i>
                            Filter
                        </button>
                        <?php if ($filter !== '' || $search !== ''): ?>
                            <a href="index.php" class="btn btn-secondary">Reset</a>
                        <?php endif; ?>
                    </form>

                    <div class="table-wrapper">
                        <table>
                            <thead>
                                <tr>
                                    <th>No</th>
                                    <th>Kode</th>
                                    <th>Nama</th>
                                    <th>No HP</th>
                                    <th>Paket</th>
                                    <th>MAC Address</th>
                                    <th>Desa</th>
                                    <th>Status</th>
                                    <th>Aksi</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if (empty($pelanggan)): ?>
                                    <tr>
                                        <td colspan="9" style="text-align: center; color: var(--text-secondary); padding: 40px;">
                                            <i class="bi bi-inbox" style="font-size: 48px; display: block; margin-bottom: 12px;"></i>
                                            Belum ada data pelanggan.
                                        </td>
                                    </tr>
                                <?php else: ?>
                                    <?php foreach ($pelanggan as $i => $p): ?>
                                        <tr>
                                            <td><?= $i + 1 ?></td>
                                            <td><code><?= htmlspecialchars($p['kode_pelanggan']) ?></code></td>
                                            <td><strong><?= htmlspecialchars($p['nama']) ?></strong></td>
                                            <td><?= htmlspecialchars($p['no_hp']) ?></td>
                                            <td><?= htmlspecialchars($p['nama_paket'] ?? '-') ?></td>
                                            <td><code><?= htmlspecialchars($p['mac_address'] ?? '-') ?></code></td>
                                            <td><?= htmlspecialchars($p['desa'] ?? '-') ?></td>
                                            <td>
                                                <?php if ($p['status'] == 'aktif'): ?>
                                                    <span class="badge badge-success">Aktif</span>
                                                <?php elseif ($p['status'] == 'isolir'): ?>
                                                    <span class="badge badge-danger">Isolir</span>
                                                <?php else: ?>
                                                    <span class="badge badge-warning">Berhenti</span>
                                                <?php endif; ?>
                                            </td>
                                            <td>
                                                <a href="edit.php?id=<?= $p['id'] ?>" class="btn btn-warning btn-sm" title="Edit">
                                                    <i class="bi bi-pencil"></i>
                                                </a>
                                                <a href="hapus.php?id=<?= $p['id'] ?>" class="btn btn-danger btn-sm" title="Hapus" onclick="return confirm('Yakin mau hapus pelanggan ini? Semua data tagihan juga akan terhapus.')">
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