<?php
require_once __DIR__ . '/../../config/koneksi.php';
cek_role(['admin']);

$id = (int) ($_GET['id'] ?? 0);
$jenis = trim($_GET['jenis'] ?? '');
$return = trim($_GET['return'] ?? '');
$allowedJenis = ['ralan', 'ranap'];

if (!in_array($jenis, $allowedJenis, true)) {
    $jenis = 'ralan';
}

if ($return === '' || strpos($return, '/admin/hasil/') !== 0) {
    $return = url('admin/hasil/' . $jenis . '.php');
}

if ($id <= 0) {
    set_flash('error', 'ID responden tidak valid.');
    header('Location: ' . $return);
    exit;
}

$stmt = mysqli_prepare($conn, 'SELECT id, nama, jenis FROM responden WHERE id = ? LIMIT 1');
mysqli_stmt_bind_param($stmt, 'i', $id);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);
$responden = mysqli_fetch_assoc($result);
mysqli_stmt_close($stmt);

if (!$responden) {
    set_flash('error', 'Data responden tidak ditemukan.');
    header('Location: ' . $return);
    exit;
}

mysqli_begin_transaction($conn);
try {
    $hapusJawaban = mysqli_prepare($conn, 'DELETE FROM jawaban WHERE id_responden = ?');
    mysqli_stmt_bind_param($hapusJawaban, 'i', $id);
    mysqli_stmt_execute($hapusJawaban);
    mysqli_stmt_close($hapusJawaban);

    $hapusResponden = mysqli_prepare($conn, 'DELETE FROM responden WHERE id = ?');
    mysqli_stmt_bind_param($hapusResponden, 'i', $id);
    mysqli_stmt_execute($hapusResponden);
    mysqli_stmt_close($hapusResponden);

    log_audit($conn, 'hapus', 'responden', $id, 'Menghapus responden ' . $responden['nama'] . ' beserta seluruh jawaban surveinya.');
    mysqli_commit($conn);
    set_flash('success', 'Responden dan seluruh jawaban terkait berhasil dihapus.');
} catch (Throwable $e) {
    mysqli_rollback($conn);
    set_flash('error', 'Gagal menghapus responden.');
}

header('Location: ' . $return);
exit;
?>
