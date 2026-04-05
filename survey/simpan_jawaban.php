<?php
require_once __DIR__ . '/../config/koneksi.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    redirect_ke('');
}

$nama = trim($_POST['nama'] ?? '');
$jenisKelamin = trim($_POST['jenis_kelamin'] ?? '');
$tanggungan = trim($_POST['tanggungan'] ?? '');
$lokasiAduan = trim($_POST['lokasi_aduan'] ?? '');
$jenis = trim($_POST['jenis'] ?? '');
$saran = trim($_POST['saran'] ?? '');
$jawaban = $_POST['jawaban'] ?? [];
$dataResponden = [
    'nama' => $nama,
    'jenis_kelamin' => $jenisKelamin,
    'tanggungan' => $tanggungan,
    'lokasi_aduan' => $lokasiAduan,
    'saran' => $saran,
];

if ($nama === '' || mb_strlen($nama) > 100 || $jenisKelamin === '' || $tanggungan === '' || mb_strlen($tanggungan) > 50 || $lokasiAduan === '' || mb_strlen($lokasiAduan) > 250 || !in_array($jenis, ['ralan', 'ranap'], true) || empty($jawaban) || mb_strlen($saran) > 1000) {
    set_old_input($dataResponden);
    set_flash('error', 'Semua data responden dan jawaban wajib diisi.');
    redirect_ke($jenis === 'ranap' ? 'survey/form_ranap.php' : 'survey/form_ralan.php');
}

if (!in_array($jenisKelamin, ['Laki-Laki', 'Perempuan'], true)) {
    set_old_input($dataResponden);
    set_flash('error', 'Data jenis kelamin tidak valid.');
    redirect_ke($jenis === 'ranap' ? 'survey/form_ranap.php' : 'survey/form_ralan.php');
}

$pertanyaanDb = ambil_pertanyaan_berdasarkan_jenis($conn, $jenis);
$wajibJawab = [];
foreach ($pertanyaanDb as $items) {
    foreach ($items as $item) {
        $wajibJawab[] = (int) $item['id'];
    }
}

foreach ($wajibJawab as $idPertanyaan) {
    if (!isset($jawaban[$idPertanyaan]) || !in_array((int) $jawaban[$idPertanyaan], [1, 2, 3], true)) {
        set_old_input($dataResponden);
        set_flash('error', 'Semua pertanyaan wajib dijawab.');
        redirect_ke($jenis === 'ranap' ? 'survey/form_ranap.php' : 'survey/form_ralan.php');
    }
}

$tanggalSimpan = date('Y-m-d H:i:s');
mysqli_begin_transaction($conn);
try {
    $stmtResponden = mysqli_prepare($conn, 'INSERT INTO responden (nama, jenis_kelamin, tanggungan, lokasi_aduan, jenis, saran, tanggal) VALUES (?, ?, ?, ?, ?, ?, ?)');
    mysqli_stmt_bind_param($stmtResponden, 'sssssss', $nama, $jenisKelamin, $tanggungan, $lokasiAduan, $jenis, $saran, $tanggalSimpan);
    mysqli_stmt_execute($stmtResponden);
    $idResponden = mysqli_insert_id($conn);
    mysqli_stmt_close($stmtResponden);

    $stmtJawaban = mysqli_prepare($conn, 'INSERT INTO jawaban (id_responden, id_pertanyaan, nilai, tanggal) VALUES (?, ?, ?, ?)');
    foreach ($jawaban as $idPertanyaan => $nilai) {
        $idPertanyaan = (int) $idPertanyaan;
        $nilai = (int) $nilai;
        if (!in_array($idPertanyaan, $wajibJawab, true)) {
            throw new RuntimeException('Pertanyaan tidak valid.');
        }
        mysqli_stmt_bind_param($stmtJawaban, 'iiis', $idResponden, $idPertanyaan, $nilai, $tanggalSimpan);
        mysqli_stmt_execute($stmtJawaban);
    }
    mysqli_stmt_close($stmtJawaban);

    sinkron_komplain_otomatis($conn, $idResponden, $nama, $lokasiAduan, $tanggalSimpan, $saran, $jawaban, $pertanyaanDb);

    mysqli_commit($conn);
    clear_old_input();
    kirim_notifikasi_telegram_survei($jenis, $nama, $jenisKelamin, $lokasiAduan, $tanggalSimpan, $saran, $jawaban);
} catch (Throwable $e) {
    mysqli_rollback($conn);
    set_old_input($dataResponden);
    set_flash('error', 'Gagal menyimpan jawaban. Silakan coba lagi.');
    redirect_ke($jenis === 'ranap' ? 'survey/form_ranap.php' : 'survey/form_ralan.php');
}

redirect_ke('survey/terima_kasih.php?jenis=' . $jenis);
?>

