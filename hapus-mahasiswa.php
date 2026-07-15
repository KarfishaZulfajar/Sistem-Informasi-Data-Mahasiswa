<?php
require_once __DIR__ . '/config/auth.php';
require_once __DIR__ . '/config/koneksi.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    redirect('mahasiswa.php');
}

if (!verify_csrf($_POST['csrf_token'] ?? null)) {
    set_flash('danger', 'Permintaan penghapusan tidak valid.');
    redirect('mahasiswa.php');
}

$id = filter_input(INPUT_POST, 'id', FILTER_VALIDATE_INT);
if (!$id) {
    set_flash('danger', 'ID mahasiswa tidak valid.');
    redirect('mahasiswa.php');
}

$stmt = $pdo->prepare('DELETE FROM mahasiswa WHERE id_mahasiswa = :id');
$stmt->execute(['id' => $id]);

if ($stmt->rowCount() > 0) {
    set_flash('success', 'Data mahasiswa berhasil dihapus.');
} else {
    set_flash('warning', 'Data mahasiswa tidak ditemukan atau sudah dihapus.');
}
redirect('mahasiswa.php');
