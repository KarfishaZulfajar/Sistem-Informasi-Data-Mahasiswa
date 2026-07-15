<?php
require_once __DIR__ . '/config/auth.php';
require_once __DIR__ . '/config/koneksi.php';

$keyword = trim($_GET['keyword'] ?? '');
$statusFilter = $_GET['status'] ?? '';
$prodiFilter = filter_input(INPUT_GET, 'prodi', FILTER_VALIDATE_INT) ?: 0;
$page = max(1, filter_input(INPUT_GET, 'page', FILTER_VALIDATE_INT) ?: 1);
$limit = 10;
$offset = ($page - 1) * $limit;

$where = [];
$params = [];
if ($keyword !== '') {
    $where[] = '(m.nim LIKE :keyword OR m.nama_mahasiswa LIKE :keyword OR m.email LIKE :keyword)';
    $params['keyword'] = '%' . $keyword . '%';
}
if (in_array($statusFilter, ['aktif', 'cuti', 'lulus', 'nonaktif'], true)) {
    $where[] = 'm.status = :status';
    $params['status'] = $statusFilter;
}
if ($prodiFilter > 0) {
    $where[] = 'm.id_prodi = :prodi';
    $params['prodi'] = $prodiFilter;
}
$whereSql = $where ? ' WHERE ' . implode(' AND ', $where) : '';

$countStmt = $pdo->prepare('SELECT COUNT(*) FROM mahasiswa m' . $whereSql);
$countStmt->execute($params);
$totalData = (int) $countStmt->fetchColumn();
$totalPages = max(1, (int) ceil($totalData / $limit));
if ($page > $totalPages) {
    $page = $totalPages;
    $offset = ($page - 1) * $limit;
}

$listSql =
    "SELECT m.id_mahasiswa, m.nim, m.nama_mahasiswa, m.jenis_kelamin, m.email, m.status,
            p.nama_prodi, COALESCE(k.nama_kelas, '-') AS nama_kelas
     FROM mahasiswa m
     JOIN prodi p ON p.id_prodi = m.id_prodi
     LEFT JOIN kelas k ON k.id_kelas = m.id_kelas" . $whereSql .
    " ORDER BY m.created_at DESC, m.id_mahasiswa DESC LIMIT :limit OFFSET :offset";
$listStmt = $pdo->prepare($listSql);
foreach ($params as $key => $value) {
    $listStmt->bindValue(':' . $key, $value);
}
$listStmt->bindValue(':limit', $limit, PDO::PARAM_INT);
$listStmt->bindValue(':offset', $offset, PDO::PARAM_INT);
$listStmt->execute();
$students = $listStmt->fetchAll();
$prodiList = $pdo->query('SELECT id_prodi, nama_prodi FROM prodi ORDER BY nama_prodi')->fetchAll();

function page_url(int $targetPage): string
{
    $query = $_GET;
    $query['page'] = $targetPage;
    return 'mahasiswa.php?' . http_build_query($query);
}

$pageTitle = 'Data Mahasiswa';
$activePage = 'mahasiswa';
require __DIR__ . '/partials/header.php';
?>
<div class="page-heading">
    <div>
        <h1>Data Mahasiswa</h1>
        <p>Kelola, cari, ubah, dan hapus data mahasiswa.</p>
    </div>
    <a href="form-mahasiswa.php" class="btn btn-primary"><i class="bi bi-plus-lg me-1"></i>Tambah Mahasiswa</a>
</div>

<div class="card app-card mb-4">
    <div class="card-body">
        <form method="get" class="row g-3 align-items-end" id="filterForm">
            <div class="col-lg-5">
                <label for="keyword" class="form-label">Pencarian</label>
                <div class="input-group">
                    <span class="input-group-text"><i class="bi bi-search"></i></span>
                    <input type="search" id="keyword" name="keyword" class="form-control" placeholder="NIM, nama, atau email" value="<?= e($keyword) ?>">
                </div>
            </div>
            <div class="col-md-4 col-lg-3">
                <label for="prodi" class="form-label">Program Studi</label>
                <select id="prodi" name="prodi" class="form-select">
                    <option value="">Semua prodi</option>
                    <?php foreach ($prodiList as $prodi): ?>
                        <option value="<?= $prodi['id_prodi'] ?>" <?= $prodiFilter === (int) $prodi['id_prodi'] ? 'selected' : '' ?>><?= e($prodi['nama_prodi']) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="col-md-4 col-lg-2">
                <label for="status" class="form-label">Status</label>
                <select id="status" name="status" class="form-select">
                    <option value="">Semua status</option>
                    <?php foreach (['aktif', 'cuti', 'lulus', 'nonaktif'] as $status): ?>
                        <option value="<?= $status ?>" <?= $statusFilter === $status ? 'selected' : '' ?>><?= ucfirst($status) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="col-md-4 col-lg-2 d-grid gap-2">
                <button class="btn btn-primary" type="submit">Terapkan</button>
                <?php if ($keyword !== '' || $statusFilter !== '' || $prodiFilter > 0): ?>
                    <a href="mahasiswa.php" class="btn btn-light btn-sm">Reset filter</a>
                <?php endif; ?>
            </div>
        </form>
    </div>
</div>

<div class="card app-card">
    <div class="card-header d-flex justify-content-between align-items-center">
        <div><h2 class="card-title">Daftar Mahasiswa</h2><small><?= number_format($totalData) ?> data ditemukan</small></div>
        <span class="badge text-bg-light">Halaman <?= $page ?> dari <?= $totalPages ?></span>
    </div>
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table app-table table-hover mb-0 align-middle">
                <thead><tr><th>No</th><th>NIM</th><th>Mahasiswa</th><th>Jenis Kelamin</th><th>Prodi</th><th>Kelas</th><th>Status</th><th class="text-center">Aksi</th></tr></thead>
                <tbody>
                <?php if (!$students): ?>
                    <tr><td colspan="8"><div class="empty-state"><i class="bi bi-search"></i><p>Data mahasiswa tidak ditemukan.</p></div></td></tr>
                <?php else: ?>
                    <?php foreach ($students as $index => $student): ?>
                        <tr>
                            <td><?= $offset + $index + 1 ?></td>
                            <td><span class="fw-semibold"><?= e($student['nim']) ?></span></td>
                            <td><div class="student-name"><?= e($student['nama_mahasiswa']) ?></div><small class="text-muted"><?= e($student['email'] ?: '-') ?></small></td>
                            <td><?= $student['jenis_kelamin'] === 'L' ? 'Laki-laki' : 'Perempuan' ?></td>
                            <td><?= e($student['nama_prodi']) ?></td>
                            <td><?= e($student['nama_kelas']) ?></td>
                            <td><span class="badge text-bg-<?= status_badge($student['status']) ?>"><?= e(ucfirst($student['status'])) ?></span></td>
                            <td>
                                <div class="d-flex justify-content-center gap-1">
                                    <a href="form-mahasiswa.php?id=<?= $student['id_mahasiswa'] ?>" class="btn btn-sm btn-warning" title="Edit"><i class="bi bi-pencil-square"></i></a>
                                    <form action="hapus-mahasiswa.php" method="post" class="delete-form" data-name="<?= e($student['nama_mahasiswa']) ?>">
                                        <input type="hidden" name="csrf_token" value="<?= e(csrf_token()) ?>">
                                        <input type="hidden" name="id" value="<?= $student['id_mahasiswa'] ?>">
                                        <button type="submit" class="btn btn-sm btn-danger" title="Hapus"><i class="bi bi-trash"></i></button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>

    <?php if ($totalPages > 1): ?>
        <div class="card-footer bg-white">
            <nav aria-label="Navigasi halaman">
                <ul class="pagination justify-content-end mb-0">
                    <li class="page-item <?= $page <= 1 ? 'disabled' : '' ?>"><a class="page-link" href="<?= e(page_url($page - 1)) ?>">Sebelumnya</a></li>
                    <?php for ($i = max(1, $page - 2); $i <= min($totalPages, $page + 2); $i++): ?>
                        <li class="page-item <?= $i === $page ? 'active' : '' ?>"><a class="page-link" href="<?= e(page_url($i)) ?>"><?= $i ?></a></li>
                    <?php endfor; ?>
                    <li class="page-item <?= $page >= $totalPages ? 'disabled' : '' ?>"><a class="page-link" href="<?= e(page_url($page + 1)) ?>">Berikutnya</a></li>
                </ul>
            </nav>
        </div>
    <?php endif; ?>
</div>
<?php require __DIR__ . '/partials/footer.php'; ?>
