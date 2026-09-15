<?php
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../includes/auth.php';
require_login();

$pdo = db();
$paket_list = $pdo->query("SELECT id, nama_paket, harga_bulanan FROM paket WHERE status = 1 ORDER BY harga_bulanan ASC")->fetchAll();

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nama = trim($_POST['nama'] ?? '');
    $no_hp = trim($_POST['no_hp'] ?? '');
    $alamat = trim($_POST['alamat'] ?? '');
    $desa = trim($_POST['desa'] ?? '');
    $kecamatan = trim($_POST['kecamatan'] ?? '');
    $mac_address = trim($_POST['mac_address'] ?? '');
    $username_pppoe = trim($_POST['username_pppoe'] ?? '');
    $password_pppoe = trim($_POST['password_pppoe'] ?? '');
    $paket_id = intval($_POST['paket_id'] ?? 0);
    $status = $_POST['status'] ?? 'aktif';
    $tgl_pasang = $_POST['tgl_pasang'] ?? date('Y-m-d');

    if ($nama === '' || $no_hp === '') {
        $error = 'Nama dan No HP wajib diisi.';
    } else {
        // Generate kode pelanggan otomatis
        $kode = 'PLG-' . strtoupper(substr(md5(uniqid()), 0, 6));

        $stmt = $pdo->prepare('INSERT INTO pelanggan (kode_pelanggan, nama, no_hp, alamat, desa, kecamatan, mac_address, username_pppoe, password_pppoe, paket_id, status, tgl_pasang) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)');
        $stmt->execute([$kode, $nama, $no_hp, $alamat, $desa, $kecamatan, $mac_address, $username_pppoe, $password_pppoe, $paket_id, $status, $tgl_pasang]);

        $_SESSION['flash'] = ['type' => 'success', 'message' => "Pelanggan $nama berhasil ditambahkan dengan kode $kode!"];
        header('Location: index.php');
        exit;
    }
}

$page_title = 'Tambah Pelanggan';
?>
<!DOCTYPE html>
<html lang="id" data-theme="light">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Tambah Pelanggan - Billing RT/RW Net</title>
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
                        <h2 class="card-title">Tambah Pelanggan Baru</h2>
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
                                <input type="text" name="nama" class="form-input" placeholder="Nama pelanggan" required>
                            </div>

                            <div class="form-group">
                                <label class="form-label">No HP / WhatsApp *</label>
                                <input type="text" name="no_hp" class="form-input" placeholder="08xxxxxxxxxx" required>
                            </div>
                        </div>

                        <div class="form-group">
                            <label class="form-label">Alamat</label>
                            <textarea name="alamat" class="form-input" rows="2" placeholder="Alamat lengkap"></textarea>
                        </div>

                        <div class="form-row">
                            <div class="form-group">
                                <label class="form-label">Desa / Kelurahan</label>
                                <input type="text" name="desa" class="form-input" placeholder="Nama desa">
                            </div>

                            <div class="form-group">
                                <label class="form-label">Kecamatan</label>
                                <input type="text" name="kecamatan" class="form-input" placeholder="Nama kecamatan">
                            </div>
                        </div>

                        <div class="form-row">
                            <div class="form-group">
                                <label class="form-label">MAC Address</label>
                                <input type="text" name="mac_address" class="form-input" placeholder="AA:BB:CC:DD:EE:FF" style="text-transform: uppercase;">
                            </div>

                            <div class="form-group">
                                <label class="form-label">Tanggal Pasang</label>
                                <input type="date" name="tgl_pasang" class="form-input" value="<?= date('Y-m-d') ?>">
                            </div>
                        </div>

                        <div class="form-row">
                            <div class="form-group">
                                <label class="form-label">Username PPPoE</label>
                                <input type="text" name="username_pppoe" class="form-input" placeholder="Username PPPoE">
                            </div>

                            <div class="form-group">
                                <label class="form-label">Password PPPoE</label>
                                <input type="text" name="password_pppoe" class="form-input" placeholder="Password PPPoE">
                            </div>
                        </div>

                        <div class="form-row">
                            <div class="form-group">
                                <label class="form-label">Paket Internet</label>
                                <select name="paket_id" class="form-input">
                                    <option value="0">-- Pilih Paket --</option>
                                    <?php foreach ($paket_list as $pk): ?>
                                        <option value="<?= $pk['id'] ?>"><?= $pk['nama_paket'] ?> - Rp <?= number_format($pk['harga_bulanan'], 0, ',', '.') ?></option>
                                    <?php endforeach; ?>
                                </select>
                            </div>

                            <div class="form-group">
                                <label class="form-label">Status</label>
                                <select name="status" class="form-input">
                                    <option value="aktif">Aktif</option>
                                    <option value="isolir">Isolir</option>
                                    <option value="berhenti">Berhenti</option>
                                </select>
                            </div>
                        </div>

                        <button type="submit" class="btn btn-primary">
                            <i class="bi bi-save"></i>
                            Simpan Pelanggan
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <?php include __DIR__ . '/../includes/footer.php'; ?>
</body>
</html>