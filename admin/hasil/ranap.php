<?php
require_once __DIR__ . '/../../config/koneksi.php';
cek_login();

$jenis = 'ranap';
$judul = 'Hasil Survei Rawat Inap';
$formUrl = url('survey/form_ranap.php');
$exportUrl = url('admin/hasil/export_ranap.php');
$activeMenu = 'ranap';
$pageTitle = $judul;
$allowedGender = ['Laki-Laki', 'Perempuan'];
$startDate = trim($_GET['start_date'] ?? '');
$endDate = trim($_GET['end_date'] ?? '');
$lokasi = trim($_GET['lokasi_aduan'] ?? '');
$kategori = trim($_GET['kategori'] ?? '');
$jenisKelamin = trim($_GET['jenis_kelamin'] ?? '');
$search = trim($_GET['q'] ?? '');
$page = max(1, (int) ($_GET['page'] ?? 1));
$perPage = 8;
$success = get_flash('success');
$error = get_flash('error');
$returnUrl = url('admin/hasil/ranap.php') . query_string();

if (!in_array($jenisKelamin, $allowedGender, true)) { $jenisKelamin = ''; }
if (!in_array($lokasi, daftar_lokasi_aduan(), true)) { $lokasi = ''; }
if ($startDate !== '' && !preg_match('/^\d{4}-\d{2}-\d{2}$/', $startDate)) { $startDate = ''; }
if ($endDate !== '' && !preg_match('/^\d{4}-\d{2}-\d{2}$/', $endDate)) { $endDate = ''; }

$kategoriOptions = [];
$kategoriQuery = mysqli_query($conn, "SELECT DISTINCT kategori FROM pertanyaan WHERE jenis = 'ranap' ORDER BY kategori ASC");
while ($row = mysqli_fetch_assoc($kategoriQuery)) { $kategoriOptions[] = $row['kategori']; }
if (!in_array($kategori, $kategoriOptions, true)) { $kategori = ''; }

$summaryWhere = ['p.jenis = ?'];
$summaryParams = [$jenis];
$summaryTypes = 's';
if ($kategori !== '') { $summaryWhere[] = 'p.kategori = ?'; $summaryParams[] = $kategori; $summaryTypes .= 's'; }
if ($jenisKelamin !== '') { $summaryWhere[] = 'r.jenis_kelamin = ?'; $summaryParams[] = $jenisKelamin; $summaryTypes .= 's'; }
if ($lokasi !== '') { $summaryWhere[] = 'r.lokasi_aduan = ?'; $summaryParams[] = $lokasi; $summaryTypes .= 's'; }
if ($startDate !== '') { $summaryWhere[] = 'DATE(r.tanggal) >= ?'; $summaryParams[] = $startDate; $summaryTypes .= 's'; }
if ($endDate !== '') { $summaryWhere[] = 'DATE(r.tanggal) <= ?'; $summaryParams[] = $endDate; $summaryTypes .= 's'; }
$summarySql = 'SELECT p.id, p.kategori, p.pertanyaan, SUM(CASE WHEN j.nilai = 3 THEN 1 ELSE 0 END) AS puas, SUM(CASE WHEN j.nilai = 2 THEN 1 ELSE 0 END) AS kurang_puas, SUM(CASE WHEN j.nilai = 1 THEN 1 ELSE 0 END) AS tidak_puas, COUNT(j.id) AS total_jawaban, ROUND(AVG(j.nilai), 2) AS rata_rata FROM pertanyaan p LEFT JOIN jawaban j ON j.id_pertanyaan = p.id LEFT JOIN responden r ON r.id = j.id_responden WHERE ' . implode(' AND ', $summaryWhere) . ' GROUP BY p.id, p.kategori, p.pertanyaan ORDER BY p.kategori ASC, p.id ASC';
$stmtSummary = mysqli_prepare($conn, $summarySql);
mysqli_stmt_bind_param($stmtSummary, $summaryTypes, ...$summaryParams);
mysqli_stmt_execute($stmtSummary);
$rekap = mysqli_stmt_get_result($stmtSummary);

$detailWhere = ['r.jenis = ?'];
$detailParams = [$jenis];
$detailTypes = 's';
if ($search !== '') { $detailWhere[] = 'r.nama LIKE ?'; $detailParams[] = '%' . $search . '%'; $detailTypes .= 's'; }
if ($jenisKelamin !== '') { $detailWhere[] = 'r.jenis_kelamin = ?'; $detailParams[] = $jenisKelamin; $detailTypes .= 's'; }
if ($lokasi !== '') { $detailWhere[] = 'r.lokasi_aduan = ?'; $detailParams[] = $lokasi; $detailTypes .= 's'; }
if ($startDate !== '') { $detailWhere[] = 'DATE(r.tanggal) >= ?'; $detailParams[] = $startDate; $detailTypes .= 's'; }
if ($endDate !== '') { $detailWhere[] = 'DATE(r.tanggal) <= ?'; $detailParams[] = $endDate; $detailTypes .= 's'; }
if ($kategori !== '') { $detailWhere[] = 'p.kategori = ?'; $detailParams[] = $kategori; $detailTypes .= 's'; }
$whereSql = implode(' AND ', $detailWhere);
$countSql = 'SELECT COUNT(DISTINCT r.id) AS total FROM responden r LEFT JOIN jawaban j ON j.id_responden = r.id LEFT JOIN pertanyaan p ON p.id = j.id_pertanyaan WHERE ' . $whereSql;
$stmtCount = mysqli_prepare($conn, $countSql);
mysqli_stmt_bind_param($stmtCount, $detailTypes, ...$detailParams);
mysqli_stmt_execute($stmtCount);
$totalRows = (int) (mysqli_fetch_assoc(mysqli_stmt_get_result($stmtCount))['total'] ?? 0);
mysqli_stmt_close($stmtCount);
$totalPages = max(1, (int) ceil($totalRows / $perPage));
$page = min($page, $totalPages);
$offset = ($page - 1) * $perPage;

$dataSql = 'SELECT DISTINCT r.id, r.nama, r.jenis_kelamin, r.lokasi_aduan, r.saran, r.tanggal FROM responden r LEFT JOIN jawaban j ON j.id_responden = r.id LEFT JOIN pertanyaan p ON p.id = j.id_pertanyaan WHERE ' . $whereSql . ' ORDER BY r.tanggal DESC, r.id DESC LIMIT ? OFFSET ?';
$stmtData = mysqli_prepare($conn, $dataSql);
$dataParams = $detailParams;
$dataParams[] = $perPage;
$dataParams[] = $offset;
$dataTypes = $detailTypes . 'ii';
mysqli_stmt_bind_param($stmtData, $dataTypes, ...$dataParams);
mysqli_stmt_execute($stmtData);
$dataResult = mysqli_stmt_get_result($stmtData);
$respondents = [];
$respondentIds = [];
while ($row = mysqli_fetch_assoc($dataResult)) {
    $row['id'] = (int) $row['id'];
    $respondents[$row['id']] = $row + ['jawaban' => []];
    $respondentIds[] = $row['id'];
}
mysqli_stmt_close($stmtData);

if ($respondentIds) {
    $idList = implode(',', array_map('intval', $respondentIds));
    $answerSql = "SELECT r.id AS id_responden, p.kategori, p.pertanyaan, j.nilai FROM responden r INNER JOIN jawaban j ON j.id_responden = r.id INNER JOIN pertanyaan p ON p.id = j.id_pertanyaan WHERE r.id IN ($idList) AND p.jenis = 'ranap'";
    if ($kategori !== '') { $answerSql .= " AND p.kategori = '" . mysqli_real_escape_string($conn, $kategori) . "'"; }
    $answerSql .= ' ORDER BY r.id DESC, p.kategori ASC, p.id ASC';
    $detailQuery = mysqli_query($conn, $answerSql);
    while ($row = mysqli_fetch_assoc($detailQuery)) {
        $idResponden = (int) $row['id_responden'];
        $nilai = (int) $row['nilai'];
        $label = $nilai === 3 ? 'Puas' : ($nilai === 2 ? 'Kurang Puas' : 'Tidak Puas');
        $group = $row['kategori'] ?: 'Lainnya';
        $respondents[$idResponden]['jawaban'][$group][] = ['pertanyaan' => $row['pertanyaan'], 'jawaban' => $label];
    }
}

function badge_detail_ranap(string $jawaban): string
{
    if ($jawaban === 'Puas') { return '<span class="detail-answer puas">Puas</span>'; }
    if ($jawaban === 'Kurang Puas') { return '<span class="detail-answer kurang">Kurang Puas</span>'; }
    if ($jawaban === 'Tidak Puas') { return '<span class="detail-answer tidak">Tidak Puas</span>'; }
    return '<span class="detail-answer kosong">-</span>';
}

require_once __DIR__ . '/../../includes/header.php';
require_once __DIR__ . '/../../includes/admin_nav.php';
?>
<div class="container py-4">
    <div class="card google-card mb-4">
        <div class="card-body p-4">
            <div class="d-flex justify-content-between align-items-center flex-wrap gap-3 mb-3">
                <div>
                    <h3 class="fw-bold mb-1"><?= e($judul); ?></h3>
                    <p class="text-muted mb-0">Rekap hasil berdasarkan filter yang dipilih dan daftar responden terbaru.</p>
                </div>
                <div class="d-flex gap-2 flex-wrap">
                    <a href="<?= $exportUrl . query_string([], ['page']); ?>" class="btn btn-success"><i class="fa-solid fa-file-excel me-2"></i>Export Excel</a>
                    <a href="<?= $formUrl; ?>" class="btn btn-outline-primary">Buka Form Ranap</a>
                </div>
            </div>
            <?php if ($success) : ?><div class="alert alert-success"><?= e($success); ?></div><?php endif; ?>
            <?php if ($error) : ?><div class="alert alert-danger"><?= e($error); ?></div><?php endif; ?>
            <form method="get" class="filter-grid mb-4">
                <div><label class="form-label">Tanggal Awal</label><input type="date" name="start_date" class="form-control" value="<?= e($startDate); ?>"></div>
                <div><label class="form-label">Tanggal Akhir</label><input type="date" name="end_date" class="form-control" value="<?= e($endDate); ?>"></div>
                <div><label class="form-label">Lokasi Aduan</label><select name="lokasi_aduan" class="form-select"><option value="">Semua Lokasi</option><?php foreach (daftar_lokasi_aduan() as $opsiLokasi) : ?><option value="<?= e($opsiLokasi); ?>" <?= $lokasi === $opsiLokasi ? 'selected' : ''; ?>><?= e($opsiLokasi); ?></option><?php endforeach; ?></select></div>
                <div><label class="form-label">Kategori</label><select name="kategori" class="form-select"><option value="">Semua Kategori</option><?php foreach ($kategoriOptions as $opsiKategori) : ?><option value="<?= e($opsiKategori); ?>" <?= $kategori === $opsiKategori ? 'selected' : ''; ?>><?= e($opsiKategori); ?></option><?php endforeach; ?></select></div>
                <div><label class="form-label">Jenis Kelamin</label><select name="jenis_kelamin" class="form-select"><option value="">Semua</option><option value="Laki-Laki" <?= $jenisKelamin === 'Laki-Laki' ? 'selected' : ''; ?>>Laki-Laki</option><option value="Perempuan" <?= $jenisKelamin === 'Perempuan' ? 'selected' : ''; ?>>Perempuan</option></select></div>
                <div><label class="form-label">Cari Responden</label><input type="text" name="q" class="form-control" value="<?= e($search); ?>" placeholder="Cari nama responden"></div>
                <div class="filter-actions"><button type="submit" class="btn btn-rs-primary"><i class="fa-solid fa-filter me-2"></i>Terapkan</button><a href="<?= url('admin/hasil/ranap.php'); ?>" class="btn btn-outline-secondary">Reset</a></div>
            </form>
            <div class="table-responsive">
                <table class="table table-hover align-middle hasil-summary-table">
                    <thead><tr><th>Kategori</th><th>Pertanyaan</th><th>Puas</th><th>Kurang Puas</th><th>Tidak Puas</th><th>Total</th><th>Rata-rata</th></tr></thead>
                    <tbody>
                        <?php if (mysqli_num_rows($rekap) === 0) : ?>
                            <tr><td colspan="7" class="text-center text-muted py-4">Belum ada rekap yang cocok dengan filter.</td></tr>
                        <?php else : ?>
                            <?php while ($row = mysqli_fetch_assoc($rekap)) : ?>
                                <tr><td><?= e($row['kategori']); ?></td><td><?= e($row['pertanyaan']); ?></td><td><?= (int) $row['puas']; ?></td><td><?= (int) $row['kurang_puas']; ?></td><td><?= (int) $row['tidak_puas']; ?></td><td><?= (int) $row['total_jawaban']; ?></td><td><?= $row['rata_rata'] !== null ? e((string) $row['rata_rata']) : '0'; ?></td></tr>
                            <?php endwhile; ?>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <div class="card google-card">
        <div class="card-body p-4">
            <div class="d-flex justify-content-between align-items-center flex-wrap gap-2 mb-3">
                <div>
                    <h5 class="fw-semibold mb-0">Daftar Responden</h5>
                    <span class="detail-pivot-note">Menampilkan <?= $totalRows > 0 ? $offset + 1 : 0; ?>-<?= min($offset + $perPage, $totalRows); ?> dari <?= $totalRows; ?> responden.</span>
                </div>
                <span class="detail-pivot-note">Klik tombol detail untuk melihat seluruh jawaban responden.</span>
            </div>
            <div class="table-responsive">
                <table class="table table-hover align-middle responden-summary-table">
                    <thead><tr><th width="170">Tanggal</th><th>Nama</th><th width="160">Jenis Kelamin</th><th width="220">Lokasi Aduan</th><th width="190">Aksi</th></tr></thead>
                    <tbody>
                        <?php if (!$respondents) : ?>
                            <tr><td colspan="5" class="text-center text-muted py-4">Tidak ada responden yang cocok dengan filter.</td></tr>
                        <?php else : ?>
                            <?php foreach ($respondents as $detail) : ?>
                                <tr>
                                    <td><?= date('d-m-Y H:i', strtotime($detail['tanggal'])); ?></td>
                                    <td><?= e($detail['nama']); ?></td>
                                    <td><?= e($detail['jenis_kelamin']); ?></td>
                                    <td><?= e($detail['lokasi_aduan']); ?></td>
                                    <td>
                                        <div class="d-flex gap-2 flex-wrap">
                                            <button type="button" class="btn btn-sm btn-outline-primary" data-bs-toggle="modal" data-bs-target="#detailRanap<?= $detail['id']; ?>"><i class="fa-solid fa-eye me-1"></i>Detail</button>
                                            <?php if (user_punya_role('admin')) : ?>
                                                <a href="<?= url('admin/hasil/hapus_responden.php?id=' . $detail['id'] . '&jenis=ranap&return=' . urlencode($returnUrl)); ?>" class="btn btn-sm btn-outline-danger" onclick="return confirm('Hapus responden ini beserta semua jawabannya?');"><i class="fa-solid fa-trash me-1"></i>Hapus</a>
                                            <?php endif; ?>
                                        </div>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
            <div class="mt-4"><?= render_pagination($page, $totalPages); ?></div>
        </div>
    </div>
</div>
<?php foreach ($respondents as $detail) : ?>
    <div class="modal fade" id="detailRanap<?= $detail['id']; ?>" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-lg modal-dialog-scrollable modal-dialog-centered">
            <div class="modal-content border-0 google-card">
                <div class="modal-header border-0 pb-0"><div><h5 class="modal-title fw-bold mb-1">Detail Jawaban Responden</h5><p class="text-muted small mb-0">Rawat Inap</p></div><button type="button" class="btn-close" data-bs-dismiss="modal"></button></div>
                <div class="modal-body pt-3">
                    <div class="detail-meta-grid mb-4"><div class="detail-meta-card"><span>Tanggal</span><strong><?= date('d-m-Y H:i', strtotime($detail['tanggal'])); ?></strong></div><div class="detail-meta-card"><span>Nama</span><strong><?= e($detail['nama']); ?></strong></div><div class="detail-meta-card"><span>Jenis Kelamin</span><strong><?= e($detail['jenis_kelamin']); ?></strong></div><div class="detail-meta-card"><span>Lokasi Aduan</span><strong><?= e($detail['lokasi_aduan']); ?></strong></div></div>
                    <?php if (!empty($detail['saran'])) : ?>
                        <div class="detail-list-item mb-4">
                            <div class="section-heading mb-3">
                                <h6 class="fw-semibold mb-1">Saran Responden</h6>
                                <p class="text-muted small mb-0">Masukan tambahan dari responden.</p>
                            </div>
                            <p class="detail-pertanyaan mb-0"><?= nl2br(e($detail['saran'])); ?></p>
                        </div>
                    <?php endif; ?>
                    <div class="detail-list">
                        <?php foreach ($detail['jawaban'] as $kategoriNama => $items) : ?>
                            <div class="detail-group"><div class="section-heading mb-3"><h6 class="fw-semibold mb-1"><?= e($kategoriNama); ?></h6><p class="text-muted small mb-0">Daftar jawaban pada kategori ini.</p></div><?php foreach ($items as $item) : ?><div class="detail-list-item"><div class="detail-list-head"><div class="flex-grow-1"></div><?= badge_detail_ranap($item['jawaban']); ?></div><p class="detail-pertanyaan mb-0"><?= e($item['pertanyaan']); ?></p></div><?php endforeach; ?></div>
                        <?php endforeach; ?>
                    </div>
                </div>
            </div>
        </div>
    </div>
<?php endforeach; ?>
<?php mysqli_stmt_close($stmtSummary); ?>
<?php require_once __DIR__ . '/../../includes/footer.php'; ?>

