<?php
require_once __DIR__ . '/../config/koneksi.php';
cek_role(['admin']);

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    redirect_ke('admin/users/index.php');
}

$username = trim($_POST['username'] ?? '');
$password = trim($_POST['password'] ?? '');
$konfirmasi = trim($_POST['konfirmasi_password'] ?? '');
$role = trim($_POST['role'] ?? 'petugas');

if ($username === '' || $password === '' || $konfirmasi === '') {
    set_flash('error', 'Semua field user wajib diisi.');
    redirect_ke('admin/users/index.php');
}

if ($password !== $konfirmasi) {
    set_flash('error', 'Konfirmasi password tidak cocok.');
    redirect_ke('admin/users/index.php');
}

if (!in_array($role, ['admin', 'petugas'], true)) {
    $role = 'petugas';
}

$cek = mysqli_prepare($conn, 'SELECT id FROM users WHERE username = ? LIMIT 1');
mysqli_stmt_bind_param($cek, 's', $username);
mysqli_stmt_execute($cek);
$hasilCek = mysqli_stmt_get_result($cek);
$userAda = mysqli_fetch_assoc($hasilCek);
mysqli_stmt_close($cek);

if ($userAda) {
    set_flash('error', 'Username sudah digunakan, silakan pilih username lain.');
    redirect_ke('admin/users/index.php');
}

$hash = password_hash($password, PASSWORD_DEFAULT);
$stmt = mysqli_prepare($conn, 'INSERT INTO users (username, password, role, created_at) VALUES (?, ?, ?, NOW())');
mysqli_stmt_bind_param($stmt, 'sss', $username, $hash, $role);
mysqli_stmt_execute($stmt);
$userId = mysqli_insert_id($conn);
mysqli_stmt_close($stmt);

log_audit($conn, 'tambah', 'user', $userId, 'Menambahkan user ' . $username . ' dengan role ' . $role . '.');
set_flash('success', 'User baru berhasil disimpan.');
redirect_ke('admin/users/index.php');
?>
