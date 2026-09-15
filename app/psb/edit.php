<?php
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../includes/auth.php';
require_login();

$pdo = db();
$id = intval($_GET['id'] ?? 0);

$stmt = $pdo->prepare('SELECT * FROM psb WHERE id = ?');
$stmt->execute([$id]);
$psb = $stmt->fetch();

if (!$psb) {
    header('Location: index.php');
    exit;
}

// Ambil daftar teknisi
$teknisi_list = $pdo->query("SELECT id, nama FROM users WHERE role = 'teknisi' AND status = 1")->fetchAll();

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nama = trim($_POST['nama'] ?? '');
    $no_hp = trim($_POST['no_hp'] ?? '');
    $alamat = trim($_POST['alamat'] ?? '');
    $desa = trim($_POST['desa'] ?? '');
    $kecamatan = trim($_POST['kecamatan'] ?? '');
    $catatan = trim($_POST['catatan'] ?? '');
    $status = $_POST['status'] ?? 'baru';
    $teknisi_id = intval($_POST['teknisi_id'] ?? 0);
    $hasil_survey = trim($_POST['hasil_survey'] ?? '');

    if ($nama === '' || $no_hp === '') {
        $error = 'Nama dan No HP wajib diisi.';
    } else {
        $teknisi_id = $teknisi_id > 0 ? $teknisi_id : null;

        $stmt = $pdo->prepare('UPDATE psb SET nama = ?, no_hp = ?, alamat = ?, desa = ?, kecamatan = ?, catatan = ?, status = ?, teknisi_id = ?, hasil_survey = ? WHERE id = ?');
        $stmt->execute([$nama, $no_hp, $alamat, $desa, $kecamatan, $catatan, $status, $teknisi_id, $hasil_survey, $id]);

        $_SESSION['flash'] = ['type' => 'success', 'message' => "Data PSB atas nama $nama berhasil diupdate!"];
        header('Location: index.php');
        exit;
    }
}

$page_title = 'Edit PSB';
?>
<!DOCTYPE html>
<html lang="id" data-theme="light">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Edit PSB - Billing RT/RW Net</title>
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
                        <h2 class="card-title">Edit Data PSB</h2>
                        <a href="index.php" class="btn btn-secondary">
                            <i class="bi bi-arrow-left"></i>
                            Kembali
                        </a>
                    </div>

                    <?php if ($error): ?>
                        <div class="alert alert-danger">
                            <i class="bi bi-exclamation-circle"></i>
                            <?= htmlspecialchars($error) ?>
                        </div>
                    <?php endif; ?>

                    <form method="post">
                        <div class="form-row">
                            <div class="form-group">
                                <label class="form-label">Nama Lengkap *</label>
                                <input type="text" name="nama" class="form-input" value="<?= htmlspecialchars($psb['nama']) ?>" required>
                            </div>

                            <div class="form-group">
                                <label class="form-label">No HP / WhatsApp *</label>
                                <input type="text" name="no_hp" class="form-input" value="<?= htmlspecialchars($psb['no_hp']) ?>" required>
                            </div>
                        </div>

                        <div class="form-group">
                            <label class="form-label">Alamat</label>
                            <textarea name="alamat" class="form-input" rows="2"><?= htmlspecialchars($psb['alamat']) ?></textarea>
                        </div>

                        <div class="form-row">
                            <div class="form-group">
                                <label class="form-label">Desa / Kelurahan</label>
                                <input type="text" name="desa" class="form-input" value="<?= htmlspecialchars($psb['desa']) ?>">
                            </div>

                            <div class="form-group">
                                <label class="form-label">Kecamatan</label>
                                <input type="text" name="kecamatan" class="form-input" value="<?= htmlspecialchars($psb['kecamatan']) ?>">
                            </div>
                        </div>

                        <div class="form-group">
                            <label class="form-label">Catatan</label>
                            <textarea name="catatan" class="form-input" rows="2"><?= htmlspecialchars($psb['catatan']) ?></textarea>
                        </div>

                        <div class="form-row">
                            <div class="form-group">
                                <label class="form-label">Status</label>
                                <select name="status" class="form-input">
                                    <option value="baru" <?= $psb['status'] == 'baru' ? 'selected' : '' ?>>Baru</option>
                                    <option value="survey" <?= $psb['status'] == 'survey' ? 'selected' : '' ?>>Survey</option>
                                    <option value="menunggu_bayar" <?= $psb['status'] == 'menunggu_bayar' ? 'selected' : '' ?>>Menunggu Bayar</option>
                                    <option value="dikerjakan" <?= $psb['status'] == 'dikerjakan' ? 'selected' : '' ?>>Dikerjakan</option>
                                    <option value="selesai" <?= $psb['status'] == 'selesai' ? 'selected' : '' ?>>Selesai</option>
                                    <option value="ditolak" <?= $psb['status'] == 'ditolak' ? 'selected' : '' ?>>Ditolak</option>
                                </select>
                            </div>

                            <div class="form-group">
                                <label class="form-label">Teknisi</label>
                                <select name="teknisi_id" class="form-input">
                                    <option value="0">-- Belum Ditugaskan --</option>
                                    <?php foreach ($teknisi_list as $t): ?>
                                        <option value="<?= $t['id'] ?>" <?= $psb['teknisi_id'] == $t['id'] ? 'selected' : '' ?>>
                                            <?= htmlspecialchars($t['nama']) ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                        </div>

                        <div class="form-group">
                            <label class="form-label">Hasil Survey</label>
                            <textarea name="hasil_survey" class="form-input" rows="3" placeholder="Contoh: Butuh kabel 85 meter dari ODP 3, sinyal kuat"><?= htmlspecialchars($psb['hasil_survey']) ?></textarea>
                        </div>

                        <button type="submit" class="btn btn-primary">
                            <i class="bi bi-save"></i>
                            Update PSB
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <?php include __DIR__ . '/../includes/footer.php'; ?>
</body>
</html>