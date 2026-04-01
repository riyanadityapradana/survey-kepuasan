<?php
require_once __DIR__ . '/../../config/koneksi.php';
cek_login();

$jenis = 'ranap';
$startDate = trim($_GET['start_date'] ?? '');
$endDate = trim($_GET['end_date'] ?? '');
$lokasi = trim($_GET['lokasi_aduan'] ?? '');
$kategori = trim($_GET['kategori'] ?? '');
$jenisKelamin = trim($_GET['jenis_kelamin'] ?? '');
$search = trim($_GET['q'] ?? '');

$pertanyaanKolom = [];
$wherePertanyaan = "WHERE jenis = 'ranap'";
if ($kategori !== '') {
    $wherePertanyaan .= " AND kategori = '" . mysqli_real_escape_string($conn, $kategori) . "'";
}
$qPertanyaan = mysqli_query($conn, "SELECT id, kategori, pertanyaan FROM pertanyaan $wherePertanyaan ORDER BY kategori ASC, id ASC");
while ($row = mysqli_fetch_assoc($qPertanyaan)) {
    $pertanyaanKolom[(int) $row['id']] = $row;
}

$where = ["r.jenis = 'ranap'"];
if ($jenisKelamin !== '') { $where[] = "r.jenis_kelamin = '" . mysqli_real_escape_string($conn, $jenisKelamin) . "'"; }
if ($lokasi !== '') { $where[] = "r.lokasi_aduan = '" . mysqli_real_escape_string($conn, $lokasi) . "'"; }
if ($startDate !== '') { $where[] = "DATE(r.tanggal) >= '" . mysqli_real_escape_string($conn, $startDate) . "'"; }
if ($endDate !== '') { $where[] = "DATE(r.tanggal) <= '" . mysqli_real_escape_string($conn, $endDate) . "'"; }
if ($search !== '') { $where[] = "r.nama LIKE '%" . mysqli_real_escape_string($conn, $search) . "%'"; }
if ($kategori !== '') { $where[] = "p.kategori = '" . mysqli_real_escape_string($conn, $kategori) . "'"; }
$whereSql = implode(' AND ', $where);
$detailQuery = mysqli_query($conn, "SELECT r.id AS id_responden, r.nama, r.jenis_kelamin, r.lokasi_aduan, r.saran, r.tanggal, p.id AS id_pertanyaan, j.nilai FROM responden r LEFT JOIN jawaban j ON j.id_responden = r.id LEFT JOIN pertanyaan p ON p.id = j.id_pertanyaan WHERE $whereSql ORDER BY r.tanggal DESC, r.id DESC, p.kategori ASC, p.id ASC");

$rows = [];
while ($row = mysqli_fetch_assoc($detailQuery)) {
    $id = (int) $row['id_responden'];
    if (!isset($rows[$id])) {
        $rows[$id] = ['tanggal' => $row['tanggal'], 'nama' => $row['nama'], 'jenis_kelamin' => $row['jenis_kelamin'], 'lokasi_aduan' => $row['lokasi_aduan'], 'jawaban' => []];
    }
    if (!empty($row['id_pertanyaan'])) {
        $nilai = (int) $row['nilai'];
        $rows[$id]['jawaban'][(int) $row['id_pertanyaan']] = $nilai === 3 ? 'Puas' : ($nilai === 2 ? 'Kurang Puas' : 'Tidak Puas');
    }
}

header('Content-Type: application/vnd.ms-excel');
header('Content-Disposition: attachment; filename="hasil-survei-rawat-inap.xls"');
?>
<table border="1">
    <tr><th>Tanggal</th><th>Nama</th><th>Jenis Kelamin</th><th>Lokasi Aduan</th><th>Saran</th><?php foreach ($pertanyaanKolom as $kolom) : ?><th><?= e($kolom['pertanyaan']); ?></th><?php endforeach; ?></tr>
    <?php foreach ($rows as $item) : ?>
        <tr><td><?= e($item['tanggal']); ?></td><td><?= e($item['nama']); ?></td><td><?= e($item['jenis_kelamin']); ?></td><td><?= e($item['lokasi_aduan']); ?></td><?php foreach ($pertanyaanKolom as $idPertanyaan => $kolom) : ?><td><?= e($item['jawaban'][$idPertanyaan] ?? '-'); ?></td><?php endforeach; ?></tr>
    <?php endforeach; ?>
</table>

