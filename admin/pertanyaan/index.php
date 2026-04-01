<?php
require_once __DIR__ . '/../../config/koneksi.php';
cek_role(['admin']);

$filterJenis = $_GET['jenis'] ?? '';
$search = trim($_GET['q'] ?? '');
$page = max(1, (int) ($_GET['page'] ?? 1));
$perPage = 10;
$allowedJenis = ['ralan', 'ranap'];
$where = [];
$params = [];
$types = '';

if (in_array($filterJenis, $allowedJenis, true)) {
    $where[] = 'jenis = ?';
    $params[] = $filterJenis;
    $types .= 's';
}

if ($search !== '') {
    $where[] = '(kategori LIKE ? OR pertanyaan LIKE ?)';
    $keyword = '%' . $search . '%';
    $params[] = $keyword;
    $params[] = $keyword;
    $types .= 'ss';
}

$whereSql = $where ? ' WHERE ' . implode(' AND ', $where) : '';
$countSql = 'SELECT COUNT(*) AS total FROM pertanyaan' . $whereSql;
$dataSql = 'SELECT * FROM pertanyaan' . $whereSql . ' ORDER BY jenis ASC, kategori ASC, id DESC LIMIT ? OFFSET ?';

if ($params) {
    $stmtCount = mysqli_prepare($conn, $countSql);
    mysqli_stmt_bind_param($stmtCount, $types, ...$params);
    mysqli_stmt_execute($stmtCount);
    $countResult = mysqli_stmt_get_result($stmtCount);
    $totalData = (int) (mysqli_fetch_assoc($countResult)['total'] ?? 0);
    mysqli_stmt_close($stmtCount);
} else {
    $countResult = mysqli_query($conn, $countSql);
    $totalData = (int) (mysqli_fetch_assoc($countResult)['total'] ?? 0);
}

$totalPages = max(1, (int) ceil($totalData / $perPage));
$page = min($page, $totalPages);
$offset = ($page - 1) * $perPage;

$stmt = mysqli_prepare($conn, $dataSql);
$dataParams = $params;
$dataParams[] = $perPage;
$dataParams[] = $offset;
$dataTypes = $types . 'ii';
mysqli_stmt_bind_param($stmt, $dataTypes, ...$dataParams);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);

$success = get_flash('success');
$error = get_flash('error');
$pageTitle = 'Kelola Pertanyaan';
$activeMenu = 'pertanyaan';
require_once __DIR__ . '/../../includes/header.php';
require_once __DIR__ . '/../../includes/admin_nav.php';
?>
<div class="container py-4">
    <div class="card google-card border-0 shadow-sm">
        <div class="card-body p-4">
            <div class="d-flex flex-wrap justify-content-between align-items-center gap-3 mb-4">
                <div>
                    <h3 class="fw-bold mb-1">Kelola Pertanyaan</h3>
                    <p class="text-muted mb-0">Tambah, cari, dan kelola pertanyaan survei secara dinamis.</p>
                </div>
                <a href="<?= url('admin/pertanyaan/tambah.php'); ?>" class="btn btn-rs-primary"><i class="fa-solid fa-plus me-2"></i>Tambah Pertanyaan</a>
            </div>

            <?php if ($success) : ?><div class="alert alert-success"><?= e($success); ?></div><?php endif; ?>
            <?php if ($error) : ?><div class="alert alert-danger"><?= e($error); ?></div><?php endif; ?>

            <form method="get" class="filter-grid mb-4">
                <div>
                    <label class="form-label">Filter Jenis</label>
                    <select name="jenis" class="form-select">
                        <option value="">Semua Jenis</option>
                        <option value="ralan" <?= $filterJenis === 'ralan' ? 'selected' : ''; ?>>Rawat Jalan</option>
                        <option value="ranap" <?= $filterJenis === 'ranap' ? 'selected' : ''; ?>>Rawat Inap</option>
                    </select>
                </div>
                <div>
                    <label class="form-label">Pencarian</label>
                    <input type="text" name="q" class="form-control" value="<?= e($search); ?>" placeholder="Cari kategori atau pertanyaan">
                </div>
                <div class="filter-actions">
                    <button type="submit" class="btn btn-rs-primary"><i class="fa-solid fa-filter me-2"></i>Terapkan</button>
                    <a href="<?= url('admin/pertanyaan/index.php'); ?>" class="btn btn-outline-secondary">Reset</a>
                </div>
            </form>

            <div class="d-flex justify-content-between align-items-center flex-wrap gap-2 mb-3">
                <span class="text-muted small">Menampilkan <?= $totalData > 0 ? $offset + 1 : 0; ?>-<?= min($offset + $perPage, $totalData); ?> dari <?= $totalData; ?> data</span>
                <span class="badge text-bg-light border">Halaman <?= $page; ?> / <?= $totalPages; ?></span>
            </div>

            <div class="table-responsive">
                <table class="table table-hover align-middle">
                    <thead>
                        <tr>
                            <th width="70">No</th>
                            <th>Jenis</th>
                            <th>Kategori</th>
                            <th>Pertanyaan</th>
                            <th width="170">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (mysqli_num_rows($result) === 0) : ?>
                            <tr><td colspan="5" class="text-center text-muted py-4">Belum ada data yang cocok dengan filter.</td></tr>
                        <?php else : ?>
                            <?php $no = $offset + 1; ?>
                            <?php while ($row = mysqli_fetch_assoc($result)) : ?>
                                <tr>
                                    <td><?= $no++; ?></td>
                                    <td><span class="badge text-bg-primary"><?= strtoupper(e($row['jenis'])); ?></span></td>
                                    <td><?= e($row['kategori']); ?></td>
                                    <td><?= e($row['pertanyaan']); ?></td>
                                    <td>
                                        <a href="<?= url('admin/pertanyaan/edit.php?id=' . $row['id']); ?>" class="btn btn-sm btn-warning text-dark"><i class="fa-solid fa-pen-to-square"></i></a>
                                        <a href="<?= url('admin/pertanyaan/hapus.php?id=' . $row['id']); ?>" class="btn btn-sm btn-danger" onclick="return confirm('Yakin ingin menghapus pertanyaan ini?')"><i class="fa-solid fa-trash"></i></a>
                                    </td>
                                </tr>
                            <?php endwhile; ?>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>

            <div class="mt-4"><?= render_pagination($page, $totalPages); ?></div>
        </div>
    </div>
</div>
<?php mysqli_stmt_close($stmt); ?>
<?php require_once __DIR__ . '/../../includes/footer.php'; ?>
