<?php
require_once __DIR__ . '/../../config/koneksi.php';
cek_role(['admin']);

$id = (int) ($_GET['id'] ?? 0);
if ($id <= 0 || $id === (int) user_saat_ini()['id']) {
    set_flash('error', 'User tidak valid untuk dihapus.');
    redirect_ke('admin/users/index.php');
}

$stmt = mysqli_prepare($conn, 'SELECT username FROM users WHERE id = ? LIMIT 1');
mysqli_stmt_bind_param($stmt, 'i', $id);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);
$user = mysqli_fetch_assoc($result);
mysqli_stmt_close($stmt);

if (!$user) {
    set_flash('error', 'User tidak ditemukan.');
    redirect_ke('admin/users/index.php');
}

$hapus = mysqli_prepare($conn, 'DELETE FROM users WHERE id = ?');
mysqli_stmt_bind_param($hapus, 'i', $id);
mysqli_stmt_execute($hapus);
mysqli_stmt_close($hapus);

log_audit($conn, 'hapus', 'user', $id, 'Menghapus user ' . $user['username'] . '.');
set_flash('success', 'User berhasil dihapus.');
redirect_ke('admin/users/index.php');
?>
