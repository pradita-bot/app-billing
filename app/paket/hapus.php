<?php
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../includes/auth.php';
require_login();
require_csrf();

$id = intval($_GET['id'] ?? 0);

if ($id > 0) {
    // Cek apakah paket ini dipakai oleh pelanggan
    $stmt = db()->prepare("SELECT COUNT(*) as total FROM pelanggan WHERE paket_id = ?");
    $stmt->execute([$id]);
    $result = $stmt->fetch();
    
    if ($result['total'] > 0) {
        $_SESSION['flash'] = ['type' => 'danger', 'message' => 'Tidak bisa hapus paket yang masih digunakan oleh pelanggan!'];
    } else {
        $stmt = db()->prepare('DELETE FROM paket WHERE id = ?');
        $stmt->execute([$id]);
        $_SESSION['flash'] = ['type' => 'success', 'message' => 'Paket berhasil dihapus!'];
    }
}

header('Location: index.php');
exit;