<?php
require_once __DIR__ . '/../../config/koneksi.php';
cek_role(['admin']);

$id = (int) ($_GET['id'] ?? $_POST['id'] ?? 0);
if ($id <= 0) {
    set_flash('error', 'Data pertanyaan tidak ditemukan.');
    redirect_ke('admin/pertanyaan/index.php');
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $jenis = trim($_POST['jenis'] ?? '');
    $kategori = trim($_POST['kategori'] ?? '');
    $pertanyaan = trim($_POST['pertanyaan'] ?? '');

    if ($jenis === '' || $kategori === '' || $pertanyaan === '') {
        set_flash('error', 'Semua field wajib diisi.');
        redirect_ke('admin/pertanyaan/edit.php?id=' . $id);
    }

    $stmt = mysqli_prepare($conn, 'UPDATE pertanyaan SET jenis = ?, kategori = ?, pertanyaan = ? WHERE id = ?');
    mysqli_stmt_bind_param($stmt, 'sssi', $jenis, $kategori, $pertanyaan, $id);
    mysqli_stmt_execute($stmt);
    mysqli_stmt_close($stmt);

    log_audit($conn, 'ubah', 'pertanyaan', $id, 'Memperbarui pertanyaan ' . $kategori . ' untuk ' . $jenis . '.');
    set_flash('success', 'Pertanyaan berhasil diperbarui.');
    redirect_ke('admin/pertanyaan/index.php');
}

$stmt = mysqli_prepare($conn, 'SELECT * FROM pertanyaan WHERE id = ? LIMIT 1');
mysqli_stmt_bind_param($stmt, 'i', $id);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);
$data = mysqli_fetch_assoc($result);
mysqli_stmt_close($stmt);

if (!$data) {
    set_flash('error', 'Data pertanyaan tidak ditemukan.');
    redirect_ke('admin/pertanyaan/index.php');
}

$error = get_flash('error');
$pageTitle = 'Edit Pertanyaan';
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
                            <h3 class="fw-bold mb-1">Edit Pertanyaan</h3>
                            <p class="text-muted mb-0">Perbarui data pertanyaan survei.</p>
                        </div>
                        <a href="<?= url('admin/pertanyaan/index.php'); ?>" class="btn btn-outline-primary">Kembali</a>
                    </div>
                    <?php if ($error) : ?><div class="alert alert-danger"><?= e($error); ?></div><?php endif; ?>
                    <form method="post">
                        <input type="hidden" name="id" value="<?= $data['id']; ?>">
                        <div class="row g-3">
                            <div class="col-md-4"><label class="form-label">Jenis Survei</label><select name="jenis" class="form-select" required><option value="ralan" <?= $data['jenis'] === 'ralan' ? 'selected' : ''; ?>>Rawat Jalan</option><option value="ranap" <?= $data['jenis'] === 'ranap' ? 'selected' : ''; ?>>Rawat Inap</option></select></div>
                            <div class="col-md-8"><label class="form-label">Kategori</label><input type="text" name="kategori" class="form-control" value="<?= e($data['kategori']); ?>" required></div>
                            <div class="col-12"><label class="form-label">Pertanyaan</label><textarea name="pertanyaan" class="form-control" rows="4" required><?= e($data['pertanyaan']); ?></textarea></div>
                        </div>
                        <button type="submit" class="btn btn-rs-primary mt-4"><i class="fa-solid fa-floppy-disk me-2"></i>Update</button>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
<?php require_once __DIR__ . '/../../includes/footer.php'; ?>
