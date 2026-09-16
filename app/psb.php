<?php
require_once __DIR__ . '/config/database.php';
require_once __DIR__ . '/config/functions.php';
require_once __DIR__ . '/includes/auth.php';
require_login();
require_csrf();

$pdo = db();
$action = $_GET['action'] ?? 'list';
$error = '';

// ============================================
// HANDLE KONVERSI PSB KE PELANGGAN
// ============================================
if ($action === 'konversi' && isset($_GET['id'])) {
    $id = intval($_GET['id']);
    
    // Ambil data PSB
    $stmt = $pdo->prepare('SELECT * FROM psb WHERE id = ?');
    $stmt->execute([$id]);
    $psb = $stmt->fetch();
    
    if (!$psb) {
        $_SESSION['flash'] = ['type' => 'danger', 'message' => 'Data PSB tidak ditemukan!'];
    } elseif ($psb['status'] !== 'selesai') {
        $_SESSION['flash'] = ['type' => 'danger', 'message' => 'PSB harus berstatus "Selesai" sebelum dikonversi ke pelanggan!'];
    } else {
        // Generate kode pelanggan baru
        $kode_pelanggan = 'PLG-' . strtoupper(substr(md5(uniqid() . $id), 0, 6));
        
        try {
            // Insert ke tabel pelanggan
            $stmt = $pdo->prepare('INSERT INTO pelanggan 
                (kode_pelanggan, nama, no_hp, alamat, desa, kecamatan, latitude, longitude, paket_id, status, tgl_pasang) 
                VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)');
            
            $stmt->execute([
                $kode_pelanggan,
                $psb['nama'],
                $psb['no_hp'],
                $psb['alamat'],
                $psb['desa'],
                $psb['kecamatan'],
                $psb['latitude'],
                $psb['longitude'],
                $psb['paket_id'],
                'aktif',
                date('Y-m-d')
            ]);
            
            $pelanggan_id = $pdo->lastInsertId();
            
            // HAPUS data PSB setelah konversi (bukan update status)
            $stmt = $pdo->prepare('DELETE FROM psb WHERE id = ?');
            $stmt->execute([$id]);
            
            // Log aktivitas
            log_aktivitas("Konversi PSB #{$id} ke pelanggan {$kode_pelanggan}");
            
            $_SESSION['flash'] = [
                'type' => 'success', 
                'message' => "PSB berhasil dikonversi ke pelanggan dengan kode: <strong>{$kode_pelanggan}</strong>. <br><a href='pelanggan.php?action=edit&id={$pelanggan_id}' class='alert-link'>Klik di sini untuk lengkapi data MAC & PPPoE</a>"
            ];
            
        } catch (PDOException $e) {
            $_SESSION['flash'] = ['type' => 'danger', 'message' => 'Gagal konversi: ' . $e->getMessage()];
        }
    }
    
    header('Location: psb.php');
    exit;
}

// ============================================
// HANDLE POST ACTIONS
// ============================================
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    
    // EDIT PSB
    if ($action === 'edit') {
        $id = intval($_POST['id'] ?? 0);
        $nama = trim($_POST['nama'] ?? '');
        $no_hp = trim($_POST['no_hp'] ?? '');
        $alamat = trim($_POST['alamat'] ?? '');
        $desa = trim($_POST['desa'] ?? '');
        $kecamatan = trim($_POST['kecamatan'] ?? '');
        $catatan = trim($_POST['catatan'] ?? '');
        $status = $_POST['status'] ?? 'baru';
        $teknisi_id = intval($_POST['teknisi_id'] ?? 0);
        $hasil_survey = trim($_POST['hasil_survey'] ?? '');
        $paket_id = intval($_POST['paket_id'] ?? 0);

        if ($id > 0 && $nama !== '' && $no_hp !== '') {
            $teknisi_id = $teknisi_id > 0 ? $teknisi_id : null;
            $paket_id = $paket_id > 0 ? $paket_id : null;
            
            $stmt = $pdo->prepare('UPDATE psb SET nama = ?, no_hp = ?, alamat = ?, desa = ?, kecamatan = ?, catatan = ?, status = ?, teknisi_id = ?, hasil_survey = ?, paket_id = ? WHERE id = ?');
            $stmt->execute([$nama, $no_hp, $alamat, $desa, $kecamatan, $catatan, $status, $teknisi_id, $hasil_survey, $paket_id, $id]);

            $_SESSION['flash'] = ['type' => 'success', 'message' => "Data PSB atas nama $nama berhasil diupdate!"];
            header('Location: psb.php');
            exit;
        } else {
            $error = 'Nama dan No HP wajib diisi.';
        }
    }
}

// ============================================
// HANDLE HAPUS
// ============================================
if ($action === 'hapus' && isset($_GET['id'])) {
    $id = intval($_GET['id']);
    
    if ($id > 0) {
        // Ambil data PSB untuk hapus foto
        $stmt = $pdo->prepare('SELECT foto_rumah FROM psb WHERE id = ?');
        $stmt->execute([$id]);
        $psb = $stmt->fetch();
        
        // Hapus file foto jika ada
        if ($psb && $psb['foto_rumah']) {
            $file_path = __DIR__ . '/uploads/foto_psb/' . $psb['foto_rumah'];
            if (file_exists($file_path)) {
                unlink($file_path);
            }
        }
        
        // Hapus data dari database
        $stmt = $pdo->prepare('DELETE FROM psb WHERE id = ?');
        $stmt->execute([$id]);
        
        $_SESSION['flash'] = ['type' => 'success', 'message' => 'Data PSB berhasil dihapus!'];
    }
    
    header('Location: psb.php');
    exit;
}

// ============================================
// GET DATA
// ============================================

// Ambil data PSB yang mau diedit
$psb_edit = null;
if ($action === 'edit' && isset($_GET['id'])) {
    $id = intval($_GET['id']);
    $stmt = $pdo->prepare('SELECT * FROM psb WHERE id = ?');
    $stmt->execute([$id]);
    $psb_edit = $stmt->fetch();
    
    if (!$psb_edit) {
        header('Location: psb.php');
        exit;
    }
}

// Ambil daftar teknisi
$teknisi_list = $pdo->query("SELECT id, nama FROM users WHERE role = 'teknisi' AND status = 1")->fetchAll();

// Ambil daftar paket
$paket_list = $pdo->query("SELECT id, nama_paket FROM paket WHERE status = 1")->fetchAll();

// Ambil semua PSB
$psb_list = $pdo->query("SELECT p.*, pk.nama_paket, u.nama as teknisi_nama FROM psb p LEFT JOIN paket pk ON p.paket_id = pk.id LEFT JOIN users u ON p.teknisi_id = u.id ORDER BY p.created_at DESC")->fetchAll();

$page_title = 'PSB (Pemasangan Baru)';
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>PSB - Billing RT/RW Net</title>
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

                <!-- FORM EDIT PSB -->
                <?php if ($action === 'edit' && $psb_edit): ?>
                    <div class="card">
                        <div class="card-header">
                            <h2 class="card-title">Edit Data PSB</h2>
                            <a href="psb.php" class="btn btn-secondary">
                                <i class="bi bi-arrow-left"></i>
                                Kembali
                            </a>
                        </div>

                        <form method="post">
                            <?= csrf_field() ?>
                            <input type="hidden" name="id" value="<?= $psb_edit['id'] ?>">
                            
                            <div class="form-row">
                                <div class="form-group">
                                    <label class="form-label">Nama Lengkap *</label>
                                    <input type="text" name="nama" class="form-input" value="<?= e($psb_edit['nama']) ?>" required>
                                </div>

                                <div class="form-group">
                                    <label class="form-label">No HP / WhatsApp *</label>
                                    <input type="text" name="no_hp" class="form-input" value="<?= e($psb_edit['no_hp']) ?>" required>
                                </div>
                            </div>

                            <div class="form-group">
                                <label class="form-label">Alamat</label>
                                <textarea name="alamat" class="form-input" rows="2"><?= e($psb_edit['alamat']) ?></textarea>
                            </div>

                            <div class="form-row">
                                <div class="form-group">
                                    <label class="form-label">Desa / Kelurahan</label>
                                    <input type="text" name="desa" class="form-input" value="<?= e($psb_edit['desa']) ?>">
                                </div>

                                <div class="form-group">
                                    <label class="form-label">Kecamatan</label>
                                    <input type="text" name="kecamatan" class="form-input" value="<?= e($psb_edit['kecamatan']) ?>">
                                </div>
                            </div>

                            <div class="form-group">
                                <label class="form-label">Catatan</label>
                                <textarea name="catatan" class="form-input" rows="2"><?= e($psb_edit['catatan']) ?></textarea>
                            </div>

                            <div class="form-row">
                                <div class="form-group">
                                    <label class="form-label">Paket Internet</label>
                                    <select name="paket_id" class="form-input">
                                        <option value="0">-- Tanpa Paket --</option>
                                        <?php foreach ($paket_list as $pk): ?>
                                            <option value="<?= $pk['id'] ?>" <?= $psb_edit['paket_id'] == $pk['id'] ? 'selected' : '' ?>>
                                                <?= e($pk['nama_paket']) ?>
                                            </option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>

                                <div class="form-group">
                                    <label class="form-label">Status</label>
                                    <select name="status" class="form-input">
                                        <option value="baru" <?= $psb_edit['status'] == 'baru' ? 'selected' : '' ?>>Baru</option>
                                        <option value="survey" <?= $psb_edit['status'] == 'survey' ? 'selected' : '' ?>>Survey</option>
                                        <option value="menunggu_bayar" <?= $psb_edit['status'] == 'menunggu_bayar' ? 'selected' : '' ?>>Menunggu Bayar</option>
                                        <option value="dikerjakan" <?= $psb_edit['status'] == 'dikerjakan' ? 'selected' : '' ?>>Dikerjakan</option>
                                        <option value="selesai" <?= $psb_edit['status'] == 'selesai' ? 'selected' : '' ?>>Selesai</option>
                                        <option value="ditolak" <?= $psb_edit['status'] == 'ditolak' ? 'selected' : '' ?>>Ditolak</option>
                                    </select>
                                </div>
                            </div>

                            <div class="form-row">
                                <div class="form-group">
                                    <label class="form-label">Teknisi</label>
                                    <select name="teknisi_id" class="form-input">
                                        <option value="0">-- Belum Ditugaskan --</option>
                                        <?php foreach ($teknisi_list as $t): ?>
                                            <option value="<?= $t['id'] ?>" <?= $psb_edit['teknisi_id'] == $t['id'] ? 'selected' : '' ?>>
                                                <?= e($t['nama']) ?>
                                            </option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>

                                <div class="form-group">
                                    <label class="form-label">Hasil Survey</label>
                                    <input type="text" name="hasil_survey" class="form-input" value="<?= e($psb_edit['hasil_survey']) ?>" placeholder="Contoh: Butuh kabel 85 meter dari ODP 3">
                                </div>
                            </div>

                            <div class="form-row">
                                <button type="submit" class="btn btn-primary">
                                    <i class="bi bi-save"></i>
                                    Update PSB
                                </button>
                                
                                <?php if ($psb_edit['status'] == 'selesai'): ?>
                                    <a href="psb.php?action=konversi&id=<?= $psb_edit['id'] ?>&csrf_token=<?= csrf_token() ?>" 
                                       class="btn btn-success"
                                       onclick="return confirm('Konversi PSB ini menjadi pelanggan?')">
                                        <i class="bi bi-person-check"></i>
                                        Konversi ke Pelanggan
                                    </a>
                                <?php endif; ?>
                            </div>
                        </form>
                    </div>

                <!-- LIST PSB -->
                <?php else: ?>
                    <div class="card">
                        <div class="card-header">
                            <h2 class="card-title">Daftar Permintaan PSB</h2>
                            <a href="daftar.php" target="_blank" class="btn btn-secondary">
                                <i class="bi bi-box-arrow-up-right"></i>
                                Buka Form Publik
                            </a>
                        </div>

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
                                    <?php if (empty($psb_list)): ?>
                                        <tr>
                                            <td colspan="8" style="text-align: center; color: var(--text-secondary); padding: 40px;">
                                                <i class="bi bi-inbox" style="font-size: 48px; display: block; margin-bottom: 12px;"></i>
                                                Belum ada permintaan PSB.
                                            </td>
                                        </tr>
                                    <?php else: ?>
                                        <?php foreach ($psb_list as $i => $p): ?>
                                            <tr>
                                                <td><?= $i + 1 ?></td>
                                                <td>
                                                    <?php if ($p['foto_rumah']): ?>
                                                        <a href="uploads/foto_psb/<?= e($p['foto_rumah']) ?>" target="_blank">
                                                            <img src="uploads/foto_psb/<?= e($p['foto_rumah']) ?>" 
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
                                                        'sudah_konversi' => ['label' => 'Sudah Konversi', 'class' => 'badge-success'],
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
                                                    
                                                    <?php if ($p['status'] == 'selesai'): ?>
                                                        <a href="psb.php?action=konversi&id=<?= $p['id'] ?>&csrf_token=<?= csrf_token() ?>" 
                                                           class="btn btn-success btn-sm" 
                                                           title="Konversi ke Pelanggan"
                                                           onclick="return confirm('Konversi PSB ini menjadi pelanggan?')">
                                                            <i class="bi bi-person-check"></i>
                                                        </a>
                                                    <?php else: ?>
                                                        <a href="psb.php?action=edit&id=<?= $p['id'] ?>" class="btn btn-warning btn-sm" title="Edit">
                                                            <i class="bi bi-pencil"></i>
                                                        </a>
                                                        <a href="psb.php?action=hapus&id=<?= $p['id'] ?>&csrf_token=<?= csrf_token() ?>" 
                                                           class="btn btn-danger btn-sm" 
                                                           title="Hapus" 
                                                           onclick="return confirm('Yakin mau hapus data PSB ini?')">
                                                            <i class="bi bi-trash"></i>
                                                        </a>
                                                    <?php endif; ?>
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