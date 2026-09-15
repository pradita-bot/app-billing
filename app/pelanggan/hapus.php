<?php
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../includes/auth.php';
require_login();

$id = intval($_GET['id'] ?? 0);

if ($id > 0) {
    $stmt = db()->prepare('DELETE FROM pelanggan WHERE id = ?');
    $stmt->execute([$id]);
    $_SESSION['flash'] = ['type' => 'success', 'message' => 'Pelanggan berhasil dihapus!'];
}

header('Location: index.php');
exit;