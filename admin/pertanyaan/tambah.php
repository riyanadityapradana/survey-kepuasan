<?php
require_once __DIR__ . '/../../config/koneksi.php';
cek_role(['admin']);

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $jenis = trim($_POST['jenis'] ?? '');
    $kategori = trim($_POST['kategori'] ?? '');
    $pertanyaan = trim($_POST['pertanyaan'] ?? '');

    if ($jenis === '' || $kategori === '' || $pertanyaan === '') {
        set_flash('error', 'Semua field wajib diisi.');
        redirect_ke('admin/pertanyaan/tambah.php');
    }

    $stmt = mysqli_prepare($conn, 'INSERT INTO pertanyaan (jenis, kategori, pertanyaan) VALUES (?, ?, ?)');
    mysqli_stmt_bind_param($stmt, 'sss', $jenis, $kategori, $pertanyaan);
    mysqli_stmt_execute($stmt);
    $pertanyaanId = mysqli_insert_id($conn);
    mysqli_stmt_close($stmt);

    log_audit($conn, 'tambah', 'pertanyaan', $pertanyaanId, 'Menambahkan pertanyaan ' . $kategori . ' untuk ' . $jenis . '.');
    set_flash('success', 'Pertanyaan berhasil ditambahkan.');
    redirect_ke('admin/pertanyaan/index.php');
}

$error = get_flash('error');
$pageTitle = 'Tambah Pertanyaan';
$activeMenu = 'pertanyaan';
require_once __DIR__ . '/../../includes/header.php';
require_once __DIR__ . '/../../includes/admin_nav.php';
?>
<div class="container py-5">
    <div class="row justify-content-center">
        <div class="col-lg-8">
            <div class="card google-card border-0 shadow-sm">
                <div class="card-body p-4 p-lg-5">
                    <div class="d-flex justify-content-between align-items-center mb-4">
                        <div>
                            <h3 class="fw-bold mb-1">Tambah Pertanyaan</h3>
                            <p class="text-muted mb-0">Tambahkan pertanyaan baru untuk survei rawat jalan atau rawat inap.</p>
                        </div>
                        <a href="<?= url('admin/pertanyaan/index.php'); ?>" class="btn btn-outline-primary">Kembali</a>
                    </div>
                    <?php if ($error) : ?><div class="alert alert-danger"><?= e($error); ?></div><?php endif; ?>
                    <form method="post">
                        <div class="row g-3">
                            <div class="col-md-4"><label class="form-label">Jenis Survei</label><select name="jenis" class="form-select" required><option value="">Pilih Jenis</option><option value="ralan">Rawat Jalan</option><option value="ranap">Rawat Inap</option></select></div>
                            <div class="col-md-8"><label class="form-label">Kategori</label><input type="text" name="kategori" class="form-control" placeholder="Contoh: Pelayanan Dokter" required></div>
                            <div class="col-12"><label class="form-label">Pertanyaan</label><textarea name="pertanyaan" class="form-control" rows="4" placeholder="Tulis pertanyaan survei di sini..." required></textarea></div>
                        </div>
                        <button type="submit" class="btn btn-rs-primary mt-4"><i class="fa-solid fa-floppy-disk me-2"></i>Simpan</button>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
<?php require_once __DIR__ . '/../../includes/footer.php'; ?>
