<?php
require_once __DIR__ . '/../config/database.php';

$pdo = db();

// Ambil paket aktif
$paket_list = $pdo->query("SELECT id, nama_paket, kecepatan, harga_bulanan FROM paket WHERE status = 1 ORDER BY harga_bulanan ASC")->fetchAll();

// Ambil setting
$setting = $pdo->query("SELECT * FROM pengaturan LIMIT 1")->fetch();

$error = '';
$success = false;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nama = trim($_POST['nama'] ?? '');
    $no_hp = trim($_POST['no_hp'] ?? '');
    $alamat = trim($_POST['alamat'] ?? '');
    $desa = trim($_POST['desa'] ?? '');
    $kecamatan = trim($_POST['kecamatan'] ?? '');
    $catatan = trim($_POST['catatan'] ?? '');
    $paket_id = intval($_POST['paket_id'] ?? 0);
    $latitude = $_POST['latitude'] ?? null;
    $longitude = $_POST['longitude'] ?? null;
    $setuju = isset($_POST['setuju']) ? 1 : 0;

    if ($nama === '' || $no_hp === '') {
        $error = 'Nama dan No HP wajib diisi.';
    } elseif ($paket_id === 0) {
        $error = 'Silakan pilih paket internet.';
    } elseif (!$setuju) {
        $error = 'Anda harus menyetujui syarat dan ketentuan.';
    } else {
        // Handle upload foto
        // Handle upload foto dengan keamanan lebih ketat
$foto_rumah = null;
if (isset($_FILES['foto_rumah']) && $_FILES['foto_rumah']['error'] === UPLOAD_ERR_OK) {
    $upload_dir = __DIR__ . '/../uploads/foto_psb/';
    
    // Buat folder jika belum ada
    if (!is_dir($upload_dir)) {
        mkdir($upload_dir, 0755, true);
    }

    // Verifikasi file adalah gambar asli
    $check = getimagesize($_FILES['foto_rumah']['tmp_name']);
    if ($check === false) {
        $error = 'File yang diupload bukan gambar yang valid.';
    } else {
        $file_ext = strtolower(pathinfo($_FILES['foto_rumah']['name'], PATHINFO_EXTENSION));
        $allowed_ext = ['jpg', 'jpeg', 'png', 'webp'];
        $allowed_mime = ['image/jpeg', 'image/png', 'image/webp'];

        if (!in_array($file_ext, $allowed_ext)) {
            $error = 'Format foto harus JPG, PNG, atau WEBP.';
        } elseif (!in_array($check['mime'], $allowed_mime)) {
            $error = 'MIME type file tidak valid.';
        } elseif ($_FILES['foto_rumah']['size'] > 5 * 1024 * 1024) {
            $error = 'Ukuran foto maksimal 5 MB.';
        } else {
            // Generate nama file yang aman
            $foto_rumah = 'psb_' . date('Ymd_His') . '_' . uniqid() . '.' . $file_ext;
            
            if (!move_uploaded_file($_FILES['foto_rumah']['tmp_name'], $upload_dir . $foto_rumah)) {
                $error = 'Gagal menyimpan file.';
                $foto_rumah = null;
            }
        }
    }
}

        if ($error === '') {
            $stmt = $pdo->prepare('INSERT INTO psb (nama, no_hp, alamat, desa, kecamatan, catatan, paket_id, latitude, longitude, foto_rumah, status) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)');
            $stmt->execute([$nama, $no_hp, $alamat, $desa, $kecamatan, $catatan, $paket_id, $latitude, $longitude, $foto_rumah, 'baru']);

            $success = true;
        }
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
        * { margin: 0; padding: 0; box-sizing: border-box; }

        :root {
            --primary: #1e40af;
            --primary-light: #3b82f6;
            --bg: #f8fafc;
            --card-bg: #ffffff;
            --text: #0f172a;
            --text-secondary: #64748b;
            --border: #e2e8f0;
            --shadow: 0 10px 40px rgba(0, 0, 0, 0.08);
            --success: #16a34a;
            --danger: #dc2626;
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
        }

        .container { max-width: 700px; margin: 0 auto; }

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

        .header { text-align: center; margin-bottom: 32px; }

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

        .header h1 { font-size: 24px; font-weight: 700; margin-bottom: 8px; }
        .header p { color: var(--text-secondary); font-size: 14px; }

        .card {
            background: var(--card-bg);
            border-radius: 16px;
            padding: 32px;
            box-shadow: var(--shadow);
            border: 1px solid var(--border);
            margin-bottom: 24px;
        }

        .section-title {
            font-size: 16px;
            font-weight: 600;
            margin-bottom: 20px;
            display: flex;
            align-items: center;
            gap: 8px;
            color: var(--primary);
        }

        .form-group { margin-bottom: 20px; }

        label {
            display: block;
            font-size: 14px;
            font-weight: 500;
            margin-bottom: 8px;
        }

        label.required::after {
            content: ' *';
            color: var(--danger);
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

        input:focus, textarea:focus, select:focus {
            outline: none;
            border-color: var(--primary);
            box-shadow: 0 0 0 3px rgba(59, 130, 246, 0.1);
        }

        .form-row {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 16px;
        }

        /* Paket Cards */
        .paket-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 16px;
            margin-bottom: 20px;
        }

        .paket-card {
            border: 2px solid var(--border);
            border-radius: 12px;
            padding: 20px;
            cursor: pointer;
            transition: all 0.2s ease;
            text-align: center;
        }

        .paket-card:hover {
            border-color: var(--primary-light);
        }

        .paket-card.selected {
            border-color: var(--primary);
            background: rgba(59, 130, 246, 0.05);
        }

        .paket-card input[type="radio"] {
            display: none;
        }

        .paket-name {
            font-size: 16px;
            font-weight: 600;
            margin-bottom: 4px;
        }

        .paket-speed {
            font-size: 13px;
            color: var(--text-secondary);
            margin-bottom: 12px;
        }

        .paket-price {
            font-size: 18px;
            font-weight: 700;
            color: var(--primary);
        }

        .paket-price span {
            font-size: 12px;
            font-weight: 400;
            color: var(--text-secondary);
        }

        /* Location */
        .location-box {
            background: var(--bg);
            border: 1px solid var(--border);
            border-radius: 8px;
            padding: 16px;
            margin-bottom: 16px;
        }

        .location-status {
            display: flex;
            align-items: center;
            gap: 8px;
            font-size: 14px;
            color: var(--text-secondary);
        }

        .location-status.success { color: var(--success); }
        .location-status.error { color: var(--danger); }

        .btn-location {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            padding: 10px 16px;
            background: var(--primary);
            color: white;
            border: none;
            border-radius: 8px;
            font-size: 14px;
            cursor: pointer;
            margin-top: 12px;
        }

        .btn-location:hover {
            opacity: 0.9;
        }

        /* Photo Upload */
        .upload-area {
            border: 2px dashed var(--border);
            border-radius: 12px;
            padding: 32px;
            text-align: center;
            cursor: pointer;
            transition: all 0.2s ease;
        }

        .upload-area:hover {
            border-color: var(--primary);
            background: rgba(59, 130, 246, 0.02);
        }

        .upload-area.has-file {
            border-color: var(--success);
            background: rgba(22, 163, 74, 0.05);
        }

        .upload-icon {
            font-size: 40px;
            color: var(--text-secondary);
            margin-bottom: 12px;
        }

        .upload-text {
            font-size: 14px;
            color: var(--text-secondary);
        }

        .upload-preview {
            max-width: 100%;
            max-height: 200px;
            border-radius: 8px;
            margin-top: 16px;
            display: none;
        }

        /* Syarat & Ketentuan */
        .syarat-box {
            background: var(--bg);
            border: 1px solid var(--border);
            border-radius: 8px;
            padding: 20px;
            max-height: 250px;
            overflow-y: auto;
            margin-bottom: 16px;
            font-size: 13px;
            line-height: 1.7;
            color: var(--text-secondary);
        }

        .checkbox-group {
            display: flex;
            align-items: flex-start;
            gap: 12px;
        }

        .checkbox-group input[type="checkbox"] {
            width: 20px;
            height: 20px;
            margin-top: 2px;
        }

        .checkbox-group label {
            font-size: 14px;
            margin-bottom: 0;
            cursor: pointer;
        }

        /* Buttons */
        .btn-submit {
            width: 100%;
            padding: 16px;
            background: var(--primary);
            color: white;
            border: none;
            border-radius: 8px;
            font-size: 16px;
            font-weight: 600;
            cursor: pointer;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 8px;
            margin-top: 24px;
        }

        .btn-submit:hover {
            background: var(--primary-light);
        }

        /* Alert */
        .alert {
            padding: 14px 18px;
            border-radius: 8px;
            margin-bottom: 20px;
            font-size: 14px;
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .alert-danger {
            background: rgba(220, 38, 38, 0.1);
            color: var(--danger);
            border: 1px solid rgba(220, 38, 38, 0.2);
        }

        /* Success */
        .success-message { text-align: center; padding: 40px 20px; }

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

        @media (max-width: 640px) {
            .form-row { grid-template-columns: 1fr; }
            .paket-grid { grid-template-columns: 1fr; }
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
            <p><?= htmlspecialchars($setting['deskripsi_psb'] ?? 'Isi form di bawah untuk mendaftar layanan internet RT/RW Net') ?></p>
        </div>

        <?php if ($success): ?>
            <div class="card">
                <div class="success-message">
                    <div class="success-icon">
                        <i class="bi bi-check-lg"></i>
                    </div>
                    <h3 style="margin-bottom: 8px;">Pendaftaran Berhasil!</h3>
                    <p style="color: var(--text-secondary); font-size: 14px; line-height: 1.6;">
                        Data Anda sudah kami terima.<br>
                        Tim kami akan segera menghubungi Anda via WhatsApp untuk proses survey dan pemasangan.
                    </p>
                    <a href="daftar.php" class="btn-submit" style="margin-top: 24px; text-decoration: none;">
                        <i class="bi bi-arrow-repeat"></i>
                        Daftar Lagi
                    </a>
                </div>
            </div>
        <?php else: ?>
            <?php if ($error): ?>
                <div class="alert alert-danger">
                    <i class="bi bi-exclamation-circle"></i>
                    <?= htmlspecialchars($error) ?>
                </div>
            <?php endif; ?>

            <form method="post" enctype="multipart/form-data" id="formPsb">
                <!-- Data Diri -->
                <div class="card">
                    <div class="section-title">
                        <i class="bi bi-person"></i>
                        Data Diri
                    </div>

                    <div class="form-row">
                        <div class="form-group">
                            <label class="required">Nama Lengkap</label>
                            <input type="text" name="nama" placeholder="Masukkan nama lengkap" required>
                        </div>

                        <div class="form-group">
                            <label class="required">No HP / WhatsApp</label>
                            <input type="tel" name="no_hp" placeholder="08xxxxxxxxxx" required>
                        </div>
                    </div>

                    <div class="form-group">
                        <label>Alamat Lengkap</label>
                        <textarea name="alamat" rows="2" placeholder="Alamat lengkap Anda"></textarea>
                    </div>

                    <div class="form-row">
                        <div class="form-group">
                            <label>Desa / Kelurahan</label>
                            <input type="text" name="desa" placeholder="Nama desa">
                        </div>

                        <div class="form-group">
                            <label>Kecamatan</label>
                            <input type="text" name="kecamatan" placeholder="Nama kecamatan">
                        </div>
                    </div>
                </div>

                <!-- Pilih Paket -->
                <div class="card">
                    <div class="section-title">
                        <i class="bi bi-box-seam"></i>
                        Pilih Paket Internet
                    </div>

                    <div class="paket-grid">
                        <?php foreach ($paket_list as $pk): ?>
                            <label class="paket-card" onclick="selectPaket(this)">
                                <input type="radio" name="paket_id" value="<?= $pk['id'] ?>">
                                <div class="paket-name"><?= htmlspecialchars($pk['nama_paket']) ?></div>
                                <div class="paket-speed"><?= htmlspecialchars($pk['kecepatan']) ?></div>
                                <div class="paket-price">
                                    Rp <?= number_format($pk['harga_bulanan'], 0, ',', '.') ?>
                                    <span>/bulan</span>
                                </div>
                            </label>
                        <?php endforeach; ?>
                    </div>
                </div>

                <!-- Lokasi -->
                <div class="card">
                    <div class="section-title">
                        <i class="bi bi-geo-alt"></i>
                        Lokasi Pemasangan
                    </div>

                    <div class="location-box">
                        <div class="location-status" id="locationStatus">
                            <i class="bi bi-info-circle"></i>
                            Klik tombol di bawah untuk mengambil lokasi GPS Anda
                        </div>
                        <button type="button" class="btn-location" onclick="getLocation()">
                            <i class="bi bi-geo-alt"></i>
                            Ambil Lokasi Saya
                        </button>
                    </div>

                    <input type="hidden" name="latitude" id="latitude">
                    <input type="hidden" name="longitude" id="longitude">

                    <div class="form-group">
                        <label>Catatan Lokasi (opsional)</label>
                        <textarea name="catatan" rows="2" placeholder="Contoh: Rumah cat biru dekat masjid, masuk gang 2"></textarea>
                    </div>
                </div>

                <!-- Foto Rumah -->
                <div class="card">
                    <div class="section-title">
                        <i class="bi bi-camera"></i>
                        Foto Depan Rumah
                    </div>

                    <div class="upload-area" id="uploadArea" onclick="document.getElementById('foto_rumah').click()">
                        <div class="upload-icon">
                            <i class="bi bi-cloud-upload"></i>
                        </div>
                        <div class="upload-text">
                            Klik untuk upload foto depan rumah<br>
                            <small>Format: JPG, PNG, WEBP (maksimal 5 MB)</small>
                        </div>
                        <img id="fotoPreview" class="upload-preview" alt="Preview">
                    </div>
                    <input type="file" name="foto_rumah" id="foto_rumah" accept="image/*" style="display: none;" onchange="previewFoto(this)">
                </div>

                <!-- Syarat & Ketentuan -->
                <div class="card">
                    <div class="section-title">
                        <i class="bi bi-file-text"></i>
                        Syarat & Ketentuan
                    </div>

                    <div class="syarat-box">
                        <?= nl2br(htmlspecialchars($setting['syarat_ketentuan'] ?? 'Syarat dan ketentuan belum diatur oleh admin.')) ?>
                    </div>

                    <div class="checkbox-group">
                        <input type="checkbox" name="setuju" id="setuju">
                        <label for="setuju">Saya telah membaca dan menyetujui syarat & ketentuan di atas</label>
                    </div>
                </div>

                <button type="submit" class="btn-submit">
                    <i class="bi bi-send"></i>
                    Kirim Pendaftaran
                </button>
            </form>
        <?php endif; ?>
    </div>

    <script>
        // Theme
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

        // Pilih Paket
        function selectPaket(card) {
            document.querySelectorAll('.paket-card').forEach(c => c.classList.remove('selected'));
            card.classList.add('selected');
            card.querySelector('input[type="radio"]').checked = true;
        }

        // Get Location
        function getLocation() {
            const status = document.getElementById('locationStatus');
            
            if (!navigator.geolocation) {
                status.innerHTML = '<i class="bi bi-x-circle"></i> Browser Anda tidak mendukung GPS';
                status.className = 'location-status error';
                return;
            }

            status.innerHTML = '<i class="bi bi-hourglass-split"></i> Mengambil lokasi...';
            status.className = 'location-status';

            navigator.geolocation.getCurrentPosition(
                function(position) {
                    document.getElementById('latitude').value = position.coords.latitude;
                    document.getElementById('longitude').value = position.coords.longitude;
                    
                    status.innerHTML = '<i class="bi bi-check-circle"></i> Lokasi berhasil diambil (' + 
                        position.coords.latitude.toFixed(6) + ', ' + 
                        position.coords.longitude.toFixed(6) + ')';
                    status.className = 'location-status success';
                },
                function(error) {
                    status.innerHTML = '<i class="bi bi-x-circle"></i> Gagal mengambil lokasi. Pastikan GPS aktif.';
                    status.className = 'location-status error';
                }
            );
        }

        // Preview Foto
        function previewFoto(input) {
            const preview = document.getElementById('fotoPreview');
            const area = document.getElementById('uploadArea');

            if (input.files && input.files[0]) {
                const reader = new FileReader();
                reader.onload = function(e) {
                    preview.src = e.target.result;
                    preview.style.display = 'block';
                    area.classList.add('has-file');
                    area.querySelector('.upload-text').innerHTML = '<i class="bi bi-check-circle" style="color: var(--success);"></i> Foto berhasil dipilih. Klik untuk ganti.';
                };
                reader.readAsDataURL(input.files[0]);
            }
        }
    </script>
</body>
</html>