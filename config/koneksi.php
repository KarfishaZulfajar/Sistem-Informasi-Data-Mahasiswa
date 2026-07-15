<?php
// Konfigurasi database XAMPP (default: user root tanpa password).
const DB_HOST = 'localhost';
const DB_NAME = 'db_mahasiswa';
const DB_USER = 'root';
const DB_PASS = '';

try {
    $pdo = new PDO(
        'mysql:host=' . DB_HOST . ';dbname=' . DB_NAME . ';charset=utf8mb4',
        DB_USER,
        DB_PASS,
        [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES => false,
        ]
    );
} catch (PDOException $e) {
    http_response_code(500);
    exit(
        '<h2>Koneksi database gagal</h2>' .
        '<p>Pastikan Apache dan MySQL di XAMPP aktif, lalu impor file <strong>database/db_mahasiswa.sql</strong>.</p>' .
        '<p>Detail: ' . htmlspecialchars($e->getMessage(), ENT_QUOTES, 'UTF-8') . '</p>'
    );
}
