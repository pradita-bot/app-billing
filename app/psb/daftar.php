<?php
require_once __DIR__ . '/../config/database.php';

$error = '';
$success = false;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nama = trim($_POST['nama'] ?? '');
    $no_hp = trim($_POST['no_hp'] ?? '');
    $alamat = trim($_POST['alamat'] ?? '');
    $desa = trim($_POST['desa'] ?? '');
    $kecamatan = trim($_POST['kecamatan'] ?? '');
    $catatan = trim($_POST['catatan'] ?? '');

    if ($nama === '' || $no_hp === '') {
        $error = 'Nama dan No HP wajib diisi.';
    } else {
        $stmt = db()->prepare('INSERT INTO psb (nama, no_hp, alamat, desa, kecamatan, catatan, status) VALUES (?, ?, ?, ?, ?, ?, ?)');
        $stmt->execute([$nama, $no_hp, $alamat, $desa, $kecamatan, $catatan, 'baru']);

        $success = true;
    }
}
?>
<!DOCTYPE html>
<html lang="id" data-theme="light">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Pendaftaran Pelanggan Baru - RT/RW Net</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" rel="stylesheet">
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        :root {
            --primary: #1e40af;
            --primary-light: #3b82f6;
            --bg: #f8fafc;
            --card-bg: #ffffff;
            --text: #0f172a;
            --text-secondary: #64748b;
            --border: #e2e8f0;
            --shadow: 0 10px 40px rgba(0, 0, 0, 0.08);
        }

        [data-theme="dark"] {
            --primary: #3b82f6;
            --primary-light: #60a5fa;
            --bg: #0f172a;
            --card-bg: #1e293b;
            --text: #f1f5f9;
            --text-secondary: #94a3b8;
            --border: #334155;
            --shadow: 0 10px 40px rgba(0, 0, 0, 0.4);
        }

        body {
            font-family: 'Inter', sans-serif;
            background: var(--bg);
            color: var(--text);
            min-height: 100vh;
            padding: 40px 20px;
            transition: all 0.3s ease;
        }

        .container {
            max-width: 600px;
            margin: 0 auto;
        }

        .theme-toggle {
            position: fixed;
            top: 20px;
            right: 20px;
            background: var(--card-bg);
            border: 1px solid var(--border);
            border-radius: 50px;
            width: 50px;
            height: 50px;
            display: flex;
            align-items: center;
            justify-content: center;
            cursor: pointer;
            font-size: 20px;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
            z-index: 100;
            color: var(--text);
        }

        .header {
            text-align: center;
            margin-bottom: 32px;
        }

        .logo-icon {
            width: 64px;
            height: 64px;
            background: linear-gradient(135deg, var(--primary), var(--primary-light));
            border-radius: 16px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 32px;
            color: white;
            margin: 0 auto 16px;
        }

        .header h1 {
            font-size: 24px;
            font-weight: 700;
            margin-bottom: 8px;
        }

        .header p {
            color: var(--text-secondary);
            font-size: 14px;
        }

        .card {
            background: var(--card-bg);
            border-radius: 16px;
            padding: 32px;
            box-shadow: var(--shadow);
            border: 1px solid var(--border);
        }

        .form-group {
            margin-bottom: 20px;
        }

        label {
            display: block;
            font-size: 14px;
            font-weight: 500;
            margin-bottom: 8px;
        }

        input, textarea, select {
            width: 100%;
            padding: 12px 16px;
            border: 1px solid var(--border);
            border-radius: 8px;
            font-size: 14px;
            font-family: inherit;
            background: var(--bg);
            color: var(--text);
        }

        input:focus, textarea:focus {
            outline: none;
            border-color: var(--primary);
            box-shadow: 0 0 0 3px rgba(59, 130, 246, 0.1);
        }

        .btn {
            width: 100%;
            padding: 14px;
            background: var(--primary);
            color: white;
            border: none;
            border-radius: 8px;
            font-size: 15px;
            font-weight: 600;
            cursor: pointer;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 8px;
        }

        .btn:hover {
            background: var(--primary-light);
        }

        .alert {
            padding: 14px 18px;
            border-radius: 8px;
            margin-bottom: 20px;
            font-size: 14px;
        }

        .alert-success {
            background: rgba(22, 163, 74, 0.1);
            color: var(--success);
            border: 1px solid rgba(22, 163, 74, 0.2);
        }

        .alert-danger {
            background: rgba(220, 38, 38, 0.1);
            color: var(--danger);
            border: 1px solid rgba(220, 38, 38, 0.2);
        }

        .success-message {
            text-align: center;
            padding: 40px 20px;
        }

        .success-icon {
            width: 80px;
            height: 80px;
            background: rgba(22, 163, 74, 0.1);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 40px;
            color: var(--success);
            margin: 0 auto 20px;
        }
    </style>
</head>
<body>
    <div class="theme-toggle" onclick="toggleTheme()">
        <i class="bi bi-moon-fill" id="theme-icon"></i>
    </div>

    <div class="container">
        <div class="header">
            <div class="logo-icon">
                <i class="bi bi-wifi"></i>
            </div>
            <h1>Pendaftaran Pelanggan Baru</h1>
            <p>Isi form di bawah untuk mendaftar layanan internet RT/RW Net</p>
        </div>

        <div class="card">
            <?php if ($success): ?>
                <div class="success-message">
                    <div class="success-icon">
                        <i class="bi bi-check-lg"></i>
                    </div>
                    <h3 style="margin-bottom: 8px;">Pendaftaran Berhasil!</h3>
                    <p style="color: var(--text-secondary); font-size: 14px;">
                        Data Anda sudah kami terima. Tim kami akan segera menghubungi Anda untuk proses survey dan pemasangan.
                    </p>
                    <a href="daftar.php" class="btn" style="margin-top: 24px;">Daftar Lagi</a>
                </div>
            <?php else: ?>
                <?php if ($error): ?>
                    <div class="alert alert-danger">
                        <i class="bi bi-exclamation-circle"></i>
                        <?= htmlspecialchars($error) ?>
                    </div>
                <?php endif; ?>

                <form method="post">
                    <div class="form-group">
                        <label>Nama Lengkap *</label>
                        <input type="text" name="nama" placeholder="Masukkan nama lengkap" required>
                    </div>

                    <div class="form-group">
                        <label>No HP / WhatsApp *</label>
                        <input type="text" name="no_hp" placeholder="08xxxxxxxxxx" required>
                    </div>

                    <div class="form-group">
                        <label>Alamat Lengkap</label>
                        <textarea name="alamat" rows="2" placeholder="Alamat lengkap Anda"></textarea>
                    </div>

                    <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 16px;">
                        <div class="form-group">
                            <label>Desa / Kelurahan</label>
                            <input type="text" name="desa" placeholder="Nama desa">
                        </div>

                        <div class="form-group">
                            <label>Kecamatan</label>
                            <input type="text" name="kecamatan" placeholder="Nama kecamatan">
                        </div>
                    </div>

                    <div class="form-group">
                        <label>Catatan (opsional)</label>
                        <textarea name="catatan" rows="2" placeholder="Catatan tambahan, misalnya: rumah cat biru dekat masjid"></textarea>
                    </div>

                    <button type="submit" class="btn">
                        <i class="bi bi-send"></i>
                        Kirim Pendaftaran
                    </button>
                </form>
            <?php endif; ?>
        </div>
    </div>

    <script>
        const savedTheme = localStorage.getItem('theme') || 'light';
        document.documentElement.setAttribute('data-theme', savedTheme);
        updateThemeIcon();

        function toggleTheme() {
            const current = document.documentElement.getAttribute('data-theme');
            const newTheme = current === 'light' ? 'dark' : 'light';
            document.documentElement.setAttribute('data-theme', newTheme);
            localStorage.setItem('theme', newTheme);
            updateThemeIcon();
        }

        function updateThemeIcon() {
            const theme = document.documentElement.getAttribute('data-theme');
            document.getElementById('theme-icon').className = theme === 'light' ? 'bi bi-moon-fill' : 'bi bi-sun-fill';
        }
    </script>
</body>
</html>