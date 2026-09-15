<?php
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../includes/auth.php';
require_login();

$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nama = trim($_POST['nama_paket'] ?? '');
    $kecepatan = trim($_POST['kecepatan'] ?? '');
    $harga = floatval($_POST['harga_bulanan'] ?? 0);
    $status = intval($_POST['status'] ?? 1);

    if ($nama === '' || $kecepatan === '' || $harga <= 0) {
        $error = 'Semua field wajib diisi dengan benar.';
    } else {
        $stmt = db()->prepare('INSERT INTO paket (nama_paket, kecepatan, harga_bulanan, status) VALUES (?, ?, ?, ?)');
        $stmt->execute([$nama, $kecepatan, $harga, $status]);
        
        $_SESSION['flash'] = ['type' => 'success', 'message' => 'Paket berhasil ditambahkan!'];
        header('Location: index.php');
        exit;
    }
}
?>
<!DOCTYPE html>
<html lang="id" data-theme="light">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Tambah Paket - Billing RT/RW Net</title>
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
                        <h2 class="card-title">Tambah Paket Baru</h2>
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
            </div>
        </div>
    </div>

    <script>
        const savedTheme = localStorage.getItem('theme') || 'light';
        document.documentElement.setAttribute('data-theme', savedTheme);

        function toggleTheme() {
            const current = document.documentElement.getAttribute('data-theme');
            const newTheme = current === 'light' ? 'dark' : 'light';
            document.documentElement.setAttribute('data-theme', newTheme);
            localStorage.setItem('theme', newTheme);
        }

        function toggleSidebar() {
            document.querySelector('.sidebar').classList.toggle('open');
        }
    </script>
</body>
</html>