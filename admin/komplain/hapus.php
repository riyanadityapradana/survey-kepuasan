<?php
require_once __DIR__ . '/../../config/koneksi.php';
cek_role(['admin']);

$id = (int) ($_GET['id'] ?? 0);
if ($id <= 0) {
    set_flash('error', 'ID komplain tidak valid.');
    redirect_ke('admin/komplain/index.php');
}

$stmt = mysqli_prepare($conn, 'SELECT area_komplain FROM komplain WHERE id = ? LIMIT 1');
mysqli_stmt_bind_param($stmt, 'i', $id);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);
$data = mysqli_fetch_assoc($result);
mysqli_stmt_close($stmt);

if (!$data) {
    set_flash('error', 'Data komplain tidak ditemukan.');
    redirect_ke('admin/komplain/index.php');
}

$hapus = mysqli_prepare($conn, 'DELETE FROM komplain WHERE id = ?');
mysqli_stmt_bind_param($hapus, 'i', $id);
mysqli_stmt_execute($hapus);
mysqli_stmt_close($hapus);

log_audit($conn, 'hapus', 'komplain', $id, 'Menghapus komplain area ' . $data['area_komplain'] . '.');
set_flash('success', 'Data komplain berhasil dihapus.');
redirect_ke('admin/komplain/index.php');
?>
