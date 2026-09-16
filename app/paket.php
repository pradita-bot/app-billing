<?php
require_once __DIR__ . '/config/database.php';
require_once __DIR__ . '/config/functions.php';
require_once __DIR__ . '/includes/auth.php';
require_login();
require_csrf();

$pdo = db();
$action = $_GET['action'] ?? 'list';
$error = '';
$success = '';

// ============================================
// HANDLE POST ACTIONS
// ============================================
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    
    // TAMBAH PAKET
    if ($action === 'tambah') {
        $nama = trim($_POST['nama_paket'] ?? '');
        $kecepatan = trim($_POST['kecepatan'] ?? '');
        $harga = floatval($_POST['harga_bulanan'] ?? 0);
        $status = intval($_POST['status'] ?? 1);

        if ($nama === '' || $kecepatan === '' || $harga <= 0) {
            $error = 'Semua field wajib diisi dengan benar.';
        } else {
            $stmt = $pdo->prepare('INSERT INTO paket (nama_paket, kecepatan, harga_bulanan, status) VALUES (?, ?, ?, ?)');
            $stmt->execute([$nama, $kecepatan, $harga, $status]);
            $_SESSION['flash'] = ['type' => 'success', 'message' => 'Paket berhasil ditambahkan!'];
            header('Location: paket.php');
            exit;
        }
    }
    
    // EDIT PAKET
    if ($action === 'edit') {
        $id = intval($_POST['id'] ?? 0);
        $nama = trim($_POST['nama_paket'] ?? '');
        $kecepatan = trim($_POST['kecepatan'] ?? '');
        $harga = floatval($_POST['harga_bulanan'] ?? 0);
        $status = intval($_POST['status'] ?? 1);

        if ($id > 0 && $nama !== '' && $kecepatan !== '' && $harga > 0) {
            $stmt = $pdo->prepare('UPDATE paket SET nama_paket = ?, kecepatan = ?, harga_bulanan = ?, status = ? WHERE id = ?');
            $stmt->execute([$nama, $kecepatan, $harga, $status, $id]);
            $_SESSION['flash'] = ['type' => 'success', 'message' => 'Paket berhasil diupdate!'];
            header('Location: paket.php');
            exit;
        } else {
            $error = 'Semua field wajib diisi dengan benar.';
        }
    }
}

// ============================================
// HANDLE GET ACTIONS
// ============================================

// HAPUS PAKET
if ($action === 'hapus' && isset($_GET['id'])) {
    $id = intval($_GET['id']);
    
    if ($id > 0) {
        // Cek apakah paket dipakai pelanggan
        $stmt = $pdo->prepare("SELECT COUNT(*) as total FROM pelanggan WHERE paket_id = ?");
        $stmt->execute([$id]);
        $result = $stmt->fetch();
        
        if ($result['total'] > 0) {
            $_SESSION['flash'] = ['type' => 'danger', 'message' => 'Tidak bisa hapus paket yang masih digunakan oleh pelanggan!'];
        } else {
            $stmt = $pdo->prepare('DELETE FROM paket WHERE id = ?');
            $stmt->execute([$id]);
            $_SESSION['flash'] = ['type' => 'success', 'message' => 'Paket berhasil dihapus!'];
        }
    }
    header('Location: paket.php');
    exit;
}

// ============================================
// GET DATA
// ============================================

// Ambil data paket yang mau diedit
$paket_edit = null;
if ($action === 'edit' && isset($_GET['id'])) {
    $id = intval($_GET['id']);
    $stmt = $pdo->prepare('SELECT * FROM paket WHERE id = ?');
    $stmt->execute([$id]);
    $paket_edit = $stmt->fetch();
    
    if (!$paket_edit) {
        header('Location: paket.php');
        exit;
    }
}

// Ambil semua paket
$paket_list = $pdo->query("SELECT * FROM paket ORDER BY created_at DESC")->fetchAll();

$page_title = 'Paket Internet';
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Paket Internet - Billing RT/RW Net</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" rel="stylesheet">
    <link href="assets/css/style.css" rel="stylesheet">
</head>
<body>
    <div class="layout">
        <?php include __DIR__ . '/includes/sidebar.php'; ?>

        <div class="main">
            <?php include __DIR__ . '/includes/header.php'; ?>

            <div class="content">
                <!-- Flash Messages -->
                <?php if (isset($_SESSION['flash'])): ?>
                    <div class="alert alert-<?= $_SESSION['flash']['type'] ?>">
                        <i class="bi bi-check-circle"></i>
                        <?= $_SESSION['flash']['message'] ?>
                    </div>
                    <?php unset($_SESSION['flash']); ?>
                <?php endif; ?>

                <?php if ($error): ?>
                    <div class="alert alert-danger">
                        <i class="bi bi-exclamation-circle"></i>
                        <?= e($error) ?>
                    </div>
                <?php endif; ?>

                <!-- FORM TAMBAH -->
                <?php if ($action === 'tambah'): ?>
                    <div class="card">
                        <div class="card-header">
                            <h2 class="card-title">Tambah Paket Baru</h2>
                            <a href="paket.php" class="btn btn-secondary">
                                <i class="bi bi-arrow-left"></i>
                                Kembali
                            </a>
                        </div>

                        <form method="post">
                            <?= csrf_field() ?>
                            
                            <div class="form-row">
                                <div class="form-group">
                                    <label class="form-label">Nama Paket</label>
                                    <input type="text" name="nama_paket" class="form-input" placeholder="Contoh: Paket Hemat" required>
                                </div>

                                <div class="form-group">
                                    <label class="form-label">Kecepatan</label>
                                    <input type="text" name="kecepatan" class="form-input" placeholder="Contoh: 10 Mbps" required>
                                </div>
                            </div>

                            <div class="form-row">
                                <div class="form-group">
                                    <label class="form-label">Harga per Bulan (Rp)</label>
                                    <input type="number" name="harga_bulanan" class="form-input" placeholder="Contoh: 100000" min="0" step="1000" required>
                                </div>

                                <div class="form-group">
                                    <label class="form-label">Status</label>
                                    <select name="status" class="form-input">
                                        <option value="1">Aktif</option>
                                        <option value="0">Nonaktif</option>
                                    </select>
                                </div>
                            </div>

                            <button type="submit" class="btn btn-primary">
                                <i class="bi bi-save"></i>
                                Simpan Paket
                            </button>
                        </form>
                    </div>

                <!-- FORM EDIT -->
                <?php elseif ($action === 'edit' && $paket_edit): ?>
                    <div class="card">
                        <div class="card-header">
                            <h2 class="card-title">Edit Paket</h2>
                            <a href="paket.php" class="btn btn-secondary">
                                <i class="bi bi-arrow-left"></i>
                                Kembali
                            </a>
                        </div>

                        <form method="post">
                            <?= csrf_field() ?>
                            <input type="hidden" name="id" value="<?= $paket_edit['id'] ?>">
                            
                            <div class="form-row">
                                <div class="form-group">
                                    <label class="form-label">Nama Paket</label>
                                    <input type="text" name="nama_paket" class="form-input" value="<?= e($paket_edit['nama_paket']) ?>" required>
                                </div>

                                <div class="form-group">
                                    <label class="form-label">Kecepatan</label>
                                    <input type="text" name="kecepatan" class="form-input" value="<?= e($paket_edit['kecepatan']) ?>" required>
                                </div>
                            </div>

                            <div class="form-row">
                                <div class="form-group">
                                    <label class="form-label">Harga per Bulan (Rp)</label>
                                    <input type="number" name="harga_bulanan" class="form-input" value="<?= $paket_edit['harga_bulanan'] ?>" min="0" step="1000" required>
                                </div>

                                <div class="form-group">
                                    <label class="form-label">Status</label>
                                    <select name="status" class="form-input">
                                        <option value="1" <?= $paket_edit['status'] == 1 ? 'selected' : '' ?>>Aktif</option>
                                        <option value="0" <?= $paket_edit['status'] == 0 ? 'selected' : '' ?>>Nonaktif</option>
                                    </select>
                                </div>
                            </div>

                            <button type="submit" class="btn btn-primary">
                                <i class="bi bi-save"></i>
                                Update Paket
                            </button>
                        </form>
                    </div>

                <!-- LIST PAKET -->
                <?php else: ?>
                    <div class="card">
                        <div class="card-header">
                            <h2 class="card-title">Daftar Paket Internet</h2>
                            <a href="paket.php?action=tambah" class="btn btn-primary">
                                <i class="bi bi-plus-lg"></i>
                                Tambah Paket
                            </a>
                        </div>

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
                                    <?php if (empty($paket_list)): ?>
                                        <tr>
                                            <td colspan="6" style="text-align: center; color: var(--text-secondary);">
                                                Belum ada paket. Klik "Tambah Paket" untuk membuat.
                                            </td>
                                        </tr>
                                    <?php else: ?>
                                        <?php foreach ($paket_list as $i => $p): ?>
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
                                                    <a href="paket.php?action=edit&id=<?= $p['id'] ?>" class="btn btn-warning btn-sm" title="Edit">
                                                        <i class="bi bi-pencil"></i>
                                                    </a>
                                                    <a href="paket.php?action=hapus&id=<?= $p['id'] ?>&csrf_token=<?= csrf_token() ?>" 
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
                <?php endif; ?>
            </div>
        </div>
    </div>

    <?php include __DIR__ . '/includes/footer.php'; ?>
</body>
</html>