<?php
require_once __DIR__ . '/config/auth.php';
require_once __DIR__ . '/config/koneksi.php';

$id = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT) ?: filter_input(INPUT_POST, 'id', FILTER_VALIDATE_INT) ?: 0;
$isEdit = $id > 0;
$errors = [];

$data = [
    'nim' => '', 'nama_mahasiswa' => '', 'jenis_kelamin' => '', 'email' => '',
    'tempat_lahir' => '', 'tanggal_lahir' => '', 'no_hp' => '', 'id_prodi' => '',
    'id_kelas' => '', 'status' => 'aktif', 'alamat' => '',
];

if ($isEdit) {
    $stmt = $pdo->prepare('SELECT * FROM mahasiswa WHERE id_mahasiswa = :id');
    $stmt->execute(['id' => $id]);
    $existing = $stmt->fetch();
    if (!$existing) {
        set_flash('danger', 'Data mahasiswa tidak ditemukan.');
        redirect('mahasiswa.php');
    }
    $data = array_merge($data, $existing);
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    foreach ($data as $key => $value) {
        if ($key !== 'id_mahasiswa' && array_key_exists($key, $_POST)) {
            $data[$key] = is_string($_POST[$key]) ? trim($_POST[$key]) : $_POST[$key];
        }
    }

    $data['id_prodi'] = filter_var($_POST['id_prodi'] ?? null, FILTER_VALIDATE_INT) ?: '';
    $data['id_kelas'] = filter_var($_POST['id_kelas'] ?? null, FILTER_VALIDATE_INT) ?: '';

    if (!verify_csrf($_POST['csrf_token'] ?? null)) {
        $errors[] = 'Permintaan tidak valid. Silakan muat ulang halaman.';
    }
    if (!preg_match('/^[0-9]{8,20}$/', $data['nim'])) {
        $errors[] = 'NIM wajib berupa 8–20 digit angka.';
    }
    if (mb_strlen($data['nama_mahasiswa']) < 3) {
        $errors[] = 'Nama mahasiswa minimal 3 karakter.';
    }
    if (!in_array($data['jenis_kelamin'], ['L', 'P'], true)) {
        $errors[] = 'Jenis kelamin wajib dipilih.';
    }
    if ($data['email'] === '' || !filter_var($data['email'], FILTER_VALIDATE_EMAIL)) {
        $errors[] = 'Format email tidak valid.';
    }
    if ($data['no_hp'] !== '' && !preg_match('/^[0-9]{10,15}$/', $data['no_hp'])) {
        $errors[] = 'Nomor HP harus terdiri dari 10–15 digit angka.';
    }
    if (!valid_date($data['tanggal_lahir']) || ($data['tanggal_lahir'] !== '' && $data['tanggal_lahir'] > date('Y-m-d'))) {
        $errors[] = 'Tanggal lahir tidak valid.';
    }
    if ($data['id_prodi'] === '') {
        $errors[] = 'Program studi wajib dipilih.';
    }
    if (!in_array($data['status'], ['aktif', 'cuti', 'lulus', 'nonaktif'], true)) {
        $errors[] = 'Status mahasiswa tidak valid.';
    }

    if (!$errors) {
        $prodiCheck = $pdo->prepare('SELECT COUNT(*) FROM prodi WHERE id_prodi = :id');
        $prodiCheck->execute(['id' => $data['id_prodi']]);
        if (!(bool) $prodiCheck->fetchColumn()) {
            $errors[] = 'Program studi tidak ditemukan.';
        }

        if ($data['id_kelas'] !== '') {
            $kelasCheck = $pdo->prepare('SELECT COUNT(*) FROM kelas WHERE id_kelas = :kelas AND id_prodi = :prodi');
            $kelasCheck->execute(['kelas' => $data['id_kelas'], 'prodi' => $data['id_prodi']]);
            if (!(bool) $kelasCheck->fetchColumn()) {
                $errors[] = 'Kelas tidak sesuai dengan program studi.';
            }
        }

        $duplicate = $pdo->prepare('SELECT COUNT(*) FROM mahasiswa WHERE nim = :nim AND id_mahasiswa != :id');
        $duplicate->execute(['nim' => $data['nim'], 'id' => $id]);
        if ((int) $duplicate->fetchColumn() > 0) {
            $errors[] = 'NIM sudah digunakan mahasiswa lain.';
        }
    }

    if (!$errors) {
        $params = [
            'nim' => $data['nim'],
            'nama' => $data['nama_mahasiswa'],
            'jk' => $data['jenis_kelamin'],
            'tempat' => $data['tempat_lahir'] ?: null,
            'tanggal' => $data['tanggal_lahir'] ?: null,
            'email' => $data['email'],
            'no_hp' => $data['no_hp'] ?: null,
            'alamat' => $data['alamat'] ?: null,
            'prodi' => $data['id_prodi'],
            'kelas' => $data['id_kelas'] ?: null,
            'status' => $data['status'],
        ];

        if ($isEdit) {
            $params['id'] = $id;
            $sql = "UPDATE mahasiswa SET nim=:nim, nama_mahasiswa=:nama, jenis_kelamin=:jk,
                    tempat_lahir=:tempat, tanggal_lahir=:tanggal, email=:email, no_hp=:no_hp,
                    alamat=:alamat, id_prodi=:prodi, id_kelas=:kelas, status=:status
                    WHERE id_mahasiswa=:id";
            $pdo->prepare($sql)->execute($params);
            set_flash('success', 'Data mahasiswa berhasil diperbarui.');
        } else {
            $sql = "INSERT INTO mahasiswa
                    (nim, nama_mahasiswa, jenis_kelamin, tempat_lahir, tanggal_lahir, email, no_hp, alamat, id_prodi, id_kelas, status)
                    VALUES (:nim, :nama, :jk, :tempat, :tanggal, :email, :no_hp, :alamat, :prodi, :kelas, :status)";
            $pdo->prepare($sql)->execute($params);
            set_flash('success', 'Data mahasiswa berhasil ditambahkan.');
        }
        redirect('mahasiswa.php');
    }
}

$prodiList = $pdo->query('SELECT id_prodi, kode_prodi, nama_prodi FROM prodi ORDER BY nama_prodi')->fetchAll();
$kelasList = $pdo->query('SELECT id_kelas, id_prodi, nama_kelas, tahun_angkatan FROM kelas ORDER BY nama_kelas')->fetchAll();

$pageTitle = $isEdit ? 'Edit Mahasiswa' : 'Tambah Mahasiswa';
$activePage = 'form';
require __DIR__ . '/partials/header.php';
?>
<div class="page-heading">
    <div>
        <h1><?= $isEdit ? 'Edit' : 'Tambah' ?> Mahasiswa</h1>
        <p><?= $isEdit ? 'Perbarui informasi mahasiswa yang dipilih.' : 'Isi formulir untuk menambahkan data baru.' ?></p>
    </div>
    <a href="mahasiswa.php" class="btn btn-light"><i class="bi bi-arrow-left me-1"></i>Kembali</a>
</div>

<?php if ($errors): ?>
    <div class="alert alert-danger" role="alert">
        <strong>Data belum dapat disimpan:</strong>
        <ul class="mb-0 mt-2">
            <?php foreach ($errors as $error): ?><li><?= e($error) ?></li><?php endforeach; ?>
        </ul>
    </div>
<?php endif; ?>

<div class="card app-card">
    <div class="card-body p-4">
        <form method="post" class="needs-validation" id="studentForm" novalidate>
            <input type="hidden" name="csrf_token" value="<?= e(csrf_token()) ?>">
            <input type="hidden" name="id" value="<?= $id ?>">

            <div class="form-section">
                <div class="section-title"><i class="bi bi-person-vcard"></i><span>Identitas Mahasiswa</span></div>
                <div class="row g-3">
                    <div class="col-md-6">
                        <label for="nim" class="form-label">NIM <span class="text-danger">*</span></label>
                        <input type="text" id="nim" name="nim" class="form-control numeric-only" required minlength="8" maxlength="20" pattern="[0-9]{8,20}" value="<?= e($data['nim']) ?>" autocomplete="off">
                        <div class="form-text">8–20 digit angka dan harus unik.</div>
                        <div class="invalid-feedback">Masukkan NIM yang valid.</div>
                    </div>
                    <div class="col-md-6">
                        <label for="nama_mahasiswa" class="form-label">Nama Mahasiswa <span class="text-danger">*</span></label>
                        <input type="text" id="nama_mahasiswa" name="nama_mahasiswa" class="form-control" required minlength="3" maxlength="100" value="<?= e($data['nama_mahasiswa']) ?>">
                        <div class="invalid-feedback">Nama minimal 3 karakter.</div>
                    </div>
                    <div class="col-md-6">
                        <label for="jenis_kelamin" class="form-label">Jenis Kelamin <span class="text-danger">*</span></label>
                        <select id="jenis_kelamin" name="jenis_kelamin" class="form-select" required>
                            <option value="">Pilih jenis kelamin</option>
                            <option value="L" <?= $data['jenis_kelamin'] === 'L' ? 'selected' : '' ?>>Laki-laki</option>
                            <option value="P" <?= $data['jenis_kelamin'] === 'P' ? 'selected' : '' ?>>Perempuan</option>
                        </select>
                        <div class="invalid-feedback">Pilih jenis kelamin.</div>
                    </div>
                    <div class="col-md-6">
                        <label for="email" class="form-label">Email <span class="text-danger">*</span></label>
                        <input type="email" id="email" name="email" class="form-control" required maxlength="100" value="<?= e($data['email']) ?>">
                        <div class="invalid-feedback">Masukkan email yang valid.</div>
                    </div>
                    <div class="col-md-6">
                        <label for="tempat_lahir" class="form-label">Tempat Lahir</label>
                        <input type="text" id="tempat_lahir" name="tempat_lahir" class="form-control" maxlength="50" value="<?= e($data['tempat_lahir']) ?>">
                    </div>
                    <div class="col-md-6">
                        <label for="tanggal_lahir" class="form-label">Tanggal Lahir</label>
                        <input type="date" id="tanggal_lahir" name="tanggal_lahir" class="form-control" max="<?= date('Y-m-d') ?>" value="<?= e($data['tanggal_lahir']) ?>">
                    </div>
                    <div class="col-md-6">
                        <label for="no_hp" class="form-label">Nomor HP</label>
                        <input type="tel" id="no_hp" name="no_hp" class="form-control numeric-only" pattern="[0-9]{10,15}" maxlength="15" placeholder="Contoh: 081234567890" value="<?= e($data['no_hp']) ?>">
                        <div class="invalid-feedback">Nomor HP harus 10–15 digit.</div>
                    </div>
                </div>
            </div>

            <div class="form-section">
                <div class="section-title"><i class="bi bi-building"></i><span>Data Akademik</span></div>
                <div class="row g-3">
                    <div class="col-md-6">
                        <label for="id_prodi" class="form-label">Program Studi <span class="text-danger">*</span></label>
                        <select id="id_prodi" name="id_prodi" class="form-select" required>
                            <option value="">Pilih program studi</option>
                            <?php foreach ($prodiList as $prodi): ?>
                                <option value="<?= $prodi['id_prodi'] ?>" <?= (string) $data['id_prodi'] === (string) $prodi['id_prodi'] ? 'selected' : '' ?>><?= e($prodi['kode_prodi'] . ' - ' . $prodi['nama_prodi']) ?></option>
                            <?php endforeach; ?>
                        </select>
                        <div class="invalid-feedback">Pilih program studi.</div>
                    </div>
                    <div class="col-md-6">
                        <label for="id_kelas" class="form-label">Kelas</label>
                        <select id="id_kelas" name="id_kelas" class="form-select" data-selected="<?= e((string) $data['id_kelas']) ?>">
                            <option value="">Pilih kelas</option>
                            <?php foreach ($kelasList as $kelas): ?>
                                <option value="<?= $kelas['id_kelas'] ?>" data-prodi="<?= $kelas['id_prodi'] ?>" <?= (string) $data['id_kelas'] === (string) $kelas['id_kelas'] ? 'selected' : '' ?>><?= e($kelas['nama_kelas'] . ' (' . $kelas['tahun_angkatan'] . ')') ?></option>
                            <?php endforeach; ?>
                        </select>
                        <div class="form-text">Daftar kelas menyesuaikan program studi.</div>
                    </div>
                    <div class="col-md-6">
                        <label for="status" class="form-label">Status <span class="text-danger">*</span></label>
                        <select id="status" name="status" class="form-select" required>
                            <?php foreach (['aktif', 'cuti', 'lulus', 'nonaktif'] as $status): ?>
                                <option value="<?= $status ?>" <?= $data['status'] === $status ? 'selected' : '' ?>><?= ucfirst($status) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="col-12">
                        <label for="alamat" class="form-label">Alamat</label>
                        <textarea id="alamat" name="alamat" class="form-control" rows="4" maxlength="500" data-counter="alamatCounter"><?= e($data['alamat']) ?></textarea>
                        <div class="form-text text-end"><span id="alamatCounter">0</span>/500 karakter</div>
                    </div>
                </div>
            </div>

            <div class="d-flex flex-wrap gap-2 justify-content-end border-top pt-4">
                <a href="mahasiswa.php" class="btn btn-outline-secondary">Batal</a>
                <?php if (!$isEdit): ?><button type="reset" class="btn btn-outline-warning" id="resetForm">Reset</button><?php endif; ?>
                <button type="submit" class="btn btn-primary submit-button"><i class="bi bi-save me-1"></i><?= $isEdit ? 'Simpan Perubahan' : 'Simpan Data' ?></button>
            </div>
        </form>
    </div>
</div>
<?php require __DIR__ . '/partials/footer.php'; ?>
