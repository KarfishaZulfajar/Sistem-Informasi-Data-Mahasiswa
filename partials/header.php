<?php
require_once __DIR__ . '/../config/functions.php';
$pageTitle = $pageTitle ?? 'SIM Mahasiswa';
$activePage = $activePage ?? '';
$user = $_SESSION['user'] ?? [];
$flash = get_flash();
?>
<!doctype html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="description" content="Sistem Informasi Data Mahasiswa berbasis PHP dan MySQL">
    <title><?= e($pageTitle) ?> - SIM Mahasiswa</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" rel="stylesheet">
    <link href="assets/css/style.css" rel="stylesheet">
</head>
<body>
<div class="app-shell">
    <aside class="sidebar" id="sidebar">
        <div class="sidebar-brand">
            <span class="brand-icon"><i class="bi bi-mortarboard-fill"></i></span>
            <div>
                <strong>SIM Mahasiswa</strong>
                <small>Panel Akademik</small>
            </div>
        </div>

        <nav class="sidebar-nav" aria-label="Menu utama">
            <a href="dashboard.php" class="<?= $activePage === 'dashboard' ? 'active' : '' ?>">
                <i class="bi bi-grid-1x2-fill"></i><span>Dashboard</span>
            </a>
            <a href="mahasiswa.php" class="<?= $activePage === 'mahasiswa' ? 'active' : '' ?>">
                <i class="bi bi-people-fill"></i><span>Data Mahasiswa</span>
            </a>
            <a href="form-mahasiswa.php" class="<?= $activePage === 'form' ? 'active' : '' ?>">
                <i class="bi bi-person-plus-fill"></i><span>Tambah Mahasiswa</span>
            </a>
            <a href="logout.php" class="logout-link">
                <i class="bi bi-box-arrow-right"></i><span>Logout</span>
            </a>
        </nav>
    </aside>

    <div class="sidebar-overlay" id="sidebarOverlay"></div>

    <div class="main-wrapper">
        <header class="topbar">
            <button class="btn btn-light sidebar-toggle" id="sidebarToggle" type="button" aria-label="Buka menu">
                <i class="bi bi-list"></i>
            </button>
            <div class="ms-auto d-flex align-items-center gap-2">
                <div class="user-avatar"><?= e(strtoupper(substr($user['nama_lengkap'] ?? 'A', 0, 1))) ?></div>
                <div class="user-meta d-none d-sm-block">
                    <strong><?= e($user['nama_lengkap'] ?? 'Administrator') ?></strong>
                    <small><?= e(ucfirst($user['role'] ?? 'admin')) ?></small>
                </div>
            </div>
        </header>

        <main class="content-wrapper">
            <?php if ($flash): ?>
                <div class="alert alert-<?= e($flash['type']) ?> alert-dismissible fade show auto-dismiss" role="alert">
                    <?= e($flash['message']) ?>
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Tutup"></button>
                </div>
            <?php endif; ?>
