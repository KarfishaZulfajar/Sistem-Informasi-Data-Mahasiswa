<?php
require_once __DIR__ . '/config/functions.php';
require_once __DIR__ . '/config/koneksi.php';

if (!empty($_SESSION['user'])) {
    redirect('dashboard.php');
}

$error = '';
$flash = get_flash();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username'] ?? '');
    $password = $_POST['password'] ?? '';

    if (!verify_csrf($_POST['csrf_token'] ?? null)) {
        $error = 'Permintaan tidak valid. Silakan muat ulang halaman.';
    } elseif ($username === '' || $password === '') {
        $error = 'Username dan password wajib diisi.';
    } else {
        $stmt = $pdo->prepare(
            "SELECT id_user, nama_lengkap, username, password, role
             FROM users
             WHERE username = :username AND status = 'aktif'
             LIMIT 1"
        );
        $stmt->execute(['username' => $username]);
        $user = $stmt->fetch();

        if ($user && password_verify($password, $user['password'])) {
            session_regenerate_id(true);
            $_SESSION['user'] = [
                'id_user' => $user['id_user'],
                'nama_lengkap' => $user['nama_lengkap'],
                'username' => $user['username'],
                'role' => $user['role'],
            ];
            set_flash('success', 'Login berhasil. Selamat datang, ' . $user['nama_lengkap'] . '!');
            redirect('dashboard.php');
        }

        $error = 'Username atau password salah.';
    }
}
?>
<!doctype html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Login - SIM Mahasiswa</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" rel="stylesheet">
    <link href="assets/css/style.css" rel="stylesheet">
</head>
<body class="login-page">
<main class="container py-4">
    <div class="row justify-content-center align-items-center min-vh-100">
        <div class="col-sm-10 col-md-7 col-lg-5 col-xl-4">
            <div class="login-card card border-0 shadow-lg">
                <div class="card-body p-4 p-md-5">
                    <div class="login-logo"><i class="bi bi-mortarboard-fill"></i></div>
                    <h1 class="h3 text-center fw-bold mb-1">Login Admin</h1>
                    <p class="text-center text-muted mb-4">Sistem Informasi Data Mahasiswa</p>

                    <?php if ($flash): ?>
                        <div class="alert alert-<?= e($flash['type']) ?>"><?= e($flash['message']) ?></div>
                    <?php endif; ?>
                    <?php if ($error !== ''): ?>
                        <div class="alert alert-danger"><?= e($error) ?></div>
                    <?php endif; ?>

                    <form method="post" class="needs-validation" novalidate>
                        <input type="hidden" name="csrf_token" value="<?= e(csrf_token()) ?>">
                        <div class="mb-3">
                            <label for="username" class="form-label">Username</label>
                            <div class="input-group">
                                <span class="input-group-text"><i class="bi bi-person"></i></span>
                                <input type="text" id="username" name="username" class="form-control" required minlength="4" autocomplete="username" value="<?= e($_POST['username'] ?? '') ?>">
                                <div class="invalid-feedback">Username minimal 4 karakter.</div>
                            </div>
                        </div>

                        <div class="mb-4">
                            <label for="password" class="form-label">Password</label>
                            <div class="input-group">
                                <span class="input-group-text"><i class="bi bi-lock"></i></span>
                                <input type="password" id="password" name="password" class="form-control" required minlength="6" autocomplete="current-password">
                                <button class="btn btn-outline-secondary toggle-password" type="button" data-target="password" aria-label="Tampilkan password">
                                    <i class="bi bi-eye"></i>
                                </button>
                                <div class="invalid-feedback">Password minimal 6 karakter.</div>
                            </div>
                        </div>

                        <button type="submit" class="btn btn-primary w-100 py-2">
                            <i class="bi bi-box-arrow-in-right me-1"></i> Masuk
                        </button>
                    </form>

                    <div class="demo-account mt-4">
                        <small><strong>Akun awal:</strong> admin / admin123</small>
                    </div>
                </div>
            </div>
        </div>
    </div>
</main>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<script src="assets/js/script.js"></script>
</body>
</html>
