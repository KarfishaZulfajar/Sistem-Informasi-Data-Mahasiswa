<?php
require_once __DIR__ . '/config/auth.php';
require_once __DIR__ . '/config/koneksi.php';

$totalMahasiswa = (int) $pdo->query('SELECT COUNT(*) FROM mahasiswa')->fetchColumn();
$totalAktif = (int) $pdo->query("SELECT COUNT(*) FROM mahasiswa WHERE status = 'aktif'")->fetchColumn();
$totalProdi = (int) $pdo->query('SELECT COUNT(*) FROM prodi')->fetchColumn();
$totalKelas = (int) $pdo->query('SELECT COUNT(*) FROM kelas')->fetchColumn();

$latestStudents = $pdo->query(
    "SELECT m.nim, m.nama_mahasiswa, m.status, p.nama_prodi,
            COALESCE(k.nama_kelas, '-') AS nama_kelas
     FROM mahasiswa m
     JOIN prodi p ON p.id_prodi = m.id_prodi
     LEFT JOIN kelas k ON k.id_kelas = m.id_kelas
     ORDER BY m.created_at DESC, m.id_mahasiswa DESC
     LIMIT 5"
)->fetchAll();

$statusRows = $pdo->query(
    "SELECT status, COUNT(*) AS jumlah
     FROM mahasiswa
     GROUP BY status"
)->fetchAll();
$statusData = ['aktif' => 0, 'cuti' => 0, 'lulus' => 0, 'nonaktif' => 0];
foreach ($statusRows as $row) {
    $statusData[$row['status']] = (int) $row['jumlah'];
}

$pageTitle = 'Dashboard';
$activePage = 'dashboard';
require __DIR__ . '/partials/header.php';
?>
<div class="page-heading">
    <div>
        <h1>Dashboard</h1>
        <p>Ringkasan data akademik yang tersimpan di database.</p>
    </div>
    <span class="badge rounded-pill text-bg-primary px-3 py-2"><i class="bi bi-shield-check me-1"></i><?= e(ucfirst($_SESSION['user']['role'])) ?></span>
</div>

<div class="row g-3 mb-4">
    <div class="col-sm-6 col-xl-3">
        <div class="stat-card">
            <div class="stat-icon bg-primary-subtle text-primary"><i class="bi bi-people-fill"></i></div>
            <div><span>Total Mahasiswa</span><strong><?= number_format($totalMahasiswa) ?></strong></div>
        </div>
    </div>
    <div class="col-sm-6 col-xl-3">
        <div class="stat-card">
            <div class="stat-icon bg-success-subtle text-success"><i class="bi bi-person-check-fill"></i></div>
            <div><span>Mahasiswa Aktif</span><strong><?= number_format($totalAktif) ?></strong></div>
        </div>
    </div>
    <div class="col-sm-6 col-xl-3">
        <div class="stat-card">
            <div class="stat-icon bg-warning-subtle text-warning"><i class="bi bi-journal-bookmark-fill"></i></div>
            <div><span>Program Studi</span><strong><?= number_format($totalProdi) ?></strong></div>
        </div>
    </div>
    <div class="col-sm-6 col-xl-3">
        <div class="stat-card">
            <div class="stat-icon bg-info-subtle text-info"><i class="bi bi-door-open-fill"></i></div>
            <div><span>Kelas</span><strong><?= number_format($totalKelas) ?></strong></div>
        </div>
    </div>
</div>

<div class="row g-4">
    <div class="col-xl-8">
        <div class="card app-card h-100">
            <div class="card-header d-flex justify-content-between align-items-center">
                <div>
                    <h2 class="card-title">Mahasiswa Terbaru</h2>
                    <small>5 data terakhir yang ditambahkan</small>
                </div>
                <a href="mahasiswa.php" class="btn btn-sm btn-outline-primary">Lihat Semua</a>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table app-table mb-0 align-middle">
                        <thead><tr><th>NIM</th><th>Nama</th><th>Prodi</th><th>Kelas</th><th>Status</th></tr></thead>
                        <tbody>
                        <?php if (!$latestStudents): ?>
                            <tr><td colspan="5"><div class="empty-state"><i class="bi bi-inbox"></i><p>Belum ada data mahasiswa.</p><a href="form-mahasiswa.php" class="btn btn-primary btn-sm">Tambah Data</a></div></td></tr>
                        <?php else: ?>
                            <?php foreach ($latestStudents as $student): ?>
                                <tr>
                                    <td><span class="fw-semibold"><?= e($student['nim']) ?></span></td>
                                    <td><?= e($student['nama_mahasiswa']) ?></td>
                                    <td><?= e($student['nama_prodi']) ?></td>
                                    <td><?= e($student['nama_kelas']) ?></td>
                                    <td><span class="badge text-bg-<?= status_badge($student['status']) ?>"><?= e(ucfirst($student['status'])) ?></span></td>
                                </tr>
                            <?php endforeach; ?>
                        <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <div class="col-xl-4">
        <div class="card app-card h-100">
            <div class="card-header">
                <h2 class="card-title">Status Mahasiswa</h2>
                <small>Distribusi status saat ini</small>
            </div>
            <div class="card-body">
                <?php foreach ($statusData as $status => $jumlah): ?>
                    <?php $persen = $totalMahasiswa > 0 ? round(($jumlah / $totalMahasiswa) * 100) : 0; ?>
                    <div class="status-row">
                        <div class="d-flex justify-content-between mb-1">
                            <span><span class="status-dot bg-<?= status_badge($status) ?>"></span><?= e(ucfirst($status)) ?></span>
                            <strong><?= $jumlah ?></strong>
                        </div>
                        <div class="progress" role="progressbar" aria-valuenow="<?= $persen ?>" aria-valuemin="0" aria-valuemax="100">
                            <div class="progress-bar bg-<?= status_badge($status) ?>" style="width: <?= $persen ?>%"></div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>
    </div>
</div>
<?php require __DIR__ . '/partials/footer.php'; ?>
