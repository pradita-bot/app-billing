<?php
require_once __DIR__ . '/../config/functions.php';

// Manajemen session & hak akses
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

function is_logged_in(): bool {
    return isset($_SESSION['user_id']);
}

function require_login(): void {
    if (!is_logged_in()) {
        header('Location: ' . BASE_URL . '/login.php');
        exit;
    }
}

function require_admin(): void {
    require_login();
    if ($_SESSION['role'] !== 'admin') {
        http_response_code(403);
        exit('Akses khusus admin.');
    }
}

function current_user(): array {
    return [
        'id'   => $_SESSION['user_id'],
        'nama' => $_SESSION['nama'],
        'role' => $_SESSION['role'],
    ];
}

/**
 * Catat aktivitas user ke log_aktivitas
 */
function log_aktivitas(string $aktivitas): void {
    require_once __DIR__ . '/../config/database.php';
    
    try {
        $stmt = db()->prepare('INSERT INTO log_aktivitas (user_id, aktivitas, ip_address) VALUES (?, ?, ?)');
        $stmt->execute([
            $_SESSION['user_id'] ?? null,
            $aktivitas,
            $_SERVER['REMOTE_ADDR'] ?? '',
        ]);
    } catch (Exception $e) {
        // Silent fail untuk logging
    }
}

/**
 * Require CSRF token untuk POST request
 */
function require_csrf(): void {
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        if (!verify_csrf()) {
            http_response_code(403);
            die('CSRF token tidak valid. Silakan refresh halaman dan coba lagi.');
        }
    }
}