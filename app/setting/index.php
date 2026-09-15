<?php
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../includes/auth.php';
require_admin();

$pdo = db();

// Ambil data setting
$stmt = $pdo->query("SELECT * FROM pengaturan LIMIT 1");
$setting = $stmt->fetch();

// Jika belum ada data, buat default
if (!$setting) {
    $pdo->exec("INSERT INTO pengaturan (nama_usaha) VALUES ('RT/RW Net')");
    $setting = $pdo->query("SELECT * FROM pengaturan LIMIT 1")->fetch();
}

$error = '';

// Handle POST
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nama_usaha = trim($_POST['nama_usaha'] ?? '');
    $alamat = trim($_POST['alamat'] ?? '');
    $no_wa = trim($_POST['no_wa'] ?? '');
    $jatuh_tempo = intval($_POST['jatuh_tempo_tanggal'] ?? 5);
    $denda = floatval($_POST['denda'] ?? 0);
    $wa_hari = trim($_POST['wa_reminder_hari'] ?? '3,1,0');
    $wa_jam = $_POST['wa_jam_kirim'] ?? '08:00';
    $api_key_wa = trim($_POST['api_key_wa'] ?? '');
    $deskripsi_psb = trim($_POST['deskripsi_psb'] ?? '');
    $syarat_ketentuan = trim($_POST['syarat_ketentuan'] ?? '');

    if ($nama_usaha === '') {
        $error = 'Nama usaha wajib diisi.';
    } else {
        try {
            $stmt = $pdo->prepare("UPDATE pengaturan SET 
                nama_usaha = ?, 
                alamat = ?, 
                no_wa = ?, 
                jatuh_tempo_tanggal = ?, 
                denda = ?, 
                wa_reminder_hari = ?, 
                wa_jam_kirim = ?, 
                api_key_wa = ?, 
                deskripsi_psb = ?, 
                syarat_ketentuan = ?");
            
            $stmt->execute([
                $nama_usaha, 
                $alamat, 
                $no_wa, 
                $jatuh_tempo, 
                $denda, 
                $wa_hari, 
                $wa_jam, 
                $api_key_wa, 
                $deskripsi_psb, 
                $syarat_ketentuan
            ]);

            $_SESSION['flash'] = ['type' => 'success', 'message' => 'Pengaturan berhasil disimpan!'];
            header('Location: index.php');
            exit;
        } catch (PDOException $e) {
            $error = 'Gagal menyimpan: ' . $e->getMessage();
        }
    }
    
    // Reload data setelah POST gagal
    $setting = $pdo->query("SELECT * FROM pengaturan LIMIT 1")->fetch();
}

$page_title = 'Pengaturan';
?>
<!DOCTYPE html>
<html lang="id" data-theme="light">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Pengaturan - Billing RT/RW Net</title>
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
                        <?= htmlspecialchars($error) ?>
                    </div>
                <?php endif; ?>

                <form method="post">
                    <!-- Informasi Usaha -->
                    <div class="card">
                        <div class="card-header">
                            <h2 class="card-title">
                                <i class="bi bi-building"></i>
                                Informasi Usaha
                            </h2>
                        </div>

                        <div class="form-row">
                            <div class="form-group">
                                <label class="form-label">Nama Usaha *</label>
                                <input type="text" name="nama_usaha" class="form-input" value="<?= htmlspecialchars($setting['nama_usaha']) ?>" required>
                            </div>

                            <div class="form-group">
                                <label class="form-label">No WhatsApp Admin</label>
                                <input type="text" name="no_wa" class="form-input" value="<?= htmlspecialchars($setting['no_wa'] ?? '') ?>" placeholder="08xxxxxxxxxx">
                            </div>
                        </div>

                        <div class="form-group">
                            <label class="form-label">Alamat Usaha</label>
                            <textarea name="alamat" class="form-input" rows="2"><?= htmlspecialchars($setting['alamat'] ?? '') ?></textarea>
                        </div>
                    </div>

                    <!-- Tagihan -->
                    <div class="card">
                        <div class="card-header">
                            <h2 class="card-title">
                                <i class="bi bi-receipt"></i>
                                Pengaturan Tagihan
                            </h2>
                        </div>

                        <div class="form-row">
                            <div class="form-group">
                                <label class="form-label">Tanggal Jatuh Tempo (per bulan)</label>
                                <input type="number" name="jatuh_tempo_tanggal" class="form-input" value="<?= $setting['jatuh_tempo_tanggal'] ?? 5 ?>" min="1" max="28">
                                <small style="color: var(--text-secondary); font-size: 12px;">Tanggal 1-28 setiap bulannya</small>
                            </div>

                            <div class="form-group">
                                <label class="form-label">Denda Keterlambatan (Rp)</label>
                                <input type="number" name="denda" class="form-input" value="<?= $setting['denda'] ?? 0 ?>" min="0" step="1000">
                                <small style="color: var(--text-secondary); font-size: 12px;">Isi 0 jika tidak ada denda</small>
                            </div>
                        </div>
                    </div>

                    <!-- WhatsApp -->
                    <div class="card">
                        <div class="card-header">
                            <h2 class="card-title">
                                <i class="bi bi-whatsapp"></i>
                                Pengaturan WhatsApp
                            </h2>
                        </div>

                        <div class="form-row">
                            <div class="form-group">
                                <label class="form-label">Reminder H-berapa saja (pisahkan koma)</label>
                                <input type="text" name="wa_reminder_hari" class="form-input" value="<?= htmlspecialchars($setting['wa_reminder_hari'] ?? '3,1,0') ?>" placeholder="3,1,0">
                                <small style="color: var(--text-secondary); font-size: 12px;">Contoh: 3,1,0 artinya kirim reminder H-3, H-1, dan Hari-H</small>
                            </div>

                            <div class="form-group">
                                <label class="form-label">Jam Kirim Reminder</label>
                                <input type="time" name="wa_jam_kirim" class="form-input" value="<?= $setting['wa_jam_kirim'] ?? '08:00' ?>">
                            </div>
                        </div>

                        <div class="form-group">
                            <label class="form-label">API Key WhatsApp Gateway</label>
                            <input type="password" name="api_key_wa" class="form-input" value="<?= htmlspecialchars($setting['api_key_wa'] ?? '') ?>" placeholder="Masukkan API key dari provider WA gateway">
                            <small style="color: var(--text-secondary); font-size: 12px;">Contoh: Fonnte, Wablas, atau provider lainnya</small>
                        </div>
                    </div>

                    <!-- Pendaftaran PSB -->
                    <div class="card">
                        <div class="card-header">
                            <h2 class="card-title">
                                <i class="bi bi-file-text"></i>
                                Pendaftaran PSB
                            </h2>
                        </div>

                        <div class="form-group">
                            <label class="form-label">Deskripsi Form PSB</label>
                            <textarea name="deskripsi_psb" class="form-input" rows="2" placeholder="Contoh: Daftar sekarang, gratis pemasangan!"><?= htmlspecialchars($setting['deskripsi_psb'] ?? '') ?></textarea>
                            <small style="color: var(--text-secondary); font-size: 12px;">Teks ini muncul di bawah judul form pendaftaran</small>
                        </div>

                        <div class="form-group">
                            <label class="form-label">Syarat & Ketentuan</label>
                            <textarea name="syarat_ketentuan" class="form-input" rows="8" placeholder="Tulis syarat dan ketentuan di sini..."><?= htmlspecialchars($setting['syarat_ketentuan'] ?? '') ?></textarea>
                            <small style="color: var(--text-secondary); font-size: 12px;">Teks ini akan muncul di form pendaftaran dan harus disetujui calon pelanggan</small>
                        </div>
                    </div>

                    <button type="submit" class="btn btn-primary">
                        <i class="bi bi-save"></i>
                        Simpan Pengaturan
                    </button>
                </form>
            </div>
        </div>
    </div>

    <?php include __DIR__ . '/../includes/footer.php'; ?>
</body>
</html>