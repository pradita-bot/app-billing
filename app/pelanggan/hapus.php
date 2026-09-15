<?php
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../includes/auth.php';
require_login();
require_csrf();

$id = intval($_GET['id'] ?? 0);

if ($id > 0) {
    // Ambil data PSB untuk hapus foto
    $stmt = db()->prepare('SELECT foto_rumah FROM psb WHERE id = ?');
    $stmt->execute([$id]);
    $psb = $stmt->fetch();
    
    // Hapus file foto jika ada
    if ($psb && $psb['foto_rumah']) {
        $file_path = __DIR__ . '/../uploads/foto_psb/' . $psb['foto_rumah'];
        if (file_exists($file_path)) {
            unlink($file_path);
        }
    }
    
    // Hapus data dari database
    $stmt = db()->prepare('DELETE FROM psb WHERE id = ?');
    $stmt->execute([$id]);
    
    $_SESSION['flash'] = ['type' => 'success', 'message' => 'Data PSB berhasil dihapus!'];
}

header('Location: index.php');
exit;