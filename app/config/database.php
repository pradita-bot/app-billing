<?php
/**
 * Koneksi Database - Billing RT/RW Net
 * Menggunakan PDO untuk keamanan (prepared statements)
 */

function db(): PDO
{
    static $pdo = null;

    if ($pdo === null) {
        // Ambil konfigurasi dari environment variable
        // Default-nya disesuaikan untuk Docker host network
        $host = getenv('DB_HOST') ?: '127.0.0.1';
        $port = getenv('DB_PORT') ?: '3306';
        $name = getenv('DB_NAME') ?: 'billing_rtnet';
        $user = getenv('DB_USER') ?: 'rtnet';
        $pass = getenv('DB_PASS') ?: 'rtnet123';

        try {
            $pdo = new PDO(
                "mysql:host=$host;port=$port;dbname=$name;charset=utf8mb4",
                $user,
                $pass,
                [
                    // Mode error: langsung lempar exception kalau ada masalah
                    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,

                    // Hasil fetch default: associative array
                    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,

                    // Matikan emulasi prepared statements (lebih aman untuk MySQL)
                    PDO::ATTR_EMULATE_PREPARES => false,

                    // Timeout koneksi 10 detik
                    PDO::ATTR_TIMEOUT => 10,
                ]
            );
        } catch (PDOException $e) {
            // Tampilkan error yang jelas untuk debugging
            die('Gagal koneksi database: ' . $e->getMessage());
        }
    }

    return $pdo;
}