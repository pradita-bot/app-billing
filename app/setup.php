<?php
require_once __DIR__ . '/config/database.php';

$pesan = '';

// Cek apakah sudah ada user
$count = db()->query('SELECT COUNT(*) FROM users')->fetchColumn();

if ($count > 0) {
    $pesan = 'Admin sudah ada. Silakan login.';
} else {
    // Buat admin default
    $hash = password_hash('admin123', PASSWORD_DEFAULT);
    $stmt = db()->prepare('INSERT INTO users (username, password, nama, role) VALUES (?, ?, ?, ?)');
    $stmt->execute(['admin', $hash, 'Administrator', 'admin']);
    $pesan = 'Admin berhasil dibuat! Username: admin | Password: admin123 — SEGERA ganti setelah login!';
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Setup</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light">
<div class="container mt-5" style="max-width: 500px">
    <div class="card shadow">
        <div class="card-body text-center">
            <h4>Setup Aplikasi</h4>
            <p class="alert alert-info"><?= htmlspecialchars($pesan) ?></p>
            <a href="login.php" class="btn btn-primary">Ke Halaman Login</a>
        </div>
    </div>
</div>
</body>
</html>