<?php
require_once __DIR__ . '/../config/koneksi.php';
cek_login();

function ambil_data_grafik(mysqli $conn, string $jenis): array
{
    $dailySql = "SELECT DATE(tanggal) AS tanggal, COUNT(*) AS total FROM responden WHERE jenis = ? AND DATE(tanggal) >= DATE_SUB(CURDATE(), INTERVAL 6 DAY) GROUP BY DATE(tanggal) ORDER BY DATE(tanggal) ASC";
    $stmtDaily = mysqli_prepare($conn, $dailySql);
    mysqli_stmt_bind_param($stmtDaily, 's', $jenis);
    mysqli_stmt_execute($stmtDaily);
    $dailyRows = mysqli_stmt_get_result($stmtDaily);
    $dailyMap = [];
    while ($row = mysqli_fetch_assoc($dailyRows)) {
        $dailyMap[$row['tanggal']] = (int) $row['total'];
    }
    mysqli_stmt_close($stmtDaily);

    $dailyLabels = [];
    $dailyValues = [];
    for ($i = 6; $i >= 0; $i--) {
        $dateKey = date('Y-m-d', strtotime('-' . $i . ' days'));
        $dailyLabels[] = date('d M', strtotime($dateKey));
        $dailyValues[] = $dailyMap[$dateKey] ?? 0;
    }

    $distributionSql = "SELECT j.nilai, COUNT(*) AS total FROM jawaban j INNER JOIN responden r ON r.id = j.id_responden WHERE r.jenis = ? GROUP BY j.nilai";
    $stmtDistribution = mysqli_prepare($conn, $distributionSql);
    mysqli_stmt_bind_param($stmtDistribution, 's', $jenis);
    mysqli_stmt_execute($stmtDistribution);
    $distributionRows = mysqli_stmt_get_result($stmtDistribution);
    $distributionMap = [1 => 0, 2 => 0, 3 => 0];
    while ($row = mysqli_fetch_assoc($distributionRows)) {
        $distributionMap[(int) $row['nilai']] = (int) $row['total'];
    }
    mysqli_stmt_close($stmtDistribution);

    $lowestSql = "SELECT p.kategori, ROUND(AVG(j.nilai), 2) AS rata_rata, COUNT(j.id) AS total_jawaban FROM pertanyaan p INNER JOIN jawaban j ON j.id_pertanyaan = p.id INNER JOIN responden r ON r.id = j.id_responden WHERE p.jenis = ? AND r.jenis = ? GROUP BY p.kategori HAVING COUNT(j.id) > 0 ORDER BY rata_rata ASC, total_jawaban DESC LIMIT 5";
    $stmtLowest = mysqli_prepare($conn, $lowestSql);
    mysqli_stmt_bind_param($stmtLowest, 'ss', $jenis, $jenis);
    mysqli_stmt_execute($stmtLowest);
    $lowestRows = mysqli_stmt_get_result($stmtLowest);
    $lowestLabels = [];
    $lowestValues = [];
    while ($row = mysqli_fetch_assoc($lowestRows)) {
        $lowestLabels[] = $row['kategori'];
        $lowestValues[] = (float) $row['rata_rata'];
    }
    mysqli_stmt_close($stmtLowest);

    return [
        'daily_labels' => $dailyLabels,
        'daily_values' => $dailyValues,
        'distribution_values' => [$distributionMap[1], $distributionMap[2], $distributionMap[3]],
        'lowest_labels' => $lowestLabels,
        'lowest_values' => $lowestValues,
    ];
}

$stats = hitung_ringkasan($conn);
$pertanyaanBaru = mysqli_query($conn, 'SELECT jenis, kategori, pertanyaan FROM pertanyaan ORDER BY id DESC LIMIT 5');
$respondenBaru = mysqli_query($conn, 'SELECT nama, jenis_kelamin, tanggungan, lokasi_aduan, saran, jenis, tanggal FROM responden ORDER BY tanggal DESC, id DESC LIMIT 5');
$auditLogs = mysqli_query($conn, 'SELECT a.aksi, a.entitas, a.deskripsi, a.created_at, u.username FROM audit_logs a LEFT JOIN users u ON u.id = a.user_id ORDER BY a.id DESC LIMIT 8');

$grafikRalan = ambil_data_grafik($conn, 'ralan');
$grafikRanap = ambil_data_grafik($conn, 'ranap');

$pageTitle = 'Dashboard Admin';
$activeMenu = 'dashboard';
$extraScripts = ['https://cdn.jsdelivr.net/npm/chart.js'];
$inlineScripts = [
    'function renderLineChart(id, labels, values, color) { const el = document.getElementById(id); if (!el) return; new Chart(el, { type: "line", data: { labels, datasets: [{ data: values, borderColor: color, backgroundColor: color.replace(")", ", 0.14)").replace("rgb", "rgba"), fill: true, tension: 0.35, borderWidth: 3, pointRadius: 4 }] }, options: { responsive: true, plugins: { legend: { display: false } }, scales: { y: { beginAtZero: true, ticks: { precision: 0 } } } } }); }',
    'function renderDoughnutChart(id, values) { const el = document.getElementById(id); if (!el) return; new Chart(el, { type: "doughnut", data: { labels: ["Tidak Puas", "Kurang Puas", "Puas"], datasets: [{ data: values, backgroundColor: ["#dc3545", "#ffc107", "#198754"] }] }, options: { responsive: true, plugins: { legend: { position: "bottom" } } } }); }',
    'function renderBarChart(id, labels, values, color) { const el = document.getElementById(id); if (!el || !labels.length) return; new Chart(el, { type: "bar", data: { labels, datasets: [{ data: values, backgroundColor: color, borderRadius: 10 }] }, options: { indexAxis: "y", responsive: true, plugins: { legend: { display: false } }, scales: { x: { min: 0, max: 3 } } } }); }',
    'renderLineChart("chartDailyRalan", ' . json_encode($grafikRalan['daily_labels']) . ', ' . json_encode($grafikRalan['daily_values']) . ', "rgb(13, 110, 253)");',
    'renderDoughnutChart("chartDistributionRalan", ' . json_encode($grafikRalan['distribution_values']) . ');',
    'renderBarChart("chartLowestRalan", ' . json_encode($grafikRalan['lowest_labels']) . ', ' . json_encode($grafikRalan['lowest_values']) . ', "rgba(13, 110, 253, 0.72)");',
    'renderLineChart("chartDailyRanap", ' . json_encode($grafikRanap['daily_labels']) . ', ' . json_encode($grafikRanap['daily_values']) . ', "rgb(25, 135, 84)");',
    'renderDoughnutChart("chartDistributionRanap", ' . json_encode($grafikRanap['distribution_values']) . ');',
    'renderBarChart("chartLowestRanap", ' . json_encode($grafikRanap['lowest_labels']) . ', ' . json_encode($grafikRanap['lowest_values']) . ', "rgba(25, 135, 84, 0.72)");',
];
require_once __DIR__ . '/../includes/header.php';
require_once __DIR__ . '/../includes/admin_nav.php';
?>
<div class="container py-4">
    <div class="d-flex flex-wrap justify-content-between align-items-center mb-4 gap-3">
        <div>
            <h2 class="fw-bold mb-1">Dashboard Admin</h2>
            <p class="text-muted mb-0">Selamat datang, <?= e($_SESSION['user']['username']); ?>. Pantau tren survei dan kualitas layanan per unit dari satu halaman.</p>
        </div>
        <div class="d-flex gap-2 flex-wrap">
            <?php if (user_punya_role('admin')) : ?>
                <a href="<?= url('admin/pertanyaan/tambah.php'); ?>" class="btn btn-rs-primary"><i class="fa-solid fa-plus me-2"></i>Tambah Pertanyaan</a>
                <a href="<?= url('admin/users/index.php'); ?>" class="btn btn-outline-primary">Kelola User</a>
            <?php endif; ?>
            <a href="<?= url('admin/hasil/ralan.php'); ?>" class="btn btn-outline-primary">Lihat Hasil Ralan</a>
            <a href="<?= url('admin/hasil/ranap.php'); ?>" class="btn btn-outline-primary">Lihat Hasil Ranap</a>
        </div>
    </div>

    <div class="row g-4 mb-4">
        <div class="col-md-3"><div class="card google-card stat-card h-100"><div class="card-body"><span>Total Pertanyaan</span><h3><?= $stats['pertanyaan']; ?></h3><i class="fa-solid fa-circle-question"></i></div></div></div>
        <div class="col-md-3"><div class="card google-card stat-card h-100"><div class="card-body"><span>Total Responden</span><h3><?= $stats['responden']; ?></h3><i class="fa-solid fa-users"></i></div></div></div>
        <div class="col-md-3"><div class="card google-card stat-card h-100"><div class="card-body"><span>Rawat Jalan</span><h3><?= $stats['ralan']; ?></h3><i class="fa-solid fa-stethoscope"></i></div></div></div>
        <div class="col-md-3"><div class="card google-card stat-card h-100"><div class="card-body"><span>Rawat Inap</span><h3><?= $stats['ranap']; ?></h3><i class="fa-solid fa-bed-pulse"></i></div></div></div>
    </div>

    <section class="mb-4">
        <div class="section-heading mb-3">
            <h4 class="fw-semibold mb-1">Grafik Rawat Jalan</h4>
            <p class="text-muted mb-0">Semua grafik di bawah ini hanya menghitung data survei rawat jalan.</p>
        </div>
        <div class="row g-4">
            <div class="col-lg-7"><div class="card google-card chart-card h-100"><div class="card-body p-4"><div class="d-flex justify-content-between align-items-center mb-3"><div><h5 class="fw-semibold mb-1">Jumlah Survei per Hari</h5><p class="text-muted small mb-0">7 hari terakhir.</p></div><span class="chart-pill">Ralan</span></div><canvas id="chartDailyRalan" height="140"></canvas></div></div></div>
            <div class="col-lg-5"><div class="card google-card chart-card h-100"><div class="card-body p-4"><div class="d-flex justify-content-between align-items-center mb-3"><div><h5 class="fw-semibold mb-1">Distribusi Kepuasan</h5><p class="text-muted small mb-0">Khusus jawaban rawat jalan.</p></div><span class="chart-pill">Ralan</span></div><canvas id="chartDistributionRalan" height="220"></canvas></div></div></div>
            <div class="col-12"><div class="card google-card chart-card h-100"><div class="card-body p-4"><div class="d-flex justify-content-between align-items-center mb-3"><div><h5 class="fw-semibold mb-1">Kategori dengan Nilai Terendah</h5><p class="text-muted small mb-0">Prioritas perbaikan untuk layanan rawat jalan.</p></div><span class="chart-pill">Ralan</span></div><?php if ($grafikRalan['lowest_labels']) : ?><canvas id="chartLowestRalan" height="180"></canvas><?php else : ?><div class="empty-state py-5">Belum ada jawaban rawat jalan untuk dianalisis.</div><?php endif; ?></div></div></div>
        </div>
    </section>

    <section class="mb-4">
        <div class="section-heading mb-3">
            <h4 class="fw-semibold mb-1">Grafik Rawat Inap</h4>
            <p class="text-muted mb-0">Semua grafik di bawah ini hanya menghitung data survei rawat inap.</p>
        </div>
        <div class="row g-4">
            <div class="col-lg-7"><div class="card google-card chart-card h-100"><div class="card-body p-4"><div class="d-flex justify-content-between align-items-center mb-3"><div><h5 class="fw-semibold mb-1">Jumlah Survei per Hari</h5><p class="text-muted small mb-0">7 hari terakhir.</p></div><span class="chart-pill chart-pill-success">Ranap</span></div><canvas id="chartDailyRanap" height="140"></canvas></div></div></div>
            <div class="col-lg-5"><div class="card google-card chart-card h-100"><div class="card-body p-4"><div class="d-flex justify-content-between align-items-center mb-3"><div><h5 class="fw-semibold mb-1">Distribusi Kepuasan</h5><p class="text-muted small mb-0">Khusus jawaban rawat inap.</p></div><span class="chart-pill chart-pill-success">Ranap</span></div><canvas id="chartDistributionRanap" height="220"></canvas></div></div></div>
            <div class="col-12"><div class="card google-card chart-card h-100"><div class="card-body p-4"><div class="d-flex justify-content-between align-items-center mb-3"><div><h5 class="fw-semibold mb-1">Kategori dengan Nilai Terendah</h5><p class="text-muted small mb-0">Prioritas perbaikan untuk layanan rawat inap.</p></div><span class="chart-pill chart-pill-success">Ranap</span></div><?php if ($grafikRanap['lowest_labels']) : ?><canvas id="chartLowestRanap" height="180"></canvas><?php else : ?><div class="empty-state py-5">Belum ada jawaban rawat inap untuk dianalisis.</div><?php endif; ?></div></div></div>
        </div>
    </section>

    <div class="row g-4 mb-4">
        <div class="col-lg-6"><div class="card google-card h-100"><div class="card-header bg-white border-0 pt-4 pb-0 px-4"><h5 class="fw-semibold mb-0">Aktivitas Audit Terbaru</h5></div><div class="card-body p-4"><?php if (!$auditLogs || mysqli_num_rows($auditLogs) === 0) : ?><div class="empty-state">Belum ada aktivitas audit.</div><?php else : ?><div class="timeline-list"><?php while ($log = mysqli_fetch_assoc($auditLogs)) : ?><div class="timeline-item"><strong><?= e($log['username'] ?? 'Sistem'); ?></strong><span class="small text-muted d-block mb-1"><?= date('d-m-Y H:i', strtotime($log['created_at'])); ?></span><p class="mb-0"><?= e($log['deskripsi'] ?: ($log['aksi'] . ' ' . $log['entitas'])); ?></p></div><?php endwhile; ?></div><?php endif; ?></div></div></div>
        <div class="col-lg-6"><div class="card google-card h-100"><div class="card-header bg-white border-0 pt-4 pb-0 px-4"><h5 class="fw-semibold mb-0">Responden Terbaru</h5></div><div class="card-body p-4"><div class="table-responsive"><table class="table align-middle"><thead><tr><th>Nama</th><th>JK</th><th>Tanggungan</th><th>Lokasi Aduan</th><th>Jenis</th><th>Tanggal</th></tr></thead><tbody><?php while ($row = mysqli_fetch_assoc($respondenBaru)) : ?><tr><td><?= e($row['nama']); ?></td><td><?= e($row['jenis_kelamin']); ?></td><td><?= e($row['tanggungan']); ?></td><td><?= e($row['lokasi_aduan']); ?></td><td><?= strtoupper(e($row['jenis'])); ?></td><td><?= date('d-m-Y H:i', strtotime($row['tanggal'])); ?></td></tr><?php endwhile; ?></tbody></table></div></div></div></div>
    </div>

    <div class="row g-4">
        <div class="col-lg-12"><div class="card google-card h-100"><div class="card-header bg-white border-0 pt-4 pb-0 px-4"><h5 class="fw-semibold mb-0">Pertanyaan Terbaru</h5></div><div class="card-body p-4"><div class="table-responsive"><table class="table align-middle"><thead><tr><th>Jenis</th><th>Kategori</th><th>Pertanyaan</th></tr></thead><tbody><?php while ($row = mysqli_fetch_assoc($pertanyaanBaru)) : ?><tr><td><span class="badge text-bg-primary"><?= strtoupper(e($row['jenis'])); ?></span></td><td><?= e($row['kategori']); ?></td><td><?= e($row['pertanyaan']); ?></td></tr><?php endwhile; ?></tbody></table></div></div></div></div>
    </div>
</div>
<?php require_once __DIR__ . '/../includes/footer.php'; ?>

