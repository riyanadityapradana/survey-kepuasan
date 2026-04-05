<?php
require_once __DIR__ . '/../../config/koneksi.php';
cek_role(['admin']);

$bulan = trim($_GET['bulan'] ?? date('m'));
$tahun = trim($_GET['tahun'] ?? date('Y'));
if (!preg_match('/^(0[1-9]|1[0-2])$/', $bulan)) { $bulan = date('m'); }
if (!preg_match('/^\d{4}$/', $tahun)) { $tahun = date('Y'); }

$sql = 'SELECT k.*, u.username AS admin_input FROM komplain k LEFT JOIN users u ON u.id = k.created_by WHERE MONTH(k.tanggal_komplain) = ? AND YEAR(k.tanggal_komplain) = ? ORDER BY k.tanggal_komplain ASC, k.id ASC';
$stmt = mysqli_prepare($conn, $sql);
$bulanInt = (int) $bulan;
$tahunInt = (int) $tahun;
mysqli_stmt_bind_param($stmt, 'ii', $bulanInt, $tahunInt);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);
$rows = [];
$rekap = ['hijau' => 0, 'kuning' => 0, 'merah' => 0, 'belum' => 0];
while ($row = mysqli_fetch_assoc($result)) {
    $row['kategori_tanggap'] = kategori_waktu_tanggap($row['tanggal_komplain'], $row['tanggal_tindak_lanjut']);
    $kodeKategori = $row['kategori_tanggap']['kode'] ?? 'belum';
    if (array_key_exists($kodeKategori, $rekap)) {
        $rekap[$kodeKategori]++;
    }
    $rows[] = $row;
}
mysqli_stmt_close($stmt);

$pageTitle = 'Laporan Waktu Tanggap Komplain';
$activeMenu = 'komplain';
require_once __DIR__ . '/../../includes/header.php';
require_once __DIR__ . '/../../includes/admin_nav.php';
?>
<div class="container py-4">
    <div class="card google-card mb-4">
        <div class="card-body p-4">
            <div class="d-flex flex-wrap justify-content-between align-items-center gap-3 mb-4">
                <div>
                    <h3 class="fw-bold mb-1">Laporan Kecepatan Waktu Tanggap Komplain</h3>
                    <p class="text-muted mb-0">Laporan bulanan kategori hijau, kuning, dan merah berdasarkan selisih waktu tanggap.</p>
                </div>
                <a href="<?= url('admin/komplain/index.php'); ?>" class="btn btn-outline-primary">Kembali ke Kelola Komplain</a>
            </div>
            <form method="get" class="filter-grid mb-4">
                <div><label class="form-label">Periode</label><input type="month" name="periode" class="form-control" value="<?= e($tahun . '-' . $bulan); ?>" oninput="const [y,m]=this.value.split('-'); if(y&&m){ document.getElementById('bulan').value=m; document.getElementById('tahun').value=y; }"><input type="hidden" id="bulan" name="bulan" value="<?= e($bulan); ?>"><input type="hidden" id="tahun" name="tahun" value="<?= e($tahun); ?>"></div>
                <div class="filter-actions"><button type="submit" class="btn btn-rs-primary"><i class="fa-solid fa-filter me-2"></i>Tampilkan</button></div>
            </form>
            <div class="row g-3 mb-4">
                <div class="col-md-3"><div class="mini-stat"><span>Hijau (<= 24 jam)</span><strong><?= $rekap['hijau']; ?></strong></div></div>
                <div class="col-md-3"><div class="mini-stat"><span>Kuning (<= 72 jam)</span><strong><?= $rekap['kuning']; ?></strong></div></div>
                <div class="col-md-3"><div class="mini-stat"><span>Merah (> 72 jam)</span><strong><?= $rekap['merah']; ?></strong></div></div>
                <div class="col-md-3"><div class="mini-stat"><span>Belum Ditindaklanjuti</span><strong><?= $rekap['belum']; ?></strong></div></div>
            </div>
            <div class="table-responsive">
                <table class="table table-bordered align-middle laporan-komplain-table">
                    <thead>
                        <tr>
                            <th width="60">No</th>
                            <th width="120">Area Komplain</th>
                            <th width="180">Identitas Pasien / Pelapor</th>
                            <th>Aduan Keluhan / Komplain</th>
                            <th width="140">Tanggal / Jam Komplain</th>
                            <th width="160">Tanggal / Jam Tindak Lanjut</th>
                            <th width="120">Kategori</th>
                            <th width="220">Keterangan Tindak Lanjut</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (!$rows) : ?>
                            <tr><td colspan="8" class="text-center text-muted py-4">Belum ada data komplain pada periode ini.</td></tr>
                        <?php else : ?>
                            <?php foreach ($rows as $index => $row) : ?>
                                <tr>
                                    <td><?= $index + 1; ?></td>
                                    <td><?= e($row['area_komplain']); ?></td>
                                    <td><?= e($row['identitas_pasien']); ?></td>
                                    <td><?= e($row['aduan']); ?></td>
                                    <td><?= date('d-m-y H:i', strtotime($row['tanggal_komplain'])); ?></td>
                                    <td><?= !empty($row['tanggal_tindak_lanjut']) ? date('d-m-y H:i', strtotime($row['tanggal_tindak_lanjut'])) : '-'; ?></td>
                                    <td><?= badge_kategori_tanggap($row['kategori_tanggap']); ?></td>
                                    <td><?= e($row['keterangan_tindak_lanjut'] ?? '-'); ?></td>
                                </tr>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
<?php require_once __DIR__ . '/../../includes/footer.php'; ?>
