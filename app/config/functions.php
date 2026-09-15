<?php

// Base URL untuk relative paths
define('BASE_URL', rtrim(dirname($_SERVER['SCRIPT_NAME']), '/'));

/**
 * Generate CSRF token
 */
function csrf_token(): string {
    if (session_status() === PHP_SESSION_NONE) {
        session_start();
    }
    
    if (empty($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
    
    return $_SESSION['csrf_token'];
}

/**
 * Verify CSRF token
 */
function verify_csrf(): bool {
    if (session_status() === PHP_SESSION_NONE) {
        session_start();
    }
    
    $token = $_POST['csrf_token'] ?? $_GET['csrf_token'] ?? '';
    
    if (empty($token) || empty($_SESSION['csrf_token'])) {
        return false;
    }
    
    return hash_equals($_SESSION['csrf_token'], $token);
}

/**
 * CSRF field untuk form
 */
function csrf_field(): string {
    return '<input type="hidden" name="csrf_token" value="' . csrf_token() . '">';
}

/**
 * Validasi MAC address
 */
function validate_mac(string $mac): bool {
    $mac = strtoupper(trim($mac));
    return preg_match('/^([0-9A-F]{2}[:-]){5}([0-9A-F]{2})$/', $mac);
}

/**
 * Validasi nomor HP Indonesia
 */
function validate_phone(string $phone): bool {
    $phone = preg_replace('/[^0-9]/', '', $phone);
    return preg_match('/^(62|0)8[1-9][0-9]{7,11}$/', $phone);
}

/**
 * Format nomor HP ke format internasional
 */
function format_phone(string $phone): string {
    $phone = preg_replace('/[^0-9]/', '', $phone);
    
    if (substr($phone, 0, 1) === '0') {
        return '62' . substr($phone, 1);
    }
    
    return $phone;
}

/**
 * Escape output untuk HTML
 */
function e(string $str): string {
    return htmlspecialchars($str, ENT_QUOTES, 'UTF-8');
}