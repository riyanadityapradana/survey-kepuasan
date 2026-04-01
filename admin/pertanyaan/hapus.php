<?php
require_once __DIR__ . '/../../config/koneksi.php';
cek_role(['admin']);

$id = (int) ($_GET['id'] ?? 0);
if ($id <= 0) {
    set_flash('error', 'ID pertanyaan tidak valid.');
    redirect_ke('admin/pertanyaan/index.php');
}

$cek = mysqli_prepare($conn, 'SELECT kategori, jenis FROM pertanyaan WHERE id = ? LIMIT 1');
mysqli_stmt_bind_param($cek, 'i', $id);
mysqli_stmt_execute($cek);
$hasil = mysqli_stmt_get_result($cek);
$data = mysqli_fetch_assoc($hasil);
mysqli_stmt_close($cek);

$stmt = mysqli_prepare($conn, 'DELETE FROM pertanyaan WHERE id = ?');
mysqli_stmt_bind_param($stmt, 'i', $id);
mysqli_stmt_execute($stmt);
mysqli_stmt_close($stmt);

if ($data) {
    log_audit($conn, 'hapus', 'pertanyaan', $id, 'Menghapus pertanyaan ' . $data['kategori'] . ' untuk ' . $data['jenis'] . '.');
}

set_flash('success', 'Pertanyaan berhasil dihapus.');
redirect_ke('admin/pertanyaan/index.php');
?>
