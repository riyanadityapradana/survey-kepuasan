<?php
require_once __DIR__ . '/../../config/koneksi.php';
cek_role(['admin']);

$search = trim($_GET['q'] ?? '');
$status = trim($_GET['status'] ?? '');
$bulan = trim($_GET['bulan'] ?? date('m'));
$tahun = trim($_GET['tahun'] ?? date('Y'));
$page = max(1, (int) ($_GET['page'] ?? 1));
$perPage = 10;
$success = get_flash('success');
$error = get_flash('error');

if (!array_key_exists($status, daftar_status_komplain()) && $status !== '') {
    $status = '';
}
if (!preg_match('/^(0[1-9]|1[0-2])$/', $bulan)) {
    $bulan = date('m');
}
if (!preg_match('/^\d{4}$/', $tahun)) {
    $tahun = date('Y');
}

$where = ['MONTH(k.tanggal_komplain) = ?', 'YEAR(k.tanggal_komplain) = ?'];
$params = [(int) $bulan, (int) $tahun];
$types = 'ii';

if ($search !== '') {
    $where[] = '(k.identitas_pasien LIKE ? OR k.area_komplain LIKE ? OR k.aduan LIKE ?)';
    $keyword = '%' . $search . '%';
    $params[] = $keyword;
    $params[] = $keyword;
    $params[] = $keyword;
    $types .= 'sss';
}
if ($status !== '') {
    $where[] = 'k.status = ?';
    $params[] = $status;
    $types .= 's';
}

$whereSql = implode(' AND ', $where);
$countSql = 'SELECT COUNT(*) AS total FROM komplain k WHERE ' . $whereSql;
$stmtCount = mysqli_prepare($conn, $countSql);
mysqli_stmt_bind_param($stmtCount, $types, ...$params);
mysqli_stmt_execute($stmtCount);
$totalRows = (int) (mysqli_fetch_assoc(mysqli_stmt_get_result($stmtCount))['total'] ?? 0);
mysqli_stmt_close($stmtCount);

$totalPages = max(1, (int) ceil($totalRows / $perPage));
$page = min($page, $totalPages);
$offset = ($page - 1) * $perPage;

$sql = 'SELECT k.*, u.username AS dibuat_oleh FROM komplain k LEFT JOIN users u ON u.id = k.created_by WHERE ' . $whereSql . ' ORDER BY k.tanggal_komplain DESC, k.id DESC LIMIT ? OFFSET ?';
$stmt = mysqli_prepare($conn, $sql);
$dataParams = $params;
$dataParams[] = $perPage;
$dataParams[] = $offset;
$dataTypes = $types . 'ii';
mysqli_stmt_bind_param($stmt, $dataTypes, ...$dataParams);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);

$pageTitle = 'Kelola Komplain';
$activeMenu = 'komplain';
require_once __DIR__ . '/../../includes/header.php';
require_once __DIR__ . '/../../includes/admin_nav.php';
?>
<div class="container py-4">
    <div class="card google-card border-0 shadow-sm">
        <div class="card-body p-4">
            <div class="d-flex flex-wrap justify-content-between align-items-center gap-3 mb-4">
                <div>
                    <h3 class="fw-bold mb-1">Kelola Komplain</h3>
                    <p class="text-muted mb-0">Input, tindak lanjut, dan pantau komplain pelanggan hanya untuk admin.</p>
                </div>
                <div class="d-flex gap-2 flex-wrap">
                    <a href="<?= url('admin/komplain/tambah.php'); ?>" class="btn btn-rs-primary"><i class="fa-solid fa-plus me-2"></i>Tambah Komplain</a>
                    <a href="<?= url('admin/komplain/laporan.php'); ?>" class="btn btn-outline-primary">Laporan Waktu Tanggap</a>
                </div>
            </div>
            <?php if ($success) : ?><div class="alert alert-success"><?= e($success); ?></div><?php endif; ?>
            <?php if ($error) : ?><div class="alert alert-danger"><?= e($error); ?></div><?php endif; ?>
            <form method="get" class="filter-grid mb-4">
                <div><label class="form-label">Periode</label><input type="month" name="periode" class="form-control" value="<?= e($tahun . '-' . $bulan); ?>" oninput="const [y,m]=this.value.split('-'); if(y&&m){ document.getElementById('bulan').value=m; document.getElementById('tahun').value=y; }"><input type="hidden" id="bulan" name="bulan" value="<?= e($bulan); ?>"><input type="hidden" id="tahun" name="tahun" value="<?= e($tahun); ?>"></div>
                <div><label class="form-label">Status</label><select name="status" class="form-select"><option value="">Semua Status</option><?php foreach (daftar_status_komplain() as $kode => $label) : ?><option value="<?= e($kode); ?>" <?= $status === $kode ? 'selected' : ''; ?>><?= e($label); ?></option><?php endforeach; ?></select></div>
                <div><label class="form-label">Cari</label><input type="text" name="q" class="form-control" value="<?= e($search); ?>" placeholder="Cari identitas, area, atau aduan"></div>
                <div class="filter-actions"><button type="submit" class="btn btn-rs-primary"><i class="fa-solid fa-filter me-2"></i>Terapkan</button><a href="<?= url('admin/komplain/index.php'); ?>" class="btn btn-outline-secondary">Reset</a></div>
            </form>
            <div class="table-responsive">
                <table class="table table-hover align-middle">
                    <thead>
                        <tr>
                            <th width="70">No</th>
                            <th width="130">Tanggal</th>
                            <th width="140">Area</th>
                            <th width="180">Identitas</th>
                            <th>Aduan</th>
                            <th width="120">Status</th>
                            <th width="120">Kategori</th>
                            <th width="170">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (mysqli_num_rows($result) === 0) : ?>
                            <tr><td colspan="8" class="text-center text-muted py-4">Belum ada data komplain untuk filter ini.</td></tr>
                        <?php else : ?>
                            <?php $no = $offset + 1; ?>
                            <?php while ($row = mysqli_fetch_assoc($result)) : ?>
                                <?php $kategoriTanggap = kategori_waktu_tanggap($row['tanggal_komplain'], $row['tanggal_tindak_lanjut']); ?>
                                <tr>
                                    <td><?= $no++; ?></td>
                                    <td><?= date('d-m-Y H:i', strtotime($row['tanggal_komplain'])); ?></td>
                                    <td><?= e($row['area_komplain']); ?></td>
                                    <td><?= e($row['identitas_pasien']); ?></td>
                                    <td><?= e($row['aduan']); ?></td>
                                    <td><span class="badge text-bg-secondary"><?= e(daftar_status_komplain()[$row['status']] ?? $row['status']); ?></span></td>
                                    <td><?= badge_kategori_tanggap($kategoriTanggap); ?></td>
                                    <td>
                                        <div class="d-flex gap-2 flex-wrap">
                                            <a href="<?= url('admin/komplain/edit.php?id=' . $row['id']); ?>" class="btn btn-sm btn-warning text-dark"><i class="fa-solid fa-pen-to-square"></i></a>
                                            <a href="<?= url('admin/komplain/hapus.php?id=' . $row['id']); ?>" class="btn btn-sm btn-danger" onclick="return confirm('Hapus data komplain ini?')"><i class="fa-solid fa-trash"></i></a>
                                        </div>
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
