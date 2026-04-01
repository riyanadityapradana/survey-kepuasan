<?php
require_once __DIR__ . '/../config/koneksi.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    redirect_ke('');
}

$nama = trim($_POST['nama'] ?? '');
$jenisKelamin = trim($_POST['jenis_kelamin'] ?? '');
$lokasiAduan = trim($_POST['lokasi_aduan'] ?? '');
$jenis = trim($_POST['jenis'] ?? '');
$jawaban = $_POST['jawaban'] ?? [];

if ($nama === '' || mb_strlen($nama) > 100 || $jenisKelamin === '' || $lokasiAduan === '' || !in_array($jenis, ['ralan', 'ranap'], true) || empty($jawaban) || mb_strlen($saran) > 1000) {
    set_flash('error', 'Semua data responden dan jawaban wajib diisi.');
    redirect_ke($jenis === 'ranap' ? 'survey/form_ranap.php' : 'survey/form_ralan.php');
}

if (!in_array($jenisKelamin, ['Laki-Laki', 'Perempuan'], true) || !in_array($lokasiAduan, daftar_lokasi_aduan(), true)) {
    set_flash('error', 'Data jenis kelamin atau lokasi aduan tidak valid.');
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
        set_flash('error', 'Semua pertanyaan wajib dijawab.');
        redirect_ke($jenis === 'ranap' ? 'survey/form_ranap.php' : 'survey/form_ralan.php');
    }
}

mysqli_begin_transaction($conn);
try {
    $stmtResponden = mysqli_prepare($conn, 'INSERT INTO responden (nama, jenis_kelamin, lokasi_aduan, jenis, saran, tanggal) VALUES (?, ?, ?, ?, ?, NOW())');
    mysqli_stmt_bind_param($stmtResponden, 'sssss', $nama, $jenisKelamin, $lokasiAduan, $jenis, $saran);
    mysqli_stmt_execute($stmtResponden);
    $idResponden = mysqli_insert_id($conn);
    mysqli_stmt_close($stmtResponden);

    $stmtJawaban = mysqli_prepare($conn, 'INSERT INTO jawaban (id_responden, id_pertanyaan, nilai, tanggal) VALUES (?, ?, ?, NOW())');
    foreach ($jawaban as $idPertanyaan => $nilai) {
        $idPertanyaan = (int) $idPertanyaan;
        $nilai = (int) $nilai;
        if (!in_array($idPertanyaan, $wajibJawab, true)) {
            throw new RuntimeException('Pertanyaan tidak valid.');
        }
        mysqli_stmt_bind_param($stmtJawaban, 'iii', $idResponden, $idPertanyaan, $nilai);
        mysqli_stmt_execute($stmtJawaban);
    }
    mysqli_stmt_close($stmtJawaban);
    mysqli_commit($conn);
} catch (Throwable $e) {
    mysqli_rollback($conn);
    set_flash('error', 'Gagal menyimpan jawaban. Silakan coba lagi.');
    redirect_ke($jenis === 'ranap' ? 'survey/form_ranap.php' : 'survey/form_ralan.php');
}

redirect_ke('survey/terima_kasih.php?jenis=' . $jenis);
?>

